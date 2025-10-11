<?php
include '../session_config.php';
include '../db/dbconn.php';

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

include '../db/dbconn.php';

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
  <script src="../js/sweetalert2@11.js"></script>
  <style>
    body {
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
      position: relative;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-size: cover;
    }

    body::after {
      content: "";
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 250px;
      height: 250px;
      background: url("logo.jpg") no-repeat center center;
      background-size: contain;
      opacity: 0.1;
      pointer-events: none;
      z-index: 0;
    }

    .navbar {
      background: linear-gradient(90deg, #889700ff, #003d2bff);
      box-shadow: 0 0 20px yellow;
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
    .btn-warning {
      background-color: #ffc107;
      color: #000;
      border: none;
    }
    .btn-warning:hover {
      background-color: #e0a800;
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
    .navbar-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }
  </style>
</head>
<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand fst-italic" href="counselor_dashboard.php">
    <img src="../pics/logo.jpg" alt="Logo" class="logo-navbar me-2">
    EVERGREEN INTEGRATED HIGHSCHOOL
  </a>
  <div class="ms-auto navbar-actions">
    <span class="navbar-text fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="counselor_change_password.php" class="btn btn-warning btn-sm">Change Password</a>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<body class="bg-light">
  <div class="container mt-5">
    <h2 class="shadow p-3 mb-5 bg-body rounded rounded-1 p-3 mb-2 bg-light text-success mb-4 text-center">Counselor Dashboard</h2>
    
    <div class="row g-4 justify-content-center">
      
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">Student Information</h5>
            <p class="card-text">View all student profiles and details.</p>
            <a href="../student/student_table.php" class="btn btn-primary w-100">Go to Students</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">Appointments</h5>
            <p class="card-text">Manage and view students' appointment schedules.</p>
            <a href="counselor_appointment.php" class="btn btn-success w-100">Go to Appointments</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">Availability</h5>
            <p class="card-text">Set and view your available dates/times.</p>
            <a href="counselor_availability.php" class="btn btn-info w-100">Manage Availability</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>