<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest($conn, $user_id);
        break;
        
    case 'POST':
        handlePostRequest($conn, $user_id);
        break;
        
    case 'DELETE':
        handleDeleteRequest($conn, $user_id);
        break;
}

$conn->close();
echo json_encode($response);

function handleGetRequest($conn, $user_id) {
    global $response;
    
    if (isset($_GET['worker_id'])) {
        // Get specific worker's location (admin/manager only)
        if (!hasAccess(['admin', 'manager'])) {
            $response['message'] = 'Access denied';
            return;
        }
        
        $worker_id = intval($_GET['worker_id']);
        $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;
        
        $stmt = $conn->prepare("
            SELECT wl.*, u.fullname, u.phone, u.email,
                   (SELECT COUNT(*) FROM services s WHERE s.assigned_to = u.id AND s.status IN ('assigned', 'in_progress')) as active_services
            FROM worker_locations wl
            JOIN users u ON wl.worker_id = u.id
            WHERE wl.worker_id = ? AND wl.timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY wl.timestamp DESC
            LIMIT 100
        ");
        $stmt->bind_param("ii", $worker_id, $hours);
    } else if (hasAccess(['worker'])) {
        // Get own location history
        $stmt = $conn->prepare("
            SELECT * FROM worker_locations 
            WHERE worker_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY timestamp DESC 
            LIMIT 100
        ");
        $stmt->bind_param("i", $user_id);
    } else {
        $response['message'] = 'Access denied';
        return;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $response['success'] = true;
    $response['locations'] = [];
    
    while ($row = $result->fetch_assoc()) {
        $response['locations'][] = $row;
    }
    $stmt->close();
}

function handlePostRequest($conn, $user_id) {
    global $response;
    
    // Allow workers AND managers to update location
    if (!hasAccess(['worker', 'manager'])) {
        $response['message'] = 'Access denied';
        return;
    }
    
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $accuracy = isset($_POST['accuracy']) ? floatval($_POST['accuracy']) : null;
    $speed = isset($_POST['speed']) ? floatval($_POST['speed']) : null;
    $heading = isset($_POST['heading']) ? floatval($_POST['heading']) : null;
    $altitude = isset($_POST['altitude']) ? floatval($_POST['altitude']) : null;
    $battery_level = isset($_POST['battery_level']) ? intval($_POST['battery_level']) : null;
    $is_moving = isset($_POST['is_moving']) ? boolval($_POST['is_moving']) : false;
    $app_version = isset($_POST['app_version']) ? $_POST['app_version'] : null;
    $device_type = isset($_POST['device_type']) ? $_POST['device_type'] : null;
    
    // Insert location data
    $stmt = $conn->prepare("
        INSERT INTO worker_locations 
        (worker_id, latitude, longitude, accuracy, speed, heading, altitude, battery_level, is_moving) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iddddddii", $user_id, $latitude, $longitude, $accuracy, $speed, $heading, $altitude, $battery_level, $is_moving);
    
    if ($stmt->execute()) {
        // Update online status
        updateOnlineStatus($conn, $user_id, true, $app_version, $device_type);
        
        // Archive to history (keep lightweight history)
        $history_stmt = $conn->prepare("
            INSERT INTO worker_location_history (worker_id, latitude, longitude)
            VALUES (?, ?, ?)
        ");
        $history_stmt->bind_param("idd", $user_id, $latitude, $longitude);
        $history_stmt->execute();
        $history_stmt->close();
        
        $response['success'] = true;
        $response['message'] = 'Location updated';
        $response['location_id'] = $conn->insert_id;
    } else {
        $response['message'] = 'Error updating location';
    }
    $stmt->close();
}

function handleDeleteRequest($conn, $user_id) {
    global $response;
    
    parse_str(file_get_contents("php://input"), $delete_vars);
    $location_id = $delete_vars['id'];
    
    if (hasAccess(['admin', 'manager'])) {
        $stmt = $conn->prepare("DELETE FROM worker_locations WHERE id = ?");
        $stmt->bind_param("i", $location_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM worker_locations WHERE id = ? AND worker_id = ?");
        $stmt->bind_param("ii", $location_id, $user_id);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Location data deleted';
    } else {
        $response['message'] = 'Error deleting location data';
    }
    $stmt->close();
}

function updateOnlineStatus($conn, $user_id, $is_online, $app_version = null, $device_type = null) {
    $stmt = $conn->prepare("
        INSERT INTO worker_online_status 
        (worker_id, is_online, last_seen, app_version, device_type) 
        VALUES (?, ?, NOW(), ?, ?)
        ON DUPLICATE KEY UPDATE 
        is_online = VALUES(is_online), 
        last_seen = VALUES(last_seen),
        app_version = VALUES(app_version),
        device_type = VALUES(device_type)
    ");
    $stmt->bind_param("iiss", $user_id, $is_online, $app_version, $device_type);
    $stmt->execute();
    $stmt->close();
}
?>