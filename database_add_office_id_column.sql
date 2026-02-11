-- Add office_id column to users table
-- This script adds the office_id foreign key column to the users table

USE pims;

-- Add office_id column to users table
ALTER TABLE users 
ADD COLUMN office_id INT DEFAULT NULL 
AFTER last_name;

-- Add foreign key constraint
ALTER TABLE users 
ADD CONSTRAINT fk_user_office 
FOREIGN KEY (office_id) REFERENCES offices(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Add index for better performance
CREATE INDEX idx_user_office_id ON users(office_id);

-- Update existing users to have a default office (optional)
-- UPDATE users SET office_id = 1 WHERE office_id IS NULL;
