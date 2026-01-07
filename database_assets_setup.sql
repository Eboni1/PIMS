-- PIMS Assets and Consumables Database Setup
-- Create tables for asset and consumable management

USE pims;

-- Asset Categories Table
CREATE TABLE IF NOT EXISTS asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Assets Table (Asset Types/Categories)
CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_categories_id INT,
    description VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    office_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_categories_id) REFERENCES asset_categories(id),
    FOREIGN KEY (office_id) REFERENCES offices(id)
);

-- Asset Items Table (Individual Asset Items)
CREATE TABLE IF NOT EXISTS asset_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    status ENUM('available', 'in_use', 'maintenance', 'disposed') DEFAULT 'available',
    value DECIMAL(10,2) DEFAULT 0.00,
    acquisition_date DATE,
    office_id INT,
    employee_id INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (office_id) REFERENCES offices(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Consumables Table
CREATE TABLE IF NOT EXISTS consumables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    reorder_level INT DEFAULT 10,
    office_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES offices(id)
);

-- Employees Table (if not exists)
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    office_id INT,
    position VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES offices(id)
);

-- Offices Table (if not exists)
CREATE TABLE IF NOT EXISTS offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(100) NOT NULL,
    office_code VARCHAR(20) UNIQUE,
    address TEXT,
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Philippines',
    phone VARCHAR(20),
    email VARCHAR(100),
    capacity INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default asset categories
INSERT INTO asset_categories (code, name, description) VALUES
('IT', 'Information Technology', 'Computers, laptops, servers, and IT equipment'),
('FURN', 'Furniture', 'Office furniture including desks, chairs, and cabinets'),
('VEH', 'Vehicles', 'Company vehicles and transportation equipment'),
('MACH', 'Machinery', 'Heavy machinery and equipment'),
('TOOLS', 'Tools', 'Hand tools and power tools'),
('OTHER', 'Other Assets', 'Miscellaneous assets not categorized elsewhere');

-- Insert default offices
INSERT INTO offices (office_name, office_code, address, state, phone, email) VALUES
('Head Office', 'HO', 'Main Building, City Hall', 'Albay', '052-123-4567', 'headoffice@lgu.gov.ph'),
('North District', 'ND', 'North District Office', 'Albay', '052-123-4568', 'north@lgu.gov.ph'),
('South District', 'SD', 'South District Office', 'Albay', '052-123-4569', 'south@lgu.gov.ph'),
('East District', 'ED', 'East District Office', 'Albay', '052-123-4570', 'east@lgu.gov.ph'),
('West District', 'WD', 'West District Office', 'Albay', '052-123-4571', 'west@lgu.gov.ph');

-- Insert sample assets
INSERT INTO assets (asset_categories_id, description, quantity, unit_cost, office_id) VALUES
(1, 'Laptop Computers', 10, 35000.00, 1),
(1, 'Desktop Computers', 15, 25000.00, 1),
(2, 'Office Chairs', 20, 3500.00, 1),
(2, 'Office Desks', 15, 8500.00, 1),
(3, 'Service Vehicle', 2, 850000.00, 1);

-- Insert sample asset items
INSERT INTO asset_items (asset_id, description, quantity, status, value, acquisition_date, office_id) VALUES
(1, 'Dell Latitude 5420', 1, 'available', 35000.00, '2024-01-15', 1),
(1, 'Dell Latitude 5420', 1, 'in_use', 35000.00, '2024-01-15', 1),
(1, 'HP EliteBook 840', 1, 'available', 38000.00, '2024-02-20', 1),
(2, 'Dell OptiPlex 7090', 1, 'in_use', 25000.00, '2024-01-10', 1),
(3, 'Ergonomic Office Chair', 1, 'available', 3500.00, '2024-03-01', 1);

-- Insert sample consumables
INSERT INTO consumables (description, quantity, unit_cost, reorder_level, office_id) VALUES
('Bond Paper A4', 50, 150.00, 20, 1),
('Ink Cartridges', 25, 850.00, 10, 1),
('Ballpoint Pens', 100, 15.00, 50, 1),
('Folders', 75, 25.00, 30, 1),
('Marker Pens', 30, 45.00, 15, 1);

-- Insert sample employees
INSERT INTO employees (firstname, lastname, email, phone, office_id, position) VALUES
('Juan', 'Dela Cruz', 'juan.cruz@lgu.gov.ph', '0912-345-6789', 1, 'Office Manager'),
('Maria', 'Santos', 'maria.santos@lgu.gov.ph', '0912-345-6790', 1, 'Administrative Assistant'),
('Jose', 'Reyes', 'jose.reyes@lgu.gov.ph', '0912-345-6791', 1, 'IT Support');
