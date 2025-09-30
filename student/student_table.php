<?php
session_start();

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

include '../db/dbconn.php';

// show students of the counselor's grade level
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Database</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/jquery.dataTables.min.css">
  <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/jquery.dataTables.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
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
  <a class="navbar-brand fst-italic" href="../counselor/counselor_dashboard.php"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN INTEGRATED HIGHSCHOOL</a>
  <div class="ms-auto">
    <span class="navbar-text me-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="container my-5">
  <div class="card p-4">
    <h3 class="mb-4 text-center text-success fw-bold">Students - <?= htmlspecialchars($grade_level); ?></h3>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover text-center align-middle" id="studentTable">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Grade Level</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">No records found for your grade level.</div>
    <?php endif; ?>
    <?php $conn->close(); ?>
  </div>
</div>

<script>
$(document).ready(function(){
  var table = $('#studentTable').DataTable({
    "ajax": "fetch.php",
    "columns": [
      { "data": "student_id" },
      { "data": "first_name" },
      { "data": "last_name" },
      { "data": "email" },
      { 
        "data": "grade_level",
        "render": function(data){
          return `<span class="badge bg-primary">${data}</span>`;
        }
      },
      {
        "data": null,
        "render": function(data, type, row){
          return `
            <button class="btn btn-sm btn-primary me-1 updateBtn" data-id="${row.student_id}">Update</a>
            <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.student_id}">Delete</button>
          `;
        }
      }
    ]
  });
  // Update button
  $('#studentTable').on('click', '.updateBtn', function(){
  var student_id = $(this).data('id');
  Swal.fire({
    title: 'Proceed to Update?',
    text: "You will be redirected to the update page.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, continue'
  }).then((result) => {
    if(result.isConfirmed){
      window.location.href = "student_update.php?student_id=" + student_id;
    }
  });
});

  // Delete button
  $('#studentTable').on('click', '.deleteBtn', function(){
    var student_id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete the record!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if(result.isConfirmed){
        $.ajax({
          url: "student_delete.php",
          type: "POST",
          data: { student_id: student_id },
          success: function(res){
            Swal.fire('Deleted!', res, 'success');
            table.ajax.reload();
          }
        });
      }
    });
  });
});
</script>

</body>
</html>

