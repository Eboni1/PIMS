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

// Get RIS ID from URL
$ris_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ris_id <= 0) {
    header('Location: ris_entries.php?error=Invalid RIS ID');
    exit();
}

// Get RIS form details
$stmt = $conn->prepare("
    SELECT * FROM ris_forms WHERE id = ?
");
$stmt->bind_param("i", $ris_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: ris_entries.php?error=RIS not found');
    exit();
}

$ris = $result->fetch_assoc();
$stmt->close();

// Get RIS items
$ris_items = [];
$stmt = $conn->prepare("
    SELECT * FROM ris_items WHERE ris_form_id = ? ORDER BY stock_no
");
$stmt->bind_param("i", $ris_id);
$stmt->execute();
$items_result = $stmt->get_result();

while ($row = $items_result->fetch_assoc()) {
    $ris_items[] = $row;
}
$stmt->close();

logSystemAction($_SESSION['user_id'], 'Viewed RIS entry', 'forms', "RIS ID: $ris_id, RIS No: {$ris['ris_no']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View RIS - <?php echo htmlspecialchars($ris['ris_no']); ?> - PIMS</title>
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
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }
        
        .signature-section {
            margin-top: 2rem;
        }
        
        .signature-box {
            border: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 0.5rem;
        }
        
        @media print {
            .no-print { display: none !important; }
            .ris-card { box-shadow: none; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <?php
    // Set page title for topbar
    $page_title = 'View RIS';
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
                        <i class="bi bi-file-earmark-text"></i> View RIS
                    </h1>
                    <p class="text-muted mb-0">RIS No: <?php echo htmlspecialchars($ris['ris_no']); ?></p>
                </div>
                <div class="col-md-4 text-md-end no-print">
                    <a href="ris_entries.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Entries
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- RIS Details -->
        <div class="ris-card">
            <!-- Entity Fields Header -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label"><strong>DIVISION:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['division']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>Responsibility Center:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['responsibility_center']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>RIS NO:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['ris_no']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>DATE:</strong></label>
                    <p class="form-control-plaintext"><?php echo date('M d, Y', strtotime($ris['date'])); ?></p>
                </div>
            </div>
            
            <!-- Entity Fields Values -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label"><strong>OFFICE:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['office']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>Code:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['code']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>SAI NO.:</strong></label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['sai_no']); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>Date:</strong></label>
                    <p class="form-control-plaintext"><?php echo date('M d, Y', strtotime($ris['date_2'])); ?></p>
                </div>
            </div>
                            
            <!-- Items Table -->
            <div class="mb-3">
                <label class="form-label"><strong>Items:</strong></label>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Stock No.</th>
                                <th>Unit</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ris_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['stock_no']); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td><?php echo number_format($item['quantity'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
                            
            <!-- Purpose -->
            <div class="mb-3">
                <label class="form-label"><strong>Purpose:</strong></label>
                <p class="form-control-plaintext"><?php echo htmlspecialchars($ris['purpose']); ?></p>
            </div>
                            
            <!-- Signature Section -->
            <div class="signature-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="signature-box">
                            <label class="form-label"><strong>REQUESTED BY:</strong></label>
                            <div class="signature-line"></div>
                            <p class="mb-2"><strong>PRINTED NAME:</strong> <?php echo htmlspecialchars($ris['requested_by']); ?></p>
                            <p class="mb-2"><strong>DESIGNATION:</strong> <?php echo htmlspecialchars($ris['requested_by_position']); ?></p>
                            <p><strong>DATE:</strong> <?php echo $ris['requested_date'] ? date('M d, Y', strtotime($ris['requested_date'])) : ''; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="signature-box">
                            <label class="form-label"><strong>APPROVED BY:</strong></label>
                            <div class="signature-line"></div>
                            <p class="mb-2"><strong>PRINTED NAME:</strong> <?php echo htmlspecialchars($ris['approved_by']); ?></p>
                            <p class="mb-2"><strong>DESIGNATION:</strong> <?php echo htmlspecialchars($ris['approved_by_position']); ?></p>
                            <p><strong>DATE:</strong> <?php echo $ris['approved_date'] ? date('M d, Y', strtotime($ris['approved_date'])) : ''; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="signature-box">
                            <label class="form-label"><strong>ISSUED BY:</strong></label>
                            <div class="signature-line"></div>
                            <p class="mb-2"><strong>PRINTED NAME:</strong> <?php echo htmlspecialchars($ris['issued_by']); ?></p>
                            <p class="mb-2"><strong>DESIGNATION:</strong> <?php echo htmlspecialchars($ris['issued_by_position']); ?></p>
                            <p><strong>DATE:</strong> <?php echo $ris['issued_date'] ? date('M d, Y', strtotime($ris['issued_date'])) : ''; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="signature-box">
                            <label class="form-label"><strong>RECEIVED BY:</strong></label>
                            <div class="signature-line"></div>
                            <p class="mb-2"><strong>PRINTED NAME:</strong> <?php echo htmlspecialchars($ris['received_by']); ?></p>
                            <p class="mb-2"><strong>DESIGNATION:</strong> <?php echo htmlspecialchars($ris['received_by_position']); ?></p>
                            <p><strong>DATE:</strong> <?php echo $ris['received_date'] ? date('M d, Y', strtotime($ris['received_date'])) : ''; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/logout-modal.php'; ?>
    <?php include 'includes/change-password-modal.php'; ?>
    <?php include 'includes/sidebar-scripts.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
