<?php
$page_title = "Vehicle Reports | ErrandsCall Portal";
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
$make = $_GET['make'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? '';
$expiry_status = $_GET['expiry_status'] ?? '';
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Vehicle Reports</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportVehicleReport()">
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
            <form id="vehicleFilters" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="startDate">Registration Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="endDate">Registration End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="make">Make</label>
                            <select class="form-control" id="make" name="make">
                                <option value="">All Makes</option>
                                <?php echo getCarMakeOptions($conn, $make); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="vehicleType">Vehicle Type</label>
                            <select class="form-control" id="vehicleType" name="vehicle_type">
                                <option value="">All Types</option>
                                <?php echo getVehicleTypeOptions($conn, $vehicle_type); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="expiryStatus">Expiry Status</label>
                            <select class="form-control" id="expiryStatus" name="expiry_status">
                                <option value="">All</option>
                                <option value="expired" <?php echo $expiry_status == 'expired' ? 'selected' : ''; ?>>Expired</option>
                                <option value="expiring_30" <?php echo $expiry_status == 'expiring_30' ? 'selected' : ''; ?>>Expiring in 30 Days</option>
                                <option value="expiring_60" <?php echo $expiry_status == 'expiring_60' ? 'selected' : ''; ?>>Expiring in 60 Days</option>
                                <option value="valid" <?php echo $expiry_status == 'valid' ? 'selected' : ''; ?>>Valid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-gradient">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="vehicle-reports.php" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vehicle Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getTotalVehicles($conn, $start_date, $end_date, $make, $vehicle_type, $expiry_status); ?></div>
                <div class="stat-label">Total Vehicles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getExpiringVehicles($conn, 30); ?></div>
                <div class="stat-label">Expiring in 30 Days</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getExpiredVehicles($conn); ?></div>
                <div class="stat-label">Expired Licenses</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo getAverageVehicleAge($conn); ?></div>
                <div class="stat-label">Avg Vehicle Age (Years)</div>
            </div>
        </div>
    </div>

    <!-- Vehicle Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Top Vehicle Makes</h5>
                </div>
                <div class="card-body">
                    <canvas id="makeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Vehicle Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiry Analysis -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">License Expiry Analysis</h5>
                </div>
                <div class="card-body">
                    <canvas id="expiryChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Vehicle Report -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vehicle Details</h5>
            <span class="badge badge-light"><?php echo getTotalVehicles($conn, $start_date, $end_date, $make, $vehicle_type, $expiry_status); ?> Records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="vehicleDetailsTable">
                    <thead>
                        <tr>
                            <th>License Plate</th>
                            <th>Make & Model</th>
                            <th>Year</th>
                            <th>Color</th>
                            <th>Owner</th>
                            <th>Disc Expiry</th>
                            <th>License Expiry</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo getVehicleReportData($conn, $start_date, $end_date, $make, $vehicle_type, $expiry_status); ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Vehicle details pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="vehicleDetailsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeVehicleCharts();

    if ($('#vehicleDetailsTable tbody tr').length > 0) {
        createPagination({
            getItems: () => $('#vehicleDetailsTable tbody tr'),
            paginationContainer: '#vehicleDetailsPagination',
            rowsPerPage: 10
        }).refresh();
    }
});

