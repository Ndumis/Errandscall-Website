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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $response['message'] = 'All password fields are required';
    echo json_encode($response);
    exit;
}

if ($new_password !== $confirm_password) {
    $response['message'] = 'New passwords do not match';
    echo json_encode($response);
    exit;
}

if (strlen($new_password) < 8) {
    $response['message'] = 'Password must be at least 8 characters long';
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();

try {
    // Get current password hash
    $user_sql = "SELECT password FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        $response['message'] = 'User not found';
        $user_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $user = $user_result->fetch_assoc();
    $user_stmt->close();
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $response['message'] = 'Current password is incorrect';
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_password_hash, $user_id);
    
    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password changed successfully';
    } else {
        $response['message'] = 'Error changing password: ' . $update_stmt->error;
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error changing password: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>