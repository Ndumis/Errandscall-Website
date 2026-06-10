<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $vehicle_id = intval($_POST['vehicle_id']);
    $make = trim($_POST['make_id']);
    $model = trim($_POST['model_id']);
    $year = intval($_POST['year']);
    $license_plate = trim($_POST['license_plate']);
    $vin = trim($_POST['vin']);
    $color = trim($_POST['color']);
    $disc_expiry = $_POST['disc_expiry'];
    $license_expiry = $_POST['license_expiry'];
    
    // Validation
    if (empty($vehicle_id) || empty($make) || empty($model) || empty($license_plate) || empty($disc_expiry) || empty($license_expiry)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Verify vehicle belongs to user (for customers)
    if (isCustomer()) {
        $check_stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $vehicle_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $response['message'] = 'Vehicle not found or access denied.';
            $check_stmt->close();
            $conn->close();
            echo json_encode($response);
            exit;
        }
        $check_stmt->close();
    }
    
    // Check if license plate already exists (excluding current vehicle)
    $check_stmt = $conn->prepare("SELECT id FROM vehicles WHERE license_plate = ? AND id != ?");
    $check_stmt->bind_param("si", $license_plate, $vehicle_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $response['message'] = 'License plate already exists.';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Get model name from car_models table
    $model_stmt = $conn->prepare("SELECT name FROM car_models WHERE id = ?");
    $model_stmt->bind_param("i", $model);
    $model_stmt->execute();
    $model_result = $model_stmt->get_result();
    
    if ($model_result->num_rows > 0) {
        $model_data = $model_result->fetch_assoc();
        $model_name = $model_data['name'];
    } else {
        $response['message'] = 'Invalid model selected.';
        $model_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $model_stmt->close();
    
    // Update vehicle
    $stmt = $conn->prepare("UPDATE vehicles SET make = ?, model = ?, year = ?, license_plate = ?, vin = ?, color = ?, disc_expiry = ?, license_expiry = ? WHERE id = ?");
    $stmt->bind_param("ssisssssi", $make, $model_name, $year, $license_plate, $vin, $color, $disc_expiry, $license_expiry, $vehicle_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Vehicle updated successfully!';
    } else {
        $response['message'] = 'Error updating vehicle: ' . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>