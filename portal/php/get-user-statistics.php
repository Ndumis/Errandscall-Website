<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'statistics' => []];

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();

$stats = [];
$roles = ['customer', 'worker', 'manager', 'admin'];

foreach ($roles as $role) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats[$role . 's'] = $result->fetch_assoc()['count'];
    $stmt->close();
}

$response['success'] = true;
$response['statistics'] = $stats;

$conn->close();
echo json_encode($response);
?>