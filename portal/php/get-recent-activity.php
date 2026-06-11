<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'activities' => []];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    if (isCustomer()) {
        // Get recent service updates
        $updates_sql = "SELECT su.update_text, su.created_at, u.fullname as user_name, 'Service Update' as action
                       FROM service_updates su
                       JOIN services s ON su.service_id = s.id
                       JOIN users u ON su.user_id = u.id
                       WHERE s.user_id = ?
                       ORDER BY su.created_at DESC
                       LIMIT 5";
        $updates_stmt = $conn->prepare($updates_sql);
        $updates_stmt->bind_param("i", $user_id);
        $updates_stmt->execute();
        $updates_result = $updates_stmt->get_result();

        while ($update = $updates_result->fetch_assoc()) {
            $response['activities'][] = [
                'action' => $update['action'],
                'description' => $update['update_text'],
                'created_at' => $update['created_at'],
                'user_name' => $update['user_name']
            ];
        }
        $updates_stmt->close();

        // Get vehicle additions
        $vehicles_sql = "SELECT 'Vehicle Added' as action,
                        CONCAT('Added ', make, ' ', model, ' (', license_plate, ')') as description,
                        created_at,
                        'System' as user_name
                        FROM vehicles
                        WHERE user_id = ?
                        ORDER BY created_at DESC
                        LIMIT 5";
        $vehicles_stmt = $conn->prepare($vehicles_sql);
        $vehicles_stmt->bind_param("i", $user_id);
        $vehicles_stmt->execute();
        $vehicles_result = $vehicles_stmt->get_result();

        while ($vehicle = $vehicles_result->fetch_assoc()) {
            $response['activities'][] = $vehicle;
        }
        $vehicles_stmt->close();

        // Get service requests
        $services_sql = "SELECT 'Service Requested' as action,
                        CONCAT('Requested ', service_type, ' for ',
                        (SELECT CONCAT(make, ' ', model, ' (', license_plate, ')')
                         FROM vehicles WHERE id = vehicle_id)) as description,
                        created_at,
                        'System' as user_name
                        FROM services
                        WHERE user_id = ?
                        ORDER BY created_at DESC
                        LIMIT 5";
        $services_stmt = $conn->prepare($services_sql);
        $services_stmt->bind_param("i", $user_id);
        $services_stmt->execute();
        $services_result = $services_stmt->get_result();

        while ($service = $services_result->fetch_assoc()) {
            $response['activities'][] = $service;
        }
        $services_stmt->close();

        // Sort all activities by date
        usort($response['activities'], function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Limit to 5 most recent activities
        $response['activities'] = array_slice($response['activities'], 0, 5);
    } else {
        // Staff (admin/manager/worker): show system-wide activity log
        $activity_labels = [
            'login' => 'User Login',
            'failed_login' => 'Failed Login Attempt',
            'logout' => 'User Logout'
        ];

        $log_sql = "SELECT al.activity_type, al.description, al.created_at, u.fullname as user_name
                     FROM activity_log al
                     LEFT JOIN users u ON al.user_id = u.id
                     ORDER BY al.created_at DESC
                     LIMIT 5";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->execute();
        $log_result = $log_stmt->get_result();

        while ($log = $log_result->fetch_assoc()) {
            $response['activities'][] = [
                'action' => $activity_labels[$log['activity_type']] ?? ucwords(str_replace('_', ' ', $log['activity_type'])),
                'description' => $log['description'],
                'created_at' => $log['created_at'],
                'user_name' => $log['user_name'] ?? 'Unknown User'
            ];
        }
        $log_stmt->close();
    }

    $response['success'] = true;

} catch (Exception $e) {
    error_log('Error fetching activity: ' . $e->getMessage());
    $response['message'] = 'Error fetching activity. Please try again.';
}

$conn->close();
echo json_encode($response);
?>