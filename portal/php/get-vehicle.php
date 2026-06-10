<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => '', 'vehicle' => null];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $vehicle_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    $conn = getDBConnection();
    
    // Build query based on user role
    if (isCustomer()) {
        $stmt = $conn->prepare("SELECT v.*, cm.id as make_id, cm2.id as model_id 
                               FROM vehicles v 
                               LEFT JOIN car_models cm2 ON v.model = cm2.name 
                               LEFT JOIN car_makes cm ON cm2.make_id = cm.id 
                               WHERE v.id = ? AND v.user_id = ?");
        $stmt->bind_param("ii", $vehicle_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT v.*, cm.id as make_id, cm2.id as model_id 
                               FROM vehicles v 
                               LEFT JOIN car_models cm2 ON v.model = cm2.name 
                               LEFT JOIN car_makes cm ON cm2.make_id = cm.id 
                               WHERE v.id = ?");
        $stmt->bind_param("i", $vehicle_id);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['vehicle'] = $result->fetch_assoc();
        } else {
            $response['message'] = 'Vehicle not found.';
        }
    } else {
        $response['message'] = 'Error fetching vehicle: ' . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
?>