<?php
// AJAX endpoint for getting form details
session_start();
require_once '../../config.php';
require_once '../../includes/system_functions.php';
require_once '../../includes/logger.php';

// Check session timeout
checkSessionTimeout();

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SESSION['role'] !== 'system_admin' && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit();
}

// Get form ID from POST data
$form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

if ($form_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid form ID']);
    exit();
}

try {
    // Fetch form details
    $stmt = $conn->prepare("
        SELECT f.*, u.first_name, u.last_name, u.username 
        FROM forms f 
        LEFT JOIN users u ON f.created_by = u.id 
        WHERE f.id = ?
    ");
    
    if ($stmt === false) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $form = $result->fetch_assoc();
        echo json_encode(['success' => true, 'form' => $form]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Form not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching form details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?>
