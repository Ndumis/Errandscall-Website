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
$make = $_GET['make'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? '';
$expiry_status = $_GET['expiry_status'] ?? '';

$sql = "SELECT v.*, u.fullname as owner_name, cm.name as make_name, cmodel.name as model_name,
               cmodel.vehicle_type, DATEDIFF(v.disc_expiry, CURDATE()) as days_until_expiry
        FROM vehicles v
        JOIN users u ON v.user_id = u.id
        JOIN car_makes cm ON v.make = cm.id
        JOIN car_models cmodel ON v.model = cmodel.id
        WHERE DATE(v.created_at) BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

if (!empty($make)) {
    $sql .= " AND v.make = ?";
    $params[] = $make;
    $types .= "i";
}

if (!empty($vehicle_type)) {
    $sql .= " AND cmodel.vehicle_type = ?";
    $params[] = $vehicle_type;
    $types .= "s";
}

if (!empty($expiry_status)) {
    switch ($expiry_status) {
        case 'expired':
            $sql .= " AND v.disc_expiry < CURDATE()";
            break;
        case 'expiring_30':
            $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'expiring_60':
            $sql .= " AND v.disc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)";
            break;
        case 'valid':
            $sql .= " AND v.disc_expiry > CURDATE()";
            break;
    }
}

$sql .= " ORDER BY v.disc_expiry ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="vehicle-report-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['License Plate', 'Make & Model', 'Year', 'Color', 'Owner', 'Disc Expiry', 'License Expiry', 'Status', 'Registration Date']);

while ($row = $result->fetch_assoc()) {
    if ($row['days_until_expiry'] < 0) {
        $expiry_label = 'Expired';
    } elseif ($row['days_until_expiry'] <= 30) {
        $expiry_label = 'Expiring Soon';
    } else {
        $expiry_label = 'Valid';
    }

    fputcsv($output, [
        $row['license_plate'],
        trim($row['make_name'] . ' ' . $row['model_name']),
        $row['year'],
        $row['color'],
        $row['owner_name'],
        date('M j, Y', strtotime($row['disc_expiry'])),
        date('M j, Y', strtotime($row['license_expiry'])),
        $expiry_label,
        date('M j, Y', strtotime($row['created_at']))
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
