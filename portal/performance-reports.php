<?php
$page_title = "Performance Reports | ErrandsCall Portal";
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
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$worker_id = $_GET['worker_id'] ?? '';
$metric = $_GET['metric'] ?? 'completion_rate';
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Performance Reports</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportPerformanceReport()">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">Performance Metrics</h5>
        </div>
        <div class="card-body">
            <form id="performanceFilters" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="worker">Worker</label>
                            <select class="form-control" id="worker" name="worker_id">
                                <option value="">All Workers</option>
                                <?php echo getWorkerOptions($conn, $worker_id); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="metric">Primary Metric</label>
                            <select class="form-control" id="metric" name="metric">
                                <option value="completion_rate" <?php echo $metric == 'completion_rate' ? 'selected' : ''; ?>>Completion Rate</option>
                                <option value="avg_completion_time" <?php echo $metric == 'avg_completion_time' ? 'selected' : ''; ?>>Avg Completion Time</option>
                                <option value="total_services" <?php echo $metric == 'total_services' ? 'selected' : ''; ?>>Total Services</option>
                                <option value="customer_rating" <?php echo $metric == 'customer_rating' ? 'selected' : ''; ?>>Customer Rating</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-gradient">
                            <i class="fas fa-chart-line mr-2"></i>Update Metrics
                        </button>
                        <a href="performance-reports.php" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getOverallCompletionRate($conn, $start_date, $end_date); ?>%</div>
                <div class="stat-label">Overall Completion Rate</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getAvgCompletionTime($conn, $start_date, $end_date); ?>h</div>
                <div class="stat-label">Avg Completion Time</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getTotalCompletedServices($conn, $start_date, $end_date); ?></div>
                <div class="stat-label">Services Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getActiveWorkers($conn, $start_date, $end_date); ?></div>
                <div class="stat-label">Active Workers</div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Worker Performance Comparison</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Performance Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trends -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Worker Performance Details -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Worker Performance Details</h5>
            <span class="badge badge-light"><?php echo getActiveWorkers($conn, $start_date, $end_date); ?> Workers</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="workerPerformanceDetailsTable">
                    <thead>
                        <tr>
                            <th>Worker</th>
                            <th>Total Assignments</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                            <th>Avg Time (Hours)</th>
                            <th>On Time Rate</th>
                            <th>Customer Rating</th>
                            <th>Performance Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo getWorkerPerformanceData($conn, $start_date, $end_date, $worker_id); ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Worker performance details pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="workerPerformanceDetailsPagination"></ul>
            </nav>
        </div>
    </div>

    <!-- Service Type Performance -->
    <div class="card mt-4">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">Service Type Performance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="serviceTypePerformanceTable">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Total Services</th>
                            <th>Avg Completion Time</th>
                            <th>Completion Rate</th>
                            <th>Most Efficient Worker</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo getServiceTypePerformance($conn, $start_date, $end_date); ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Service type performance pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="serviceTypePerformancePagination"></ul>
            </nav>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePerformanceCharts();

    if ($('#workerPerformanceDetailsTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#workerPerformanceDetailsTable tbody tr'),
            paginationContainer: '#workerPerformanceDetailsPagination',
            rowsPerPage: 10
        }).refresh();
    }

    if ($('#serviceTypePerformanceTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#serviceTypePerformanceTable tbody tr'),
            paginationContainer: '#serviceTypePerformancePagination',
            rowsPerPage: 10
        }).refresh();
    }
});

