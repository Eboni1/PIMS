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

logSystemAction($_SESSION['user_id'], 'Printed RIS entry', 'forms', "RIS ID: $ris_id, RIS No: {$ris['ris_no']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print RIS - <?php echo htmlspecialchars($ris['ris_no']); ?> - PIMS</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .print-container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .entity-info {
            margin-bottom: 20px;
        }
        
        .entity-row {
            display: flex;
            border: 1px solid #000;
        }
        
        .entity-cell {
            flex: 1;
            padding: 8px;
            border-right: 1px solid #000;
            font-weight: bold;
        }
        
        .entity-cell:last-child {
            border-right: none;
        }
        
        .entity-value {
            border-top: 1px solid #000;
            padding: 8px;
            min-height: 30px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .items-table td:nth-child(1),
        .items-table td:nth-child(2),
        .items-table td:nth-child(4),
        .items-table td:nth-child(5),
        .items-table td:nth-child(6) {
            text-align: center;
        }
        
        .purpose-section {
            margin-bottom: 30px;
        }
        
        .purpose-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .purpose-text {
            border: 1px solid #000;
            padding: 10px;
            min-height: 60px;
        }
        
        .signature-section {
            margin-top: 40px;
        }
        
        .signature-row {
            display: flex;
            margin-bottom: 20px;
        }
        
        .signature-box {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }
        
        .signature-info {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .signature-value {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 3px;
            font-size: 12px;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none !important; }
        }
        
        @page {
            margin: 0.5in;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="form-title">REQUISITION AND ISSUE SLIP</div>
        </div>
        
        <!-- Entity Information -->
        <div class="entity-info">
            <div class="entity-row">
                <div class="entity-cell">DIVISION:</div>
                <div class="entity-cell">Responsibility Center:</div>
                <div class="entity-cell">RIS NO:</div>
                <div class="entity-cell">DATE:</div>
            </div>
            <div class="entity-row">
                <div class="entity-value"><?php echo htmlspecialchars($ris['division']); ?></div>
                <div class="entity-value"><?php echo htmlspecialchars($ris['responsibility_center']); ?></div>
                <div class="entity-value"><?php echo htmlspecialchars($ris['ris_no']); ?></div>
                <div class="entity-value"><?php echo date('m/d/Y', strtotime($ris['date'])); ?></div>
            </div>
            
            <div class="entity-row" style="margin-top: 10px;">
                <div class="entity-cell">OFFICE:</div>
                <div class="entity-cell">Code:</div>
                <div class="entity-cell">SAI NO.:</div>
                <div class="entity-cell">Date:</div>
            </div>
            <div class="entity-row">
                <div class="entity-value"><?php echo htmlspecialchars($ris['office']); ?></div>
                <div class="entity-value"><?php echo htmlspecialchars($ris['code']); ?></div>
                <div class="entity-value"><?php echo htmlspecialchars($ris['sai_no']); ?></div>
                <div class="entity-value"><?php echo date('m/d/Y', strtotime($ris['date_2'])); ?></div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
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
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['total_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Purpose -->
        <div class="purpose-section">
            <div class="purpose-label">Purpose:</div>
            <div class="purpose-text"><?php echo htmlspecialchars($ris['purpose']); ?></div>
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-title">REQUESTED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['requested_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['requested_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="signature-value"><?php echo $ris['requested_date'] ? date('m/d/Y', strtotime($ris['requested_date'])) : 'mm/dd/yyyy'; ?></div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">APPROVED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['approved_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['approved_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="signature-value"><?php echo $ris['approved_date'] ? date('m/d/Y', strtotime($ris['approved_date'])) : 'mm/dd/yyyy'; ?></div>
                </div>
            </div>
            
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-title">ISSUED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['issued_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['issued_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="signature-value"><?php echo $ris['issued_date'] ? date('m/d/Y', strtotime($ris['issued_date'])) : 'mm/dd/yyyy'; ?></div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">RECEIVED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['received_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-value"><?php echo htmlspecialchars($ris['received_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="signature-value"><?php echo $ris['received_date'] ? date('m/d/Y', strtotime($ris['received_date'])) : 'mm/dd/yyyy'; ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
