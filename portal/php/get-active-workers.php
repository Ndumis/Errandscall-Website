<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();

try {
    // Get active workers with their latest location and current service info
    $sql = "
        SELECT 
            u.id,
            u.fullname,
            u.role,
            u.email,
            wos.is_online,
            wos.last_seen,
            wl.latitude,
            wl.longitude,
            wl.timestamp as location_timestamp,
            wl.battery_level,
            wl.is_moving,
            s.id as service_id,
            s.service_type,
            s.status as service_status
        FROM users u
        LEFT JOIN worker_online_status wos ON u.id = wos.worker_id
        LEFT JOIN worker_locations wl ON u.id = wl.worker_id
        LEFT JOIN services s ON u.id = s.assigned_to AND s.status IN ('assigned', 'in_progress')
        WHERE u.role IN ('worker', 'manager')
        AND u.status = 'active'
        ORDER BY wos.is_online DESC, wl.timestamp DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $workers = [];
    $processed_workers = [];
    
    while ($row = $result->fetch_assoc()) {
        $worker_id = $row['id'];
        
        // Only include each worker once (latest location)
        if (!isset($processed_workers[$worker_id])) {
            $worker = [
                'id' => $worker_id,
                'fullname' => $row['fullname'],
                'role' => $row['role'],
                'email' => $row['email'],
                'is_online' => (bool)$row['is_online'],
                'last_seen' => $row['last_seen'],
                'assigned_services' => 0
            ];
            
            // Add location data if available
            if ($row['latitude'] && $row['longitude']) {
                $worker['last_location'] = [
                    'latitude' => (float)$row['latitude'],
                    'longitude' => (float)$row['longitude'],
                    'timestamp' => $row['location_timestamp'],
                    'battery_level' => $row['battery_level'] !== null ? (int)$row['battery_level'] : null,
                    'is_moving' => (bool)$row['is_moving']
                ];
            }
            
            // Add current service if available
            if ($row['service_id']) {
                $worker['current_service'] = [
                    'service_id' => $row['service_id'],
                    'service_type' => $row['service_type'],
                    'status' => $row['service_status']
                ];
            }
            
            $workers[] = $worker;
            $processed_workers[$worker_id] = true;
        }
    }
    
    // Count assigned services for each worker
    $count_sql = "
        SELECT assigned_to, COUNT(*) as count 
        FROM services 
        WHERE status IN ('assigned', 'in_progress') 
        AND assigned_to IS NOT NULL 
        GROUP BY assigned_to
    ";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    
    $service_counts = [];
    while ($row = $count_result->fetch_assoc()) {
        $service_counts[$row['assigned_to']] = $row['count'];
    }
    
    // Update assigned services count
    foreach ($workers as &$worker) {
        $worker['assigned_services'] = $service_counts[$worker['id']] ?? 0;
    }
    unset($worker);

    $online_count = count(array_filter($workers, function($worker) {
        return $worker['is_online'];
    }));

    echo json_encode([
        'success' => true,
        'workers' => $workers,
        'online_count' => $online_count
    ]);
    
} catch (Exception $e) {
    error_log("Error getting active workers: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving worker data'
    ]);
}

$conn->close();
?>