-- Add 'it_personnel' role to users table
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin','teacher','student','it_personnel') NOT NULL;
