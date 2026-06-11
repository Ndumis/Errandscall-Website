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
$worker_id = $_GET['worker_id'] ?? '';

$sql = "SELECT u.fullname,
               COUNT(s.id) as total_assignments,
               SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed,
               AVG(CASE WHEN s.status = 'completed' THEN TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) ELSE NULL END) as avg_time,
               SUM(CASE WHEN s.status = 'completed' AND TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at) <= 24 THEN 1 ELSE 0 END) as on_time
        FROM users u
        LEFT JOIN services s ON u.id = s.assigned_to AND DATE(s.created_at) BETWEEN ? AND ?
        WHERE u.role = 'worker'";

$params = [$start_date, $end_date];
$types = "ss";

if (!empty($worker_id)) {
    $sql .= " AND u.id = ?";
    $params[] = $worker_id;
    $types .= "i";
}

$sql .= " GROUP BY u.id, u.fullname ORDER BY completed DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="performance-report-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Worker', 'Total Assignments', 'Completed', 'Completion Rate (%)', 'Avg Time (Hours)', 'On Time Rate (%)', 'Performance Score']);

while ($row = $result->fetch_assoc()) {
    $completion_rate = $row['total_assignments'] > 0
        ? round(($row['completed'] / $row['total_assignments']) * 100, 1)
        : 0;

    $on_time_rate = $row['completed'] > 0
        ? round(($row['on_time'] / $row['completed']) * 100, 1)
        : 0;

    $avg_time = round($row['avg_time'] ?? 0, 1);

    $performance_score = round(min(100, ($completion_rate * 0.4) + ((100 - min($avg_time, 48) * 2.08) * 0.3) + ($on_time_rate * 0.3)));

    fputcsv($output, [
        $row['fullname'],
        $row['total_assignments'],
        $row['completed'],
        $completion_rate,
        $avg_time,
        $on_time_rate,
        $performance_score
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
