<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

// Get user ID from request
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

$conn = getDBConnection();

try {
    // Check if user exists and is not admin
    $check_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $userId);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    if ($user['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Cannot delete admin users']);
        exit;
    }
    
    // Delete user
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $userId);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        throw new Exception('Failed to delete user');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>