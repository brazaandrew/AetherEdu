-- =====================================================
-- CORE SQL CHANGES SUMMARY - NewELMS System
-- =====================================================
-- Essential database modifications made during this session

-- 1. SUBJECTS TABLE - Add grade level support
ALTER TABLE `subjects` ADD COLUMN `grade_level` VARCHAR(50) DEFAULT NULL AFTER `description`;
ALTER TABLE `subjects` ADD INDEX `idx_grade_level` (`grade_level`);

-- 2. USERS TABLE - Add retention status tracking
ALTER TABLE `users` ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted';
ALTER TABLE `users` ADD COLUMN `retention_reason` TEXT DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `retention_updated_by` INT DEFAULT NULL;

-- 3. USERS TABLE - Add retention indexes and foreign key
ALTER TABLE `users` ADD INDEX `idx_retention_status` (`retention_status`);
ALTER TABLE `users` ADD INDEX `idx_retention_school_year` (`retention_school_year`);
ALTER TABLE `users` ADD INDEX `idx_retention_updated_by` (`retention_updated_by`);
ALTER TABLE `users` ADD CONSTRAINT `fk_users_retention_updated_by` FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 4. ENROLLMENTS TABLE - Create with retention status
CREATE TABLE IF NOT EXISTS `enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted',
    `retention_reason` TEXT DEFAULT NULL,
    `retention_school_year` VARCHAR(20) DEFAULT NULL,
    `retention_updated_at` TIMESTAMP NULL DEFAULT NULL,
    `retention_updated_by` INT DEFAULT NULL,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_subject_id` (`subject_id`),
    INDEX `idx_enrollment_retention_status` (`retention_status`),
    INDEX `idx_enrollment_retention_school_year` (`retention_school_year`),
    INDEX `idx_enrollment_retention_updated_by` (`retention_updated_by`),
    UNIQUE KEY `unique_student_subject_enrollment` (`student_id`, `subject_id`),
    CONSTRAINT `fk_enrollments_retention_updated_by` FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. STUDENT FEES - Update discount/grant types to store negative credit values
UPDATE student_fees sf
JOIN fee_types ft ON ft.id = sf.fee_type_id
SET sf.amount = -ABS(CASE WHEN sf.amount <> 0 THEN sf.amount ELSE sf.discount END),
    sf.balance = -ABS(CASE WHEN sf.amount <> 0 THEN sf.amount ELSE sf.discount END)
WHERE LOWER(ft.name) LIKE '%discount%'
   OR LOWER(ft.name) LIKE '%grant%'
   OR LOWER(ft.name) LIKE '%scholar%'
   OR LOWER(ft.name) LIKE '%voucher%';

-- 6. NOTIFICATIONS TABLE - Create for dynamic topbar notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) DEFAULT '#',
    `icon` VARCHAR(50) DEFAULT 'bi-info-circle',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;