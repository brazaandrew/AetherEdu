-- Add librarian role to the users table role enum
-- First, we need to modify the role column to include 'librarian'

-- Check current role values and add librarian if not exists
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian') NOT NULL DEFAULT 'student';
