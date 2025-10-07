<?php
require_once __DIR__ . '/../phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $studentName, $verificationToken) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chowxinnlu@gmail.com';
        $mail->Password   = 'dkrnuziewiqvdewe'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('chowxinnlu@gmail.com', 'Evergreen Academy');
        $mail->addAddress($email, $studentName);
        
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';
        
        // CHANGE YOUR DOMAIN HERE
        $verificationLink = "http://localhost/guidance_management/student/verify_email.php?token=" . $verificationToken;
$codeVerificationLink = "http://localhost/guidance_management/student/verify_code.php";
        
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #646300ff; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background-color: #f4f4f4; padding: 30px; }
                    .button { display: inline-block; padding: 12px 30px; background-color: #646300ff; 
                             color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome!</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello, {$studentName}!</h2>
                        <p>Thank you for registering with our Guidance Management System.</p>
                        <p>Please verify your email address by clicking the button below:</p>
                        <p style='text-align: center;'>
                            <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                        </p>
                        <p>Or copy and paste this link:</p>
                        <p style='word-break: break-all; color: #646300ff;'>{$verificationLink}</p>
                        <p><strong>Note:</strong> This link expires in 24 hours.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 School Guidance System</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->send();
        return ['success' => true, 'message' => 'Verification email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}
?>