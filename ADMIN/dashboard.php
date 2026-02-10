<?php
session_start();
require_once '../config.php';
require_once '../includes/system_functions.php';
require_once '../includes/logger.php';

// Check session timeout
checkSessionTimeout();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Check if user has correct role (admin or system_admin)
if (!in_array($_SESSION['role'], ['admin', 'system_admin'])) {
    header('Location: ../index.php');
    exit();
}

// Log dashboard access
logSystemAction($_SESSION['user_id'], 'access', 'admin_dashboard', 'Admin accessed dashboard');

// Get dashboard statistics
$stats = [];

// Check database connection first
if (!$conn || $conn->connect_error) {
    $stats['error'] = 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error');
} else {
    try {
        // ===== ASSET ITEMS SUMMARY =====
        // Check if status column exists
        $check_item_status = $conn->query("SHOW COLUMNS FROM asset_items LIKE 'status'");
        $item_has_status = $check_item_status && $check_item_status->num_rows > 0;
        
        // Total Asset Value - separate query for clarity
        $total_value_query = "SELECT COALESCE(SUM(value), 0) as total_asset_value FROM asset_items";
        $total_value_result = $conn->query($total_value_query);
        if ($total_value_result) {
            $total_value_data = $total_value_result->fetch_assoc();
            $stats['total_asset_value'] = $total_value_data['total_asset_value'];
        }
        
        $asset_items_query = "SELECT 
            COUNT(*) as total_items" .
            ($item_has_status ? ",
            SUM(CASE WHEN status = 'serviceable' THEN 1 ELSE 0 END) as serviceable_items,
            SUM(CASE WHEN status = 'unserviceable' THEN 1 ELSE 0 END) as unserviceable_items" : ",
            0 as serviceable_items,
            0 as unserviceable_items") . "
            FROM asset_items";
        $asset_result = $conn->query($asset_items_query);
        if ($asset_result) {
            $asset_data = $asset_result->fetch_assoc();
            $stats = array_merge($stats, $asset_data);
        }
        
        // ===== ASSET CATEGORIES =====
        $categories_query = "SELECT 
            ac.id, ac.category_code as code, ac.category_name as name,
            COUNT(ai.id) as item_count,
            COUNT(DISTINCT ai.asset_id) as asset_count,
            SUM(ai.value) as total_value
            FROM asset_categories ac
            LEFT JOIN assets a ON ac.id = a.asset_categories_id
            LEFT JOIN asset_items ai ON a.id = ai.asset_id
            GROUP BY ac.id, ac.category_code, ac.category_name
            ORDER BY total_value DESC
            LIMIT 6";
        $categories_result = $conn->query($categories_query);
        $stats['top_categories'] = [];
        if ($categories_result) {
            while ($row = $categories_result->fetch_assoc()) {
                $stats['top_categories'][] = $row;
            }
        }

        // ===== CONSUMABLES =====
        $consumables_query = "SELECT 
            COUNT(*) as total_consumables,
            SUM(quantity) as total_quantity,
            SUM(quantity * unit_cost) as total_value,
            COUNT(DISTINCT office_id) as offices_with_consumables
            FROM consumables";
        $consumables_result = $conn->query($consumables_query);
        if ($consumables_result) {
            $stats = array_merge($stats, $consumables_result->fetch_assoc());
        }

        // Low stock count
        $low_stock_query = "SELECT COUNT(*) as low_stock_count FROM consumables WHERE quantity <= reorder_level";
        $low_stock_result = $conn->query($low_stock_query);
        if ($low_stock_result) {
            $stats['low_stock_count'] = $low_stock_result->fetch_assoc()['low_stock_count'];
        }

        // ===== FUEL MANAGEMENT =====
        $fuel_stock_query = "SELECT 
            COALESCE(SUM(quantity), 0) as total_fuel_stock,
            COUNT(DISTINCT fuel_type_id) as fuel_types_count
            FROM fuel_stock";
        $fuel_result = $conn->query($fuel_stock_query);
        if ($fuel_result) {
            $fuel_data = $fuel_result->fetch_assoc();
            $stats['total_fuel_stock'] = $fuel_data['total_fuel_stock'];
            $stats['fuel_types_count'] = $fuel_data['fuel_types_count'];
        }

        // Fuel transactions today
        $fuel_today_query = "SELECT 
            COUNT(*) as today_transactions,
            SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as fuel_out_today,
            SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) as fuel_in_today
            FROM fuel_transactions 
            WHERE DATE(transaction_date) = CURDATE()";
        $fuel_today_result = $conn->query($fuel_today_query);
        if ($fuel_today_result) {
            $stats = array_merge($stats, $fuel_today_result->fetch_assoc());
        }

        // ===== FORMS/ENTRIES SUMMARY =====
        // Check if columns exist first
        $check_par = $conn->query("SHOW COLUMNS FROM par_forms LIKE 'total_amount'");
        $par_has_total = $check_par && $check_par->num_rows > 0;
        $par_query = "SELECT COUNT(*) as par_count" . ($par_has_total ? ", COALESCE(SUM(total_amount), 0) as par_value" : ", 0 as par_value") . " FROM par_forms";
        $par_result = $conn->query($par_query);
        if ($par_result) {
            $par_data = $par_result->fetch_assoc();
            $stats['par_count'] = $par_data['par_count'];
            $stats['par_value'] = $par_data['par_value'];
        }

        $check_ics = $conn->query("SHOW COLUMNS FROM ics_forms LIKE 'total_amount'");
        $ics_has_total = $check_ics && $check_ics->num_rows > 0;
        $ics_query = "SELECT COUNT(*) as ics_count" . ($ics_has_total ? ", COALESCE(SUM(total_amount), 0) as ics_value" : ", 0 as ics_value") . " FROM ics_forms";
        $ics_result = $conn->query($ics_query);
        if ($ics_result) {
            $ics_data = $ics_result->fetch_assoc();
            $stats['ics_count'] = $ics_data['ics_count'];
            $stats['ics_value'] = $ics_data['ics_value'];
        }

        $check_ris = $conn->query("SHOW COLUMNS FROM ris_forms LIKE 'total_amount'");
        $ris_has_total = $check_ris && $check_ris->num_rows > 0;
        $ris_query = "SELECT COUNT(*) as ris_count" . ($ris_has_total ? ", COALESCE(SUM(total_amount), 0) as ris_value" : ", 0 as ris_value") . " FROM ris_forms";
        $ris_result = $conn->query($ris_query);
        if ($ris_result) {
            $ris_data = $ris_result->fetch_assoc();
            $stats['ris_count'] = $ris_data['ris_count'];
            $stats['ris_value'] = $ris_data['ris_value'];
        }

        $check_iirup = $conn->query("SHOW COLUMNS FROM iirup_forms LIKE 'total_amount'");
        $iirup_has_total = $check_iirup && $check_iirup->num_rows > 0;
        $iirup_query = "SELECT COUNT(*) as iirup_count" . ($iirup_has_total ? ", COALESCE(SUM(total_amount), 0) as iirup_value" : ", 0 as iirup_value") . " FROM iirup_forms";
        $iirup_result = $conn->query($iirup_query);
        if ($iirup_result) {
            $iirup_data = $iirup_result->fetch_assoc();
            $stats['iirup_count'] = $iirup_data['iirup_count'];
            $stats['iirup_value'] = $iirup_data['iirup_value'];
        }

        $check_itr = $conn->query("SHOW COLUMNS FROM itr_forms LIKE 'total_amount'");
        $itr_has_total = $check_itr && $check_itr->num_rows > 0;
        $itr_query = "SELECT COUNT(*) as itr_count" . ($itr_has_total ? ", COALESCE(SUM(total_amount), 0) as itr_value" : ", 0 as itr_value") . " FROM itr_forms";
        $itr_result = $conn->query($itr_query);
        if ($itr_result) {
            $itr_data = $itr_result->fetch_assoc();
            $stats['itr_count'] = $itr_data['itr_count'];
            $stats['itr_value'] = $itr_data['itr_value'];
        }

        // ===== INVENTORY TAGS & RED TAGS =====
        // Check if status column exists
        $check_tags_status = $conn->query("SHOW COLUMNS FROM inventory_tags LIKE 'status'");
        $tags_has_status = $check_tags_status && $check_tags_status->num_rows > 0;
        
        $tags_query = "SELECT 
            COUNT(*) as total_tags" . 
            ($tags_has_status ? ",
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_tags,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tags" : ",
            0 as active_tags,
            0 as pending_tags") . "
            FROM inventory_tags";
        $tags_result = $conn->query($tags_query);
        if ($tags_result) {
            $stats = array_merge($stats, $tags_result->fetch_assoc());
        }

        $check_red_status = $conn->query("SHOW COLUMNS FROM red_tags LIKE 'status'");
        $red_has_status = $check_red_status && $check_red_status->num_rows > 0;
        
        $red_tags_query = "SELECT 
            COUNT(*) as red_tag_count" . 
            ($red_has_status ? ",
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_red_tags" : ",
            0 as active_red_tags") . "
            FROM red_tags";
        $red_tags_result = $conn->query($red_tags_query);
        if ($red_tags_result) {
            $red_data = $red_tags_result->fetch_assoc();
            $stats['red_tag_count'] = $red_data['red_tag_count'];
            $stats['active_red_tags'] = $red_data['active_red_tags'];
        }

        // ===== OFFICES & EMPLOYEES =====
        // Check if status column exists
        $check_office_status = $conn->query("SHOW COLUMNS FROM offices LIKE 'status'");
        $office_has_status = $check_office_status && $check_office_status->num_rows > 0;
        
        $offices_query = "SELECT COUNT(*) as office_count FROM offices" . ($office_has_status ? " WHERE status = 'active'" : "");
        $offices_result = $conn->query($offices_query);
        if ($offices_result) {
            $stats['office_count'] = $offices_result->fetch_assoc()['office_count'];
        }

        $check_emp_status = $conn->query("SHOW COLUMNS FROM employees LIKE 'status'");
        $emp_has_status = $check_emp_status && $check_emp_status->num_rows > 0;
        
        $employees_query = "SELECT 
            COUNT(*) as employee_count" .
            ($emp_has_status ? ",
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees" : ",
            COUNT(*) as active_employees") . "
            FROM employees";
        $employees_result = $conn->query($employees_query);
        if ($employees_result) {
            $emp_data = $employees_result->fetch_assoc();
            $stats['employee_count'] = $emp_data['employee_count'];
            $stats['active_employees'] = $emp_data['active_employees'];
        }

        // ===== OFFICE DISTRIBUTION =====
        $office_dist_query = "SELECT 
            o.office_name,
            COUNT(ai.id) as item_count,
            SUM(ai.value) as item_value
            FROM offices o
            LEFT JOIN asset_items ai ON o.id = ai.office_id
            GROUP BY o.id, o.office_name
            ORDER BY item_value DESC
            LIMIT 5";
        $office_dist_result = $conn->query($office_dist_query);
        $stats['office_distribution'] = [];
        if ($office_dist_result) {
            while ($row = $office_dist_result->fetch_assoc()) {
                $stats['office_distribution'][] = $row;
            }
        }

        // ===== RECENT ACTIVITY =====
        $check_item_status = $conn->query("SHOW COLUMNS FROM asset_items LIKE 'status'");
        $item_has_status = $check_item_status && $check_item_status->num_rows > 0;
        $recent_items_query = "SELECT 
            ai.id, ai.description" . ($item_has_status ? ", ai.status" : "") . ", ai.last_updated,
            a.description as asset_description,
            o.office_name
            FROM asset_items ai
            LEFT JOIN assets a ON ai.asset_id = a.id
            LEFT JOIN offices o ON ai.office_id = o.id
            ORDER BY ai.last_updated DESC
            LIMIT 5";
        $recent_items_result = $conn->query($recent_items_query);
        $stats['recent_items'] = [];
        if ($recent_items_result) {
            while ($row = $recent_items_result->fetch_assoc()) {
                $stats['recent_items'][] = $row;
            }
        }

        $recent_fuel_query = "SELECT 
            ft.*, u.first_name, u.last_name
            FROM fuel_transactions ft
            LEFT JOIN users u ON ft.user_id = u.id
            ORDER BY ft.transaction_date DESC
            LIMIT 5";
        $recent_fuel_result = $conn->query($recent_fuel_query);
        $stats['recent_fuel'] = [];
        if ($recent_fuel_result) {
            while ($row = $recent_fuel_result->fetch_assoc()) {
                $stats['recent_fuel'][] = $row;
            }
        }

        $recent_forms_query = "
            (SELECT 'PAR' as form_type, par_no COLLATE utf8mb4_unicode_ci as form_no, created_at, " . ($par_has_total ? "total_amount" : "0") . " as total_amount, 'par_entries.php' as link FROM par_forms ORDER BY created_at DESC LIMIT 2)
            UNION ALL
            (SELECT 'ICS' as form_type, ics_no COLLATE utf8mb4_unicode_ci as form_no, created_at, " . ($ics_has_total ? "total_amount" : "0") . " as total_amount, 'ics_entries.php' as link FROM ics_forms ORDER BY created_at DESC LIMIT 2)
            UNION ALL
            (SELECT 'RIS' as form_type, ris_no COLLATE utf8mb4_unicode_ci as form_no, created_at, " . ($ris_has_total ? "total_amount" : "0") . " as total_amount, 'ris_entries.php' as link FROM ris_forms ORDER BY created_at DESC LIMIT 2)
            UNION ALL
            (SELECT 'IIRUP' as form_type, form_number COLLATE utf8mb4_unicode_ci as form_no, created_at, " . ($iirup_has_total ? "total_amount" : "0") . " as total_amount, 'iirup_entries.php' as link FROM iirup_forms ORDER BY created_at DESC LIMIT 2)
            UNION ALL
            (SELECT 'ITR' as form_type, itr_no COLLATE utf8mb4_unicode_ci as form_no, created_at, " . ($itr_has_total ? "total_amount" : "0") . " as total_amount, 'itr_entries.php' as link FROM itr_forms ORDER BY created_at DESC LIMIT 2)
            ORDER BY created_at DESC
            LIMIT 5";
        $recent_forms_result = $conn->query($recent_forms_query);
        $stats['recent_forms'] = [];
        if ($recent_forms_result) {
            while ($row = $recent_forms_result->fetch_assoc()) {
                $stats['recent_forms'][] = $row;
            }
        }

        // ===== MAINTENANCE ITEMS =====
        $check_item_status = $conn->query("SHOW COLUMNS FROM asset_items LIKE 'status'");
        $item_has_status = $check_item_status && $check_item_status->num_rows > 0;
        $maintenance_query = "SELECT 
            ai.id, ai.description" . ($item_has_status ? ", ai.status" : "") . ", ai.last_updated,
            a.description as asset_description,
            o.office_name
            FROM asset_items ai
            LEFT JOIN assets a ON ai.asset_id = a.id
            LEFT JOIN offices o ON ai.office_id = o.id" .
            ($item_has_status ? " WHERE ai.status = 'unserviceable'" : "") . "
            ORDER BY ai.last_updated DESC
            LIMIT 5";
        $maintenance_result = $conn->query($maintenance_query);
        $stats['maintenance_items'] = [];
        if ($maintenance_result) {
            while ($row = $maintenance_result->fetch_assoc()) {
                $stats['maintenance_items'][] = $row;
            }
        }

        // ===== LOW STOCK ALERTS =====
        $low_stock_items_query = "SELECT 
            c.id, c.description, c.quantity, c.reorder_level,
            o.office_name
            FROM consumables c
            LEFT JOIN offices o ON c.office_id = o.id
            WHERE c.quantity <= c.reorder_level
            ORDER BY c.quantity ASC
            LIMIT 5";
        $low_stock_items_result = $conn->query($low_stock_items_query);
        $stats['low_stock_items'] = [];
        if ($low_stock_items_result) {
            while ($row = $low_stock_items_result->fetch_assoc()) {
                $stats['low_stock_items'][] = $row;
            }
        }

    } catch (Exception $e) {
        $stats['error'] = "Error fetching dashboard stats: " . $e->getMessage();
        error_log("Admin Dashboard Error: " . $e->getMessage());
    }
}

// Set default values if not set
$defaults = [
    'total_items' => 0, 'serviceable_items' => 0, 'unserviceable_items' => 0,
    'total_asset_value' => 0, 'total_value' => 0, 'top_categories' => [],
    'total_consumables' => 0, 'total_quantity' => 0, 
    'low_stock_count' => 0,
    'total_fuel_stock' => 0, 'fuel_types_count' => 0,
    'today_transactions' => 0, 'fuel_out_today' => 0, 'fuel_in_today' => 0,
    'par_count' => 0, 'ics_count' => 0, 'ris_count' => 0, 'iirup_count' => 0, 'itr_count' => 0,
    'par_value' => 0, 'ics_value' => 0, 'ris_value' => 0, 'iirup_value' => 0, 'itr_value' => 0,
    'total_tags' => 0, 'active_tags' => 0, 'pending_tags' => 0,
    'red_tag_count' => 0, 'active_red_tags' => 0,
    'office_count' => 0, 'employee_count' => 0, 'active_employees' => 0,
    'office_distribution' => [],
    'recent_items' => [], 'recent_fuel' => [], 'recent_forms' => [],
    'maintenance_items' => [], 'low_stock_items' => []
];

foreach ($defaults as $key => $value) {
    if (!isset($stats[$key])) {
        $stats[$key] = $value;
    }
}

// Calculate totals
$total_forms = $stats['par_count'] + $stats['ics_count'] + $stats['ris_count'] + $stats['iirup_count'] + $stats['itr_count'];
$total_forms_value = $stats['par_value'] + $stats['ics_value'] + $stats['ris_value'] + $stats['iirup_value'] + $stats['itr_value'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/index.css" rel="stylesheet">
    <link href="../assets/css/theme-custom.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F7F3F3 0%, #C1EAF2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .dashboard-header {
            background: white;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #191BA9;
        }
        
        .module-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }
        
        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(25, 27, 169, 0.15);
            border-color: #191BA9;
            color: inherit;
        }
        
        .module-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-size: 1.5rem;
        }
        
        .module-icon.blue { background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%); color: white; }
        .module-icon.green { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .module-icon.orange { background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%); color: white; }
        .module-icon.purple { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white; }
        .module-icon.red { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .module-icon.teal { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); color: white; }
        
        .module-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .module-desc {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }
        
        .stat-icon.blue { background: rgba(25, 27, 169, 0.1); color: #191BA9; }
        .stat-icon.green { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-icon.orange { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
        .stat-icon.purple { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        .stat-icon.red { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .stat-icon.cyan { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .stat-sublabel {
            font-size: 0.7rem;
            color: #adb5bd;
            margin-top: 0.25rem;
        }
        
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 1.25rem;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: #191BA9;
        }
        
        .chart-container {
            position: relative;
            height: 220px;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-title {
            font-size: 0.85rem;
            font-weight: 500;
            color: #212529;
            margin-bottom: 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .activity-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .activity-time {
            font-size: 0.7rem;
            color: #adb5bd;
            white-space: nowrap;
        }
        
        .status-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .status-serviceable { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-in_use { background: rgba(25, 27, 169, 0.1); color: #191BA9; }
        .status-maintenance { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
        .status-disposed { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .status-unserviceable { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
        
        .alert-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255, 193, 7, 0.08);
            border-radius: 10px;
            margin-bottom: 0.5rem;
            border-left: 3px solid #ffc107;
        }
        
        .alert-item:last-child {
            margin-bottom: 0;
        }
        
        .alert-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 193, 7, 0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffc107;
            font-size: 0.875rem;
        }
        
        .alert-content {
            flex: 1;
        }
        
        .alert-title {
            font-size: 0.8rem;
            font-weight: 500;
            color: #212529;
        }
        
        .alert-desc {
            font-size: 0.7rem;
            color: #6c757d;
        }
        
        .alert-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #dc3545;
        }
        
        .form-summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .form-summary-item:last-child {
            border-bottom: none;
        }
        
        .form-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-type-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .form-type-icon.par { background: rgba(25, 27, 169, 0.1); color: #191BA9; }
        .form-type-icon.ics { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .form-type-icon.ris { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
        .form-type-icon.iirup { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        .form-type-icon.itr { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        
        .form-count {
            font-weight: 600;
            color: #212529;
        }
        
        .form-value {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .category-code {
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
            background: #191BA9;
            color: white;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .category-name {
            font-size: 0.8rem;
            color: #212529;
        }
        
        .category-count {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .view-all {
            font-size: 0.8rem;
            color: #191BA9;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php $page_title = 'Admin Dashboard'; ?>
    <div class="main-wrapper" id="mainWrapper">
        <?php require_once 'includes/sidebar-toggle.php'; ?>
        <?php require_once 'includes/sidebar.php'; ?>
        <?php require_once 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1" style="font-weight: 700; color: #191BA9;">
                        <i class="bi bi-grid-1x2-fill me-2"></i>Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">Welcome back! Here's an overview of your property inventory system.</p>
                    <?php if (isset($stats['error'])): ?>
                        <div class="alert alert-warning mt-2 mb-0 py-2" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <small><?php echo htmlspecialchars($stats['error']); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="exportData()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="assets.php" class="module-card">
                    <div class="module-icon blue">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="module-title">Assets</div>
                    <div class="module-desc">Manage assets</div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="asset_items.php" class="module-card">
                    <div class="module-icon green">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div class="module-title">Asset Items</div>
                    <div class="module-desc">Track items</div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="consumables.php" class="module-card">
                    <div class="module-icon orange">
                        <i class="bi bi-archive"></i>
                    </div>
                    <div class="module-title">Consumables</div>
                    <div class="module-desc">Supplies & stock</div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="fuel.php" class="module-card">
                    <div class="module-icon purple">
                        <i class="bi bi-fuel-pump"></i>
                    </div>
                    <div class="module-title">Fuel</div>
                    <div class="module-desc">Fuel management</div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="inventory_tags.php" class="module-card">
                    <div class="module-icon teal">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <div class="module-title">Inventory Tags</div>
                    <div class="module-desc">QR & tracking</div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="reports.php" class="module-card">
                    <div class="module-icon red">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="module-title">Reports</div>
                    <div class="module-desc">View reports</div>
                </a>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_items']); ?></div>
                    <div class="stat-label">Total Asset Items</div>
                    <div class="stat-sublabel"><?php echo number_format($stats['serviceable_items']); ?> serviceable</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <span style="font-size: 1.25rem; font-weight: 600;">₱</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_value'], 2); ?></div>
                    <div class="stat-label">Total Asset Value</div>
                    <div class="stat-sublabel">Across all categories</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_forms); ?></div>
                    <div class="stat-label">Total Forms</div>
                    <div class="stat-sublabel">PAR, ICS, RIS, ITR, IIRUP</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-fuel-pump"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_fuel_stock'], 0); ?></div>
                    <div class="stat-label">Fuel Stock (L)</div>
                    <div class="stat-sublabel"><?php echo $stats['fuel_types_count']; ?> fuel types</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-pie-chart"></i> Asset Status Distribution
                            </div>
                            <div class="chart-container">
                                <canvas id="assetStatusChart"></canvas>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-6">
                                    <div class="small text-muted">Serviceable</div>
                                    <div class="fw-bold text-success"><?php echo $stats['serviceable_items']; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Unserviceable</div>
                                    <div class="fw-bold text-danger"><?php echo $stats['unserviceable_items']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-building"></i> Office Distribution
                            </div>
                            <div class="chart-container">
                                <canvas id="officeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="section-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-title mb-0">
                            <i class="bi bi-files"></i> Forms & Entries Summary
                        </div>
                        <a href="par_entries.php" class="view-all">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-summary-item">
                                <div class="form-type">
                                    <div class="form-type-icon par">PAR</div>
                                    <span class="small">Property Receipts</span>
                                </div>
                                <div class="text-end">
                                    <div class="form-count"><?php echo number_format($stats['par_count']); ?></div>
                                    <div class="form-value">PHP <?php echo number_format($stats['par_value'], 2); ?></div>
                                </div>
                            </div>
                            <div class="form-summary-item">
                                <div class="form-type">
                                    <div class="form-type-icon ics">ICS</div>
                                    <span class="small">Custodian Slips</span>
                                </div>
                                <div class="text-end">
                                    <div class="form-count"><?php echo number_format($stats['ics_count']); ?></div>
                                    <div class="form-value">PHP <?php echo number_format($stats['ics_value'], 2); ?></div>
                                </div>
                            </div>
                            <div class="form-summary-item">
                                <div class="form-type">
                                    <div class="form-type-icon ris">RIS</div>
                                    <span class="small">Requisition Slips</span>
                                </div>
                                <div class="text-end">
                                    <div class="form-count"><?php echo number_format($stats['ris_count']); ?></div>
                                    <div class="form-value">PHP <?php echo number_format($stats['ris_value'], 2); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-summary-item">
                                <div class="form-type">
                                    <div class="form-type-icon iirup">IIRUP</div>
                                    <span class="small">Unserviceable Report</span>
                                </div>
                                <div class="text-end">
                                    <div class="form-count"><?php echo number_format($stats['iirup_count']); ?></div>
                                    <div class="form-value">PHP <?php echo number_format($stats['iirup_value'], 2); ?></div>
                                </div>
                            </div>
                            <div class="form-summary-item">
                                <div class="form-type">
                                    <div class="form-type-icon itr">ITR</div>
                                    <span class="small">Transfer Requests</span>
                                </div>
                                <div class="text-end">
                                    <div class="form-count"><?php echo number_format($stats['itr_count']); ?></div>
                                    <div class="form-value">PHP <?php echo number_format($stats['itr_value'], 2); ?></div>
                                </div>
                            </div>
                            <div class="form-summary-item" style="background: rgba(25, 27, 169, 0.05); border-radius: 8px; padding: 0.75rem; margin-top: 0.5rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Total Value</span>
                                    <span class="fw-bold text-primary">PHP <?php echo number_format($total_forms_value, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="section-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-title mb-0">
                            <i class="bi bi-clock-history"></i> Recent Activity
                        </div>
                    </div>
                    <div class="activity-list">
                        <?php 
                        $all_activity = [];
                        
                        foreach ($stats['recent_items'] as $item) {
                            $all_activity[] = [
                                'type' => 'asset',
                                'icon' => 'bi-box',
                                'icon_class' => 'blue',
                                'title' => htmlspecialchars($item['description']),
                                'meta' => 'Status: ' . ucfirst($item['status']),
                                'time' => $item['last_updated']
                            ];
                        }
                        
                        foreach ($stats['recent_fuel'] as $fuel) {
                            $all_activity[] = [
                                'type' => 'fuel',
                                'icon' => 'bi-fuel-pump',
                                'icon_class' => 'orange',
                                'title' => ucfirst($fuel['fuel_type']) . ' - ' . $fuel['transaction_type'],
                                'meta' => number_format($fuel['quantity'], 2) . ' L',
                                'time' => $fuel['transaction_date']
                            ];
                        }
                        
                        foreach ($stats['recent_forms'] as $form) {
                            $all_activity[] = [
                                'type' => 'form',
                                'icon' => 'bi-file-earmark',
                                'icon_class' => 'purple',
                                'title' => $form['form_type'] . ' - ' . $form['form_no'],
                                'meta' => 'PHP ' . number_format($form['total_amount'], 2),
                                'time' => $form['created_at']
                            ];
                        }
                        
                        usort($all_activity, function($a, $b) {
                            return strtotime($b['time']) - strtotime($a['time']);
                        });
                        
                        $all_activity = array_slice($all_activity, 0, 6);
                        
                        if (!empty($all_activity)): 
                            foreach ($all_activity as $activity): 
                                $time_diff = time() - strtotime($activity['time']);
                                if ($time_diff < 3600) {
                                    $time_display = floor($time_diff / 60) . ' min ago';
                                } elseif ($time_diff < 86400) {
                                    $time_display = floor($time_diff / 3600) . ' hours ago';
                                } else {
                                    $time_display = floor($time_diff / 86400) . ' days ago';
                                }
                        ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $activity['icon_class']; ?>" style="background: rgba(25, 27, 169, 0.1); color: #191BA9;">
                                    <i class="bi <?php echo $activity['icon']; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php echo $activity['title']; ?></div>
                                    <div class="activity-meta"><?php echo $activity['meta']; ?></div>
                                </div>
                                <div class="activity-time"><?php echo $time_display; ?></div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4"></i>
                                <p class="small mt-2">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php if ($stats['low_stock_count'] > 0 || $stats['active_red_tags'] > 0 || count($stats['maintenance_items']) > 0): ?>
                <div class="section-card mb-4">
                    <div class="section-title">
                        <i class="bi bi-bell"></i> Alerts & Notifications
                    </div>
                    
                    <?php if ($stats['low_stock_count'] > 0): ?>
                    <div class="alert-item">
                        <div class="alert-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Low Stock Items</div>
                            <div class="alert-desc"><?php echo $stats['low_stock_count']; ?> items need replenishment</div>
                        </div>
                        <a href="consumables.php" class="alert-value">View</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($stats['active_red_tags'] > 0): ?>
                    <div class="alert-item">
                        <div class="alert-icon">
                            <i class="bi bi-tag-fill"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Active Red Tags</div>
                            <div class="alert-desc"><?php echo $stats['active_red_tags']; ?> unserviceable items</div>
                        </div>
                        <a href="red_tags.php" class="alert-value">View</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($stats['maintenance_items']) && count($stats['maintenance_items']) > 0): ?>
                    <div class="alert-item">
                        <div class="alert-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Maintenance Required</div>
                            <div class="alert-desc"><?php echo count($stats['maintenance_items']); ?> items under maintenance</div>
                        </div>
                        <a href="asset_items.php?status=maintenance" class="alert-value">View</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="section-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-title mb-0">
                            <i class="bi bi-tags"></i> Top Categories
                        </div>
                        <a href="asset_categories.php" class="view-all">
                            All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <?php if (!empty($stats['top_categories'])): ?>
                        <?php foreach ($stats['top_categories'] as $category): ?>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-code"><?php echo htmlspecialchars($category['code']); ?></span>
                                <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                            </div>
                            <div class="category-count"><?php echo $category['item_count']; ?> items</div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <small>No categories found</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="section-card mb-4">
                    <div class="section-title">
                        <i class="bi bi-info-circle"></i> System Overview
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: rgba(25, 27, 169, 0.05);">
                                <div class="fw-bold" style="color: #191BA9; font-size: 1.25rem;"><?php echo number_format($stats['office_count']); ?></div>
                                <div class="small text-muted">Offices</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: rgba(40, 167, 69, 0.05);">
                                <div class="fw-bold text-success" style="font-size: 1.25rem;"><?php echo number_format($stats['active_employees']); ?></div>
                                <div class="small text-muted">Employees</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: rgba(253, 126, 20, 0.05);">
                                <div class="fw-bold" style="color: #fd7e14; font-size: 1.25rem;"><?php echo number_format($stats['active_tags']); ?></div>
                                <div class="small text-muted">Active Tags</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: rgba(220, 53, 69, 0.05);">
                                <div class="fw-bold text-danger" style="font-size: 1.25rem;"><?php echo number_format($stats['today_transactions']); ?></div>
                                <div class="small text-muted">Fuel Today</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="section-card">
                    <div class="section-title">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </div>
                    <div class="d-grid gap-2">
                        <a href="create_tag.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Create Inventory Tag
                        </a>
                        <a href="fuel.php?tab=fuelout" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-fuel-pump me-1"></i> Record Fuel Out
                        </a>
                        <a href="red_tags.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-tag-fill me-1"></i> Create Red Tag
                        </a>
                        <a href="par_form.php" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-plus me-1"></i> New PAR Form
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <?php require_once 'includes/logout-modal.php'; ?>
    <?php require_once 'includes/change-password-modal.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'includes/sidebar-scripts.php'; ?>
    <script>
        function refreshDashboard() {
            location.reload();
        }
        
        function exportData() {
            const data = {
                timestamp: new Date().toISOString(),
                assets: {
                    total_items: <?php echo $stats['total_items']; ?>,
                    serviceable: <?php echo $stats['serviceable_items']; ?>,
                    in_use: <?php echo $stats['in_use_items']; ?>,
                    maintenance: <?php echo $stats['maintenance_items']; ?>,
                    value: <?php echo $stats['total_value']; ?>
                },
                forms: {
                    total: <?php echo $total_forms; ?>,
                    value: <?php echo $total_forms_value; ?>,
                    par: <?php echo $stats['par_count']; ?>,
                    ics: <?php echo $stats['ics_count']; ?>,
                    ris: <?php echo $stats['ris_count']; ?>,
                    iirup: <?php echo $stats['iirup_count']; ?>,
                    itr: <?php echo $stats['itr_count']; ?>
                },
                fuel: {
                    stock: <?php echo $stats['total_fuel_stock']; ?>,
                    today_transactions: <?php echo $stats['today_transactions']; ?>
                }
            };
            
            let csv = 'Category,Metric,Value\n';
            csv += `Assets,Total Items,${data.assets.total_items}\n`;
            csv += `Assets,Serviceable,${data.assets.serviceable}\n`;
            csv += `Assets,In Use,${data.assets.in_use}\n`;
            csv += `Assets,Value,${data.assets.value}\n`;
            csv += `Forms,Total Count,${data.forms.total}\n`;
            csv += `Forms,Total Value,${data.forms.value}\n`;
            csv += `Fuel,Stock (L),${data.fuel.stock}\n`;
            csv += `Fuel,Today's Transactions,${data.fuel.today_transactions}\n`;
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `dashboard_export_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = 'Inter, sans-serif';
            Chart.defaults.color = '#666';
            
            const assetStatusCtx = document.getElementById('assetStatusChart').getContext('2d');
            new Chart(assetStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Serviceable', 'Unserviceable'],
                    datasets: [{
                        data: [
                            <?php echo $stats['serviceable_items']; ?>,
                            <?php echo $stats['unserviceable_items']; ?>
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#dc3545'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            const officeCtx = document.getElementById('officeChart').getContext('2d');
            const officeData = <?php echo json_encode(array_slice($stats['office_distribution'], 0, 5)); ?>;
            new Chart(officeCtx, {
                type: 'bar',
                data: {
                    labels: officeData.map(o => o.office_name.substring(0, 15)),
                    datasets: [{
                        label: 'Item Value',
                        data: officeData.map(o => o.item_value),
                        backgroundColor: '#5CC2F2',
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'PHP ' + (value / 1000000).toFixed(1) + 'M';
                                },
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>