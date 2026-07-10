-- Add enrollment_key column to subjects table if it doesn't exist
-- Run this on InfinityFree if the enrollment_key column is missing

ALTER TABLE `subjects` 
ADD COLUMN `enrollment_key` VARCHAR(10) DEFAULT NULL AFTER `description`,
ADD INDEX `idx_enrollment_key` (`enrollment_key`);
