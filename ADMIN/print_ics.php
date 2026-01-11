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

// Get ICS ID from URL
$ics_id = $_GET['id'] ?? 0;
if (empty($ics_id)) {
    header('Location: ics_entries.php');
    exit();
}

// Get ICS form details
$ics_form = null;
$stmt = $conn->prepare("SELECT * FROM ics_forms WHERE id = ?");
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $ics_form = $result->fetch_assoc();
}
$stmt->close();

if (!$ics_form) {
    header('Location: ics_entries.php');
    exit();
}

// Get ICS items
$ics_items = [];
$stmt = $conn->prepare("SELECT * FROM ics_items WHERE form_id = ? ORDER BY item_no");
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $ics_items[] = $row;
}
$stmt->close();

// Get header image from forms table
$header_image = '';
$result = $conn->query("SELECT header_image FROM forms WHERE form_code = 'ICS'");
if ($result && $row = $result->fetch_assoc()) {
    $header_image = $row['header_image'];
}

logSystemAction($_SESSION['user_id'], 'Printed ICS Form', 'forms', "ICS ID: $ics_id, ICS No: {$ics_form['ics_no']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVENTORY CUSTODIAN SLIP</title>
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
        
        .entity-label {
            width: 140px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .entity-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            font-size: 12px;
            padding: 2px 5px;
            min-width: 150px;
            max-width: 200px;
        }
        
        .entity-row .entity-label:nth-child(3),
        .entity-row .entity-value:nth-child(4) {
            margin-left: 20px;
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
        
        .items-table .unit-cost-col {
            width: 12%;
        }
        
        .items-table .total-cost-col {
            width: 12%;
        }
        
        .items-table .description-col {
            width: 35%;
        }
        
        .items-table .item-no-col {
            width: 10%;
        }
        
        .items-table .useful-life-col {
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
            padding: 0 20px;
        }
        
        .signature-column:first-child {
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
            text-align: left;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            display: block;
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
            <div class="form-title">INVENTORY CUSTODIAN SLIP</div>
        </div>
        
        <!-- Entity Information -->
        <div class="entity-section">
            <div class="entity-row">
                <div class="entity-label">Entity Name:</div>
                <div class="entity-value"><?php echo htmlspecialchars($ics_form['entity_name']); ?></div>
                <div class="entity-label">ICS No:</div>
                <div class="entity-value"><?php echo htmlspecialchars($ics_form['ics_no']); ?></div>
            </div>
            <div class="entity-row">
                <div class="entity-label">Fund Cluster:</div>
                <div class="entity-value"><?php echo htmlspecialchars($ics_form['fund_cluster']); ?></div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="quantity-col">Quantity</th>
                    <th class="unit-col">Unit</th>
                    <th colspan="2" class="amount-header">Amount</th>
                    <th class="description-col text-left">Description</th>
                    <th class="item-no-col">Item No.</th>
                    <th class="useful-life-col">Estimated Useful Life</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="unit-cost-col">Unit Cost</th>
                    <th class="total-cost-col">Total Cost</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ics_items as $item): ?>
                    <tr>
                        <td><?php echo number_format($item['quantity'], 0); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo number_format($item['unit_cost'], 2); ?></td>
                        <td><?php echo number_format($item['total_cost'], 2); ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($item['description']); ?></td>
                        <td></td>
                        <td><?php echo htmlspecialchars($item['useful_life']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="total-label">Total:</td>
                    <td><?php echo number_format(array_sum(array_column($ics_items, 'total_cost')), 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Signatures Section -->
        <div class="signatures-section">
            <div class="signature-row">
                <div class="signature-column">
                    <div class="signature-label">Received from:</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ics_form['received_from']); ?></div>
                    <div class="signature-position"><?php echo htmlspecialchars($ics_form['received_from_position']); ?></div>
                    <div class="date-line">
                        <?php if (!empty($ics_form['received_from_date']) && $ics_form['received_from_date'] !== '0000-00-00'): ?>
                            Date: <?php echo date('F d, Y', strtotime($ics_form['received_from_date'])); ?>
                        <?php else: ?>
                            Date: 
                        <?php endif; ?>
                    </div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">Received by:</div>
                    <div class="signature-name"><?php echo htmlspecialchars($ics_form['received_by']); ?></div>
                    <div class="signature-position"><?php echo htmlspecialchars($ics_form['received_by_position']); ?></div>
                    <div class="date-line">
                        <?php if (!empty($ics_form['received_by_date']) && $ics_form['received_by_date'] !== '0000-00-00'): ?>
                            Date: <?php echo date('F d, Y', strtotime($ics_form['received_by_date'])); ?>
                        <?php else: ?>
                            Date:
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
