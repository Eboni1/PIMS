<?php
session_start();
require_once '../config.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['system_admin', 'admin'])) {
    header('Location: ../index.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: asset_items.php');
    exit();
}

// Get form data
$item_id = intval($_POST['item_id']);
$category_id = intval($_POST['category_id']);
$property_no = trim($_POST['property_no']);
$inventory_tag = trim($_POST['inventory_tag']);
$person_accountable = intval($_POST['person_accountable']);
$end_user = trim($_POST['end_user']);
$date_counted = trim($_POST['date_counted']);
$tag_format_id = intval($_POST['tag_format_id']);
$current_number = intval($_POST['current_number']);

// Get category-specific fields
$category_fields = [];
$category_code = '';
$category_sql = "SELECT category_code FROM asset_categories WHERE id = ?";
$category_stmt = $conn->prepare($category_sql);
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category_result = $category_stmt->get_result();
if ($category_row = $category_result->fetch_assoc()) {
    $category_code = $category_row['category_code'];
}

// Handle image upload
$image_filename = '';
$image_path = '';
if (isset($_FILES['asset_image']) && $_FILES['asset_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['asset_image'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        header('Location: create_tag.php?id=' . $item_id);
        exit();
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size must be less than 5MB.';
        header('Location: create_tag.php?id=' . $item_id);
        exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $image_filename = 'asset_' . $item_id . '_' . time() . '.' . $extension;
    $upload_path = '../uploads/inventory_tags/' . $image_filename;
    
    // Create directory if it doesn't exist
    if (!is_dir('../uploads/inventory_tags/')) {
        mkdir('../uploads/inventory_tags/', 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $_SESSION['error'] = 'Error uploading image file.';
        header('Location: create_tag.php?id=' . $item_id);
        exit();
    }
    
    $image_path = 'uploads/inventory_tags/' . $image_filename;
}

// Validate required fields
if (empty($item_id) || empty($category_id) || empty($property_no) || empty($inventory_tag) || empty($person_accountable) || empty($end_user) || empty($date_counted)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    header('Location: create_tag.php?id=' . $item_id);
    exit();
}

// Get asset item information
$asset_sql = "SELECT ai.description, a.description as asset_description FROM asset_items ai JOIN assets a ON ai.asset_id = a.id WHERE ai.id = ?";
$asset_stmt = $conn->prepare($asset_sql);
$asset_stmt->bind_param("i", $item_id);
$asset_stmt->execute();
$asset_result = $asset_stmt->get_result();
$asset = $asset_result->fetch_assoc();

if (!$asset) {
    $_SESSION['error'] = 'Asset item not found';
    header('Location: asset_items.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Insert into inventory_tags table
    $insert_sql = "INSERT INTO inventory_tags (asset_item_id, tag_number, property_number, item_description, category_id, person_accountable, end_user, location, `condition`, status, created_by, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, CURRENT_TIMESTAMP)";
    
    // Default location and condition
    $location = 'Main Office';
    $condition = 'Good';
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isssiisssi", $item_id, $inventory_tag, $property_no, $asset['asset_description'], $category_id, $person_accountable, $end_user, $location, $condition, $_SESSION['user_id']);
    $insert_stmt->execute();
    
    $tag_id = $conn->insert_id;
    
    // Handle image attachment if uploaded
    if ($image_filename) {
        $attachment_sql = "INSERT INTO inventory_tag_attachments (tag_id, asset_item_id, filename, original_name, file_type, file_size, file_path, created_by, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $attachment_stmt = $conn->prepare($attachment_sql);
        $attachment_stmt->bind_param("iisssisi", $tag_id, $item_id, $image_filename, $_FILES['asset_image']['name'], $_FILES['asset_image']['type'], $_FILES['asset_image']['size'], $image_path, $_SESSION['user_id']);
        $attachment_stmt->execute();
    }
    
    // Add to history
    $history_details = "Tag submission created for item ID {$item_id}: Tag Number: {$inventory_tag}, Property Number: {$property_no}, Category: {$category_code}";
    if ($image_filename) {
        $history_details .= " (Image: {$image_filename})";
    }
    
    $history_sql = "INSERT INTO inventory_tag_history (tag_id, asset_item_id, action, details, created_by, created_at) 
                    VALUES (?, ?, 'Submitted', ?, ?, CURRENT_TIMESTAMP)";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bind_param("iisi", $tag_id, $item_id, $history_details, $_SESSION['user_id']);
    $history_stmt->execute();
    
    // Update tag format current number if tag format is used
    if ($tag_format_id > 0) {
        $new_number = $current_number + 1;
        $update_tag_sql = "UPDATE tag_formats SET current_number = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $update_tag_stmt = $conn->prepare($update_tag_sql);
        $update_tag_stmt->bind_param("iii", $new_number, $_SESSION['user_id'], $tag_format_id);
        $update_tag_stmt->execute();
    }
    
    // Update asset item status to show tag is pending
    $update_asset_sql = "UPDATE asset_items SET status = 'pending_tag', last_updated = CURRENT_TIMESTAMP WHERE id = ?";
    $update_asset_stmt = $conn->prepare($update_asset_sql);
    $update_asset_stmt->bind_param("i", $item_id);
    $update_asset_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = 'Tag submitted successfully! Your submission is now pending approval.';
    
    // Redirect to tag submissions page
    header('Location: inventory_tag_submissions.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error'] = 'Error submitting tag: ' . $e->getMessage();
    header('Location: create_tag.php?id=' . $item_id);
    exit();
}
?>
