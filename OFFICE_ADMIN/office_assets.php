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

// Check if user has correct role (office_admin or admin)
if ($_SESSION['role'] !== 'office_admin' && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../config.php';
require_once '../includes/logger.php';

// Log office assets page access
logSystemAction($_SESSION['user_id'], 'access', 'office_assets', 'Office admin accessed office assets page');

// Handle form submissions
$message = '';
$message_type = '';

// Get office assets
$assets = [];
try {
    $office_id = $_SESSION['office_id'];
    $stmt = $conn->prepare("
        SELECT ai.*, a.description as asset_description, ac.name as category_name, 
               e.firstname, e.lastname, o.office_name 
        FROM asset_items ai 
        LEFT JOIN assets a ON ai.asset_id = a.id 
        LEFT JOIN asset_categories ac ON a.asset_categories_id = ac.id 
        LEFT JOIN employees e ON ai.employee_id = e.id 
        LEFT JOIN offices o ON ai.office_id = o.id 
        WHERE ai.office_id = ? 
        ORDER BY ai.last_updated DESC
    ");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $assets[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching assets: " . $e->getMessage());
}

// Get asset statistics
$stats = [];
try {
    $office_id = $_SESSION['office_id'];
    
    // Total assets
    $stmt = $conn->prepare("SELECT COUNT(*) as total_assets FROM asset_items WHERE office_id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset_stats = $result->fetch_assoc();
    $stats['total_assets'] = $asset_stats['total_assets'];
    $stmt->close();
    
    // Status distribution
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM asset_items WHERE office_id = ? GROUP BY status");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['status'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['status'][$row['status']] = $row['count'];
    }
    $stmt->close();
    
    // Total value
    $stmt = $conn->prepare("SELECT SUM(value) as total_value FROM asset_items WHERE office_id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $value_stats = $result->fetch_assoc();
    $stats['total_value'] = $value_stats['total_value'] ?? 0;
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error fetching asset stats: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Assets - PIMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
        
        .asset-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(25, 27, 169, 0.1);
            margin-bottom: 1rem;
        }
        
        .asset-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-weight: 600;
        }
        
        .status-available {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .status-in_use {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            color: white;
        }
        
        .status-maintenance {
            background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            color: white;
        }
        
        .status-disposed {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-weight: 600;
            background: linear-gradient(135deg, #5CC2F2 0%, #C1EAF2 100%);
            color: var(--dark-color);
        }
        
        .stats-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .form-control {
            background: var(--light-color);
            border: 2px solid var(--accent-color);
            border-radius: var(--border-radius-lg);
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 27, 169, 0.25);
            outline: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: var(--border-radius-lg);
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(25, 27, 169, 0.3);
        }
        
        /* Custom scrollbar for webkit browsers */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .main-content::-webkit-scrollbar-track {
            background: rgba(25, 27, 169, 0.1);
            border-radius: 4px;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            border-radius: 4px;
        }
        
        .main-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5CC2F2 0%, #191BA9 100%);
        }
    </style>
</head>
<body>
<?php
// Set page title for topbar
$page_title = 'Office Assets';
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
                        <i class="bi bi-box-seam"></i> Office Assets
                    </h1>
                    <p class="text-muted mb-0">Manage and monitor office assets and equipment</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-info btn-sm" onclick="exportAssets()">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Asset Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $stats['total_assets'] ?? 0; ?></div>
                            <div class="text-muted">Total Assets</div>
                            <small class="text-success">
                                <i class="bi bi-box"></i> 
                                All Items
                            </small>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $stats['status']['available'] ?? 0; ?></div>
                            <div class="text-muted">Available</div>
                            <small class="text-success">Ready for Use</small>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $stats['status']['in_use'] ?? 0; ?></div>
                            <div class="text-muted">In Use</div>
                            <small class="text-info">Currently Assigned</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number">₱<?php echo number_format($stats['total_value'] ?? 0, 2); ?></div>
                            <div class="text-muted">Total Value</div>
                            <small class="text-warning">Asset Worth</small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-currency-peso fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Assets Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white rounded-top-4">
                        <h6 class="mb-0"><i class="bi bi-box-seam"></i> Office Assets Inventory</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="assetsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset Details</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Value</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assets)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="bi bi-box-seam fs-1 text-muted"></i>
                                                <p class="text-muted mt-3">No assets found in this office</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($assets as $asset): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-box fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($asset['description']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($asset['asset_description'] ?? 'N/A'); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="category-badge">
                                                        <?php echo htmlspecialchars($asset['category_name'] ?? 'Uncategorized'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $asset['status']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $asset['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($asset['employee_id']): ?>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($asset['firstname'] . ' ' . $asset['lastname']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($asset['end_user'] ?? 'N/A'); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Unassigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong>₱<?php echo number_format($asset['value'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i>
                                                        <?php echo date('M j, Y', strtotime($asset['last_updated'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn" onclick="viewAsset(<?php echo $asset['id']; ?>)" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-warning action-btn" onclick="editAsset(<?php echo $asset['id']; ?>)" title="Edit Asset">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once '../SYSTEM_ADMIN/includes/logout-modal.php'; ?>
    <?php require_once '../SYSTEM_ADMIN/includes/change-password-modal.php'; ?>
    
    <!-- Add Asset Modal -->
    <div class="modal fade" id="addAssetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_asset">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Asset Description</label>
                                    <input type="text" class="form-control" id="description" name="description" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        try {
                                            $cat_stmt = $conn->prepare("SELECT id, name FROM asset_categories ORDER BY name");
                                            $cat_stmt->execute();
                                            $cat_result = $cat_stmt->get_result();
                                            
                                            while ($category = $cat_result->fetch_assoc()) {
                                                echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                                            }
                                            $cat_stmt->close();
                                        } catch (Exception $e) {
                                            echo "<option value=''>Error loading categories</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="available">Available</option>
                                        <option value="in_use">In Use</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="disposed">Disposed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="value" class="form-label">Value (₱)</label>
                                    <input type="number" class="form-control" id="value" name="value" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit</label>
                                    <input type="text" class="form-control" id="unit" name="unit" placeholder="e.g., pcs, units, sets">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="acquisition_date" class="form-label">Acquisition Date</label>
                                    <input type="date" class="form-control" id="acquisition_date" name="acquisition_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[5, 'desc']], // Sort by last updated descending
                language: {
                    search: "Search assets:",
                    lengthMenu: "Show _MENU_ assets",
                    info: "Showing _START_ to _END_ of _TOTAL_ assets",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
        
        // View asset details
        function viewAsset(assetId) {
            // For now, just show an alert. In a real implementation, 
            // this would open a modal with asset details
            alert('View asset details for ID: ' + assetId);
        }
        
        // Edit asset
        function editAsset(assetId) {
            // For now, just show an alert. In a real implementation,
            // this would open an edit modal with asset data
            alert('Edit asset with ID: ' + assetId);
        }
        
        // Export assets
        function exportAssets() {
            // For now, just show an alert. In a real implementation,
            // this would generate and download an Excel/CSV file
            alert('Export assets functionality would be implemented here');
        }
        
        // Clear form on add modal close
        document.getElementById('addAssetModal').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });
    </script>
</body>
</html>