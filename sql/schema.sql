-- ============================================================
-- Staj Takip ve Destek Uygulaması - Veritabanı Şeması (MVP)
-- MariaDB 10.4+ / MySQL 8.0+
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS staj_app
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE staj_app;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- users: öğretmen + öğrenci (işletme hesabı yoktur)
-- ----------------------------------------------------------
CREATE TABLE users (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email                VARCHAR(255) NOT NULL UNIQUE,
    name                 VARCHAR(255) NOT NULL,
    role                 ENUM('teacher','student') NOT NULL,
    password_hash        VARCHAR(255) NOT NULL,
    is_minor             TINYINT(1) DEFAULT 0,           -- 18 yaş altı mı?
    parent_consent_given TINYINT(1) DEFAULT 0,            -- veli onayı (reşit değilse zorunlu)
    parent_consent_at    TIMESTAMP NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- companies (işletme). Hesap yok, sadece staj ilişkisinde.
-- ----------------------------------------------------------
CREATE TABLE companies (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- internships: öğrenci <-> işletme <-> öğretmen bağlantısı
-- ----------------------------------------------------------
CREATE TABLE internships (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    company_id      INT UNSIGNED NOT NULL,
    teacher_id      INT UNSIGNED NOT NULL,
    term            VARCHAR(100) NOT NULL,        -- örn: 2024 Güz
    started_at      DATE NULL,
    ended_at        DATE NULL,
        status          ENUM('pending_company','active','completed','cancelled')
                     DEFAULT 'pending_company',
    company_confirmed TINYINT(1) DEFAULT 0,
    teacher_comment  TEXT NULL,          -- öğretmen yorumu
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE RESTRICT,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_intern_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- daily_records: günlük yoklama ("bugün stajdayım")
-- ----------------------------------------------------------
CREATE TABLE daily_records (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    internship_id INT UNSIGNED NOT NULL,
    date        DATE NOT NULL,
    present     TINYINT(1) DEFAULT 1,             -- 1 = stajda, 0 = yok
    location    VARCHAR(255) NULL,                -- konum (isteğe bağlı)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    UNIQUE KEY uq_daily (internship_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- journal: staj defteri (günlük not + isteğe bağlı fotoğraf)
-- ----------------------------------------------------------
CREATE TABLE journal (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    internship_id INT UNSIGNED NOT NULL,
    date        DATE NOT NULL,
    entry       TEXT NULL,
    photo_url   VARCHAR(500) NULL,                -- public/storage/uploads/...
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
        UNIQUE KEY uq_journal (internship_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- company_confirmations: süreli, tek amaçlı onay linki
-- ----------------------------------------------------------
CREATE TABLE company_confirmations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    internship_id INT UNSIGNED NOT NULL,
    token       VARCHAR(128) NOT NULL UNIQUE,
    expires_at  DATETIME NOT NULL,
    confirmed   TINYINT(1) DEFAULT 0,
    confirmed_at TIMESTAMP NULL,
    evaluation  TEXT NULL,                        -- JSON: 3-4 soruluk puanları
    feedback    TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- issues: sorun bildirme
-- ----------------------------------------------------------
CREATE TABLE issues (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    internship_id INT UNSIGNED NOT NULL,
    reported_by   INT UNSIGNED NOT NULL,          -- öğrenci user_id
    category      ENUM('other_duty','safety','behavior','other') NOT NULL,
    description   TEXT NOT NULL,
    anonymous     TINYINT(1) DEFAULT 0,
    status        ENUM('new','in_progress','resolved') DEFAULT 'new',
    resolved_at   TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- faq_items: sık sorulan sorular (statik, düzenlenebilir)
-- ----------------------------------------------------------
CREATE TABLE faq_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question    VARCHAR(255) NOT NULL,
    answer      TEXT NOT NULL,
    sort_order  INT DEFAULT 0,
    active      TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- checklist_items: staj kontrol listesi
-- ----------------------------------------------------------
CREATE TABLE checklist_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order  INT DEFAULT 0,
    active      TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- consents: KVKK / veli onayı kayıtları
-- ----------------------------------------------------------
CREATE TABLE consents (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    consent_type ENUM('kvkk','parent') NOT NULL,
    given       TINYINT(1) DEFAULT 0,
    given_at    TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_consent (user_id, consent_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
