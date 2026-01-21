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

// Log employees page access
logSystemAction($_SESSION['user_id'], 'access', 'employees', 'Admin accessed employees page');

// Handle filter parameters
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';
$office_filter = isset($_GET['office']) ? intval($_GET['office']) : 0;
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$clearance_filter = isset($_GET['clearance']) ? trim($_GET['clearance']) : '';

// Get employees with filters
$employees = [];
try {
    $sql = "SELECT e.*, o.office_name 
            FROM employees e 
            LEFT JOIN offices o ON e.office_id = o.id 
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($search_filter !== '') {
        $sql .= " AND (e.firstname LIKE ? OR e.lastname LIKE ? OR e.email LIKE ? OR e.employee_no LIKE ?)";
        $searchParam = "%{$search_filter}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        $types .= 'ssss';
    }
    
    if ($office_filter > 0) {
        $sql .= " AND e.office_id = ?";
        $params[] = $office_filter;
        $types .= 'i';
    }
    
    if ($status_filter !== '') {
        $sql .= " AND e.employment_status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    if ($clearance_filter !== '') {
        $sql .= " AND e.clearance_status = ?";
        $params[] = $clearance_filter;
        $types .= 's';
    }
    
    $sql .= " ORDER BY e.lastname, e.firstname";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
} catch (Exception $e) {
    error_log("Error fetching employees: " . $e->getMessage());
    $employees = [];
}

// Get offices for filter dropdown
$offices = [];
try {
    $result = $conn->query("SELECT id, office_name FROM offices WHERE status = 'active' ORDER BY office_name");
    while ($row = $result->fetch_assoc()) {
        $offices[] = $row;
    }
} catch (Exception $e) {
    $offices = [];
}

// Calculate statistics
$stats = [
    'total_employees' => count($employees),
    'permanent_employees' => 0,
    'cleared_employees' => 0,
    'uncleared_employees' => 0
];

