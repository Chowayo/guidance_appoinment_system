<?php
session_start();

// Redirect to login if student is not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: user_log_reg.php");
    exit;
}

include '../db/dbconn.php';

// Fetch logged-in student's info
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, grade_level FROM student WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// If somehow the student record no longer exists, destroy session and redirect
if (!$student) {
    session_destroy();
    header("Location: user_log_reg.php");
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
  <style>
      body {
          background: linear-gradient(135deg, #667eea, #764ba2);
          font-family: 'Poppins', sans-serif;
          min-height: 100vh;
      }
      .dashboard-container {
          max-width: 800px;
          margin: 50px auto;
          background: #fff;
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
  </style>
</head>
<body>
  <div class="dashboard-container text-center">
      <h1>Welcome, <?= htmlspecialchars($student['first_name']); ?>!</h1>
      <p>Here’s your information:</p>
      <div class="text-start mt-4">
          <p><span class="info-label">Student ID:</span> <?= htmlspecialchars($student['student_id']); ?></p>
          <p><span class="info-label">Full Name:</span> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
          <p><span class="info-label">Email:</span> <?= htmlspecialchars($student['email']); ?></p>
          <p><span class="info-label">Grade Level:</span> <?= htmlspecialchars($student['grade_level']); ?></p>
      </div>
      <a href="user_logout.php" class="btn btn-danger logout-btn">Logout</a>
      <a href="user_appointment.php" class="btn btn-danger ">Appointment</a>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
