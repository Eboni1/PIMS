<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo 'Not logged in';
    exit();
}

echo 'Session OK';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
</head>
<body>
    <h1>Simple Test Page</h1>
    <p>If you can see this, the basic PHP is working.</p>
    <button onclick="window.print()">Print Test</button>
</body>
</html>
