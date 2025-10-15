<?php
include '../session_config.php';
include '../db/dbconn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Counselor Portal</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="../js/sweetalert2@11.js"></script>

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

    .container-box {
      background: rgba(255, 255, 255, 0.10);
      width: 900px;
      height: 550px;
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      display: flex;
      border: 3px solid rgba(167, 255, 126, 0.6);
      backdrop-filter: blur(50px);
    }
    
    .welcome-container {
      width: 45%;
      background: linear-gradient(135deg, rgba(167, 255, 126, 0.3) 0%, rgba(160, 253, 163, 0.5) 100%);
      color: #2d5016;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
      transition: all 0.6s ease-in-out;
      border-right: 2px solid rgba(167, 255, 126, 0.4);
      position: relative;
    }

    .welcome-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.3) 0%, transparent 50%);
      pointer-events: none;
    }

    .logo-container {
      background: white;
      padding: 20px;
      border-radius: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }

    .form-area {
      width: 55%;
      position: relative;
      overflow: hidden;
      background: white;
    }

    .form-container {
      width: 100%;
      height: 100%;
      padding: 50px 40px;
      position: absolute;
      top: 0;
      transition: all 0.6s ease-in-out;
    }

    .login-form { left: 0; z-index: 2; }
    .notice-form { left: 100%; opacity: 0; z-index: 1; }

    .container-box.active .login-form {
      left: -100%;
      opacity: 0;
      z-index: 1;
    }
    
    .container-box.active .notice-form {
      left: 0;
      opacity: 1;
      z-index: 2;
    }

    .form-control {
      border-radius: 12px;
      margin-bottom: 20px;
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

    .logo-gif {
      width: 150px;
      height: auto; 
      display: block;
      filter: brightness(1.1) contrast(0.95);
    }

    .welcome-container h2 {
      font-weight: 700;
      color: #2d5016;
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }

    .welcome-container p {
      color: #3d6020;
      font-size: 14px;
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }

    .btn-light {
      background: white;
      border: 2px solid #a7ff7e;
      color: #2d5016;
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
      cursor: pointer;
    }

    .btn-light:hover {
      background: #a7ff7e;
      color: #2d5016;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(167, 255, 126, 0.4);
    }

    .btn-dark {
      background: #2d5016;
      border: 2px solid #2d5016;
      color: white;
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
      cursor: pointer;
    }

    .btn-dark:hover {
      background: #3d6020;
      border-color: #3d6020;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(45, 80, 22, 0.4);
    }

    .login-form h2, .notice-form h2 {
      font-family: Georgia, serif;
      color: #2d5016;
      margin-bottom: 30px;
      font-weight: 700;
    }

    .notice-form p {
      color: #555;
      line-height: 1.8;
      font-size: 15px;
    }

    .forgot-link {
      text-align: center;
      margin-top: 15px;
    }

    .forgot-link a {
      color: #6b7c00;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .forgot-link a:hover {
      color: #8a9e00;
      text-decoration: underline;
    }

    .back-btn {
      color: #6b7c00;
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .back-btn:hover {
      color: #8a9e00;
    }

    .form-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      display: block;
    }

    .button-group {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
  </style>
</head>
<body>

<div class="container-box" id="box">

  <div class="welcome-container">
    <div class="logo-container">
      <img src="logo.jpg" alt="School Logo" class="logo-gif">
    </div>
    <h2 class="fw-bold">Counselor Portal</h2>
    <p>Restricted Site: Authorized Staff Access Only</p>
    <div class="d-flex flex-column gap-2 mt-3">
      <button class="btn btn-light" onclick="showLogin()">Counselor Login</button>
      <button class="btn btn-dark" onclick="showNotice()">Notice</button>
    </div>
  </div>

  <div class="form-area">

    <!-- Login Form -->
    <div class="form-container login-form">
      <h2>Counselor Login</h2>
      <form action="counselor_function.php" method="POST">
        <input type="text" name="counselor_id" class="form-control" placeholder="Counselor ID" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" class="btn btn-custom">Login</button>
      </form>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="mt-3 text-danger fw-bold">
          <?= $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <div class="forgot-link">
        <a href="forgot_password.php">Forgot Password?</a>
      </div>
    </div>

    <!-- Notice Form -->
    <div class="form-container notice-form">
      <h2>System Notice</h2>
      <p class="mt-3">⚠ Only authorized counselors can access this system.<br><br>
      All activities are monitored and logged for security purposes.<br><br>
      If you experience any issues accessing your account, please contact the system administrator.</p>
      <div style="margin-top: 30px;">
        <a href="javascript:void(0);" class="back-btn" onclick="showLogin()">← Back to Login</a>
      </div>
    </div>

  </div>
</div>

<script>
  function showLogin() {
    document.getElementById('box').classList.remove('active');
  }
  
  function showNotice() {
    document.getElementById('box').classList.add('active');
  }

  <?php if (isset($_SESSION['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Login Successful',
        text: '<?= $_SESSION['success']; ?>',
        timer: 2000, 
        showConfirmButton: false,
        position: 'center'
    }).then(() => {
        <?php if (isset($_GET['redirect']) && $_GET['redirect'] === 'dashboard'): ?>
            window.location.href = "counselor_dashboard.php";
        <?php elseif (isset($_GET['redirect']) && $_GET['redirect'] === 'change_password'): ?>
            window.location.href = "change_password.php";
        <?php endif; ?>
    });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
</script>

</body>
</html>