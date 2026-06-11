<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');
include('../includes/upload-validator.php');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_type = $_POST['service_type'];
    $vehicle_id = intval($_POST['vehicle_id']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'] ?? 'Medium';
    $preferred_date = $_POST['preferred_date'] ?? null;
    
    // Validation
    if (empty($service_type) || empty($vehicle_id)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Verify vehicle belongs to user
    $check_stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
    if ($check_stmt === false) {
        error_log('Database error: ' . $conn->error);
        $response['message'] = 'A database error occurred. Please try again later.';
        echo json_encode($response);
        exit;
    }
    
    $check_stmt->bind_param("ii", $vehicle_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $response['message'] = 'Invalid vehicle selected.';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Insert service request (without documents first)
    $stmt = $conn->prepare("INSERT INTO services (service_type, description, priority, preferred_date, user_id, vehicle_id) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        error_log('Database error: ' . $conn->error);
        $response['message'] = 'A database error occurred. Please try again later.';
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("ssssii", $service_type, $description, $priority, $preferred_date, $user_id, $vehicle_id);
    
    if ($stmt->execute()) {
        $service_id = $conn->insert_id;
        
        // Handle file uploads for service_documents table
        if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
            $upload_dir = '../assets/uploads/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $allowed_types = ['pdf', 'png', 'jpg', 'jpeg'];
            $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];

            for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['documents']['name'][$i],
                        'tmp_name' => $_FILES['documents']['tmp_name'][$i],
                        'size' => $_FILES['documents']['size'][$i],
                    ];

                    // Validate file type, content, and size
                    if (validateUploadedFile($file, $allowed_types, $allowed_mime_types, 10 * 1024 * 1024)) {
                        continue; // Skip invalid files
                    }

                    $file_extension = pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION);
                    $filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $target_path)) {
                        // Insert into service_documents table
                        $web_path = 'assets/uploads/documents/' . $filename;
                        $doc_stmt = $conn->prepare("INSERT INTO service_documents (service_id, document_path, document_type, uploaded_by) VALUES (?, ?, 'user_uploaded', ?)");
                        if ($doc_stmt) {
                            $doc_stmt->bind_param("isi", $service_id, $web_path, $user_id);
                            $doc_stmt->execute();
                            $doc_stmt->close();
                        }
                    }
                }
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Service request submitted successfully!';
    } else {
        error_log('Error submitting service request: ' . $conn->error);
        $response['message'] = 'Error submitting service request. Please try again.';
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>