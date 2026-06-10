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
        $response['message'] = 'Database error: ' . $conn->error;
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
        $response['message'] = 'Database error: ' . $conn->error;
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
            
            for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION);
                    $filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $filename;
                    
                    // Validate file type
                    $allowed_types = ['pdf', 'png', 'jpg', 'jpeg'];
                    if (!in_array(strtolower($file_extension), $allowed_types)) {
                        continue; // Skip invalid files
                    }
                    
                    // Validate file size (10MB)
                    if ($_FILES['documents']['size'][$i] > 10 * 1024 * 1024) {
                        continue; // Skip oversized files
                    }
                    
                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $target_path)) {
                        // Insert into service_documents table
                        $doc_stmt = $conn->prepare("INSERT INTO service_documents (service_id, document_path, document_type, uploaded_by) VALUES (?, ?, 'user_uploaded', ?)");
                        if ($doc_stmt) {
                            $doc_stmt->bind_param("isi", $service_id, $target_path, $user_id);
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
        $response['message'] = 'Error submitting service request: ' . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>