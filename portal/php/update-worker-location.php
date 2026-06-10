<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only workers can update their location
    if (!hasAccess(['worker'])) {
        $response['message'] = 'Access denied';
        echo json_encode($response);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $accuracy = isset($_POST['accuracy']) ? floatval($_POST['accuracy']) : null;
    $battery_level = isset($_POST['battery_level']) ? intval($_POST['battery_level']) : null;
    $is_moving = isset($_POST['is_moving']) ? boolval($_POST['is_moving']) : false;
    
    // Validate coordinates
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        $response['message'] = 'Invalid coordinates';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Insert location data
    $stmt = $conn->prepare("
        INSERT INTO worker_locations 
        (worker_id, latitude, longitude, accuracy, battery_level, is_moving) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddii", $user_id, $latitude, $longitude, $accuracy, $battery_level, $is_moving);
    
    if ($stmt->execute()) {
        // Update online status
        $update_stmt = $conn->prepare("
            UPDATE worker_online_status 
            SET is_online = 1, last_seen = NOW() 
            WHERE worker_id = ?
        ");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Archive to history (keep lightweight history)
        $history_stmt = $conn->prepare("
            INSERT INTO worker_location_history (worker_id, latitude, longitude)
            VALUES (?, ?, ?)
        ");
        $history_stmt->bind_param("idd", $user_id, $latitude, $longitude);
        $history_stmt->execute();
        $history_stmt->close();
        
        $response['success'] = true;
        $response['message'] = 'Location updated successfully';
        $response['location_id'] = $conn->insert_id;
    } else {
        $response['message'] = 'Error updating location: ' . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>