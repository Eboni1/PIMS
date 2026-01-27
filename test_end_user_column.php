<?php
require_once 'config.php';

// Check if end_user column exists in asset_items table
echo "Checking if end_user column exists in asset_items table...\n";

$result = $conn->query("DESCRIBE asset_items");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

if (in_array('end_user', $columns)) {
    echo "✓ end_user column EXISTS in asset_items table\n";
} else {
    echo "✗ end_user column MISSING in asset_items table\n";
    echo "Available columns: " . implode(', ', $columns) . "\n";
}

// Test a sample query
echo "\nTesting sample asset item data...\n";
$test_result = $conn->query("SELECT id, description, employee_id, end_user FROM asset_items LIMIT 3");
if ($test_result) {
    while ($row = $test_result->fetch_assoc()) {
        echo "ID: {$row['id']}, Description: {$row['description']}, Employee ID: {$row['employee_id']}, End User: " . ($row['end_user'] ?? 'NULL') . "\n";
    }
} else {
    echo "Error querying asset_items: " . $conn->error . "\n";
}

$conn->close();
?>
