<?php
// get-user-vehicles.php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();

    // Join with car_makes table to get the make name
    $stmt = $conn->prepare("
        SELECT 
            v.id,
            v.year,
            v.license_plate,
            v.color,
            v.vin,
            cm.name as make_name,
            v.model as model_name
        FROM vehicles v
        LEFT JOIN car_makes cm ON v.make = cm.id
        WHERE v.user_id = ? 
        ORDER BY v.year DESC, cm.name, v.model
    ");
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicles = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'vehicles' => $vehicles
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>