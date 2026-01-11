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

// Get header image from forms table
$header_image = '';
$result = $conn->query("SELECT header_image FROM forms WHERE form_code = 'RIS'");
if ($result && $row = $result->fetch_assoc()) {
    $header_image = $row['header_image'];
}

logSystemAction($_SESSION['user_id'], 'Printed RIS entry', 'forms', "RIS ID: $ris_id, RIS No: {$ris['ris_no']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REQUISITION AND ISSUE SLIP</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        
        .print-container {
            width: 100%;
            max-width: 8.27in;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            min-height: 100vh;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .entity-section {
            margin-bottom: 25px;
        }
        
        .entity-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .entity-column {
            flex: 1;
            padding: 0 5px;
            text-align: left;
        }
        
        .entity-column:not(:last-child) {
            border-right: 1px solid #ccc;
        }
        
        .entity-label {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 12px;
        }
        
        .entity-value {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            display: block;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 15px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
        }
        
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }
        
        .items-table .text-left {
            text-align: left;
        }
        
        .items-table .quantity-col {
            width: 8%;
        }
        
        .items-table .unit-col {
            width: 8%;
        }
        
        .items-table .price-col {
            width: 12%;
        }
        
        .items-table .total-col {
            width: 12%;
        }
        
        .items-table .description-col {
            width: 35%;
        }
        
        .items-table .stock-col {
            width: 10%;
        }
        
        .items-table .purpose-col {
            width: 15%;
        }
        
        .amount-header {
            text-align: center;
        }
        
        .total-row td {
            font-weight: bold;
            background: #f0f0f0;
        }
        
        .total-row .total-label {
            text-align: right;
            padding-right: 10px;
        }
        
        .nothing-follows {
            text-align: center;
            font-style: italic;
            margin-bottom: 30px;
        }
        
        .purpose-section {
            margin-bottom: 25px;
        }
        
        .purpose-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .purpose-text {
            border: 1px solid #000;
            padding: 10px;
            min-height: 60px;
            font-size: 12px;
        }
        
        .signatures-section {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            margin-top: 0;
        }
        
        .signature-row {
            display: flex;
            margin-bottom: 30px;
        }
        
        .signature-column {
            flex: 1;
            padding: 0 10px;
            text-align: center;
        }
        
        .signature-column:not(:last-child) {
            border-right: 1px solid #ccc;
        }
        
        .signature-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .signature-name {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 12px;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            display: block;
        }
        
        .signature-position {
            font-style: italic;
            margin-bottom: 15px;
            font-size: 11px;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            display: block;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            min-height: 40px;
            margin-bottom: 5px;
        }
        
        .signature-position-line {
            border-bottom: 1px solid #000;
            min-height: 20px;
            margin-bottom: 5px;
        }
        
        .date-line {
            font-size: 11px;
            text-align: center;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            display: block;
        }
        
        .signature-info {
            font-size: 10px;
            margin-bottom: 3px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .print-container {
                padding: 0;
            }
            
            /* Hide browser print headers and footers */
            @page {
                size: A4;
                margin: 0.5in;
            }
            
            /* Ensure no extra headers appear */
            html {
                overflow: hidden;
            }
            
            /* Hide any potential header elements */
            header, nav, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header Section -->
        <div class="header-section">
            <?php 
            if (!empty($header_image)) {
                echo '<div style="margin-bottom: 20px;">';
                echo '<img src="../uploads/forms/' . htmlspecialchars($header_image) . '" alt="Header Image" style="width: 100%; max-height: 150px; object-fit: contain;">';
                echo '</div>';
            }
            ?>
                    </div>
        
        <!-- Entity Information -->
        <div class="entity-section">
            <!-- Header Row: 4 Columns -->
            <div class="entity-row">
                <div class="entity-column">
                    <div class="entity-label">DIVISION:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['division']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">Responsibility Center:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['responsibility_center']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">RIS NO:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['ris_no']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">DATE:</div>
                    <div class="entity-value"><?php echo date('m/d/Y', strtotime($ris['date'])); ?></div>
                </div>
            </div>
            
            <!-- Second Row: 4 Columns -->
            <div class="entity-row">
                <div class="entity-column">
                    <div class="entity-label">OFFICE:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['office']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">Code:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['code']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">SAI NO.:</div>
                    <div class="entity-value"><?php echo htmlspecialchars($ris['sai_no']); ?></div>
                </div>
                <div class="entity-column">
                    <div class="entity-label">Date:</div>
                    <div class="entity-value"><?php echo date('m/d/Y', strtotime($ris['date_2'])); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="stock-col">Stock No.</th>
                    <th class="unit-col">Unit</th>
                    <th class="description-col text-left">Description</th>
                    <th class="quantity-col">Quantity</th>
                    <th class="price-col">Price</th>
                    <th class="total-col">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ris_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['stock_no']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo number_format($item['quantity'], 2); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['total_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="total-label">Total:</td>
                    <td><?php echo number_format(array_sum(array_column($ris_items, 'total_amount')), 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Purpose -->
        <div class="purpose-section">
            <div class="purpose-label">Purpose:</div>
            <div class="purpose-text"><?php echo htmlspecialchars($ris['purpose']); ?></div>
        </div>
        
        <!-- Signatures Section -->
        <div class="signatures-section">
            <!-- First Row: 4 Columns -->
            <div class="signature-row">
                <div class="signature-column">
                    <div class="signature-label">REQUESTED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ris['requested_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-position"><?php echo htmlspecialchars($ris['requested_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="date-line">
                        <?php if (!empty($ris['requested_date']) && $ris['requested_date'] !== '0000-00-00'): ?>
                            <?php echo date('m/d/Y', strtotime($ris['requested_date'])); ?>
                        <?php else: ?>
                            mm/dd/yyyy
                        <?php endif; ?>
                    </div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">APPROVED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ris['approved_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-position"><?php echo htmlspecialchars($ris['approved_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="date-line">
                        <?php if (!empty($ris['approved_date']) && $ris['approved_date'] !== '0000-00-00'): ?>
                            <?php echo date('m/d/Y', strtotime($ris['approved_date'])); ?>
                        <?php else: ?>
                            mm/dd/yyyy
                        <?php endif; ?>
                    </div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">ISSUED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ris['issued_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-position"><?php echo htmlspecialchars($ris['issued_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="date-line">
                        <?php if (!empty($ris['issued_date']) && $ris['issued_date'] !== '0000-00-00'): ?>
                            <?php echo date('m/d/Y', strtotime($ris['issued_date'])); ?>
                        <?php else: ?>
                            mm/dd/yyyy
                        <?php endif; ?>
                    </div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">RECEIVED BY:</div>
                    <div class="signature-line"></div>
                    <div class="signature-info">Signature over Printed Name</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ris['received_by']); ?></div>
                    <div class="signature-info">Designation</div>
                    <div class="signature-position"><?php echo htmlspecialchars($ris['received_by_position']); ?></div>
                    <div class="signature-info">Date</div>
                    <div class="date-line">
                        <?php if (!empty($ris['received_date']) && $ris['received_date'] !== '0000-00-00'): ?>
                            <?php echo date('m/d/Y', strtotime($ris['received_date'])); ?>
                        <?php else: ?>
                            mm/dd/yyyy
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
