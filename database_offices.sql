-- Offices Table
CREATE TABLE IF NOT EXISTS offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(100) NOT NULL UNIQUE,
    office_code VARCHAR(10) NOT NULL UNIQUE,
    address TEXT,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Philippines',
    phone VARCHAR(50),
    email VARCHAR(100),
    capacity INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_office_code (office_code),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default offices
INSERT INTO offices (office_name, office_code, address, state, postal_code, phone, email, capacity, created_by) VALUES
('Head Office', 'HO', '123 Municipal Hall, Makati Avenue', 'Metro Manila', '1200', '+63-2-8888-0001', 'headoffice@pims.com', 100, 1),
('North District', 'ND', '456 North Avenue, Quezon City', 'Metro Manila', '1100', '+63-2-8888-0002', 'north@pims.com', 50, 1),
('South District', 'SD', '789 South Expressway, Alabang', 'Metro Manila', '1770', '+63-2-8888-0003', 'south@pims.com', 40, 1),
('East District', 'ED', '321 East Road, Pasig City', 'Metro Manila', '1600', '+63-2-8888-0004', 'east@pims.com', 35, 1),
('West District', 'WD', '654 West Boulevard, Manila', 'Metro Manila', '1000', '+63-2-8888-0005', 'west@pims.com', 45, 1);
