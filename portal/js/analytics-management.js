class AnalyticsManager {
    constructor() {
        this.charts = {};
        this.currentFilters = {};
        this.isInitialized = false;
        this.init();
    }

    init() {
        // Check if we're on the correct page before initializing
        if (!this.isAnalyticsPage()) {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.safeInitialize();
            });
        } else {
            this.safeInitialize();
        }
    }
	
	 isAnalyticsPage() {
        // Check if we're on the analytics page by looking for specific elements
        return document.getElementById('reportFilters') !== null;
    }
	
	safeInitialize() {
        try {
            this.bindEvents();
            this.loadOverviewReport();
            this.isInitialized = true;
        } catch (error) {
            console.error('Analytics manager initialization failed:', error);
        }
    }

     bindEvents() {
        try {
            const reportType = document.getElementById('reportType');
            const dateRange = document.getElementById('dateRange');
            const reportFilters = document.getElementById('reportFilters');
            const exportPdf = document.getElementById('exportPdf');
            const exportExcel = document.getElementById('exportExcel');
            const refreshReport = document.getElementById('refreshReport');
            const saveReport = document.getElementById('saveReport');

            if (reportType) {
                reportType.addEventListener('change', () => this.loadReport());
            }
            
            if (dateRange) {
                dateRange.addEventListener('change', () => {
                    const customDates = document.querySelectorAll('.custom-date');
                    customDates.forEach(el => {
                        if (el) {
                            el.style.display = dateRange.value === 'custom' ? 'block' : 'none';
                        }
                    });
                });
            }

            if (reportFilters) {
                reportFilters.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.loadReport();
                });
            }

            if (exportPdf) exportPdf.addEventListener('click', () => this.exportReport('pdf'));
            if (exportExcel) exportExcel.addEventListener('click', () => this.exportReport('excel'));
            if (refreshReport) refreshReport.addEventListener('click', () => this.loadReport());
            if (saveReport) saveReport.addEventListener('click', () => this.saveReport());

        } catch (error) {
            console.error('Error binding events:', error);
        }
    }

    updateServicesChart(chartData) {
        try {
            const ctx = document.getElementById('servicesChart');
            if (!ctx) {
                console.warn('Services chart element not found');
                return;
            }
            
            // Destroy existing chart if it exists
            if (this.charts.services) {
                this.charts.services.destroy();
            }

            // Check if we have data to display
            if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                ctx.innerHTML = '<div class="text-center p-4"><p>No data available for the selected period</p></div>';
                return;
            }

            this.charts.services = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Services Created',
                        data: chartData.data,
                        borderColor: '#ff8c00',
                        backgroundColor: 'rgba(255, 140, 0, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error updating services chart:', error);
            const ctx = document.getElementById('servicesChart');
            if (ctx) {
                ctx.innerHTML = '<div class="text-center p-4 text-danger"><p>Error loading chart</p></div>';
            }
        }
    }

    async loadReport() {
        const filters = this.getFilters();
        this.currentFilters = filters;
        
        try {
            this.showLoading();
            
            const response = await this.ajaxRequest('php/analytics-management.php', 'GET', filters);
            
            if (response.success) {
                this.updateCharts(response.data);
                this.updateStats(response.data.stats);
                this.renderReportTable(response.data);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Failed to load report: ' + error.message);
        }
    }

    async loadOverviewReport() {
        await this.loadReport();
    }

    getFilters() {
        const form = document.getElementById('reportFilters');
        if (!form) return {};
        
        const formData = new FormData(form);
        const filters = {};
        
        for (let [key, value] of formData.entries()) {
            filters[key] = value;
        }

        // Set default dates if custom range not selected
        if (filters.date_range !== 'custom') {
            const dates = this.getDateRange(filters.date_range);
            filters.start_date = dates.start;
            filters.end_date = dates.end;
        }

        return filters;
    }

    getDateRange(range) {
        const today = new Date();
        let start = new Date();
        let end = new Date();

        switch (range) {
            case 'today':
                start.setHours(0, 0, 0, 0);
                end.setHours(23, 59, 59, 999);
                break;
            case 'yesterday':
                start.setDate(today.getDate() - 1);
                start.setHours(0, 0, 0, 0);
                end.setDate(today.getDate() - 1);
                end.setHours(23, 59, 59, 999);
                break;
            case 'this_week':
                start.setDate(today.getDate() - today.getDay());
                start.setHours(0, 0, 0, 0);
                end.setHours(23, 59, 59, 999);
                break;
            case 'last_week':
                start.setDate(today.getDate() - today.getDay() - 7);
                start.setHours(0, 0, 0, 0);
                end.setDate(today.getDate() - today.getDay() - 1);
                end.setHours(23, 59, 59, 999);
                break;
            case 'this_month':
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                end.setHours(23, 59, 59, 999);
                break;
            case 'last_month':
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
                end.setHours(23, 59, 59, 999);
                break;
        }

        return {
            start: start.toISOString().split('T')[0],
            end: end.toISOString().split('T')[0]
        };
    }

    updateServicesChart(chartData) {
        try {
            const ctx = document.getElementById('servicesChart');
            if (!ctx) {
                console.warn('Services chart element not found');
                return;
            }
            
            // Destroy existing chart if it exists
            if (this.charts.services) {
                this.charts.services.destroy();
            }

            // Check if we have data to display
            if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                ctx.innerHTML = '<div class="text-center p-4"><p>No data available for the selected period</p></div>';
                return;
            }

            this.charts.services = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Services Created',
                        data: chartData.data,
                        borderColor: '#ff8c00',
                        backgroundColor: 'rgba(255, 140, 0, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error updating services chart:', error);
            const ctx = document.getElementById('servicesChart');
            if (ctx) {
                ctx.innerHTML = '<div class="text-center p-4 text-danger"><p>Error loading chart</p></div>';
            }
        }
    }

    updateServiceTypesChart(chartData) {
        try {
            const ctx = document.getElementById('serviceTypesChart');
            if (!ctx) {
                console.warn('Service types chart element not found');
                return;
            }
            
            if (this.charts.serviceTypes) {
                this.charts.serviceTypes.destroy();
            }

            // Check if we have data to display
            if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                ctx.innerHTML = '<div class="text-center p-4"><p>No data available for the selected period</p></div>';
                return;
            }

            this.charts.serviceTypes = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: [
                            '#ff8c00', '#ff6b00', '#ffd700', '#2c3e50', '#28a745', '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error updating service types chart:', error);
            const ctx = document.getElementById('serviceTypesChart');
            if (ctx) {
                ctx.innerHTML = '<div class="text-center p-4 text-danger"><p>Error loading chart</p></div>';
            }
        }
    }

    updateServiceTypesChart(chartData) {
        const ctx = document.getElementById('serviceTypesChart');
        if (!ctx) return;
        
        if (this.charts.serviceTypes) {
            this.charts.serviceTypes.destroy();
        }

        this.charts.serviceTypes = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    data: chartData.data || [],
                    backgroundColor: [
                        '#ff8c00', '#ff6b00', '#ffd700', '#2c3e50', '#28a745', '#dc3545'
                    ]
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

    updateStats(stats) {
        if (!stats) return;
        
        const updateElement = (id, value) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        };

        updateElement('totalServices', stats.total_services || 0);
        updateElement('completedServices', stats.completed_services || 0);
        updateElement('revenue', 'R' + (stats.revenue || 0));
        updateElement('avgCompletion', (stats.avg_completion || 0) + 'h');
    }

    renderReportTable(data) {
        const reportType = this.currentFilters.report_type;
        const reportContent = document.getElementById('reportContent');
        if (!reportContent) return;

        let html = '';

        switch (reportType) {
            case 'services':
                html = this.renderServicesTable(data.detailed);
                break;
            case 'vehicles':
                html = this.renderVehiclesTable(data.detailed);
                break;
            case 'users':
                html = this.renderUsersTable(data.detailed);
                break;
            case 'performance':
                html = this.renderPerformanceTable(data.detailed);
                break;
            default:
                html = this.renderOverviewTable(data.detailed);
        }

        reportContent.innerHTML = html;
    }

    renderServicesTable(data) {
        if (!data || !Array.isArray(data)) return '<p>No data available</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Completion Rate</th>
                            <th>Avg Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${row.service_type || 'N/A'}</td>
                                <td><span class="status-badge status-${row.status || 'unknown'}">${row.status || 'Unknown'}</span></td>
                                <td>${row.count || 0}</td>
                                <td>${row.completion_rate || 0}%</td>
                                <td>${row.avg_time || 0}h</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderVehiclesTable(data) {
        if (!data || !Array.isArray(data)) return '<p>No data available</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Count</th>
                            <th>Avg Year</th>
                            <th>Expiring Soon</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${row.make || 'N/A'}</td>
                                <td>${row.model || 'N/A'}</td>
                                <td>${row.count || 0}</td>
                                <td>${row.avg_year || 'N/A'}</td>
                                <td>${row.expiring_soon || 0}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderOverviewTable(data) {
        if (!data || !Array.isArray(data)) return '<p>No data available</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Customer</th>
                            <th>Assigned Worker</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${row.service_type || 'N/A'}</td>
                                <td><span class="status-badge status-${row.status || 'unknown'}">${row.status || 'Unknown'}</span></td>
                                <td>${row.customer_name || 'N/A'}</td>
                                <td>${row.assigned_worker || 'Unassigned'}</td>
                                <td>${row.created_at ? new Date(row.created_at).toLocaleDateString() : 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderUsersTable(data) {
        if (!data || !Array.isArray(data)) return '<p>No data available</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Total Users</th>
                            <th>Active Users</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${row.role || 'N/A'}</td>
                                <td>${row.total_users || 0}</td>
                                <td>${row.active_users || 0}</td>
                                <td>${row.registration_date || 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    renderPerformanceTable(data) {
        if (!data || !Array.isArray(data)) return '<p>No data available</p>';
        
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Total Assignments</th>
                            <th>Completed</th>
                            <th>Avg Completion Time</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${row.fullname || 'N/A'}</td>
                                <td>${row.role || 'N/A'}</td>
                                <td>${row.total_assignments || 0}</td>
                                <td>${row.completed_assignments || 0}</td>
                                <td>${row.avg_completion_time || 0}h</td>
                                <td>${row.completion_rate || 0}%</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    async ajaxRequest(url, method, data) {
        const formData = new URLSearchParams();
        for (const key in data) {
            formData.append(key, data[key]);
        }

        const response = await fetch(url, {
            method: method,
            body: method === 'GET' ? null : formData,
            headers: method === 'POST' ? {
                'Content-Type': 'application/x-www-form-urlencoded',
            } : {}
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    async exportReport(format) {
        try {
            const response = await this.ajaxRequest('php/analytics-management.php', 'POST', {
                action: 'export',
                format: format,
                ...this.currentFilters
            });

            if (response.success) {
                // Create download link
                const link = document.createElement('a');
                link.href = response.file_url;
                link.download = response.file_name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Export failed: ' + error.message);
        }
    }

    async saveReport() {
        const name = prompt('Enter report name:');
        if (!name) return;

        try {
            const response = await this.ajaxRequest('php/analytics-management.php', 'POST', {
                action: 'save',
                name: name,
                config: JSON.stringify(this.currentFilters)
            });

            if (response.success) {
                this.showSuccess('Report saved successfully');
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('Save failed: ' + error.message);
        }
    }

    showLoading() {
        const reportContent = document.getElementById('reportContent');
        if (reportContent) {
            reportContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading report...</span>
                    </div>
                    <p class="mt-2">Generating report...</p>
                </div>
            `;
        }
    }

    showError(message) {
        const reportContent = document.getElementById('reportContent');
        if (reportContent) {
            reportContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${message}
                </div>
            `;
        }
    }

    showSuccess(message) {
        const reportContent = document.getElementById('reportContent');
        if (reportContent) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            reportContent.insertBefore(alert, reportContent.firstChild);
        }
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    new AnalyticsManager();
});