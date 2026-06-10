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
        if (isset($_GET['id'])) {
            // Read a single email template
            $stmt = $conn->prepare("SELECT * FROM email_templates WHERE id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $response['success'] = true;
                $response['template'] = $result->fetch_assoc();
            } else {
                $response['message'] = 'Template not found';
            }
            $stmt->close();
        } else {
            // List all email templates
            $result = $conn->query("SELECT * FROM email_templates ORDER BY name");
            $response['success'] = true;
            $response['templates'] = $result->fetch_all(MYSQLI_ASSOC);
        }
        break;
        
    case 'POST':
        // Create or update email template
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $variables = $_POST['variables'] ?? '';
        
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("
                UPDATE email_templates 
                SET name = ?, subject = ?, body = ?, variables = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssssi", $name, $subject, $body, $variables, $id);
        } else {
            // Create
            $stmt = $conn->prepare("
                INSERT INTO email_templates (name, subject, body, variables) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $name, $subject, $body, $variables);
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = $id > 0 ? 'Template updated' : 'Template created';
        } else {
            $response['message'] = 'Error saving template';
        }
        $stmt->close();
        break;
        
    case 'DELETE':
        // Delete email template
        parse_str(file_get_contents("php://input"), $delete_vars);
        $id = $delete_vars['id'];
        
        $stmt = $conn->prepare("DELETE FROM email_templates WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Template deleted';
        } else {
            $response['message'] = 'Error deleting template';
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);
?>