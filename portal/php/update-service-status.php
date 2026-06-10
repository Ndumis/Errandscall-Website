<?php
include('../config/database.php');
include('../includes/auth-check.php');

header('Content-Type: application/json');

// Only workers can update their assigned services
if (!isWorker()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$priority = isset($_POST['priority']) ? $_POST['priority'] : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($service_id <= 0 || empty($status) || empty($priority)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate status
$allowed_statuses = ['assigned', 'in_progress', 'completed'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$conn = getDBConnection();

// Check if service exists and is assigned to this worker
$check_stmt = $conn->prepare("SELECT id, status FROM services WHERE id = ? AND assigned_to = ?");
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

// Update service
$update_stmt = $conn->prepare("UPDATE services SET status = ?, priority = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$update_stmt->bind_param("ssi", $status, $priority, $service_id);

if ($update_stmt->execute()) {
    // Add progress update if notes are provided
    if (!empty($notes)) {
        $update_text = "Status updated to: " . str_replace('_', ' ', $status) . ". Notes: " . $notes;
        $update_type = 'status_change';
    } else {
        $update_text = "Status updated to: " . str_replace('_', ' ', $status);
        $update_type = 'status_change';
    }
    
    $update_stmt2 = $conn->prepare("INSERT INTO service_updates (service_id, user_id, update_text, update_type) VALUES (?, ?, ?, ?)");
    $update_stmt2->bind_param("iiss", $service_id, $user_id, $update_text, $update_type);
    $update_stmt2->execute();
    $update_stmt2->close();
    
    // Log activity
    logActivity($user_id, 'service_update', "Updated service #" . $service_id . " to " . $status);
    
    echo json_encode(['success' => true, 'message' => 'Service updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update service']);
}

$update_stmt->close();
$conn->close();
?>