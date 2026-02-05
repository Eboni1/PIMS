<?php
session_start();
require_once '../config.php';
require_once '../includes/logger.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['system_admin', 'admin'])) {
    header('Location: ../index.php');
    exit();
}

// Get filters from URL
$employee_status_filter = isset($_GET['employee_status']) ? $_GET['employee_status'] : '';
$clearance_status_filter = isset($_GET['clearance_status']) ? $_GET['clearance_status'] : '';

// Build WHERE conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($employee_status_filter)) {
    $where_conditions[] = "e.employment_status = ?";
    $params[] = $employee_status_filter;
    $types .= 's';
}

if (!empty($clearance_status_filter)) {
    $where_conditions[] = "e.clearance_status = ?";
    $params[] = $clearance_status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get employee data
$data = [];
$total_count = 0;

$sql = "SELECT e.id, e.employee_no, e.firstname, e.lastname, e.position, 
               e.employment_status, e.clearance_status, e.email, e.phone,
               e.created_at, o.office_name
        FROM employees e 
        LEFT JOIN offices o ON e.office_id = o.id 
        $where_clause
        ORDER BY e.lastname, e.firstname";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_count++;
}
$stmt->close();

// Get system settings for logo
$system_settings = [];
try {
    $stmt = $conn->prepare("SELECT setting_name, setting_value FROM system_settings");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $system_settings[$row['setting_name']] = $row['setting_value'];
    }
    $stmt->close();
} catch (Exception $e) {
    // Fallback to default if database fails
    $system_settings['system_logo'] = '';
    $system_settings['system_name'] = 'PIMS';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($system_settings['system_name']); ?> - Employees Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .print-header {
            text-align: left;
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .print-header img {
            max-width: 200px;
            
            object-fit: contain;
            float: left;
            margin-right: 20px;
        }
        
        .print-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .print-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .gov-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            
        }
        
        .gov-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.2;
        }
        
        .municipality {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .province {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000;
        }
        
        .filters-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .report-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .report-table td {
            font-size: 11px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-permanent { background: #d4edda; color: #155724; }
        .status-contractual { background: #cce5ff; color: #004085; }
        .status-job_order { background: #fff3cd; color: #856404; }
        .status-resigned { background: #f8d7da; color: #721c24; }
        .status-retired { background: #e2e3e5; color: #383d41; }
        .status-cleared { background: #d4edda; color: #155724; }
        .status-uncleared { background: #f8d7da; color: #721c24; }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
            }
            
            .print-header {
                padding: 10px;
            }
            
            .report-table th {
                background: #f0f0f0 !important;
                color: #000 !important;
                border: 1px solid #000;
            }
            
            /* Hide browser print headers and footers */
            @page {
                size: legal landscape;
                margin: 0.5in;
            }
            
            html {
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div style="display: flex; align-items: flex-start; gap: 20px;">
            <!-- Logo on the left -->
            <div style="flex-shrink: 0;">
                <?php 
                if (!empty($system_settings['system_logo'])) {
                    echo '<img src="../' . htmlspecialchars($system_settings['system_logo']) . '" alt="' . htmlspecialchars($system_settings['system_name']) . '" style="max-width: 250px; max-height: 100px;">';
                } else {
                    echo '<img src="../img/system_logo.png" alt="' . htmlspecialchars($system_settings['system_name']) . '" style="max-width: 250px; max-height: 100px;">';
                }
                ?>
            </div>
            
            <!-- Government header on the right -->
            <div style="flex: 1;">
                <div class="gov-header" style="text-align: center; padding: 0;">
                    <div class="gov-title">Republic of the Philippines</div>
                    <div class="municipality">Municipality of Pilar</div>
                    <div class="province">Province of Sorsogon</div>
                    <div class="print-title"><?php echo htmlspecialchars($system_settings['system_name']); ?> - Employees Report</div>
                    <div class="print-subtitle">Generated on <?php echo date('F j, Y g:i A'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($where_conditions)): ?>
        <div class="filters-info">
            <strong>Filters Applied:</strong>
            <?php if (!empty($employee_status_filter)): ?>
                Employment Status: <?php echo ucfirst(str_replace('_', ' ', $employee_status_filter)); ?><br>
            <?php endif; ?>
            
            <?php if (!empty($clearance_status_filter)): ?>
                Clearance Status: <?php echo ucfirst($clearance_status_filter); ?><br>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Employees Report Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th>Employee No</th>
                <th>Name</th>
                <th>Position</th>
                <th>Office</th>
                <th>Employment Status</th>
                <th>Clearance Status</th>
                <th>Date Added</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="8" class="text-center">No employees found matching the criteria</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data as $employee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($employee['employee_no']); ?></td>
                        <td><?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($employee['position'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($employee['office_name'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            $status_class = '';
                            $status = $employee['employment_status'];
                            switch($status) {
                                case 'permanent': $status_class = 'status-permanent'; break;
                                case 'contractual': $status_class = 'status-contractual'; break;
                                case 'job_order': $status_class = 'status-job-order'; break;
                                case 'resigned': $status_class = 'status-resigned'; break;
                                case 'retired': $status_class = 'status-retired'; break;
                                default: $status_class = 'bg-secondary'; break;
                            }
                            echo '<span class="status-badge ' . $status_class . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $clearance_class = '';
                            $clearance = $employee['clearance_status'];
                            switch($clearance) {
                                case 'cleared': $clearance_class = 'status-cleared'; break;
                                case 'uncleared': $clearance_class = 'status-uncleared'; break;
                                default: $clearance_class = 'bg-secondary'; break;
                            }
                            echo '<span class="status-badge ' . $clearance_class . '">' . ucfirst($clearance) . '</span>';
                            ?>
                        </td>
                        <td><?php echo $employee['created_at'] ? date('M j, Y', strtotime($employee['created_at'])) : 'N/A'; ?></td>
                        <td>
                            <?php 
                            $contact = [];
                            if ($employee['email']) $contact[] = htmlspecialchars($employee['email']);
                            if ($employee['phone']) $contact[] = htmlspecialchars($employee['phone']);
                            echo !empty($contact) ? implode('<br>', $contact) : 'N/A';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        
        <?php if (!empty($data)): ?>
            <tfoot>
                <tr>
                    <th colspan="7" class="text-right">Total:</th>
                    <th class="text-right"><?php echo $total_count; ?></th>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
