<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/turnstile.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/email_verify.php';
require_once __DIR__ . '/signatures.php';

$config = petition_config();

if (!$config['debug']) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

date_default_timezone_set('UTC');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header_remove('X-Powered-By');

// Best-effort schema ensure (adds is_public when missing). Never fatal on page render.
petition_ensure_schema();
