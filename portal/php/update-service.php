<?php
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/auth-check.php');
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];
    $service_id = intval($_POST['service_id']);
    $status = $_POST['status'];
    $update_text = trim($_POST['update_text']);
    $assigned_to = isset($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
    $notify_customer = isset($_POST['notify_customer']);

    // Validation
    if (empty($status) || empty($update_text)) {
        $response['message'] = 'Status and update text are required.';
        echo json_encode($response);
        exit;
    }

    $conn = getDBConnection();

    // Verify service access
    if ($user_role === 'customer') {
        $check_sql = "SELECT id FROM services WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $service_id, $user_id);
    } elseif ($user_role === 'worker') {
        $check_sql = "SELECT id FROM services WHERE id = ? AND assigned_to = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $service_id, $user_id);
    } else {
        $check_sql = "SELECT id FROM services WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $service_id);
    }

    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $response['message'] = 'Service not found or access denied.';
        $check_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();

    // Update service
    $update_sql = "UPDATE services SET status = ?, assigned_to = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if ($assigned_to) {
        $update_stmt->bind_param("sii", $status, $assigned_to, $service_id);
    } else {
        $update_stmt->bind_param("sii", $status, $assigned_to, $service_id);
    }

    if (!$update_stmt->execute()) {
        $response['message'] = 'Error updating service: ' . $conn->error;
        $update_stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $update_stmt->close();

    // Add service update
    $update_stmt = $conn->prepare("INSERT INTO service_updates (service_id, user_id, update_text, update_type) VALUES (?, ?, ?, 'progress_update')");
    $update_stmt->bind_param("iis", $service_id, $user_id, $update_text);
    $update_stmt->execute();
    $update_stmt->close();

    // Handle document upload
    if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
        $upload_dir = '../assets/uploads/documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
            if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                $file_extension = pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION);
                $filename = 'doc_' . $service_id . '_' . time() . '_' . $i . '.' . $file_extension;
                $target_path = $upload_dir . $filename;

                $allowed_types = ['pdf', 'png', 'jpg', 'jpeg'];
                if (in_array(strtolower($file_extension), $allowed_types) && 
                    $_FILES['documents']['size'][$i] <= 10 * 1024 * 1024) {
                    
                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $target_path)) {
                        $doc_stmt = $conn->prepare("INSERT INTO service_documents (service_id, document_path, document_type, uploaded_by) VALUES (?, ?, 'worker_requested', ?)");
                        $doc_stmt->bind_param("isi", $service_id, $target_path, $user_id);
                        $doc_stmt->execute();
                        $doc_stmt->close();
                    }
                }
            }
        }
    }

    // TODO: Send email notification if $notify_customer is true
    // You would implement email sending logic here

    $conn->close();

    $response['success'] = true;
    $response['message'] = 'Service updated successfully!';
}

echo json_encode($response);
?>