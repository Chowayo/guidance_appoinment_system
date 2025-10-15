<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
include '../db/dbconn.php';
include '../config.php';

require_once __DIR__ . '/../phpmailer/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $studentName, $resetToken) {
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
        $mail->Subject = 'Password Reset Request - Evergreen Academy';

        $resetLink = $base_url . "/student/reset_password.php?token=" . urlencode($resetToken);
        
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
                    .header { background-color: #646300ff; color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .greeting { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
                    .button { display: inline-block; padding: 14px 35px; background-color: #646300ff; 
                             color: white !important; text-decoration: none; border-radius: 8px; margin: 20px 0; 
                             font-weight: bold; transition: background-color 0.3s; }
                    .button:hover { background-color: #dfcc29ff; }
                    .link-box { background-color: #f5f5f5; padding: 15px; border-radius: 8px; 
                               word-break: break-all; margin: 15px 0; border-left: 4px solid #646300ff; 
                               font-size: 13px; color: #666; }
                    .warning-box { background-color: #fff3cd; padding: 20px; border-radius: 8px; 
                                  border-left: 4px solid #ffc107; margin: 20px 0; }
                    .warning-title { font-weight: bold; color: #856404; margin-bottom: 10px; font-size: 16px; }
                    .note-list { margin: 0; padding-left: 20px; color: #856404; }
                    .note-list li { margin: 5px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; 
                             border-top: 1px solid #e0e0e0; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔑 Password Reset Request</h1>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello, {$studentName}!</p>
                        <p>We received a request to reset your password for your Evergreen Academy account.</p>
                        <p>Click the button below to reset your password:</p>
                        
                        <p style='text-align: center;'>
                            <a href='{$resetLink}' class='button'>Reset Password</a>
                        </p>
                        
                        <p style='text-align: center; font-size: 13px; color: #666; margin-top: 15px;'>
                            Or copy and paste this link into your browser:
                        </p>
                        <div class='link-box'>{$resetLink}</div>
                        
                        <div class='warning-box'>
                            <div class='warning-title'>⚠️ Important Security Information:</div>
                            <ul class='note-list'>
                                <li>This password reset link will <strong>expire in 1 hour</strong></li>
                                <li>If you didn't request a password reset, please ignore this email</li>
                                <li>Your password will remain unchanged until you create a new one</li>
                                <li>For security, never share this link with anyone</li>
                            </ul>
                        </div>
                        
                        <p style='font-size: 14px; color: #666; margin-top: 20px;'>
                            If you're having trouble clicking the button, you can manually copy and paste the URL into your browser.
                        </p>
                    </div>
                    <div class='footer'>
                        <p><strong>Evergreen Academy</strong></p>
                        <p>Guidance Management System</p>
                        <p>&copy; 2025 All Rights Reserved</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Hello {$studentName},\n\n"
                       . "We received a request to reset your password.\n\n"
                       . "Reset your password using this link: {$resetLink}\n\n"
                       . "This link expires in 1 hour.\n\n"
                       . "If you didn't request this, please ignore this email.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Password reset email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format. Please enter a valid email address.';
        header("Location: " . BASE_URL . "/student/forgot_password.php");
        exit;
    }
    
    $query = "SELECT student_id, first_name, last_name FROM student WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $studentName = $student['first_name'] . ' ' . $student['last_name'];
        
        $resetToken = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $update = $conn->prepare("UPDATE student SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $resetToken, $expiry, $email);
        
        if ($update->execute()) {
            $emailResult = sendPasswordResetEmail($email, $studentName, $resetToken);
            
            if ($emailResult['success']) {
                $_SESSION['success'] = '✓ Password reset link has been sent to your email address. Please check your inbox and spam folder.';
            } else {
                $_SESSION['error'] = 'Failed to send reset email. Please try again later or contact support.';
            }
        } else {
            $_SESSION['error'] = 'An error occurred while processing your request. Please try again.';
        }
        
        $update->close();
    } else {
        $_SESSION['error'] = '✗ Email address not found in our system. Please check your email or register a new account.';
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: " . BASE_URL . "/student/forgot_password.php");
    exit;
}

header("Location: " . BASE_URL . "/student/forgot_password.php");
exit;
?>
