<?php
$page_title = "Service Management | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

// Get services based on user role
$conn = getDBConnection();
if (isCustomer()) {
    $stmt = $conn->prepare("SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                           u.fullname as assigned_to_name 
                           FROM services s 
                           JOIN vehicles v ON s.vehicle_id = v.id 
                           LEFT JOIN users u ON s.assigned_to = u.id 
                           WHERE s.user_id = ? 
                           ORDER BY s.created_at DESC");
    $stmt->bind_param("i", $user_id);
} elseif (isWorker()) {
    $stmt = $conn->prepare("SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                           u.fullname as customer_name,
                           u2.fullname as assigned_to_name 
                           FROM services s 
                           JOIN vehicles v ON s.vehicle_id = v.id 
                           JOIN users u ON s.user_id = u.id 
                           LEFT JOIN users u2 ON s.assigned_to = u2.id 
                           WHERE s.assigned_to = ? OR s.assigned_to IS NULL 
                           ORDER BY s.created_at DESC");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                           u.fullname as customer_name,
                           u2.fullname as assigned_to_name 
                           FROM services s 
                           JOIN vehicles v ON s.vehicle_id = v.id 
                           JOIN users u ON s.user_id = u.id 
                           LEFT JOIN users u2 ON s.assigned_to = u2.id 
                           ORDER BY s.created_at DESC");
}
$stmt->execute();
$services_result = $stmt->get_result();
$stmt->close();

// Get workers for assignment (admin/manager only)
$workers = [];
if (hasAccess(['admin', 'manager'])) {
    $worker_stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'worker'");
    $worker_stmt->execute();
    $workers_result = $worker_stmt->get_result();
    while ($worker = $workers_result->fetch_assoc()) {
        $workers[] = $worker;
    }
    $worker_stmt->close();
}

$conn->close();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Service Management</h1>
    </div>
	<?php if (hasAccess(['worker'])): ?>
	<!-- Service Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="totalServices">0</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="pendingServices">0</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="assignedServices">0</div>
                <div class="stat-label">Assigned</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="progressServices">0</div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="completedServices">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number" id="cancelledServices">0</div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>
    </div>
	<?php endif; ?>
    

    <!-- Services Table -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Service Requests</h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light filter-btn active" data-status="all">All</button>
                <button type="button" class="btn btn-light filter-btn" data-status="pending">Pending</button>
                <button type="button" class="btn btn-light filter-btn" data-status="assigned">Assigned</button>
                <button type="button" class="btn btn-light filter-btn" data-status="in_progress">In Progress</button>
                <button type="button" class="btn btn-light filter-btn" data-status="completed">Completed</button>
                <button type="button" class="btn btn-light filter-btn" data-status="cancelled">Cancelled</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="servicesTable">
                    <thead>
                        <tr>
                            <th>Service ID</th>
                            <th>Service Type</th>
                            <th>Vehicle</th>
                            <?php if (!isCustomer()): ?>
                            <th>Customer</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($services_result->num_rows > 0): ?>
                            <?php while($service = $services_result->fetch_assoc()): ?>
                            <tr class="service-row" data-status="<?php echo $service['status']; ?>">
                                <td>#<?php echo str_pad($service['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo $service['service_type']; ?></strong>
                                    <?php if ($service['description']): ?>
                                    <br><small class="text-muted"><?php echo substr($service['description'], 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $service['make'] . ' ' . $service['model'] . ' (' . $service['license_plate'] . ')'; ?></td>
                                <?php if (!isCustomer()): ?>
                                <td><?php echo $service['customer_name']; ?></td>
                                <?php endif; ?>
                                <td>
                                    <span class="badge status-badge status-<?php echo $service['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $service['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($service['assigned_to_name']): ?>
                                        <?php echo $service['assigned_to_name']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($service['created_at'])); ?></td>
                                <td>
									<div class="btn-group btn-group-sm">
										<button class="btn btn-outline-primary view-service" data-service-id="<?php echo $service['id']; ?>">
											<i class="fas fa-eye"></i>
										</button>
										<?php if (hasAccess(['admin', 'manager']) || (isWorker() && $service['assigned_to'] == $user_id)): ?>
										<button class="btn btn-outline-success update-service" data-service-id="<?php echo $service['id']; ?>">
											<i class="fas fa-edit"></i>
										</button>
										<?php endif; ?>
										<?php if (hasAccess(['admin', 'manager']) || (isCustomer() && $service['status'] == 'pending')): ?>
										<button class="btn btn-outline-danger delete-service" data-service-id="<?php echo $service['id']; ?>" data-service-number="#<?php echo str_pad($service['id'], 6, '0', STR_PAD_LEFT); ?>">
											<i class="fas fa-trash"></i>
										</button>
										<?php endif; ?>
									</div>
								</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo isCustomer() ? '7' : '8'; ?>" class="text-center py-4">
                                    <i class="fas fa-tasks fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No service requests found.</p>
                                    <?php if (isCustomer()): ?>
                                    <button class="btn btn-gradient" data-toggle="modal" data-target="#requestServiceModal">
                                        <i class="fas fa-plus mr-2"></i>Request Your First Service
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($services_result->num_rows > 0): ?>
            <nav aria-label="Services pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="servicesPagination"></ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Service Modal - Enhanced -->
<div class="modal fade" id="viewServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Service Request Details - #<span id="serviceIdHeader"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="serviceDetails">
                <!-- Service details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <?php if (hasAccess(['admin', 'manager', 'worker'])): ?>
                <button type="button" class="btn btn-primary" id="uploadMoreDocumentsBtn">
                    <i class="fas fa-upload mr-2"></i>Upload Documents
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Documents Modal -->
<div class="modal fade" id="uploadDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Documents for Service #<span id="uploadServiceId"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="uploadDocumentsForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="service_id" id="uploadServiceIdInput">
                    
                    <div class="form-group">
                        <label>Document Type</label>
                        <select class="form-control" name="document_type" required>
                            <option value="user_uploaded">User Uploaded</option>
                            <option value="worker_requested">Worker Requested</option>
                            <option value="completion_doc">Completion Document</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief description of the documents..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Documents</label>
                        <div class="upload-area" id="documentsUploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                                <h5>Drag & Drop Files Here</h5>
                                <p class="text-muted">or click to browse</p>
                                <p class="small text-muted mb-0">PDF, PNG, JPG, JPEG up to 10MB each</p>
                            </div>
                            <input type="file" name="documents[]" id="documentsInput" 
                                   accept=".pdf,.png,.jpg,.jpeg" multiple style="display: none;">
                        </div>
                        
                        <!-- Selected Files List -->
                        <div id="documentsFilesList" class="mt-3" style="display: none;">
                            <h6>Selected Files:</h6>
                            <div id="selectedDocuments" class="selected-files"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-upload mr-2"></i>Upload Documents
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete service request <strong id="deleteServiceNumber"></strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All associated documents and updates will be permanently deleted.</p>
                
                <div class="form-group">
                    <label for="deleteReason">Reason for deletion (optional):</label>
                    <textarea class="form-control" id="deleteReason" rows="2" placeholder="Provide a reason for deletion..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash mr-2"></i>Delete Service
                </button>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>

<script src="js/services-management.js"></script>