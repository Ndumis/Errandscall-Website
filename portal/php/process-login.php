<?php
header('Content-Type: application/json');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/database.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Check if username is email or ID number
    $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'id_number';
    
    $stmt = $conn->prepare("SELECT id, password, fullname, role, status FROM users WHERE $field = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if user account is active
        if ($user['status'] !== 'active') {
            $response['message'] = 'Your account is inactive. Please contact administrator.';
            echo json_encode($response);
            exit;
        }
        
        if (password_verify($password, $user['password'])) {
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
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            
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
            $response['message'] = 'Invalid password.';
            
            // Log failed login attempt
            $activity_type = 'failed_login';
            $description = 'Failed login attempt - incorrect password';
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            
            $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $log_stmt->bind_param("issss", $user['id'], $activity_type, $description, $ip_address, $user_agent);
            $log_stmt->execute();
            $log_stmt->close();
        }
    } else {
        $response['message'] = 'User not found.';
        
        // Log failed login attempt for non-existent user
        $activity_type = 'failed_login';
        $description = 'Failed login attempt - user not found';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("issss", 0, $activity_type, $description, $ip_address, $user_agent);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    $stmt->close();
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