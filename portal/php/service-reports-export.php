<?php
include('../config/database.php');
include('../includes/auth-check.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getDBConnection();

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$service_type = $_GET['service_type'] ?? '';
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';

$sql = "SELECT s.*, u.fullname as customer_name, v.license_plate, cm.name as make_name, cmodel.name as model_name
        FROM services s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN vehicles v ON s.vehicle_id = v.id
        LEFT JOIN car_makes cm ON v.make = cm.id
        LEFT JOIN car_models cmodel ON v.model = cmodel.id
        WHERE DATE(s.created_at) BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

if (!empty($service_type)) {
    $sql .= " AND s.service_type = ?";
    $params[] = $service_type;
    $types .= "s";
}

if (!empty($status)) {
    $sql .= " AND s.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($priority)) {
    $sql .= " AND s.priority = ?";
    $params[] = $priority;
    $types .= "s";
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="service-report-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Service ID', 'Type', 'Customer', 'Vehicle', 'Status', 'Priority', 'Created Date', 'Completion Time']);

while ($row = $result->fetch_assoc()) {
    $completion_time = $row['status'] == 'completed'
        ? round((strtotime($row['updated_at']) - strtotime($row['created_at'])) / 3600, 1) . 'h'
        : 'N/A';

    fputcsv($output, [
        '#' . $row['id'],
        $row['service_type'],
        $row['customer_name'],
        trim($row['make_name'] . ' ' . $row['model_name'] . ' (' . $row['license_plate'] . ')'),
        ucfirst(str_replace('_', ' ', $row['status'])),
        $row['priority'],
        date('M j, Y', strtotime($row['created_at'])),
        $completion_time
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
