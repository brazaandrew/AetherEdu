-- =====================================================
-- SQL Script: Add Grade Level Support to Subjects
-- =====================================================
-- This script adds grade level functionality to the subjects table
-- Run this script to enable grade level filtering feature

-- Check if grade_level column exists before adding it
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
    'SELECT "Column grade_level already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for better performance (only if column was added)
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `subjects` ADD INDEX `idx_grade_level` (`grade_level`)',
    'SELECT "Index idx_grade_level already exists or column not found" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add combined index for grade_level and archived status
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE `subjects` ADD INDEX `idx_grade_archived` (`grade_level`, `archived`)',
    'SELECT "Index idx_grade_archived already exists or column not found" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Display current subjects table structure
DESCRIBE `subjects`;

-- Display message
SELECT 'Grade level column setup completed successfully!' as result;