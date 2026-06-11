// Live location sharing for workers and managers.
// Only the most recent position is sent to the server
// (php/gps-management.php upserts a single row per user) so the
// database stays small.
(function() {
    if (typeof currentUserRole === 'undefined' || !['worker', 'manager'].includes(currentUserRole)) {
        return;
    }

    const SEND_DISTANCE_THRESHOLD = 30;   // meters - send update once moved at least this far
    const SEND_HEARTBEAT_INTERVAL = 60000; // ms - send update at least this often even when stationary
    const MOVING_SPEED_THRESHOLD = 0.5;    // m/s (~1.8 km/h)

    let watchId = null;
    let lastSent = null; // { lat, lng, time }
    let lastRaw = null;  // { lat, lng, time }

    function toRad(deg) {
        return deg * Math.PI / 180;
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // earth radius in meters
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2 +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function getBatteryLevel() {
        if (navigator.getBattery) {
            return navigator.getBattery().then(battery => Math.round(battery.level * 100)).catch(() => null);
        }
        return Promise.resolve(null);
    }

    function setStatus(state) {
        const indicator = document.getElementById('locationStatusIndicator');
        if (!indicator) return;

        const states = {
            active: { text: 'Location: On', cls: 'badge-success', icon: 'fa-location-arrow' },
            error: { text: 'Location: Error', cls: 'badge-warning', icon: 'fa-exclamation-triangle' },
            denied: { text: 'Location: Off', cls: 'badge-danger', icon: 'fa-map-marker-alt' },
            unsupported: { text: 'Location: Unsupported', cls: 'badge-secondary', icon: 'fa-map-marker-alt' }
        };

        const s = states[state] || states.denied;
        indicator.className = `badge ${s.cls}`;
        indicator.innerHTML = `<i class="fas ${s.icon} mr-1"></i>${s.text}`;
        indicator.title = state === 'active'
            ? 'Your live location is being shared with admins/managers'
            : 'Live location sharing is off';
    }

    function handlePosition(position) {
        const { latitude, longitude, accuracy, altitude, heading, speed } = position.coords;
        const timestamp = new Date(position.timestamp).toISOString();

        setStatus('active');

        // Determine movement: prefer device-reported speed, fall back to distance/time
        let isMoving = speed != null && speed > MOVING_SPEED_THRESHOLD;
        if (!isMoving && lastRaw) {
            const distance = calculateDistance(lastRaw.lat, lastRaw.lng, latitude, longitude);
            const elapsedSec = (Date.now() - lastRaw.time) / 1000;
            if (elapsedSec > 0 && (distance / elapsedSec) > MOVING_SPEED_THRESHOLD) {
                isMoving = true;
            }
        }
        lastRaw = { lat: latitude, lng: longitude, time: Date.now() };

        // Throttle server updates so the small DB only ever holds the latest position
        let shouldSend = !lastSent;
        if (lastSent) {
            const distance = calculateDistance(lastSent.lat, lastSent.lng, latitude, longitude);
            const elapsed = Date.now() - lastSent.time;
            shouldSend = distance >= SEND_DISTANCE_THRESHOLD || elapsed >= SEND_HEARTBEAT_INTERVAL;
        }

        if (shouldSend) {
            getBatteryLevel().then(batteryLevel => {
                sendLocation({ latitude, longitude, accuracy, speed, heading, altitude, batteryLevel, isMoving });
            });
            lastSent = { lat: latitude, lng: longitude, time: Date.now() };
        }
    }

    function sendLocation(data) {
        const formData = new FormData();
        formData.append('latitude', data.latitude);
        formData.append('longitude', data.longitude);
        if (data.accuracy != null) formData.append('accuracy', data.accuracy);
        if (data.speed != null) formData.append('speed', data.speed);
        if (data.heading != null) formData.append('heading', data.heading);
        if (data.altitude != null) formData.append('altitude', data.altitude);
        if (data.batteryLevel != null) formData.append('battery_level', data.batteryLevel);
        formData.append('is_moving', data.isMoving ? '1' : '0');
        formData.append('device_type', /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop');

        fetch('php/gps-management.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                if (!result.success) {
                    console.warn('[ErrandsCall Location] Update rejected:', result.message);
                }
            })
            .catch(err => console.warn('[ErrandsCall Location] Network error:', err));
    }

    function handleError(error) {
        console.warn('[ErrandsCall Location] Error:', error.message);
        if (error.code === error.PERMISSION_DENIED) {
            // Permission was revoked mid-session - stop watching and force the prompt again
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            showPrompt(window.isSecureContext ? 'denied' : 'insecure');
        } else {
            setStatus('error');
        }
    }

    function startTracking() {
        if (watchId !== null) return;

        if (!navigator.geolocation) {
            setStatus('unsupported');
            return;
        }

        watchId = navigator.geolocation.watchPosition(handlePosition, handleError, {
            enableHighAccuracy: true,
            maximumAge: 10000,
            timeout: 20000
        });
    }

    // Mandatory prompt: workers and managers must enable location sharing to proceed.
    // The modal has a static backdrop and no dismiss/close control.
    // mode: 'default' (ask), 'denied' (blocked in browser settings), or 'insecure' (no https/localhost)
    function showPrompt(mode) {
        const modalEl = document.getElementById('locationPermissionModal');
        if (!modalEl || typeof $ === 'undefined') {
            setStatus('denied');
            return;
        }

        const sections = {
            default: document.getElementById('locationPromptDefault'),
            denied: document.getElementById('locationPromptDenied'),
            insecure: document.getElementById('locationPromptInsecure')
        };
        Object.keys(sections).forEach(function(key) {
            if (sections[key]) sections[key].classList.toggle('d-none', key !== mode);
        });

        setStatus('denied');

        if (!$(modalEl).hasClass('show')) {
            $(modalEl).modal('show');
        }

        const enableBtn = document.getElementById('locationEnableBtn');
        if (enableBtn && !enableBtn.dataset.bound) {
            enableBtn.dataset.bound = '1';
            enableBtn.addEventListener('click', function() {
                if (!window.isSecureContext) {
                    showPrompt('insecure');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        $(modalEl).modal('hide');
                        handlePosition(position);
                        startTracking();
                    },
                    function(error) {
                        console.warn('[ErrandsCall Location] Error:', error.message);
                        showPrompt(error.code === error.PERMISSION_DENIED ? 'denied' : 'default');
                    },
                    { enableHighAccuracy: true, maximumAge: 10000, timeout: 20000 }
                );
            });
        }
    }

    function init() {
        if (!navigator.geolocation) {
            setStatus('unsupported');
            return;
        }

        if (!window.isSecureContext) {
            // Geolocation can't be granted on this connection - still block access,
            // but explain that a secure (https/localhost) address is needed.
            showPrompt('insecure');
            return;
        }

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(result => {
                if (result.state === 'granted') {
                    startTracking();
                } else {
                    showPrompt(result.state === 'denied' ? 'denied' : 'default');
                }

                result.onchange = function() {
                    if (this.state === 'granted') {
                        const modalEl = document.getElementById('locationPermissionModal');
                        if (modalEl && typeof $ !== 'undefined') {
                            $(modalEl).modal('hide');
                        }
                        startTracking();
                    } else if (this.state === 'denied') {
                        showPrompt('denied');
                    }
                };
            }).catch(() => showPrompt('default'));
        } else {
            showPrompt('default');
        }
    }

    document.addEventListener('DOMContentLoaded', init);

    window.addEventListener('beforeunload', function() {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }
    });
})();
