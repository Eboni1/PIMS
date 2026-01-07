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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/index.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-available { background-color: #d4edda; color: #155724; }
        .status-in_use { background-color: #cce5ff; color: #004085; }
        .status-maintenance { background-color: #fff3cd; color: #856404; }
        .status-pending { background-color: #e2e3e5; color: #383d41; }
        .status-disposed { background-color: #f8d7da; color: #721c24; }
        .asset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-box"></i> Asset Items
                </h1>
                <a href="assets.php" class="btn btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Assets
                </a>
            </div>

            <!-- Asset Header -->
            <div class="asset-header">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="mb-3"><?php echo htmlspecialchars($asset['description']); ?></h2>
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
                            <div class="stats-label">Total Items</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $available_items; ?></div>
                        <div class="stats-label"><i class="bi bi-check-circle"></i> Available</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $in_use_items; ?></div>
                        <div class="stats-label"><i class="bi bi-person"></i> In Use</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $maintenance_items; ?></div>
                        <div class="stats-label"><i class="bi bi-tools"></i> Maintenance</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $pending_items; ?></div>
                        <div class="stats-label"><i class="bi bi-clock"></i> Pending</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $disposed_items; ?></div>
                        <div class="stats-label"><i class="bi bi-trash"></i> Disposed</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Individual Asset Items
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($items)): ?>
                        <div class="table-responsive">
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
                                            <td><?php echo number_format($item['value'], 2); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($item['acquisition_date'])); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($item['last_updated'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="mt-3 text-muted">No individual items found for this asset.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="includes/sidebar-scripts.js"></script>
</body>
</html>
