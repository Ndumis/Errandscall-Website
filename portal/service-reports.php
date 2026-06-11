<?php
$page_title = "Service Reports | ErrandsCall Portal";
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
$date_range = $_GET['date_range'] ?? 'this_month';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$service_type = $_GET['service_type'] ?? '';
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$custom_date_style = $date_range === 'custom' ? '' : ' style="display: none;"';
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Service Reports</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportServiceReport()">
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
            <form id="serviceFilters" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dateRange">Date Range</label>
                            <select class="form-control" id="dateRange" name="date_range">
                                <option value="today" <?php echo $date_range == 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="yesterday" <?php echo $date_range == 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                                <option value="this_week" <?php echo $date_range == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="last_week" <?php echo $date_range == 'last_week' ? 'selected' : ''; ?>>Last Week</option>
                                <option value="this_month" <?php echo $date_range == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                                <option value="last_month" <?php echo $date_range == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                                <option value="this_quarter" <?php echo $date_range == 'this_quarter' ? 'selected' : ''; ?>>This Quarter</option>
                                <option value="last_quarter" <?php echo $date_range == 'last_quarter' ? 'selected' : ''; ?>>Last Quarter</option>
                                <option value="this_year" <?php echo $date_range == 'this_year' ? 'selected' : ''; ?>>This Year</option>
                                <option value="last_year" <?php echo $date_range == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
                                <option value="custom" <?php echo $date_range == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 custom-date"<?php echo $custom_date_style; ?>>
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3 custom-date"<?php echo $custom_date_style; ?>>
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="serviceType">Service Type</label>
                            <select class="form-control" id="serviceType" name="service_type">
                                <option value="">All Types</option>
                                <option value="Vehicle License Renewal" <?php echo $service_type == 'Vehicle License Renewal' ? 'selected' : ''; ?>>License Renewal</option>
                                <option value="Business Vehicle Registration" <?php echo $service_type == 'Business Vehicle Registration' ? 'selected' : ''; ?>>Business Registration</option>
                                <option value="Vehicle De-registration" <?php echo $service_type == 'Vehicle De-registration' ? 'selected' : ''; ?>>De-registration</option>
                                <option value="Change of Ownership" <?php echo $service_type == 'Change of Ownership' ? 'selected' : ''; ?>>Change of Ownership</option>
                                <option value="Roadworthy Certificates" <?php echo $service_type == 'Roadworthy Certificates' ? 'selected' : ''; ?>>Roadworthy Certificates</option>
                                <option value="Other" <?php echo $service_type == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="assigned" <?php echo $status == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                <option value="in_progress" <?php echo $status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="">All Priorities</option>
                                <option value="Low" <?php echo $priority == 'Low' ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo $priority == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo $priority == 'High' ? 'selected' : ''; ?>>High</option>
                                <option value="Urgent" <?php echo $priority == 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-gradient">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="service-reports.php" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getTotalServices($conn, $start_date, $end_date, $service_type, $status, $priority); ?></div>
                <div class="stat-label">Total Services</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getCompletedServices($conn, $start_date, $end_date, $service_type, $priority); ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getAverageCompletionTime($conn, $start_date, $end_date, $service_type); ?></div>
                <div class="stat-label">Avg Completion (Hours)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getCompletionRate($conn, $start_date, $end_date, $service_type); ?>%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
    </div>

    <!-- Service Type Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Service Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Service Report -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Service Details</h5>
            <span class="badge badge-light"><?php echo getTotalServices($conn, $start_date, $end_date, $service_type, $status, $priority); ?> Records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="serviceDetailsTable">
                    <thead>
                        <tr>
                            <th>Service ID</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created Date</th>
                            <th>Completion Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo getServiceReportData($conn, $start_date, $end_date, $service_type, $status, $priority); ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Service details pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="serviceDetailsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/date-range-filter.js"></script>
<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeServiceCharts();
    initDateRangeFilter('#serviceFilters');

    if ($('#serviceDetailsTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#serviceDetailsTable tbody tr'),
            paginationContainer: '#serviceDetailsPagination',
            rowsPerPage: 10
        }).refresh();
    }
});

