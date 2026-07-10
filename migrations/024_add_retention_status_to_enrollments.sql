-- Migration: Add retention status fields to enrollments table
-- This migration adds academic status tracking to student enrollments
-- Supports Promoted, Retained, and Irregular status tracking

-- Add retention status columns to enrollments table
ALTER TABLE `enrollments` 
ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted' AFTER `enrolled_at`,
ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`;

-- Add indexes for better query performance
ALTER TABLE `enrollments` 
ADD INDEX `idx_retention_status` (`retention_status`),
ADD INDEX `idx_retention_school_year` (`retention_school_year`),
ADD INDEX `idx_retention_updated_by` (`retention_updated_by`);

-- Add foreign key constraint for retention_updated_by (references users table)
ALTER TABLE `enrollments` 
ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add comment to table for documentation
ALTER TABLE `enrollments` COMMENT = 'Student enrollment records with academic retention status tracking';