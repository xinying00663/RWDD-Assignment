-- SQL script to add Role column to users table
-- Run this in phpMyAdmin to update your existing users table

USE ecogo;

-- Add Role column to users table
ALTER TABLE `users` 
ADD COLUMN `Role` ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER `Password`;

-- Update any existing users to have 'user' role by default
UPDATE `users` SET `Role` = 'user' WHERE `Role` IS NULL;

-- Optional: If you want to make a specific user an admin, run:
-- UPDATE `users` SET `Role` = 'admin' WHERE `Email` = 'admin@example.com';
