<?php
declare(strict_types=1);

function petition_client_ip(): string
{
    // Prefer direct connection IP. Do not trust X-Forwarded-For unless you terminate
    // TLS at Cloudflare and configure trusted proxies in Plesk.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return is_string($ip) ? $ip : '0.0.0.0';
}

function petition_rate_limit_allow(string $ip): array
{
    $cfg = petition_config()['rate_limit'];
    $max = (int) $cfg['max'];
    $window = (int) $cfg['window'];
    $whitelist = $cfg['whitelist'];

    if ($max <= 0 || in_array($ip, $whitelist, true)) {
        return ['allowed' => true, 'remaining' => $max, 'retry_after' => 0];
    }

    $dir = $cfg['storage'];
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        // Fail open for availability but log-ish via error_log
        error_log('petition: unable to create rate limit storage at ' . $dir);
        return ['allowed' => true, 'remaining' => $max, 'retry_after' => 0];
    }

    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $now = time();
    $hits = [];

    if (is_readable($file)) {
        $raw = file_get_contents($file);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        if (is_array($decoded) && isset($decoded['hits']) && is_array($decoded['hits'])) {
            $hits = array_map('intval', $decoded['hits']);
            $hits = array_values(array_filter($hits, static fn(int $t) => $t > $now - $window));
        }
    }

    if (count($hits) >= $max) {
        $oldest = min($hits);
        $retry = max(1, ($oldest + $window) - $now);
        return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retry];
    }

    $hits[] = $now;
    $payload = json_encode(['hits' => $hits], JSON_THROW_ON_ERROR);
    file_put_contents($file, $payload, LOCK_EX);

    return [
        'allowed' => true,
        'remaining' => max(0, $max - count($hits)),
        'retry_after' => 0,
    ];
}
