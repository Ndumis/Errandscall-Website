<?php
$page_title = "Service Assignments | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

// Only workers can access this page
if (!isWorker()) {
    header('Location: services-management.php');
    exit;
}

$conn = getDBConnection();

// Get assigned services for the current worker
$stmt = $conn->prepare("SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                       u.fullname as customer_name, u.phone as customer_phone,
                       u2.fullname as assigned_to_name 
                       FROM services s 
                       JOIN vehicles v ON s.vehicle_id = v.id 
                       JOIN users u ON s.user_id = u.id 
                       LEFT JOIN users u2 ON s.assigned_to = u2.id 
                       WHERE s.assigned_to = ? 
                       ORDER BY 
                         CASE s.status 
                           WHEN 'in_progress' THEN 1
                           WHEN 'assigned' THEN 2
                           WHEN 'pending' THEN 3
                           ELSE 4
                         END,
                         s.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$services_result = $stmt->get_result();
$stmt->close();

// Get available services that can be picked up
$available_stmt = $conn->prepare("SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                                u.fullname as customer_name, u.phone as customer_phone
                                FROM services s 
                                JOIN vehicles v ON s.vehicle_id = v.id 
                                JOIN users u ON s.user_id = u.id 
                                WHERE s.assigned_to IS NULL AND s.status = 'pending'
                                ORDER BY s.created_at DESC");
$available_stmt->execute();
$available_services_result = $available_stmt->get_result();
$available_stmt->close();

$conn->close();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">My Service Assignments</h1>
        <div class="badge badge-primary badge-lg">
            <i class="fas fa-tasks mr-2"></i>
            <span id="assignedCount">0</span> Assigned Services
        </div>
    </div>

    <!-- Service Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="stat-number" id="totalAssigned">0</div>
                <div class="stat-label">Total Assigned</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning text-white">
                <div class="stat-number" id="inProgressCount">0</div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="stat-number" id="completedCount">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="stat-number" id="availableCount">0</div>
                <div class="stat-label">Available</div>
            </div>
        </div>
    </div>

    <!-- Available Services Section -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list-alt mr-2"></i>Available Services
            </h5>
            <span class="badge badge-light" id="availableServicesCount">0 services available</span>
        </div>
        <div class="card-body">
            <?php if ($available_services_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Service ID</th>
                                <th>Service Type</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Priority</th>
                                <th>Preferred Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($service = $available_services_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo str_pad($service['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo $service['service_type']; ?></strong>
                                    <?php if ($service['description']): ?>
                                    <br><small class="text-muted"><?php echo substr($service['description'], 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $service['make'] . ' ' . $service['model'] . ' (' . $service['license_plate'] . ')'; ?></td>
                                <td>
                                    <?php echo $service['customer_name']; ?>
                                    <br><small class="text-muted"><?php echo $service['customer_phone']; ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $service['priority'] == 'High' ? 'danger' : ($service['priority'] == 'Medium' ? 'warning' : 'info'); ?>">
                                        <?php echo $service['priority']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($service['preferred_date'])); ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm pickup-service" data-service-id="<?php echo $service['id']; ?>">
                                        <i class="fas fa-hand-paper mr-1"></i>Pick Up
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm view-service" data-service-id="<?php echo $service['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <p class="text-muted">No available services at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- My Assigned Services -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-check mr-2"></i>My Assigned Services
            </h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light filter-btn active" data-status="all">All</button>
                <button type="button" class="btn btn-light filter-btn" data-status="assigned">Assigned</button>
                <button type="button" class="btn btn-light filter-btn" data-status="in_progress">In Progress</button>
                <button type="button" class="btn btn-light filter-btn" data-status="completed">Completed</button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($services_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="assignedServicesTable">
                        <thead>
                            <tr>
                                <th>Service ID</th>
                                <th>Service Type</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Preferred Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                <td>
                                    <?php echo $service['customer_name']; ?>
                                    <br><small class="text-muted"><?php echo $service['customer_phone']; ?></small>
                                </td>
                                <td>
                                    <span class="badge status-badge status-<?php echo $service['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $service['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $service['priority'] == 'High' ? 'danger' : ($service['priority'] == 'Medium' ? 'warning' : 'info'); ?>">
                                        <?php echo $service['priority']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($service['preferred_date'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary view-service" data-service-id="<?php echo $service['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success update-service" data-service-id="<?php echo $service['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($service['status'] != 'completed'): ?>
                                        <button class="btn btn-outline-info add-update" data-service-id="<?php echo $service['id']; ?>">
                                            <i class="fas fa-comment"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-tasks fa-2x text-muted mb-3"></i>
                    <p class="text-muted">You don't have any assigned services yet.</p>
                    <p class="text-info">Pick up available services from the list above to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Update Service Modal -->
<div class="modal fade" id="updateServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Service - #<span id="updateServiceIdHeader"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="updateServiceForm">
                <div class="modal-body">
                    <input type="hidden" name="service_id" id="updateServiceId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updateStatus">Status</label>
                                <select class="form-control" id="updateStatus" name="status" required>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="updatePriority">Priority</label>
                                <select class="form-control" id="updatePriority" name="priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="serviceNotes">Progress Notes (Optional)</label>
                        <textarea class="form-control" id="serviceNotes" name="notes" rows="3" placeholder="Add any progress notes or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save mr-2"></i>Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Progress Update Modal -->
<div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Progress Update - #<span id="updateServiceIdHeader2"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addUpdateForm">
                <div class="modal-body">
                    <input type="hidden" name="service_id" id="updateServiceId2">
                    
                    <div class="form-group">
                        <label for="updateType">Update Type</label>
                        <select class="form-control" id="updateType" name="update_type" required>
                            <option value="progress_update">Progress Update</option>
                            <option value="note">Note</option>
                            <option value="document_request">Document Request</option>
                            <option value="status_change">Status Change</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="updateText">Update Details</label>
                        <textarea class="form-control" id="updateText" name="update_text" rows="4" placeholder="Describe the progress, issue, or update..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment mr-2"></i>Add Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Service Modal -->
<div class="modal fade" id="viewServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Service Details - #<span id="serviceIdHeader"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="serviceDetails">
                <!-- Service details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="js/service-assignments.js"></script>