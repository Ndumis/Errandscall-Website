class WorkerLocationTracker {
    constructor() {
        this.trackingInterval = null;
        this.isTracking = false;
        this.updateFrequency = 3600000; // 1 hour in milliseconds
        this.watchId = null;
    }

    // Initialize location tracking
    init() {
        this.checkLocationPermission();
        this.setupEventListeners();
    }

    // Check if location permission is granted
    async checkLocationPermission() {
        if (!navigator.geolocation) {
            this.showMessage('Geolocation is not supported by this browser.', 'error');
            return false;
        }

        try {
            const permission = await navigator.permissions.query({ name: 'geolocation' });
            this.handlePermissionState(permission.state);
            
            permission.onchange = () => {
                this.handlePermissionState(permission.state);
            };
        } catch (error) {
            console.log('Permission API not supported, requesting location directly');
            this.requestLocation();
        }
    }

    handlePermissionState(state) {
        switch (state) {
            case 'granted':
                this.startTracking();
                break;
            case 'prompt':
                this.showLocationPrompt();
                break;
            case 'denied':
                this.showMessage('Location access is denied. Please enable location permissions in your browser settings.', 'warning');
                break;
        }
    }

    // Show location permission prompt
    showLocationPrompt() {
        const modal = document.createElement('div');
        modal.className = 'location-permission-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Location Sharing Required</h5>
                </div>
                <div class="modal-body">
                    <p>To provide better service and enable live tracking, we need to access your location.</p>
                    <p>Your location will be updated every hour while you're online.</p>
                    <div class="permission-options">
                        <button class="btn btn-primary" id="allowLocation">Allow Location Access</button>
                        <button class="btn btn-secondary" id="denyLocation">Not Now</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        document.getElementById('allowLocation').addEventListener('click', () => {
            this.requestLocation();
            document.body.removeChild(modal);
        });
        
        document.getElementById('denyLocation').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    // Request location access
    requestLocation() {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.updateLocation(position);
                this.startTracking();
            },
            (error) => {
                this.handleLocationError(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    // Start continuous tracking
    startTracking() {
        if (this.isTracking) return;
        
        this.isTracking = true;
        
        // Update immediately
        this.getCurrentLocation();
        
        // Set up periodic updates
        this.trackingInterval = setInterval(() => {
            this.getCurrentLocation();
        }, this.updateFrequency);
        
        // Watch for significant position changes
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                // Only update if user is moving
                if (position.coords.speed > 1) {
                    this.updateLocation(position, true);
                }
            },
            (error) => {
                console.error('Location watch error:', error);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 300000, // 5 minutes
                timeout: 15000
            }
        );
        
        this.showMessage('Location tracking started', 'success');
    }

    // Get current location
    getCurrentLocation() {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.updateLocation(position);
            },
            (error) => {
                this.handleLocationError(error);
            },
            {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            }
        );
    }

    // Update location on server
    async updateLocation(position, isMoving = false) {
        const locationData = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            battery_level: await this.getBatteryLevel(),
            is_moving: isMoving || position.coords.speed > 1
        };
        
        try {
            const response = await fetch('php/update-worker-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(locationData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('Location updated:', new Date().toLocaleTimeString());
            } else {
                console.error('Failed to update location:', result.message);
            }
        } catch (error) {
            console.error('Error updating location:', error);
        }
    }

    // Get battery level if available
    async getBatteryLevel() {
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                return Math.round(battery.level * 100);
            } catch (error) {
                console.log('Battery API not available');
            }
        }
        return null;
    }

    // Handle location errors
    handleLocationError(error) {
        let message = '';
        switch (error.code) {
            case error.PERMISSION_DENIED:
                message = 'Location access denied. Please enable location permissions.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Location information unavailable.';
                break;
            case error.TIMEOUT:
                message = 'Location request timed out.';
                break;
            default:
                message = 'An unknown error occurred.';
                break;
        }
        this.showMessage(message, 'error');
    }

    // Stop tracking
    stopTracking() {
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }
        
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        
        this.isTracking = false;
        this.showMessage('Location tracking stopped', 'info');
    }

    // Show status message
    showMessage(message, type = 'info') {
        // Create or update status message in UI
        let messageElement = document.getElementById('locationStatus');
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.id = 'locationStatus';
            messageElement.className = `alert alert-${type} location-status`;
            document.body.appendChild(messageElement);
        }
        
        messageElement.textContent = message;
        messageElement.className = `alert alert-${type} location-status`;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
        }, 5000);
    }

    // Setup event listeners
    setupEventListeners() {
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, reduce tracking frequency
                if (this.trackingInterval) {
                    clearInterval(this.trackingInterval);
                    this.trackingInterval = setInterval(() => {
                        this.getCurrentLocation();
                    }, this.updateFrequency * 2); // Reduce frequency when not visible
                }
            } else {
                // Page is visible, resume normal tracking
                if (this.trackingInterval) {
                    clearInterval(this.trackingInterval);
                    this.trackingInterval = setInterval(() => {
                        this.getCurrentLocation();
                    }, this.updateFrequency);
                }
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize for workers
    if (document.body.classList.contains('worker-role')) {
        const locationTracker = new WorkerLocationTracker();
        locationTracker.init();
        
        // Make it globally available
        window.locationTracker = locationTracker;
    }
});