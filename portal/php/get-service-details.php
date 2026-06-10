<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'service' => [], 'updates' => [], 'documents' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

if (!isset($_GET['id'])) {
    $response['message'] = 'Service ID is required.';
    echo json_encode($response);
    exit;
}

$service_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$conn = getDBConnection();

// Get service details with access control
if ($user_role === 'customer') {
    $service_sql = "SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                   CONCAT(v.make, ' ', v.model, ' (', v.license_plate, ')') as vehicle_info,
                   u.fullname as customer_name,
                   u2.fullname as assigned_to_name 
                   FROM services s 
                   JOIN vehicles v ON s.vehicle_id = v.id 
                   JOIN users u ON s.user_id = u.id 
                   LEFT JOIN users u2 ON s.assigned_to = u2.id 
                   WHERE s.id = ? AND s.user_id = ?";
    $service_stmt = $conn->prepare($service_sql);
    $service_stmt->bind_param("ii", $service_id, $user_id);
} else {
    $service_sql = "SELECT s.*, v.make, v.model, v.license_plate, v.year, 
                   CONCAT(v.make, ' ', v.model, ' (', v.license_plate, ')') as vehicle_info,
                   u.fullname as customer_name,
                   u2.fullname as assigned_to_name 
                   FROM services s 
                   JOIN vehicles v ON s.vehicle_id = v.id 
                   JOIN users u ON s.user_id = u.id 
                   LEFT JOIN users u2 ON s.assigned_to = u2.id 
                   WHERE s.id = ?";
    $service_stmt = $conn->prepare($service_sql);
    $service_stmt->bind_param("i", $service_id);
}

$service_stmt->execute();
$service_result = $service_stmt->get_result();

if ($service_result->num_rows === 0) {
    $response['message'] = 'Service not found or access denied.';
    $service_stmt->close();
    $conn->close();
    echo json_encode($response);
    exit;
}

$response['service'] = $service_result->fetch_assoc();
$service_stmt->close();

// Get service updates
$updates_stmt = $conn->prepare("SELECT su.*, u.fullname as user_name 
                               FROM service_updates su 
                               JOIN users u ON su.user_id = u.id 
                               WHERE su.service_id = ? 
                               ORDER BY su.created_at DESC");
$updates_stmt->bind_param("i", $service_id);
$updates_stmt->execute();
$updates_result = $updates_stmt->get_result();

while ($update = $updates_result->fetch_assoc()) {
    $response['updates'][] = $update;
}
$updates_stmt->close();

// Get service documents
$docs_stmt = $conn->prepare("SELECT sd.*, u.fullname as uploaded_by_name 
                            FROM service_documents sd 
                            JOIN users u ON sd.uploaded_by = u.id 
                            WHERE sd.service_id = ? 
                            ORDER BY sd.created_at DESC");
$docs_stmt->bind_param("i", $service_id);
$docs_stmt->execute();
$docs_result = $docs_stmt->get_result();

while ($doc = $docs_result->fetch_assoc()) {
    $response['documents'][] = $doc;
}
$docs_stmt->close();

$conn->close();

$response['success'] = true;
echo json_encode($response);
?>