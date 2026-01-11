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

// Get export format from POST request
$format = $_POST['format'] ?? 'excel';
$date_from = $_POST['date_from'] ?? null;
$date_to = $_POST['date_to'] ?? null;

logSystemAction($_SESSION['user_id'], 'Exported ICS Data', 'forms', "Format: $format, Date Range: $date_from to $date_to");

// Build base query
$where_conditions = [];
$params = [];
$types = '';

if ($date_from && $date_to) {
    $where_conditions[] = "f.created_at BETWEEN ? AND ?";
    $params[] = $date_from . ' 00:00:00';
    $params[] = $date_to . ' 23:59:59';
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get ICS forms with items
$ics_forms = [];
$query = "
    SELECT 
        f.id,
        f.ics_no,
        f.entity_name,
        f.fund_cluster,
        f.received_from,
        f.received_from_position,
        f.received_from_date,
        f.received_by,
        f.received_by_position,
        f.received_by_date,
        f.created_at,
        COUNT(i.item_id) as item_count,
        SUM(i.total_cost) as total_value
    FROM ics_forms f 
    LEFT JOIN ics_items i ON f.id = i.form_id 
    $where_clause
    GROUP BY f.id 
    ORDER BY f.created_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ics_forms[] = $row;
    }
}
$stmt->close();

// Get detailed items for each ICS form
foreach ($ics_forms as &$form) {
    $items_query = "
        SELECT 
            item_no,
            quantity,
            unit,
            unit_cost,
            total_cost,
            description,
            useful_life
        FROM ics_items 
        WHERE form_id = ? 
        ORDER BY item_no
    ";
    
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $form['id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $form['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $form['items'][] = $item;
    }
    $items_stmt->close();
}

if ($format === 'excel') {
    // Export to Excel (CSV format that can be opened in Excel)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ICS_Entries_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, [
        'ICS No.',
        'Entity Name',
        'Fund Cluster',
        'Item No.',
        'Description',
        'Quantity',
        'Unit',
        'Unit Cost',
        'Total Cost',
        'Useful Life',
        'Received From',
        'Position',
        'Received Date',
        'Received By',
        'Position',
        'Received Date',
        'Date Created'
    ]);
    
    // Data rows
    foreach ($ics_forms as $form) {
        if (empty($form['items'])) {
            // If no items, still output the form information
            fputcsv($output, [
                $form['ics_no'],
                $form['entity_name'],
                $form['fund_cluster'],
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $form['received_from'],
                $form['received_from_position'],
                $form['received_from_date'] ? date('Y-m-d', strtotime($form['received_from_date'])) : '',
                $form['received_by'],
                $form['received_by_position'],
                $form['received_by_date'] ? date('Y-m-d', strtotime($form['received_by_date'])) : '',
                date('Y-m-d', strtotime($form['created_at']))
            ]);
        } else {
            // Output each item as a separate row
            foreach ($form['items'] as $item) {
                fputcsv($output, [
                    $form['ics_no'],
                    $form['entity_name'],
                    $form['fund_cluster'],
                    $item['item_no'],
                    $item['description'],
                    $item['quantity'],
                    $item['unit'],
                    $item['unit_cost'],
                    $item['total_cost'],
                    $item['useful_life'],
                    $form['received_from'],
                    $form['received_from_position'],
                    $form['received_from_date'] ? date('Y-m-d', strtotime($form['received_from_date'])) : '',
                    $form['received_by'],
                    $form['received_by_position'],
                    $form['received_by_date'] ? date('Y-m-d', strtotime($form['received_by_date'])) : '',
                    date('Y-m-d', strtotime($form['created_at']))
                ]);
            }
        }
    }
    
    fclose($output);
    
} elseif ($format === 'summary') {
    // Export to Summary Report (CSV format)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ICS_Summary_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, [
        'ICS No.',
        'Entity Name',
        'Fund Cluster',
        'Total Items',
        'Total Value',
        'Received From',
        'Received By',
        'Date Created'
    ]);
    
    // Data rows
    foreach ($ics_forms as $form) {
        fputcsv($output, [
            $form['ics_no'],
            $form['entity_name'],
            $form['fund_cluster'],
            $form['item_count'],
            $form['total_value'],
            $form['received_from'],
            $form['received_by'],
            date('Y-m-d', strtotime($form['created_at']))
        ]);
    }
    
    fclose($output);
    
} else {
    // Default to Excel format
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ICS_Entries_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['ICS No.', 'Entity Name', 'Fund Cluster', 'Total Items', 'Total Value', 'Date Created']);
    
    foreach ($ics_forms as $form) {
        fputcsv($output, [
            $form['ics_no'],
            $form['entity_name'],
            $form['fund_cluster'],
            $form['item_count'],
            $form['total_value'],
            date('Y-m-d', strtotime($form['created_at']))
        ]);
    }
    
    fclose($output);
}

exit();
?>
