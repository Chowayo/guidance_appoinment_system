<?php
include "../db/dbconn.php";
session_start();

if (!isset($_SESSION['counselor_id'])) {
    header("Location: counselor_login.php");
    exit;
}

$counselor_id = $_SESSION['counselor_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_availability'])) {
    $available_date = $_POST['available_date'];
    $start_time     = $_POST['start_time'];
    $end_time       = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $counselor_id, $available_date, $start_time, $end_time);
    $stmt->execute();

    echo "<script>alert('Availability saved successfully!'); window.location='counselor_availability.php';</script>";
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM counselor_availability WHERE id=$delete_id AND counselor_id=$counselor_id");
    echo "<script>alert('Availability deleted!'); window.location='counselor_availability.php';</script>";
}

$result = $conn->query("SELECT * FROM counselor_availability WHERE counselor_id=$counselor_id ORDER BY available_date, start_time");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Set Availability</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Set Availability</h2>
  <form method="post" class="card p-4 mb-5">
    <input type="hidden" name="save_availability" value="1">
    <div class="mb-3">
      <label class="form-label">Date</label>
      <input type="date" name="available_date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Start Time</label>
      <input type="time" name="start_time" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">End Time</label>
      <input type="time" name="end_time" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Save Availability</button>
  </form>

  <!-- Availability list -->
  <h3>My Availability</h3>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['available_date'] ?></td>
          <td><?= date("h:i A", strtotime($row['start_time'])) ?></td>
          <td><?= date("h:i A", strtotime($row['end_time'])) ?></td>
          <td>
            <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this availability?');">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center">No availability set yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
