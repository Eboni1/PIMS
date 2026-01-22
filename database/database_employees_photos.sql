-- Add profile photo column to employees table
ALTER TABLE employees 
ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER phone;

-- Create uploads directory for employee photos if it doesn't exist
-- Note: This needs to be created manually on the server: uploads/employees/

-- Update existing employees to have default photos (optional)
-- UPDATE employees SET profile_photo = 'default-avatar.png' WHERE profile_photo IS NULL;
