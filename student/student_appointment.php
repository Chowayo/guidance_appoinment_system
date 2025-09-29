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

// fetch counselor availability
$sql = "SELECT id, available_date, start_time, end_time 
        FROM counselor_availability 
        WHERE counselor_id=? AND available_date >= CURDATE()
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
    background: linear-gradient(135deg, #074b0cff, #8ceb99ff);
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
  </style>
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="text-success shadow p-3 mb-5 bg-body rounded p-3 mb-2 bg-success text-success">
    <img src="logo.jpg" alt="Logo" class="logo me-2">Book Appointment
    <img src="logo.jpg" alt="Logo" class="logo me-2">
  </h2>

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
    </div>

    <div class="mb-3">
      <label class="form-label">Reason for Appointment</label>
      <textarea name="reason" class="form-control" rows="3" required placeholder="Enter the reason for your appointment..."></textarea>
    </div>

    <button type="submit" class="btn btn-success">Submit Appointment</button>
  </form>

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
            $('#studentAppointmentForm')[0].reset();
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