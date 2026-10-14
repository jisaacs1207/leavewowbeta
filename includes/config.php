<?php
declare(strict_types=1);

/**
 * Load environment variables from petition/.env (or process env).
 */
function petition_load_env(string $envPath): void
{
    if (!is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }
        // Strip matching single/double quotes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$name] = $value;
        putenv($name . '=' . $value);
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (string) $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env($key);
    if ($value === null) {
        return $default;
    }
    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}

function env_int(string $key, int $default): int
{
    $value = env($key);
    if ($value === null || !is_numeric($value)) {
        return $default;
    }
    return (int) $value;
}

function petition_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $root = dirname(__DIR__);
    petition_load_env($root . '/.env');

    $config = [
        'root' => $root,
        'db' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env_int('DB_PORT', 3306),
            'name' => env('DB_NAME', 'leavewowbeta'),
            'user' => env('DB_USER', 'leavewowbeta'),
            'pass' => env('DB_PASS', ''),
        ],
        'turnstile' => [
            'site_key' => env('TURNSTILE_SITE_KEY', ''),
            'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
        ],
        'rate_limit' => [
            'max' => env_int('RATE_LIMIT_MAX', 5),
            'window' => env_int('RATE_LIMIT_WINDOW_SECONDS', 3600),
            'whitelist' => array_values(array_filter(array_map(
                'trim',
                explode(',', env('RATE_LIMIT_WHITELIST', '') ?? '')
            ))),
            'storage' => $root . '/storage/rate_limits',
        ],
        'app_url' => rtrim(env('APP_URL', 'https://leavewowbeta.live') ?? 'https://leavewowbeta.live', '/'),
        'debug' => env_bool('APP_DEBUG', false),
        'hash_pepper' => env('HASH_PEPPER', env('DB_PASS', 'leavewowbeta-pepper')),
    ];

    return $config;
}