function initializeServiceCharts() {
    // Service Type Chart
    const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
    new Chart(serviceTypeCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(getServiceTypeLabels($conn, $start_date, $end_date)); ?>,
            datasets: [{
                data: <?php echo json_encode(getServiceTypeData($conn, $start_date, $end_date)); ?>,
                backgroundColor: ['#ff8c00', '#ff6b00', '#ffd700', '#2c3e50', '#28a745', '#dc3545']
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

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(getStatusLabels($conn, $start_date, $end_date)); ?>,
            datasets: [{
                data: <?php echo json_encode(getStatusData($conn, $start_date, $end_date)); ?>,
                backgroundColor: ['#ffc107', '#17a2b8', '#007bff', '#28a745', '#dc3545']
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
}

function exportServiceReport() {
    const filters = new URLSearchParams(window.location.search);
    window.open(`php/service-reports-export.php?${filters.toString()}`, '_blank');
}
</script>

<?php
// PHP Helper Functions
function getTotalServices($conn, $start_date, $end_date, $service_type, $status, $priority) {
    $sql = "SELECT COUNT(*) as total FROM services WHERE DATE(created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($service_type)) {
        $sql .= " AND service_type = ?";
        $params[] = $service_type;
        $types .= "s";
    }
    
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($priority)) {
        $sql .= " AND priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getCompletedServices($conn, $start_date, $end_date, $service_type, $priority) {
    $sql = "SELECT COUNT(*) as total FROM services WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($service_type)) {
        $sql .= " AND service_type = ?";
        $params[] = $service_type;
        $types .= "s";
    }
    
    if (!empty($priority)) {
        $sql .= " AND priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getAverageCompletionTime($conn, $start_date, $end_date, $service_type) {
    $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time 
            FROM services 
            WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($service_type)) {
        $sql .= " AND service_type = ?";
        $params[] = $service_type;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return round($result['avg_time'] ?? 0, 1);
}

function getCompletionRate($conn, $start_date, $end_date, $service_type) {
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM services 
            WHERE DATE(created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($service_type)) {
        $sql .= " AND service_type = ?";
        $params[] = $service_type;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result['total'] > 0) {
        return round(($result['completed'] / $result['total']) * 100, 1);
    }
    
    return 0;
}

function getServiceTypeLabels($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("SELECT DISTINCT service_type FROM services WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['service_type'];
    }
    $stmt->close();
    
    return $labels;
}

function getServiceTypeData($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("SELECT service_type, COUNT(*) as count FROM services WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY service_type");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['count'];
    }
    $stmt->close();
    
    return $data;
}

function getStatusLabels($conn, $start_date, $end_date) {
    return ['Pending', 'Assigned', 'In Progress', 'Completed', 'Cancelled'];
}

function getStatusData($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM services WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $statusCounts = [];
    while ($row = $result->fetch_assoc()) {
        $statusCounts[$row['status']] = $row['count'];
    }
    $stmt->close();
    
    return [
        $statusCounts['pending'] ?? 0,
        $statusCounts['assigned'] ?? 0,
        $statusCounts['in_progress'] ?? 0,
        $statusCounts['completed'] ?? 0,
        $statusCounts['cancelled'] ?? 0
    ];
}

function getServiceReportData($conn, $start_date, $end_date, $service_type, $status, $priority) {
    $sql = "SELECT s.*, u.fullname as customer_name, v.license_plate, cm.name as make_name, cmodel.name as model_name
            FROM services s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN vehicles v ON s.vehicle_id = v.id
            LEFT JOIN car_makes cm ON v.make = cm.id
            LEFT JOIN car_models cmodel ON v.model = cmodel.id
            WHERE DATE(s.created_at) BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($service_type)) {
        $sql .= " AND s.service_type = ?";
        $params[] = $service_type;
        $types .= "s";
    }
    
    if (!empty($status)) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($priority)) {
        $sql .= " AND s.priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    $sql .= " ORDER BY s.created_at DESC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $completion_time = $row['status'] == 'completed' ? 
            round((strtotime($row['updated_at']) - strtotime($row['created_at'])) / 3600, 1) . 'h' : 
            'N/A';
            
        $html .= "
        <tr>
            <td>#{$row['id']}</td>
            <td>{$row['service_type']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['make_name']} {$row['model_name']} ({$row['license_plate']})</td>
            <td><span class='status-badge status-{$row['status']}'>" . ucfirst($row['status']) . "</span></td>
            <td><span class='badge badge-primary'>{$row['priority']}</span></td>
            <td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>
            <td>{$completion_time}</td>
            <td>
                <a href='services-management.php?view={$row['id']}' class='btn btn-sm btn-outline-primary'>
                    <i class='fas fa-eye'></i>
                </a>
            </td>
        </tr>";
    }
    
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='9' class='text-center py-4'>No services found for the selected filters.</td></tr>";
    }
    
    return $html;
}

$conn->close();
?>