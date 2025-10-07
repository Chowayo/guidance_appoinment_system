<?php
session_start();
include "../db/dbconn.php";
require_once 'password_reset_email.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $messageType = "error";
    } else {
        // Check if email exists and is verified
        $query = "SELECT * FROM student WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            
            // Check if email is verified
            if ($student['is_verified'] == 0) {
                $message = "Please verify your email first before resetting password.";
                $messageType = "error";
            } else {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Update reset token in database
                $updateQuery = "UPDATE student SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("sss", $resetToken, $tokenExpiry, $email);
                
                if ($updateStmt->execute()) {
                    // Send reset email
                    $studentName = $student['first_name'] . ' ' . $student['last_name'];
                    $emailResult = sendPasswordResetEmail($email, $studentName, $resetToken);
                    
                    if ($emailResult['success']) {
                        $message = "Password reset link has been sent to your email. Please check your inbox or spam folder.";
                        $messageType = "success";
                    } else {
                        $message = "Failed to send reset email. Please try again later.";
                        $messageType = "error";
                    }
                } else {
                    $message = "Failed to process request. Please try again.";
                    $messageType = "error";
                }
                
                $updateStmt->close();
            }
        } else {
            // Don't reveal if email exists for security
            $message = "If this email is registered, you will receive a password reset link.";
            $messageType = "info";
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
    <title>Forgot Password</title>
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
            padding: 20px;
        }
        
        .container-box {
            background: rgba(167, 255, 126, 0.50);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-style: outset;
            border-color: #a0fda3ff;
        }
        
        h2 {
            font-family: Georgia, serif;
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
        
        .subtitle {
            text-align: center;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 25px;
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
        
        .icon {
            text-align: center;
            font-size: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-box">
        <div class="icon">🔑</div>
        <h2>Forgot Password?</h2>
        <p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : ($messageType == 'error' ? 'danger' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Send Reset Link</button>
        </form>
        
        <div class="back-link">
            <a href="student_log_reg.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>