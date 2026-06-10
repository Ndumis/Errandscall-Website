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
    $vehicle_id = intval($_POST['vehicle_id']);
    $image_side = trim($_POST['image_side']);
    
    // Validation
    if (empty($vehicle_id) || empty($image_side)) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit;
    }
    
    // Verify vehicle belongs to user (for customers)
    if (isCustomer()) {
        $conn = getDBConnection();
        $check_stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $vehicle_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $response['message'] = 'Vehicle not found or access denied.';
            $check_stmt->close();
            $conn->close();
            echo json_encode($response);
            exit;
        }
        $check_stmt->close();
    }
    
    // Handle file upload
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        // Correct path - go up one level from php/ to portal/, then into assets/uploads/
        $upload_dir = '../assets/uploads/vehicle_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION);
        $filename = 'vehicle_' . $vehicle_id . '_' . $image_side . '_' . time() . '.' . $file_extension;
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
            $conn = getDBConnection();
            
            // Check if image for this side already exists
            $check_stmt = $conn->prepare("SELECT id FROM vehicle_images WHERE vehicle_id = ? AND image_side = ?");
            $check_stmt->bind_param("is", $vehicle_id, $image_side);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            // Store the path for web access (remove the ../ for web URLs)
            $web_path = 'assets/uploads/vehicle_images/' . $filename;
            
            if ($check_result->num_rows > 0) {
                // Update existing image
                $update_stmt = $conn->prepare("UPDATE vehicle_images SET image_path = ? WHERE vehicle_id = ? AND image_side = ?");
                $update_stmt->bind_param("sis", $web_path, $vehicle_id, $image_side);
                
                if ($update_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Image updated successfully!';
                } else {
                    $response['message'] = 'Error updating image: ' . $conn->error;
                }
                $update_stmt->close();
            } else {
                // Insert new image
                $insert_stmt = $conn->prepare("INSERT INTO vehicle_images (vehicle_id, image_path, image_side) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iss", $vehicle_id, $web_path, $image_side);
                
                if ($insert_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Image uploaded successfully!';
                } else {
                    $response['message'] = 'Error uploading image: ' . $conn->error;
                }
                $insert_stmt->close();
            }
            
            $check_stmt->close();
            $conn->close();
        } else {
            $response['message'] = 'Failed to upload image. Check directory permissions.';
        }
    } else {
        $upload_error = $_FILES['vehicle_image']['error'] ?? 'Unknown';
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $response['message'] = 'Upload error: ' . ($error_messages[$upload_error] ?? 'Unknown error (' . $upload_error . ')');
    }
}

echo json_encode($response);
?>