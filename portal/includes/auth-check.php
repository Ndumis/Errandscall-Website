<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout configuration (30 minutes)
$session_timeout = 1800; // 30 minutes in seconds

// Check if user is logged in and session hasn't expired
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session expired - update worker status before logout
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'worker') {
        include('config/database.php');
        $conn = getDBConnection();
        updateWorkerOnlineStatus($conn, $_SESSION['user_id'], false);
        $conn->close();
    }
    
    session_unset();
    session_destroy();
    header('Location: index.php?error=session_expired');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Validate user still exists in database and update worker status
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User no longer exists or is inactive - update worker status
    if ($user_role === 'worker') {
        updateWorkerOnlineStatus($conn, $user_id, false);
    }
    
    session_unset();
    session_destroy();
    header('Location: index.php?error=user_invalid');
    exit;
}

// Update worker's last seen timestamp if they are online
if ($user_role === 'worker') {
    $update_stmt = $conn->prepare("UPDATE worker_online_status SET last_seen = NOW() WHERE worker_id = ? AND is_online = 1");
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$stmt->close();
$conn->close();

// Role-based access control functions
function isAdmin() {
    return $_SESSION['user_role'] === 'admin';
}

function isManager() {
    return $_SESSION['user_role'] === 'manager';
}

function isWorker() {
    return $_SESSION['user_role'] === 'worker';
}

function isCustomer() {
    return $_SESSION['user_role'] === 'customer';
}

function hasAccess($required_roles) {
    return in_array($_SESSION['user_role'], $required_roles);
}

// Log user activity
function logActivity($user_id, $activity_type, $description) {
    include('../config/database.php');
    $conn = getDBConnection();
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $activity_type, $description, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

/**
 * Update worker online status
 */
function updateWorkerOnlineStatus($conn, $worker_id, $is_online, $app_version = null, $device_type = null) {
    // Check if record exists
    $check_stmt = $conn->prepare("SELECT worker_id FROM worker_online_status WHERE worker_id = ?");
    $check_stmt->bind_param("i", $worker_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $check_stmt->close();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $update_stmt = $conn->prepare("UPDATE worker_online_status SET is_online = ?, last_seen = NOW(), app_version = ?, device_type = ? WHERE worker_id = ?");
        $update_stmt->bind_param("issi", $is_online, $app_version, $device_type, $worker_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert new record
        $insert_stmt = $conn->prepare("INSERT INTO worker_online_status (worker_id, is_online, last_seen, app_version, device_type) VALUES (?, ?, NOW(), ?, ?)");
        $insert_stmt->bind_param("iiss", $worker_id, $is_online, $app_version, $device_type);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    // If worker is going offline, also update their location status
    if (!$is_online) {
        $location_stmt = $conn->prepare("UPDATE worker_locations SET is_moving = 0 WHERE worker_id = ?");
        $location_stmt->bind_param("i", $worker_id);
        $location_stmt->execute();
        $location_stmt->close();
    }
}
?>