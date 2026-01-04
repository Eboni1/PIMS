<?php
// AJAX endpoint for clearing logs
session_start();
require_once '../config.php';

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SESSION['role'] !== 'system_admin' && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit();
}

// Get clear range from POST data
$range = isset($_POST['range']) ? $_POST['range'] : 'all';

try {
    // Build WHERE clause based on range
    $where_conditions = [];
    $params = [];
    $types = '';
    
    switch ($range) {
        case 'all':
            // Clear all logs - no WHERE clause needed
            break;
        case 'older_than_30':
            $where_conditions[] = "created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'older_than_90':
            $where_conditions[] = "created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            break;
        case 'older_than_365':
            $where_conditions[] = "created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)";
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid clear range']);
            exit();
    }
    
    // First, count how many logs will be deleted
    $count_sql = "SELECT COUNT(*) as count FROM system_logs";
    if (!empty($where_conditions)) {
        $count_sql .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $deleted_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    
    // Perform the deletion
    $delete_sql = "DELETE FROM system_logs";
    if (!empty($where_conditions)) {
        $delete_sql .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $stmt = $conn->prepare($delete_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Log the clear action itself
        $clear_details = "Cleared system logs - Range: " . str_replace('_', ' ', $range) . " - Deleted: " . $deleted_count . " entries";
        logSystemAction($_SESSION['user_id'], 'logs_cleared', 'system_logs', $clear_details);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully deleted {$deleted_count} log entries."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear logs']);
    }
    
} catch (Exception $e) {
    error_log("Clear logs error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?>
