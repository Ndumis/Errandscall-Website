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
        // Read ratings
        if (isset($_GET['id'])) {
            // Single rating
            $stmt = $conn->prepare("
                SELECT sr.*, s.service_type, u_sender.fullname as user_name, u_worker.fullname as worker_name
                FROM service_ratings sr
                JOIN services s ON sr.service_id = s.id
                JOIN users u_sender ON sr.user_id = u_sender.id
                JOIN users u_worker ON sr.worker_id = u_worker.id
                WHERE sr.id = ? AND (sr.user_id = ? OR ? IN (SELECT id FROM users WHERE role IN ('admin', 'manager')))
            ");
            $stmt->bind_param("iii", $_GET['id'], $user_id, $user_id);
        } else {
            // Multiple ratings
            if (hasAccess(['admin', 'manager'])) {
                $stmt = $conn->prepare("
                    SELECT sr.*, s.service_type, u_sender.fullname as user_name, u_worker.fullname as worker_name
                    FROM service_ratings sr
                    JOIN services s ON sr.service_id = s.id
                    JOIN users u_sender ON sr.user_id = u_sender.id
                    JOIN users u_worker ON sr.worker_id = u_worker.id
                    ORDER BY sr.created_at DESC
                    LIMIT 100
                ");
            } else if (hasAccess(['worker'])) {
                $stmt = $conn->prepare("
                    SELECT sr.*, s.service_type, u_sender.fullname as user_name
                    FROM service_ratings sr
                    JOIN services s ON sr.service_id = s.id
                    JOIN users u_sender ON sr.user_id = u_sender.id
                    WHERE sr.worker_id = ?
                    ORDER BY sr.created_at DESC
                ");
                $stmt->bind_param("i", $user_id);
            } else {
                $stmt = $conn->prepare("
                    SELECT sr.*, s.service_type, u_worker.fullname as worker_name
                    FROM service_ratings sr
                    JOIN services s ON sr.service_id = s.id
                    JOIN users u_worker ON sr.worker_id = u_worker.id
                    WHERE sr.user_id = ?
                    ORDER BY sr.created_at DESC
                ");
                $stmt->bind_param("i", $user_id);
            }
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $response['success'] = true;
        $response['ratings'] = [];
        
        while ($row = $result->fetch_assoc()) {
            $response['ratings'][] = $row;
        }
        $stmt->close();
        break;
        
    case 'POST':
        // Create rating
        $service_id = intval($_POST['service_id']);
        $rating = intval($_POST['rating']);
        $comment = $_POST['comment'] ?? '';
        
        // Verify service exists and is completed
        $verify_stmt = $conn->prepare("
            SELECT id, assigned_to FROM services 
            WHERE id = ? AND user_id = ? AND status = 'completed'
        ");
        $verify_stmt->bind_param("ii", $service_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows > 0) {
            $service = $verify_result->fetch_assoc();
            $worker_id = $service['assigned_to'];
            
            // Check if already rated
            $check_stmt = $conn->prepare("SELECT id FROM service_ratings WHERE service_id = ? AND user_id = ?");
            $check_stmt->bind_param("ii", $service_id, $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows === 0) {
                $insert_stmt = $conn->prepare("
                    INSERT INTO service_ratings (service_id, user_id, worker_id, rating, comment) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insert_stmt->bind_param("iiiis", $service_id, $user_id, $worker_id, $rating, $comment);
                
                if ($insert_stmt->execute()) {
                    updateWorkerRating($conn, $worker_id);
                    $response['success'] = true;
                    $response['message'] = 'Rating submitted successfully';
                }
                $insert_stmt->close();
            } else {
                $response['message'] = 'You have already rated this service';
            }
            $check_stmt->close();
        } else {
            $response['message'] = 'Service not found or not completed';
        }
        $verify_stmt->close();
        break;
        
    case 'PUT':
        // Update rating (only comment)
        parse_str(file_get_contents("php://input"), $put_vars);
        $id = $put_vars['id'];
        $comment = $put_vars['comment'] ?? '';
        
        $stmt = $conn->prepare("
            UPDATE service_ratings SET comment = ? 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("sii", $comment, $id, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Rating updated';
        } else {
            $response['message'] = 'Error updating rating';
        }
        $stmt->close();
        break;
        
    case 'DELETE':
        // Delete rating (admin/manager only or own rating)
        $delete_vars = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = $delete_vars['id'] ?? 0;
        
        if (hasAccess(['admin', 'manager'])) {
            $stmt = $conn->prepare("DELETE FROM service_ratings WHERE id = ?");
            $stmt->bind_param("i", $id);
        } else {
            $stmt = $conn->prepare("DELETE FROM service_ratings WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Rating deleted';
        } else {
            $response['message'] = 'Error deleting rating';
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);

function updateWorkerRating($conn, $worker_id) {
    $stmt = $conn->prepare("
        INSERT INTO worker_ratings_summary (worker_id, total_ratings, average_rating) 
        SELECT 
            worker_id,
            COUNT(*) as total_ratings,
            ROUND(AVG(rating), 2) as average_rating
        FROM service_ratings 
        WHERE worker_id = ?
        GROUP BY worker_id
        ON DUPLICATE KEY UPDATE 
            total_ratings = VALUES(total_ratings),
            average_rating = VALUES(average_rating)
    ");
    $stmt->bind_param("i", $worker_id);
    $stmt->execute();
    $stmt->close();
}
?>