<?php
include '../session_config.php';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Counselor Portal</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="../js/jquery-3.6.0.min.js"></script>
  
  <style>
    body {
      background-image: url('Backdrop2.jpg');
      background-size: cover;         
      background-repeat: no-repeat;   
      background-attachment: fixed;   
      background-position: center;    
      height: 100vh;                  
      display: flex;                  
      justify-content: center;
      align-items: center;
      margin: 0;
    }

    .reset-container {
      background: rgba(255, 255, 255, 0.95);
      max-width: 500px;
      width: 90%;
      border-radius: 25px;
      padding: 40px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.3);
      border: 3px solid rgba(167, 255, 126, 0.6);
      backdrop-filter: blur(50px);
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo-container img {
      width: 100px;
      height: auto;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    h2 {
      font-family: Georgia, serif;
      color: #2d5016;
      text-align: center;
      margin-bottom: 10px;
      font-weight: 700;
    }

    .subtitle {
      text-align: center;
      color: #666;
      margin-bottom: 30px;
      font-size: 0.9rem;
    }

    .badge-counselor {
      background: linear-gradient(135deg, #6b7c00 0%, #8a9e00 100%);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 15px;
    }

    .form-control {
      border-radius: 12px;
      margin-bottom: 15px;
      border: 2px solid #e0e0e0;
      padding: 12px 15px;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: #a7ff7e;
      box-shadow: 0 0 0 0.2rem rgba(167, 255, 126, 0.25);
    }

    .btn-custom {
      background: linear-gradient(135deg, #6b7c00 0%, #8a9e00 100%);
      color: #fff;
      border-radius: 12px;
      padding: 12px;
      font-weight: 600;
      border: none;
      transition: all 0.3s;
      box-shadow: 0 4px 10px rgba(100, 99, 0, 0.3);
      cursor: pointer;
      width: 100%;
    }

    .btn-custom:hover {
      background: linear-gradient(135deg, #8a9e00 0%, #a8bd00 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(100, 99, 0, 0.4);
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
      transition: all 0.3s;
    }

    .back-link a:hover {
      color: #8a9e00;
      text-decoration: underline;
    }

    .alert {
      border-radius: 10px;
      margin-bottom: 20px;
      padding: 12px;
      font-size: 0.9rem;
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

    .info-box strong {
      color: #2d5016;
    }
  </style>
</head>
<body>

<div class="reset-container">
  <div class="logo-container">
    <img src="logo.jpg" alt="School Logo">
    <div class="text-center">
      <span class="badge-counselor">COUNSELOR PORTAL</span>
    </div>
  </div>

  <h2>🔑 Reset Password</h2>
  <p class="subtitle">Enter your email to receive a password reset link</p>

  <?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <?php if(!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>

  <div class="info-box">
    <strong>Note:</strong> This is for authorized counselors only. Make sure to check your spam/junk folder if you don't see the email in your inbox.
  </div>

  <form action="counselor_forgot_password.php" method="POST">
    <input 
      type="email" 
      name="email" 
      class="form-control" 
      placeholder="Enter your registered email address" 
      required
      autocomplete="email"
    >
    <button type="submit" class="btn btn-custom">Send Reset Link</button>
  </form>

  <div class="back-link">
    <a href="counselor_login.php">← Back to Login</a>
  </div>
</div>

</body>
</html>