<?php
require_once 'config.php';

// Check for existing unit values in forms
echo "Checking existing unit values in forms:\n";

// Check PAR items
$result = $conn->query("SELECT DISTINCT unit FROM par_items WHERE unit IS NOT NULL AND unit != '' ORDER BY unit");
echo "\nPAR units:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['unit'] . "\n";
}

// Check ICS items  
$result = $conn->query("SELECT DISTINCT unit FROM ics_items WHERE unit IS NOT NULL AND unit != '' ORDER BY unit");
echo "\nICS units:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['unit'] . "\n";
}

// Check ITR items
$result = $conn->query("SELECT DISTINCT unit FROM itr_items WHERE unit IS NOT NULL AND unit != '' ORDER BY unit");
echo "\nITR units:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['unit'] . "\n";
}

// Check RIS items
$result = $conn->query("SELECT DISTINCT unit FROM ris_items WHERE unit IS NOT NULL AND unit != '' ORDER BY unit");
echo "\nRIS units:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['unit'] . "\n";
}

// Check IIRUP items
$result = $conn->query("SELECT DISTINCT unit FROM iirup_items WHERE unit IS NOT NULL AND unit != '' ORDER BY unit");
echo "\nIIRUP units:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['unit'] . "\n";
}
?>