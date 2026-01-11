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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $format = $_POST['format'] ?? 'excel';
    $date_from = $_POST['date_from'] ?? null;
    $date_to = $_POST['date_to'] ?? null;
    
    try {
        // Build base query
        $where_conditions = [];
        $params = [];
        $types = '';
        
        if ($date_from) {
            $where_conditions[] = "DATE(f.created_at) >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to) {
            $where_conditions[] = "DATE(f.created_at) <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        logSystemAction($_SESSION['user_id'], 'Exported PAR data', 'forms', "Format: $format, Date range: $date_from to $date_to");
        
        if ($format === 'summary') {
            // Export summary of PAR forms only
            $sql = "
                SELECT 
                    f.par_no,
                    f.entity_name,
                    f.fund_cluster,
                    f.office_location,
                    f.received_by_name,
                    f.received_by_position,
                    f.issued_by_name,
                    f.issued_by_position,
                    COUNT(i.id) as item_count,
                    COALESCE(SUM(i.amount), 0) as total_value,
                    f.created_at
                FROM par_forms f 
                LEFT JOIN par_items i ON f.id = i.form_id 
                $where_clause
                GROUP BY f.id 
                ORDER BY f.created_at DESC
            ";
            
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create CSV for summary
            $filename = 'par_summary_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'PAR Number',
                'Entity Name',
                'Fund Cluster',
                'Office Location',
                'Received By',
                'Received By Position',
                'Issued By',
                'Issued By Position',
                'Item Count',
                'Total Value',
                'Created Date'
            ]);
            
            // CSV data
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['par_no'],
                    $row['entity_name'],
                    $row['fund_cluster'],
                    $row['office_location'],
                    $row['received_by_name'],
                    $row['received_by_position'],
                    $row['issued_by_name'],
                    $row['issued_by_position'],
                    $row['item_count'],
                    $row['total_value'],
                    $row['created_at']
                ]);
            }
            
            fclose($output);
            $stmt->close();
            
        } else {
            // Export detailed PAR items
            $sql = "
                SELECT 
                    f.par_no,
                    f.entity_name,
                    f.fund_cluster,
                    f.office_location,
                    f.received_by_name,
                    f.received_by_position,
                    f.issued_by_name,
                    f.issued_by_position,
                    i.quantity,
                    i.unit,
                    i.description,
                    i.property_number,
                    i.date_acquired,
                    i.amount,
                    f.created_at
                FROM par_forms f 
                INNER JOIN par_items i ON f.id = i.form_id 
                $where_clause
                ORDER BY f.created_at DESC, i.id
            ";
            
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create CSV for detailed export
            $filename = 'par_detailed_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'PAR Number',
                'Entity Name',
                'Fund Cluster',
                'Office Location',
                'Received By',
                'Received By Position',
                'Issued By',
                'Issued By Position',
                'Quantity',
                'Unit',
                'Description',
                'Property Number',
                'Date Acquired',
                'Amount',
                'Created Date'
            ]);
            
            // CSV data
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['par_no'],
                    $row['entity_name'],
                    $row['fund_cluster'],
                    $row['office_location'],
                    $row['received_by_name'],
                    $row['received_by_position'],
                    $row['issued_by_name'],
                    $row['issued_by_position'],
                    $row['quantity'],
                    $row['unit'],
                    $row['description'],
                    $row['property_number'],
                    $row['date_acquired'],
                    $row['amount'],
                    $row['created_at']
                ]);
            }
            
            fclose($output);
            $stmt->close();
        }
        
        exit();
        
    } catch (Exception $e) {
        error_log("Error exporting PAR data: " . $e->getMessage());
        $_SESSION['error_message'] = "Error exporting data: " . $e->getMessage();
        header('Location: par_entries.php');
        exit();
    }
} else {
    header('Location: par_entries.php');
    exit();
}
?>
