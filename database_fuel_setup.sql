-- Fuel Management System Database Setup
-- This script creates the necessary tables for the fuel inventory management system

-- Create fuel_transactions table
CREATE TABLE IF NOT EXISTS `fuel_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `transaction_type` enum('IN','OUT') NOT NULL,
    `fuel_type` enum('diesel','gasoline','premium') NOT NULL,
    `quantity` decimal(10,2) NOT NULL,
    `transaction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `source` varchar(100) DEFAULT NULL COMMENT 'For IN transactions: Delivery, Transfer, Refill, etc.',
    `supplier` varchar(100) DEFAULT NULL COMMENT 'For IN transactions: Supplier name',
    `employee_id` int(11) DEFAULT NULL COMMENT 'For OUT transactions: Employee who received fuel',
    `recipient_name` varchar(100) DEFAULT NULL COMMENT 'For OUT transactions: If no employee selected',
    `purpose` varchar(200) DEFAULT NULL COMMENT 'For OUT transactions: Purpose of fuel usage',
    `vehicle_equipment` varchar(100) DEFAULT NULL COMMENT 'For OUT transactions: Vehicle or equipment ID',
    `odometer_reading` int(11) DEFAULT NULL COMMENT 'For OUT transactions: Current odometer reading',
    `odometer_unit` varchar(10) DEFAULT 'km' COMMENT 'For OUT transactions: km or miles',
    `tank_number` varchar(50) DEFAULT NULL COMMENT 'Tank involved in transaction',
    `user_id` int(11) NOT NULL COMMENT 'User who recorded the transaction',
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_transaction_date` (`transaction_date`),
    KEY `idx_fuel_type` (`fuel_type`),
    KEY `idx_transaction_type` (`transaction_type`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_tank_number` (`tank_number`),
    CONSTRAINT `fk_fuel_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fuel_transactions_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create fuel_inventory table
CREATE TABLE IF NOT EXISTS `fuel_inventory` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tank_number` varchar(50) NOT NULL,
    `fuel_type` enum('diesel','gasoline','premium') NOT NULL,
    `capacity` decimal(10,2) NOT NULL COMMENT 'Tank capacity in liters',
    `current_level` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Current fuel level in liters',
    `location` varchar(100) DEFAULT NULL COMMENT 'Physical location of tank',
    `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
    `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_by` int(11) DEFAULT NULL COMMENT 'User who last updated the tank',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tank_number` (`tank_number`),
    KEY `idx_fuel_type` (`fuel_type`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_fuel_inventory_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample fuel tanks
INSERT IGNORE INTO `fuel_inventory` (`tank_number`, `fuel_type`, `capacity`, `current_level`, `location`, `updated_by`) VALUES
('TANK-D001', 'diesel', 5000.00, 3500.00, 'Main Station - Diesel Tank 1', 1),
('TANK-D002', 'diesel', 3000.00, 2100.00, 'Main Station - Diesel Tank 2', 1),
('TANK-G001', 'gasoline', 2000.00, 1500.00, 'Main Station - Gasoline Tank 1', 1),
('TANK-G002', 'gasoline', 1500.00, 800.00, 'Main Station - Gasoline Tank 2', 1),
('TANK-P001', 'premium', 1000.00, 750.00, 'Main Station - Premium Tank', 1);

-- Insert sample fuel transactions
INSERT IGNORE INTO `fuel_transactions` 
    (`transaction_type`, `fuel_type`, `quantity`, `transaction_date`, `source`, `supplier`, `employee_id`, `purpose`, `vehicle_equipment`, `tank_number`, `user_id`, `notes`) VALUES
-- Fuel IN transactions
('IN', 'diesel', 2000.00, '2024-01-15 09:00:00', 'Delivery', 'Petron Corporation', NULL, NULL, NULL, 'TANK-D001', 1, 'Regular diesel delivery'),
('IN', 'gasoline', 1500.00, '2024-01-16 10:30:00', 'Delivery', 'Shell Philippines', NULL, NULL, NULL, 'TANK-G001', 1, 'Premium gasoline delivery'),
('IN', 'premium', 500.00, '2024-01-17 14:00:00', 'Transfer', 'Main Station', NULL, NULL, NULL, 'TANK-P001', 1, 'Transfer from reserve tank'),
('IN', 'diesel', 1000.00, '2024-01-18 08:15:00', 'Delivery', 'Caltex Philippines', NULL, NULL, NULL, 'TANK-D002', 1, 'Emergency diesel delivery'),
('IN', 'gasoline', 800.00, '2024-01-19 11:45:00', 'Refill', 'Mobile Tanker', NULL, NULL, NULL, 'TANK-G002', 1, 'Refill from mobile tanker'),

-- Fuel OUT transactions
('OUT', 'diesel', 50.00, '2024-01-15 14:30:00', NULL, NULL, 1, 'Field Operations', 'VEH-001', 'TANK-D001', 1, 'Vehicle refueling for field work'),
('OUT', 'gasoline', 30.00, '2024-01-15 15:45:00', NULL, NULL, 2, 'Office Travel', 'VEH-002', 'TANK-G001', 1, 'Office vehicle refueling'),
('OUT', 'premium', 25.00, '2024-01-16 09:15:00', NULL, NULL, 3, 'Executive Travel', 'VEH-003', 'TANK-P001', 1, 'Executive vehicle refueling'),
('OUT', 'diesel', 75.00, '2024-01-16 10:30:00', NULL, NULL, 4, 'Equipment Operation', 'EQP-001', 'TANK-D001', 1, 'Heavy equipment refueling'),
('OUT', 'gasoline', 40.00, '2024-01-17 08:00:00', NULL, NULL, 5, 'Maintenance', 'VEH-004', 'TANK-G001', 1, 'Maintenance vehicle refueling');

-- Create triggers for automatic inventory updates
DELIMITER //

-- Trigger to update fuel inventory when fuel IN transaction is added
CREATE TRIGGER IF NOT EXISTS `tr_fuel_in_inventory_update`
AFTER INSERT ON `fuel_transactions`
FOR EACH ROW
BEGIN
    IF NEW.transaction_type = 'IN' AND NEW.tank_number IS NOT NULL THEN
        UPDATE fuel_inventory 
        SET current_level = current_level + NEW.quantity,
            last_updated = NOW()
        WHERE tank_number = NEW.tank_number;
    END IF;
END//

-- Trigger to update fuel inventory when fuel OUT transaction is added
CREATE TRIGGER IF NOT EXISTS `tr_fuel_out_inventory_update`
AFTER INSERT ON `fuel_transactions`
FOR EACH ROW
BEGIN
    IF NEW.transaction_type = 'OUT' AND NEW.tank_number IS NOT NULL THEN
        UPDATE fuel_inventory 
        SET current_level = current_level - NEW.quantity,
            last_updated = NOW()
        WHERE tank_number = NEW.tank_number AND current_level >= NEW.quantity;
    END IF;
END//

-- Trigger to reverse inventory update when fuel transaction is deleted
CREATE TRIGGER IF NOT EXISTS `tr_fuel_transaction_delete_reverse`
AFTER DELETE ON `fuel_transactions`
FOR EACH ROW
BEGIN
    IF OLD.tank_number IS NOT NULL THEN
        IF OLD.transaction_type = 'IN' THEN
            -- Reverse the fuel IN (subtract from inventory)
            UPDATE fuel_inventory 
            SET current_level = current_level - OLD.quantity,
                last_updated = NOW()
            WHERE tank_number = OLD.tank_number;
        ELSEIF OLD.transaction_type = 'OUT' THEN
            -- Reverse the fuel OUT (add back to inventory)
            UPDATE fuel_inventory 
            SET current_level = current_level + OLD.quantity,
                last_updated = NOW()
            WHERE tank_number = OLD.tank_number;
        END IF;
    END IF;
END//

DELIMITER ;

-- Create view for fuel summary
CREATE OR REPLACE VIEW `v_fuel_summary` AS
SELECT 
    fuel_type,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) as total_fuel_in,
    SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as total_fuel_out,
    SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) - 
    SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as net_change,
    MIN(transaction_date) as first_transaction,
    MAX(transaction_date) as last_transaction
