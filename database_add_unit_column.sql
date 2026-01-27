-- Add unit column to asset_items table
-- Run this script to update existing database

USE pims;

-- Add unit column to asset_items table
ALTER TABLE asset_items 
ADD COLUMN unit VARCHAR(50) AFTER quantity;

-- Log the change
SELECT 'unit column added to asset_items table successfully' as message;
