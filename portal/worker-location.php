<?php
$page_title = "Share Location | ErrandsCall Worker";
include('config/database.php');
include('includes/auth-check.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'worker') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .status-online { color: #28a745; }
        .status-offline { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Share My Location</h4>
                    </div>
                    <div class="card-body text-center">
                        <div id="status" class="mb-3">
                            <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                            <h5 id="statusText">Requesting location access...</h5>
                            <p id="locationInfo" class="text-muted"></p>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                Your location will be shared with managers for service coordination.
                                Location updates automatically every 30 seconds.
                            </small>
                        </div>
                        
                        <button id="startSharing" class="btn btn-success btn-lg">
                            <i class="fas fa-play mr-2"></i>Start Sharing Location
                        </button>
                        
                        <button id="stopSharing" class="btn btn-danger btn-lg mt-2" style="display: none;">
                            <i class="fas fa-stop mr-2"></i>Stop Sharing
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let watchId = null;
        const startBtn = document.getElementById('startSharing');
        const stopBtn = document.getElementById('stopSharing');
        const statusText = document.getElementById('statusText');
        const locationInfo = document.getElementById('locationInfo');

        function startLocationSharing() {
            if (!navigator.geolocation) {
                statusText.textContent = 'Geolocation is not supported by this browser.';
                return;
            }

            statusText.textContent = 'Sharing location...';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';

            // Get location updates every 30 seconds
            watchId = navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Update display
                    locationInfo.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    statusText.innerHTML = '<span class="status-online">Location Sharing Active</span>';
                    
                    // Send to server
                    updateLocationOnServer(lat, lng);
                },
                function(error) {
                    console.error('Error getting location:', error);
                    statusText.innerHTML = '<span class="status-offline">Location Error</span>';
                    locationInfo.textContent = 'Unable to get your location. Please check permissions.';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }

        function stopLocationSharing() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            
            statusText.textContent = 'Location Sharing Stopped';
            locationInfo.textContent = '';
            startBtn.style.display = 'block';
            stopBtn.style.display = 'none';
            
            // Notify server that worker is offline
            setWorkerOffline();
        }

        function updateLocationOnServer(lat, lng) {
            fetch('php/update-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to update location on server');
                }
            })
            .catch(error => {
                console.error('Error updating location:', error);
            });
        }

        function setWorkerOffline() {
            // You can implement this to set worker as offline on server
            console.log('Worker stopped sharing location');
        }

        // Event listeners
        startBtn.addEventListener('click', startLocationSharing);
        stopBtn.addEventListener('click', stopLocationSharing);

        // Auto-start if user grants permission
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function() {
                    // Permission granted, auto-start
                    startLocationSharing();
                },
                function() {
                    // Permission denied, wait for manual start
                    statusText.textContent = 'Click "Start Sharing" to begin location sharing';
                }
            );
        }
    </script>
</body>
</html>