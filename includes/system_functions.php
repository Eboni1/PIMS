<?php
// System functions for PIMS
require_once '../config.php';

/**
 * Log system actions for audit trail
 */
function logSystemAction($user_id, $action, $module, $description = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO system_logs (user_id, action, module, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt->bind_param("isssss", $user_id, $action, $module, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log system action: " . $e->getMessage());
        return false;
    }
}

/**
 * Log security events
 */
function logSecurityEvent($event_type, $description, $severity = 'low', $user_id = null) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO security_logs (event_type, description, severity, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt->bind_param("ssssss", $event_type, $description, $severity, $user_id, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log security event: " . $e->getMessage());
        return false;
    }
}

/**
 * Get system setting value
 */
function getSystemSetting($setting_name, $default = null) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_name = ?");
        $stmt->bind_param("s", $setting_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
        
        return $default;
    } catch (Exception $e) {
        error_log("Failed to get system setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Update system setting
 */
function updateSystemSetting($setting_name, $setting_value) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_name = ?");
        $stmt->bind_param("ss", $setting_value, $setting_name);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to update system setting: " . $e->getMessage());
        return false;
    }
}

/**
 * Send system email notification
 */
function sendSystemEmail($to, $subject, $message, $is_html = true) {
    try {
        // Include PHPMailer
        require_once '../SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php';
        require_once '../SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Get email settings
        $system_email = getSystemSetting('system_email', 'noreply@pims.com');
        
        // Use PHP mail() function
        $mail->isMail();
        
        $mail->setFrom($system_email, 'PIMS System');
        $mail->addAddress($to);
        
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        if ($is_html) {
            $mail->isHTML(true);
        }
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send email: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password, $min_length = 8) {
    $errors = [];
    
    if (strlen($password) < $min_length) {
        $errors[] = "Password must be at least $min_length characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

/**
 * Check if user account is locked
 */
function isUserLocked($username) {
    global $conn;
    
    try {
        $max_attempts = getSystemSetting('max_login_attempts', 5);
        
        $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_logs WHERE username = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['attempts'] >= $max_attempts;
    } catch (Exception $e) {
        error_log("Failed to check user lock status: " . $e->getMessage());
        return false;
    }
}

/**
 * Log login attempt
 */
function logLoginAttempt($username, $success, $failure_reason = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO login_logs (username, ip_address, user_agent, success, failure_reason) VALUES (?, ?, ?, ?, ?)");
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt->bind_param("sssis", $username, $ip_address, $user_agent, $success, $failure_reason);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
        return false;
    }
}
?>
