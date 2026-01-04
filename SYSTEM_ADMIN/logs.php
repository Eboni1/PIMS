<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Check if user has correct role
if ($_SESSION['role'] !== 'system_admin' && $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config.php';

// Function to log system actions
function logSystemAction($user_id, $action, $module, $details = null) {
    global $conn;
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $conn->prepare("
            INSERT INTO system_logs (user_id, action, module, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssss", $user_id, $action, $module, $details, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log system action: " . $e->getMessage());
        return false;
    }
}

// Get logs with filters
$logs = [];
$total_logs = 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filter parameters
$filter_user = isset($_GET['filter_user']) ? $_GET['filter_user'] : '';
$filter_action = isset($_GET['filter_action']) ? $_GET['filter_action'] : '';
$filter_module = isset($_GET['filter_module']) ? $_GET['filter_module'] : '';
$filter_date_from = isset($_GET['filter_date_from']) ? $_GET['filter_date_from'] : '';
$filter_date_to = isset($_GET['filter_date_to']) ? $_GET['filter_date_to'] : '';

try {
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($filter_user)) {
        $where_conditions[] = "sl.user_id = ?";
        $params[] = $filter_user;
        $types .= 'i';
    }
    
    if (!empty($filter_action)) {
        $where_conditions[] = "sl.action LIKE ?";
        $params[] = '%' . $filter_action . '%';
        $types .= 's';
    }
    
    if (!empty($filter_module)) {
        $where_conditions[] = "sl.module = ?";
        $params[] = $filter_module;
        $types .= 's';
    }
    
    if (!empty($filter_date_from)) {
        $where_conditions[] = "DATE(sl.created_at) >= ?";
        $params[] = $filter_date_from;
        $types .= 's';
    }
    
    if (!empty($filter_date_to)) {
        $where_conditions[] = "DATE(sl.created_at) <= ?";
        $params[] = $filter_date_to;
        $types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM system_logs sl LEFT JOIN users u ON sl.user_id = u.id $where_clause";
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_logs = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    // Get logs with pagination
    $sql = "
        SELECT sl.*, u.first_name, u.last_name, u.username 
        FROM system_logs sl 
        LEFT JOIN users u ON sl.user_id = u.id 
        $where_clause 
        ORDER BY sl.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($sql);
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching logs: " . $e->getMessage());
}

// Get users for filter dropdown
$users = [];
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, username FROM users ORDER BY first_name, last_name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching users: " . $e->getMessage());
}

// Get unique actions for filter
$actions = [];
try {
    $stmt = $conn->prepare("SELECT DISTINCT action FROM system_logs ORDER BY action");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $actions[] = $row['action'];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching actions: " . $e->getMessage());
}

// Get unique modules for filter
$modules = [];
try {
    $stmt = $conn->prepare("SELECT DISTINCT module FROM system_logs ORDER BY module");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row['module'];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching modules: " . $e->getMessage());
}

