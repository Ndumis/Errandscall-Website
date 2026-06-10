<?php
include('../config/database.php');
include('../includes/auth-check.php');

header('Content-Type: application/json');

if (!isWorker()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$conn = getDBConnection();

// Get assigned services count
$assigned_stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE assigned_to = ?");
$assigned_stmt->bind_param("i", $user_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();
$assigned_count = $assigned_result->fetch_assoc()['count'];
$assigned_stmt->close();

// Get in-progress services count
$in_progress_stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE assigned_to = ? AND status = 'in_progress'");
$in_progress_stmt->bind_param("i", $user_id);
$in_progress_stmt->execute();
$in_progress_result = $in_progress_stmt->get_result();
$in_progress_count = $in_progress_result->fetch_assoc()['count'];
$in_progress_stmt->close();

// Get completed services count
$completed_stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE assigned_to = ? AND status = 'completed'");
$completed_stmt->bind_param("i", $user_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();
$completed_count = $completed_result->fetch_assoc()['count'];
$completed_stmt->close();

// Get available services count
$available_stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE assigned_to IS NULL AND status = 'pending'");
$available_stmt->execute();
$available_result = $available_stmt->get_result();
$available_count = $available_result->fetch_assoc()['count'];
$available_stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'statistics' => [
        'assigned' => $assigned_count,
        'in_progress' => $in_progress_count,
        'completed' => $completed_count,
        'available' => $available_count
    ]
]);
?>