<?php
// This endpoint returns JSON to an AJAX caller, so PHP warnings/notices
// (e.g. from mail() when no local SMTP server is configured) must not
// be echoed into the response body - they'd corrupt the JSON.
ini_set('display_errors', '0');

header('Content-Type: application/json');
include('../config/database.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Generate a 6-digit OTP, store its hash with a 10 minute expiry
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_hash = hash('sha256', $otp);
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $otp_hash, $expiry, $user['id']);

        if ($update_stmt->execute()) {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: ErrandsCall <info@errandscall.co.za>\r\n";

            $name = $user['fullname'];

            $body = "
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset='UTF-8'>
              <meta name='viewport' content='width=device-width, initial-scale=1.0'>
              <style>
                body { margin:0; padding:0; background:#f4f4f4; font-family: Arial, Helvetica, sans-serif; color:#333; }
                .email-wrapper { max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; }
                .email-header {
                  background-color:#ff8c00;
                  background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), linear-gradient(90deg, #ff8c00, #ffd700);
                  padding:25px; text-align:center;
                }
                .email-header h1 { margin:0; color:#fff; font-size:20px; letter-spacing:0.5px; }
                .email-body { padding:30px; }
                .email-body h2 { margin-top:0; color:#ff8c00; font-size:18px; }
                .email-body p { line-height:1.6; font-size:14px; }
                .otp-box {
                  background:#f9f9f9; border-left:4px solid #ff8c00; padding:20px;
                  margin:25px 0; text-align:center; border-radius:4px;
                }
                .otp-code { font-size:32px; font-weight:bold; letter-spacing:8px; color:#ff8c00; }
                .otp-expiry { font-size:13px; color:#999; margin-top:10px; }
                .email-footer { background:#fafafa; padding:18px 30px; text-align:center; font-size:12px; color:#999; border-top:1px solid #eee; }
                @media (max-width:600px) {
                  .email-body, .email-header, .email-footer { padding-left:18px !important; padding-right:18px !important; }
                  .otp-code { font-size:26px; letter-spacing:5px; }
                }
              </style>
            </head>
            <body>
              <div class='email-wrapper'>
                <div class='email-header'>
                  <h1>Password Reset Request</h1>
                </div>
                <div class='email-body'>
                  <h2>Hi {$name},</h2>
                  <p>We received a request to reset your ErrandsCall portal password. Use the code below to continue:</p>
                  <div class='otp-box'>
                    <div class='otp-code'>{$otp}</div>
                    <div class='otp-expiry'>This code expires in 10 minutes.</div>
                  </div>
                  <p>If you did not request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='email-footer'>
                  This is an automated message from ErrandsCall. Please do not reply to this email.
                </div>
              </div>
            </body>
            </html>";

            $mailSent = mail($email, 'Your ErrandsCall Password Reset Code', $body, $headers);

            $response['success'] = true;
            if ($mailSent) {
                $response['message'] = 'An OTP has been sent to your email address.';
            } else {
                // Demo-mode fallback when local mail() is unavailable
                $response['message'] = 'Email delivery is unavailable in this environment. Your OTP code is: ' . $otp;
                $response['otp'] = $otp;
            }
        } else {
            $response['message'] = 'Error generating OTP. Please try again.';
        }

        $update_stmt->close();
    } else {
        $response['message'] = 'Email not found in our system.';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
