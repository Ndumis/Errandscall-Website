<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = [
    'success' => false,
    'unreadMessages' => 0,
    'unreadNotifications' => 0
];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get unread messages count
$messages_stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM chat_messages 
    WHERE receiver_id = ? AND is_read = FALSE
");
$messages_stmt->bind_param("i", $user_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$response['unreadMessages'] = $messages_result->fetch_assoc()['unread_count'] ?? 0;
$messages_stmt->close();

// Get unread notifications count (you'll need to create a notifications table)
$notifications_stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM notifications 
    WHERE user_id = ? AND is_read = FALSE
");
$notifications_stmt->bind_param("i", $user_id);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();
$response['unreadNotifications'] = $notifications_result->fetch_assoc()['unread_count'] ?? 0;
$notifications_stmt->close();

$response['success'] = true;
$conn->close();

echo json_encode($response);
?>