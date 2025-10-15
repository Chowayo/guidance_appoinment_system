<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
include '../db/dbconn.php';
require_once 'counselor_password_reset_email.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format. Please enter a valid email address.';
        header('Location: forgot_password.php');
        exit;
    }
    
    $query = "SELECT counselor_id, first_name, last_name, email FROM counselor WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $counselor = $result->fetch_assoc();
        $counselorName = $counselor['first_name'] . ' ' . $counselor['last_name'];
        
        $resetToken = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        

        $update = $conn->prepare("UPDATE counselor SET reset_token = ?, reset_token_expiry = ? WHERE counselor_id = ?");
        $update->bind_param("ssi", $resetToken, $expiry, $counselor['counselor_id']);
        
        if ($update->execute()) {
            $emailResult = sendCounselorPasswordResetEmail($email, $counselorName, $resetToken);
            
            if ($emailResult['success']) {
                $_SESSION['success'] = '✓ Password reset link has been sent to your email address. Please check your inbox and spam folder.';
            } else {
                $_SESSION['error'] = 'Failed to send reset email. Please try again later or contact the system administrator.';
            }
        } else {
            $_SESSION['error'] = 'An error occurred while processing your request. Please try again.';
        }
        
        $update->close();
    } else {
        $_SESSION['error'] = '✗ Email address not found in our system. Please check your email or contact the system administrator.';
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: forgot_password.php');
    exit;
}

header('Location: forgot_password.php');
exit;
?>