<?php
session_start();
require_once 'config.php';
require_once 'includes/logger.php';

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
