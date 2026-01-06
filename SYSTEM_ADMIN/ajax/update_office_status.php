<?php
// Start output buffering to prevent any unwanted output
ob_start();

session_start();
require_once '../../config.php';
require_once '../../includes/system_functions.php';
require_once '../../includes/logger.php';

$response = ['success' => false, 'message' => 'Unknown error'];

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'system_admin') {
    $response = ['success' => false, 'message' => 'Unauthorized access'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['office_id']) && isset($_POST['status'])) {
    $office_id = intval($_POST['office_id']);
    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
    
    try {
        // Get office info before update
        $stmt = $conn->prepare("SELECT office_name, office_code FROM offices WHERE id = ?");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $office = $result->fetch_assoc();
        
        if ($office) {
            // Update office status
            $stmt = $conn->prepare("UPDATE offices SET status = ?, updated_by = ? WHERE id = ?");
            $stmt->bind_param("sii", $status, $_SESSION['user_id'], $office_id);
            $stmt->execute();
            
            // Log the action
            logSystemAction($_SESSION['user_id'], 'office_status_updated', 'office_management', 
                "Updated office status: " . addslashes($office['office_name']) . " (" . addslashes($office['office_code']) . ") to {$status}");
            
            $response = [
                'success' => true, 
                'message' => "Office status updated to {$status}"
            ];
        } else {
            $response = ['success' => false, 'message' => 'Office not found'];
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request'];
}

// Clean output buffer and send JSON response
ob_end_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');
echo json_encode($response);
?>
