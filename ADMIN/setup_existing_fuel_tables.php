<?php
require_once '../config.php';

echo "<h2>Fuel Database Setup (Existing Tables)</h2>";

// Create fuel_types table
$create_fuel_types = "
CREATE TABLE IF NOT EXISTS `fuel_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_fuel_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create fuel_stock table
$create_fuel_stock = "
CREATE TABLE IF NOT EXISTS `fuel_stock` (
    `fuel_type_id` int(11) NOT NULL,
    `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`fuel_type_id`),
    FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create fuel_in table
$create_fuel_in = "
CREATE TABLE IF NOT EXISTS `fuel_in` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fuel_type` int(11) NOT NULL,
    `quantity` decimal(10,2) NOT NULL,
    `unit_price` decimal(10,2) DEFAULT NULL,
    `total_cost` decimal(10,2) DEFAULT NULL,
    `storage_location` varchar(100) DEFAULT NULL,
    `delivery_receipt` varchar(50) DEFAULT NULL,
    `supplier_name` varchar(100) DEFAULT NULL,
    `received_by` varchar(100) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`fuel_type`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE,
    KEY `idx_fuel_in_date` (`date_time`),
    KEY `idx_fuel_in_type` (`fuel_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create fuel_out table
$create_fuel_out = "
CREATE TABLE IF NOT EXISTS `fuel_out` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `fo_date` date NOT NULL,
    `fo_time_in` time NOT NULL,
    `fo_fuel_no` varchar(20) DEFAULT NULL,
    `fo_plate_no` varchar(20) DEFAULT NULL,
    `fo_request` varchar(200) DEFAULT NULL,
    `fo_fuel_type` int(11) NOT NULL,
    `fo_liters` decimal(10,2) NOT NULL,
    `fo_vehicle_type` varchar(50) DEFAULT NULL,
    `fo_receiver` varchar(100) DEFAULT NULL,
    `fo_time_out` time DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`fo_fuel_type`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE,
    KEY `idx_fuel_out_date` (`fo_date`),
    KEY `idx_fuel_out_type` (`fo_fuel_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Execute table creation
$tables = [
    'fuel_types' => $create_fuel_types,
    'fuel_stock' => $create_fuel_stock,
    'fuel_in' => $create_fuel_in,
    'fuel_out' => $create_fuel_out
];

$success_count = 0;
$error_count = 0;

foreach ($tables as $table_name => $sql) {
    try {
        $result = $conn->query($sql);
        if ($result) {
            $success_count++;
            echo "<div style='color: green;'>✓ Table '$table_name' created successfully</div>";
        } else {
            $error_count++;
            echo "<div style='color: red;'>✗ Error creating table '$table_name': " . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<div style='color: red;'>✗ Exception creating table '$table_name': " . $e->getMessage() . "</div>";
    }
}

// Insert sample fuel types if tables are empty
if ($success_count >= 4) {
    echo "<h3>Inserting Sample Data:</h3>";
    
    // Check if fuel_types is empty
    $check_fuel_types = $conn->query("SELECT COUNT(*) as count FROM fuel_types");
    $fuel_types_count = $check_fuel_types->fetch_assoc()['count'];
    
    if ($fuel_types_count == 0) {
        $sample_fuel_types = [
            ['Diesel', 1],
            ['Gasoline', 1],
            ['Premium', 1]
        ];
        
        foreach ($sample_fuel_types as $fuel_type) {
            $sql = "INSERT INTO fuel_types (name, is_active) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $fuel_type[0], $fuel_type[1]);
            
            if ($stmt->execute()) {
                echo "<div style='color: green;'>✓ Added fuel type: {$fuel_type[0]}</div>";
            } else {
                echo "<div style='color: red;'>✗ Error adding fuel type {$fuel_type[0]}: " . $stmt->error . "</div>";
            }
        }
    } else {
        echo "<div style='color: blue;'>ℹ Fuel types already exist ($fuel_types_count records)</div>";
    }
    
    // Insert sample stock data
    $check_fuel_stock = $conn->query("SELECT COUNT(*) as count FROM fuel_stock");
    $stock_count = $check_fuel_stock->fetch_assoc()['count'];
    
    if ($stock_count == 0) {
        // Get fuel type IDs
        $fuel_types_result = $conn->query("SELECT id, name FROM fuel_types ORDER BY name");
        
        while ($fuel_type = $fuel_types_result->fetch_assoc()) {
            $quantity = rand(500, 2000); // Random quantity between 500-2000 liters
            $sql = "INSERT INTO fuel_stock (fuel_type_id, quantity, created_by) VALUES (?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('id', $fuel_type['id'], $quantity);
            
            if ($stmt->execute()) {
                echo "<div style='color: green;'>✓ Added stock for {$fuel_type['name']}: {$quantity} L</div>";
            } else {
                echo "<div style='color: red;'>✗ Error adding stock for {$fuel_type['name']}: " . $stmt->error . "</div>";
            }
        }
    } else {
        echo "<div style='color: blue;'>ℹ Fuel stock already has data ($stock_count records)</div>";
    }
    
    // Clear existing fuel_in and fuel_out data to avoid conflicts
    $conn->query("DELETE FROM fuel_in");
    $conn->query("DELETE FROM fuel_out");
    
    // Insert sample fuel_in transactions
    $fuel_types_result = $conn->query("SELECT id, name FROM fuel_types ORDER BY name");
    
    while ($fuel_type = $fuel_types_result->fetch_assoc()) {
        $quantity = rand(100, 500);
        $unit_price = rand(40, 60); // Random price between 40-60 per liter
        $total_cost = $quantity * $unit_price;
        $supplier_name = 'Sample Supplier';
        $storage_location = 'Main Tank';
        $delivery_receipt = 'DR-' . rand(1000, 9999);
        $received_by = 'Admin User';
        $remarks = 'Sample fuel delivery transaction';
        $created_by = 1;
        
        $sql = "INSERT INTO fuel_in (fuel_type, quantity, unit_price, total_cost, storage_location, delivery_receipt, supplier_name, received_by, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('idddsssssi', $fuel_type['id'], $quantity, $unit_price, $total_cost, $storage_location, $delivery_receipt, $supplier_name, $received_by, $remarks, $created_by);
        
        if ($stmt->execute()) {
            echo "<div style='color: green;'>✓ Added fuel in transaction for {$fuel_type['name']}: {$quantity} L</div>";
        } else {
            echo "<div style='color: red;'>✗ Error adding fuel in for {$fuel_type['name']}: " . $stmt->error . "</div>";
        }
    }
    
    // Insert sample fuel_out transactions
    $fuel_types_result = $conn->query("SELECT id, name FROM fuel_types ORDER BY name");
    
    while ($fuel_type = $fuel_types_result->fetch_assoc()) {
        $quantity = rand(20, 100);
        $fo_fuel_no = 'F001';
        $fo_plate_no = 'ABC-123';
        $fo_request = 'Office Travel';
        $fo_vehicle_type = 'Sedan';
        $fo_receiver = 'John Doe';
        $created_by = 1;
        
        $sql = "INSERT INTO fuel_out (fo_date, fo_time_in, fo_fuel_no, fo_plate_no, fo_request, fo_fuel_type, fo_liters, fo_vehicle_type, fo_receiver, created_by) VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssidssi', $fo_fuel_no, $fo_plate_no, $fo_request, $fuel_type['id'], $quantity, $fo_vehicle_type, $fo_receiver, $created_by);
        
        if ($stmt->execute()) {
            echo "<div style='color: green;'>✓ Added fuel out transaction for {$fuel_type['name']}: {$quantity} L</div>";
        } else {
            echo "<div style='color: red;'>✗ Error adding fuel out for {$fuel_type['name']}: " . $stmt->error . "</div>";
        }
    }
}

// Verification
echo "<h3>Verification:</h3>";
$tables_to_check = ['fuel_types', 'fuel_stock', 'fuel_in', 'fuel_out'];
foreach ($tables_to_check as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    echo "<div style='color: green;'>✓ Table '$table' exists with $count records</div>";
}

echo "<hr>";
if ($success_count >= 4) {
    echo "<div style='color: green; font-size: 18px;'><strong>✓ Fuel database setup completed successfully!</strong></div>";
    echo "<p>You can now access the <a href='fuel.php'>Fuel Inventory</a> page.</p>";
} else {
    echo "<div style='color: red; font-size: 18px;'><strong>⚠ Setup completed with errors</strong></div>";
}

echo "<p><a href='fuel.php'>Go to Fuel Inventory</a> | <a href='dashboard.php'>Back to Dashboard</a></p>";
?>
