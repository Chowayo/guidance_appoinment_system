<?php
session_start();
require_once __DIR__ . '/../db/dbconn.php';

$message = '';
$messageType = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token and check expiry
    $query = "SELECT * FROM student WHERE verification_token = ? AND token_expiry > NOW() AND is_verified = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Token is valid, update student record
        $updateQuery = "UPDATE student SET is_verified = 1, verified_at = NOW(), 
                       verification_token = NULL, token_expiry = NULL 
                       WHERE verification_token = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $token);
        
        if ($updateStmt->execute()) {
            $message = "Email verified successfully! You can now log in to your account.";
            $messageType = "success";
        } else {
            $message = "Verification failed. Please try again.";
            $messageType = "error";
        }
        
        $updateStmt->close();
    } else {
        // Check if already verified
        $checkQuery = "SELECT * FROM student WHERE verification_token = ? AND is_verified = 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $token);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $message = "This email has already been verified. You can log in now.";
            $messageType = "info";
        } else {
            $message = "Invalid or expired verification link. Please request a new verification email.";
            $messageType = "error";
        }
        
        $checkStmt->close();
    }
    
    $stmt->close();
} else {
    $message = "No verification token provided.";
    $messageType = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .success .icon { color: #4CAF50; }
        .error .icon { color: #f44336; }
        .info .icon { color: #2196F3; }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .resend-link {
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container <?php echo $messageType; ?>">
        <div class="icon">
            <?php
            if ($messageType == 'success') {
                echo '✓';
            } elseif ($messageType == 'error') {
                echo '✗';
            } else {
                echo 'ℹ';
            }
            ?>
        </div>
        
        <h1>
            <?php
            if ($messageType == 'success') {
                echo 'Verification Successful!';
            } elseif ($messageType == 'error') {
                echo 'Verification Failed';
            } else {
                echo 'Already Verified';
            }
            ?>
        </h1>
        
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
        
        <?php if ($messageType == 'success' || $messageType == 'info'): ?>
            <a href="student_log_reg.php" class="button">Go to Login</a>
        <?php else: ?>
            <a href="resend_verification.php" class="resend-link">Resend Verification Email</a>
            <br><br>
            <a href="student_log_reg.php" class="button">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>