<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Honeypot: bots fill hidden "website" field
$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    // Pretend success to avoid tipping off scrapers
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Thank you for signing.']);
    exit;
}

$ip = petition_client_ip();
$rate = petition_rate_limit_allow($ip);
if (!$rate['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . (string) $rate['retry_after']);
    echo json_encode([
        'ok' => false,
        'error' => 'Too many signature attempts from your network. Please try again later.',
        'retry_after' => $rate['retry_after'],
    ]);
    exit;
}

$turnstileToken = (string) ($_POST['cf-turnstile-response'] ?? '');
$verify = petition_verify_turnstile($turnstileToken, $ip);
if (!$verify['success']) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $verify['error'] ?? 'Verification failed.']);
    exit;
}

$validated = petition_validate_signature_input([
    'email' => $_POST['email'] ?? '',
    'character_name' => $_POST['character_name'] ?? '',
]);

if (!$validated['ok']) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Please fix the highlighted fields.',
        'fields' => $validated['errors'],
    ]);
    exit;
}

$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);

try {
    $result = petition_create_signature($validated['data'], $ip, $userAgent);
} catch (Throwable $e) {
    // Never log emails or form bodies.
    error_log('petition: sign failed: ' . $e->getMessage());
    http_response_code(500);
    $payload = [
        'ok' => false,
        'error' => 'Could not save your signature. Please try again later.',
        'code' => 'server',
    ];
    if (petition_config()['debug']) {
        $payload['debug'] = $e->getMessage();
    }
    echo json_encode($payload);
    exit;
}

if (!$result['ok']) {
    $status = ($result['code'] ?? '') === 'duplicate' ? 409 : 500;
    http_response_code($status);
    echo json_encode([
        'ok' => false,
        'error' => $result['error'] ?? 'Could not save signature.',
        'code' => $result['code'] ?? 'error',
    ]);
    exit;
}

try {
    $count = petition_signature_count();
    $recent = petition_recent_signatures(12);
} catch (Throwable $e) {
    $count = 0;
    $recent = [];
}

$namesThreshold = max(1, (int) (petition_config()['names_public_threshold'] ?? 25));
$namesVisible = $count >= $namesThreshold;

// Public payload: character names + relative times only below threshold: omit names.
// Never include email. Never pad fake rows.
$publicRecent = [];
if ($namesVisible) {
    $publicRecent = array_map(static function (array $row): array {
        return [
            'character_name' => $row['character_name'],
            'when' => petition_format_relative_time($row['created_at']),
            'created_at' => $row['created_at'],
        ];
    }, $recent);
}

echo json_encode([
    'ok' => true,
    'message' => 'Thank you. Your signature was recorded.',
    'count' => $count,
    'names_visible' => $namesVisible,
    'names_threshold' => $namesThreshold,
    'recent' => $publicRecent,
]);
