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
    $theme = $_POST['theme'] ?? 'light';
    $sidebar_style = $_POST['sidebar_style'] ?? 'expanded';
    $show_charts = isset($_POST['show_charts']) ? 1 : 0;
    $recent_activity = isset($_POST['recent_activity']) ? 1 : 0;
    $quick_actions = isset($_POST['quick_actions']) ? 1 : 0;
    
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
                      theme = ?, sidebar_style = ?, show_charts = ?, recent_activity = ?,
                      quick_actions = ?, updated_at = NOW() 
                      WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssiiii", $theme, $sidebar_style, $show_charts, $recent_activity, 
                         $quick_actions, $user_id);
    } else {
        // Insert new settings with defaults for other fields
        $insert_sql = "INSERT INTO user_settings 
                      (user_id, theme, sidebar_style, show_charts, recent_activity,
                       quick_actions, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("issiii", $user_id, $theme, $sidebar_style, $show_charts, 
                         $recent_activity, $quick_actions);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Preferences saved successfully';
    } else {
        $response['message'] = 'Error saving settings: ' . $stmt->error;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error saving settings: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>