-- Simple SQL to add retention status to enrollments table
-- Run this in phpMyAdmin or your MySQL client

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

-- Add retention status columns
ALTER TABLE `enrollments` 
ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted' AFTER `enrolled_at`,
ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`;

-- Add indexes for performance
ALTER TABLE `enrollments` 
ADD INDEX `idx_retention_status` (`retention_status`),
ADD INDEX `idx_retention_school_year` (`retention_school_year`),
ADD INDEX `idx_retention_updated_by` (`retention_updated_by`);

-- Add foreign key constraint
ALTER TABLE `enrollments` 
ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Verify the changes
SHOW COLUMNS FROM `enrollments`;