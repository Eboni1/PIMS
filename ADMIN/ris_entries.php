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

logSystemAction($_SESSION['user_id'], 'Accessed RIS Entries', 'forms', 'ris_entries.php');

// Get all RIS forms with items
$ris_forms = [];
$result = $conn->query("
    SELECT 
        f.*,
        COUNT(i.id) as item_count,
        SUM(i.total_amount) as total_value
    FROM ris_forms f 
    LEFT JOIN ris_items i ON f.id = i.ris_form_id 
    GROUP BY f.id 
    ORDER BY f.created_at DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ris_forms[] = $row;
    }
}

// Get header image from forms table
$header_image = '';
$result = $conn->query("SELECT header_image FROM forms WHERE form_code = 'RIS'");
if ($result && $row = $result->fetch_assoc()) {
    $header_image = $row['header_image'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Entries - PIMS</title>
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
        
        .ris-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border-left: 4px solid #191BA9;
        }
        
        .ris-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .ris-number {
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
        }
        
        @media print {
            .no-print { display: none !important; }
            .ris-card { box-shadow: none; }
        }
    </style>
</head>
<body>
    <?php
    // Set page title for topbar
    $page_title = 'RIS Entries';
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
                        <i class="bi bi-file-earmark-text"></i> RIS Entries
                    </h1>
                    <p class="text-muted mb-0">View and manage Requisition and Issue Slip entries</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search RIS forms..." style="margin-bottom: 10px;">
                    <div>
                        <a href="ris_form.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New RIS
                        </a>
                        <button class="btn btn-outline-success btn-sm ms-2" onclick="exportRISData()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($ris_forms); ?></div>
                    <div class="text-muted">Total RIS Forms</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">
                        <?php 
                        $total_items = array_sum(array_column($ris_forms, 'item_count'));
                        echo $total_items; 
                        ?>
                    </div>
                    <div class="text-muted">Total Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">
                        ₱<?php 
                        $total_value = array_sum(array_column($ris_forms, 'total_value'));
                        echo number_format($total_value, 2); 
                        ?>
                    </div>
                    <div class="text-muted">Total Value</div>
                </div>
            </div>
        </div>

        <!-- RIS Forms List -->
        <div class="row">
            <?php if (empty($ris_forms)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">No RIS Entries Found</h4>
                        <p class="text-muted">Start by creating your first RIS form.</p>
                        <a href="ris_form.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create RIS Form
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($ris_forms as $ris): ?>
                    <div class="col-12">
                        <div class="ris-card">
                            <div class="row align-items-start">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="ris-number">
                                                <i class="bi bi-file-earmark-text"></i> <?php echo htmlspecialchars($ris['ris_no']); ?>
                                            </div>
                                            <h5 class="mb-2"><?php echo htmlspecialchars($ris['division']); ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-building"></i> Office: <?php echo htmlspecialchars($ris['office']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Requested By:</small>
                                            <p class="mb-1"><?php echo htmlspecialchars($ris['requested_by']); ?></p>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($ris['requested_by_position']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Approved By:</small>
                                            <p class="mb-1"><?php echo htmlspecialchars($ris['approved_by']); ?></p>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($ris['approved_by_position']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 text-end">
                                    <div class="mb-3">
                                        <div class="text-muted small">Items Count</div>
                                        <div class="h4"><?php echo $ris['item_count']; ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Total Value</div>
                                        <div class="h4">₱<?php echo number_format($ris['total_value'] ?: 0, 2); ?></div>
                                    </div>
                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($ris['created_at'])); ?>
                                    </div>
                                    <div class="no-print">
                                        <button class="btn btn-sm btn-outline-primary btn-action me-2" onclick="viewRIS(<?php echo $ris['id']; ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-info btn-action" onclick="printRIS(<?php echo $ris['id']; ?>)">
                                            <i class="bi bi-printer"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/logout-modal.php'; ?>
    <?php include 'includes/change-password-modal.php'; ?>
    <?php include 'includes/sidebar-scripts.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewRIS(id) {
            window.open('ris_view.php?id=' + id, '_blank');
        }
        
        function printRIS(id) {
            window.open('print_ris.php?id=' + id, '_blank');
        }
        
        function searchRISForms() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const risCards = document.querySelectorAll('.ris-card');
            
            risCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const risNumber = card.querySelector('.ris-number')?.textContent.toLowerCase() || '';
                const division = card.querySelector('h5')?.textContent.toLowerCase() || '';
                const office = card.querySelector('.text-muted')?.textContent.toLowerCase() || '';
                
                // Check if search term matches any field
                const matches = text.includes(searchTerm) || 
                               risNumber.includes(searchTerm) || 
                               division.includes(searchTerm) || 
                               office.includes(searchTerm);
                
                // Show/hide card based on search
                if (matches || searchTerm === '') {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Add search on input change
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', searchRISForms);
            }
        });
        
        function exportRISData() {
            // Create export modal
            const modalHtml = `
                <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exportModalLabel">
                                    <i class="bi bi-download"></i> Export RIS Entries
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="exportForm" method="POST" action="export_ris.php">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="exportFormat" class="form-label">Export Format</label>
                                        <select class="form-select" id="exportFormat" name="format" required>
                                            <option value="excel">Excel (CSV) - Detailed</option>
                                            <option value="summary">Excel (CSV) - Summary</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dateFrom" class="form-label">Date From (Optional)</label>
                                        <input type="date" class="form-control" id="dateFrom" name="date_from">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dateTo" class="form-label">Date To (Optional)</label>
                                        <input type="date" class="form-control" id="dateTo" name="date_to">
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <small>
                                            <strong>Excel (CSV) - Detailed:</strong> All items with complete details<br>
                                            <strong>Excel (CSV) - Summary:</strong> Summary of RIS forms only
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if present
            const existingModal = document.getElementById('exportModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body and show it
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
            modal.show();
            
            // Handle form submission
            document.getElementById('exportForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const format = formData.get('format');
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
                submitBtn.disabled = true;
                
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'export_ris.php';
                
                // Add form data
                for (const [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
                
                // Reset button after delay
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
                }, 1000);
            });
        }
    </script>
</body>
</html>
