<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'settings' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    // Get user settings from database
    $settings_sql = "SELECT * FROM user_settings WHERE user_id = ?";
    $settings_stmt = $conn->prepare($settings_sql);
    $settings_stmt->bind_param("i", $user_id);
    $settings_stmt->execute();
    $settings_result = $settings_stmt->get_result();
    
    if ($settings_result->num_rows > 0) {
        $response['settings'] = $settings_result->fetch_assoc();
    } else {
        // Return default settings if none exist
        $response['settings'] = [
            'language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'items_per_page' => 25,
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'service_updates' => 1,
            'assignment_notifications' => 1,
            'document_uploads' => 1,
            'system_maintenance' => 1,
            'feature_updates' => 0,
            'data_collection' => 1,
            'marketing_emails' => 0,
            'two_factor_auth' => 0,
            'login_alerts' => 1,
            'theme' => 'light',
            'sidebar_style' => 'expanded',
            'show_charts' => 1,
            'recent_activity' => 1,
            'quick_actions' => 1
        ];
    }
    
    $settings_stmt->close();
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Error fetching settings: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>