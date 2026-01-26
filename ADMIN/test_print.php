<?php
session_start();
require_once '../config.php';
require_once '../includes/system_functions.php';

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

// Get tag ID
$tag_id = intval($_GET['id'] ?? 0);
if ($tag_id === 0) {
    echo 'Invalid tag ID';
    exit();
}

// Get tag details
$sql = "SELECT ai.*, a.description as asset_description, a.unit_cost,
               ac.category_name, ac.category_code,
               o.office_name, o.address,
               e.employee_no, e.firstname, e.lastname, e.position
        FROM asset_items ai 
        LEFT JOIN assets a ON ai.asset_id = a.id 
        LEFT JOIN asset_categories ac ON a.asset_categories_id = ac.id 
        LEFT JOIN offices o ON ai.office_id = o.id 
        LEFT JOIN employees e ON ai.employee_id = e.id 
        WHERE ai.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tag_id);
$stmt->execute();
$result = $stmt->get_result();
$tag = $result->fetch_assoc();

if (!$tag) {
    echo 'Tag not found';
    exit();
}

// Log the print action
require_once '../includes/logger.php';
logSystemAction($_SESSION['user_id'], 'print', 'inventory_tag', "Printed inventory tag: {$tag['inventory_tag']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVENTORY TAG</title>
    <style>
        @page {
            size: Letter;
            margin: 0.5in;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .tag-container {
            width: 100%;
            max-width: 8in;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
            background: white;
        }
        
        .tag-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .seal {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            text-align: center;
            font-weight: bold;
        }
        
        .header-text {
            flex: 1;
            text-align: center;
            margin: 0 20px;
        }
        
        .header-text h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header-text h3 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .tag-number {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
        }
        
        .tag-body {
            margin-bottom: 20px;
        }
        
        .field-row {
            display: flex;
            margin-bottom: 10px;
            align-items: flex-start;
        }
        
        .field-label {
            width: 180px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .field-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 0;
        }
        
        .signature-section {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin-bottom: 5px;
        }
        
        .signature-label {
            font-size: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="tag-container">
        <div class="tag-header">
            <div class="header-row">
                <div class="seal">
                    SEAL<br>OF<br>LGU
                </div>
                <div class="header-text">
                    <h2>BAYAN NG PILAR</h2>
                    <h3>LALAWIGAN NG SORSOGON</h3>
                </div>
                <div class="tag-number">
                    No. <?php echo htmlspecialchars($tag['inventory_tag']); ?><br>
                    <small>INVENTORY TAG</small>
                </div>
            </div>
        </div>
        
        <div class="tag-body">
            <div class="field-row">
                <div class="field-label">Office/Location:</div>
                <div class="field-value"><?php echo htmlspecialchars($tag['office_name'] ?? ''); ?></div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Description of the property:</div>
                <div class="field-value"><?php echo htmlspecialchars($tag['description']); ?></div>
            </div>
            
            <div class="field-row">
                <div class="field-label">Person Accountable:</div>
                <div class="field-value"><?php echo htmlspecialchars($tag['firstname'] . ' ' . $tag['lastname']); ?></div>
            </div>
        </div>
        
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">COA Representative</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Signature of the Inventory Committee</div>
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
