-- Add tab protection column to quizzes table
ALTER TABLE quizzes ADD COLUMN tab_protection_enabled TINYINT(1) DEFAULT 0;