foreach ($employees as $emp) {
    if ($emp['employment_status'] === 'permanent') {
        $stats['permanent_employees']++;
    }
    if ($emp['clearance_status'] === 'cleared') {
        $stats['cleared_employees']++;
    } elseif ($emp['clearance_status'] === 'uncleared') {
        $stats['uncleared_employees']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - PIMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/index.css" rel="stylesheet">
    <link href="../assets/css/theme-custom.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F7F3F3 0%, #C1EAF2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .stats-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-permanent { background: #d4edda; color: #155724; }
        .status-contractual { background: #cce5ff; color: #004085; }
        .status-job_order { background: #fff3cd; color: #856404; }
        .status-resigned { background: #f8d7da; color: #721c24; }
        .status-retired { background: #e2e3e5; color: #383d41; }
        
        .clearance-cleared { background: #d4edda; color: #155724; }
        .clearance-uncleared { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php
    // Set page title for topbar
    $page_title = 'Employees';
    ?>
    <!-- Main Content Wrapper -->
    <div class="main-wrapper" id="mainWrapper">
        <?php require_once 'includes/sidebar-toggle.php'; ?>
        <?php require_once 'includes/sidebar.php'; ?>
        <?php require_once 'includes/topbar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="bi bi-people"></i> Employees
                    </h1>
                    <p class="text-muted mb-0">Manage employee records and clearance status</p>
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> mt-2" role="alert">
                            <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-primary" onclick="addEmployee()">
                        <i class="bi bi-plus-circle"></i> Add Employee
                    </button>
                    <button class="btn btn-success btn-sm ms-2" onclick="exportEmployees()">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_employees']; ?></div>
                    <div class="stats-label"><i class="bi bi-people"></i> Total Employees</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['permanent_employees']; ?></div>
                    <div class="stats-label"><i class="bi bi-person-badge"></i> Permanent Employees</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['cleared_employees']; ?></div>
                    <div class="stats-label"><i class="bi bi-shield-check"></i> Cleared Employees</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['uncleared_employees']; ?></div>
                    <div class="stats-label"><i class="bi bi-shield-x"></i> Uncleared Employees</div>
                </div>
            </div>
        </div>
        
        <!-- Employees Table -->
        <div class="table-container">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Employee Records</h5>
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="officeFilter">
                                <option value="">All Offices</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value="<?php echo $office['id']; ?>" <?php echo $office_filter == $office['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($office['office_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="permanent" <?php echo $status_filter == 'permanent' ? 'selected' : ''; ?>>Permanent</option>
                                <option value="contractual" <?php echo $status_filter == 'contractual' ? 'selected' : ''; ?>>Contractual</option>
                                <option value="job_order" <?php echo $status_filter == 'job_order' ? 'selected' : ''; ?>>Job Order</option>
                                <option value="resigned" <?php echo $status_filter == 'resigned' ? 'selected' : ''; ?>>Resigned</option>
                                <option value="retired" <?php echo $status_filter == 'retired' ? 'selected' : ''; ?>>Retired</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="clearanceFilter">
                                <option value="">All Clearance</option>
                                <option value="cleared" <?php echo $clearance_filter == 'cleared' ? 'selected' : ''; ?>>Cleared</option>
                                <option value="uncleared" <?php echo $clearance_filter == 'uncleared' ? 'selected' : ''; ?>>Uncleared</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="employeesTable">
                    <thead>
                        <tr>
                            <th>Employee No.</th>
                            <th>Name</th>
                            <th>Office</th>
                            <th>Employment Status</th>
                            <th>Clearance Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['employee_no'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?>
                                        <?php if (!empty($employee['email'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($employee['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($employee['office_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status = $employee['employment_status'] ?? 'permanent';
                                        switch($status) {
                                            case 'permanent':
                                                $status_class = 'status-permanent';
                                                break;
                                            case 'contractual':
                                                $status_class = 'status-contractual';
                                                break;
                                            case 'job_order':
                                                $status_class = 'status-job_order';
                                                break;
                                            case 'resigned':
                                                $status_class = 'status-resigned';
                                                break;
                                            case 'retired':
                                                $status_class = 'status-retired';
                                                break;
                                            default:
                                                $status_class = 'status-permanent';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $clearance_class = '';
                                        $clearance = $employee['clearance_status'] ?? 'uncleared';
                                        switch($clearance) {
                                            case 'cleared':
                                                $clearance_class = 'clearance-cleared';
                                                break;
                                            case 'uncleared':
                                                $clearance_class = 'clearance-uncleared';
                                                break;
                                            default:
                                                $clearance_class = 'clearance-uncleared';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $clearance_class; ?>">
                                            <?php echo ucfirst($clearance); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" onclick="viewEmployee(<?php echo $employee['id']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="editEmployee(<?php echo $employee['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-people fs-1"></i>
                                    <p class="mt-2">No employees found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    </div> <!-- Close main-wrapper -->
    
    <?php require_once 'includes/logout-modal.php'; ?>
    <?php require_once 'includes/change-password-modal.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <?php require_once 'includes/sidebar-scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#employeesTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'asc']], // Sort by employee number by default
                searching: true,
                language: {
                    lengthMenu: "Show _MENU_ employees per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ employees",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No employees found."
                },
                columnDefs: [
                    {
                        targets: [5], // Actions column
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Initialize search from URL parameter
            var initialSearch = '<?php echo htmlspecialchars($search_filter); ?>';
            if (initialSearch !== '') {
                table.search(initialSearch).draw();
            }
            
            // Initialize filters from URL parameters
            var initialOffice = '<?php echo $office_filter; ?>';
            var initialStatus = '<?php echo htmlspecialchars($status_filter); ?>';
            var initialClearance = '<?php echo htmlspecialchars($clearance_filter); ?>';
            
            if (initialOffice !== '0') {
                // Get the office name from the dropdown
                var officeName = $('#officeFilter option[value="' + initialOffice + '"]').text();
                table.column(2).search(officeName, true, false).draw();
            }
            if (initialStatus !== '') {
                table.column(3).search(initialStatus, true, false).draw();
            }
            if (initialClearance !== '') {
                table.column(4).search(initialClearance, true, false).draw();
            }
            
            // Filter functionality
            $('#officeFilter, #statusFilter, #clearanceFilter').on('change', function() {
                // Get filter values
                var office = $('#officeFilter').val();
                var status = $('#statusFilter').val();
                var clearance = $('#clearanceFilter').val();
                
                // Apply filters using DataTables API with regex
                if (office) {
                    // Get the office name from the dropdown option text
                    var officeName = $('#officeFilter option:selected').text();
                    table.column(2).search(officeName, true, false).draw();
                } else {
                    table.column(2).search('').draw();
                }
                
                if (status) {
                    table.column(3).search(status, true, false).draw();
                } else {
                    table.column(3).search('').draw();
                }
                
                if (clearance) {
                    table.column(4).search(clearance, true, false).draw();
                } else {
                    table.column(4).search('').draw();
                }
            });
        });

        // Employee management functions
        function addEmployee() {
            // TODO: Implement add employee modal or redirect
            alert('Add employee functionality will be implemented');
        }
        
        function viewEmployee(id) {
            // TODO: Implement view employee modal
            alert('View employee ID: ' + id);
        }
        
        function editEmployee(id) {
            // TODO: Implement edit employee modal
            alert('Edit employee ID: ' + id);
        }
        
        // Export employees function
        function exportEmployees() {
            const table = $('#employeesTable').DataTable();
            let csv = 'Employee No,Name,Email,Phone,Office,Position,Employment Status,Clearance Status,Created At\n';
            
            const data = table.data().toArray();
            for (let row of data) {
                const rowData = [
                    row[0], // Employee No
                    row[1], // Name
                    '', // Email (extracted separately)
                    '', // Phone (not displayed in table)
                    row[2], // Office
                    '', // Position (not displayed in table)
                    row[3], // Employment Status
                    row[4], // Clearance Status
                    '' // Created At (not displayed in table)
                ];
                csv += rowData.map(cell => `"${cell}"`).join(',') + '\n';
            }
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `employees_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
