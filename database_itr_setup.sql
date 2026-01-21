-- ITR (Inventory Transfer Request) Database Setup Script
-- USE pims;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing ITR tables if they exist
DROP TABLE IF EXISTS itr_items;
DROP TABLE IF EXISTS itr_forms;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create ITR Forms table
CREATE TABLE itr_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_name VARCHAR(255) NOT NULL,
    fund_cluster VARCHAR(100) NOT NULL,
    itr_no VARCHAR(50) UNIQUE NOT NULL,
    from_office VARCHAR(255) NOT NULL,
    to_office VARCHAR(255) NOT NULL,
    transfer_date DATE,
    transfer_type ENUM('Donation', 'Reassignment', 'Relocate', 'Others') DEFAULT 'Reassignment',
    transfer_type_others VARCHAR(100),
    end_user VARCHAR(100),
    purpose TEXT,
    requested_by VARCHAR(100) NOT NULL,
    requested_by_position VARCHAR(100) NOT NULL,
    requested_date DATE NOT NULL,
    approved_by VARCHAR(100) NOT NULL,
    approved_by_position VARCHAR(100) NOT NULL,
    approved_date DATE NOT NULL,
    released_by VARCHAR(100) NOT NULL,
    released_by_position VARCHAR(100) NOT NULL,
    released_date DATE NOT NULL,
    received_by VARCHAR(100) NOT NULL,
    received_by_position VARCHAR(100) NOT NULL,
    received_date DATE NOT NULL,
    status ENUM('draft', 'submitted', 'approved', 'released', 'received', 'cancelled') DEFAULT 'draft',
    total_amount DECIMAL(12,2) DEFAULT 0.00,
    created_by INT NOT NULL,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_itr_no (itr_no),
    INDEX idx_entity_name (entity_name),
    INDEX idx_from_office (from_office),
    INDEX idx_to_office (to_office),
    INDEX idx_status (status),
    INDEX idx_transfer_date (transfer_date)
);

-- Create ITR Items table
CREATE TABLE itr_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    item_no INT NOT NULL,
    date_acquired DATE,
    ics_par_no VARCHAR(100),
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit VARCHAR(50) DEFAULT 'pcs',
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    condition_of_inventory VARCHAR(50) DEFAULT 'serviceable',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES itr_forms(id) ON DELETE CASCADE,
    INDEX idx_form_id (form_id),
    INDEX idx_item_no (item_no),
    INDEX idx_description (description(255))
);

-- Create trigger to update total_amount when items change
DELIMITER //
CREATE TRIGGER itr_items_after_insert 
AFTER INSERT ON itr_items
FOR EACH ROW
BEGIN
    UPDATE itr_forms 
    SET total_amount = (
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM itr_items 
        WHERE form_id = NEW.form_id
    ),
    updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.form_id;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER itr_items_after_update 
AFTER UPDATE ON itr_items
FOR EACH ROW
BEGIN
    UPDATE itr_forms 
    SET total_amount = (
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM itr_items 
        WHERE form_id = NEW.form_id
    ),
    updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.form_id;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER itr_items_after_delete 
AFTER DELETE ON itr_items
FOR EACH ROW
BEGIN
    UPDATE itr_forms 
    SET total_amount = (
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM itr_items 
        WHERE form_id = OLD.form_id
    ),
    updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.form_id;
END//
DELIMITER ;

-- Create view for ITR summary
CREATE VIEW itr_summary AS
SELECT 
    itr.id,
    itr.entity_name,
    itr.fund_cluster,
    itr.itr_no,
    itr.from_office,
    itr.to_office,
    itr.transfer_date,
    itr.transfer_type,
    itr.end_user,
    itr.purpose,
    itr.status,
    itr.total_amount,
    COUNT(ii.id) as item_count,
    itr.created_by,
    u.first_name,
    u.last_name,
    itr.created_at,
    itr.updated_at
FROM itr_forms itr
LEFT JOIN itr_items ii ON itr.id = ii.form_id
LEFT JOIN users u ON itr.created_by = u.id
GROUP BY itr.id;
