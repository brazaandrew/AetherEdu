-- Add nurse role to users table
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian', 'cashier', 'nurse') 
NOT NULL DEFAULT 'student';
