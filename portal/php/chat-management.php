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
        if (isset($_GET['service_id'])) {
            getChatMessages($conn, $user_id);
        } else if (isset($_GET['start_chat'])) {
            startChatSession($conn, $user_id);
        } else {
            getChatSessions($conn, $user_id);
        }
        break;
        
    case 'POST':
        if (isset($_POST['action']) && $_POST['action'] === 'toggle_session') {
            toggleChatSession($conn, $user_id);
        } else {
            sendChatMessage($conn, $user_id);
        }
        break;
        
    case 'PUT':
        markMessagesAsRead($conn, $user_id);
        break;
        
    case 'DELETE':
        deleteChatMessage($conn, $user_id);
        break;
}

$conn->close();
echo json_encode($response);

function getChatSessions($conn, $user_id) {
    global $response;
    
    if (hasAccess(['admin', 'manager'])) {
        $stmt = $conn->prepare("
            SELECT 
                cs.*,
                s.service_type,
                s.status as service_status,
                u_customer.fullname as customer_name,
                u_worker.fullname as worker_name,
                (SELECT COUNT(*) FROM chat_messages cm WHERE cm.chat_session_id = cs.id AND cm.receiver_id = ? AND cm.is_read = FALSE) as unread_count,
                (SELECT message FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message_time
            FROM chat_sessions cs
            JOIN services s ON cs.service_id = s.id
            JOIN users u_customer ON cs.customer_id = u_customer.id
            JOIN users u_worker ON cs.worker_id = u_worker.id
            WHERE cs.is_active = TRUE
            ORDER BY cs.last_message_at DESC
        ");
        $stmt->bind_param("i", $user_id);
    } else if (hasAccess(['worker'])) {
        $stmt = $conn->prepare("
            SELECT 
                cs.*,
                s.service_type,
                s.status as service_status,
                u_customer.fullname as customer_name,
                (SELECT COUNT(*) FROM chat_messages cm WHERE cm.chat_session_id = cs.id AND cm.receiver_id = ? AND cm.is_read = FALSE) as unread_count,
                (SELECT message FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message_time
            FROM chat_sessions cs
            JOIN services s ON cs.service_id = s.id
            JOIN users u_customer ON cs.customer_id = u_customer.id
            WHERE cs.worker_id = ? AND cs.is_active = TRUE
            ORDER BY cs.last_message_at DESC
        ");
        $stmt->bind_param("ii", $user_id, $user_id);
    } else {
        // Customer view
        $stmt = $conn->prepare("
            SELECT 
                cs.*,
                s.service_type,
                s.status as service_status,
                u_worker.fullname as worker_name,
                (SELECT COUNT(*) FROM chat_messages cm WHERE cm.chat_session_id = cs.id AND cm.receiver_id = ? AND cm.is_read = FALSE) as unread_count,
                (SELECT message FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message_time
            FROM chat_sessions cs
            JOIN services s ON cs.service_id = s.id
            JOIN users u_worker ON cs.worker_id = u_worker.id
            WHERE cs.customer_id = ? AND cs.is_active = TRUE
            ORDER BY cs.last_message_at DESC
        ");
        $stmt->bind_param("ii", $user_id, $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['success'] = true;
    $response['sessions'] = [];
    
    while ($row = $result->fetch_assoc()) {
        $response['sessions'][] = $row;
    }
    $stmt->close();
}

function startChatSession($conn, $user_id) {
    global $response;
    
    $service_id = intval($_GET['start_chat']);
    
    // Verify service exists and user has access
    $verify_stmt = $conn->prepare("
        SELECT s.id, s.user_id, s.assigned_to, s.status 
        FROM services s 
        WHERE s.id = ? AND (s.user_id = ? OR s.assigned_to = ? OR ? IN (SELECT id FROM users WHERE role IN ('admin', 'manager')))
    ");
    $verify_stmt->bind_param("iiii", $service_id, $user_id, $user_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $response['message'] = 'Service not found or access denied';
        $verify_stmt->close();
        return;
    }
    
    $service = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    // Check if service is assigned and in progress
    if (empty($service['assigned_to'])) {
        $response['message'] = 'Service is not assigned to a worker yet';
        return;
    }
    
    if (!in_array($service['status'], ['assigned', 'in_progress'])) {
        $response['message'] = 'Chat is only available for assigned or in-progress services';
        return;
    }
    
    // Check if chat session already exists
    $check_stmt = $conn->prepare("SELECT id FROM chat_sessions WHERE service_id = ?");
    $check_stmt->bind_param("i", $service_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $response['message'] = 'Chat session already exists';
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    // Create new chat session
    $insert_stmt = $conn->prepare("
        INSERT INTO chat_sessions (service_id, customer_id, worker_id) 
        VALUES (?, ?, ?)
    ");
    $insert_stmt->bind_param("iii", $service_id, $service['user_id'], $service['assigned_to']);
    
    if ($insert_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Chat session started';
        $response['chat_session_id'] = $insert_stmt->insert_id;
        
        // Send welcome message
        sendWelcomeMessage($conn, $insert_stmt->insert_id, $service['user_id'], $service['assigned_to']);
    } else {
        $response['message'] = 'Error starting chat session';
    }
    $insert_stmt->close();
}

function sendWelcomeMessage($conn, $chat_session_id, $customer_id, $worker_id) {
    $welcome_message = "Hello! This chat is for discussing your service. How can I help you today?";
    
    $stmt = $conn->prepare("
        INSERT INTO chat_messages (chat_session_id, sender_id, receiver_id, message) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $chat_session_id, $worker_id, $customer_id, $welcome_message);
    $stmt->execute();
    $stmt->close();
    
    // Update last message time
    updateLastMessageTime($conn, $chat_session_id);
}

function getChatMessages($conn, $user_id) {
    global $response;
    
    $chat_session_id = intval($_GET['service_id']);
    
    // Verify user has access to this chat session
    $verify_stmt = $conn->prepare("
        SELECT cs.id FROM chat_sessions cs 
        WHERE cs.id = ? AND (cs.customer_id = ? OR cs.worker_id = ? OR ? IN (SELECT id FROM users WHERE role IN ('admin', 'manager')))
    ");
    $verify_stmt->bind_param("iiii", $chat_session_id, $user_id, $user_id, $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        $response['message'] = 'Access denied to this chat';
        $verify_stmt->close();
        return;
    }
    $verify_stmt->close();
    
    // Get messages
    $stmt = $conn->prepare("
        SELECT 
            cm.*,
            u.fullname as sender_name,
            u.role as sender_role,
            CASE WHEN cm.sender_id = ? THEN 1 ELSE 0 END as is_own_message
        FROM chat_messages cm
        JOIN users u ON cm.sender_id = u.id
        WHERE cm.chat_session_id = ?
        ORDER BY cm.created_at ASC
    ");
    $stmt->bind_param("ii", $user_id, $chat_session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['success'] = true;
    $response['messages'] = [];
    
    while ($row = $result->fetch_assoc()) {
        $response['messages'][] = $row;
    }
    $stmt->close();
    
    // Mark messages as read
    markMessagesAsRead($conn, $user_id, $chat_session_id);
}

function sendChatMessage($conn, $user_id) {
    global $response;
    
    $chat_session_id = intval($_POST['chat_session_id']);
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        $response['message'] = 'Message cannot be empty';
        return;
    }
    
    // Verify user has access to this chat session and get receiver
    $verify_stmt = $conn->prepare("
        SELECT 
            cs.customer_id, 
            cs.worker_id,
            CASE 
                WHEN ? = cs.customer_id THEN cs.worker_id
                ELSE cs.customer_id
            END as receiver_id
        FROM chat_sessions cs 
        WHERE cs.id = ? AND (cs.customer_id = ? OR cs.worker_id = ?)
    ");
    $verify_stmt->bind_param("iiii", $user_id, $chat_session_id, $user_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $response['message'] = 'Access denied to this chat';
        $verify_stmt->close();
        return;
    }
    
    $chat_session = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    // Insert message
    $stmt = $conn->prepare("
        INSERT INTO chat_messages (chat_session_id, sender_id, receiver_id, message) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $chat_session_id, $user_id, $chat_session['receiver_id'], $message);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Message sent';
        $response['message_id'] = $stmt->insert_id;
        
        // Update last message time
        updateLastMessageTime($conn, $chat_session_id);
        
        // Send notification to receiver
        sendChatNotification($conn, $chat_session['receiver_id'], $user_id, $message);
    } else {
        $response['message'] = 'Error sending message';
    }
    $stmt->close();
}

function updateLastMessageTime($conn, $chat_session_id) {
    $stmt = $conn->prepare("
        UPDATE chat_sessions 
        SET last_message_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $chat_session_id);
    $stmt->execute();
    $stmt->close();
}

function markMessagesAsRead($conn, $user_id, $chat_session_id = null) {
    if ($chat_session_id) {
        $stmt = $conn->prepare("
            UPDATE chat_messages 
            SET is_read = TRUE 
            WHERE chat_session_id = ? AND receiver_id = ? AND is_read = FALSE
        ");
        $stmt->bind_param("ii", $chat_session_id, $user_id);
    } else {
        // Mark all unread messages as read
        $stmt = $conn->prepare("
            UPDATE chat_messages 
            SET is_read = TRUE 
            WHERE receiver_id = ? AND is_read = FALSE
        ");
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $stmt->close();
}

function sendChatNotification($conn, $receiver_id, $sender_id, $message) {
    // Get sender name
    $sender_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $sender_stmt->bind_param("i", $sender_id);
    $sender_stmt->execute();
    $sender_result = $sender_stmt->get_result();
    $sender_name = $sender_result->fetch_assoc()['fullname'];
    $sender_stmt->close();
    
    // Create notification
    $notification_stmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message, type, related_entity_type) 
        VALUES (?, 'New Chat Message', ?, 'info', 'service')
    ");
    $preview_message = substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '');
    $notification_message = "New message from {$sender_name}: {$preview_message}";
    $notification_stmt->bind_param("is", $receiver_id, $notification_message);
    $notification_stmt->execute();
    $notification_stmt->close();
    
    // Here you could also integrate with email notifications
    // sendEmailNotification($receiver_id, $sender_name, $message);
}

function toggleChatSession($conn, $user_id) {
    global $response;

    if (!hasAccess(['admin', 'manager'])) {
        $response['message'] = 'Access denied';
        return;
    }

    $chat_session_id = intval($_POST['session_id']);

    $stmt = $conn->prepare("SELECT is_active FROM chat_sessions WHERE id = ?");
    $stmt->bind_param("i", $chat_session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'Chat session not found';
        $stmt->close();
        return;
    }

    $session = $result->fetch_assoc();
    $stmt->close();

    $new_status = $session['is_active'] ? 0 : 1;

    $update_stmt = $conn->prepare("UPDATE chat_sessions SET is_active = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $new_status, $chat_session_id);

    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $new_status ? 'Conversation reopened' : 'Conversation closed';
        $response['is_active'] = $new_status;
    } else {
        $response['message'] = 'Error updating chat session';
    }
    $update_stmt->close();
}

function deleteChatMessage($conn, $user_id) {
    global $response;
    
    parse_str(file_get_contents("php://input"), $delete_vars);
    $message_id = $delete_vars['id'];
    
    if (hasAccess(['admin', 'manager'])) {
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE id = ? AND sender_id = ?");
        $stmt->bind_param("ii", $message_id, $user_id);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Message deleted';
    } else {
        $response['message'] = 'Error deleting message';
    }
    $stmt->close();
}
?>