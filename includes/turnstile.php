<?php
declare(strict_types=1);

/**
 * Verify a Cloudflare Turnstile token server-side.
 * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 */
function petition_verify_turnstile(string $token, ?string $remoteIp = null): array
{
    $config = petition_config();
    $secret = $config['turnstile']['secret_key'] ?? '';
    if ($secret === '') {
        // Local/staging convenience only — never skip in production.
        if (!empty($config['debug'])) {
            return [
                'success' => true,
                'error' => null,
                'skipped' => true,
            ];
        }
        return [
            'success' => false,
            'error' => 'Turnstile is not configured (missing TURNSTILE_SECRET_KEY).',
        ];
    }

    if ($token === '') {
        return [
            'success' => false,
            'error' => 'Please complete the human verification.',
        ];
    }

    $payload = [
        'secret' => $secret,
        'response' => $token,
    ];
    if ($remoteIp !== null && $remoteIp !== '') {
        $payload['remoteip'] = $remoteIp;
    }

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    if ($ch === false) {
        return ['success' => false, 'error' => 'Unable to start verification request.'];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno !== 0 || !is_string($raw) || $raw === '') {
        return ['success' => false, 'error' => 'Verification service unavailable. Try again shortly.'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['success' => false, 'error' => 'Invalid verification response.'];
    }

    if (!empty($data['success'])) {
        return ['success' => true, 'error' => null];
    }

    return [
        'success' => false,
        'error' => 'Human verification failed. Please try again.',
        'codes' => $data['error-codes'] ?? [],
    ];
}
