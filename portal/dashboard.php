<?php
$page_title = "Dashboard | ErrandsCall Portal";

include('config/database.php');
include('includes/auth-check.php');

// Then include header and sidebar
include('includes/header.php');
include('includes/sidebar.php');

// Get dashboard statistics based on user role
$conn = getDBConnection();

// Total vehicles (for customers: their vehicles, for staff: all vehicles)
if (isCustomer()) {
    $vehicles_sql = "SELECT COUNT(*) as total FROM vehicles WHERE user_id = ?";
    $vehicles_stmt = $conn->prepare($vehicles_sql);
    $vehicles_stmt->bind_param("i", $user_id);
} else {
    $vehicles_sql = "SELECT COUNT(*) as total FROM vehicles";
    $vehicles_stmt = $conn->prepare($vehicles_sql);
}
$vehicles_stmt->execute();
$vehicles_result = $vehicles_stmt->get_result();
$total_vehicles = $vehicles_result->fetch_assoc()['total'];
$vehicles_stmt->close();

// Expiring soon vehicles
if (isCustomer()) {
    $expiring_sql = "SELECT COUNT(*) as total FROM vehicles WHERE user_id = ? AND disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    $expiring_stmt = $conn->prepare($expiring_sql);
    $expiring_stmt->bind_param("i", $user_id);
} else {
    $expiring_sql = "SELECT COUNT(*) as total FROM vehicles WHERE disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    $expiring_stmt = $conn->prepare($expiring_sql);
}
$expiring_stmt->execute();
$expiring_result = $expiring_stmt->get_result();
$expiring_vehicles = $expiring_result->fetch_assoc()['total'];
$expiring_stmt->close();

// Total services
if (isCustomer()) {
    $services_sql = "SELECT COUNT(*) as total FROM services WHERE user_id = ?";
    $services_stmt = $conn->prepare($services_sql);
    $services_stmt->bind_param("i", $user_id);
} else {
    $services_sql = "SELECT COUNT(*) as total FROM services";
    $services_stmt = $conn->prepare($services_sql);
}
$services_stmt->execute();
$services_result = $services_stmt->get_result();
$total_services = $services_result->fetch_assoc()['total'];
$services_stmt->close();

// Pending services
if (isCustomer()) {
    $pending_sql = "SELECT COUNT(*) as total FROM services WHERE user_id = ? AND status = 'pending'";
    $pending_stmt = $conn->prepare($pending_sql);
    $pending_stmt->bind_param("i", $user_id);
} else {
    $pending_sql = "SELECT COUNT(*) as total FROM services WHERE status = 'pending'";
    $pending_stmt = $conn->prepare($pending_sql);
}
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_services = $pending_result->fetch_assoc()['total'];
$pending_stmt->close();

// Additional stats for admin/manager
$total_users = 0;
$active_workers = 0;

if (hasAccess(['admin', 'manager'])) {
    $users_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $users_stmt->execute();
    $users_result = $users_stmt->get_result();
    $total_users = $users_result->fetch_assoc()['total'];
    $users_stmt->close();
    
    $workers_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'worker' AND status = 'active'");
    $workers_stmt->execute();
    $workers_result = $workers_stmt->get_result();
    $active_workers = $workers_result->fetch_assoc()['total'];
    $workers_stmt->close();
}

//$conn->close();
?>

<div class="main-content">
    <!-- Dashboard Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_vehicles; ?></div>
            <div class="stat-label">Total Vehicles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $expiring_vehicles; ?></div>
            <div class="stat-label">Discs Expiring Soon</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_services; ?></div>
            <div class="stat-label">Total Services</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $pending_services; ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
        
        <?php if (hasAccess(['admin', 'manager'])): ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $active_workers; ?></div>
            <div class="stat-label">Active Workers</div>
        </div>
        <?php endif; ?>
    </div>
