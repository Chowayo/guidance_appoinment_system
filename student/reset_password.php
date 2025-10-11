<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
include '../db/dbconn.php';

$message = '';
$messageType = '';
$validToken = false;
$email = '';

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token
    $query = "SELECT email, first_name FROM student WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $validToken = true;
        $student = $result->fetch_assoc();
        $email = $student['email'];
    } else {
        $message = "Invalid or expired reset link. Please request a new password reset.";
        $messageType = "error";
    }
    
    $stmt->close();
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Verify token again
    $query = "SELECT email FROM student WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Validate password
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            // Hash new password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Update password and clear reset token
            $updateQuery = "UPDATE student SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ss", $hashed_password, $student['email']);
            
            if ($updateStmt->execute()) {
                $message = "success";
                $messageType = "success";
            } else {
                $message = "Failed to reset password. Please try again.";
                $messageType = "error";
            }
            
            $updateStmt->close();
        } else {
            $message = implode("<br>", $errors);
            $messageType = "error";
            $validToken = true;
            $email = $student['email'];
        }
    } else {
        $message = "Invalid or expired reset link.";
        $messageType = "error";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script src="../js/sweetalert2@11.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #a7ff7e 0%, #646300ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        
        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        
        .reset-icon {
            text-align: center;
            font-size: 60px;
            color: #646300;
            margin-bottom: 20px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }
        
        .btn-reset {
            background: #646300;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-reset:hover {
            background: #dfcc29;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(100, 99, 0, 0.3);
        }
        
        .password-strength {
            font-size: 0.85rem;
            margin-top: -10px;
            margin-bottom: 10px;
            min-height: 20px;
        }
        
        .strength-weak { color: #dc3545; font-weight: bold; }
        .strength-medium { color: #ffc107; font-weight: bold; }
        .strength-strong { color: #28a745; font-weight: bold; }
        
        .password-requirements {
            font-size: 0.75rem;
            color: #666;
            margin-top: -10px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .requirement {
            display: block;
            margin: 3px 0;
        }
        
        .requirement.met { color: #28a745; }
        .requirement.unmet { color: #dc3545; }
        
        .error-container {
            text-align: center;
        }
        
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .btn-link {
            display: inline-block;
            margin-top: 20px;
            color: #646300;
            text-decoration: none;
            font-weight: bold;
        }
        
        .btn-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if ($messageType == 'success'): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset Successful!',
                    text: 'Your password has been updated. You can now login with your new password.',
                    confirmButtonColor: '#646300'
                }).then(() => {
                    window.location.href = 'student_log_reg.php';
                });
            </script>
        <?php elseif ($validToken): ?>
            <div class="reset-icon">🔑</div>
            <h2>Reset Your Password</h2>
            <p class="subtitle">Enter your new password below</p>
            
            <?php if (!empty($message) && $messageType == 'error'): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                
                <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required>
                
                <div class="password-strength" id="passwordStrength"></div>
                
                <div class="password-requirements" id="passwordRequirements">
                    <span class="requirement unmet" id="req-length">✗ At least 8 characters</span>
                    <span class="requirement unmet" id="req-uppercase">✗ One uppercase letter</span>
                    <span class="requirement unmet" id="req-number">✗ One number</span>
                </div>
                
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                <div class="password-strength" id="passwordMatch"></div>
                
                <button type="submit" name="reset_password" class="btn-reset">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h2>Invalid Reset Link</h2>
                <p style="color: #666; margin: 20px 0;">
                    <?php echo !empty($message) ? htmlspecialchars($message) : 'This password reset link is invalid or has expired.'; ?>
                </p>
                
                <a href="forgot_password.php" class="btn-link">Request New Reset Link</a>
                <br>
                <a href="student_log_reg.php" class="btn-link">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            // Real-time password validation
            $('#password').on('keyup', function() {
                const password = $(this).val();
                
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                
                $('#req-length').toggleClass('met', hasLength).toggleClass('unmet', !hasLength)
                    .html(hasLength ? '✓ At least 8 characters' : '✗ At least 8 characters');
                
                $('#req-uppercase').toggleClass('met', hasUppercase).toggleClass('unmet', !hasUppercase)
                    .html(hasUppercase ? '✓ One uppercase letter' : '✗ One uppercase letter');
                
                $('#req-number').toggleClass('met', hasNumber).toggleClass('unmet', !hasNumber)
                    .html(hasNumber ? '✓ One number' : '✗ One number');
                
                const strengthDiv = $('#passwordStrength');
                if (password.length === 0) {
                    strengthDiv.html('');
                } else if (hasLength && hasUppercase && hasNumber) {
                    strengthDiv.html('<span class="strength-strong">Strong password!</span>');
                } else if (hasLength || hasUppercase || hasNumber) {
                    strengthDiv.html('<span class="strength-medium">Medium strength</span>');
                } else {
                    strengthDiv.html('<span class="strength-weak">Weak password</span>');
                }
            });
            
            // Check password match
            $('#confirm_password').on('keyup', function() {
                const password = $('#password').val();
                const confirm = $(this).val();
                const matchDiv = $('#passwordMatch');
                
                if (confirm.length === 0) {
                    matchDiv.html('');
                } else if (password === confirm) {
                    matchDiv.html('<span class="strength-strong">Passwords match!</span>');
                } else {
                    matchDiv.html('<span class="strength-weak">Passwords do not match</span>');
                }
            });
        });
    </script>
</body>
</html>