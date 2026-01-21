-- Update employees table to include missing fields
-- Run this script to add employee_no, employment_status, and clearance_status fields

ALTER TABLE employees 
ADD COLUMN employee_no VARCHAR(20) UNIQUE AFTER id,
ADD COLUMN employment_status ENUM('permanent', 'contractual', 'job_order', 'resigned', 'retired') DEFAULT 'permanent' AFTER position,
ADD COLUMN clearance_status ENUM('cleared', 'uncleared') DEFAULT 'uncleared' AFTER employment_status;

-- Add indexes for better performance
CREATE INDEX idx_employees_employment_status ON employees(employment_status);
CREATE INDEX idx_employees_clearance_status ON employees(clearance_status);
CREATE INDEX idx_employees_employee_no ON employees(employee_no);

-- Update existing employees to have employee numbers (optional)
UPDATE employees SET employee_no = CONCAT('EMP', LPAD(id, 4, '0')) WHERE employee_no IS NULL;
