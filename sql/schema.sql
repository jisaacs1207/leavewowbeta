-- leavewowbeta.live petition schema (MySQL 5.7+ / MariaDB 10.3+)
-- Run once in Plesk phpMyAdmin or: mysql -u USER -p DB_NAME < sql/schema.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS signatures (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    email_hash CHAR(64) NOT NULL,
    character_name VARCHAR(32) NOT NULL,
    realm VARCHAR(64) NULL DEFAULT NULL,
    ip_hash CHAR(64) NULL DEFAULT NULL,
    user_agent_hash CHAR(64) NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_signatures_email_hash (email_hash),
    KEY idx_signatures_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: store hashed email only in production by clearing email after verification.
-- This schema keeps email for petition delivery/verification as stated in the privacy note.