FROM fuel_transactions
GROUP BY fuel_type;

-- Create view for tank status
CREATE OR REPLACE VIEW `v_tank_status` AS
SELECT 
    fi.id,
    fi.tank_number,
    fi.fuel_type,
    fi.capacity,
    fi.current_level,
    ROUND((fi.current_level / fi.capacity) * 100, 2) as fill_percentage,
    CASE 
        WHEN (fi.current_level / fi.capacity) * 100 >= 75 THEN 'Full'
        WHEN (fi.current_level / fi.capacity) * 100 >= 25 THEN 'Normal'
        ELSE 'Low'
    END as status_level,
    fi.location,
    fi.status,
    fi.last_updated,
    CASE 
        WHEN fi.status = 'active' THEN 'Available'
        WHEN fi.status = 'inactive' THEN 'Not Available'
        WHEN fi.status = 'maintenance' THEN 'Under Maintenance'
    END as availability_status
FROM fuel_inventory fi
WHERE fi.status != 'inactive';

-- Create view for daily fuel usage
CREATE OR REPLACE VIEW `v_daily_fuel_usage` AS
SELECT 
    DATE(transaction_date) as usage_date,
    fuel_type,
    SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) as fuel_in,
    SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as fuel_out,
    COUNT(*) as transaction_count
FROM fuel_transactions
GROUP BY DATE(transaction_date), fuel_type
ORDER BY usage_date DESC, fuel_type;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_fuel_transactions_date_range` ON `fuel_transactions` (`transaction_date`, `fuel_type`);
CREATE INDEX IF NOT EXISTS `idx_fuel_inventory_type_status` ON `fuel_inventory` (`fuel_type`, `status`);

-- Output setup completion message
SELECT 'Fuel Management System database setup completed successfully!' as message;
