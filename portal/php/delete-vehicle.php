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
    
    if (empty($vehicle_id)) {
        $response['message'] = 'Vehicle ID is required.';
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
    
    // Delete vehicle images first
    $images_stmt = $conn->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ?");
    $images_stmt->bind_param("i", $vehicle_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    
    // Delete physical image files
    while ($image = $images_result->fetch_assoc()) {
        if (file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }
    }
    $images_stmt->close();
    
    // Delete image records from database
    $delete_images_stmt = $conn->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
    $delete_images_stmt->bind_param("i", $vehicle_id);
    $delete_images_stmt->execute();
    $delete_images_stmt->close();
    
    // Delete vehicle
    $delete_stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $delete_stmt->bind_param("i", $vehicle_id);
    
    if ($delete_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Vehicle deleted successfully!';
    } else {
        error_log('Error deleting vehicle: ' . $conn->error);
        $response['message'] = 'Error deleting vehicle. Please try again.';
    }
    
    $delete_stmt->close();
    $conn->close();
}

echo json_encode($response);
?>