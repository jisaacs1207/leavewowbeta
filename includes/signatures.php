<?php
declare(strict_types=1);

function petition_normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function petition_email_hash(string $email): string
{
    $pepper = petition_config()['hash_pepper'] ?? '';
    return hash('sha256', $pepper . '|' . petition_normalize_email($email));
}

function petition_ip_hash(string $ip): string
{
    $pepper = petition_config()['hash_pepper'] ?? '';
    return hash('sha256', $pepper . '|ip|' . $ip);
}

function petition_sanitize_character_name(string $name): string
{
    // WoW character names: letters only, typically 2-12; allow spaces for RP servers / display.
    $name = trim($name);
    $name = preg_replace('/\s+/u', ' ', $name) ?? '';
    // Strip control chars and angle brackets
    $name = preg_replace('/[\x00-\x1F\x7F<>]/u', '', $name) ?? '';
    return $name;
}

/**
 * Validate public form input. Only email + character name are collected.
 *
 * @return array{ok:bool,errors:array<string,string>,data:array{email:string,character_name:string}}
 */
function petition_validate_signature_input(array $input): array
{
    $errors = [];

    $email = petition_normalize_email((string) ($input['email'] ?? ''));
    $character = petition_sanitize_character_name((string) ($input['character_name'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors['email'] = 'Email is too long.';
    }

    $charLen = mb_strlen($character);
    if ($character === '' || $charLen < 2) {
        $errors['character_name'] = 'Enter your character name (at least 2 characters).';
    } elseif ($charLen > 32) {
        $errors['character_name'] = 'Character name must be 32 characters or fewer.';
    } elseif (!preg_match('/^[\p{L}][\p{L}\s\-\']{0,31}$/u', $character)) {
        $errors['character_name'] = 'Character name may only contain letters, spaces, hyphens, or apostrophes.';
    }

    return [
        'ok' => $errors === [],
        'errors' => $errors,
        'data' => [
            'email' => $email,
            'character_name' => $character,
        ],
    ];
}

function petition_signature_count(): int
{
    $stmt = petition_db()->query('SELECT COUNT(*) AS c FROM signatures');
    $row = $stmt->fetch();
    return (int) ($row['c'] ?? 0);
}

/**
 * Public list only: never select or return email / email_hash / ip fields.
 *
 * @return list<array{character_name:string,created_at:string}>
 */
function petition_recent_signatures(int $limit = 12): array
{
    $limit = max(1, min(50, $limit));
    $stmt = petition_db()->prepare(
        'SELECT character_name, created_at
         FROM signatures
         ORDER BY created_at DESC, id DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return is_array($rows) ? $rows : [];
}

/**
 * Store email server-side for petition delivery/verification.
 * Uniqueness is enforced via email_hash. Realm is no longer collected (stored NULL).
 *
 * @return array{ok:bool,error?:string,code?:string,id?:int}
 */
function petition_create_signature(array $data, string $ip, string $userAgent): array
{
    $pdo = petition_db();
    $emailHash = petition_email_hash($data['email']);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO signatures
                (email, email_hash, character_name, realm, ip_hash, user_agent_hash, created_at)
             VALUES
                (:email, :email_hash, :character_name, NULL, :ip_hash, :ua_hash, UTC_TIMESTAMP())'
        );
        $stmt->execute([
            ':email' => $data['email'],
            ':email_hash' => $emailHash,
            ':character_name' => $data['character_name'],
            ':ip_hash' => petition_ip_hash($ip),
            ':ua_hash' => hash('sha256', (petition_config()['hash_pepper'] ?? '') . '|ua|' . $userAgent),
        ]);

        return ['ok' => true, 'id' => (int) $pdo->lastInsertId()];
    } catch (PDOException $e) {
        // 23000 = integrity constraint (duplicate email_hash)
        if ($e->getCode() === '23000') {
            return [
                'ok' => false,
                'code' => 'duplicate',
                'error' => 'This email has already signed the petition.',
            ];
        }
        // Do not log email addresses or request bodies.
        error_log('petition: insert failed (PDO ' . $e->getCode() . ')');
        return [
            'ok' => false,
            'code' => 'server',
            'error' => 'Could not save your signature. Please try again later.',
        ];
    }
}

function petition_format_relative_time(string $utcDatetime): string
{
    try {
        $then = new DateTimeImmutable($utcDatetime, new DateTimeZone('UTC'));
    } catch (Exception $e) {
        return $utcDatetime;
    }
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $diff = $now->getTimestamp() - $then->getTimestamp();
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $m = (int) floor($diff / 60);
        return $m . 'm ago';
    }
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);
        return $h . 'h ago';
    }
    $d = (int) floor($diff / 86400);
    return $d . 'd ago';
}
