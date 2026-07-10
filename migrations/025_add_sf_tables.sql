-- Migration 025: School Forms (SF9 / SF10) Support Tables
-- Run once on both TLCA and SSIS databases

-- Extend settings table with school information keys
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('school_name',      'The Light Christian Academy'),
    ('school_id',        ''),
    ('school_division',  ''),
    ('school_region',    'Region IV-A (CALABARZON)'),
    ('school_address',   ''),
    ('school_year',      '2024-2025'),
    ('school_head_name', ''),
    ('registrar_name',   ''),
    ('school_logo',      'assets/images/school-logo.png');

-- Quarterly behavioral ratings per student per school year
CREATE TABLE IF NOT EXISTS student_behavior (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    school_year  VARCHAR(20) NOT NULL DEFAULT '2024-2025',
    quarter      TINYINT NOT NULL COMMENT '1-4',
    -- Core Values (O=Outstanding, VS=Very Satisfactory, S=Satisfactory, F=Fairly Satisfactory, D=Did Not Meet)
    maka_diyos       VARCHAR(5) DEFAULT NULL,
    makatao          VARCHAR(5) DEFAULT NULL,
    makakalikasan    VARCHAR(5) DEFAULT NULL,
    makabansa        VARCHAR(5) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_behavior (student_id, school_year, quarter),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log for generated school forms (SF9/SF10)
CREATE TABLE IF NOT EXISTS generated_school_forms (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    student_id        INT NOT NULL,
    form_type         ENUM('SF9','SF10') NOT NULL,
    school_year       VARCHAR(20),
    generated_by      INT NOT NULL,
    generated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address        VARCHAR(45),
    verification_code VARCHAR(64) UNIQUE,
    FOREIGN KEY (student_id)   REFERENCES users(id),
    FOREIGN KEY (generated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
