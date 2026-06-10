let map;
let markers = {};
let workers = [];
let selectedWorkerId = null;
let updateInterval;

// Initialize map
function initMap() {
    map = L.map('map').setView([-26.2041, 28.0473], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// Load active workers
function loadActiveWorkers() {
    fetch('php/get-active-workers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                workers = data.workers;
                updateOnlineCount(data.online_count);
                updateLastUpdateTime();
                displayWorkersList();
                updateMapMarkers();
            } else {
                console.error('Error loading workers:', data.message);
                showError('Failed to load worker data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error loading worker data');
        });
}

// Update online count display
function updateOnlineCount(onlineCount) {
    const onlineCountElement = document.getElementById('onlineCount');
    if (onlineCountElement) {
        onlineCountElement.textContent = `${onlineCount} online`;
    }
}

// Update last update time
function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (lastUpdateElement) {
        lastUpdateElement.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
    }
}

// Display workers list
function displayWorkersList() {
    const container = document.getElementById('workersList');
    
    if (workers.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                <p class="text-muted">No active workers</p>
                <small class="text-muted">Workers will appear here when they come online</small>
            </div>
        `;
        return;
    }
    
    let html = '';
    workers.forEach(worker => {
        const lastSeen = worker.last_location ? 
            `Last update: ${formatTime(worker.last_location.timestamp)}` : 
            'No location data';
        
        const statusClass = worker.is_online ? 'worker-online' : 'worker-offline';
        const statusText = worker.is_online ? 'Online' : 'Offline';
        const isSelected = selectedWorkerId === worker.id ? 'selected-worker' : '';
        const batteryLevel = worker.last_location?.battery_level ? 
            `<small class="text-muted d-block">Battery: ${worker.last_location.battery_level}%</small>` : '';
        
        html += `
            <div class="worker-item p-3 border-bottom ${isSelected}" 
                 onclick="focusOnWorker(${worker.id})" 
                 style="cursor: pointer; transition: all 0.3s ease;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${worker.fullname}</h6>
                        <small class="text-muted d-block">${worker.role}</small>
                        <small class="text-muted">${lastSeen}</small>
                        ${batteryLevel}
                    </div>
                    <div class="text-right">
                        <span class="badge badge-${worker.is_online ? 'success' : 'secondary'}">
                            ${statusText}
                        </span>
                        <br>
                        <small class="text-muted">${worker.assigned_services || 0} assigned</small>
                    </div>
                </div>
                ${worker.current_service ? `
                <div class="mt-2 p-2 bg-light rounded">
                    <small class="text-muted">Current Service:</small><br>
                    <strong>${worker.current_service.service_type}</strong>
                </div>
                ` : ''}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Update map markers
function updateMapMarkers() {
    // Clear existing markers
    Object.values(markers).forEach(marker => {
        map.removeLayer(marker);
    });
    markers = {};
    
    let hasValidLocations = false;
    let bounds = [];
    
    // Add new markers
    workers.forEach(worker => {
        if (worker.last_location) {
            const latLng = [worker.last_location.latitude, worker.last_location.longitude];
            bounds.push(latLng);
            
            const marker = L.marker(latLng).addTo(map);
            
            // Custom icon based on online status
            const icon = L.divIcon({
                className: `worker-marker ${worker.is_online ? 'online' : 'offline'} ${selectedWorkerId === worker.id ? 'highlighted' : ''}`,
                html: `
                    <div class="marker-content">
                        <i class="fas fa-${worker.is_online ? 'user' : 'user-clock'}"></i>
                    </div>
                `,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
            
            marker.setIcon(icon);
            
            const popupContent = `
                <div class="worker-popup">
                    <h6>${worker.fullname}</h6>
                    <p class="mb-1"><strong>Role:</strong> ${worker.role}</p>
                    <p class="mb-1"><strong>Status:</strong> ${worker.is_online ? 'Online' : 'Offline'}</p>
                    <p class="mb-1"><strong>Last Update:</strong> ${formatTime(worker.last_location.timestamp)}</p>
                    ${worker.last_location.battery_level ? `<p class="mb-1"><strong>Battery:</strong> ${worker.last_location.battery_level}%</p>` : ''}
                    ${worker.last_location.is_moving ? `<p class="mb-1"><strong>Status:</strong> Moving</p>` : ''}
                    ${worker.current_service ? `
                    <p class="mb-1"><strong>Current Service:</strong> ${worker.current_service.service_type}</p>
                    ` : ''}
                    <button class="btn btn-sm btn-primary mt-2" onclick="focusOnWorker(${worker.id})">
                        Focus on Worker
                    </button>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers[worker.id] = marker;
            hasValidLocations = true;
        }
    });
    
    // Adjust map bounds to show all markers
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [20, 20] });
    }
}

// Focus on specific worker
function focusOnWorker(workerId) {
    const marker = markers[workerId];
    const worker = workers.find(w => w.id === workerId);
    
    if (marker && worker) {
        // Update selected worker
        selectedWorkerId = workerId;
        displayWorkersList();
        
        // Center map on worker with zoom
        map.setView(marker.getLatLng(), 15);
        marker.openPopup();
        
        // Highlight marker
        highlightMarker(marker);
    }
}

// Highlight marker animation
function highlightMarker(marker) {
    const icon = marker.getIcon();
    icon.options.className += ' highlighted';
    marker.setIcon(icon);
    
    setTimeout(() => {
        const className = icon.options.className.replace(' highlighted', '');
        icon.options.className = className;
        marker.setIcon(icon);
    }, 3000);
}

// Format time for display
function formatTime(timestamp) {
    if (!timestamp) return 'Never';
    
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff/60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff/3600000) + 'h ago';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Show error message
function showError(message) {
    const container = document.getElementById('workersList');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            ${message}
        </div>
    `;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadActiveWorkers();
    
    // Update every 30 seconds for real-time feel
    updateInterval = setInterval(loadActiveWorkers, 30000);
    
    // Add auto-refresh indicator
    setInterval(() => {
        const lastUpdate = document.getElementById('lastUpdate');
        if (lastUpdate) {
            const text = lastUpdate.textContent;
            if (!text.includes('Updating...')) {
                lastUpdate.textContent = text.replace('Last updated:', 'Updating...');
            }
        }
    }, 25000);
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});