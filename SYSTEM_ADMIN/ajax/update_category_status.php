<?php
// AJAX endpoint for updating category status
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

// Handle POST request for updating category status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_id']) && isset($_POST['status'])) {
    $category_id = intval($_POST['category_id']);
    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
    
    try {
        // Get category info before update
        $stmt = $conn->prepare("SELECT category_name, category_code FROM asset_categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            exit();
        }
        
        // Update category status
        $stmt = $conn->prepare("UPDATE asset_categories SET status = ?, updated_by = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $_SESSION['user_id'], $category_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Log the status change
            logSystemAction($_SESSION['user_id'], 'category_status_updated', 'asset_management', 
                "Updated status for category: {$category['category_name']} ({$category['category_code']}) to {$status}");
            
            echo json_encode([
                'success' => true, 
                'message' => "Category status updated to {$status} successfully"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
