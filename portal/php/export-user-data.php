<?php
include('../config/database.php');
include('../includes/auth-check.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$format = $_GET['format'] ?? 'json';
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id_number, fullname, email, phone, dob, role, status, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();
unset($settings['id'], $settings['user_id']);

$vehicles = [];
$stmt = $conn->prepare("SELECT make, model, year, license_plate, color, disc_expiry, license_expiry, created_at FROM vehicles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $vehicles[] = $row;
$stmt->close();

$services = [];
$stmt = $conn->prepare("SELECT service_type, description, status, priority, preferred_date, created_at FROM services WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $services[] = $row;
$stmt->close();

$conn->close();

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="my-data-' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Profile']);
    fputcsv($output, array_keys($profile));
    fputcsv($output, array_values($profile));
    fputcsv($output, []);

    fputcsv($output, ['Settings']);
    if (!empty($settings)) {
        fputcsv($output, array_keys($settings));
        fputcsv($output, array_values($settings));
    }
    fputcsv($output, []);

    fputcsv($output, ['Vehicles']);
    if (!empty($vehicles)) {
        fputcsv($output, array_keys($vehicles[0]));
        foreach ($vehicles as $vehicle) fputcsv($output, array_values($vehicle));
    } else {
        fputcsv($output, ['No vehicles']);
    }
    fputcsv($output, []);

    fputcsv($output, ['Services']);
    if (!empty($services)) {
        fputcsv($output, array_keys($services[0]));
        foreach ($services as $service) fputcsv($output, array_values($service));
    } else {
        fputcsv($output, ['No services']);
    }

    fclose($output);
} else {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="my-data-' . date('Y-m-d') . '.json"');

    echo json_encode([
        'profile' => $profile,
        'settings' => $settings,
        'vehicles' => $vehicles,
        'services' => $services,
        'exported_at' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

exit;
