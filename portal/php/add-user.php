<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    // Validation
    if (empty($id_number) || empty($fullname) || empty($email) || empty($phone) || empty($dob) || empty($role) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Check if email or ID number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR id_number = ?");
    $stmt->bind_param("ss", $email, $id_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['message'] = 'Email or ID number already registered.';
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $status = 'active';
    $stmt = $conn->prepare("INSERT INTO users (id_number, fullname, email, phone, dob, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $id_number, $fullname, $email, $phone, $dob, $hashed_password, $role, $status);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User added successfully!';
    } else {
        $response['message'] = 'Error adding user: ' . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>