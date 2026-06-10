<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'messages' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

$conn = getDBConnection();

// Get chat messages
$stmt = $conn->prepare("
    SELECT 
        cm.*,
        u_sender.fullname as sender_name,
        u_sender.role as sender_role,
        u_receiver.fullname as receiver_name
    FROM chat_messages cm
    JOIN users u_sender ON cm.sender_id = u_sender.id
    JOIN users u_receiver ON cm.receiver_id = u_receiver.id
    WHERE cm.service_id = ? AND (cm.sender_id = ? OR cm.receiver_id = ?)
    ORDER BY cm.created_at ASC
");
$stmt->bind_param("iii", $service_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$response['success'] = true;
while ($row = $result->fetch_assoc()) {
    $response['messages'][] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'sender_id' => $row['sender_id'],
        'sender_name' => $row['sender_name'],
        'sender_role' => $row['sender_role'],
        'is_own_message' => ($row['sender_id'] == $user_id),
        'created_at' => $row['created_at'],
        'time_ago' => timeAgo($row['created_at'])
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return date('M j, g:i A', $time);
}
?>