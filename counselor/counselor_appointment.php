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

?>

<!DOCTYPE html>
<html lang="en">
  <meta charset="UTF-8">
  <title>Counselor Dashboard</title>
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
    background: linear-gradient(135deg, #074b0cff, #8ceb99ff);
    position: relative;
    overflow: hidden;
    }
    h2 {
      font-weight: 700;
      letter-spacing: 1px;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }

    .table-hover tbody tr:hover {
      background-color: #f1f9f1 !important;
      transform: scale(1.01);
      transition: all 0.2s ease-in-out;
    }

    .badge {
      font-size: 0.9rem;
      padding: 0.5em 0.8em;
      border-radius: 8px;
    }
    .badge.bg-warning { box-shadow: 0 0 8px rgba(255,193,7,0.6); }
    .badge.bg-success { box-shadow: 0 0 8px rgba(40,167,69,0.6); }
    .badge.bg-danger  { box-shadow: 0 0 8px rgba(220,53,69,0.6); }
    .badge.bg-info    { box-shadow: 0 0 8px rgba(23,162,184,0.6); }

    .btn {
      transition: all 0.3s ease;
      border-radius: 6px;
    }
    .btn:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    .table {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .logo{
     height: 100px;
     width: auto;
    }
    thead.table-dark {
    background: linear-gradient(135deg, #1b5e20, #43a047) !important;
    color: #fff !important;
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
<div class="container mt-5 text-success shadow p-3 mb-5 bg-body rounded p-3 mb-2 bg-success text-success">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <img src="logo.jpg" alt="Logo" class="logo me-2"><h2>My Appointments</h2>
    <div>
        <a href="counselor_delete_function.php?action=clear_all" class="btn btn-warning me-2 clear-all-link">Clear All</a> <!-- Added class for JS targeting -->
    </div>
</div>

  <table id="appointmentsTable" class="table table-bordered table-hover table-striped">
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
      
    </tbody>
  </table>
</div>

  <script>
$(document).ready(function(){
  var table = $('#appointmentsTable').DataTable({
    "ajax": {
      "url": "get_appointments.php",
      "dataSrc": "data"
    },
    "columns": [
      { "data": 0 },
      { "data": 1 },
      { "data": 2 },
      { "data": 3 },
      { "data": 4 },
      { "data": 5 },
      { "data": 6 },
      { "data": 7 }
    ],
    "paging": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "pageLength": 10,
    "order": [[3, "asc"]],
    "columnDefs": [
      { "orderable": false, "targets": [0, 7] },
      { "searchable": false, "targets": [0, 7] }
    ],
    "language": {
      "search": "Search appointments:",
      "lengthMenu": "Show _MENU_ entries per page",
      "info": "Showing _START_ to _END_ of _TOTAL_ appointments",
      "paginate": {
        "first": "First",
        "last": "Last",
        "next": "Next",
        "previous": "Previous"
      }
    },
    "drawCallback": function(settings) {
      var api = this.api();
      api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1;
      });
    }
  });

  // reload every 30 seconds
  setInterval(function () {
    table.ajax.reload(null, false);
  }, 30000);
});

  $('#appointmentsTable').on('click', 'a[href*="action=delete_single"]', function(e) {
    e.preventDefault();
    var link = $(this).attr('href');
    
    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete this appointment!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = link;
      }
    });
  });

  // SweetAlert Clear All
  $('.clear-all-link').on('click', function(e) {
    e.preventDefault();
    var link = $(this).attr('href');
    
    Swal.fire({
      title: 'Are you sure?',
      text: "This will clear ALL appointments! This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, clear all!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = link;
      }
    });
  });
  </script>
</body>
</html>