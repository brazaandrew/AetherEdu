-- =====================================================
-- SQL Script: Add Retention Status to Enrollments
-- =====================================================
-- This script adds academic retention status tracking to the enrollments table
-- Includes Promoted, Retained, and Irregular status with audit trail

-- Check if enrollments table exists
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'enrollments'
);

-- Create enrollments table if it doesn't exist
SET @sql = IF(
    @table_exists = 0,
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

-- Check if retention_status column already exists
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'enrollments' 
    AND COLUMN_NAME = 'retention_status'
);

-- Add retention status columns only if they don't exist
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `enrollments` 
     ADD COLUMN `retention_status` ENUM(\'Promoted\', \'Retained\', \'Irregular\') DEFAULT \'Promoted\' AFTER `enrolled_at`,
     ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
     ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
     ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
     ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`',
    'SELECT "Retention status columns already exist" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for better performance (only if columns were added)
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `enrollments` 
     ADD INDEX `idx_retention_status` (`retention_status`),
     ADD INDEX `idx_retention_school_year` (`retention_school_year`),
     ADD INDEX `idx_retention_updated_by` (`retention_updated_by`)',
    'SELECT "Indexes already exist or columns not found" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint (only if columns were added)
SET @sql = IF(
    @column_exists = 0 AND @table_exists > 0,
    'ALTER TABLE `enrollments` 
     ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
     FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Foreign key already exists or not needed" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Display current enrollments table structure
DESCRIBE `enrollments`;

-- Display success message
SELECT 'Enrollment retention status setup completed successfully!' as result,
       'Added: retention_status, retention_reason, retention_school_year, retention_updated_at, retention_updated_by' as fields_added;