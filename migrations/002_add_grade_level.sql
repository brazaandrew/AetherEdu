-- Add grade_level column to users table for students
ALTER TABLE users ADD COLUMN grade_level VARCHAR(20) NULL AFTER role;

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_grade_level ON users(grade_level);