<?php
header('Content-Type: application/json');
require_once('../config/database.php');
require_once('../includes/session-config.php');

startSecureSession();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$worker_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['latitude']) || !isset($input['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid location data']);
    exit;
}

$conn = getDBConnection();

try {
    // Update current location
    $sql = "INSERT INTO worker_locations (worker_id, latitude, longitude, timestamp) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude), 
            timestamp = NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idd", $worker_id, $input['latitude'], $input['longitude']);
    $stmt->execute();
    
    // Update online status
    $status_sql = "INSERT INTO worker_online_status (worker_id, is_online, last_seen) 
                   VALUES (?, 1, NOW())
                   ON DUPLICATE KEY UPDATE 
                   is_online = 1, 
                   last_seen = NOW()";
    
    $status_stmt = $conn->prepare($status_sql);
    $status_stmt->bind_param("i", $worker_id);
    $status_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Location updated']);
    
} catch (Exception $e) {
    error_log("Error updating location: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating location']);
}

$conn->close();
?>