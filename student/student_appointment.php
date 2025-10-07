<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_log_reg.php");
    exit;
}

include '../db/dbconn.php';

// getting the logged-in student's info
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, grade_level 
                        FROM student WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header("Location: student_log_reg.php");
    exit;
}

// finding the counselor for this grade level
$grade_level = $student['grade_level'];
$stmt = $conn->prepare("SELECT counselor_id, first_name, last_name 
                        FROM counselor WHERE grade_level=? LIMIT 1");
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$counselor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// fetch counselor availability - ONLY AVAILABLE SLOTS (is_available = 1)
$sql = "SELECT id, available_date, start_time, end_time 
        FROM counselor_availability 
        WHERE counselor_id=? 
        AND available_date >= CURDATE()
        AND is_available = 1
        ORDER BY available_date, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor['counselor_id']);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['start_time']);
    $end   = new DateTime($row['end_time']);
    $date  = $row['available_date'];

    while ($start < $end) {
        $slot_start = $start->format("H:i:s");
        $slot_end   = $start->modify("+60 minutes")->format("H:i:s");

        // checks if slot already booked
        $check = $conn->prepare("SELECT 1 FROM appointments 
                                 WHERE counselor_id=? AND date=? AND time=? 
                                 AND status='approved'");
        $check->bind_param("iss", $counselor['counselor_id'], $date, $slot_start);
        $check->execute();
        $isTaken = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$isTaken) {
            $slots[] = [
                'availability_id' => $row['id'],
                'date' => $date,
                'start_time' => $slot_start,
                'end_time' => $slot_end
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Book Appointment</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery-3.6.0.min.js"></script>
<script src="../js/sweetalert2@11.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
  <style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    margin: 0;
    background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
    position: relative;
    overflow: hidden;
    }
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border-radius: 12px;
    }

    .card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .form-select:hover {
      border-color: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
      transition: 0.3s;
    }

    textarea:hover {
      border-color: #17a2b8;
      box-shadow: 0 0 8px rgba(23, 162, 184, 0.4);
      transition: 0.3s;
    }

    .btn-success {
      transition: all 0.3s ease;
      border-radius: 8px;
    }
    .btn-success:hover {
      background-color: #218838;
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(33, 136, 56, 0.3);
    }

    h2 {
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 20px;
      text-align: center;
      letter-spacing: 1px;
    }
    .logo{
     height: 50px;
     width: auto;
    }
    .logo-navbar {
      height: 40px;
      width: auto;
    }
    .navbar{
      background: linear-gradient(90deg, #889700ff, #003d2bff);
      box-shadow: 0 0 20px yellow;
    }
    
    .no-slots-message {
      text-align: center;
      padding: 40px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .no-slots-message i {
      font-size: 60px;
      color: #ffc107;
      margin-bottom: 20px;
    }
  </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
    <div class="container-fluid">
      <a class="navbar-brand fst-italic fw-bold" href="#"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN PORTAL</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active text-warning fw-bold" aria-current="page" href="landing_page.php">Dashboard</a>
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
<div class="container mt-5">
  <h2 class="text-success shadow p-3 mb-5 bg-body rounded p-3 mb-2 bg-success text-success">
    <img src="logo.jpg" alt="Logo" class="logo me-2">Book Appointment
    <img src="logo.jpg" alt="Logo" class="logo me-2">
  </h2>

  <?php if (empty($slots)): ?>
    <div class="no-slots-message">
      <div style="font-size: 60px; color: #ffc107; margin-bottom: 20px;">📅</div>
      <h3 style="color: #6c757d;">No Available Slots</h3>
      <p style="color: #6c757d; margin-top: 10px;">
        There are currently no available appointment slots. Please check back later or contact your counselor.
      </p>
      <a href="landing_page.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
  <?php else: ?>
  <form id="studentAppointmentForm" class="card p-4">
    <input type="hidden" name="counselor_id" value="<?= $counselor['counselor_id'] ?>">
    <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">

    <div class="mb-3">
      <label class="form-label">Select Available Slot</label>
      <select name="slot" class="form-select" required>
        <option value="">-- Select --</option>
        <?php foreach ($slots as $slot): ?>
          <option value="<?= $slot['date'].'|'.$slot['start_time'] ?>">
            <?= date("F j, Y", strtotime($slot['date'])) ?> 
            (<?= date("h:i A", strtotime($slot['start_time'])) ?> - 
             <?= date("h:i A", strtotime($slot['end_time'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">Showing <?= count($slots) ?> available time slot(s)</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Reason for Appointment</label>
      <textarea name="reason" class="form-control" rows="3" required placeholder="Enter the reason for your appointment..."></textarea>
    </div>

    <button type="submit" class="btn btn-success">Submit Appointment</button>
  </form>
  <?php endif; ?>

</div>

<script>
$(document).ready(function() {
  $('#studentAppointmentForm').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('action', 'book_appointment');

    Swal.fire({
      title: 'Submitting...',
      text: 'Please wait while we book your appointment.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: 'student_appointment_function.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: 'Success!',
            text: response.message || 'Appointment booked successfully!',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'student_appointment.php';
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: response.message || 'Failed to book appointment.',
            icon: 'error'
          });
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
        Swal.fire({
          title: 'Error!',
          text: 'Something went wrong. Please try again.',
          icon: 'error'
        });
      }
    });
  });
});
</script>

</body>
</html>