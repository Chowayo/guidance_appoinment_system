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
 <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/jquery.dataTables.min.css">
  <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/jquery.dataTables.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
  <style>
  body {
      background: linear-gradient(135deg, #cbf0ceff, #8ceb99ff  );
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

  h2, h3 {
    text-align: center;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
  }

  .card {
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    background: #fff;
  }

  .btn-success {
    transition: 0.3s;
  }
  .btn-success:hover {
    transform: scale(1.05);
    background-color: #28a745;
  }

  .table {
    border-radius: 12px;
    overflow: hidden;
  }

  .table thead {
    background: #c5f08dff;
    color: #fff;
  }

  .table tbody tr:hover {
    background: rgba(0,0,0,0.05);
    transition: 0.3s;
  }

  .btn-danger {
    transition: 0.3s;
  }
  .btn-danger:hover {
    transform: scale(1.05);
    background-color: #c82333;
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
    table thead {
      background: linear-gradient(90deg, #0d5b61ff, #008011ff);
      color: white;
    }
    table tbody tr {
      transition: background-color 0.2s ease, box-shadow 0.2s ease;
      color: #023100ff;
    }
    table tbody tr:hover {
      background-color: #eef4ff;
      box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
    }
    .badge {
      padding: 6px 10px;
      font-size: 0.85rem;
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
<body>

<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand fst-italic" href="counselor_dashboard.php"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN INTEGRATED HIGHSCHOOL</a>
  <div class="ms-auto">
    <span class="navbar-text me-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

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

  <h3>My Availability</h3>
  <table id="availabilityTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['available_date'] ?></td>
    <td><?= date("h:i A", strtotime($row['start_time'])) ?></td>
    <td><?= date("h:i A", strtotime($row['end_time'])) ?></td>
    <td>
      <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id'] ?>">Delete</a>
    </td>
  </tr>
<?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function(){

  let availTable = $('#availabilityTable').DataTable({
  "pageLength": 5,
  "lengthMenu": [5, 10, 20],
  "order": [[0, "asc"], [1, "asc"]],
  "columnDefs": [
    { "orderable": false, "targets": 3 }
  ],
  "language": {
    "emptyTable": "No availability set yet"
  }
});

  // SweetAlert for Delete
  $('table').on('click', '.btn-danger', function(e){
    e.preventDefault();
    var link = $(this).attr('href');

    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete this availability slot!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if(result.isConfirmed){
        window.location.href = link;
      }
    });
  });

});
</script>

</body>
</html>