function initializePerformanceCharts() {
    // Worker Performance Comparison
    const perfCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(perfCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(getWorkerPerformanceLabels($conn, $start_date, $end_date)); ?>,
            datasets: [{
                label: 'Completion Rate (%)',
                data: <?php echo json_encode(getWorkerCompletionRates($conn, $start_date, $end_date)); ?>,
                backgroundColor: '#28a745',
                yAxisID: 'y'
            }, {
                label: 'Avg Time (Hours)',
                data: <?php echo json_encode(getWorkerCompletionTimes($conn, $start_date, $end_date)); ?>,
                backgroundColor: '#ffc107',
                yAxisID: 'y1',
                type: 'line'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Completion Rate (%)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Avg Time (Hours)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Performance Distribution
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (90-100%)', 'Good (75-89%)', 'Average (60-74%)', 'Needs Improvement (<60%)'],
            datasets: [{
                data: <?php echo json_encode(getPerformanceDistribution($conn, $start_date, $end_date)); ?>,
                backgroundColor: ['#28a745', '#20c997', '#ffc107', '#dc3545']
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

    // Performance Trends
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(getPerformanceTrendLabels($conn)); ?>,
            datasets: [{
                label: 'Avg Completion Rate',
                data: <?php echo json_encode(getPerformanceTrendData($conn, 'completion_rate')); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true
            }, {
                label: 'Avg Completion Time',
                data: <?php echo json_encode(getPerformanceTrendData($conn, 'completion_time')); ?>,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Completion Rate (%)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Avg Time (Hours)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

function exportPerformanceReport() {
    const filters = new URLSearchParams(window.location.search);
    window.open(`php/performance-reports-export.php?${filters.toString()}`, '_blank');
}
</script>

<?php
// PHP Helper Functions for Performance Reports
function getWorkerOptions($conn, $selected_worker) {
    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'worker' ORDER BY fullname");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = $row['id'] == $selected_worker ? 'selected' : '';
        $options .= "<option value='{$row['id']}' {$selected}>{$row['fullname']}</option>";
    }
    $stmt->close();
    
    return $options;
}

function getOverallCompletionRate($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM services 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($result['total'] > 0) {
        return round(($result['completed'] / $result['total']) * 100, 1);
    }
    
    return 0;
}

function getAvgCompletionTime($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time
        FROM services 
        WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return round($result['avg_time'] ?? 0, 1);
}

function getTotalCompletedServices($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM services 
        WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getActiveWorkers($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT assigned_to) as total
        FROM services 
        WHERE assigned_to IS NOT NULL AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getWorkerPerformanceLabels($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT u.fullname
        FROM users u
        JOIN services s ON u.id = s.assigned_to
        WHERE u.role = 'worker' AND DATE(s.created_at) BETWEEN ? AND ?
        GROUP BY u.id, u.fullname
        ORDER BY COUNT(s.id) DESC
        LIMIT 10
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['fullname'];
    }
    $stmt->close();
    
    return $labels;
}

function getWorkerCompletionRates($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT u.fullname,
               COUNT(s.id) as total,
               SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM users u
        JOIN services s ON u.id = s.assigned_to
        WHERE u.role = 'worker' AND DATE(s.created_at) BETWEEN ? AND ?
        GROUP BY u.id, u.fullname
        ORDER BY COUNT(s.id) DESC
        LIMIT 10
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rate = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1) : 0;
        $rates[] = $rate;
    }
    $stmt->close();
    
    return $rates;
}

function getWorkerCompletionTimes($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT u.fullname,
               AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as avg_time
        FROM users u
        JOIN services s ON u.id = s.assigned_to
        WHERE u.role = 'worker' AND s.status = 'completed' AND DATE(s.created_at) BETWEEN ? AND ?
        GROUP BY u.id, u.fullname
        ORDER BY COUNT(s.id) DESC
        LIMIT 10
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $times = [];
    while ($row = $result->fetch_assoc()) {
        $times[] = round($row['avg_time'] ?? 0, 1);
    }
    $stmt->close();
    
    return $times;
}

function getPerformanceDistribution($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT u.id,
               COUNT(s.id) as total_assignments,
               SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed,
               AVG(CASE WHEN s.status = 'completed' THEN TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) ELSE NULL END) as avg_time,
               SUM(CASE WHEN s.status = 'completed' AND TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) <= 24 THEN 1 ELSE 0 END) as on_time
        FROM users u
        LEFT JOIN services s ON u.id = s.assigned_to AND DATE(s.created_at) BETWEEN ? AND ?
        WHERE u.role = 'worker'
        GROUP BY u.id
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Buckets: Excellent (90-100%), Good (75-89%), Average (60-74%), Needs Improvement (<60%)
    $buckets = [0, 0, 0, 0];

    while ($row = $result->fetch_assoc()) {
        if ($row['total_assignments'] == 0) {
            continue;
        }

        $completion_rate = ($row['completed'] / $row['total_assignments']) * 100;
        $on_time_rate = $row['completed'] > 0 ? ($row['on_time'] / $row['completed']) * 100 : 0;
        $avg_time = $row['avg_time'] ?? 0;

        $performance_score = min(100, ($completion_rate * 0.4) + ((100 - min($avg_time, 48) * 2.08) * 0.3) + ($on_time_rate * 0.3));

        if ($performance_score >= 90) {
            $buckets[0]++;
        } elseif ($performance_score >= 75) {
            $buckets[1]++;
        } elseif ($performance_score >= 60) {
            $buckets[2]++;
        } else {
            $buckets[3]++;
        }
    }
    $stmt->close();

    return $buckets;
}

function getPerformanceTrendLabels($conn) {
    $labels = [];
    for ($i = 11; $i >= 0; $i--) {
        $labels[] = date('M Y', strtotime("-$i months"));
    }
    return $labels;
}

function getPerformanceTrendData($conn, $type) {
    $data = [];
    for ($i = 11; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t', strtotime("-$i months"));
        
        if ($type == 'completion_rate') {
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM services 
                WHERE created_at BETWEEN ? AND ?
            ");
        } else {
            $stmt = $conn->prepare("
                SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time
                FROM services 
                WHERE status = 'completed' AND created_at BETWEEN ? AND ?
            ");
        }
        
        $stmt->bind_param("ss", $month_start, $month_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($type == 'completion_rate') {
            $value = $result['total'] > 0 ? round(($result['completed'] / $result['total']) * 100, 1) : 0;
        } else {
            $value = round($result['avg_time'] ?? 0, 1);
        }
        
        $data[] = $value;
        $stmt->close();
    }
    return $data;
}

function getWorkerPerformanceData($conn, $start_date, $end_date, $worker_id) {
    $sql = "SELECT u.id, u.fullname,
                   COUNT(s.id) as total_assignments,
                   SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed,
                   AVG(CASE WHEN s.status = 'completed' THEN TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) ELSE NULL END) as avg_time,
                   SUM(CASE WHEN s.status = 'completed' AND TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) <= 24 THEN 1 ELSE 0 END) as on_time
            FROM users u
            LEFT JOIN services s ON u.id = s.assigned_to AND DATE(s.created_at) BETWEEN ? AND ?
            WHERE u.role = 'worker'";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($worker_id)) {
        $sql .= " AND u.id = ?";
        $params[] = $worker_id;
        $types .= "i";
    }
    
    $sql .= " GROUP BY u.id, u.fullname ORDER BY completed DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $completion_rate = $row['total_assignments'] > 0 ? 
            round(($row['completed'] / $row['total_assignments']) * 100, 1) : 0;
            
        $on_time_rate = $row['completed'] > 0 ? 
            round(($row['on_time'] / $row['completed']) * 100, 1) : 0;
            
        $avg_time = round($row['avg_time'] ?? 0, 1);
        
        // Simple performance score calculation
        $performance_score = min(100, ($completion_rate * 0.4) + ((100 - min($avg_time, 48) * 2.08) * 0.3) + ($on_time_rate * 0.3));
        
        $html .= "
        <tr>
            <td><strong>{$row['fullname']}</strong></td>
            <td>{$row['total_assignments']}</td>
            <td>{$row['completed']}</td>
            <td>
                <div class='progress' style='height: 20px;'>
                    <div class='progress-bar' role='progressbar' style='width: {$completion_rate}%' 
                         aria-valuenow='{$completion_rate}' aria-valuemin='0' aria-valuemax='100'>
                        {$completion_rate}%
                    </div>
                </div>
            </td>
            <td>{$avg_time}h</td>
            <td>{$on_time_rate}%</td>
            <td>
                <span class='rating-stars'>
                    <i class='fas fa-star text-warning'></i>
                    <i class='fas fa-star text-warning'></i>
                    <i class='fas fa-star text-warning'></i>
                    <i class='fas fa-star text-warning'></i>
                    <i class='far fa-star text-warning'></i>
                </span>
            </td>
            <td>
                <span class='badge " . getPerformanceBadgeClass($performance_score) . "'>
                    " . round($performance_score) . "
                </span>
            </td>
        </tr>";
    }
    
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='8' class='text-center py-4'>No performance data found for the selected filters.</td></tr>";
    }
    
    return $html;
}

