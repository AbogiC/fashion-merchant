-- ----------------------------------------------------------------------------
-- Fashion Merchant – authentication schema
-- Run with:  mysql -u root -p < backend/database/schema.sql
-- (or import this file through phpMyAdmin / your MySQL client)
-- ----------------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS fashion_merchant
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fashion_merchant;

CREATE TABLE IF NOT EXISTS users (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                  VARCHAR(100)    NOT NULL,
    email                 VARCHAR(255)    NOT NULL,
    password_hash         VARCHAR(255)    NULL,
    google_id             VARCHAR(191)    NULL,
    avatar_url            VARCHAR(500)    NULL,
    role                  ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active             TINYINT(1)      NOT NULL DEFAULT 1,
    failed_login_attempts INT UNSIGNED    NOT NULL DEFAULT 0,
    locked_until          DATETIME        NULL,
    last_login_at         DATETIME        NULL,
    created_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_google_id (google_id),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
