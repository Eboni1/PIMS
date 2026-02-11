<?php
// Migration script to add office_id column to users table
require_once 'config.php';

echo "Starting migration: Adding office_id column to users table...\n";

try {
    // Check if column already exists
    $check_stmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'pims' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'office_id'");
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "office_id column already exists in users table. Skipping migration.\n";
        exit;
    }
    
    // Add office_id column
    $alter_sql = "ALTER TABLE users ADD COLUMN office_id INT DEFAULT NULL AFTER last_name";
    if ($conn->query($alter_sql)) {
        echo "✓ office_id column added successfully\n";
    } else {
        echo "✗ Failed to add office_id column: " . $conn->error . "\n";
        exit;
    }
    
    // Add foreign key constraint
    $fk_sql = "ALTER TABLE users ADD CONSTRAINT fk_user_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL ON UPDATE CASCADE";
    if ($conn->query($fk_sql)) {
        echo "✓ Foreign key constraint added successfully\n";
    } else {
        echo "✗ Failed to add foreign key constraint: " . $conn->error . "\n";
        exit;
    }
    
    // Add index for better performance
    $index_sql = "CREATE INDEX idx_user_office_id ON users(office_id)";
    if ($conn->query($index_sql)) {
        echo "✓ Index added successfully\n";
    } else {
        echo "✗ Failed to add index: " . $conn->error . "\n";
        exit;
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

$conn->close();
?>
