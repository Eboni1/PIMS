<?php
require_once '../config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "<h3>Debugging QR Codes</h3>";

// Check if any QR codes exist
$result = $conn->query("SELECT id, inventory_tag, qr_code FROM asset_items WHERE qr_code IS NOT NULL AND qr_code != '' LIMIT 5");
if ($result->num_rows > 0) {
    echo "<h4>Found QR codes in database:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Inventory Tag</th><th>QR Code File</th><th>File Exists</th></tr>";
    while($row = $result->fetch_assoc()) {
        $qr_file = '../uploads/inventory_tags/' . $row['qr_code'];
        $file_exists = file_exists($qr_file) ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['inventory_tag']) . "</td>";
        echo "<td>" . htmlspecialchars($row['qr_code']) . "</td>";
        echo "<td>$file_exists</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No QR codes found in database</p>";
}

// Check directory contents
echo "<h4>Files in inventory_tags directory:</h4>";
$dir = '../uploads/inventory_tags/';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>Directory does not exist: $dir</p>";
}

$conn->close();
?>
