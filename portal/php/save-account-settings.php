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
    $language = $_POST['language'] ?? 'en';
    $timezone = $_POST['timezone'] ?? 'UTC';
    $date_format = $_POST['date_format'] ?? 'Y-m-d';
    $items_per_page = intval($_POST['items_per_page'] ?? 25);
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    
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
                      language = ?, timezone = ?, date_format = ?, items_per_page = ?,
                      email_notifications = ?, sms_notifications = ?, updated_at = NOW() 
                      WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssiiii", $language, $timezone, $date_format, $items_per_page, 
                         $email_notifications, $sms_notifications, $user_id);
    } else {
        // Insert new settings
        $insert_sql = "INSERT INTO user_settings 
                      (user_id, language, timezone, date_format, items_per_page, 
                       email_notifications, sms_notifications, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("isssiii", $user_id, $language, $timezone, $date_format, $items_per_page,
                         $email_notifications, $sms_notifications);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Account settings saved successfully';
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