<?php
// ITR Form Include for form_details.php
?>

<!-- ITR Form Management -->
<ul class="nav nav-tabs" id="itrTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="itr-preview-tab" data-bs-toggle="tab" data-bs-target="#itr-preview" type="button" role="tab">
            <i class="bi bi-eye"></i> ITR Preview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="itr-entries-tab" data-bs-toggle="tab" data-bs-target="#itr-entries" type="button" role="tab">
            <i class="bi bi-list"></i> ITR Entries
        </button>
    </li>
</ul>

<div class="tab-content" id="itrTabsContent">
    <!-- ITR Preview Tab -->
    <div class="tab-pane fade show active" id="itr-preview" role="tabpanel">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-info text-white rounded-top-4">
                <h6 class="mb-0">
                    <i class="bi bi-eye"></i> ITR Form Preview
                </h6>
            </div>
            <div class="card-body">
                <div class="itr-preview-container" style="background: white; border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; font-family: 'Times New Roman', serif;">
                    <!-- ITR Form Header -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <?php 
                        // Debug: Check header image
                        if (!empty($header_image)) {
                            echo "<!-- Debug: header_image found: " . htmlspecialchars($header_image) . " -->";
                            echo '<div style="margin-bottom: 10px;">';
                            echo '<img src="../uploads/forms/' . htmlspecialchars($header_image) . '" alt="Header Image" style="width: 100%; max-height: 120px; object-fit: contain; border: 1px solid red;">';
                            echo '</div>';
                        } else {
                            echo "<!-- Debug: header_image is empty -->";
                        }
                        ?>
                        
                    </div>
                    
                    <!-- Top Section -->
                    <div style="margin-bottom: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 25%; padding: 5px; border: 1px solid #000;"><strong>Entity Name:</strong></td>
                                <td style="width: 25%; padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="width: 25%; padding: 5px; border: 1px solid #000;"><strong>Fund Cluster:</strong></td>
                                <td style="width: 25%; padding: 5px; border: 1px solid #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;"><strong>From Accountable Officer/Agency/Fund Cluster:</strong></td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="padding: 5px; border: 1px solid #000;"><strong>To Accountable Officer/Agency/Fund Cluster:</strong></td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;"><strong>Transfer Type:</strong></td>
                                <td style="padding: 5px; border: 1px solid #000;">
                                    <input type="checkbox" style="margin-right: 5px;"> Relocate
                                    <input type="checkbox" style="margin-right: 5px;"> Others (Specify): _________
                                </td>
                                <td style="padding: 5px; border: 1px solid #000;"><strong>ITR No.:</strong></td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;"><strong>Date:</strong></td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="padding: 5px; border: 1px solid #000;" colspan="2">&nbsp;</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Main Items Table -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000;">
                            <thead>
                                <tr style="background: #f0f0f0;">
                                    <th style="padding: 5px; border: 1px solid #000; text-align: center; width: 15%;">Date Acquired</th>
                                    <th style="padding: 5px; border: 1px solid #000; text-align: center; width: 15%;">Item No.</th>
                                    <th style="padding: 5px; border: 1px solid #000; text-align: left; width: 25%;">ICS & PAR No./Date</th>
                                    <th style="padding: 5px; border: 1px solid #000; text-align: left; width: 25%;">Description</th>
                                    <th style="padding: 5px; border: 1px solid #000; text-align: center; width: 10%;">Unit Price</th>
                                    <th style="padding: 5px; border: 1px solid #000; text-align: center; width: 10%;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Reason for Transfer Section -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000; font-weight: bold; width: 20%;">Reason/s for Transfer:</td>
                                <td style="padding: 5px; border: 1px solid #000; min-height: 80px; vertical-align: top;">&nbsp;</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Signature Section -->
                    <div style="margin-top: 40px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 33%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-weight: bold;">Approved by:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Designation</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Date</p>
                                </td>
                                <td style="width: 33%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-weight: bold;">Released/Issued by:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Designation</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Date</p>
                                </td>
                                <td style="width: 33%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-weight: bold;">Received by:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Designation</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Date</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Footer Note -->
                    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
                        <p style="margin: 0;">This form is prescribed by Commission on Audit (COA)</p>
                        <p style="margin: 0;">Inventory Transfer Report (ITR) - Government Property Management</p>
                    </div>
                </div>
                
                <!-- Preview Controls -->
                <div class="mt-3 text-center">
                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Preview
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="zoomPreview()">
                        <i class="bi bi-zoom-in"></i> Zoom
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ITR Entries Tab -->
    <div class="tab-pane fade" id="itr-entries" role="tabpanel">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h6 class="mb-0">
                    <i class="bi bi-list"></i> ITR Entries
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($form_data)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No ITR entries found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="itrTable" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ITR No.</th>
                                    <th>Entity Name</th>
                                    <th>From Accountable Officer</th>
                                    <th>To Accountable Officer</th>
                                    <th>Date</th>
                                    <th>Items Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($form_data as $entry): ?>
                                    <?php
                                    // Get item count for this ITR
                                    $item_count_result = $conn->query("SELECT COUNT(*) as count FROM itr_items WHERE itr_id = " . $entry['itr_id']);
                                    $item_count = $item_count_result->fetch_assoc()['count'];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($entry['itr_no']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($entry['entity_name']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['from_accountable_officer']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['to_accountable_officer']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($entry['date'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $item_count; ?> items</span></td>
                                        <td>
                                            <div class="form-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewITR(<?php echo $entry['itr_id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="manageITRItems(<?php echo $entry['itr_id']; ?>)">
                                                    <i class="bi bi-box"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
