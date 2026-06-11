<?php
include('../config/database.php');
include('../includes/auth-check.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getDBConnection();

$start_date = $_GET['start_date'] ?? date('Y-01-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$role = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT u.*,
               COUNT(DISTINCT v.id) as vehicle_count,
               COUNT(DISTINCT s.id) as service_count,
               MAX(al.created_at) as last_activity
        FROM users u
        LEFT JOIN vehicles v ON u.id = v.user_id
        LEFT JOIN services s ON u.id = s.user_id
        LEFT JOIN activity_log al ON u.id = al.user_id
        WHERE DATE(u.created_at) BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

if (!empty($role)) {
    $sql .= " AND u.role = ?";
    $params[] = $role;
    $types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND u.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="user-report-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID Number', 'Full Name', 'Email', 'Phone', 'Role', 'Status', 'Vehicles', 'Services', 'Registration Date', 'Last Login']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id_number'],
        $row['fullname'],
        $row['email'],
        $row['phone'],
        ucfirst($row['role']),
        ucfirst($row['status']),
        $row['vehicle_count'],
        $row['service_count'],
        date('M j, Y', strtotime($row['created_at'])),
        $row['last_activity'] ? date('M j, Y H:i', strtotime($row['last_activity'])) : 'Never'
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
