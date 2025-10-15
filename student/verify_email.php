<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
require_once __DIR__ . '/../db/dbconn.php';
require_once __DIR__ . '/../config.php';

$message = '';
$messageType = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    $query = "SELECT student_id, email, first_name, is_verified 
              FROM student 
              WHERE verification_token = ? 
              AND token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();

        if ($student['is_verified'] == 0) {
            $updateQuery = "UPDATE student 
                            SET is_verified = 1, verified_at = NOW(), 
                                verification_token = NULL, token_expiry = NULL 
                            WHERE verification_token = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $token);
            
            if ($updateStmt->execute()) {
                $message = "Email verified successfully! You can now log in to your account.";
                $messageType = "success";
            } else {
                $message = "Verification failed. Please try again or contact support.";
                $messageType = "error";
            }
            
            $updateStmt->close();
        } else {
            $message = "This email has already been verified. You can log in now.";
            $messageType = "info";
        }
    } else {
        $message = "Invalid or expired verification link. Please request a new verification email.";
        $messageType = "error";
    }

    $stmt->close();
} else {
    $message = "No verification token provided. Please check your email for the verification link.";
    $messageType = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Student Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a7ff7e 0%, #646300ff 100%);
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
        
        .success .icon { color: #28a745; }
        .error .icon { color: #dc3545; }
        .info .icon { color: #17a2b8; }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
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
            background: linear-gradient(135deg, #6b7c00 0%, #8a9e00 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .button:hover {
            background: linear-gradient(135deg, #8a9e00 0%, #a8bd00 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(100, 99, 0, 0.4);
        }
        
        .resend-link {
            display: inline-block;
            margin-top: 15px;
            margin-bottom: 15px;
            color: #6b7c00;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .resend-link:hover {
            text-decoration: underline;
            color: #8a9e00;
        }
    </style>
</head>
<body>
    <div class="container <?php echo htmlspecialchars($messageType); ?>">
        <div class="icon">
            <?php
            if ($messageType == 'success') echo '✓';
            elseif ($messageType == 'error') echo '✗';
            else echo 'ℹ️';
            ?>
        </div>
        
        <h1>
            <?php
            if ($messageType == 'success') echo 'Verification Successful!';
            elseif ($messageType == 'error') echo 'Verification Failed';
            else echo 'Already Verified';
            ?>
        </h1>
        
        <p class="message"><?php echo htmlspecialchars($message); ?></p>

        <?php 
        $loginUrl = $base_url . "/student/student_log_reg.php";
$resendUrl = $base_url . "/student/resend_verification.php";
        ?>

        <?php if ($messageType == 'success' || $messageType == 'info'): ?>
            <a href="<?php echo $loginUrl; ?>" class="button">Go to Login</a>
        <?php else: ?>
            <a href="<?php echo $resendUrl; ?>" class="resend-link">Resend Verification Email</a>
            <br>
            <a href="<?php echo $loginUrl; ?>" class="button">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>
