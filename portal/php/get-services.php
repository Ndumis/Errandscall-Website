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
$limit = isset($_GET['recent']) ? intval($_GET['recent']) : 0;
$conn = getDBConnection();

if (isCustomer()) {
    $sql = "SELECT s.*, v.make, v.model, v.license_plate, v.year
            FROM services s
            JOIN vehicles v ON s.vehicle_id = v.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC";
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $limit);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
} else {
    $sql = "SELECT s.*, v.make, v.model, v.license_plate, v.year, u.fullname as owner_name
            FROM services s
            JOIN vehicles v ON s.vehicle_id = v.id
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC";
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
    } else {
        $stmt = $conn->prepare($sql);
    }
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response['success'] = true;
    while ($row = $result->fetch_assoc()) {
        $row['vehicle_info'] = $row['make'] . ' ' . $row['model'] . ' (' . $row['license_plate'] . ')';
        $response['services'][] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>