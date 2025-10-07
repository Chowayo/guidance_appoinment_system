<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_log_reg.php");
    exit;
}

include '../db/dbconn.php';

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, grade_level FROM student WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    session_destroy();
    header("Location: student_log_reg.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
      body {
          background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
          position: relative;
          background-repeat: no-repeat;
          background-attachment: fixed;
          background-size: cover;
      }

      body::before {
      content: "";
      position: absolute;
      top: 95%;
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

      .dashboard-container {
          max-width: 800px;
          margin: 80px auto 50px auto;
          background: #fff;
          opacity: 0.95;
          border-radius: 15px;
          padding: 40px;
          box-shadow: 0 10px 25px rgba(0,0,0,0.3);
      }
      h1 {
          margin-bottom: 30px;
      }
      .info-label {
          font-weight: 600;
      }
      .logout-btn {
          margin-top: 30px;
      }
      .logo-navbar {
        height: 40px;
        width: auto;
      }
      .navbar{
        background: linear-gradient(90deg, #889700ff, #003d2bff);
        box-shadow: 0 0 20px yellow;
      }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
    <div class="container-fluid">
      <a class="navbar-brand fst-italic fw-bold " href="#"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN PORTAL</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active text-warning fw-bold" aria-current="page" href="#">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-info fw-bold" href="student_appointment.php">Appointments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger fw-bold" href="student_logout.php" id="logoutBtnNav">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="dashboard-container text-center">
      <h1 class="fw-bold">Welcome, <?= htmlspecialchars($student['first_name']); ?>!</h1>
      <p>Here’s your information:</p>
      <div class="text-start mt-4">
          <p><span class="info-label">Student ID:</span> <?= htmlspecialchars($student['student_id']); ?></p>
          <p><span class="info-label">Full Name:</span> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
          <p><span class="info-label">Email:</span> <?= htmlspecialchars($student['email']); ?></p>
          <p><span class="info-label">Grade Level:</span> <?= htmlspecialchars($student['grade_level']); ?></p>
      </div>
  </div>

  <script>
  function handleLogout(event, url) {
    event.preventDefault();
    Swal.fire({
      title: "Are you sure?",
      text: "You will be logged out.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgba(6, 68, 14, 1)",
      cancelButtonColor: "#8cca8aff",
      confirmButtonText: "Yes, logout"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  }

  document.getElementById("logoutBtn").addEventListener("click", function(e) {
    handleLogout(e, this.href);
  });

  document.getElementById("logoutBtnNav").addEventListener("click", function(e) {
    handleLogout(e, this.href);
  });

  document.getElementById("appointmentBtn").addEventListener("click", function(event) {
    event.preventDefault();
    Swal.fire({
      title: "Go to Appointment Page?",
      text: "You will be redirected to set/view appointments.",
      icon: "info",
      showCancelButton: true,
      confirmButtonColor: "rgba(6, 68, 14, 1)",
      cancelButtonColor: "#8cca8aff",
      confirmButtonText: "Yes, proceed"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = this.href;
      }
    });
  });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php
$stmt->close();
$conn->close();
?>
