<?php
header('Content-Type: application/json');
include('../config/database.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($otp) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $response['message'] = 'Password must be at least 8 characters long and contain both letters and numbers.';
        echo json_encode($response);
        exit;
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, reset_token, reset_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $otp_hash = hash('sha256', $otp);

        $is_valid = !empty($user['reset_token']) && !empty($user['reset_expiry'])
            && hash_equals($user['reset_token'], $otp_hash)
            && strtotime($user['reset_expiry']) >= time();

        if (!$is_valid) {
            $response['message'] = 'Invalid or expired OTP.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user['id']);

            if ($update_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Password reset successfully! You can now login with your new password.';
                $response['redirect'] = 'index.php';
            } else {
                $response['message'] = 'Error resetting password. Please try again.';
            }

            $update_stmt->close();
        }
    } else {
        $response['message'] = 'Invalid or expired OTP.';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
