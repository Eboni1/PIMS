<?php
session_start();
require_once 'config.php';

// Import the logging function
function logSystemAction($user_id, $action, $module, $details = null) {
    global $conn;
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $conn->prepare("
            INSERT INTO system_logs (user_id, action, module, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $user_id, $action, $module, $details, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log system action: " . $e->getMessage());
        return false;
    }
}

// Log logout if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'] ?? null;
    $user_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
    $user_email = $_SESSION['email'] ?? '';
    $user_role = $_SESSION['role'] ?? '';
    
    // Log the logout event
    logSystemAction($user_id, 'logout', 'authentication', "User logged out: {$user_name} ({$user_email}) with role: {$user_role}");
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>
