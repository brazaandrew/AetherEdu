-- =====================================================
-- SIMPLE DATABASE UPDATES - NewELMS System
-- =====================================================
-- Manual execution version - Run each section as needed

-- =====================================================
-- 1. ADD GRADE LEVEL TO SUBJECTS TABLE
-- =====================================================

-- Add grade_level column to subjects table
ALTER TABLE `subjects` 
ADD COLUMN `grade_level` VARCHAR(50) DEFAULT NULL AFTER `description`;

-- Add index for better performance
ALTER TABLE `subjects` 
ADD INDEX `idx_grade_level` (`grade_level`);

-- =====================================================
-- 2. ADD RETENTION STATUS TO USERS TABLE
-- =====================================================

-- Add retention status columns to users table
ALTER TABLE `users` 
ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted',
ADD COLUMN `retention_reason` TEXT DEFAULT NULL,
ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL,
ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN `retention_updated_by` INT DEFAULT NULL;

-- Add indexes for users retention status
ALTER TABLE `users` 
ADD INDEX `idx_retention_status` (`retention_status`),
ADD INDEX `idx_retention_school_year` (`retention_school_year`),
ADD INDEX `idx_retention_updated_by` (`retention_updated_by`);

-- Add foreign key constraint for users
ALTER TABLE `users` 
ADD CONSTRAINT `fk_users_retention_updated_by` 
FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- 3. CREATE/UPDATE ENROLLMENTS TABLE
-- =====================================================

-- Create enrollments table if it doesn't exist
CREATE TABLE IF NOT EXISTS `enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_subject_id` (`subject_id`),
    UNIQUE KEY `unique_student_subject_enrollment` (`student_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add retention status columns to enrollments table
ALTER TABLE `enrollments` 
ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted' AFTER `enrolled_at`,
ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`;

-- Add indexes for enrollments retention status
ALTER TABLE `enrollments` 
ADD INDEX `idx_enrollment_retention_status` (`retention_status`),
ADD INDEX `idx_enrollment_retention_school_year` (`retention_school_year`),
ADD INDEX `idx_enrollment_retention_updated_by` (`retention_updated_by`);

-- Add foreign key constraint for enrollments
ALTER TABLE `enrollments` 
ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- 4. VERIFICATION QUERIES
-- =====================================================

-- Verify subjects table
SHOW COLUMNS FROM `subjects`;

-- Verify users retention columns
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME LIKE '%retention%';

-- Verify enrollments table
SHOW COLUMNS FROM `enrollments`;

-- Show all new indexes
SHOW INDEX FROM `subjects` WHERE Key_name LIKE '%grade%';
SHOW INDEX FROM `users` WHERE Key_name LIKE '%retention%';
SHOW INDEX FROM `enrollments` WHERE Key_name LIKE '%retention%';