<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $make = trim($_POST['make_id']);
    $model = trim($_POST['model_id']);
    $year = intval($_POST['year']);
    $license_plate = trim($_POST['license_plate']);
    $vin = trim($_POST['vin']);
    $color = trim($_POST['color']);
    $disc_expiry = $_POST['disc_expiry'];
    $license_expiry = $_POST['license_expiry'];
    
    // Validation
    if (empty($make) || empty($model) || empty($license_plate) || empty($disc_expiry) || empty($license_expiry)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Check for connection error
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        echo json_encode($response);
        exit;
    }
    
    // Check if license plate already exists
    $check_stmt = $conn->prepare("SELECT id FROM vehicles WHERE license_plate = ?");
    if (!$check_stmt) {
        $response['message'] = 'Prepare failed: ' . $conn->error;
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $check_stmt->bind_param("s", $license_plate);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $response['message'] = 'License plate already exists.';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Handle file upload - Store filename only, not full path
    $image_filename = null;
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION);
        $filename = 'vehicle_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            $response['message'] = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
            echo json_encode($response);
            exit;
        }
        
        // Validate file size (5MB)
        if ($_FILES['vehicle_image']['size'] > 5 * 1024 * 1024) {
            $response['message'] = 'File size must be less than 5MB.';
            echo json_encode($response);
            exit;
        }
        
        if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $target_path)) {
            $image_filename = $filename; // Store only filename
        }
    }
    
    // Insert vehicle - match your table structure
    // Since your table doesn't have image_path column, we'll skip it
    // If you want to store images, you'll need to add the column or modify the table
    $sql = "INSERT INTO vehicles (user_id, make, model, year, license_plate, vin, color, disc_expiry, license_expiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $response['message'] = 'Prepare failed: ' . $conn->error;
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("ississsss", $user_id, $make, $model, $year, $license_plate, $vin, $color, $disc_expiry, $license_expiry);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Vehicle added successfully!';
        
        // If you uploaded an image and want to store it, you have two options:
        // 1. Add image_path column to your vehicles table
        // 2. Create a separate vehicle_images table
        if ($image_filename) {
            // Option: Store image reference separately or update your table structure
            error_log("Image uploaded but not stored in database: " . $image_filename);
        }
    } else {
        $response['message'] = 'Error adding vehicle: ' . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>