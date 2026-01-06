-- Asset Categories Table
CREATE TABLE IF NOT EXISTS asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    depreciation_rate DECIMAL(5,2) DEFAULT 0.00,
    useful_life_years INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_category_code (category_code),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default categories
INSERT INTO asset_categories (category_name, category_code, description, depreciation_rate, useful_life_years, created_by) VALUES
('Furniture & Fixtures', 'FF', 'Office furniture, chairs, desks, cabinets, and other fixtures', 10.00, 7, 1),
('Computer Equipment', 'CE', 'Desktop computers, laptops, servers, and related peripherals', 33.33, 3, 1),
('Vehicles', 'VH', 'Company vehicles, cars, trucks, and transportation equipment', 20.00, 5, 1),
('Machinery & Equipment', 'ME', 'Industrial machinery, production equipment, and tools', 15.00, 10, 1),
('Buildings & Improvements', 'BI', 'Buildings, structures, and leasehold improvements', 2.50, 40, 1),
('Land', 'LD', 'Land and land improvements (non-depreciable)', 0.00, 0, 1),
('Software', 'SW', 'Licensed software, applications, and digital assets', 33.33, 3, 1),
('Office Equipment', 'OE', 'Printers, scanners, phones, and general office equipment', 20.00, 5, 1);
