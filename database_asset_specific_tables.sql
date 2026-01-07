-- Asset Category Specific Tables
-- These tables store additional information specific to each asset category

-- Vehicles Additional Information Table
CREATE TABLE IF NOT EXISTS asset_vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    plate_number VARCHAR(20) NOT NULL,
    engine_number VARCHAR(50),
    chassis_number VARCHAR(50),
    color VARCHAR(30),
    model VARCHAR(50),
    brand VARCHAR(50),
    year_manufactured INT,
    fuel_type ENUM('gasoline', 'diesel', 'electric', 'hybrid', 'lpg') DEFAULT 'gasoline',
    transmission_type ENUM('manual', 'automatic', 'cvt') DEFAULT 'manual',
    registration_date DATE,
    registration_expiry DATE,
    insurance_provider VARCHAR(100),
    insurance_policy_number VARCHAR(50),
    insurance_expiry DATE,
    odometer_reading INT DEFAULT 0,
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_plate_number (plate_number),
    INDEX idx_registration_expiry (registration_expiry),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Computer Equipment Additional Information Table
CREATE TABLE IF NOT EXISTS asset_computers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    processor VARCHAR(100),
    ram_capacity VARCHAR(20),
    storage_type ENUM('hdd', 'ssd', 'hybrid') DEFAULT 'hdd',
    storage_capacity VARCHAR(20),
    graphics_card VARCHAR(100),
    operating_system VARCHAR(100),
    mac_address VARCHAR(17),
    ip_address VARCHAR(15),
    serial_number VARCHAR(50),
    warranty_provider VARCHAR(100),
    warranty_expiry DATE,
    purchase_date DATE,
    last_service_date DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    assigned_to VARCHAR(100),
    department VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_serial_number (serial_number),
    INDEX idx_mac_address (mac_address),
    INDEX idx_warranty_expiry (warranty_expiry),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Furniture & Fixtures Additional Information Table
CREATE TABLE IF NOT EXISTS asset_furniture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    furniture_type ENUM('desk', 'chair', 'cabinet', 'shelf', 'table', 'sofa', 'bed', 'other') DEFAULT 'other',
    material ENUM('wood', 'metal', 'plastic', 'glass', 'leather', 'fabric', 'composite') DEFAULT 'wood',
    color VARCHAR(30),
    dimensions VARCHAR(50),
    weight_capacity INT,
    manufacturer VARCHAR(100),
    model_number VARCHAR(50),
    purchase_date DATE,
    warranty_expiry DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    location_building VARCHAR(100),
    location_floor VARCHAR(20),
    location_room VARCHAR(50),
    assembly_required BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_furniture_type (furniture_type),
    INDEX idx_location (location_building, location_floor),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Machinery & Equipment Additional Information Table
CREATE TABLE IF NOT EXISTS asset_machinery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    machine_type VARCHAR(100),
    manufacturer VARCHAR(100),
    model_number VARCHAR(50),
    serial_number VARCHAR(50),
    capacity VARCHAR(50),
    power_requirements VARCHAR(100),
    voltage INT,
    operating_weight DECIMAL(10,2),
    dimensions VARCHAR(50),
    installation_date DATE,
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    maintenance_interval_days INT DEFAULT 90,
    operator_required BOOLEAN DEFAULT TRUE,
    safety_certification VARCHAR(100),
    certification_expiry DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    location_building VARCHAR(100),
    location_area VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_serial_number (serial_number),
    INDEX idx_next_maintenance (next_maintenance_date),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Buildings & Improvements Additional Information Table
CREATE TABLE IF NOT EXISTS asset_buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    building_type ENUM('office', 'warehouse', 'factory', 'residential', 'commercial', 'other') DEFAULT 'other',
    address TEXT NOT NULL,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Philippines',
    total_floor_area DECIMAL(10,2),
    number_of_floors INT,
    year_built INT,
    year_renovated INT,
    construction_type ENUM('concrete', 'wood', 'steel', 'mixed') DEFAULT 'concrete',
    roof_type VARCHAR(50),
    electrical_capacity VARCHAR(50),
    water_supply ENUM('municipal', 'well', 'mixed') DEFAULT 'municipal',
    sewage_system ENUM('municipal', 'septic_tank', 'mixed') DEFAULT 'municipal',
    fire_safety_system BOOLEAN DEFAULT FALSE,
    security_system BOOLEAN DEFAULT FALSE,
    air_conditioning BOOLEAN DEFAULT FALSE,
    elevator_count INT DEFAULT 0,
    parking_spaces INT DEFAULT 0,
    property_tax_number VARCHAR(50),
    land_title_number VARCHAR(50),
    zoning_classification VARCHAR(100),
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_building_type (building_type),
    INDEX idx_address (city, state),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Land Additional Information Table
