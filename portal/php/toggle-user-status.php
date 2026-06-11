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
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

    if ($user_id === $_SESSION['user_id']) {
        $response['message'] = 'You cannot change your own account status.';
        echo json_encode($response);
        exit;
    }

    $conn = getDBConnection();

    $check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();
    $check_stmt->close();

    if (!$user) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    if ($user['role'] === 'admin') {
        $response['message'] = 'Cannot change status of admin users.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $user_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $status === 'active' ? 'User activated successfully!' : 'User deactivated successfully!';
        $response['status'] = $status;
    } else {
        $response['message'] = 'Error updating user status: ' . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
