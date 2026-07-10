-- Migration: Add auto-grading columns to quiz tables
-- Date: 2025-11-17
-- Description: Add columns to support auto-grading and manual grading workflow

-- Add columns to quiz_answers table
ALTER TABLE quiz_answers 
ADD COLUMN IF NOT EXISTS requires_manual_grading TINYINT(1) DEFAULT 0 COMMENT 'Flag for essay questions requiring manual grading',
ADD COLUMN IF NOT EXISTS manual_points_awarded INT DEFAULT NULL COMMENT 'Points awarded by teacher for essay questions',
CHANGE COLUMN student_answer answer_text TEXT COMMENT 'Student answer text';

-- Add columns to quiz_attempts table
ALTER TABLE quiz_attempts
ADD COLUMN IF NOT EXISTS auto_graded_score INT DEFAULT 0 COMMENT 'Score from auto-graded questions (MCQ, TF, ID)',
ADD COLUMN IF NOT EXISTS needs_manual_grading TINYINT(1) DEFAULT 0 COMMENT 'Flag indicating essay questions need manual grading',
ADD COLUMN IF NOT EXISTS graded_at DATETIME NULL COMMENT 'Timestamp when manual grading was completed';

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_quiz_answers_grading ON quiz_answers(attempt_id, requires_manual_grading);
CREATE INDEX IF NOT EXISTS idx_quiz_attempts_grading ON quiz_attempts(student_id, needs_manual_grading);
CREATE INDEX IF NOT EXISTS idx_grades_student_subject ON grades(student_id, subject_id);
