<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    $service_updates = isset($_POST['service_updates']) ? 1 : 0;
    $assignment_notifications = isset($_POST['assignment_notifications']) ? 1 : 0;
    $document_uploads = isset($_POST['document_uploads']) ? 1 : 0;
    $system_maintenance = isset($_POST['system_maintenance']) ? 1 : 0;
    $feature_updates = isset($_POST['feature_updates']) ? 1 : 0;
    
    // Check if settings already exist
    $check_sql = "SELECT id FROM user_settings WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();
    
    if ($check_result->num_rows > 0) {
        // Update existing settings
        $update_sql = "UPDATE user_settings SET 
                      service_updates = ?, assignment_notifications = ?, document_uploads = ?,
                      system_maintenance = ?, feature_updates = ?, updated_at = NOW() 
                      WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iiiiii", $service_updates, $assignment_notifications, $document_uploads,
                         $system_maintenance, $feature_updates, $user_id);
    } else {
        // Insert new settings with defaults for other fields
        $insert_sql = "INSERT INTO user_settings 
                      (user_id, service_updates, assignment_notifications, document_uploads,
                       system_maintenance, feature_updates, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiiiii", $user_id, $service_updates, $assignment_notifications, 
                         $document_uploads, $system_maintenance, $feature_updates);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Notification settings saved successfully';
    } else {
        error_log('Error saving settings: ' . $stmt->error);
        $response['message'] = 'Error saving settings. Please try again.';
    }

    $stmt->close();

} catch (Exception $e) {
    error_log('Error saving settings: ' . $e->getMessage());
    $response['message'] = 'Error saving settings. Please try again.';
}

$conn->close();
echo json_encode($response);
?>