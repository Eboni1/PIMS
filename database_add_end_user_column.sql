-- Add end_user column to asset_items table
-- Run this script to update existing database

USE pims;

-- Add end_user column to asset_items table
ALTER TABLE asset_items 
ADD COLUMN end_user VARCHAR(255) AFTER employee_id;

-- Log the change
SELECT 'end_user column added to asset_items table successfully' as message;
