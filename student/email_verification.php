<?php
require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $studentName, $verificationToken) {
    global $base_url;
    $mail = new PHPMailer(true);
    
    try {
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
        $mail->Subject = 'Verify Your Email Address - Evergreen Academy';
        
        $verificationLink = $base_url . "/student/verify_email.php?token=" . urlencode($verificationToken);

        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                    .header { background-color: #646300ff; color: white; padding: 25px; text-align: center; }
                    .content { padding: 30px; background-color: #f9f9f9; }
                    .content h2 { color: #333; }
                    .button { display: inline-block; padding: 14px 35px; background-color: #646300ff; 
                             color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                    .button:hover { background-color: #dfcc29ff; }
                    .link-box { background-color: #f5f5f5; padding: 15px; border-radius: 8px; 
                               word-break: break-all; border-left: 4px solid #646300ff; font-size: 13px; color: #666; margin-top: 10px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; border-top: 1px solid #eee; background: #fafafa; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to Evergreen Academy 🌿</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello, {$studentName}!</h2>
                        <p>Thank you for registering with the <strong>Guidance Management System</strong>.</p>
                        <p>Please verify your email address by clicking the button below:</p>
                        <p style='text-align: center;'>
                            <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                        </p>
                        <p style='font-size: 13px; color: #666;'>Or copy and paste this link into your browser:</p>
                        <div class='link-box'>{$verificationLink}</div>
                        <p><strong>Note:</strong> This verification link will expire in <strong>24 hours</strong>.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 Evergreen Academy | Guidance Management System</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Hello {$studentName},\n\n"
                       . "Thank you for registering with Evergreen Academy.\n\n"
                       . "Please verify your email using the following link:\n{$verificationLink}\n\n"
                       . "This link will expire in 24 hours.\n\n"
                       . "— Evergreen Academy Guidance Management System";
        
        $mail->send();
        return ['success' => true, 'message' => 'Verification email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}
?>
