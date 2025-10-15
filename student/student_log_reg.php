<?php
include '../session_config.php';
include '../db/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = intval($_POST["student_id"]);
    $password   = trim($_POST["password"]);

    
    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, password, grade_level, is_verified FROM student WHERE student_id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $grade_level, $is_verified);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            
            // Check if email is verified
            if ($is_verified == 0) {
                $error = "Please verify your email before logging in. Check your inbox for the verification link.";
            } else {
                $_SESSION["student_id"] = $id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"]  = $last_name;
                $_SESSION["email"]      = $email;
                $_SESSION["grade_level"]= $grade_level;

                header("Location: landing_page.php");
                exit;
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Student not found!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="../js/jquery-3.6.0.min.js"></script>
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
      padding: 35px 30px;
      position: absolute;
      top: 0;
      transition: all 0.6s ease-in-out;
      display: flex;
      flex-direction: column;
    }

    .login-form { left: 0; z-index: 2; }
    .register-form { 
      left: 100%; 
      opacity: 0; 
      z-index: 1;
      padding: 25px 30px;
    }

    .container-box.active .login-form {
      left: -100%;
      opacity: 0;
      z-index: 1;
    }
    
    .container-box.active .register-form {
      left: 0;
      opacity: 1;
      z-index: 2;
    }

    .form-control {
      border-radius: 10px;
      margin-bottom: 8px;
      border: 2px solid #e0e0e0;
      padding: 8px 12px;
      transition: all 0.3s;
      font-size: 0.85rem;
      height: 36px;
    }

    .form-control:focus {
      border-color: #a7ff7e;
      box-shadow: 0 0 0 0.2rem rgba(167, 255, 126, 0.25);
    }

    .form-control::placeholder {
      font-size: 0.8rem;
    }

    .btn-custom {
      background: linear-gradient(135deg, #6b7c00 0%, #8a9e00 100%);
      color: #fff;
      border-radius: 10px;
      padding: 10px;
      font-weight: 600;
      border: none;
      transition: all 0.3s;
      box-shadow: 0 4px 10px rgba(100, 99, 0, 0.3);
      cursor: pointer;
      width: 100%;
      font-size: 0.9rem;
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

    .login-form h2, .register-form h2 {
      font-family: Georgia, serif;
      color: #2d5016;
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 1.6rem;
    }

    .forgot-link {
      text-align: center;
      margin-top: 12px;
    }

    .forgot-link a {
      color: #6b7c00;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.3s;
    }

    .forgot-link a:hover {
      color: #8a9e00;
      text-decoration: underline;
    }

    .form-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      display: block;
    }

    .password-strength {
      font-size: 0.75rem;
      margin-bottom: 5px;
      margin-top: -3px;
      min-height: 16px;
    }

    .strength-weak { color: #dc3545; font-weight: bold; }
    .strength-medium { color: #ffc107; font-weight: bold; }
    .strength-strong { color: #28a745; font-weight: bold; }

    .password-requirements {
      font-size: 0.7rem;
      color: #666;
      margin-top: -3px;
      margin-bottom: 6px;
      padding: 6px 10px;
      background: rgba(167, 255, 126, 0.1);
      border-radius: 6px;
      border: 1px solid rgba(167, 255, 126, 0.3);
      line-height: 1.3;
    }

    .requirement {
      display: block;
      margin: 1px 0;
    }

    .requirement.met {
      color: #28a745;
    }

    .requirement.unmet {
      color: #dc3545;
    }

    .resend-link {
      text-align: center;
      margin-top: 8px;
      font-size: 0.75rem;
    }

    .resend-link a {
      color: #6b7c00;
      text-decoration: none;
      font-weight: 600;
    }

    .resend-link a:hover {
      color: #8a9e00;
      text-decoration: underline;
    }

    .alert {
      border-radius: 8px;
      margin-bottom: 15px;
      padding: 10px;
      font-size: 0.85rem;
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

    select.form-control {
      font-size: 0.85rem;
      padding: 8px 12px;
    }

    .mb-3 {
      margin-bottom: 8px !important;
    }

    .invalid-feedback {
      font-size: 0.7rem;
      margin-top: -5px;
      margin-bottom: 5px;
    }

    /* Compact register form */
    .register-form .form-control {
      margin-bottom: 6px;
    }

    .register-form .password-strength {
      margin-bottom: 4px;
    }

    .register-form .password-requirements {
      margin-bottom: 5px;
    }

    .register-form .btn-custom {
      margin-top: 8px;
    }
  </style>
</head>
<body>

<div class="container-box" id="box">

  <div class="welcome-container">
    <div class="logo-container">
      <img src="logo.jpg" alt="School Logo" class="logo-gif">
    </div>
    <h2 class="fw-bold">Welcome to Our Guidance Counselor Appointment Portal!</h2>
    <p>Please Login/Register your info</p>
    <div class="d-flex flex-column gap-2 mt-3">
      <button class="btn btn-light" onclick="showLogin()">Student Login</button>
      <button class="btn btn-dark" onclick="showRegister()">Register</button>
    </div>
  </div>

  <div class="form-area">

    <!-- Login Form -->
    <div class="form-container login-form">
      <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <h2>Student Login</h2>
      <form action="student_log_reg.php" method="POST">
        <input type="text" name="student_id" class="form-control" placeholder="Student ID" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" class="btn btn-custom">Login</button>
      </form>
      
      <div class="forgot-link">
        <a href="forgot_password.php">Forgot Password?</a>
      </div>
      
      <div class="resend-link">
        <small>Didn't receive verification email? 
        <a href="resend_verification.php">Resend</a>
        </small>
      </div>
    </div>

    <!-- Register Form -->
    <div class="form-container register-form">
      <h2>Register</h2>
      <form action="student_function.php" method="POST" id="addStudent">
        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        
        <div class="mb-3">
          <input type="text" class="form-control" id="student_id" name="student_id" 
                 required pattern="[0-9]{9}" maxlength="9" minlength="9"
                 placeholder="9-digit Student ID" title="Student ID must be exactly 9 digits">
          <div class="invalid-feedback">Student ID must be exactly 9 digits.</div>
        </div>
        
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        <div class="password-strength" id="passwordStrength"></div>
        
        <div class="password-requirements" id="passwordRequirements">
          <span class="requirement unmet" id="req-length">✗ At least 8 characters</span>
          <span class="requirement unmet" id="req-uppercase">✗ One uppercase letter</span>
          <span class="requirement unmet" id="req-number">✗ One number</span>
        </div>
        
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
        <div class="password-strength" id="passwordMatch"></div>
        
        <select name="grade_level" class="form-control" required>
          <option value="" disabled selected>Select Grade Level</option>
          <option value="Grade 1">Grade 1</option>
          <option value="Grade 2">Grade 2</option>
          <option value="Grade 3">Grade 3</option>
          <option value="Grade 4">Grade 4</option>
          <option value="Grade 5">Grade 5</option>
          <option value="Grade 6">Grade 6</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
          <option value="Grade 11">Grade 11</option>
          <option value="Grade 12">Grade 12</option>
        </select>
        
        <button type="submit" class="btn btn-custom">Register</button>
      </form>
    </div>

  </div>
</div>

<script>
  function showLogin() {
    document.getElementById('box').classList.remove('active');
  }
  
  function showRegister() {
    document.getElementById('box').classList.add('active');
  }

  $(document).ready(function() {
    let isSubmitting = false;

    document.addEventListener('DOMContentLoaded', function() {
      const studentIdInput = document.getElementById('student_id');
      
      if (studentIdInput) {
        studentIdInput.addEventListener('input', function(e) {
          this.value = this.value.replace(/[^0-9]/g, '');
          
          if (this.value.length > 9) {
            this.value = this.value.slice(0, 9);
          }
          
          if (this.value.length === 9) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
          } else {
            this.classList.remove('is-valid');
            if (this.value.length > 0) {
              this.classList.add('is-invalid');
            }
          }
        });
        
        const form = studentIdInput.closest('form');
        if (form) {
          form.addEventListener('submit', function(e) {
            const studentId = studentIdInput.value;
            
            if (studentId.length !== 9 || !/^\d{9}$/.test(studentId)) {
              e.preventDefault();
              studentIdInput.classList.add('is-invalid');
              
              Swal.fire({
                icon: 'error',
                title: 'Invalid Student ID',
                text: 'Student ID must be exactly 9 digits!',
                confirmButtonColor: '#dc3545'
              });
              
              return false;
            }
          });
        }
      }
    });
    
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
    
    $('#addStudent').on('submit', function(e) {
      e.preventDefault();
      
      if (isSubmitting) {
        console.log('Already submitting, please wait...');
        return false;
      }
      
      const password = $('#password').val();
      const confirm = $('#confirm_password').val();
      
      if (password.length < 8) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Password',
          text: 'Password must be at least 8 characters long!'
        });
        return false;
      }
      
      if (!/[A-Z]/.test(password)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Password',
          text: 'Password must contain at least one uppercase letter!'
        });
        return false;
      }
      
      if (!/[0-9]/.test(password)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Password',
          text: 'Password must contain at least one number!'
        });
        return false;
      }
      
      if (password !== confirm) {
        Swal.fire({
          icon: 'error',
          title: 'Passwords Do Not Match',
          text: 'Please make sure both passwords are the same!'
        });
        return false;
      }
      
      isSubmitting = true;
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.text();
      submitBtn.prop('disabled', true).text('Registering...');
      
      const formData = new FormData(this);
      
      $.ajax({
        url: 'student_function.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          response = response.trim();
          
          console.log('=== DEBUG INFO ===');
          console.log('Raw response:', response);
          console.log('Response length:', response.length);
          console.log('Contains "already registered"?', response.includes('already registered'));
          console.log('Contains "Registration successful"?', response.includes('Registration successful'));
          console.log('==================');
          
          if (response.includes('Registration successful')) {
            Swal.fire({
              icon: 'success',
              title: 'Registration Successful!',
              html: 'Please check your email to verify your account.<br><small style="color: #666;">Check your spam folder if you don\'t see it.</small>',
              confirmButtonText: 'OK',
              confirmButtonColor: '#646300ff'
            }).then(() => {
              $('#addStudent')[0].reset();
              $('#passwordStrength').html('');
              $('#passwordMatch').html('');
              $('.requirement').removeClass('met').addClass('unmet').each(function() {
                if (this.id === 'req-length') $(this).html('✗ At least 8 characters');
                if (this.id === 'req-uppercase') $(this).html('✗ One uppercase letter');
                if (this.id === 'req-number') $(this).html('✗ One number');
              });
              showLogin();
            });
          } 
          else if (response.includes('already registered')) {
            Swal.fire({
              icon: 'error',
              title: 'Already Registered',
              text: 'This Student ID or Email is already registered!'
            });
          }
          else if (response.includes('Password validation failed')) {
            Swal.fire({
              icon: 'error',
              title: 'Invalid Password',
              html: response.replace(/\n/g, '<br>')
            });
          }
          else {
            Swal.fire({
              icon: 'error',
              title: 'Registration Failed',
              text: response
            });
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Please try again.'
          });
        },
        complete: function() {
          isSubmitting = false;
          submitBtn.prop('disabled', false).text(originalText);
        }
      });
    });
  });
</script>

</body>
</html>