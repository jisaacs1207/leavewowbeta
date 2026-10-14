<?php
declare(strict_types=1);

/**
 * Optional third-party email checks (syntax/MX/disposable).
 * Fail-open: timeouts and transport errors never block a signature.
 * Never log the email address.
 */

/**
 * @return array{ok:bool,error?:string,skipped?:bool,status?:string}
 */
function petition_verify_email_address(string $email): array
{
    $cfg = petition_config();
    if (empty($cfg['email_verifier']['enabled'])) {
        return ['ok' => true, 'skipped' => true];
    }

    $url = (string) ($cfg['email_verifier']['url'] ?? '');
    if ($url === '') {
        return ['ok' => true, 'skipped' => true];
    }

    $timeout = max(1, (int) ($cfg['email_verifier']['timeout'] ?? 2));
    $endpoint = $url . (str_contains($url, '?') ? '&' : '?') . 'email=' . rawurlencode($email);

    $ch = curl_init($endpoint);
    if ($ch === false) {
        error_log('petition: email verifier curl_init failed; allowing sign');
        return ['ok' => true, 'skipped' => true];
    }

    curl_setopt_array($ch, [
        CURLOPT_HTTPGET => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: leavewowbeta-petition/1.0',
        ],
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0 || !is_string($raw) || $raw === '' || $http >= 500 || $http === 0) {
        // Fail open: do not block the petition if the helper is down.
        error_log('petition: email verifier unavailable (http=' . $http . ', errno=' . $errno . '); allowing sign');
        return ['ok' => true, 'skipped' => true];
    }

    if ($http >= 400) {
        error_log('petition: email verifier client error http=' . $http . '; allowing sign');
        return ['ok' => true, 'skipped' => true];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_log('petition: email verifier returned non-JSON; allowing sign');
        return ['ok' => true, 'skipped' => true];
    }

    $status = strtoupper((string) ($data['status'] ?? ''));
    $validations = is_array($data['validations'] ?? null) ? $data['validations'] : [];

    // Local filter_var already caught obvious format errors; treat API INVALID_FORMAT as soft signal only.
    if ($status === 'DISPOSABLE' || !empty($validations['is_disposable'])) {
        return [
            'ok' => false,
            'error' => 'Use a lasting email address, not a disposable one.',
            'status' => 'DISPOSABLE',
        ];
    }

    // Do not reject on mailbox_exists / score: API docs say that is MX-level, not inbox proof.
    return [
        'ok' => true,
        'status' => $status !== '' ? $status : 'UNKNOWN',
    ];
}
