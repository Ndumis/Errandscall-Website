<?php
$page_title = "Analytics & Reports | ErrandsCall Portal";

include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

// Check access permissions
if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}

$conn = getDBConnection();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Analytics & Reports</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" id="exportCsv">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </button>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">Report Filters</h5>
        </div>
        <div class="card-body">
            <form id="reportFilters">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reportType">Report Type</label>
                            <select class="form-control" id="reportType" name="report_type">
                                <option value="overview">Overview Dashboard</option>
                                <option value="services">Services Report</option>
                                <option value="vehicles">Vehicles Report</option>
                                <option value="users">Users Report</option>
                                <option value="performance">Performance Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dateRange">Date Range</label>
                            <select class="form-control" id="dateRange" name="date_range">
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 custom-date" style="display: none;">
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date">
                        </div>
                    </div>
                    <div class="col-md-3 custom-date" style="display: none;">
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-gradient btn-block">
                                <i class="fas fa-chart-bar mr-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="totalServices">0</div>
                <div class="stat-label">Total Services</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="completedServices">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="revenue">R0</div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="avgCompletion">0h</div>
                <div class="stat-label">Avg Completion</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Services Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="servicesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Service Types</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceTypesChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="card">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detailed Report</h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-light" id="refreshReport">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn btn-sm btn-light" id="saveReport">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="reportContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading report...</span>
                    </div>
                    <p class="mt-2">Generating report...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/analytics-management.js"></script>