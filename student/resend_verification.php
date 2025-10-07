<?php
session_start();
include "../db/dbconn.php";
require_once 'email_verification.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $messageType = "error";
    } else {
        // Check if email exists and verification status
        $query = "SELECT * FROM student WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            
            // Check if already verified
            if ($student['is_verified'] == 1) {
                $message = "This email is already verified! You can log in now.";
                $messageType = "info";
            } else {
                // Email exists but not verified - resend verification
                $newToken = bin2hex(random_bytes(32));
                $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Update token
                $updateQuery = "UPDATE student SET verification_token = ?, token_expiry = ? WHERE email = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("sss", $newToken, $tokenExpiry, $email);
                
                if ($updateStmt->execute()) {
                    // Resend email
                    $studentName = $student['first_name'] . ' ' . $student['last_name'];
                    $emailResult = sendVerificationEmail($email, $studentName, $newToken);
                    
                    if ($emailResult['success']) {
                        $message = "Verification email has been resent! Please check your inbox and spam folder.";
                        $messageType = "success";
                    } else {
                        $message = "Failed to send email. Please try again later.";
                        $messageType = "error";
                    }
                } else {
                    $message = "Failed to update verification token. Please try again.";
                    $messageType = "error";
                }
                
                $updateStmt->close();
            }
        } else {
            // Email not found
            $message = "This email is not registered. Please register first.";
            $messageType = "error";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Email</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('Backdrop.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container-box {
            background: rgba(167, 255, 126, 0.50);
            width: 500px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-style: outset;
            border-color: #a0fda3ff;
        }
        
        h2 {
            font-family: Georgia, serif;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .btn-custom {
            background: #646300ff;
            color: #fff;
            border-radius: 10px;
            transition: 0.3s;
        }
        
        .btn-custom:hover {
            background: #dfcc29ff;
            color: #fff;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: #646300ff;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .info-text {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="container-box">
        <h2>Resend Verification Email</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : ($messageType == 'error' ? 'danger' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
                
                <?php if ($messageType == 'info' && strpos($message, 'already verified') !== false): ?>
                    <hr style="margin: 10px 0;">
                    <a href="student_log_reg.php" class="btn btn-sm btn-custom">Go to Login</a>
                <?php endif; ?>
                
                <?php if ($messageType == 'error' && strpos($message, 'not registered') !== false): ?>
                    <hr style="margin: 10px 0;">
                    <a href="student_log_reg.php" class="btn btn-sm btn-custom">Register Now</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <p class="info-text">
            Enter your email address to receive a new verification link.
        </p>
        
        <form method="POST" action="">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Resend Verification Email</button>
        </form>
        
        <div class="back-link">
            <a href="student_log_reg.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>