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
$format = $_POST['format'] ?? 'json';
$conn = getDBConnection();

try {
    // In a real application, you would:
    // 1. Generate the export file
    // 2. Store it on the server
    // 3. Send email with download link
    // 4. Clean up old files
    
    // For now, we'll simulate the process
    $export_data = [
        'user_info' => [
            'fullname' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ],
        'export_time' => date('Y-m-d H:i:s'),
        'format' => $format
    ];
    
    // Simulate processing time
    sleep(2);
    
    $response['success'] = true;
    $response['message'] = 'Export started successfully. You will receive an email when your data is ready for download.';
    $response['export_id'] = uniqid('export_');
    
} catch (Exception $e) {
    $response['message'] = 'Error starting export: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>