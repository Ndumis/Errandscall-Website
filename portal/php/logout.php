<?php
require_once('../includes/session-config.php');
startSecureSession();

// Log logout activity if user was logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    include('../config/database.php');
    $conn = getDBConnection();
    
    $activity_type = 'logout';
    $description = 'User logged out';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $log_stmt->bind_param("issss", $_SESSION['user_id'], $activity_type, $description, $ip_address, $user_agent);
    $log_stmt->execute();
    $log_stmt->close();
    
    // Update worker online status if user is a worker
    updateWorkerOnlineStatus($conn, $_SESSION['user_id'], false);
    
    $conn->close();
}

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../index.php?message=logged_out');
exit;

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