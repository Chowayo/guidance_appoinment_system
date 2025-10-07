<?php
session_start();
include "../db/dbconn.php";

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
                // Email is verified, proceed with login
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
  <title>Login Page</title>
<link href="../css/bootstrap.min.css" rel="stylesheet">
<script src="../js/jquery-3.6.0.min.js"></script>
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
    background: rgba(167, 255, 126, 0.50);
    width: 1100px;
    height: 620px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    display: flex;
    border-style: outset;
    border-color: #a0fda3ff;
}

.welcome-container {
    width: 40%;
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
    padding: 30px 50px;
    position: absolute;
    top: 0;
    left: 0;
    transition: all 0.6s ease-in-out;
    display: flex;
    flex-direction: column;
}

.login-form { z-index: 2; }
.register-form { opacity: 0; z-index: 1; }

.container-box.active .login-form {
    transform: translateX(-100%);
    opacity: 0;
}

.container-box.active .register-form {
    transform: translateX(0%);
    opacity: 1;
    z-index: 2;
}

.form-control {
    border-radius: 10px;
    margin-bottom: 6px;
    padding: 8px;
    font-size: 0.95rem;
}

.btn-custom {
    background: #646300ff;
    color: #fff;
    border-radius: 10px;
    transition: 0.3s;
    padding: 10px;
    font-size: 1rem;
    margin-top: 8px;
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

.password-strength {
    font-size: 0.78rem;
    margin-bottom: 2px;
    margin-top: 2px;
    min-height: 16px;
}

.strength-weak { color: #dc3545; font-weight: bold; }
.strength-medium { color: #ffc107; font-weight: bold; }
.strength-strong { color: #28a745; font-weight: bold; }

.password-requirements {
    font-size: 0.7rem;
    color: #666;
    margin-top: 0;
    margin-bottom: 6px;
    padding: 4px 8px;
    background: rgba(255,255,255,0.5);
    border-radius: 5px;
}

.requirement {
    display: block;
    margin: 0;
    line-height: 1.2;
}

.requirement.met {
    color: #28a745;
}

.requirement.unmet {
    color: #dc3545;
}

.form-container h2 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    margin-top: 0;
}

.resend-link {
    text-align: center;
    margin-top: 10px;
    font-size: 0.85rem;
}

.resend-link a {
    color: #646300ff;
    text-decoration: none;
}

.resend-link a:hover {
    text-decoration: underline;
}
  </style>
</head>
<body>

<div class="container-box" id="box">

  <div class="welcome-container">
    <img src="logo.gif" alt="School Logo" class="logo-gif">
    <h2 class="fw-bold" style="font-family:Georgia, serif">Welcome to Our Official School Website!</h2>
    <p>Please Login/Register your info:</p>
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-light" onclick="showLogin()">Login</button>
      <button class="btn btn-dark" onclick="showRegister()">Register</button>
    </div>
  </div>


  <div class="form-area">

    <div class="form-container login-form">
  <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <h2 style="font-family:Georgia, serif">Login</h2>
  <form action="student_log_reg.php" method="POST">
    <input type="text" name="student_id" class="form-control" placeholder="Student ID" required>
    <input type="password" name="password" class="form-control" placeholder="Password" required>
    <button type="submit" class="btn btn-custom w-100">Login</button>
  </form>
  
  <!-- ADD THIS -->
  <div class="text-center mt-3">
    <a href="forgot_password.php" style="color: #646300ff; text-decoration: none; font-size: 0.9rem;">
      Forgot Password?
    </a>
  </div>
  
  <div class="resend-link">
    <small>Didn't receive verification email? 
    <a href="resend_verification.php">Resend</a>
    </small>
  </div>
</div>

 
<div class="form-container register-form">
    <h2 style="font-family:Georgia, serif">Register</h2>
    <form action="student_function.php" method="POST" id="addStudent">
        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        <input type="number" name="student_id" class="form-control" placeholder="Student ID" required>
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
        <button type="submit" class="btn btn-custom w-100">Register</button>
    </form>
</div>
  </div>
</div>

<script>
  // Functions to switch between login and register forms
  function showLogin() {
    document.getElementById('box').classList.remove('active');
  }
  
  function showRegister() {
    document.getElementById('box').classList.add('active');
  }

  $(document).ready(function() {
    let isSubmitting = false; // Flag to prevent double submission
    
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
    
    // Form validation on submit
    $('#addStudent').on('submit', function(e) {
      e.preventDefault();
      
      // Prevent double submission
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
      
      // Set submitting flag
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
          // Clean up response
          response = response.trim();
          
          console.log('=== DEBUG INFO ===');
          console.log('Raw response:', response);
          console.log('Response length:', response.length);
          console.log('Contains "already registered"?', response.includes('already registered'));
          console.log('Contains "Registration successful"?', response.includes('Registration successful'));
          console.log('==================');
          
          // Check for success
          if (response.includes('Registration successful')) {
            Swal.fire({
              icon: 'success',
              title: 'Registration Successful!',
              html: 'Please check your email to verify your account.<br><small style="color: #666;">Check your spam folder if you don\'t see it.</small>',
              confirmButtonText: 'OK',
              confirmButtonColor: '#646300ff'
            }).then(() => {
              // Clear the form
              $('#addStudent')[0].reset();
              $('#passwordStrength').html('');
              $('#passwordMatch').html('');
              $('.requirement').removeClass('met').addClass('unmet').each(function() {
                if (this.id === 'req-length') $(this).html('✗ At least 8 characters');
                if (this.id === 'req-uppercase') $(this).html('✗ One uppercase letter');
                if (this.id === 'req-number') $(this).html('✗ One number');
              });
              showLogin(); // Switch to login form
            });
          } 
          // Check for already registered
          else if (response.includes('already registered')) {
            Swal.fire({
              icon: 'error',
              title: 'Already Registered',
              text: 'This Student ID or Email is already registered!'
            });
          }
          // Check for password validation errors
          else if (response.includes('Password validation failed')) {
            Swal.fire({
              icon: 'error',
              title: 'Invalid Password',
              html: response.replace(/\n/g, '<br>')
            });
          }
          // Generic error
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
          // Re-enable form submission
          isSubmitting = false;
          submitBtn.prop('disabled', false).text(originalText);
        }
      });
    });
  });
</script>

</body>
</html>