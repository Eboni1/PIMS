<?php
session_start();
require_once '../config.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['system_admin', 'admin'])) {
    header('Location: ../index.php');
    exit();
}

// Get parameters
$action = $_GET['action'] ?? '';
$tag_id = intval($_GET['id'] ?? 0);
$reason = $_GET['reason'] ?? '';

if (empty($action) || empty($tag_id)) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: inventory_tag_submissions.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get tag submission details
    $tag_sql = "SELECT it.*, ai.id as asset_item_id FROM inventory_tags it LEFT JOIN asset_items ai ON it.asset_item_id = ai.id WHERE it.id = ?";
    $tag_stmt = $conn->prepare($tag_sql);
    $tag_stmt->bind_param("i", $tag_id);
    $tag_stmt->execute();
    $tag_result = $tag_stmt->get_result();
    $tag = $tag_result->fetch_assoc();
    
    if (!$tag) {
        throw new Exception('Tag submission not found');
    }
    
    switch ($action) {
        case 'approve':
            // Update tag status to approved
            $update_sql = "UPDATE inventory_tags SET status = 'Approved', approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $_SESSION['user_id'], $tag_id);
            $update_stmt->execute();
            
            // Add to history
            $history_sql = "INSERT INTO inventory_tag_history (tag_id, asset_item_id, action, details, created_by, created_at) 
                           VALUES (?, ?, 'Approved', 'Tag submission approved by admin', ?, CURRENT_TIMESTAMP)";
            $history_stmt = $conn->prepare($history_sql);
            $history_stmt->bind_param("iii", $tag_id, $tag['asset_item_id'], $_SESSION['user_id']);
            $history_stmt->execute();
            
            $_SESSION['success'] = 'Tag submission approved successfully!';
            break;
            
        case 'reject':
            if (empty($reason)) {
                throw new Exception('Rejection reason is required');
            }
            
            // Update tag status to rejected
            $update_sql = "UPDATE inventory_tags SET status = 'Rejected', approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $_SESSION['user_id'], $tag_id);
            $update_stmt->execute();
            
            // Add to history
            $history_sql = "INSERT INTO inventory_tag_history (tag_id, asset_item_id, action, details, created_by, created_at) 
                           VALUES (?, ?, 'Rejected', ?, ?, CURRENT_TIMESTAMP)";
            $history_stmt = $conn->prepare($history_sql);
            $details = "Tag submission rejected. Reason: " . $reason;
            $history_stmt->bind_param("iisi", $tag_id, $tag['asset_item_id'], $details, $_SESSION['user_id']);
            $history_stmt->execute();
            
            // Reset asset item status back to no_tag
            if ($tag['asset_item_id']) {
                $reset_sql = "UPDATE asset_items SET status = 'no_tag', last_updated = CURRENT_TIMESTAMP WHERE id = ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("i", $tag['asset_item_id']);
                $reset_stmt->execute();
            }
            
            $_SESSION['success'] = 'Tag submission rejected successfully!';
            break;
            
        case 'process':
            // Update tag status to processed
            $update_sql = "UPDATE inventory_tags SET status = 'Processed', updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $_SESSION['user_id'], $tag_id);
            $update_stmt->execute();
            
            // Update asset item with tag information
            if ($tag['asset_item_id']) {
                $asset_sql = "UPDATE asset_items SET 
                              property_no = ?, 
                              inventory_tag = ?, 
                              employee_id = ?, 
                              status = 'available',
                              last_updated = CURRENT_TIMESTAMP
                              WHERE id = ?";
                $asset_stmt = $conn->prepare($asset_sql);
                $asset_stmt->bind_param("ssii", $tag['property_number'], $tag['tag_number'], $tag['person_accountable'], $tag['asset_item_id']);
                $asset_stmt->execute();
            }
            
            // Add to history
            $history_sql = "INSERT INTO inventory_tag_history (tag_id, asset_item_id, action, details, created_by, created_at) 
                           VALUES (?, ?, 'Processed', 'Tag processed and applied to asset item', ?, CURRENT_TIMESTAMP)";
            $history_stmt = $conn->prepare($history_sql);
            $history_stmt->bind_param("iii", $tag_id, $tag['asset_item_id'], $_SESSION['user_id']);
            $history_stmt->execute();
            
            $_SESSION['success'] = 'Tag processed successfully! Asset item has been updated.';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    // Commit transaction
    $conn->commit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error'] = 'Error processing tag: ' . $e->getMessage();
}

header('Location: inventory_tag_submissions.php');
exit();
?>
