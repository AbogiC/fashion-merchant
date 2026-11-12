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

-- ----------------------------------------------------------------------------
-- Public site content
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS collections (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    season     VARCHAR(60)  NOT NULL DEFAULT '',
    pieces     INT UNSIGNED NOT NULL DEFAULT 0,
    monogram   VARCHAR(8)   NULL,
    image_url  VARCHAR(500) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_collections_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guidelines (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number      VARCHAR(10)  NOT NULL DEFAULT '',
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_guidelines_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS looks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label       VARCHAR(50)  NOT NULL DEFAULT '',
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    pieces      VARCHAR(255) NOT NULL DEFAULT '',
    monogram    VARCHAR(8)   NULL,
    image_url   VARCHAR(500) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_looks_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(160) NOT NULL,
    title        VARCHAR(200) NOT NULL,
    excerpt      TEXT NOT NULL,
    body         LONGTEXT NULL,
    image_url    VARCHAR(500) NULL,
    published_at DATE NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_news_slug (slug),
    KEY idx_news_active (is_active, published_at, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_settings (
    id                         TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    hero_eyebrow               VARCHAR(100) NOT NULL DEFAULT '',
    hero_title_primary         VARCHAR(120) NOT NULL DEFAULT '',
    hero_title_secondary       VARCHAR(120) NOT NULL DEFAULT '',
    hero_description           TEXT NOT NULL,
    hero_cta_label             VARCHAR(60)  NOT NULL DEFAULT '',
    hero_cta_url               VARCHAR(255) NOT NULL DEFAULT '/collection',
    philosophy_subtitle        VARCHAR(100) NOT NULL DEFAULT '',
    philosophy_title_primary   VARCHAR(120) NOT NULL DEFAULT '',
    philosophy_title_secondary VARCHAR(120) NOT NULL DEFAULT '',
    philosophy_body            TEXT NOT NULL,
    philosophy_cta_label       VARCHAR(60)  NOT NULL DEFAULT '',
    philosophy_cta_url         VARCHAR(255) NOT NULL DEFAULT '/about',
    philosophy_monogram        VARCHAR(8)   NOT NULL DEFAULT 'M',
    updated_at                 DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_featured (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tag        VARCHAR(80)  NOT NULL DEFAULT '',
    title      VARCHAR(150) NOT NULL,
    copy       VARCHAR(255) NOT NULL DEFAULT '',
    monogram   VARCHAR(8)   NULL,
    image_url  VARCHAR(500) NULL,
    link_url   VARCHAR(255) NOT NULL DEFAULT '/collection',
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_home_featured_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_stats (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    value      VARCHAR(20)  NOT NULL,
    suffix     VARCHAR(20)  NOT NULL DEFAULT '',
    label      VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_home_stats_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS home_marquee (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word       VARCHAR(120) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_home_marquee_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
