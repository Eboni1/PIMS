<?php
// IIRUP Form Include for form_details.php
?>

<!-- IIRUP Form Management -->
<ul class="nav nav-tabs" id="iirupTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="iirup-preview-tab" data-bs-toggle="tab" data-bs-target="#iirup-preview" type="button" role="tab">
            <i class="bi bi-eye"></i> IIRUP Preview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="iirup-entries-tab" data-bs-toggle="tab" data-bs-target="#iirup-entries" type="button" role="tab">
            <i class="bi bi-list"></i> IIRUP Entries
        </button>
    </li>
</ul>

<div class="tab-content" id="iirupTabsContent">
    <!-- IIRUP Preview Tab -->
    <div class="tab-pane fade show active" id="iirup-preview" role="tabpanel">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-info text-white rounded-top-4">
                <h6 class="mb-0">
                    <i class="bi bi-eye"></i> IIRUP Form Preview
                </h6>
            </div>
            <div class="card-body">
                <div class="iirup-preview-container" style="background: white; border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; font-family: 'Times New Roman', serif;">
                    <!-- IIRUP Form Header -->
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
                        <div style="text-align: center; margin-bottom: 10px;">
                            <p style="margin: 0; font-size: 10px;">Republic of the Philippines</p>
                            <p style="margin: 0; font-size: 12px; font-weight: bold;">MUNICIPALITY OF PILAR</p>
                            <p style="margin: 0; font-size: 10px;">Province of Sorsogon</p>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div style="flex: 1; text-align: center;">
                                <p style="margin: 0; font-size: 16px; font-weight: bold;">INVENTORY AND INSPECTION REPORT FOR UNSERVICEABLE PROPERTY</p>
                            </div>
                            <div style="flex: 0 0 auto;">
                                <p style="margin: 0; font-size: 10px;">Annex C</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Header Information -->
                    <div style="margin-bottom: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 33%; padding: 5px; border: 1px solid #000;"><strong>Accountable Officer:</strong></td>
                                <td style="width: 33%; padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="width: 34%; padding: 5px; border: 1px solid #000;"><strong>Designation:</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                                <td style="padding: 5px; border: 1px solid #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;" colspan="3"><strong>Department/Office:</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000;" colspan="3">&nbsp;</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Inventory Table -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; font-size: 10px;">
                            <thead>
                                <tr style="background: #f0f0f0;">
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Date Acquired</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 15%;">Particulars/Articles</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 10%;">Property No.</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 5%;">Qty</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Unit Cost</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Total Cost</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Accumulated Depreciation</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Accumulated Impairment Losses</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Carrying Amount</th>
                                    <th rowspan="2" style="padding: 3px; border: 1px solid #000; text-align: center; width: 8%;">Remarks</th>
                                    <th colspan="5" style="padding: 3px; border: 1px solid #000; text-align: center; width: 22%;">Inspection and Disposal</th>
                                </tr>
                                <tr style="background: #f0f0f0;">
                                    <th style="padding: 3px; border: 1px solid #000; text-align: center; width: 4%;">Sale</th>
                                    <th style="padding: 3px; border: 1px solid #000; text-align: center; width: 4%;">Transfer</th>
                                    <th style="padding: 3px; border: 1px solid #000; text-align: center; width: 4%;">Destruction</th>
                                    <th style="padding: 3px; border: 1px solid #000; text-align: center; width: 4%;">Others (Specify)</th>
                                    <th style="padding: 3px; border: 1px solid #000; text-align: center; width: 6%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                    <td style="padding: 3px; border: 1px solid #000;">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Appraised Value Section -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50%; padding: 5px; border: 1px solid #000;">
                                    <strong>Appraised Value:</strong> _____________________
                                </td>
                                <td style="width: 50%; padding: 5px; border: 1px solid #000;">
                                    <strong>OR No.:</strong> _____________________
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Request Section -->
                    <div style="margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 12px;">I HEREBY request inspection and disposition of the above-mentioned article/s as unserviceable property.</p>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-weight: bold;">Requested by:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <p style="margin: 5px 0; font-size: 12px;">of Accountable Officer</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Designation of Accountable Officer</p>
                                </td>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-weight: bold;">Approved by:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <p style="margin: 5px 0; font-size: 12px;">of Authorized Official</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Designation of Authorized Official</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Certification Section -->
                    <div style="margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 12px;">I CERTIFY that I have inspected each and every article listed above and found them to be unserviceable and recommended for disposal as indicated.</p>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <p style="margin: 5px 0; font-size: 12px;">of Inspection Officer</p>
                                </td>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-size: 12px;">Date:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Witness Certification Section -->
                    <div style="margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 12px;">I CERTIFY that I have witnessed the disposition of the above-mentioned article/s.</p>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                                    <p style="margin: 5px 0; font-size: 12px;">Signature over Printed Name</p>
                                    <p style="margin: 5px 0; font-size: 12px;">of Witness</p>
                                </td>
                                <td style="width: 50%; padding: 10px; text-align: center; vertical-align: top;">
                                    <p style="margin: 0; font-size: 12px;">Date:</p>
                                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 20px;"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Footer Note -->
                    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
                        <p style="margin: 0;">This form is prescribed by Commission on Audit (COA)</p>
                        <p style="margin: 0;">Inventory and Inspection Report for Unserviceable Property (IIRUP) - Government Property Management</p>
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
    
    <!-- IIRUP Entries Tab -->
    <div class="tab-pane fade" id="iirup-entries" role="tabpanel">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h6 class="mb-0">
                    <i class="bi bi-list"></i> IIRUP Entries
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($form_data)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No IIRUP entries found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="iirupTable" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Accountable Officer</th>
                                    <th>Designation</th>
                                    <th>Office</th>
                                    <th>Date Created</th>
                                    <th>Items Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($form_data as $entry): ?>
                                    <?php
                                    // Get item count for this IIRUP
                                    $item_count_result = $conn->query("SELECT COUNT(*) as count FROM iirup_items WHERE iirup_id = " . $entry['id']);
                                    $item_count = $item_count_result->fetch_assoc()['count'];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($entry['accountable_officer']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($entry['designation']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['office']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($entry['created_at'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $item_count; ?> items</span></td>
                                        <td>
                                            <div class="form-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewIIRUP(<?php echo $entry['id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="manageIIRUPItems(<?php echo $entry['id']; ?>)">
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
