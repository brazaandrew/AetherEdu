-- Migration: Add quarterly grades columns
-- Date: 2025-12-28
-- Description: Add Q1, Q2, Q3, Q4 grade columns and average calculation

-- Add quarterly grade columns to grades table
ALTER TABLE grades 
ADD COLUMN q1_grade DECIMAL(5,2) DEFAULT NULL AFTER computed_at,
ADD COLUMN q2_grade DECIMAL(5,2) DEFAULT NULL AFTER q1_grade,
ADD COLUMN q3_grade DECIMAL(5,2) DEFAULT NULL AFTER q2_grade,
ADD COLUMN q4_grade DECIMAL(5,2) DEFAULT NULL AFTER q3_grade,
ADD COLUMN average_grade DECIMAL(5,2) DEFAULT NULL AFTER q4_grade,
ADD COLUMN updated_at DATETIME DEFAULT NULL;

-- Add index for faster lookups
ALTER TABLE grades 
ADD INDEX idx_quarterly_grades (student_id, subject_id);