// Calculate pagination
$total_pages = ceil($total_logs / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - PIMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/index.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F7F3F3 0%, #C1EAF2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: var(--primary-gradient);
            box-shadow: 2px 0 10px rgba(25, 27, 169, 0.1);
            transition: left 0.3s ease-in-out;
            z-index: 1040;
            overflow-y: auto;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav-item {
            display: block;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }
        
        .sidebar-nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-nav-item i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1050;
            background: var(--primary-gradient);
            border: none;
            border-radius: var(--border-radius);
            color: white;
            padding: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }
        
        .sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .sidebar-toggle.sidebar-active {
            left: 300px;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Main content shift when sidebar is active */
        .main-wrapper {
            transition: margin-left 0.3s ease-in-out;
        }
        
        .main-wrapper.sidebar-active {
            margin-left: 280px;
        }
        
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 10px rgba(25, 27, 169, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: padding-left 0.3s ease-in-out;
            padding-left: 80px;
        }
        
        .navbar.sidebar-active {
            padding-left: 20px;
        }
        
        .main-content {
            padding: 2rem;
            max-height: calc(100vh - 76px);
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .page-header {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
        }
        
        .log-entry {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .log-entry:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .log-entry.error {
            border-left-color: #dc3545;
        }
        
        .log-entry.warning {
            border-left-color: #ffc107;
        }
        
        .log-entry.success {
            border-left-color: #28a745;
        }
        
        .log-entry.info {
            border-left-color: #17a2b8;
        }
        
        .action-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .filter-section {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        
        .search-box {
            background: white;
            border: 2px solid var(--accent-color);
            border-radius: var(--border-radius-lg);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }
        
        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 27, 169, 0.25);
            outline: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                max-height: calc(100vh - 60px);
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
            
            .main-wrapper.sidebar-active {
                margin-left: 0;
            }
            
            .navbar.sidebar-active {
                padding-left: 80px;
            }
            
            .sidebar-toggle.sidebar-active {
                left: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list fs-5"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>
                <i class="bi bi-box-seam"></i>
                PIMS Navigation
            </h3>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-nav-item">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="user_management.php" class="sidebar-nav-item">
                <i class="bi bi-people"></i>
                User Management
            </a>
            <a href="logs.php" class="sidebar-nav-item active">
                <i class="bi bi-clock-history"></i>
                System Logs
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-box"></i>
                Inventory Management
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-tags"></i>
                Categories
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-arrow-left-right"></i>
                Transactions
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-file-text"></i>
                Reports
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-gear"></i>
                System Settings
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-shield-exclamation"></i>
                Security Audit
            </a>
            <a href="#" class="sidebar-nav-item">
                <i class="bi bi-cloud-download"></i>
                Backup System
            </a>
            <div class="sidebar-nav-item" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem;">
                <i class="bi bi-box-arrow-right"></i>
                <a href="../logout.php" onclick="event.preventDefault(); confirmLogout();" style="color: inherit; text-decoration: none;">Logout</a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content Wrapper -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark" id="mainNavbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <img src="../img/trans_logo.png" alt="PIMS Logo" style="max-height: 30px; border-radius: 4px; margin-right: 10px;">
                    PILAR INVENTORY MANAGEMENT SYSTEM
                </a>
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i> 
                        <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?> 
                        <span class="badge bg-warning text-dark ms-2"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></span>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </nav>
    
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="bi bi-clock-history"></i> System Logs
                        </h1>
                        <p class="text-muted mb-0">Monitor system activities and audit trail</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                                <i class="bi bi-trash"></i> Clear Logs
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="exportLogs()">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_user" class="form-label">User</label>
                        <select class="form-control" id="filter_user" name="filter_user">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $filter_user == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_action" class="form-label">Action</label>
                        <select class="form-control" id="filter_action" name="filter_action">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo $action; ?>" <?php echo $filter_action == $action ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_module" class="form-label">Module</label>
                        <select class="form-control" id="filter_module" name="filter_module">
                            <option value="">All Modules</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module; ?>" <?php echo $filter_module == $module ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($module)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filter_date_from" name="filter_date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="filter_date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filter_date_to" name="filter_date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Logs Display -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-header bg-primary text-white rounded-top-4">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history"></i> System Logs 
                                <span class="badge bg-light text-dark ms-2"><?php echo number_format($total_logs); ?> Total</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($logs)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                                    <p class="text-muted mt-3">No logs found</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                    $logClass = 'info';
                                    if (strpos($log['action'], 'delete') !== false || strpos($log['action'], 'error') !== false) {
                                        $logClass = 'error';
                                    } elseif (strpos($log['action'], 'update') !== false || strpos($log['action'], 'edit') !== false) {
                                        $logClass = 'warning';
                                    } elseif (strpos($log['action'], 'create') !== false || strpos($log['action'], 'add') !== false || strpos($log['action'], 'login') !== false) {
                                        $logClass = 'success';
                                    }
                                    ?>
                                    <div class="log-entry <?php echo $logClass; ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="action-badge bg-<?php echo $logClass === 'error' ? 'danger' : ($logClass === 'warning' ? 'warning' : ($logClass === 'success' ? 'success' : 'info')); ?> text-white me-2">
                                                        <?php echo htmlspecialchars($log['action']); ?>
                                                    </span>
                                                    <span class="badge bg-secondary me-2">
                                                        <?php echo htmlspecialchars(ucfirst($log['module'])); ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y H:i:s', strtotime($log['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <strong>User:</strong> 
                                                    <?php 
                                                    if ($log['user_id']) {
                                                        echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name'] . ' (@' . $log['username'] . ')');
                                                    } else {
                                                        echo 'System';
                                                    }
                                                    ?>
                                                </div>
                                                <?php if (!empty($log['details'])): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <strong>Details:</strong> <?php echo htmlspecialchars($log['details']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <strong>IP:</strong> <?php echo htmlspecialchars($log['ip_address']); ?> | 
                                                        <strong>Agent:</strong> <?php echo htmlspecialchars(substr($log['user_agent'], 0, 50)) . '...'; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(<?php echo $log['id']; ?>)">
                                                        <i class="bi bi-eye"></i> Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <nav aria-label="Logs pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($filter_user) ? '&filter_user=' . urlencode($filter_user) : ''; ?><?php echo !empty($filter_action) ? '&filter_action=' . urlencode($filter_action) : ''; ?><?php echo !empty($filter_module) ? '&filter_module=' . urlencode($filter_module) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filter_user) ? '&filter_user=' . urlencode($filter_user) : ''; ?><?php echo !empty($filter_action) ? '&filter_action=' . urlencode($filter_action) : ''; ?><?php echo !empty($filter_module) ? '&filter_module=' . urlencode($filter_module) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($filter_user) ? '&filter_user=' . urlencode($filter_user) : ''; ?><?php echo !empty($filter_action) ? '&filter_action=' . urlencode($filter_action) : ''; ?><?php echo !empty($filter_module) ? '&filter_module=' . urlencode($filter_module) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Logout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-box-arrow-right fs-1 text-warning"></i>
                    </div>
                    <h6 class="text-center mb-3">Are you sure you want to logout?</h6>
                    <p class="text-muted text-center mb-0">
                        You will be logged out of the Pilar Inventory Management System. Any unsaved work will be lost.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <a href="../logout.php" class="btn btn-warning">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle Functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainWrapper = document.getElementById('mainWrapper');
        const mainNavbar = document.getElementById('mainNavbar');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            mainWrapper.classList.toggle('sidebar-active');
            sidebarToggle.classList.toggle('sidebar-active');
        }
        
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            mainWrapper.classList.remove('sidebar-active');
            sidebarToggle.classList.remove('sidebar-active');
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
        
        // Logout confirmation
        function confirmLogout() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }
        
        // Update all logout links to use confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLinks = document.querySelectorAll('a[href="../logout.php"]');
            logoutLinks.forEach(link => {
                // Don't intercept the logout button inside the modal
                if (!link.closest('#logoutModal')) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        confirmLogout();
                    });
                }
            });
        });
        
        // View log details
        function viewLogDetails(logId) {
            // This would open a modal with full log details
            alert('Log details view would open here for log ID: ' + logId);
        }
        
        // Clear logs
        function clearLogs() {
            if (confirm('Are you sure you want to clear all system logs? This action cannot be undone.')) {
                // This would send a request to clear logs
                alert('Clear logs functionality would be implemented here');
            }
        }
        
        // Export logs
        function exportLogs() {
            // This would export logs to CSV or other format
            alert('Export logs functionality would be implemented here');
        }
        
        // Auto-refresh logs every 30 seconds
        setInterval(function() {
            console.log('Auto-refreshing logs...');
            // In production, this would fetch updated data via AJAX
        }, 30000);
    </script>
</body>
</html>
