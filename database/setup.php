<?php
// Database setup script for PIMS system tables
require_once '../config.php';

echo "<h2>PIMS Database Setup</h2>";

try {
    // Read SQL file
    $sqlFile = __DIR__ . '/create_system_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Only extract system_settings table creation and data
    $systemSettingsSQL = '';
    $lines = explode("\n", $sql);
    $inSystemSettings = false;
    $systemSettingsStart = false;
    
    foreach ($lines as $line) {
        if (strpos($line, '-- Insert default system settings') !== false) {
            $systemSettingsStart = true;
        }
        
        if ($systemSettingsStart) {
            $systemSettingsSQL .= $line . "\n";
        }
        
        if ($systemSettingsStart && trim($line) === ';' && strpos($line, 'system_settings') !== false) {
            $systemSettingsStart = false;
            break;
        }
    }
    
    echo "<h3>Adding missing system_settings table...</h3>";
    
    // Execute only system settings SQL
    if (!empty($systemSettingsSQL)) {
        $statements = array_filter(array_map('trim', explode(';', $systemSettingsSQL)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                echo "<p>Executing: " . substr($statement, 0, 50) . "...</p>";
                
                try {
                    $result = $conn->query($statement);
                    if ($result) {
                        echo "<span style='color: green;'>✓ Success</span><br>";
                    } else {
                        echo "<span style='color: orange;'>⚠ No result (may be expected)</span><br>";
                    }
                } catch (Exception $e) {
                    // Check if it's a "table already exists" error
                    if (strpos($e->getMessage(), 'already exists') !== false || 
                        strpos($e->getMessage(), 'Duplicate key name') !== false) {
                        echo "<span style='color: blue;'>ℹ Table already exists (skipped)</span><br>";
                    } else {
                        echo "<span style='color: red;'>✗ Error: " . $e->getMessage() . "</span><br>";
                    }
                }
            }
        }
    }
    
    echo "<h3>Verifying existing tables...</h3>";
    
    // Check if system_settings table exists
    $result = $conn->query("SHOW TABLES LIKE 'system_settings'");
    if ($result->num_rows > 0) {
        echo "<span style='color: green;'>✓ Table 'system_settings' exists</span><br>";
        
        // Show row count
        $count = $conn->query("SELECT COUNT(*) as count FROM system_settings")->fetch_assoc();
        echo "  &nbsp; Records: " . $count['count'] . "<br>";
        
        // Show current settings
        $settings = $conn->query("SELECT setting_name, setting_value FROM system_settings ORDER BY setting_name");
        echo "<h4>Current Settings:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Setting Name</th><th>Value</th></tr>";
        while ($row = $settings->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['setting_name']) . "</td><td>" . htmlspecialchars($row['setting_value']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<span style='color: red;'>✗ Table 'system_settings' not found</span><br>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='../SYSTEM_ADMIN/system_settings.php'>Go to System Settings</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Setup Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
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