<!-- Add this after the existing dashboard stats -->
	<div class="row mt-4">
		<!-- Service Status Overview -->
		<div class="col-lg-12 mb-4">
			<div class="card">
				<div class="card-header bg-gradient text-white">
					<h5 class="mb-0">Service Status Overview</h5>
				</div>
				<div class="card-body">
					<div class="row text-center">
						<?php
						// Get service status counts
						if (isCustomer()) {
							$status_sql = "SELECT status, COUNT(*) as count FROM services WHERE user_id = ? GROUP BY status";
							$status_stmt = $conn->prepare($status_sql);
							$status_stmt->bind_param("i", $user_id);
						} else {
							$status_sql = "SELECT status, COUNT(*) as count FROM services GROUP BY status";
							$status_stmt = $conn->prepare($status_sql);
						}
						$status_stmt->execute();
						$status_result = $status_stmt->get_result();
						
						$status_counts = [];
						while ($row = $status_result->fetch_assoc()) {
							$status_counts[$row['status']] = $row['count'];
						}
						$status_stmt->close();
						
						$statuses = [
							'pending' => ['color' => 'warning', 'icon' => 'clock'],
							'assigned' => ['color' => 'info', 'icon' => 'user-check'],
							'in_progress' => ['color' => 'primary', 'icon' => 'cog'],
							'completed' => ['color' => 'success', 'icon' => 'check-circle'],
							'cancelled' => ['color' => 'danger', 'icon' => 'times-circle']
						];
						
						foreach ($statuses as $status => $info) {
							$count = $status_counts[$status] ?? 0;
							echo "
							<div class='col-md-2 col-6 mb-3'>
								<div class='status-card text-{$info['color']}'>
									<i class='fas fa-{$info['icon']} fa-2x mb-2'></i>
									<h4 class='mb-1'>{$count}</h4>
									<small class='text-uppercase font-weight-bold'>" . ucfirst(str_replace('_', ' ', $status)) . "</small>
								</div>
							</div>";
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

    <!-- Recent Vehicles & Services -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Vehicles</h5>
                    <a href="vehicles-management.php" class="text-white">View All</a>
                </div>
                <div class="card-body">
                    <div id="recentVehicles">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Services</h5>
                    <a href="services-management.php" class="text-white">View All</a>
                </div>
                <div class="card-body">
                    <div id="recentServices">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (isCustomer()): ?>
                        <div class="col-md-6 mb-3">
                            <a class="btn btn-gradient btn-block" href="vehicles-management.php">
                                <i class="fas fa-car mr-2"></i>My Vehicles
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a class="btn btn-gradient btn-block" href="services-management.php">
                                <i class="fas fa-tasks mr-2"></i>My Services
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (hasAccess(['admin', 'manager'])): ?>
                        <div class="col-md-6 mb-3">
                            <a href="users-management.php" class="btn btn-gradient btn-block">
                                <i class="fas fa-users mr-2"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="services-management.php" class="btn btn-gradient btn-block">
                                <i class="fas fa-cog mr-2"></i>Manage Services
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (hasAccess(['worker'])): ?>
                        <div class="col-md-6 mb-3">
                            <a href="services-management.php" class="btn btn-gradient btn-block">
                                <i class="fas fa-list mr-2"></i>My Assignments
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (hasAccess(['worker','customer'])): ?>
                        <div class="col-md-6 mb-3">
                            <a href="chat.php" class="btn btn-gradient btn-block">
                                <i class="fas fa-list mr-2"></i>My Chats
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                    <?php if (hasAccess(['admin', 'manager'])): ?>
                    <a href="user-reports.php" class="text-white">View All</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div id="recentActivity" class="recent-activity-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	
	<!-- Chat Modal -->
	<div class="modal fade" id="chatModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header bg-gradient text-white">
					<h5 class="modal-title">Service Chat</h5>
					<button type="button" class="close text-white" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="chatMessages" style="height: 400px; overflow-y: auto;"></div>
					<div class="chat-input mt-3">
						<textarea class="form-control" id="chatMessage" placeholder="Type your message..."></textarea>
						<button class="btn btn-gradient mt-2" id="sendMessage">Send</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>

<!-- Include Modals -->
<?php include('includes/footer.php'); ?>

<script src="js/dashboard.js"></script>
<?php if (isWorker()): ?>
<script src="js/worker-location-tracker.js"></script>
<?php endif; ?>