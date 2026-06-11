<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $image_id = intval($_POST['image_id']);
    
    $conn = getDBConnection();
    
    // Verify image belongs to user's vehicle
    $check_stmt = $conn->prepare("SELECT vi.id, vi.image_path 
                                 FROM vehicle_images vi 
                                 JOIN vehicles v ON vi.vehicle_id = v.id 
                                 WHERE vi.id = ? AND v.user_id = ?");
    $check_stmt->bind_param("ii", $image_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $response['message'] = 'Image not found or access denied.';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $image = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Delete image file
    if (file_exists($image['image_path'])) {
        unlink($image['image_path']);
    }
    
    // Delete database record
    $delete_stmt = $conn->prepare("DELETE FROM vehicle_images WHERE id = ?");
    $delete_stmt->bind_param("i", $image_id);
    
    if ($delete_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Image deleted successfully!';
    } else {
        $response['message'] = 'Error deleting image record.';
    }
    
    $delete_stmt->close();
    $conn->close();
}

echo json_encode($response);
?>