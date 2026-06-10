<?php
// This endpoint returns plain text to an AJAX caller, so PHP warnings/notices
// must not be echoed into the response body (they'd corrupt it).
ini_set('display_errors', '0');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);
    $phone   = filter_var(trim($_POST["phone"] ?? ''), FILTER_SANITIZE_STRING);
    $service = filter_var(trim($_POST["service"] ?? ''), FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo "Please fill in all fields.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email address.";
        exit;
    }

    // Map the service dropdown value to a readable label
    $serviceLabels = [
        'license-renewal'  => 'License Disc Renewal',
        'change-ownership' => 'Change of Ownership',
        'drivers-license'  => "Driver's License Booking",
        'roadworthy'       => 'Roadworthy Certificate',
        'number-plates'    => 'Number Plates',
        'other'            => 'Other Service',
    ];
    $serviceLabel = $serviceLabels[$service] ?? 'Not specified';
    $phoneDisplay = !empty($phone) ? $phone : 'Not provided';
    $submittedAt  = date('d M Y, H:i');

    $to      = "info@errandscall.co.za"; // CHANGE THIS
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: ".$name." <".$email.">\r\n";
    $headers .= "Reply-To: ".$email."\r\n";

    $logoUrl = "https://www.errandscall.co.za/images/logo.png"; // CHANGE path to your logo
    $replyMailto = "mailto:" . rawurlencode($email) . "?subject=" . rawurlencode("Re: " . $subject);

    // ===== Admin Email (You) =====
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <style>
        body { margin:0; padding:0; background:#f4f4f4; font-family: Arial, Helvetica, sans-serif; color:#333; }
        .email-wrapper { max-width:600px; margin:0 auto; background:#ffffff; }
        .email-header {
          background-color:#ff8c00;
          background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), linear-gradient(90deg, #ff8c00, #ffd700);
          padding:24px 30px; text-align:center;
        }
        .email-header h1 { margin:0; color:#fff; font-size:20px; letter-spacing:0.5px; }
        .email-body { padding:30px; }
        .email-body h2 { margin-top:0; color:#ff8c00; font-size:18px; }
        .info-table { width:100%; border-collapse:collapse; margin:15px 0 25px; }
        .info-table td { padding:10px 12px; border-bottom:1px solid #eee; font-size:14px; vertical-align:top; }
        .info-table td.label { width:140px; font-weight:bold; color:#555; }
        .message-box { background:#f9f9f9; border-left:4px solid #ff8c00; padding:15px; margin:0 0 25px; font-size:14px; line-height:1.6; }
        .btn { display:inline-block; background:#ff8c00; color:#ffffff !important; text-decoration:none; padding:12px 28px; border-radius:5px; font-weight:bold; font-size:14px; }
        .email-footer { background:#fafafa; padding:18px 30px; text-align:center; font-size:12px; color:#999; border-top:1px solid #eee; }
        @media (max-width:600px) {
          .email-body, .email-header, .email-footer { padding-left:18px !important; padding-right:18px !important; }
          .info-table tr { display:block; padding-bottom:8px; border-bottom:1px solid #eee; }
          .info-table td { display:block; border-bottom:none; padding:2px 0; }
          .info-table td.label { width:auto; }
        }
      </style>
    </head>
    <body>
      <div class='email-wrapper'>
        <div class='email-header'>
          <h1>New Contact Form Submission</h1>
        </div>
        <div class='email-body'>
          <h2>Contact Details</h2>
          <table class='info-table'>
            <tr><td class='label'>Name</td><td>{$name}</td></tr>
            <tr><td class='label'>Email</td><td>{$email}</td></tr>
            <tr><td class='label'>Phone</td><td>{$phoneDisplay}</td></tr>
            <tr><td class='label'>Service</td><td>{$serviceLabel}</td></tr>
            <tr><td class='label'>Subject</td><td>{$subject}</td></tr>
            <tr><td class='label'>Submitted</td><td>{$submittedAt}</td></tr>
          </table>
          <h2>Message</h2>
          <div class='message-box'>".nl2br($message)."</div>
          <p style='text-align:center; margin:25px 0 0;'>
            <a href='{$replyMailto}' class='btn'>Reply to {$name}</a>
          </p>
        </div>
        <div class='email-footer'>
          This message was submitted via the contact form on errandscall.co.za.
        </div>
      </div>
    </body>
    </html>";

    $mailSent = mail($to, "ErrandsCall Contact: ".$subject, $body, $headers);

    // ===== Auto-Reply (Client) =====
    $replySubject = "We received your message - ErrandsCall";
    $replyHeaders = "MIME-Version: 1.0" . "\r\n";
    $replyHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $replyHeaders .= "From: ErrandsCall <info@errandscall.co.za>\r\n";

    $replyBody = "
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
        .email-header img { max-height:50px; }
        .email-body { padding:30px; }
        .email-body h2 { margin-top:0; color:#ff8c00; font-size:20px; }
        .email-body p { line-height:1.6; font-size:14px; }
        .summary-box { background:#f9f9f9; border-left:4px solid #ff8c00; padding:15px 18px; margin:20px 0; font-size:14px; }
        .summary-box p { margin:6px 0; }
        .steps { margin:25px 0; }
        .step { display:flex; align-items:flex-start; margin-bottom:15px; }
        .step-number {
          flex-shrink:0; width:28px; height:28px; line-height:28px; text-align:center;
          border-radius:50%; background:#ff8c00; color:#ffffff; font-weight:bold; font-size:14px; margin-right:12px;
        }
        .step-text { font-size:14px; padding-top:3px; }
        .btn-row { text-align:center; margin:30px 0 10px; }
        .btn { display:inline-block; background:#ff8c00; color:#ffffff !important; text-decoration:none; padding:12px 30px; border-radius:5px; font-weight:bold; font-size:14px; margin:6px; }
        .btn-outline { display:inline-block; border:2px solid #ff8c00; color:#ff8c00 !important; text-decoration:none; padding:10px 28px; border-radius:5px; font-weight:bold; font-size:14px; margin:6px; }
        .email-footer { background:#fafafa; padding:20px 30px; text-align:center; font-size:12px; color:#888; border-top:1px solid #eee; }
        .email-footer p { margin:4px 0; }
        .email-footer a { color:#ff8c00; text-decoration:none; }
        @media (max-width:600px) {
          .email-body, .email-header, .email-footer { padding-left:18px !important; padding-right:18px !important; }
          .btn, .btn-outline { display:block; margin:8px auto; width:80%; box-sizing:border-box; }
        }
      </style>
    </head>
    <body>
      <div class='email-wrapper'>
        <div class='email-header'>
          <img src='{$logoUrl}' alt='ErrandsCall Logo'>
        </div>
        <div class='email-body'>
          <h2>Thank You, {$name}!</h2>
          <p>We've received your message and a member of our team will get back to you shortly.</p>

          <div class='summary-box'>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Service:</strong> {$serviceLabel}</p>
            <p><strong>Your Message:</strong><br>".nl2br($message)."</p>
          </div>

          <h2 style='font-size:16px;'>What Happens Next?</h2>
          <div class='steps'>
            <div class='step'>
              <div class='step-number'>1</div>
              <div class='step-text'>Our team reviews your message &mdash; usually within <strong>2 hours</strong> during business hours.</div>
            </div>
            <div class='step'>
              <div class='step-number'>2</div>
              <div class='step-text'>A licensing specialist will contact you via email or phone to discuss your request.</div>
            </div>
            <div class='step'>
              <div class='step-number'>3</div>
              <div class='step-text'>We get to work &mdash; so you can stay on the couch while we handle the paperwork.</div>
            </div>
          </div>

          <div class='btn-row'>
            <a href='https://www.errandscall.co.za/services.php' class='btn'>Browse Services</a>
            <a href='tel:+27789444633' class='btn-outline'>Call Us</a>
          </div>
        </div>
        <div class='email-footer'>
          <p><strong>ErrandsCall</strong> &mdash; Vehicle Licensing Made Easy</p>
          <p><a href='tel:+27789444633'>+27 78 944 4633</a> &nbsp;|&nbsp; <a href='mailto:info@errandscall.co.za'>info@errandscall.co.za</a></p>
          <p>Mon-Fri: 8AM-5PM | Sat: 9AM-1PM</p>
          <p>&copy; ".date("Y")." ErrandsCall. All rights reserved.</p>
        </div>
      </div>
    </body>
    </html>";

    $replySent = mail($email, $replySubject, $replyBody, $replyHeaders);

    if ($mailSent) {
        echo "Thank you, your message has been sent!";
    } else {
        http_response_code(500);
        echo "Sorry, we could not send your message. Please try again.";
    }

} else {
    http_response_code(403);
    echo "Invalid request.";
}
