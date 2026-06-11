<?php
$page_title = "Analytics Dashboard | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Analytics Dashboard</h5>
                        <div class="date-filter d-flex align-items-center">
                            <select class="form-control form-control-sm mr-2" id="dateRange" onchange="onDateRangeChange()">
                                <option value="week">Last 7 Days</option>
                                <option value="month" selected>Last 30 Days</option>
                                <option value="quarter">Last 3 Months</option>
                                <option value="year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                            <input type="date" class="form-control form-control-sm mr-1 d-none" id="customStartDate" onchange="loadAnalytics()">
                            <input type="date" class="form-control form-control-sm d-none" id="customEndDate" onchange="loadAnalytics()">
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="stat-number text-primary" id="totalServices">0</div>
                                    <div class="stat-label">Total Services</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="stat-number text-success" id="completedServices">0</div>
                                    <div class="stat-label">Completed</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="stat-number text-warning" id="revenue">$0</div>
                                    <div class="stat-label">Revenue</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="stat-number text-info" id="activeWorkers">0</div>
                                    <div class="stat-label">Active Workers</div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Service Trends</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="servicesChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Service Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="serviceDistributionChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Worker Performance -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Worker Performance</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="workerPerformanceTable">
                                                <thead>
                                                    <tr>
                                                        <th>Worker</th>
                                                        <th>Completed Services</th>
                                                        <th>Average Rating</th>
                                                        <th>Total Ratings</th>
                                                        <th>Performance Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="workerPerformanceBody">
                                                    <!-- Data will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <nav aria-label="Worker performance pagination">
                                            <ul class="pagination justify-content-center mb-0 mt-3" id="workerPerformancePagination"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let servicesChart, distributionChart;

document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics();
});

function onDateRangeChange() {
    const isCustom = document.getElementById('dateRange').value === 'custom';
    const startInput = document.getElementById('customStartDate');
    const endInput = document.getElementById('customEndDate');

    startInput.classList.toggle('d-none', !isCustom);
    endInput.classList.toggle('d-none', !isCustom);

    if (isCustom && (!startInput.value || !endInput.value)) {
        return;
    }

    loadAnalytics();
}

function formatDateForApi(date) {
    return date.toISOString().split('T')[0];
}

function getDateRange() {
    const dateRange = document.getElementById('dateRange').value;
    const end = new Date();
    const start = new Date();

    switch (dateRange) {
        case 'week':
            start.setDate(end.getDate() - 6);
            break;
        case 'quarter':
            start.setMonth(end.getMonth() - 3);
            break;
        case 'year':
            start.setFullYear(end.getFullYear() - 1);
            break;
        case 'custom': {
            const customStart = document.getElementById('customStartDate').value;
            const customEnd = document.getElementById('customEndDate').value;
            return {
                startDate: customStart || formatDateForApi(start),
                endDate: customEnd || formatDateForApi(end)
            };
        }
        case 'month':
        default:
            start.setDate(end.getDate() - 29);
    }

    return { startDate: formatDateForApi(start), endDate: formatDateForApi(end) };
}

function loadAnalytics() {
    const { startDate, endDate } = getDateRange();

    fetch(`php/analytics-management.php?report_type=overview&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateQuickStats(data.data.stats);
                updateCharts(data.data.charts);
            }
        })
        .catch(error => console.error('Error:', error));

    loadWorkerPerformance();
}

function updateQuickStats(stats) {
    stats = stats || {};
    document.getElementById('totalServices').textContent = stats.total_services || 0;
    document.getElementById('completedServices').textContent = stats.completed_services || 0;
    document.getElementById('revenue').textContent = '$' + (stats.revenue || 0);
    document.getElementById('activeWorkers').textContent = stats.active_workers || 0;
}

function updateCharts(charts) {
    updateServicesChart(charts?.servicesTrend);
    updateDistributionChart(charts?.serviceTypes);
}

function updateServicesChart(trend) {
    const ctx = document.getElementById('servicesChart').getContext('2d');

    if (servicesChart) {
        servicesChart.destroy();
    }

    const labels = trend?.labels || [];

    servicesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Services Created',
                data: trend?.created || [],
                borderColor: '#ff8c00',
                backgroundColor: 'rgba(255, 140, 0, 0.1)',
                tension: 0.4
            }, {
                label: 'Services Completed',
                data: trend?.completed || [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function updateDistributionChart(distribution) {
    const ctx = document.getElementById('serviceDistributionChart').getContext('2d');

    if (distributionChart) {
        distributionChart.destroy();
    }

    const labels = distribution?.labels || [];
    const data = distribution?.data || [];
    const colors = ['#ff8c00', '#17a2b8', '#28a745', '#6c757d', '#ffd700', '#dc3545', '#6610f2'];

    distributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.length ? labels : ['No data'],
            datasets: [{
                data: data.length ? data : [1],
                backgroundColor: data.length ? colors.slice(0, labels.length) : ['#e9ecef']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

function loadWorkerPerformance() {
    fetch('php/get-analytics.php?period=month')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayWorkerPerformance(data.analytics.workers);
            }
        })
        .catch(error => console.error('Error:', error));
}

let workerPerformancePager = null;

function displayWorkerPerformance(workers) {
    const tbody = document.getElementById('workerPerformanceBody');

    if (!workers || workers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No data available</td></tr>';
        $('#workerPerformancePagination').empty();
        return;
    }

    let html = '';
    workers.forEach(worker => {
        const avgRating = parseFloat(worker.avg_rating) || 0;
        const performanceScore = calculatePerformanceScore(worker);
        const scoreColor = performanceScore >= 90 ? 'success' :
                          performanceScore >= 70 ? 'warning' : 'danger';

        html += `
            <tr>
                <td>${worker.fullname}</td>
                <td>${worker.completed_services || 0}</td>
                <td>
                    <div class="rating-stars">
                        ${generateStars(avgRating)}
                    </div>
                    <small class="text-muted">${avgRating.toFixed(1)}/5</small>
                </td>
                <td>${worker.total_ratings || 0}</td>
                <td>
                    <span class="badge badge-${scoreColor}">${performanceScore}%</span>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    if (!workerPerformancePager) {
        workerPerformancePager = createPagination({
            getItems: () => $('#workerPerformanceBody tr'),
            paginationContainer: '#workerPerformancePagination',
            rowsPerPage: 10
        });
    }
    workerPerformancePager.refresh();
}

function calculatePerformanceScore(worker) {
    const completed = worker.completed_services || 0;
    const rating = worker.avg_rating || 0;
    const totalRatings = worker.total_ratings || 0;
    
    // Simple scoring algorithm
    let score = 0;
    score += Math.min(completed * 2, 40); // Up to 40% for completed services
    score += rating * 10; // Up to 50% for rating (5*10=50)
    score += Math.min(totalRatings * 2, 10); // Up to 10% for number of ratings
    
    return Math.min(Math.round(score), 100);
}

function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        const starClass = i <= rating ? 'fas fa-star' : 'far fa-star';
        stars += `<i class="${starClass} text-warning"></i> `;
    }
    return stars;
}
</script>

<?php include('includes/footer.php'); ?>