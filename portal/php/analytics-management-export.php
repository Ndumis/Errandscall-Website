<?php
include('../config/database.php');
include('../includes/auth-check.php');

if (!hasAccess(['admin', 'manager'])) {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getDBConnection();

$report_type = $_GET['report_type'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="analytics-' . $report_type . '-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

switch ($report_type) {
    case 'services':
        fputcsv($output, ['Service Type', 'Status', 'Count', 'Completion Rate (%)', 'Avg Time (Hours)']);
        $stmt = $conn->prepare("
            SELECT service_type, status, COUNT(*) as count,
                   ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)), 1) as avg_time,
                   ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as completion_rate
            FROM services
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY service_type, status
            ORDER BY service_type, status
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['service_type'], $row['status'], $row['count'], $row['completion_rate'], $row['avg_time']]);
        }
        $stmt->close();
        break;

    case 'vehicles':
        fputcsv($output, ['Make', 'Model', 'Count', 'Avg Year', 'Expiring Soon']);
        $stmt = $conn->prepare("
            SELECT cm.name as make, cmodel.name as model, COUNT(*) as count,
                   ROUND(AVG(v.year)) as avg_year,
                   SUM(CASE WHEN v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon
            FROM vehicles v
            JOIN car_makes cm ON v.make = cm.id
            JOIN car_models cmodel ON v.model = cmodel.id
            WHERE DATE(v.created_at) BETWEEN ? AND ?
            GROUP BY cm.name, cmodel.name
            ORDER BY count DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['make'], $row['model'], $row['count'], $row['avg_year'], $row['expiring_soon']]);
        }
        $stmt->close();
        break;

    case 'users':
        fputcsv($output, ['Role', 'Total Users', 'Active Users', 'Registration Date']);
        $stmt = $conn->prepare("
            SELECT role, COUNT(*) as total_users,
                   SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                   DATE(created_at) as registration_date
            FROM users
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY role, DATE(created_at)
            ORDER BY registration_date DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['role'], $row['total_users'], $row['active_users'], $row['registration_date']]);
        }
        $stmt->close();
        break;

    case 'performance':
        fputcsv($output, ['Name', 'Role', 'Total Assignments', 'Completed', 'Avg Completion Time (Hours)', 'Completion Rate (%)']);
        $stmt = $conn->prepare("
            SELECT u.fullname, u.role,
                   COUNT(s.id) as total_assignments,
                   SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed_assignments,
                   ROUND(AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)), 1) as avg_completion_time,
                   ROUND(SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(s.id), 1) as completion_rate
            FROM users u
            LEFT JOIN services s ON u.id = s.assigned_to
            WHERE u.role IN ('worker', 'manager')
            AND (s.created_at IS NULL OR DATE(s.created_at) BETWEEN ? AND ?)
            GROUP BY u.id, u.fullname, u.role
            HAVING total_assignments > 0
            ORDER BY completion_rate DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['fullname'], $row['role'], $row['total_assignments'], $row['completed_assignments'], $row['avg_completion_time'], $row['completion_rate']]);
        }
        $stmt->close();
        break;

    default:
        fputcsv($output, ['Service Type', 'Status', 'Customer', 'Assigned Worker', 'Created Date']);
        $stmt = $conn->prepare("
            SELECT s.service_type, s.status, s.created_at,
                   u.fullname as customer_name, worker.fullname as assigned_worker
            FROM services s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN users worker ON s.assigned_to = worker.id
            WHERE DATE(s.created_at) BETWEEN ? AND ?
            ORDER BY s.created_at DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['service_type'],
                $row['status'],
                $row['customer_name'],
                $row['assigned_worker'] ?? 'Unassigned',
                date('M j, Y', strtotime($row['created_at']))
            ]);
        }
        $stmt->close();
        break;
}

fclose($output);
$conn->close();
