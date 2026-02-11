<?php
// Database setup script to add office_id column to users table
require_once '../config.php';

echo "<h2>PIMS Database Setup - Add Office ID Column</h2>";

try {
    echo "<h3>Checking if office_id column already exists...</h3>";
    
    // Check if column already exists
    $check_stmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'pims' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'office_id'");
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<span style='color: blue;'>ℹ office_id column already exists in users table. Skipping migration.</span><br>";
    } else {
        echo "<h3>Adding office_id column to users table...</h3>";
        
        // Add office_id column
        $alter_sql = "ALTER TABLE users ADD COLUMN office_id INT DEFAULT NULL AFTER last_name";
        if ($conn->query($alter_sql)) {
            echo "<span style='color: green;'>✓ office_id column added successfully</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Failed to add office_id column: " . $conn->error . "</span><br>";
            throw new Exception("Failed to add office_id column");
        }
        
        // Add foreign key constraint
        echo "<h3>Adding foreign key constraint...</h3>";
        $fk_sql = "ALTER TABLE users ADD CONSTRAINT fk_user_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL ON UPDATE CASCADE";
        if ($conn->query($fk_sql)) {
            echo "<span style='color: green;'>✓ Foreign key constraint added successfully</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠ Failed to add foreign key constraint (may already exist): " . $conn->error . "</span><br>";
        }
        
        // Add index for better performance
        echo "<h3>Adding index for office_id...</h3>";
        $index_sql = "CREATE INDEX idx_user_office_id ON users(office_id)";
        if ($conn->query($index_sql)) {
            echo "<span style='color: green;'>✓ Index added successfully</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠ Failed to add index (may already exist): " . $conn->error . "</span><br>";
        }
    }
    
    echo "<h3>Verifying office_id column...</h3>";
    
    // Check if office_id column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'office_id'");
    if ($result->num_rows > 0) {
        echo "<span style='color: green;'>✓ Column 'office_id' exists in users table</span><br>";
        
        // Show column details
        $column = $result->fetch_assoc();
        echo "  &nbsp; Type: " . htmlspecialchars($column['Type']) . "<br>";
        echo "  &nbsp; Null: " . htmlspecialchars($column['Null']) . "<br>";
        echo "  &nbsp; Default: " . htmlspecialchars($column['Default']) . "<br>";
        
        // Show available offices
        echo "<h4>Available Offices:</h4>";
        $offices = $conn->query("SELECT id, office_name, office_code FROM offices WHERE status = 'active' ORDER BY office_name");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Office Name</th><th>Office Code</th></tr>";
        while ($row = $offices->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" . htmlspecialchars($row['office_name']) . "</td><td>" . htmlspecialchars($row['office_code']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<span style='color: red;'>✗ Column 'office_id' not found in users table</span><br>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='../SYSTEM_ADMIN/user_management.php'>Go to User Management</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Setup Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3, h4 {
    color: #333;
}
p {
    background: white;
    padding: 10px;
    border-radius: 5px;
    margin: 5px 0;
}
table {
    margin: 10px 0;
}
th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
th {
    background: #f2f2f2;
    font-weight: bold;
}
</style>