CREATE TABLE IF NOT EXISTS asset_land (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    land_type ENUM('commercial', 'residential', 'agricultural', 'industrial', 'mixed') DEFAULT 'commercial',
    address TEXT NOT NULL,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Philippines',
    lot_area DECIMAL(12,2),
    frontage DECIMAL(10,2),
    depth DECIMAL(10,2),
    shape ENUM('regular', 'irregular') DEFAULT 'regular',
    topography ENUM('flat', 'sloping', 'hilly', 'mountainous') DEFAULT 'flat',
    zoning_classification VARCHAR(100),
    land_classification VARCHAR(100),
    tax_declaration_number VARCHAR(50),
    land_title_number VARCHAR(50),
    survey_number VARCHAR(50),
    corner_lot BOOLEAN DEFAULT FALSE,
    road_access ENUM('paved', 'gravel', 'dirt', 'none') DEFAULT 'paved',
    utilities_available ENUM('full', 'partial', 'none') DEFAULT 'partial',
    flood_prone BOOLEAN DEFAULT FALSE,
    encumbrances TEXT,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_land_type (land_type),
    INDEX idx_address (city, state),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Software Additional Information Table
CREATE TABLE IF NOT EXISTS asset_software (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    software_name VARCHAR(100) NOT NULL,
    version VARCHAR(50),
    license_type ENUM('perpetual', 'subscription', 'open_source', 'freemium') DEFAULT 'perpetual',
    license_key VARCHAR(200),
    number_of_licenses INT DEFAULT 1,
    platform ENUM('windows', 'mac', 'linux', 'web', 'mobile', 'multi_platform') DEFAULT 'windows',
    installation_date DATE,
    license_expiry DATE,
    renewal_cost DECIMAL(10,2),
    vendor VARCHAR(100),
    support_contact VARCHAR(100),
    activation_method ENUM('key', 'online', 'usb_dongle', 'account') DEFAULT 'key',
    server_based BOOLEAN DEFAULT FALSE,
    concurrent_users INT,
    hardware_requirements TEXT,
    installation_path VARCHAR(200),
    assigned_department VARCHAR(100),
    condition_status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_software_name (software_name),
    INDEX idx_license_expiry (license_expiry),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Office Equipment Additional Information Table
CREATE TABLE IF NOT EXISTS asset_office_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL UNIQUE,
    equipment_type ENUM('printer', 'scanner', 'photocopier', 'fax', 'telephone', 'projector', 'shredder', 'other') DEFAULT 'other',
    brand VARCHAR(50),
    model VARCHAR(50),
    serial_number VARCHAR(50),
    connectivity ENUM('usb', 'network', 'wireless', 'bluetooth', 'multi') DEFAULT 'usb',
    network_ip VARCHAR(15),
    functions TEXT,
    paper_size VARCHAR(20),
    print_speed_ppm INT,
    scan_resolution VARCHAR(20),
    color_capability BOOLEAN DEFAULT FALSE,
    power_consumption VARCHAR(20),
    warranty_provider VARCHAR(100),
    warranty_expiry DATE,
    last_service_date DATE,
    next_service_date DATE,
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    location_building VARCHAR(100),
    location_floor VARCHAR(20),
    location_room VARCHAR(50),
    assigned_to VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_asset_id (asset_id),
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_serial_number (serial_number),
    INDEX idx_warranty_expiry (warranty_expiry),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create a view to easily identify which table to use for each asset category
CREATE OR REPLACE VIEW asset_category_tables AS
SELECT 
    ac.id as category_id,
    ac.category_name,
    ac.category_code,
    CASE ac.category_code
        WHEN 'FF' THEN 'asset_furniture'
        WHEN 'CE' THEN 'asset_computers'
        WHEN 'VH' THEN 'asset_vehicles'
        WHEN 'ME' THEN 'asset_machinery'
        WHEN 'BI' THEN 'asset_buildings'
        WHEN 'LD' THEN 'asset_land'
        WHEN 'SW' THEN 'asset_software'
        WHEN 'OE' THEN 'asset_office_equipment'
        ELSE NULL
    END as specific_table_name
FROM asset_categories ac
WHERE ac.status = 'active';
