<?php
// Start output buffering to prevent any unwanted output
ob_start();

session_start();
require_once '../../config.php';
require_once '../../includes/system_functions.php';

$response = ['success' => false, 'message' => 'Unknown error'];

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'system_admin') {
    $response = ['success' => false, 'message' => 'Unauthorized access'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM offices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $office = $result->fetch_assoc();
        
        if ($office) {
            $response = ['success' => true, 'office' => $office];
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
echo json_encode($response);
?>
