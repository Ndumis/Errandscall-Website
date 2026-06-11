<?php
require_once('../includes/session-config.php');
startSecureSession();
header('Content-Type: application/json');
include('../config/database.php');
include('../includes/upload-validator.php');

$response = ['success' => false, 'message' => ''];

// Validates a 13-digit South African ID number (date of birth + Luhn checksum)
function isValidSaId($id) {
    if (!preg_match('/^\d{13}$/', $id)) {
        return false;
    }

    $yy = (int) substr($id, 0, 2);
    $mm = (int) substr($id, 2, 2);
    $dd = (int) substr($id, 4, 2);
    $currentYY = (int) date('y');
    $century = ($yy <= $currentYY) ? 2000 : 1900;
    $year = $century + $yy;

    if (!checkdate($mm, $dd, $year)) {
        return false;
    }

    // Luhn checksum
    $sumOdd = 0;
    for ($i = 0; $i < 12; $i += 2) {
        $sumOdd += (int) $id[$i];
    }

    $evenDigits = '';
    for ($i = 1; $i < 12; $i += 2) {
        $evenDigits .= $id[$i];
    }
    $evenDoubled = (string) ((int) $evenDigits * 2);
    $sumEven = 0;
    for ($i = 0; $i < strlen($evenDoubled); $i++) {
        $sumEven += (int) $evenDoubled[$i];
    }

    $checkDigit = (10 - (($sumOdd + $sumEven) % 10)) % 10;
    return $checkDigit === (int) $id[12];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_foreign_national = isset($_POST['is_foreign_national']) && $_POST['is_foreign_national'] === '1' ? 1 : 0;
    $id_number = trim($_POST['id_number'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $initials = trim($_POST['initials'] ?? '');
    $firstnames = trim($_POST['firstnames'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $confirm_email = trim($_POST['confirm_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $address_line3 = trim($_POST['address_line3'] ?? '');
    $address_line4 = trim($_POST['address_line4'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Required fields validation
    if (empty($id_number) || empty($dob) || empty($initials) || empty($firstnames) || empty($surname) ||
        empty($email) || empty($confirm_email) || empty($phone) || empty($address_line1) ||
        empty($postal_code) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    if ($email !== $confirm_email) {
        $response['message'] = 'Email addresses do not match.';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $response['message'] = 'Password must be at least 8 characters long and contain both letters and numbers.';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit;
    }

    if (!$is_foreign_national && !isValidSaId($id_number)) {
        $response['message'] = 'Please enter a valid 13-digit South African ID number.';
        echo json_encode($response);
        exit;
    }

    // ID document upload validation
    if (!isset($_FILES['id_document']) || $_FILES['id_document']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Please upload a copy of your ID document.';
        echo json_encode($response);
        exit;
    }

    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_ext = strtolower(pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION));

    $validation_error = validateUploadedFile($_FILES['id_document'], $allowed_ext, $allowed_mime_types, $max_size);
    if ($validation_error) {
        $response['message'] = $validation_error;
        echo json_encode($response);
        exit;
    }

    $conn = getDBConnection();

    // Check if email or ID number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR id_number = ?");
    $stmt->bind_param("ss", $email, $id_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = 'Email or ID number already registered.';
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Move uploaded ID document to secure directory
    $upload_dir = '../assets/uploads/id_documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = 'idoc_' . time() . '_' . uniqid() . '.' . $file_ext;
    $destination = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['id_document']['tmp_name'], $destination)) {
        $response['message'] = 'Failed to upload ID document. Please try again.';
        $conn->close();
        echo json_encode($response);
        exit;
    }

    $id_document_path = 'assets/uploads/id_documents/' . $filename;
    $fullname = trim("$firstnames $surname");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (
        id_number, fullname, firstnames, surname, initials, email, phone, dob,
        is_foreign_national, id_document_path, address_line1, address_line2,
        address_line3, address_line4, postal_code, password, role, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $role = 'customer';
    $status = 'active';

    $stmt->bind_param(
        "ssssssssisssssssss",
        $id_number, $fullname, $firstnames, $surname, $initials, $email, $phone, $dob,
        $is_foreign_national, $id_document_path, $address_line1, $address_line2,
        $address_line3, $address_line4, $postal_code, $hashed_password, $role, $status
    );

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_id_number'] = $id_number;
        $_SESSION['logged_in'] = true;

        $response['success'] = true;
        $response['message'] = 'Registration successful! Redirecting to dashboard...';
        $response['redirect'] = 'dashboard.php';
    } else {
        // Clean up uploaded file if the insert failed
        unlink($destination);
        $response['message'] = 'Registration failed. Please try again.';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
