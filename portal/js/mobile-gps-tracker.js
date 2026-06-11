class GPSTracker {
    constructor() {
        this.trackingInterval = null;
        this.isTracking = false;
        this.lastLocation = null;
        this.updateInterval = 30000; // 30 seconds
        this.minDistance = 10; // meters
        this.userId = null;
        this.userRole = null;
    }

    async initialize(userId, userRole) {
        this.userId = userId;
        this.userRole = userRole;
        
        // Check if GPS tracking is allowed for this role
        if (!this.canTrackLocation()) {
            return false;
        }

        // Request permissions
        if (!await this.requestPermissions()) {
            return false;
        }

        return true;
    }

    canTrackLocation() {
        // Only workers and managers can be tracked
        return ['worker', 'manager'].includes(this.userRole);
    }

    async requestPermissions() {
        if (!navigator.geolocation) {
            console.error('Geolocation is not supported by this browser.');
            return false;
        }

        try {
            const permission = await navigator.permissions.query({ name: 'geolocation' });
            return permission.state !== 'denied';
        } catch (error) {
            // Fallback for browsers that don't support permissions API
            return new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(
                    () => resolve(true),
                    () => resolve(false),
                    { timeout: 5000 }
                );
            });
        }
    }

    startTracking() {
        if (this.isTracking) return;

        this.isTracking = true;
        
        // Get initial position
        this.getCurrentPosition();
        
        // Start continuous tracking
        this.trackingInterval = setInterval(() => {
            this.getCurrentPosition();
        }, this.updateInterval);

        // Also track on visibility change (when app comes to foreground)
        document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
    }

    stopTracking() {
        if (!this.isTracking) return;

        this.isTracking = false;
        clearInterval(this.trackingInterval);
        document.removeEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
        
        // Update online status to offline
        this.updateOnlineStatus(false);
    }

    handleVisibilityChange() {
        if (!document.hidden) {
            // App came to foreground, get immediate location
            this.getCurrentPosition();
        }
    }

    getCurrentPosition() {
        return new Promise((resolve) => {
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000 // Accept cached position up to 1 minute old
            };

            navigator.geolocation.getCurrentPosition(
                (position) => this.handleNewPosition(position),
                (error) => this.handlePositionError(error),
                options
            );
        });
    }

    handleNewPosition(position) {
        const newLocation = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            speed: position.coords.speed,
            heading: position.coords.heading,
            altitude: position.coords.altitude,
            timestamp: position.timestamp
        };

        // Check if location has changed significantly
        if (this.shouldUpdateLocation(newLocation)) {
            this.lastLocation = newLocation;
            this.sendLocationToServer(newLocation);
        }
    }

    shouldUpdateLocation(newLocation) {
        if (!this.lastLocation) return true;

        // Calculate distance between last and new location
        const distance = this.calculateDistance(
            this.lastLocation.latitude,
            this.lastLocation.longitude,
            newLocation.latitude,
            newLocation.longitude
        );

        return distance >= this.minDistance;
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
                
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    toRad(degrees) {
        return degrees * (Math.PI/180);
    }

    async sendLocationToServer(location) {
        try {
            const formData = new FormData();
            formData.append('latitude', location.latitude);
            formData.append('longitude', location.longitude);
            formData.append('accuracy', location.accuracy);
            formData.append('speed', location.speed || 0);
            formData.append('heading', location.heading || 0);
            formData.append('altitude', location.altitude || 0);
            formData.append('battery_level', await this.getBatteryLevel());
            formData.append('is_moving', location.speed > 1); // Consider moving if speed > 1 m/s
            formData.append('app_version', '1.0.0');
            formData.append('device_type', this.getDeviceType());

            const response = await fetch('php/gps-management.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateOnlineStatus(true);
            } else {
                console.error('Failed to update location:', data.message);
            }
        } catch (error) {
            console.error('Error sending location:', error);
        }
    }

    async getBatteryLevel() {
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                return Math.round(battery.level * 100);
            } catch (error) {
                // Battery API not supported
            }
        }
        return null;
    }

    getDeviceType() {
        const ua = navigator.userAgent;
        if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry/.test(ua)) {
            return 'Mobile';
        } else if (/Tablet|iPad/.test(ua)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    async updateOnlineStatus(isOnline) {
        try {
            const response = await fetch('php/update-online-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    is_online: isOnline,
                    user_id: this.userId
                })
            });
        } catch (error) {
            console.error('Error updating online status:', error);
        }
    }

    // Watch position for real-time updates (more frequent but battery intensive)
    startRealTimeTracking() {
        if ('watchPosition' in navigator.geolocation) {
            this.watchId = navigator.geolocation.watchPosition(
                (position) => this.handleNewPosition(position),
                (error) => this.handlePositionError(error),
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 5000 // Very fresh data
                }
            );
        }
    }

    stopRealTimeTracking() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
        }
    }

    handlePositionError(error) {
        console.error('GPS Error:', error.message);
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                console.error('User denied location access');
                break;
            case error.POSITION_UNAVAILABLE:
                console.error('Location information unavailable');
                break;
            case error.TIMEOUT:
                console.error('Location request timed out');
                break;
        }
    }
}

// Initialize GPS tracker when page loads
document.addEventListener('DOMContentLoaded', async function() {
    const gpsTracker = new GPSTracker();
    
    // Get user info from PHP session (you'll need to set these variables)
    const userId = <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
    const userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    
    if (userId && await gpsTracker.initialize(userId, userRole)) {
        gpsTracker.startTracking();
        
        // Make tracker globally available for manual control
        window.gpsTracker = gpsTracker;
    }
});