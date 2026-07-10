-- Simple SQL to add grade_level column to subjects table
-- Run this in phpMyAdmin or your MySQL client

-- Add the grade_level column
ALTER TABLE `subjects` 
ADD COLUMN `grade_level` VARCHAR(50) DEFAULT NULL AFTER `description`;

-- Add index for better performance
ALTER TABLE `subjects` 
ADD INDEX `idx_grade_level` (`grade_level`);

-- Optional: Add combined index
ALTER TABLE `subjects` 
ADD INDEX `idx_grade_archived` (`grade_level`, `archived`);

-- Verify the changes
SHOW COLUMNS FROM `subjects`;