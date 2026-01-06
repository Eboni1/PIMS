<?php
// AJAX endpoint for fetching category data
session_start();
require_once '../../config.php';
require_once '../../includes/logger.php';

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SESSION['role'] !== 'system_admin') {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit();
}

// Handle GET request for fetching category data
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM asset_categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        if ($category) {
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching category: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
