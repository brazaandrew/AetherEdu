-- Migration: Add grade_level column to subjects table
-- This migration adds a grade_level column to store the grade level for each subject
-- Supports filtering subjects by grade level (Grade 7-12, SHS tracks)

-- Add grade_level column to subjects table
ALTER TABLE `subjects` 
ADD COLUMN `grade_level` VARCHAR(50) DEFAULT NULL AFTER `description`;

-- Add index for better query performance when filtering by grade level
ALTER TABLE `subjects` 
ADD INDEX `idx_grade_level` (`grade_level`);

-- Optional: Add index for combined filtering (grade_level + archived status) - disabled because subjects table has no archived column
-- ALTER TABLE `subjects` 
-- ADD INDEX `idx_grade_archived` (`grade_level`, `archived`);

-- Update existing subjects with sample grade levels (optional)
-- You can uncomment and modify these based on your existing data

-- UPDATE `subjects` SET `grade_level` = 'Grade 7' WHERE `code` LIKE '%7%' OR `name` LIKE '%Grade 7%';
-- UPDATE `subjects` SET `grade_level` = 'Grade 8' WHERE `code` LIKE '%8%' OR `name` LIKE '%Grade 8%';
-- UPDATE `subjects` SET `grade_level` = 'Grade 9' WHERE `code` LIKE '%9%' OR `name` LIKE '%Grade 9%';
-- UPDATE `subjects` SET `grade_level` = 'Grade 10' WHERE `code` LIKE '%10%' OR `name` LIKE '%Grade 10%';
-- UPDATE `subjects` SET `grade_level` = 'Grade 11' WHERE `code` LIKE '%11%' OR `name` LIKE '%Grade 11%';
-- UPDATE `subjects` SET `grade_level` = 'Grade 12' WHERE `code` LIKE '%12%' OR `name` LIKE '%Grade 12%';

-- Update DepEd subjects with specific grade levels
-- UPDATE `subjects` SET `grade_level` = 'Grade 7' WHERE `code` IN ('ENG7', 'FIL7', 'MATH7', 'SCI7', 'AP7', 'TLE7', 'MAPEH7', 'ESP7');
-- UPDATE `subjects` SET `grade_level` = 'Grade 8' WHERE `code` IN ('ENG8', 'FIL8', 'MATH8', 'SCI8', 'AP8', 'TLE8', 'MAPEH8', 'ESP8');
-- UPDATE `subjects` SET `grade_level` = 'Grade 9' WHERE `code` IN ('ENG9', 'FIL9', 'MATH9', 'SCI9', 'AP9', 'TLE9', 'MAPEH9', 'ESP9');
-- UPDATE `subjects` SET `grade_level` = 'Grade 10' WHERE `code` IN ('ENG10', 'FIL10', 'MATH10', 'SCI10', 'AP10', 'TLE10', 'MAPEH10', 'ESP10');

-- Update SHS Core subjects
-- UPDATE `subjects` SET `grade_level` = 'Core Subjects' WHERE `code` IN ('ORALCOM', 'KOMFIL', 'GENMATH', 'STAT', 'EARTHSCI', 'PHYSSCI', 'PEHM', 'UCSP', 'CONTEMP', 'MEDIA');

-- Update SHS Track subjects
-- UPDATE `subjects` SET `grade_level` = 'STEM Track' WHERE `code` IN ('PRECAL', 'BASICCAL', 'GENCHEM', 'GENPHY', 'GENBIO');
-- UPDATE `subjects` SET `grade_level` = 'ABM Track' WHERE `code` IN ('FUNACCO', 'BUSMATH', 'BUSFIN', 'ORGMGT', 'BUSMARK');
-- UPDATE `subjects` SET `grade_level` = 'HUMSS Track' WHERE `code` IN ('CREWRITE', 'CRENONFIC', 'PHILHIST', 'WORLDREL', 'TRENDS');
-- UPDATE `subjects` SET `grade_level` = 'TVL Track' WHERE `code` IN ('EMPTECH', 'ENTRE', 'INQUIRE');