function initializeVehicleCharts() {
    // Make Distribution Chart
    const makeCtx = document.getElementById('makeChart').getContext('2d');
    new Chart(makeCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(getTopMakeLabels($conn)); ?>,
            datasets: [{
                label: 'Number of Vehicles',
                data: <?php echo json_encode(getTopMakeData($conn)); ?>,
                backgroundColor: '#ff8c00'
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

    // Type Distribution Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(getVehicleTypeLabels($conn)); ?>,
            datasets: [{
                data: <?php echo json_encode(getVehicleTypeData($conn)); ?>,
                backgroundColor: ['#ff8c00', '#ff6b00', '#ffd700', '#2c3e50', '#28a745']
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

    // Expiry Chart
    const expiryCtx = document.getElementById('expiryChart').getContext('2d');
    new Chart(expiryCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(getExpiryTimelineLabels()); ?>,
            datasets: [{
                label: 'Licenses Expiring',
                data: <?php echo json_encode(getExpiryTimelineData($conn)); ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
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

function exportVehicleReport() {
    const filters = new URLSearchParams(window.location.search);
    window.open(`php/vehicle-reports-export.php?${filters.toString()}`, '_blank');
}
</script>

<?php
// PHP Helper Functions for Vehicle Reports
function getCarMakeOptions($conn, $selected_make) {
    $stmt = $conn->prepare("SELECT id, name FROM car_makes WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = $row['id'] == $selected_make ? 'selected' : '';
        $options .= "<option value='{$row['id']}' {$selected}>{$row['name']}</option>";
    }
    $stmt->close();
    
    return $options;
}

function getVehicleTypeOptions($conn, $selected_type) {
    $stmt = $conn->prepare("SELECT DISTINCT vehicle_type FROM car_models WHERE vehicle_type IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = $row['vehicle_type'] == $selected_type ? 'selected' : '';
        $options .= "<option value='{$row['vehicle_type']}' {$selected}>" . ucfirst($row['vehicle_type']) . "</option>";
    }
    $stmt->close();
    
    return $options;
}

function getTotalVehicles($conn, $start_date, $end_date, $make, $vehicle_type, $expiry_status) {
    $sql = "SELECT COUNT(*) as total FROM vehicles v 
            JOIN car_makes cm ON v.make = cm.id
            JOIN car_models cmodel ON v.model = cmodel.id
            WHERE DATE(v.created_at) BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($make)) {
        $sql .= " AND v.make = ?";
        $params[] = $make;
        $types .= "i";
    }
    
    if (!empty($vehicle_type)) {
        $sql .= " AND cmodel.vehicle_type = ?";
        $params[] = $vehicle_type;
        $types .= "s";
    }
    
    if (!empty($expiry_status)) {
        switch ($expiry_status) {
            case 'expired':
                $sql .= " AND v.disc_expiry < CURDATE()";
                break;
            case 'expiring_30':
                $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'expiring_60':
                $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)";
                break;
            case 'valid':
                $sql .= " AND v.disc_expiry > CURDATE()";
                break;
        }
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getExpiringVehicles($conn, $days) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vehicles WHERE disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)");
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getExpiredVehicles($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vehicles WHERE disc_expiry < CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total'];
}

function getAverageVehicleAge($conn) {
    $stmt = $conn->prepare("SELECT AVG(YEAR(CURDATE()) - year) as avg_age FROM vehicles WHERE year IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return round($result['avg_age'] ?? 0, 1);
}

function getTopMakeLabels($conn) {
    $stmt = $conn->prepare("
        SELECT cm.name, COUNT(*) as count 
        FROM vehicles v 
        JOIN car_makes cm ON v.make = cm.id 
        GROUP BY cm.name 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['name'];
    }
    $stmt->close();
    
    return $labels;
}

function getTopMakeData($conn) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM vehicles v 
        JOIN car_makes cm ON v.make = cm.id 
        GROUP BY cm.name 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['count'];
    }
    $stmt->close();
    
    return $data;
}

function getVehicleTypeLabels($conn) {
    $stmt = $conn->prepare("
        SELECT DISTINCT cmodel.vehicle_type 
        FROM vehicles v 
        JOIN car_models cmodel ON v.model = cmodel.id 
        WHERE cmodel.vehicle_type IS NOT NULL
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = ucfirst($row['vehicle_type']);
    }
    $stmt->close();
    
    return $labels;
}

function getVehicleTypeData($conn) {
    $stmt = $conn->prepare("
        SELECT cmodel.vehicle_type, COUNT(*) as count 
        FROM vehicles v 
        JOIN car_models cmodel ON v.model = cmodel.id 
        WHERE cmodel.vehicle_type IS NOT NULL
        GROUP BY cmodel.vehicle_type 
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['count'];
    }
    $stmt->close();
    
    return $data;
}

function getExpiryTimelineLabels() {
    $labels = [];
    for ($i = 0; $i < 12; $i++) {
        $labels[] = date('M Y', strtotime("+$i months"));
    }
    return $labels;
}

function getExpiryTimelineData($conn) {
    $data = [];
    for ($i = 0; $i < 12; $i++) {
        $month_start = date('Y-m-01', strtotime("+$i months"));
        $month_end = date('Y-m-t', strtotime("+$i months"));
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM vehicles WHERE disc_expiry BETWEEN ? AND ?");
        $stmt->bind_param("ss", $month_start, $month_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $data[] = $result['count'];
        $stmt->close();
    }
    return $data;
}

function getVehicleReportData($conn, $start_date, $end_date, $make, $vehicle_type, $expiry_status) {
    $sql = "SELECT v.*, u.fullname as owner_name, cm.name as make_name, cmodel.name as model_name,
                   cmodel.vehicle_type, DATEDIFF(v.disc_expiry, CURDATE()) as days_until_expiry
            FROM vehicles v
            JOIN users u ON v.user_id = u.id
            JOIN car_makes cm ON v.make = cm.id
            JOIN car_models cmodel ON v.model = cmodel.id
            WHERE DATE(v.created_at) BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($make)) {
        $sql .= " AND v.make = ?";
        $params[] = $make;
        $types .= "i";
    }
    
    if (!empty($vehicle_type)) {
        $sql .= " AND cmodel.vehicle_type = ?";
        $params[] = $vehicle_type;
        $types .= "s";
    }
    
    if (!empty($expiry_status)) {
        switch ($expiry_status) {
            case 'expired':
                $sql .= " AND v.disc_expiry < CURDATE()";
                break;
            case 'expiring_30':
                $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'expiring_60':
                $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)";
                break;
            case 'valid':
                $sql .= " AND v.disc_expiry > CURDATE()";
                break;
        }
    }
    
    $sql .= " ORDER BY v.disc_expiry ASC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    while ($row = $result->fetch_assoc()) {
        $expiry_status = '';
        $status_class = '';
        
        if ($row['days_until_expiry'] < 0) {
            $expiry_status = 'Expired';
            $status_class = 'text-danger';
        } elseif ($row['days_until_expiry'] <= 30) {
            $expiry_status = 'Expiring Soon';
            $status_class = 'text-warning';
        } else {
            $expiry_status = 'Valid';
            $status_class = 'text-success';
        }
        
        $html .= "
        <tr>
            <td><strong>{$row['license_plate']}</strong></td>
            <td>{$row['make_name']} {$row['model_name']}</td>
            <td>{$row['year']}</td>
            <td>{$row['color']}</td>
            <td>{$row['owner_name']}</td>
            <td>" . date('M j, Y', strtotime($row['disc_expiry'])) . "</td>
            <td>" . date('M j, Y', strtotime($row['license_expiry'])) . "</td>
            <td><span class='{$status_class}'>{$expiry_status}</span></td>
            <td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>
        </tr>";
    }
    
    $stmt->close();
    
    if (empty($html)) {
        $html = "<tr><td colspan='9' class='text-center py-4'>No vehicles found for the selected filters.</td></tr>";
    }
    
    return $html;
}

$conn->close();
?>