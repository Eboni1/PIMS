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

// Log property card access
logSystemAction($_SESSION['user_id'], 'access', 'property_card', 'User accessed Property Card page');

// Get asset items with PAR ID
$asset_items = [];
if ($conn && !$conn->connect_error) {
    try {
        // Simple query first to test
        $query = "SELECT 
                    ai.id,
                    ai.created_at,
                    ai.property_no,
                    ai.description,
                    ai.value,
                    ai.par_id,
                    ai.employee_id
                  FROM asset_items ai
                  WHERE ai.par_id IS NOT NULL AND ai.par_id != ''
                  ORDER BY ai.created_at ASC";
        
        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add employee and PAR info separately
                $row['employee_name'] = '';
                $row['employee_no'] = '';
                $row['par_no'] = '';
                
                // Get employee info
                if (!empty($row['employee_id'])) {
                    $emp_query = "SELECT CONCAT(firstname, ' ', lastname) as name, employee_no FROM employees WHERE id = " . intval($row['employee_id']);
                    $emp_result = $conn->query($emp_query);
                    if ($emp_result && $emp_data = $emp_result->fetch_assoc()) {
                        $row['employee_name'] = $emp_data['name'];
                        $row['employee_no'] = $emp_data['employee_no'];
                    }
                }
                
                // Get PAR info
                if (!empty($row['par_id'])) {
                    $par_query = "SELECT par_no, received_by_name FROM par_forms WHERE id = " . intval($row['par_id']);
                    $par_result = $conn->query($par_query);
                    if ($par_result && $par_data = $par_result->fetch_assoc()) {
                        $row['par_no'] = $par_data['par_no'];
                        $row['received_by'] = $par_data['received_by_name'];
                    }
                }
                
                $asset_items[] = $row;
            }
        }
    } catch (Exception $e) {
        // Error handling - could add user feedback here
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Card - PIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
        
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #191BA9;
        }
        
        .property-card-table {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-custom thead th {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-custom thead th:first-child {
            border-top-left-radius: 12px;
        }
        
        .table-custom thead th:last-child {
            border-top-right-radius: 12px;
        }
        
        .table-custom tbody td {
            padding: 0.875rem 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .table-custom tbody tr:hover {
            background: rgba(25, 27, 169, 0.02);
        }
        
        .property-no {
            font-weight: 600;
            color: #191BA9;
            font-family: 'Courier New', monospace;
        }
        
        .par-reference {
            background: rgba(25, 27, 169, 0.1);
            color: #191BA9;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .employee-name {
            font-weight: 500;
            color: #212529;
        }
        
        .employee-no {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .quantity-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }
        
        .value-cell {
            font-weight: 600;
            color: #212529;
            text-align: right;
        }
        
        .balance-qty {
            font-weight: 600;
            color: #fd7e14;
            text-align: center;
        }
        
        .date-cell {
            font-size: 0.85rem;
            color: #6c757d;
            white-space: nowrap;
        }
        
        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .description-cell:hover {
            white-space: normal;
            overflow: visible;
            text-overflow: initial;
        }
        
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            flex: 1;
            min-width: 150px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        @media (max-width: 768px) {
            .table-custom {
                font-size: 0.8rem;
            }
            
            .table-custom thead th,
            .table-custom tbody td {
                padding: 0.5rem 0.25rem;
            }
            
            .description-cell {
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
    <?php $page_title = 'Property Card'; ?>
    <div class="main-wrapper" id="mainWrapper">
        <?php require_once 'includes/sidebar-toggle.php'; ?>
        <?php require_once 'includes/sidebar.php'; ?>
        <?php require_once 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2" style="font-weight: 700; color: #191BA9;">
                        <i class="bi bi-credit-card me-2"></i>Property Card
                    </h1>
                    <p class="text-muted mb-0">View all asset items with Property Acknowledgment Receipt (PAR) references</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn export-btn" onclick="exportToCSV()">
                        <i class="bi bi-download me-1"></i> Export to CSV
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (empty($asset_items)): ?>
            <div class="property-card-table">
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #adb5bd;"></i>
                    <h4 class="mt-3 text-muted">No Property Items Found</h4>
                    <p class="text-muted">There are no asset items with PAR references in the system.</p>
                    <a href="par_form.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Create PAR Form
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Statistics Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($asset_items); ?></div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format(array_sum(array_column($asset_items, 'value')), 2); ?></div>
                    <div class="stat-label">Total Value (₱)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count(array_unique(array_column($asset_items, 'par_id'))); ?></div>
                    <div class="stat-label">PAR Forms</div>
                </div>
            </div>
            
            <div class="property-card-table">
                <div class="table-responsive">
                    <table class="table table-custom" id="propertyCardTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Property No.</th>
                                <th>Description</th>
                                <th>Employee</th>
                                <th>Receipt</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Total Value</th>
                                <th>Balance Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $balance_counter = [];
                            foreach ($asset_items as $index => $item): 
                                // Initialize balance counter for each property number
                                $property_key = $item['property_no'];
                                if (!isset($balance_counter[$property_key])) {
                                    $balance_counter[$property_key] = 0;
                                }
                                $balance_counter[$property_key]++;
                            ?>
                                <tr>
                                    <td class="date-cell">
                                        <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                    </td>
                                    <td>
                                        <span class="property-no"><?php echo htmlspecialchars($item['property_no']); ?></span>
                                    </td>
                                    <td class="description-cell" title="<?php echo htmlspecialchars($item['description']); ?>">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </td>
                                    <td>
                                        <?php if ($item['employee_name']): ?>
                                            <div class="employee-info">
                                                <span class="employee-name"><?php echo htmlspecialchars($item['employee_name']); ?></span>
                                                <span class="employee-no"><?php echo htmlspecialchars($item['employee_no']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['par_no']): ?>
                                            <span class="par-reference"><?php echo htmlspecialchars($item['par_no']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">PAR-<?php echo str_pad($item['par_id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="quantity-badge">1</span>
                                    </td>
                                    <td class="value-cell">
                                        ₱<?php echo number_format($item['value'], 2); ?>
                                    </td>
                                    <td class="value-cell">
                                        ₱<?php echo number_format($item['value'], 2); ?>
                                    </td>
                                    <td class="balance-qty">
                                        <?php echo $balance_counter[$property_key]; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php require_once 'includes/logout-modal.php'; ?>
    <?php require_once 'includes/change-password-modal.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        function exportToCSV() {
            const table = document.getElementById('propertyCardTable');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            // Add headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));
            
            // Add data rows
            table.querySelectorAll('tbody tr').forEach(tr => {
                const rowData = [];
                tr.querySelectorAll('td').forEach(td => {
                    let cellText = td.textContent.trim();
                    // Remove currency symbols and extra spaces
                    cellText = cellText.replace(/₱/g, '').replace(/\s+/g, ' ');
                    // Escape commas in text
                    if (cellText.includes(',')) {
                        cellText = `"${cellText}"`;
                    }
                    rowData.push(cellText);
                });
                csv.push(rowData.join(','));
            });
            
            // Create and download CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'property_card_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Auto-refresh every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
