<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id']);
    $rating = intval($_POST['rating']);
    $comment = $_POST['comment'] ?? '';
    
    $conn = getDBConnection();
    
    // Verify user can rate this service
    $verify_stmt = $conn->prepare("
        SELECT s.id, s.assigned_to 
        FROM services s 
        WHERE s.id = ? AND s.user_id = ? AND s.status = 'completed'
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
            // Insert rating
            $insert_stmt = $conn->prepare("
                INSERT INTO service_ratings (service_id, user_id, worker_id, rating, comment) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_stmt->bind_param("iiiis", $service_id, $user_id, $worker_id, $rating, $comment);
            
            if ($insert_stmt->execute()) {
                // Update worker rating summary
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
    $conn->close();
}

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