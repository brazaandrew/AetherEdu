-- Migration: Add auto-grading columns to quiz tables
-- Date: 2025-11-18

-- Add columns to quiz_attempts if they don't exist
ALTER TABLE quiz_attempts 
ADD COLUMN IF NOT EXISTS auto_graded_score INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS needs_manual_grading TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS graded_at DATETIME NULL;

-- Add columns to quiz_answers if they don't exist
ALTER TABLE quiz_answers 
ADD COLUMN IF NOT EXISTS requires_manual_grading TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS manual_points_awarded INT DEFAULT NULL;
