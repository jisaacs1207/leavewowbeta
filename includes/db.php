<?php
declare(strict_types=1);

function petition_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = petition_config()['db'];
    $host = $cfg['host'];
    // On many Linux hosts, "localhost" uses a UNIX socket; prefer explicit TCP
    // when a port is set, or fall back to common Plesk/MariaDB socket paths.
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $dsn = sprintf(
            'mysql:host=127.0.0.1;port=%d;dbname=%s;charset=utf8mb4',
            $cfg['port'],
            $cfg['name']
        );
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $cfg['port'],
            $cfg['name']
        );
    }

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        $socketCandidates = [
            env('DB_SOCKET', null),
            '/var/run/mysqld/mysqld.sock',
            '/run/mysqld/mysqld.sock',
            '/tmp/mysql.sock',
        ];
        $last = $e;
        foreach ($socketCandidates as $socket) {
            if ($socket === null || $socket === '' || !file_exists($socket)) {
                continue;
            }
            try {
                $pdo = new PDO(
                    sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $socket, $cfg['name']),
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                return $pdo;
            } catch (PDOException $inner) {
                $last = $inner;
            }
        }
        throw $last;
    }

    return $pdo;
}
