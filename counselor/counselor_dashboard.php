<?php
session_start();

// Check if counselor is logged in
if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

include '../db/dbconn.php';

// Only show students of the counselor's grade level
$grade_level = $_SESSION['grade_level'];

$sql = "SELECT student_id, first_name, last_name, email, grade_level 
        FROM student 
        WHERE grade_level = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Counselor Dashboard</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #cbf0ceff, #8ceb99ff);
      position: relative;
      overflow: hidden;
    }

    body::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: url('logo.jpg') no-repeat center;
      background-size: 1000px;
      opacity: 0.10;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
      padding-top: 100%;
      background-position: center 480px;
    }

    .navbar {
      background: linear-gradient(90deg, #005504ff, #003d2bff);
    }
    .navbar-brand {
      color: #fff !important;
      font-weight: bold;
      font-size: 22px;
      letter-spacing: 1px;
    }
    .navbar-text {
      color: #dbe7ff !important;
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
      background: #ffffffee;
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: translateY(-3px);
    }
    .btn {
      transition: all 0.2s ease-in-out;
    }
    .btn-primary {
      background-color: #003a13ff;
      border: none;
    }
    .btn-primary:hover {
      background-color: #6ddf86ff;
      transform: scale(1.05);
    }
    .btn-success {
      background-color: #198754;
      border: none;
    }
    .btn-success:hover {
      background-color: #157347;
      transform: scale(1.05);
    }
    .btn-info {
      background-color: #0dcaf0;
      border: none;
    }
    .btn-info:hover {
      background-color: #0aa2c0;
      transform: scale(1.05);
    }
    .btn-danger {
      background-color: #dc3545;
      border: none;
    }
    .btn-danger:hover {
      background-color: #a71d2a;
      transform: scale(1.05);
    }
    .logo-navbar {
      height: 40px;
      width: auto;
    }
  </style>
</head>
<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand fst-italic" href="#">
    <img src="../pics/logo.jpg" alt="Logo" class="logo-navbar me-2">
    EVERGREEN INTEGRATED HIGHSCHOOL
  </a>
  <div class="ms-auto">
    <span class="navbar-text me-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4 text-center">🎓 Counselor Dashboard</h2>
    
    <div class="row g-4 justify-content-center">
      <!-- Student Information -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">👩‍🎓 Student Information</h5>
            <p class="card-text">View all student profiles and details.</p>
            <a href="../user/user_table.php" class="btn btn-primary w-100">Go to Students</a>
          </div>
        </div>
      </div>

      <!-- Appointments -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">📅 Appointments</h5>
            <p class="card-text">Manage and view students' appointment schedules.</p>
            <a href="counselor_appointment.php" class="btn btn-success w-100">Go to Appointments</a>
          </div>
        </div>
      </div>

      <!-- Availability -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">🕒 Availability</h5>
            <p class="card-text">Set and view your available dates/times.</p>
            <a href="counselor_availability.php" class="btn btn-info w-100">Manage Availability</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
