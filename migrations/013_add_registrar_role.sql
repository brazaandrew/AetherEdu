-- Add 'registrar' role to users table
-- Registrar can view student lists and add/manage student accounts

ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin','teacher','student','it_personnel','registrar') NOT NULL;
