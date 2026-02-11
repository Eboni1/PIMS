-- Add office column to users table
-- This script adds an office field to the users table

USE pims;

ALTER TABLE users 
ADD COLUMN `office` VARCHAR(100) DEFAULT NULL AFTER `address`;

-- Add index for better performance if office will be used in queries
ALTER TABLE users 
ADD INDEX `idx_office` (`office`);

-- Update existing users to have a default office value (optional)
-- UPDATE users SET office = 'Main Office' WHERE office IS NULL;
