<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to change password']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Validate new password length
    if (strlen($new_password) < 8) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
        exit();
    }
    
    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit();
    }
    
    try {
        // Get current user data
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($current_password, $user['password_hash'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }
        
        // Check if new password is same as current password
        if (password_verify($new_password, $user['password_hash'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'New password must be different from current password']);
            exit();
        }
        
        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $new_password_hash, $user_id);
        
        if ($stmt->execute()) {
            // Log the password change (non-critical - don't fail if logging fails)
            try {
                $user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
                $details = "User changed their password";
                $action = 'password_changed';
                $module = 'authentication';
                
                $log_stmt = $conn->prepare("
                    INSERT INTO system_logs (user_id, action, module, details, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $log_stmt->bind_param("isssss", $user_id, $action, $module, $details, $ip_address, $user_agent);
                $log_stmt->execute();
                $log_stmt->close();
            } catch (Exception $log_error) {
                // Log the error but don't fail the password change
                error_log("Failed to log password change: " . $log_error->getMessage());
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
    }
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
