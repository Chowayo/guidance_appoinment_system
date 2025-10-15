<?php
include '../session_config.php';
include '../db/dbconn.php';

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

$counselor_id = $_SESSION['counselor_id'];
$message = '';
$messageType = '';

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    }
    
    if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = "New password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[0-9]/', $new_password)) {
        $errors[] = "New password must contain at least one number";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if ($current_password === $new_password) {
        $errors[] = "New password must be different from current password";
    }
    
    if (empty($errors)) {
        // Get current password from database
        $stmt = $conn->prepare("SELECT password FROM counselor WHERE counselor_id = ?");
        $stmt->bind_param("i", $counselor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row && password_verify($current_password, $row['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $updateStmt = $conn->prepare("UPDATE counselor SET password = ? WHERE counselor_id = ?");
            $updateStmt->bind_param("si", $hashed_password, $counselor_id);
            
            if ($updateStmt->execute()) {
                $message = "Password updated successfully!";
                $messageType = "success";
                $success = true;
            } else {
                $message = "Failed to update password. Please try again.";
                $messageType = "error";
            }
            $updateStmt->close();
        } else {
            $message = "Current password is incorrect";
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - Counselor</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="../js/sweetalert2@11.js"></script>
  <style>
    body {
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .password-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      padding: 40px;
      max-width: 500px;
      width: 100%;
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .password-container h2 {
      color: #333;
      font-weight: 700;
      text-align: center;
      margin-bottom: 30px;
    }

    .form-label {
      font-weight: 600;
      color: #555;
      margin-bottom: 8px;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px;
      border: 1px solid #ddd;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    .form-control:focus {
      border-color: #28a745;
      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .mb-3 {
      margin-bottom: 20px;
    }

    .password-requirements {
      font-size: 0.85rem;
      color: #666;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .requirement {
      display: block;
      margin: 5px 0;
    }

    .requirement.met { 
      color: #28a745; 
      font-weight: 600;
    }

    .requirement.unmet { 
      color: #dc3545; 
      font-weight: 600;
    }

    .btn-change {
      background: #28a745;
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

    .btn-change:hover {
      background: #218838;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-back {
      background: #6c757d;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
      width: 100%;
      margin-top: 10px;
      display: block;
      text-decoration: none;
      text-align: center;
    }

    .btn-back:hover {
      background: #5a6268;
      text-decoration: none;
      color: white;
    }

    .button-group {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .alert {
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .logo {
      height: 60px;
      width: auto;
      display: block;
      margin: 0 auto 20px;
    }

    .password-strength {
      font-size: 0.85rem;
      margin-top: 5px;
      min-height: 20px;
    }

    .strength-weak { color: #dc3545; font-weight: bold; }
    .strength-medium { color: #ffc107; font-weight: bold; }
    .strength-strong { color: #28a745; font-weight: bold; }
  </style>
</head>
<body>

<div class="password-container">
  <img src="../pics/logo.jpg" alt="Logo" class="logo">
  <h2>Change Password</h2>

  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <form id="changePasswordForm" method="POST" action="">
    <!-- Current Password -->
    <div class="mb-3">
      <label for="current_password" class="form-label">Current Password</label>
      <input type="password" name="current_password" id="current_password" 
             class="form-control" placeholder="Enter your current password" required>
      <small class="text-muted">Required to verify your identity</small>
    </div>

    <!-- New Password -->
    <div class="mb-3">
      <label for="new_password" class="form-label">New Password</label>
      <input type="password" name="new_password" id="new_password" 
             class="form-control" placeholder="Enter new password" required>
      <div class="password-strength" id="passwordStrength"></div>
      
      <div class="password-requirements">
        <span class="requirement unmet" id="req-length">✗ At least 8 characters</span>
        <span class="requirement unmet" id="req-uppercase">✗ One uppercase letter</span>
        <span class="requirement unmet" id="req-number">✗ One number</span>
      </div>
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" id="confirm_password" 
             class="form-control" placeholder="Re-enter new password" required>
      <div class="password-strength" id="passwordMatch"></div>
    </div>

    <div class="button-group">
      <button type="submit" class="btn-change">Update Password</button>
      <a href="counselor_dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>
  </form>
  <?php else: ?>
    <div style="text-align: center; padding: 40px 0;">
      <h3 style="color: #28a745; margin-bottom: 20px;">Password Changed Successfully! ✓</h3>
      <p style="color: #666; margin-bottom: 30px;">Your password has been updated. You will be redirected shortly...</p>
      <a href="counselor_dashboard.php" class="btn-back" style="max-width: 300px; margin: 0 auto; display: block;">Back to Dashboard</a>
    </div>
    <script>
      setTimeout(function() {
        window.location.href = 'counselor_dashboard.php';
      }, 3000);
    </script>
  <?php endif; ?>

<script>
document.getElementById('new_password').addEventListener('keyup', function() {
  const password = this.value;
  
  const hasLength = password.length >= 8;
  const hasUppercase = /[A-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  
  updateRequirement('req-length', hasLength, 'At least 8 characters');
  updateRequirement('req-uppercase', hasUppercase, 'One uppercase letter');
  updateRequirement('req-number', hasNumber, 'One number');
  
  // Show strength indicator
  const strengthDiv = document.getElementById('passwordStrength');
  if (password.length === 0) {
    strengthDiv.innerHTML = '';
  } else if (hasLength && hasUppercase && hasNumber) {
    strengthDiv.innerHTML = '<span class="strength-strong">Strong password!</span>';
  } else if (hasLength || hasUppercase || hasNumber) {
    strengthDiv.innerHTML = '<span class="strength-medium">Medium strength</span>';
  } else {
    strengthDiv.innerHTML = '<span class="strength-weak">Weak password</span>';
  }
});

document.getElementById('confirm_password').addEventListener('keyup', function() {
  const password = document.getElementById('new_password').value;
  const confirm = this.value;
  const matchDiv = document.getElementById('passwordMatch');
  
  if (confirm.length === 0) {
    matchDiv.innerHTML = '';
  } else if (password === confirm) {
    matchDiv.innerHTML = '<span class="strength-strong">Passwords match!</span>';
  } else {
    matchDiv.innerHTML = '<span class="strength-weak">Passwords do not match</span>';
  }
});

function updateRequirement(id, met, text) {
  const element = document.getElementById(id);
  if (met) {
    element.classList.remove('unmet');
    element.classList.add('met');
    element.innerHTML = '✓ ' + text;
  } else {
    element.classList.remove('met');
    element.classList.add('unmet');
    element.innerHTML = '✗ ' + text;
  }
}
</script>

</body>
</html>