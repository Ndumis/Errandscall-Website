<?php
$page_title = "User Reports | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}

$conn = getDBConnection();

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-01-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$role = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$activity_type = $_GET['activity_type'] ?? '';
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">User Reports & Activities</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportUserReport()">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">Report Filters</h5>
        </div>
        <div class="card-body">
            <form id="userFilters" method="GET">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="startDate">Registration Start</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="endDate">Registration End</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="role">User Role</label>
                            <select class="form-control" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="manager" <?php echo $role == 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="worker" <?php echo $role == 'worker' ? 'selected' : ''; ?>>Worker</option>
                                <option value="customer" <?php echo $role == 'customer' ? 'selected' : ''; ?>>Customer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="activityType">Activity Type</label>
                            <select class="form-control" id="activityType" name="activity_type">
                                <option value="">All Activities</option>
                                <option value="login" <?php echo $activity_type == 'login' ? 'selected' : ''; ?>>Login</option>
                                <option value="service_created" <?php echo $activity_type == 'service_created' ? 'selected' : ''; ?>>Service Created</option>
                                <option value="vehicle_added" <?php echo $activity_type == 'vehicle_added' ? 'selected' : ''; ?>>Vehicle Added</option>
                                <option value="profile_update" <?php echo $activity_type == 'profile_update' ? 'selected' : ''; ?>>Profile Update</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-gradient btn-block">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getTotalUsers($conn, $start_date, $end_date, $role, $status_filter); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getUsersByRole($conn, 'customer'); ?></div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getUsersByRole($conn, 'worker'); ?></div>
                <div class="stat-label">Workers</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getActiveUsers($conn, 7); ?></div>
                <div class="stat-label">Active (7 days)</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getNewUsersThisMonth($conn); ?></div>
                <div class="stat-label">New This Month</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number"><?php echo getAvgServicesPerUser($conn); ?></div>
                <div class="stat-label">Avg Services/User</div>
            </div>
        </div>
    </div>

    <!-- User Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">User Role Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="roleChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">User Registration Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="registrationChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activities -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent User Activities</h5>
                    <span class="badge badge-light">Last 100 Activities</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="userActivitiesTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Activity Type</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php echo getUserActivities($conn, $activity_type); ?>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="User activities pagination">
                        <ul class="pagination justify-content-center mb-0 mt-3" id="userActivitiesPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed User Report -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Details</h5>
            <span class="badge badge-light"><?php echo getTotalUsers($conn, $start_date, $end_date, $role, $status_filter); ?> Users</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="userDetailsTable">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Vehicles</th>
                            <th>Services</th>
                            <th>Registration Date</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo getUserReportData($conn, $start_date, $end_date, $role, $status_filter); ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="User details pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="userDetailsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeUserCharts();

    if ($('#userActivitiesTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#userActivitiesTable tbody tr'),
            paginationContainer: '#userActivitiesPagination',
            rowsPerPage: 10
        }).refresh();
    }

    if ($('#userDetailsTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#userDetailsTable tbody tr'),
            paginationContainer: '#userDetailsPagination',
            rowsPerPage: 10
        }).refresh();
    }
});

