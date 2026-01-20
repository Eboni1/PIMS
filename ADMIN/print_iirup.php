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

// Get IIRUP ID from URL
$iirup_id = $_GET['id'] ?? 0;
if (empty($iirup_id)) {
    header('Location: iirup_entries.php');
    exit();
}

// Get IIRUP form details
$iirup_form = null;
$stmt = $conn->prepare("SELECT * FROM iirup_forms WHERE id = ?");
$stmt->bind_param("i", $iirup_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $iirup_form = $result->fetch_assoc();
}
$stmt->close();

if (!$iirup_form) {
    header('Location: iirup_entries.php');
    exit();
}

// Get IIRUP items
$iirup_items = [];
$stmt = $conn->prepare("SELECT * FROM iirup_items WHERE form_id = ? ORDER BY item_order");
$stmt->bind_param("i", $iirup_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $iirup_items[] = $row;
}
$stmt->close();

// Get header image from forms table
$header_image = '';
$result = $conn->query("SELECT header_image FROM forms WHERE form_code = 'IIRUP'");
if ($result && $row = $result->fetch_assoc()) {
    $header_image = $row['header_image'];
}

logSystemAction($_SESSION['user_id'], 'Printed IIRUP Form', 'forms', "IIRUP ID: $iirup_id, Form No: {$iirup_form['form_number']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IIRUP PRINT - <?php echo htmlspecialchars($iirup_form['form_number']); ?> - PIMS</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
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
            margin-bottom: 0;
            align-items: center;
        }
        
        .entity-label {
            width: 140px;
            margin-bottom: 0;
            font-size: 12px;
        }
        
        .entity-value {
            flex: 1;
            border-bottom:  solid #000;
            min-height: 0;
            font-size: 12px;
            padding: 2px 5px;
            min-width: 150px;
            max-width: 200px;
        }
        
        .entity-row .entity-label:nth-child(3),
        .entity-row .entity-label:nth-child(4) {
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
            position: relative;
            margin-top: 0px;
            bottom: auto;
            left: auto;
            right: auto;
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
            margin-bottom: 0;
            font-size: 12px;
        }
        
        .signature-name {
            font-weight: bold;
            
            font-size: 12px;
            
            min-height: 20px;
            padding: 2px 5px;
            display: block;
        }
        
        .signature-position {
            font-style: italic;
           
            font-size: 11px;
            margin-bottom: 0;
            min-height: 0;
            padding: 2px 5px;
            display: block;
        }
        
        .signature-line {
            text-align: center;
            min-height: 0;
           
        }
        
        .date-line {
            font-size: 11px;
            text-align: left;
            
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
                max-width: none;
                min-height: none;
            }
            
            /* Hide browser print headers and footers */
            html {
                overflow: hidden;
            }
            
            @page {
                size: A4 landscape;
                margin: 0.5in;
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
        
        <!-- Form Information -->
        <div class="entity-section">
            <div class="entity-row">
                <div class="entity-label text-center" style="width: 100%; margin-bottom: 15px; text-align: center;">
                    <label style="font-style: italic; font-family: 'Times New Roman', serif;">As of <?php echo htmlspecialchars($iirup_form['as_of_year']); ?></label>
                </div>
            </div>
            <div class="entity-row">
                <div class="entity-label text-center" style="width: 33%;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 5px; text-align: center;"><?php echo htmlspecialchars($iirup_form['accountable_officer']); ?></div>
                    <div style="margin-top: 5px; text-align: center;">(Accountable Officer)</div>
                </div>
                <div class="entity-label text-center" style="width: 33%;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 5px; text-align: center;"><?php echo htmlspecialchars($iirup_form['designation']); ?></div>
                    <div style="margin-top: 5px; text-align: center;">(Designation)</div>
                </div>
                <div class="entity-label text-center" style="width: 33%;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 5px; text-align: center;"><?php echo htmlspecialchars($iirup_form['department_office']); ?></div>
                    <div style="margin-top: 5px; text-align: center;">(Department/Office)</div>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th colspan="10" class="text-center" style="background-color: #f8f9fa; font-weight: bold;">INVENTORY</th>
                    <th colspan="11" class="text-center" style="background-color: #e9ecef; font-weight: bold;">INSPECTION AND DISPOSAL</th>
                </tr>
                <tr>
                    <th style="width: 6%;">Date Acquired</th>
                    <th style="width: 18%;">Particulars</th>
                    <th style="width: 7%;">Property No.</th>
                    <th style="width: 6%;">Qty</th>
                    <th style="width: 7%;">Unit Cost</th>
                    <th style="width: 7%;">Total Cost</th>
                    <th style="width: 8%;">Accum. Depreciation</th>
                    <th style="width: 8%;">Accum. Impairment losses</th>
                    <th style="width: 8%;">Carrying amount</th>
                    <th style="width: 5%;">Remarks</th>
                    
                    <th style="width: 6%;">Sale</th>
                    <th style="width: 6%;">Transfer</th>
                    <th style="width: 6%;">Destruction</th>
                    <th style="width: 5%;">Others</th>
                    <th style="width: 6%;">Total</th>
                    <th style="width: 6%;">Appraised value</th>
                    <th style="width: 4%;">OR no.</th>
                    <th style="width: 5%;">Amount</th>
                    <th style="width: 5%;">Dept</th>
                    <th style="width: 4%;">Code</th>
                    <th style="width: 6%;">Date received</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($iirup_items as $item): ?>
                    <tr>
                        <td><?php echo !empty($item['date_acquired']) ? date('M d, Y', strtotime($item['date_acquired'])) : ''; ?></td>
                        <td><?php echo htmlspecialchars($item['particulars']); ?></td>
                        <td><?php echo htmlspecialchars($item['property_no']); ?></td>
                        <td><?php echo number_format($item['quantity'], 2); ?></td>
                        <td>₱<?php echo number_format($item['unit_cost'], 2); ?></td>
                        <td>₱<?php echo number_format($item['total_cost'], 2); ?></td>
                        <td>₱<?php echo number_format($item['accumulated_depreciation'], 2); ?></td>
                        <td>₱<?php echo number_format($item['impairment_losses'], 2); ?></td>
                        <td>₱<?php echo number_format($item['carrying_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['inventory_remarks']); ?></td>
                        <td>₱<?php echo number_format($item['disposal_sale'], 2); ?></td>
                        <td>₱<?php echo number_format($item['disposal_transfer'], 2); ?></td>
                        <td>₱<?php echo number_format($item['disposal_destruction'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['disposal_others']); ?></td>
                        <td>₱<?php echo number_format($item['disposal_total'], 2); ?></td>
                        <td>₱<?php echo number_format($item['appraised_value'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['or_no']); ?></td>
                        <td>₱<?php echo number_format($item['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['dept_office']); ?></td>
                        <td><?php echo htmlspecialchars($item['control_no']); ?></td>
                        <td><?php echo !empty($item['date_received']) ? date('M d, Y', strtotime($item['date_received'])) : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
           
        </table>
        
        <!-- Signatures Section -->
        <div class="signatures-section" style="position: relative; margin-top: 40px; bottom: auto; left: auto; right: auto;">
            <!-- Certification Statement -->
            <div class="certification-statement" style="margin-bottom: 30px;">
                <div class="entity-row">
                    <div class="entity-label" style="width: 50%;">
                        <p style="margin: 0; font-size: 11px;">I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of property enumerated above.</p>
                    </div>
                    <div class="entity-label" style="width: 25%;">
                        <p style="margin: 0; font-size: 11px;">I CERTIFY that I have inspected each and every article enumerated in this report, and that disposition made thereof was, in my judgment, best for public interest.</p>
                    </div>
                    <div class="entity-label" style="width: 25%;">
                        <p style="margin: 0; font-size: 11px;">I CERTIFY that I have witnessed disposition of articles enumerated on this report this _____ day of _____.</p>
                    </div>
                </div>
            </div>
            
            <!-- Signature Lines -->
            <div class="signature-row">
                <div class="signature-column">
                    <div class="signature-label">Requested by:</div>
                    <div class="signature-line" style="border-bottom: 1px solid #333; padding-bottom: 0; margin-bottom: 2px;"><?php echo htmlspecialchars($iirup_form['accountable_officer_name']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Signature over Printed Name of Accountable Officer)</div>
                    <div class="signature-line" style="border-bottom: 1px solid #333; padding-bottom: 5px;"><?php echo htmlspecialchars($iirup_form['accountable_officer_designation']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Designation of Accountable Officer)</div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">Approved by:</div>
                    <div class="signature-line" style="border-bottom: 1px solid #000; padding-bottom: 5px;"><?php echo htmlspecialchars($iirup_form['authorized_official_name']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Signature over Printed Name of Authorized Official)</div>
                    <div class="signature-line" style="border-bottom: 1px solid #000; padding-bottom: 5px;"><?php echo htmlspecialchars($iirup_form['authorized_official_designation']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Designation of Authorized Official)</div>
                </div>
                <div class="signature-column">
                    
                    <div class="signature-line" style="border-bottom: 1px solid #000; padding-bottom: 5px;"><?php echo htmlspecialchars($iirup_form['inspection_officer_name']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Signature over Printed Name of Inspection Officer)</div>
                </div>
                <div class="signature-column">
                    
                    <div class="signature-line" style="border-bottom: 1px solid #000; padding-bottom: 5px;"><?php echo htmlspecialchars($iirup_form['witness_name']); ?></div>
                    <div class="signature-position" style="font-size: 11px; text-align: center;">(Signature over Printed Name of Witness)</div>
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
