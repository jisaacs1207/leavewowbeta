-- Idempotent-ish upgrade for existing leavewowbeta DBs.
-- Prefer letting includes/schema.php apply this on request; use this file only if you run SQL by hand.

ALTER TABLE signatures
  ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER character_name;

ALTER TABLE signatures
  ADD KEY idx_signatures_public_created (is_public, created_at);
