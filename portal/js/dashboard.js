$(document).ready(function() {
    // Load dashboard data
    loadRecentActivity();
    loadRecentVehicles();
    loadRecentServices();

    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadRecentActivity();
        loadRecentServices();
    }, 30000);
});

function loadRecentActivity() {
    $.ajax({
        url: 'php/get-recent-activity.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.activities) {
                let html = '';
                if (response.activities.length > 0) {
                    response.activities.forEach(activity => {
                        // Ensure we have all required fields with fallbacks
                        const action = activity.action || 'Activity';
                        const description = activity.description || 'No description available';
                        const userName = activity.user_name || 'System';
                        const createdAt = activity.created_at || new Date().toISOString();
                        
                        html += `
                            <div class="update-item mb-3 p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong class="text-primary">${action}</strong>
										<p></p>
                                        <small class="text-muted">
                                            <i class="fas fa-pen mr-1"></i>${description}
                                        </small>
										<p></p>
                                        <small class="text-muted">
                                            <i class="fas fa-user mr-1"></i>${userName}
                                        </small>
                                    </div>
                                    <small class="text-muted text-nowrap ml-2">
                                        <i class="fas fa-clock mr-1"></i>${formatTime(createdAt)}
                                    </small>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = `
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No recent activity</p>
                            <small class="text-muted">Activity will appear here as you use the system</small>
                        </div>
                    `;
                }
                $('#recentActivity').html(html);
            } else {
                $('#recentActivity').html(`
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                        <p class="text-muted">No activity data available</p>
                        ${response.message ? `<small class="text-muted">${response.message}</small>` : ''}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading activity:', error);
            console.error('Response:', xhr.responseText);
            $('#recentActivity').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Error loading activity data
                    <br><small>Check console for details</small>
                </div>
            `);
        }
    });
}

function loadRecentVehicles() {
    $.ajax({
        url: 'php/get-vehicles.php?recent=5',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.vehicles) {
                let html = '';
                if (response.vehicles.length > 0) {
                    response.vehicles.forEach(vehicle => {
                        const discExpiry = new Date(vehicle.disc_expiry);
                        const today = new Date();
                        const daysUntilExpiry = Math.ceil((discExpiry - today) / (1000 * 60 * 60 * 24));
                        let expiryBadge = '';

                        if (daysUntilExpiry < 0) {
                            expiryBadge = '<span class="badge badge-dark ml-2">Expired</span>';
                        } else if (daysUntilExpiry <= 7) {
                            expiryBadge = '<span class="badge badge-danger ml-2">Expiring Soon</span>';
                        } else if (daysUntilExpiry <= 30) {
                            expiryBadge = '<span class="badge badge-warning ml-2">Expiring</span>';
                        }
                        
                        html += `
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded bg-light">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <strong>${vehicle.make || 'Unknown'} ${vehicle.model || ''}</strong>
                                        ${expiryBadge}
                                    </div>
                                    <div class="text-muted small">
                                        <div>Plate: ${vehicle.license_plate || 'N/A'}</div>
                                        <div>Year: ${vehicle.year || 'N/A'} | Color: ${vehicle.color || 'N/A'}</div>
                                        <div>Disc Expiry: ${formatDate(vehicle.disc_expiry)}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = '<div class="text-center py-4"><i class="fas fa-car fa-2x text-muted mb-2"></i><p class="text-muted">No vehicles found</p></div>';
                }
                $('#recentVehicles').html(html);
            } else {
                $('#recentVehicles').html('<div class="text-center py-4"><p class="text-muted">No vehicle data available</p></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading vehicles:', error);
            $('#recentVehicles').html('<div class="alert alert-danger">Error loading vehicle data</div>');
        }
    });
}

function loadRecentServices() {
    $.ajax({
        url: 'php/get-services.php?recent=5',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.services) {
                let html = '';
                if (response.services.length > 0) {
                    response.services.forEach(service => {
                        const statusClass = getStatusClass(service.status);
                        const ownerLine = service.owner_name
                            ? `<small class="text-muted d-block"><i class="fas fa-user mr-1"></i>${service.owner_name}</small>`
                            : '';
                        html += `
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded bg-light">
                                <div class="flex-grow-1">
                                    <strong class="text-muted d-block">${service.service_type || 'Unknown Service'}</strong>
                                    <small class="text-muted d-block">${service.vehicle_info || 'No vehicle info'}</small>
                                    ${ownerLine}
                                    <small class="text-muted">Requested: ${formatTime(service.created_at)}</small>
                                </div>
                                <span class="badge ${statusClass} ml-2">${service.status || 'pending'}</span>
                            </div>
                        `;
                    });
                } else {
                    html = '<div class="text-center py-4"><i class="fas fa-tasks fa-2x text-muted mb-2"></i><p class="text-muted">No services found</p></div>';
                }
                $('#recentServices').html(html);
            } else {
                $('#recentServices').html('<div class="text-center py-4"><p class="text-muted">No service data available</p></div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading services:', error);
            $('#recentServices').html('<div class="alert alert-danger">Error loading service data</div>');
        }
    });
}

// Utility functions
function formatTime(dateString) {
    if (!dateString) return 'Unknown time';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid date';
    
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString();
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return isNaN(date.getTime()) ? 'Invalid date' : date.toLocaleDateString();
}

function getStatusClass(status) {
    const statusMap = {
        'pending': 'badge-warning',
        'assigned': 'badge-info',
        'in_progress': 'badge-primary',
        'completed': 'badge-success',
        'cancelled': 'badge-danger'
    };
    return statusMap[status] || 'badge-secondary';
}