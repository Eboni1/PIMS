<?php
session_start();
require_once '../config.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['system_admin', 'admin'])) {
    header('Location: ../index.php');
    exit();
}

// Get asset ID from URL
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;

if ($asset_id === 0) {
    header('Location: assets.php');
    exit();
}

// Get asset details
$asset = null;
$asset_sql = "SELECT a.*, ac.category_name, ac.category_code, o.office_name 
              FROM assets a 
              LEFT JOIN asset_categories ac ON a.asset_categories_id = ac.id 
              LEFT JOIN offices o ON a.office_id = o.id 
              WHERE a.id = ?";
$asset_stmt = $conn->prepare($asset_sql);
$asset_stmt->bind_param("i", $asset_id);
$asset_stmt->execute();
$asset_result = $asset_stmt->get_result();
if ($asset_row = $asset_result->fetch_assoc()) {
    $asset = $asset_row;
}
$asset_stmt->close();

if (!$asset) {
    header('Location: assets.php');
    exit();
}

// Get asset items
$items = [];
$items_sql = "SELECT ai.* FROM asset_items ai WHERE ai.asset_id = ? ORDER BY ai.id";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $asset_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
while ($item_row = $items_result->fetch_assoc()) {
    $items[] = $item_row;
}
$items_stmt->close();

// Calculate statistics
$total_items = count($items);
$available_items = count(array_filter($items, function($item) { return $item['status'] === 'available'; }));
$in_use_items = count(array_filter($items, function($item) { return $item['status'] === 'in_use'; }));
$maintenance_items = count(array_filter($items, function($item) { return $item['status'] === 'maintenance'; }));
$pending_items = count(array_filter($items, function($item) { return $item['status'] === 'pending'; }));
$disposed_items = count(array_filter($items, function($item) { return $item['status'] === 'disposed'; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Items - <?php echo htmlspecialchars($asset['description']); ?> | PIMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
        
        .page-header {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(25, 27, 169, 0.3);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.125rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-available { background-color: #d4edda; color: #155724; }
        .status-in_use { background-color: #cce5ff; color: #004085; }
        .status-maintenance { background-color: #fff3cd; color: #856404; }
        .status-pending { background-color: #e2e3e5; color: #383d41; }
        .status-disposed { background-color: #f8d7da; color: #721c24; }
        
        .text-value {
            font-weight: 600;
            color: #191BA9;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(25, 27, 169, 0.05);
        }
        
        .btn-back {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-lg);
            transition: var(--transition);
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 27, 169, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <?php
    // Set page title for topbar
    $page_title = 'Asset Items - ' . htmlspecialchars($asset['description']);
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
                        <i class="bi bi-box"></i> Asset Items
                    </h1>
                    <p class="text-muted mb-0">Individual items for: <?php echo htmlspecialchars($asset['description']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="assets.php" class="btn btn-back">
                        <i class="bi bi-arrow-left"></i> Back to Assets
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Asset Details Card -->
        <div class="table-container mb-4">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Asset Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($asset['category_code'] . ' - ' . $asset['category_name']); ?></p>
                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($asset['unit']); ?></p>
                            <p><strong>Office:</strong> <?php echo htmlspecialchars($asset['office_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Quantity:</strong> <?php echo $asset['quantity']; ?></p>
                            <p><strong>Unit Cost:</strong> <?php echo number_format($asset['unit_cost'], 2); ?></p>
                            <p><strong>Total Value:</strong> <?php echo number_format($asset['quantity'] * $asset['unit_cost'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $total_items; ?></div>
                        <div class="stats-label"><i class="bi bi-box"></i> Total Items</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $available_items; ?></div>
                    <div class="stats-label"><i class="bi bi-check-circle"></i> Available</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $in_use_items; ?></div>
                    <div class="stats-label"><i class="bi bi-person"></i> In Use</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $maintenance_items; ?></div>
                    <div class="stats-label"><i class="bi bi-tools"></i> Maintenance</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $pending_items; ?></div>
                    <div class="stats-label"><i class="bi bi-clock"></i> Pending</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $disposed_items; ?></div>
                    <div class="stats-label"><i class="bi bi-trash"></i> Disposed</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-container">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Individual Asset Items</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">Showing <?php echo count($items); ?> of <?php echo $total_items; ?> items</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <?php if (!empty($items)): ?>
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item ID</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Value</th>
                                <th>Acquisition Date</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($item['status']) {
                                            case 'available':
                                                $status_class = 'status-available';
                                                break;
                                            case 'in_use':
                                                $status_class = 'status-in_use';
                                                break;
                                            case 'maintenance':
                                                $status_class = 'status-maintenance';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'disposed':
                                                $status_class = 'status-disposed';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-value"><?php echo number_format($item['value'], 2); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['acquisition_date'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['last_updated'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-action" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="mt-3 text-muted">No individual items found for this asset.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    </div> <!-- Close main wrapper -->
    
    <?php require_once 'includes/logout-modal.php'; ?>
    <?php require_once 'includes/change-password-modal.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'includes/sidebar-scripts.php'; ?>
</body>
</html>
