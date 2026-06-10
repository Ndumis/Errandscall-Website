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
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validation
if (empty($fullname) || empty($email)) {
    $response['message'] = 'Full name and email are required';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();

try {
    // Check if email already exists (excluding current user)
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $response['message'] = 'Email already exists';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Update user profile
    $update_sql = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $fullname, $email, $phone, $address, $user_id);
    
    if ($update_stmt->execute()) {
        // Update session data
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_email'] = $email;
        
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully';
    } else {
        $response['message'] = 'Error updating profile: ' . $update_stmt->error;
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error updating profile: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>