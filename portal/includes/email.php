<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class EmailService {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure() {
        // SMTP Configuration
        $this->mail->isSMTP();
        $this->mail->Host = 'your-smtp-host.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'noreply@errandscall.com';
        $this->mail->Password = 'your-email-password';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        // Sender info
        $this->mail->setFrom('noreply@errandscall.com', 'ErrandsCall Portal');
        $this->mail->isHTML(true);
    }
    
    public function sendServiceUpdate($userEmail, $userName, $serviceDetails) {
        try {
            $this->mail->addAddress($userEmail, $userName);
            $this->mail->Subject = 'Service Update: ' . $serviceDetails['service_type'];
            
            $html = $this->getServiceUpdateTemplate($userName, $serviceDetails);
            $this->mail->Body = $html;
            $this->mail->AltBody = strip_tags($html);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendDocumentRequest($userEmail, $userName, $requestDetails) {
        try {
            $this->mail->addAddress($userEmail, $userName);
            $this->mail->Subject = 'Document Request for Your Service';
            
            $html = $this->getDocumentRequestTemplate($userName, $requestDetails);
            $this->mail->Body = $html;
            $this->mail->AltBody = strip_tags($html);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getServiceUpdateTemplate($userName, $serviceDetails) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: linear-gradient(135deg, #ff8c00, #ffd700); padding: 20px; color: white; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>ErrandsCall Service Update</h2>
            </div>
            <div class='content'>
                <p>Hello $userName,</p>
                <p>Your service <strong>{$serviceDetails['service_type']}</strong> has been updated:</p>
                <p><strong>Status:</strong> {$serviceDetails['status']}</p>
                <p><strong>Update:</strong> {$serviceDetails['update_text']}</p>
                <p><strong>Vehicle:</strong> {$serviceDetails['vehicle_info']}</p>
                <p>You can view the complete details in your dashboard.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " ErrandsCall. All rights reserved.</p>
            </div>
        </body>
        </html>";
    }
    
    private function getDocumentRequestTemplate($userName, $requestDetails) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: linear-gradient(135deg, #ff8c00, #ffd700); padding: 20px; color: white; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .btn { background: #ff8c00; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Document Request</h2>
            </div>
            <div class='content'>
                <p>Hello $userName,</p>
                <p>We need additional documents for your service <strong>{$requestDetails['service_type']}</strong>:</p>
                <p><strong>Required Documents:</strong> {$requestDetails['documents_needed']}</p>
                <p><strong>Instructions:</strong> {$requestDetails['instructions']}</p>
                <p>Please upload these documents through your dashboard.</p>
                <p><a href='https://your-portal.com/upload-documents.php' class='btn'>Upload Documents</a></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " ErrandsCall. All rights reserved.</p>
            </div>
        </body>
        </html>";
    }
}
?>