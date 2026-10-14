<?php
declare(strict_types=1);

/**
 * Lightweight idempotent schema ensures for production deploys
 * (Plesk has no convenient mysql CLI via REST for one-off ALTERs).
 */
function petition_ensure_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        $pdo = petition_db();
        $stmt = $pdo->query("SHOW COLUMNS FROM signatures LIKE 'is_public'");
        $col = $stmt ? $stmt->fetch() : false;
        if ($col === false || $col === null) {
            $pdo->exec(
                'ALTER TABLE signatures
                 ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER character_name'
            );
        }

        // Helpful for public recent-name queries; ignore if it already exists.
        try {
            $pdo->exec(
                'ALTER TABLE signatures
                 ADD KEY idx_signatures_public_created (is_public, created_at)'
            );
        } catch (PDOException $e) {
            // Duplicate key name / already exists.
        }
    } catch (Throwable $e) {
        error_log('petition: schema ensure failed: ' . $e->getMessage());
    }
}
