<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || !hasAccess(['admin', 'manager'])) {
    echo json_encode($response);
    exit;
}

$conn = getDBConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest($conn);
        break;
    case 'POST':
        handlePostRequest($conn);
        break;
    default:
        $response['message'] = 'Invalid request method';
}

$conn->close();
echo json_encode($response);

function handleGetRequest($conn) {
    global $response;
    
    $report_type = $_GET['report_type'] ?? 'overview';
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');

    try {
        switch ($report_type) {
            case 'services':
                $data = getServicesReport($conn, $start_date, $end_date);
                break;
            case 'vehicles':
                $data = getVehiclesReport($conn, $start_date, $end_date);
                break;
            case 'users':
                $data = getUsersReport($conn, $start_date, $end_date);
                break;
            case 'performance':
                $data = getPerformanceReport($conn, $start_date, $end_date);
                break;
            default:
                $data = getOverviewReport($conn, $start_date, $end_date);
        }

        $response['success'] = true;
        $response['data'] = $data;
    } catch (Exception $e) {
        $response['message'] = 'Error generating report: ' . $e->getMessage();
    }
}

function handlePostRequest($conn) {
    global $response;
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save':
            saveReport($conn);
            break;
        case 'export':
            exportReport($conn);
            break;
        default:
            $response['message'] = 'Invalid action';
    }
}

function getOverviewReport($conn, $start_date, $end_date) {
    $data = [];
    
    // Basic stats
    $data['stats'] = getBasicStats($conn, $start_date, $end_date);
    
    // Charts data
    $data['charts'] = [
        'servicesTrend' => getServicesTrend($conn, $start_date, $end_date),
        'serviceTypes' => getServiceTypesDistribution($conn, $start_date, $end_date)
    ];
    
    // Detailed data
    $data['detailed'] = getDetailedOverview($conn, $start_date, $end_date);
    
    return $data;
}

function getDetailedOverview($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.service_type,
            s.status,
            s.created_at,
            s.updated_at,
            u.fullname as customer_name,
            worker.fullname as assigned_worker
        FROM services s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN users worker ON s.assigned_to = worker.id
        WHERE DATE(s.created_at) BETWEEN ? AND ?
        ORDER BY s.created_at DESC
        LIMIT 50
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detailed_data = [];
    while ($row = $result->fetch_assoc()) {
        $detailed_data[] = $row;
    }
    $stmt->close();
    
    return $detailed_data;
}

function getBasicStats($conn, $start_date, $end_date) {
    $stats = [];
    
    // Total services
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_services,
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_services,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_services
        FROM services 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $services_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $stats = array_merge($stats, $services_stats);
    
    // Revenue (placeholder - add payments table later)
    $stats['revenue'] = 0;
    
    // Average completion time
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_completion
        FROM services 
        WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $completion_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $stats['avg_completion'] = round($completion_stats['avg_completion'] ?? 0, 1);
    
    return $stats;
}

function getServicesTrend($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM services 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = date('M j', strtotime($row['date']));
        $data[] = $row['count'];
    }
    $stmt->close();
    
    return ['labels' => $labels, 'data' => $data];
}

function getServiceTypesDistribution($conn, $start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT service_type, COUNT(*) as count
        FROM services 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY service_type
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['service_type'];
        $data[] = $row['count'];
    }
    $stmt->close();
    
    return ['labels' => $labels, 'data' => $data];
}

function getServicesReport($conn, $start_date, $end_date) {
    $data = [];
    
    // Basic stats
    $data['stats'] = getBasicStats($conn, $start_date, $end_date);
    
    // Service type breakdown
    $stmt = $conn->prepare("
        SELECT 
            service_type,
            status,
            COUNT(*) as count,
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
    
    $data['detailed'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['detailed'][] = $row;
    }
    $stmt->close();
    
    return $data;
}

function getVehiclesReport($conn, $start_date, $end_date) {
    $data = [];
    
    // Vehicle statistics
    $stmt = $conn->prepare("
        SELECT 
            cm.name as make,
            cmodel.name as model,
            COUNT(*) as count,
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
    
    $data['detailed'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['detailed'][] = $row;
    }
    $stmt->close();
    
    return $data;
}

function getUsersReport($conn, $start_date, $end_date) {
    $data = [];
    
    // User statistics
    $stmt = $conn->prepare("
        SELECT 
            role,
            COUNT(*) as total_users,
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
    
    $data['detailed'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['detailed'][] = $row;
    }
    $stmt->close();
    
    return $data;
}

function getPerformanceReport($conn, $start_date, $end_date) {
    $data = [];
    
    // Worker performance
    $stmt = $conn->prepare("
        SELECT 
            u.fullname,
            u.role,
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
    
    $data['detailed'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['detailed'][] = $row;
    }
    $stmt->close();
    
    return $data;
}

function saveReport($conn) {
    global $response;
    
    $name = $_POST['name'] ?? '';
    $config = json_encode($_POST['config'] ?? []);
    $user_id = $_SESSION['user_id'];
    
    if (empty($name)) {
        $response['message'] = 'Report name is required';
        return;
    }
    
    $stmt = $conn->prepare("
        INSERT INTO saved_reports (name, config, user_id) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("ssi", $name, $config, $user_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Report saved successfully';
        $response['report_id'] = $stmt->insert_id;
    } else {
        $response['message'] = 'Error saving report: ' . $stmt->error;
    }
    $stmt->close();
}

function exportReport($conn) {
    global $response;
    
    // This is a simplified export - in production, you'd use libraries like TCPDF or PhpSpreadsheet
    $format = $_POST['format'] ?? 'pdf';
    $report_type = $_POST['report_type'] ?? 'overview';
    
    // Generate file (placeholder implementation)
    $filename = "report_{$report_type}_" . date('Y-m-d') . ".{$format}";
    $filepath = "../exports/{$filename}";
    
    // Ensure exports directory exists
    if (!is_dir('../exports')) {
        mkdir('../exports', 0755, true);
    }
    
    // In a real implementation, you would generate the actual file content here
    file_put_contents($filepath, "Export content for {$report_type} report");
    
    $response['success'] = true;
    $response['file_url'] = $filepath;
    $response['file_name'] = $filename;
    $response['message'] = 'Report exported successfully';
}