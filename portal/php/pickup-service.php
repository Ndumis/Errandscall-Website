<?php
include('../config/database.php');
include('../includes/auth-check.php');

header('Content-Type: application/json');

// Only workers can pick up services
if (!isWorker()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$service_id = isset($data['service_id']) ? intval($data['service_id']) : 0;

if ($service_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid service ID' .$data]);
    exit;
}

$conn = getDBConnection();

// Check if service exists and is available
$check_stmt = $conn->prepare("SELECT id, status, assigned_to FROM services WHERE id = ?");
$check_stmt->bind_param("i", $service_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$service = $result->fetch_assoc();
$check_stmt->close();

if (!$service) {
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    $conn->close();
    exit;
}

if ($service['assigned_to'] !== null) {
    echo json_encode(['success' => false, 'message' => 'Service is already assigned']);
    $conn->close();
    exit;
}

if ($service['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Service is not available for pickup']);
    $conn->close();
    exit;
}

// Assign service to worker
$update_stmt = $conn->prepare("UPDATE services SET assigned_to = ?, status = 'assigned', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$update_stmt->bind_param("ii", $user_id, $service_id);

if ($update_stmt->execute()) {
    // Add progress update
    $update_text = "Service picked up by worker";
    $update_stmt2 = $conn->prepare("INSERT INTO service_updates (service_id, user_id, update_text, update_type) VALUES (?, ?, ?, 'status_change')");
    $update_stmt2->bind_param("iis", $service_id, $user_id, $update_text);
    $update_stmt2->execute();
    $update_stmt2->close();
    
    // Log activity
    logActivity($user_id, 'service_pickup', "Picked up service #" . $service_id);
    
    echo json_encode(['success' => true, 'message' => 'Service picked up successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to pick up service']);
}

$update_stmt->close();
$conn->close();
?>