function initializeUserCharts() {
    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(getRoleLabels($conn)); ?>,
            datasets: [{
                data: <?php echo json_encode(getRoleData($conn)); ?>,
                backgroundColor: ['#dc3545', '#ffc107', '#28a745', '#007bff']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Registration Trend Chart
    const regCtx = document.getElementById('registrationChart').getContext('2d');
    new Chart(regCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(getRegistrationTrendLabels($conn)); ?>,
            datasets: [{
                label: 'New Registrations',
                data: <?php echo json_encode(getRegistrationTrendData($conn)); ?>,
                borderColor: '#ff8c00',
                backgroundColor: 'rgba(255, 140, 0, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function exportUserReport() {
    const filters = new URLSearchParams(window.location.search);
    window.open(`php/user-reports-export.php?${filters.toString()}`, '_blank');
}
</script>

<?php
// PHP Helper Functions for User Reports
function getTotalUsers($conn, $start_date, $end_date, $role, $status_filter) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    if (!empty($status_filter)) {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getUsersByRole($conn, $role) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getActiveUsers($conn, $days) {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as total 
        FROM activity_log 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    ");
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getNewUsersThisMonth($conn) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM users 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getAvgServicesPerUser($conn) {
    $stmt = $conn->prepare("
        SELECT AVG(service_count) as avg_services 
        FROM (
            SELECT user_id, COUNT(*) as service_count 
            FROM services 
            GROUP BY user_id
        ) as user_services
    ");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return round($result['avg_services'] ?? 0, 1);
}

function getRoleLabels($conn) {
    return ['Admin', 'Manager', 'Worker', 'Customer'];
}

function getRoleData($conn) {
    $roles = ['admin', 'manager', 'worker', 'customer'];
    $data = [];
    
    foreach ($roles as $role) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $data[] = $result['total'];
        $stmt->close();
    }
    
    return $data;
}

function getRegistrationTrendLabels($conn) {
    $labels = [];
    for ($i = 11; $i >= 0; $i--) {
        $labels[] = date('M Y', strtotime("-$i months"));
    }
    return $labels;
}

function getRegistrationTrendData($conn) {
    $data = [];
    for ($i = 11; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t', strtotime("-$i months"));
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ?");
        $stmt->bind_param("ss", $month_start, $month_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $data[] = $result['count'];
        $stmt->close();
    }
    return $data;
}

function getUserActivities($conn, $activity_type) {
    $sql = "SELECT al.*, u.fullname, u.role 
            FROM activity_log al 
            JOIN users u ON al.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($activity_type)) {
        $sql .= " AND al.activity_type = ?";
        $params[] = $activity_type;
        $types .= "s";
    }
    
    $sql .= " ORDER BY al.created_at DESC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $html .= "
        <tr>
            <td>{$row['fullname']}</td>
            <td><span class='badge badge-{$row['role']}'>{$row['role']}</span></td>
            <td>" . ucfirst(str_replace('_', ' ', $row['activity_type'])) . "</td>
            <td>{$row['description']}</td>
            <td><code>{$row['ip_address']}</code></td>
            <td>" . date('M j, Y H:i', strtotime($row['created_at'])) . "</td>
        </tr>";
    }
    
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='6' class='text-center py-4'>No activities found.</td></tr>";
    }
    
    return $html;
}

function getUserReportData($conn, $start_date, $end_date, $role, $status_filter) {
    $sql = "SELECT u.*, 
                   COUNT(DISTINCT v.id) as vehicle_count,
                   COUNT(DISTINCT s.id) as service_count,
                   MAX(al.created_at) as last_activity
            FROM users u
            LEFT JOIN vehicles v ON u.id = v.user_id
            LEFT JOIN services s ON u.id = s.user_id
            LEFT JOIN activity_log al ON u.id = al.user_id
            WHERE DATE(u.created_at) BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($role)) {
        $sql .= " AND u.role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    if (!empty($status_filter)) {
        $sql .= " AND u.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $html .= "
        <tr>
            <td>{$row['id_number']}</td>
            <td>{$row['fullname']}</td>
            <td>{$row['email']}</td>
            <td>{$row['phone']}</td>
            <td><span class='badge badge-{$row['role']}'>{$row['role']}</span></td>
            <td><span class='badge badge-success'>{$row['status']}</span></td>
            <td>{$row['vehicle_count']}</td>
            <td>{$row['service_count']}</td>
            <td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>
            <td>" . ($row['last_activity'] ? date('M j, Y H:i', strtotime($row['last_activity'])) : 'Never') . "</td>
        </tr>";
    }
    
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='10' class='text-center py-4'>No users found for the selected filters.</td></tr>";
    }
    
    return $html;
}

$conn->close();
?>