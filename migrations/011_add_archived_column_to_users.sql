-- Add archived column to users table
ALTER TABLE users ADD COLUMN archived TINYINT(1) DEFAULT 0;