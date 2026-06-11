<?php
$page_title = "Live Tracking | ErrandsCall Portal";
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
                    <div class="card-header bg-gradient text-white">
                        <h5 class="mb-0">Live Worker Tracking</h5>
                        <small class="opacity-75">Real-time location tracking of field workers</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Workers List -->
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Active Workers</h6>
                                        <span class="badge badge-primary" id="onlineCount">0 online</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="workersList" style="max-height: 500px; overflow-y: auto;">
                                            <div class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="sr-only">Loading workers...</span>
                                                </div>
                                                <p class="mt-2 text-muted">Loading workers...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Map -->
                            <div class="col-md-8">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Live Map</h6>
                                        <small class="text-muted" id="lastUpdate">Updating...</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="map" style="height: 500px; border-radius: 0 0 8px 8px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-center">
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color online-marker"></span>
                                        <small class="text-muted">Online</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color offline-marker"></span>
                                        <small class="text-muted">Offline</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color selected-marker"></span>
                                        <small class="text-muted">Selected</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-shape worker-shape"><i class="fas fa-user"></i></span>
                                        <small class="text-muted">Worker</small>
                                    </div>
                                    <div class="legend-item mb-2">
                                        <span class="legend-shape manager-shape"><i class="fas fa-user-tie"></i></span>
                                        <small class="text-muted">Manager</small>
                                    </div>
                                </div>
                                <p class="text-center text-muted small mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Map refreshes automatically every 30 seconds. Workers and managers must enable location sharing for their position to appear here.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<!-- Load the external JavaScript -->
<script src="js/live-tracking.js"></script>

<style>
.worker-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.worker-item.selected-worker {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.worker-online {
    color: var(--success-color);
}

.worker-offline {
    color: var(--gray-medium);
}

.worker-popup {
    min-width: 200px;
}

/* Custom marker styles */
.worker-marker {
    background: white;
    border: 3px solid;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.worker-marker.online {
    border-color: #28a745;
    color: #28a745;
}

.worker-marker.offline {
    border-color: #6c757d;
    color: #6c757d;
}

.worker-marker.highlighted {
    animation: pulse 1s infinite;
    border-color: #2196f3;
    color: #2196f3;
}

/* Manager markers are square so they're distinguishable from worker (circle) markers */
.worker-marker.role-manager {
    border-radius: 6px;
}

.marker-content {
    font-size: 12px;
}

/* Legend styles */
.legend-item {
    display: flex;
    align-items: center;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 8px;
    border: 2px solid;
}

.online-marker {
    background: white;
    border-color: #28a745;
}

.offline-marker {
    background: white;
    border-color: #6c757d;
}

.selected-marker {
    background: white;
    border-color: #2196f3;
    animation: pulse 2s infinite;
}

/* Legend marker shapes (mirrors map marker icons) */
.legend-shape {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    border: 2px solid var(--gray-medium);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: var(--gray-medium);
    background: white;
}

.legend-shape.worker-shape {
    border-radius: 50%;
}

.legend-shape.manager-shape {
    border-radius: 4px;
}

/* Role badges in the workers list */
.role-badge-worker,
.role-badge-manager {
    background: var(--primary-gradient-muted);
    color: #fff;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<?php include('includes/footer.php'); ?>