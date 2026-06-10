<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'services' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT s.*, v.make, v.model, v.license_plate, v.year
    FROM services s
    JOIN vehicles v ON s.vehicle_id = v.id
    WHERE s.user_id = ? AND s.status = 'completed'
    AND NOT EXISTS (
        SELECT 1 FROM service_ratings sr WHERE sr.service_id = s.id AND sr.user_id = s.user_id
    )
    ORDER BY s.updated_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$response['success'] = true;
while ($row = $result->fetch_assoc()) {
    $row['vehicle_info'] = $row['make'] . ' ' . $row['model'] . ' (' . $row['license_plate'] . ')';
    $response['services'][] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
