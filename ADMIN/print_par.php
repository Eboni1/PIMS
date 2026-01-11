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

// Get PAR ID from URL
$par_id = $_GET['id'] ?? 0;
if (empty($par_id)) {
    header('Location: par_entries.php');
    exit();
}

// Get PAR form details
$par_form = null;
$stmt = $conn->prepare("SELECT * FROM par_forms WHERE id = ?");
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $par_form = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "PAR form not found";
    header('Location: par_entries.php');
    exit();
}
$stmt->close();

// Get PAR items
$par_items = [];
$stmt = $conn->prepare("SELECT * FROM par_items WHERE form_id = ? ORDER BY id");
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $par_items[] = $row;
}
$stmt->close();

logSystemAction($_SESSION['user_id'], 'Printed PAR Form', 'forms', "PAR ID: $par_id, PAR No: {$par_form['par_no']}");

// Get header image from forms table
$header_image = '';
$result = $conn->query("SELECT header_image FROM forms WHERE form_code = 'PAR'");
if ($result && $row = $result->fetch_assoc()) {
    $header_image = $row['header_image'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PAR Form - PIMS</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .par-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .par-header h2 {
            margin: 10px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .par-number {
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            border: 2px solid #000;
            display: inline-block;
        }
        
        .form-details {
            margin-bottom: 20px;
        }
        
        .form-details .row {
            margin-bottom: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
        }
        
        .items-table td {
            vertical-align: top;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            width: 45%;
            float: left;
            margin-right: 5%;
        }
        
        .signature-box:last-child {
            margin-right: 0;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin-bottom: 5px;
        }
        
        .clear {
            clear: both;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="par-header">
        <?php 
        if (!empty($header_image)) {
            echo '<div style="margin-bottom: 20px;">';
            echo '<img src="../uploads/forms/' . htmlspecialchars($header_image) . '" alt="Header Image" style="width: 100%; max-height: 100px; object-fit: contain;">';
            echo '</div>';
        }
        ?>
        <h2>PROPERTY ACKNOWLEDGMENT RECEIPT</h2>
        <div class="par-number">
            PAR No: <?php echo htmlspecialchars($par_form['par_no']); ?>
        </div>
    </div>

    <!-- Form Details -->
    <div class="form-details">
        <div class="row">
            <div style="width: 33%; float: left;">
                <strong>Entity Name:</strong><br>
                <?php echo htmlspecialchars($par_form['entity_name']); ?>
            </div>
            <div style="width: 33%; float: left;">
                <strong>Fund Cluster:</strong><br>
                <?php echo htmlspecialchars($par_form['fund_cluster']); ?>
            </div>
            <div style="width: 33%; float: left;">
                <strong>Office Location:</strong><br>
                <?php echo htmlspecialchars($par_form['office_location']); ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">Qty</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 15%;">Property Number</th>
                <th style="width: 15%;">Date Acquired</th>
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($par_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['property_number'] ?? ''); ?></td>
                    <td><?php echo $item['date_acquired'] ? date('m/d/Y', strtotime($item['date_acquired'])) : ''; ?></td>
                    <td><?php echo number_format($item['amount'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p><strong>Received by:</strong></p>
            <p><?php echo htmlspecialchars($par_form['received_by_name']); ?></p>
            <p><?php echo htmlspecialchars($par_form['received_by_position']); ?></p>
            <?php if ($par_form['received_by_date']): ?>
                <p>Date: <?php echo date('F d, Y', strtotime($par_form['received_by_date'])); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <p><strong>Issued by:</strong></p>
            <p><?php echo htmlspecialchars($par_form['issued_by_name']); ?></p>
            <p><?php echo htmlspecialchars($par_form['issued_by_position']); ?></p>
            <?php if ($par_form['issued_by_date']): ?>
                <p>Date: <?php echo date('F d, Y', strtotime($par_form['issued_by_date'])); ?></p>
            <?php endif; ?>
        </div>
        <div class="clear"></div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
