<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $id_number = trim($_POST['id_number']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    // Managers cannot change roles to admin
    if ($_SESSION['user_role'] === 'manager' && $role === 'admin') {
        $response['message'] = 'Managers cannot assign admin role.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Check if email or ID number already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR id_number = ?) AND id != ?");
    $stmt->bind_param("ssi", $email, $id_number, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['message'] = 'Email or ID number already registered by another user.';
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
    
    // Build update query
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET id_number = ?, fullname = ?, email = ?, phone = ?, dob = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $id_number, $fullname, $email, $phone, $dob, $role, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET id_number = ?, fullname = ?, email = ?, phone = ?, dob = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $id_number, $fullname, $email, $phone, $dob, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User updated successfully!';
    } else {
        error_log('Error updating user: ' . $conn->error);
        $response['message'] = 'Error updating user. Please try again.';
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>