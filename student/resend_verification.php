<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
require_once __DIR__ . '/../db/dbconn.php';
require_once __DIR__ . '/../student_func/email_verification.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } else {

        $stmt = $conn->prepare("SELECT student_id, first_name, last_name, is_verified FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            if ($student['is_verified'] == 1) {
                $message = "This email is already verified. You can log in now.";
                $messageType = "info";
            } else {

                $verificationToken = bin2hex(random_bytes(32));
                $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $updateStmt = $conn->prepare("
                    UPDATE student 
                    SET verification_token = ?, token_expiry = ? 
                    WHERE email = ?
                ");
                $updateStmt->bind_param("sss", $verificationToken, $tokenExpiry, $email);

                if ($updateStmt->execute()) {
                    $studentName = $student['first_name'] . ' ' . $student['last_name'];
                    $emailResult = sendVerificationEmail($email, $studentName, $verificationToken);

                    if ($emailResult['success']) {
                        $message = "A new verification email has been sent! Please check your inbox or spam folder.";
                        $messageType = "success";
                    } else {
                        $message = "Email sending failed: " . htmlspecialchars($emailResult['message']);
                        $messageType = "error";
                    }
                } else {
                    $message = "Failed to update verification token. Please try again.";
                    $messageType = "error";
                }

                $updateStmt->close();
            }
        } else {
            $message = "No account found with this email address.";
            $messageType = "error";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Student Portal</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a7ff7e 0%, #646300ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .resend-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        .icon {
            text-align: center;
            font-size: 60px;
            margin-bottom: 20px;
            color: #646300ff;
        }
        h2 {
            text-align: center;
            color: #2d5016;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #a7ff7e;
            box-shadow: 0 0 0 0.2rem rgba(167, 255, 126, 0.25);
        }
        .btn-custom {
            background: linear-gradient(135deg, #6b7c00 0%, #8a9e00 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            cursor: pointer;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #8a9e00 0%, #a8bd00 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(100, 99, 0, 0.3);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #6b7c00;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .back-link a:hover {
            color: #8a9e00;
            text-decoration: underline;
        }
        .info-box {
            background-color: #f0f8ff;
            border-left: 4px solid #6b7c00;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="resend-container">
        <div class="icon">📧</div>
        <h2>Resend Verification Email</h2>
        <p class="subtitle">Enter your registered email to receive a new verification link</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'info' ? 'info' : 'danger'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType !== 'success' && $messageType !== 'info'): ?>
            <div class="info-box">
                <strong>Note:</strong> Check your spam/junk folder if you don’t see the email in your inbox.
            </div>

            <form method="POST" action="">
                <input 
                    type="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="Enter your registered email address" 
                    required
                >
                <button type="submit" class="btn-custom">Resend Verification Email</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="student_log_reg.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>
