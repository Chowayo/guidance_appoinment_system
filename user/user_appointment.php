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
$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, grade_level 
                        FROM student WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header("Location: user_log_reg.php");
    exit;
}

// Find the counselor for this grade level
$grade_level = $student['grade_level'];
$stmt = $conn->prepare("SELECT counselor_id, first_name, last_name 
                        FROM counselor WHERE grade_level=? LIMIT 1");
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$counselor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch counselor availability (future dates only)
$sql = "SELECT id, available_date, start_time, end_time 
        FROM counselor_availability 
        WHERE counselor_id=? AND available_date >= CURDATE()
        ORDER BY available_date, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor['counselor_id']);
$stmt->execute();
$result = $stmt->get_result();

// Generate slots
$slots = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['start_time']);
    $end   = new DateTime($row['end_time']);
    $date  = $row['available_date'];

    while ($start < $end) {
        $slot_start = $start->format("H:i:s");
        $slot_end   = $start->modify("+60 minutes")->format("H:i:s");

        // Check if slot already booked
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
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Book Appointment</h2>
  <form action="user_appointment_function.php" method="post" class="card p-4">
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
      <textarea name="reason" class="form-control" required></textarea>
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
  </form>
</div>
</body>
</html>
