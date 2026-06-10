<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$conn = getDBConnection();

try {
    $query = "SELECT id, id_number, fullname, email, phone, dob, role, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch users: ' . $e->getMessage()
    ]);
}

$conn->close();
?>