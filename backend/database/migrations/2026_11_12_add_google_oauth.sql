-- ----------------------------------------------------------------------------
-- Add Google OAuth support to the users table.
--
-- Existing installations should run this once:
--   mysql -u root -p fashion_merchant < backend/database/migrations/2026_11_12_add_google_oauth.sql
--
-- Fresh installations only need backend/database/schema.sql (already updated).
-- ----------------------------------------------------------------------------

USE fashion_merchant;

ALTER TABLE users
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN google_id  VARCHAR(191) NULL AFTER password_hash,
    ADD COLUMN avatar_url VARCHAR(500) NULL AFTER google_id,
    ADD UNIQUE KEY uq_users_google_id (google_id);
