-- Create fuel_in table for fuel incoming transactions
CREATE TABLE IF NOT EXISTS `fuel_in` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fuel_type` varchar(50) NOT NULL,
    `quantity` decimal(10,2) NOT NULL,
    `unit_price` decimal(10,2) DEFAULT 0.00,
    `total_cost` decimal(10,2) DEFAULT 0.00,
    `storage_location` varchar(100) DEFAULT NULL,
    `delivery_receipt` varchar(100) DEFAULT NULL,
    `supplier_name` varchar(100) DEFAULT NULL,
    `received_by` int(11) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_fuel_in_date` (`date_time`),
    KEY `idx_fuel_in_type` (`fuel_type`),
    KEY `idx_fuel_in_created` (`created_by`),
    CONSTRAINT `fk_fuel_in_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fuel_in_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Output completion message
SELECT 'fuel_in table created successfully!' as message;
