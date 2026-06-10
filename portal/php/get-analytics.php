<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'analytics' => []];

if (!isset($_SESSION['user_id']) || !hasAccess(['admin', 'manager'])) {
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'month'; // week, month, year

// Service analytics
$services_stmt = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        DATE(created_at) as date
    FROM services 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)
    GROUP BY status, DATE(created_at)
    ORDER BY date
");
$services_stmt->execute();
$services_result = $services_stmt->get_result();

$service_data = [];
while ($row = $services_result->fetch_assoc()) {
    $service_data[$row['date']][$row['status']] = $row['count'];
}
$response['analytics']['services'] = $service_data;

// Revenue analytics (if you have pricing)
$revenue_data = [];
$revenue_stmt = $conn->prepare("
    SELECT
        DATE(created_at) as date,
        SUM(amount) as revenue
    FROM payments
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $period)
    AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY date
");
if ($revenue_stmt) {
    $revenue_stmt->execute();
    $revenue_result = $revenue_stmt->get_result();

    while ($row = $revenue_result->fetch_assoc()) {
        $revenue_data[$row['date']] = floatval($row['revenue']);
    }
    $revenue_stmt->close();
}
$response['analytics']['revenue'] = $revenue_data;

// Worker performance
$workers_data = [];
$workers_stmt = $conn->prepare("
    SELECT
        u.id,
        u.fullname,
        COUNT(s.id) as completed_services,
        COALESCE(wrs.average_rating, 0) as avg_rating,
        COALESCE(wrs.total_ratings, 0) as total_ratings
    FROM users u
    LEFT JOIN services s ON u.id = s.assigned_to AND s.status = 'completed'
    LEFT JOIN worker_ratings_summary wrs ON u.id = wrs.worker_id
    WHERE u.role = 'worker'
    GROUP BY u.id
    ORDER BY completed_services DESC
");
if ($workers_stmt) {
    $workers_stmt->execute();
    $workers_result = $workers_stmt->get_result();

    while ($row = $workers_result->fetch_assoc()) {
        $workers_data[] = $row;
    }
    $workers_stmt->close();
}
$response['analytics']['workers'] = $workers_data;

$response['success'] = true;

$services_stmt->close();
$conn->close();

echo json_encode($response);
?>