<?php
session_start();
include "../db/dbconn.php";

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

$counselor_id = $_SESSION['counselor_id'];
$grade_level  = $_SESSION['grade_level'];

$sql = "SELECT student_id, first_name, last_name, email, grade_level 
        FROM student 
        WHERE grade_level = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();

$sql = "SELECT a.appointment_id, a.date, a.time, a.reason, a.status,
               s.first_name, s.last_name, s.grade_level
        FROM appointments a
        JOIN student s ON a.student_id = s.student_id
        WHERE a.counselor_id = ?
        ORDER BY a.date, a.time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Counselor Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Appointments</h2>
    <div>
        <a href="counselor_delete_function.php?action=clear_all" class="btn btn-warning me-2" onclick="return confirm('Are you sure you want to clear ALL appointments? This action cannot be undone.');">Clear All</a>
        <a href="counselor_logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

  <table class="table table-bordered table-hover table-striped">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Student</th>
        <th>Grade</th>
        <th>Date</th>
        <th>Time</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $count=1; while($row=$appointments->fetch_assoc()): ?>
      <tr>
        <td><?= $count++ ?></td>
        <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
        <td><?= $row['grade_level'] ?></td>
        <td><?= date("F j, Y", strtotime($row['date'])) ?></td>
        <td><?= date("h:i A", strtotime($row['time'])) ?></td>
        <td><?= htmlspecialchars($row['reason']) ?></td>
        <td>
          <?php if ($row['status']=='pending'): ?>
            <span class="badge bg-warning">Pending</span>
          <?php elseif ($row['status']=='approved'): ?>
            <span class="badge bg-success">Approved</span>
          <?php elseif ($row['status']=='declined'): ?>
            <span class="badge bg-danger">Declined</span>
          <?php else: ?>
            <span class="badge bg-info">Rescheduled</span>
          <?php endif; ?>
        </td>
        <td>
    <?php if ($row['status'] == 'pending'): ?>
        <a href="counselor_update_function.php?id=<?= $row['appointment_id'] ?>&action=approve" class="btn btn-sm btn-success mb-1">Approve</a>
        <a href="counselor_update_function.php?id=<?= $row['appointment_id'] ?>&action=decline" class="btn btn-sm btn-danger mb-1">Decline</a>
        <a href="counselor_update_function.php?id=<?= $row['appointment_id'] ?>&action=reschedule" class="btn btn-sm btn-info mb-1">Reschedule</a>
    <?php else: ?>
        <a href="counselor_delete_function.php?id=<?= $row['appointment_id'] ?>&action=delete_single" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
    <?php endif; ?>
</td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
