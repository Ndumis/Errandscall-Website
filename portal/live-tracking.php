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
                                <div class="d-flex justify-content-center">
                                    <div class="legend-item mr-3">
                                        <span class="legend-color online-marker"></span>
                                        <small class="text-muted">Online Worker</small>
                                    </div>
                                    <div class="legend-item mr-3">
                                        <span class="legend-color offline-marker"></span>
                                        <small class="text-muted">Offline Worker</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color selected-marker"></span>
                                        <small class="text-muted">Selected Worker</small>
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

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<?php include('includes/footer.php'); ?>