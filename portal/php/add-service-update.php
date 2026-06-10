<?php
include('../config/database.php');
include('../includes/auth-check.php');

header('Content-Type: application/json');

// Only workers can add updates to their assigned services
if (!isWorker()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
$update_type = isset($_POST['update_type']) ? $_POST['update_type'] : '';
$update_text = isset($_POST['update_text']) ? trim($_POST['update_text']) : '';

if ($service_id <= 0 || empty($update_type) || empty($update_text)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate update type
$allowed_types = ['progress_update', 'document_request', 'status_change', 'note'];
if (!in_array($update_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid update type']);
    exit;
}

$conn = getDBConnection();

// Check if service exists and is assigned to this worker
$check_stmt = $conn->prepare("SELECT id FROM services WHERE id = ? AND assigned_to = ?");
$check_stmt->bind_param("ii", $service_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$service = $result->fetch_assoc();
$check_stmt->close();

if (!$service) {
    echo json_encode(['success' => false, 'message' => 'Service not found or not assigned to you']);
    $conn->close();
    exit;
}

// Add service update
$insert_stmt = $conn->prepare("INSERT INTO service_updates (service_id, user_id, update_text, update_type) VALUES (?, ?, ?, ?)");
$insert_stmt->bind_param("iiss", $service_id, $user_id, $update_text, $update_type);

if ($insert_stmt->execute()) {
    // Log activity
    logActivity($user_id, 'service_update_added', "Added update to service #" . $service_id);
    
    echo json_encode(['success' => true, 'message' => 'Progress update added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add progress update']);
}

$insert_stmt->close();
$conn->close();
?>