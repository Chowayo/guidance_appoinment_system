<?php
session_start();
include "../db/dbconn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = intval($_POST["student_id"]);
    $password   = trim($_POST["password"]);

    
    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, password, grade_level FROM student WHERE student_id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $grade_level);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            
            $_SESSION["student_id"] = $id;
            $_SESSION["first_name"] = $first_name;
            $_SESSION["last_name"]  = $last_name;
            $_SESSION["email"]      = $email;
            $_SESSION["grade_level"]= $grade_level;

            header("Location: landing_page.php");
            exit;
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ student not found!";
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
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
      font-family: 'Poppins', sans-serif;
    }
    .container-box {
      background: #a7ff7eff;
      width: 850px;
      height: 500px;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(255, 0, 0, 0.3);
      display: flex;
    }
    .welcome-container {
      width: 50%;
      background: linear-gradient(135deg, #0b8600ff, #f3f8a7ff);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
      transition: all 0.6s ease-in-out;
    }
    .form-area {
      width: 50%;
      position: relative;
      overflow: hidden;
    }
    .form-container {
      width: 100%;
      height: 100%;
      padding: 50px;
      position: absolute;
      top: 0;
      left: 0;
      transition: all 0.6s ease-in-out;
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
    <h2 class= "fw-bold" style="font-family:Georgia, serif ">Welcome to Official School Guidance Appointment Website!</h2>
    <p>Please Login/Register your info:</p>
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-light" onclick="showLogin()">Login</button>
      <button class="btn btn-dark" onclick="showRegister()">Register</button>
    </div>
  </div>


  <div class="form-area">

    <div class="form-container login-form">
      <?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
      <h2 style="font-family:Georgia, serif ">Login</h2>
      <form action="student_log_reg.php" method="POST">
        <input type="text" name="student_id" class="form-control" placeholder="Student ID" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" class="btn btn-custom w-100">Login</button>
      </form>
    </div>

 
    <div class="form-container register-form">
      <h2 style="font-family:Georgia, serif ">Register</h2>
      <form action="student_function.php" method="POST" id=addStudent>
        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        <input type="number" name="student_id" class="form-control" placeholder="Student ID" required>
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
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
  function showLogin() {
    document.getElementById('box').classList.remove('active');
  }
  function showRegister() {
    document.getElementById('box').classList.add('active');
  }

</script>

</body>
</html>
