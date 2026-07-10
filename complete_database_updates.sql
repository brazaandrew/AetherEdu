-- =====================================================
-- COMPLETE DATABASE UPDATES - NewELMS System
-- =====================================================
-- This script contains all database changes made during the session
-- Run this to update your database with all new features

-- =====================================================
-- 1. SUBJECTS TABLE - Add Grade Level Column
-- =====================================================

-- Check if grade_level column exists in subjects table
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'subjects' 
    AND COLUMN_NAME = 'grade_level'
);

-- Add grade_level column only if it doesn't exist
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `subjects` ADD COLUMN `grade_level` VARCHAR(50) DEFAULT NULL AFTER `description`',
    'SELECT "Column grade_level already exists in subjects" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for subjects grade_level (only if column was added)
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `subjects` ADD INDEX `idx_grade_level` (`grade_level`)',
    'SELECT "Index idx_grade_level already exists or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 2. USERS TABLE - Add Retention Status Columns
-- =====================================================

-- Check if retention_status column exists in users table
SET @retention_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'retention_status'
);

-- Add retention status columns to users table (only if they don't exist)
SET @sql = IF(
    @retention_exists = 0,
    'ALTER TABLE `users` 
     ADD COLUMN `retention_status` ENUM(\'Promoted\', \'Retained\', \'Irregular\') DEFAULT \'Promoted\',
     ADD COLUMN `retention_reason` TEXT DEFAULT NULL,
     ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL,
     ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL,
     ADD COLUMN `retention_updated_by` INT DEFAULT NULL',
    'SELECT "Retention status columns already exist in users table" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for users retention status (only if columns were added)
SET @sql = IF(
    @retention_exists = 0,
    'ALTER TABLE `users` 
     ADD INDEX `idx_retention_status` (`retention_status`),
     ADD INDEX `idx_retention_school_year` (`retention_school_year`),
     ADD INDEX `idx_retention_updated_by` (`retention_updated_by`)',
    'SELECT "Retention indexes already exist or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint for users retention_updated_by (only if columns were added)
SET @sql = IF(
    @retention_exists = 0,
    'ALTER TABLE `users` 
     ADD CONSTRAINT `fk_users_retention_updated_by` 
     FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Users foreign key already exists or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 3. ENROLLMENTS TABLE - Create and Add Retention Status
-- =====================================================

-- Check if enrollments table exists
SET @enrollments_table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'enrollments'
);

-- Create enrollments table if it doesn't exist
SET @sql = IF(
    @enrollments_table_exists = 0,
    'CREATE TABLE IF NOT EXISTS `enrollments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `subject_id` INT NOT NULL,
        `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_student_id` (`student_id`),
        INDEX `idx_subject_id` (`subject_id`),
        UNIQUE KEY `unique_student_subject_enrollment` (`student_id`, `subject_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'SELECT "Enrollments table already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if retention_status column exists in enrollments table
SET @enrollment_retention_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'enrollments' 
    AND COLUMN_NAME = 'retention_status'
);

-- Add retention status columns to enrollments table (only if they don't exist)
SET @sql = IF(
    @enrollment_retention_exists = 0 AND @enrollments_table_exists > 0,
    'ALTER TABLE `enrollments` 
     ADD COLUMN `retention_status` ENUM(\'Promoted\', \'Retained\', \'Irregular\') DEFAULT \'Promoted\' AFTER `enrolled_at`,
     ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
     ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
     ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
     ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`',
    'SELECT "Enrollment retention columns already exist or table not found" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for enrollments retention status (only if columns were added)
SET @sql = IF(
    @enrollment_retention_exists = 0 AND @enrollments_table_exists > 0,
    'ALTER TABLE `enrollments` 
     ADD INDEX `idx_enrollment_retention_status` (`retention_status`),
     ADD INDEX `idx_enrollment_retention_school_year` (`retention_school_year`),
     ADD INDEX `idx_enrollment_retention_updated_by` (`retention_updated_by`)',
    'SELECT "Enrollment retention indexes already exist or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint for enrollments retention_updated_by (only if columns were added)
SET @sql = IF(
    @enrollment_retention_exists = 0 AND @enrollments_table_exists > 0,
    'ALTER TABLE `enrollments` 
     ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
     FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Enrollments foreign key already exists or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 4. VERIFY ALL CHANGES
-- =====================================================

-- Show subjects table structure
SELECT 'SUBJECTS TABLE STRUCTURE:' as info;
DESCRIBE `subjects`;

-- Show users table structure (retention columns)
SELECT 'USERS TABLE RETENTION COLUMNS:' as info;
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME LIKE '%retention%'
ORDER BY ORDINAL_POSITION;

-- Show enrollments table structure
SELECT 'ENROLLMENTS TABLE STRUCTURE:' as info;
DESCRIBE `enrollments`;

-- Show all indexes created
SELECT 'INDEXES CREATED:' as info;
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
AND (
    INDEX_NAME LIKE '%grade_level%' OR 
    INDEX_NAME LIKE '%retention%' OR
    INDEX_NAME LIKE '%enrollment%'
)
ORDER BY TABLE_NAME, INDEX_NAME;

-- Show foreign key constraints
SELECT 'FOREIGN KEY CONSTRAINTS:' as info;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND CONSTRAINT_NAME LIKE '%retention%';

-- Final success message
SELECT '✅ ALL DATABASE UPDATES COMPLETED SUCCESSFULLY!' as result,
       'Features added: Grade Level Filter, Retention Status System, Enrollment Management' as summary;