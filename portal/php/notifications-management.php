<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get notifications
        $limit = intval($_GET['limit'] ?? 50);
        $unread_only = $_GET['unread_only'] ?? false;
        
        $query = "
            SELECT * FROM notifications 
            WHERE user_id = ?
        ";
        
        if ($unread_only) {
            $query .= " AND is_read = FALSE";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response['success'] = true;
        $response['notifications'] = [];
        
        while ($row = $result->fetch_assoc()) {
            $response['notifications'][] = $row;
        }
        $stmt->close();
        break;
        
    case 'POST':
        // Create notification (admin/manager only) or mark as read
        if (isset($_POST['mark_read'])) {
            // Mark as read
            $notification_id = intval($_POST['notification_id']);
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = TRUE 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $notification_id, $user_id);
        } else if (hasAccess(['admin', 'manager'])) {
            // Create notification
            $target_user_id = $_POST['user_id'] ?? 0;
            $title = $_POST['title'];
            $message = $_POST['message'];
            $type = $_POST['type'] ?? 'info';
            
            if ($target_user_id > 0) {
                // Single user
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, title, message, type) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("isss", $target_user_id, $title, $message, $type);
            } else {
                // All users
                $users_stmt = $conn->prepare("SELECT id FROM users");
                $users_stmt->execute();
                $users_result = $users_stmt->get_result();
                
                $response['success'] = true;
                $response['sent_count'] = 0;
                
                while ($user = $users_result->fetch_assoc()) {
                    $stmt = $conn->prepare("
                        INSERT INTO notifications (user_id, title, message, type) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->bind_param("isss", $user['id'], $title, $message, $type);
                    if ($stmt->execute()) {
                        $response['sent_count']++;
                    }
                    $stmt->close();
                }
                $users_stmt->close();
                break;
            }
        } else {
            $response['message'] = 'Access denied';
            break;
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = isset($_POST['mark_read']) ? 'Notification marked as read' : 'Notification sent';
        } else {
            $response['message'] = 'Error processing notification';
        }
        
        if (isset($stmt)) $stmt->close();
        break;
        
    case 'DELETE':
        // Delete notification
        parse_str(file_get_contents("php://input"), $delete_vars);
        $notification_id = $delete_vars['id'];
        
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Notification deleted';
        } else {
            $response['message'] = 'Error deleting notification';
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);
?>