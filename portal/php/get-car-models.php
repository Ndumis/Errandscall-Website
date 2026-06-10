<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'data' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['make_id'])) {
    $make_id = intval($_GET['make_id']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, year_from, year_to FROM car_models WHERE make_id = ? AND is_active = TRUE ORDER BY name");
    $stmt->bind_param("i", $make_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $response['success'] = true;
        while ($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>