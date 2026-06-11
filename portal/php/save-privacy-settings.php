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
    $data_collection = isset($_POST['data_collection']) ? 1 : 0;
    $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
    $two_factor_auth = isset($_POST['two_factor_auth']) ? 1 : 0;
    $login_alerts = isset($_POST['login_alerts']) ? 1 : 0;
    
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
                      data_collection = ?, marketing_emails = ?, two_factor_auth = ?,
                      login_alerts = ?, updated_at = NOW() 
                      WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iiiii", $data_collection, $marketing_emails, $two_factor_auth, 
                         $login_alerts, $user_id);
    } else {
        // Insert new settings with defaults for other fields
        $insert_sql = "INSERT INTO user_settings 
                      (user_id, data_collection, marketing_emails, two_factor_auth,
                       login_alerts, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiiii", $user_id, $data_collection, $marketing_emails, 
                         $two_factor_auth, $login_alerts);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Privacy settings saved successfully';
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