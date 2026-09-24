<?php
header('Content-Type: application/json');
include('../config/database.php');
require_once('../includes/rate-limit.php');

// A reset code allows 5 wrong tries (20 per IP address) within its 10 minute life
const RESET_MAX_PER_ACCOUNT = 5;
const RESET_MAX_PER_IP = 20;
const RESET_WINDOW_MINUTES = 10;

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

    if (tooManyAttempts($conn, 'reset', $email, RESET_MAX_PER_ACCOUNT, RESET_MAX_PER_IP, RESET_WINDOW_MINUTES)) {
        $conn->close();
        $response['message'] = 'Too many incorrect codes. Please request a new code.';
        echo json_encode($response);
        exit;
    }

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
            recordAttempt($conn, 'reset', $email);

            // Out of tries: cancel the code so it can't be guessed any further
            if (tooManyAttempts($conn, 'reset', $email, RESET_MAX_PER_ACCOUNT, PHP_INT_MAX, RESET_WINDOW_MINUTES)) {
                $cancel_stmt = $conn->prepare("UPDATE users SET reset_token = NULL, reset_expiry = NULL WHERE id = ?");
                $cancel_stmt->bind_param("i", $user['id']);
                $cancel_stmt->execute();
                $cancel_stmt->close();
                $response['message'] = 'Too many incorrect codes. Please request a new code.';
            }
        } else {
            clearAttempts($conn, 'reset', $email);
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
        recordAttempt($conn, 'reset', $email);
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
