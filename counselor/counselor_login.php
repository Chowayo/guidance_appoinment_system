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
    background-image: url('Backdrop.jpg');
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
      background: rgba(167, 255, 126, 0.50); /* same green, 85% opacity */
      width: 850px;
      height: 500px;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.3);
      display: flex;
      border-style: outset;
      border-color: #a0fda3ff;
    }
    .welcome-container {
      width: 50%;
      background: rgba(167, 255, 126, 0.100);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
      transition: all 0.6s ease-in-out;
      border-style: outset;
      border-radius: 15px;
      border-color: #a0fda3ff;
    }

    .form-area {
      width: 60%;
      position: relative;
      overflow: hidden;
    }

    .form-container {
      width: 100%;
      height: 100%;
      padding: 50px;
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
      border-radius: 10px;
      margin-bottom: 15px;
    }
    .btn-custom {
      background: #646300ff;
      color: #fff;
      border-radius: 10px;
      transition: 0.3s;
    }
    .btn-custom:hover {
      background: #dfcc29ff;
    }
    .logo-gif {
    width: 200px;
    height: auto; 
    margin-bottom: 20px;
    mix-blend-mode: screen;
    }
  </style>
</head>
<body>

<div class="container-box" id="box">

  <div class="welcome-container">
    <img src="logo.gif" alt="School Logo" class="logo-gif">
    <h2 class="fw-bold">Counselor Portal</h2>
    <p>Restricted Site: Authorized Staff Access Only</p>
    <div class="d-flex flex-column gap-2 mt-3">
      <button class="btn btn-light" onclick="showLogin()">Counselor Login</button>
      <button class="btn btn-dark" onclick="showNotice()">Notice</button>
    </div>
  </div>


  <div class="form-area">


    <div class="form-container login-form">
  <h2 style="font-family:Georgia, serif ">Counselor Login</h2>
  <form action="counselor_function.php" method="POST">
    <input type="text" name="counselor_id" class="form-control" placeholder="Counselor ID" required>
    <input type="password" name="password" class="form-control" placeholder="Password" required>
    <button type="submit" class="btn btn-custom w-100">Login</button>
  </form>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="mt-3 text-danger fw-bold">
      <?= $_SESSION['error']; ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
</div>


    <div class="form-container notice-form">
      <h2>System Notice</h2>
      <p class="mt-3">⚠ Only authorized counseloristrators can access this system.<br>
      All activities are monitored and logged.</p>
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
        timer: 2000,              // auto-close after 2 seconds
        showConfirmButton: false, // hide OK button
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