function getServiceTypePerformance($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            service_type,
            COUNT(*) as total_services,
            AVG(CASE WHEN status = 'completed' THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) ELSE NULL END) as avg_time,
            ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as completion_rate,
            (SELECT u.fullname 
             FROM services s2 
             JOIN users u ON s2.assigned_to = u.id 
             WHERE s2.service_type = s.service_type AND s2.status = 'completed' 
             GROUP BY s2.assigned_to 
             ORDER BY AVG(TIMESTAMPDIFF(HOUR, s2.created_at, s2.updated_at)) ASC 
             LIMIT 1) as efficient_worker
        FROM services s
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY service_type
        ORDER BY total_services DESC
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $html .= "
        <tr>
            <td>{$row['service_type']}</td>
            <td>{$row['total_services']}</td>
            <td>" . round($row['avg_time'] ?? 0, 1) . "h</td>
            <td>{$row['completion_rate']}%</td>
            <td>{$row['efficient_worker']}</td>
        </tr>";
    }
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='5' class='text-center py-4'>No service type performance data found.</td></tr>";
    }
    
    return $html;
}

function getPerformanceBadgeClass($score) {
    if ($score >= 90) return 'badge-success';
    if ($score >= 75) return 'badge-primary';
    if ($score >= 60) return 'badge-warning';
    return 'badge-danger';
}

$conn->close();
?>