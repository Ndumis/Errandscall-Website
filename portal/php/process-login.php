<?php
header('Content-Type: application/json');

require_once('../includes/session-config.php');
startSecureSession();

include('../config/database.php');
require_once('../includes/rate-limit.php');

// Lock an account after 5 failed logins, or an IP address after 20, for 15 minutes
const LOGIN_MAX_PER_ACCOUNT = 5;
const LOGIN_MAX_PER_IP = 20;
const LOGIN_WINDOW_MINUTES = 15;

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();

    if (tooManyAttempts($conn, 'login', $username, LOGIN_MAX_PER_ACCOUNT, LOGIN_MAX_PER_IP, LOGIN_WINDOW_MINUTES)) {
        $conn->close();
        $response['message'] = 'Too many failed login attempts. Please wait ' . LOGIN_WINDOW_MINUTES . ' minutes and try again, or reset your password.';
        echo json_encode($response);
        exit;
    }
    
    // Check if username is email or ID number
    $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'id_number';
    
    $stmt = $conn->prepare("SELECT id, password, fullname, role, status FROM users WHERE $field = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->num_rows === 1 ? $result->fetch_assoc() : null;
    $stmt->close();

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if ($user && password_verify($password, $user['password'])) {
        // Only reveal the account status once the password is proven correct
        if ($user['status'] !== 'active') {
            $response['message'] = 'Your account is inactive. Please contact administrator.';
            $conn->close();
            echo json_encode($response);
            exit;
        }

        clearAttempts($conn, 'login', $username);

        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        // Log login activity
        $activity_type = 'login';
        $description = 'User logged in successfully';
        
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("issss", $user['id'], $activity_type, $description, $ip_address, $user_agent);
        $log_stmt->execute();
        $log_stmt->close();
        
        // Update worker online status if user is a worker
        updateWorkerOnlineStatus($conn, $user['id'], true);
        
        $response['success'] = true;
        $response['message'] = 'Login successful!';
        $response['redirect'] = 'dashboard.php';
    } else {
        // Same message whether the account exists or not, so accounts can't be discovered
        $response['message'] = 'Incorrect email/ID number or password.';
        recordAttempt($conn, 'login', $username);

        // Failed attempts on real accounts also go to the activity log
        // (activity_log.user_id must reference an existing user)
        if ($user) {
            $activity_type = 'failed_login';
            $description = 'Failed login attempt - incorrect password';
            
            $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $log_stmt->bind_param("issss", $user['id'], $activity_type, $description, $ip_address, $user_agent);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
    
    $conn->close();
}

echo json_encode($response);

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