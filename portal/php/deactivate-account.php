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
    // Deactivate user account (soft delete)
    $update_sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    
    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Account deactivated successfully';
    } else {
        error_log('Error deactivating account: ' . $update_stmt->error);
        $response['message'] = 'Error deactivating account. Please try again.';
    }

    $update_stmt->close();

} catch (Exception $e) {
    error_log('Error deactivating account: ' . $e->getMessage());
    $response['message'] = 'Error deactivating account. Please try again.';
}

$conn->close();
echo json_encode($response);
?>