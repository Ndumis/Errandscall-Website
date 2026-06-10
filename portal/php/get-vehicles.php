<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'vehicles' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = isset($_GET['recent']) ? intval($_GET['recent']) : 5;
$conn = getDBConnection();

if (isCustomer()) {
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
} else {
    $stmt = $conn->prepare("SELECT v.*, u.fullname as owner_name FROM vehicles v JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $response['success'] = true;
    while ($row = $result->fetch_assoc()) {
        $response['vehicles'][] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>