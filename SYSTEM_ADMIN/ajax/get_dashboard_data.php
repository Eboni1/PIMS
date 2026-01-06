<?php
session_start();
require_once '../../config.php';
require_once '../../includes/system_functions.php';

// Check session timeout
checkSessionTimeout();

// Check if user is logged in and has correct role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'system_admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

// Get fresh statistics
$stats = [];

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

try {
    // User statistics
    $user_query = "SELECT COUNT(*) as total_users, SUM(is_active) as active_users FROM users";
    $user_result = $conn->query($user_query);
    if ($user_result) {
        $user_stats = $user_result->fetch_assoc();
        $stats['total_users'] = $user_stats['total_users'];
        $stats['active_users'] = $user_stats['active_users'];
        $stats['inactive_users'] = $stats['total_users'] - $stats['active_users'];
    }
    
    // Role distribution
    $role_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $role_result = $conn->query($role_query);
    $stats['roles'] = [];
    if ($role_result) {
        while ($row = $role_result->fetch_assoc()) {
            $stats['roles'][$row['role']] = $row['count'];
        }
    }
    
    // Categories statistics
    $cat_table_check = $conn->query("SHOW TABLES LIKE 'asset_categories'");
    if ($cat_table_check && $cat_table_check->num_rows > 0) {
        $cat_query = "SELECT COUNT(*) as total_categories, SUM(status = 'active') as active_categories FROM asset_categories";
        $cat_result = $conn->query($cat_query);
        if ($cat_result) {
            $cat_stats = $cat_result->fetch_assoc();
            $stats['total_categories'] = $cat_stats['total_categories'];
            $stats['active_categories'] = $cat_stats['active_categories'];
            $stats['inactive_categories'] = $stats['total_categories'] - $stats['active_categories'];
        }
    } else {
        // Try regular categories table if asset_categories doesn't exist
        $cat_table_check2 = $conn->query("SHOW TABLES LIKE 'categories'");
        if ($cat_table_check2 && $cat_table_check2->num_rows > 0) {
            $cat_query2 = "SELECT COUNT(*) as total_categories FROM categories";
            $cat_result2 = $conn->query($cat_query2);
            if ($cat_result2) {
                $cat_stats2 = $cat_result2->fetch_assoc();
                $stats['total_categories'] = $cat_stats2['total_categories'];
                $stats['active_categories'] = $cat_stats2['total_categories']; // Assume all active if no status column
                $stats['inactive_categories'] = 0;
            }
        }
    }
    
    // Offices statistics
    $office_table_check = $conn->query("SHOW TABLES LIKE 'offices'");
    if ($office_table_check && $office_table_check->num_rows > 0) {
        $office_query = "SELECT COUNT(*) as total_offices, SUM(status = 'active') as active_offices FROM offices";
        $office_result = $conn->query($office_query);
        if ($office_result) {
            $office_stats = $office_result->fetch_assoc();
            $stats['total_offices'] = $office_stats['total_offices'];
            $stats['active_offices'] = $office_stats['active_offices'];
            $stats['inactive_offices'] = $stats['total_offices'] - $stats['active_offices'];
        }
    }
    
    // Forms statistics
    $form_table_check = $conn->query("SHOW TABLES LIKE 'forms'");
    if ($form_table_check && $form_table_check->num_rows > 0) {
        $form_query = "SELECT COUNT(*) as total_forms, SUM(status = 'active') as active_forms FROM forms";
        $form_result = $conn->query($form_query);
        if ($form_result) {
            $form_stats = $form_result->fetch_assoc();
            $stats['total_forms'] = $form_stats['total_forms'];
            $stats['active_forms'] = $form_stats['active_forms'];
            $stats['inactive_forms'] = $stats['total_forms'] - $stats['active_forms'];
        }
    }
    
    // Recent activity
    $logs_table_check = $conn->query("SHOW TABLES LIKE 'system_logs'");
    if ($logs_table_check && $logs_table_check->num_rows > 0) {
        try {
            $activity_query = "SELECT DATE(timestamp) as date, COUNT(*) as count FROM system_logs WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(timestamp) ORDER BY date";
            $activity_result = $conn->query($activity_query);
            $stats['activity_trend'] = [];
            if ($activity_result) {
                while ($row = $activity_result->fetch_assoc()) {
                    $stats['activity_trend'][] = $row;
                }
            }
            
            $recent_logs_query = "SELECT sl.action, sl.module, sl.description, u.username, sl.timestamp FROM system_logs sl LEFT JOIN users u ON sl.user_id = u.id ORDER BY sl.timestamp DESC LIMIT 10";
            $recent_logs_result = $conn->query($recent_logs_query);
            $stats['recent_logs'] = [];
            if ($recent_logs_result) {
                while ($row = $recent_logs_result->fetch_assoc()) {
                    // Convert timestamp to created_at for consistency
                    $row['created_at'] = $row['timestamp'];
                    $stats['recent_logs'][] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("AJAX system logs error: " . $e->getMessage());
            $stats['activity_trend'] = [];
            $stats['recent_logs'] = [];
        }
    }
    
    // Set default values if not set
    $defaults = [
        'total_users' => 0, 'active_users' => 0, 'inactive_users' => 0,
        'total_categories' => 0, 'active_categories' => 0, 'inactive_categories' => 0,
        'total_offices' => 0, 'active_offices' => 0, 'inactive_offices' => 0,
        'total_forms' => 0, 'active_forms' => 0, 'inactive_forms' => 0,
        'roles' => [], 'activity_trend' => [], 'recent_logs' => []
    ];

    foreach ($defaults as $key => $value) {
        if (!isset($stats[$key])) {
            $stats[$key] = $value;
        }
    }

    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
