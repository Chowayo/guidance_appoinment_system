<?php
include '../session_config.php';
include '../db/dbconn.php';

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
<head>
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
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
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
      font-size: 0.85rem;
      padding: 0.4em 0.7em;
      border-radius: 8px;
      font-weight: 600;
    }
    .badge.bg-warning { box-shadow: 0 0 8px rgba(255,193,7,0.6); }
    .badge.bg-success { box-shadow: 0 0 8px rgba(40,167,69,0.6); }
    .badge.bg-danger  { box-shadow: 0 0 8px rgba(220,53,69,0.6); }
    .badge.bg-info    { box-shadow: 0 0 8px rgba(23,162,184,0.6); }

    .urgency-low {
      background-color: #28a745;
      color: white;
    }
    
    .urgency-medium {
      background-color: #ffc107;
      color: #000;
    }
    
    .urgency-high {
      background-color: #dc3545;
      color: white;
    }

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
    
    /* Tooltip for additional notes */
    .notes-cell {
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      cursor: help;
    }
    
    /* Make table scrollable on small screens */
    .table-responsive {
      overflow-x: auto;
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

<div class="container mt-5 text-success shadow p-3 mb-5 bg-body rounded p-3 mb-2 bg-success text-success">
  
  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php 
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
    ?>
  <?php endif; ?>
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
      <img src="logo.jpg" alt="Logo" class="logo me-2">
      <h2 class="mb-0">My Appointments</h2>
    </div>
    <div>
      <a href="counselor_add_appointment.php" class="btn btn-primary me-2">➕ Add Appointment</a>
      <a href="counselor_delete_function.php?action=clear_all" class="btn btn-warning me-2 clear-all-link">Clear All</a>
    </div>
  </div>

  <div class="table-responsive">
    <table id="appointmentsTable" class="table table-striped table-success table-bordered table-hover">
      <thead class="table-warning">
        <tr>
          <th>#</th>
          <th>Student ID</th>
          <th>Student Name</th>
          <th>Grade</th>
          <th>Purpose</th>
          <th>Urgency</th>
          <th>Date</th>
          <th>Time</th>
          <th>Additional Notes</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Data loaded via AJAX -->
      </tbody>
    </table>
  </div>
</div>

<script>
$(document).ready(function(){
  var table = $('#appointmentsTable').DataTable({
    "ajax": {
      "url": "get_appointments.php",
      "dataSrc": "data"
    },
    "columns": [
      { "data": 0 },  // #
      { "data": 1 },  // Student ID
      { "data": 2 },  // Student Name
      { "data": 3 },  // Grade
      { "data": 4 },  // Purpose
      { "data": 5 },  // Urgency
      { "data": 6 },  // Date
      { "data": 7 },  // Time
      { "data": 8 },  // Additional Notes
      { "data": 9 },  // Status
      { "data": 10 }  // Actions
    ],
    "paging": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "pageLength": 10,
    "order": [[6, "asc"], [7, "asc"]],
    "columnDefs": [
      { "orderable": false, "targets": [0, 10] },
      { "searchable": false, "targets": [0, 10] }
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
      },
      "emptyTable": "No appointments found"
    },
    "drawCallback": function(settings) {
      var api = this.api();
      api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1;
      });
    }
  });

  // Reload every 30 seconds
  setInterval(function () {
    table.ajax.reload(null, false);
  }, 30000);

  // View Details Modal
  $(document).on('click', '.view-details', function(e) {
    e.preventDefault();
    
    var $btn = $(this);
    
    var studentName = $btn.attr('data-student');
    var studentNum = $btn.attr('data-studentnum');
    var grade = $btn.attr('data-grade');
    var purpose = $btn.attr('data-purpose');
    var urgency = $btn.attr('data-urgency');
    var date = $btn.attr('data-date');
    var time = $btn.attr('data-time');
    var notes = $btn.attr('data-notes');
    var email = $btn.attr('data-email');
    var status = $btn.attr('data-status');
    
    var urgencyColor = urgency === 'High' ? '#dc3545' : 
                       urgency === 'Medium' ? '#ffc107' : '#28a745';
    var urgencyIcon = urgency === 'High' ? '🔴' : 
                      urgency === 'Medium' ? '🟡' : '🟢';
    
    Swal.fire({
      title: '📋 Appointment Details',
      html: `
        <div style="text-align: left; padding: 10px;">
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Student ID:</strong><br>
            <span style="font-size: 16px; font-family: monospace; background: #f0f0f0; padding: 5px 10px; border-radius: 5px;">${studentNum}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Student Name:</strong><br>
            <span style="font-size: 18px;">${studentName}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Grade Level:</strong><br>
            <span>${grade}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Purpose:</strong><br>
            <span>${purpose}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Urgency Level:</strong><br>
            <span style="background-color: ${urgencyColor}; color: white; padding: 5px 12px; border-radius: 15px; font-weight: bold;">
              ${urgencyIcon} ${urgency}
            </span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Appointment Date:</strong><br>
            <span>${date}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Appointment Time:</strong><br>
            <span>${time}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Contact Email:</strong><br>
            <span>${email}</span>
          </div>
          
          <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Additional Notes:</strong><br>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 3px solid #007bff;">
              ${notes}
            </div>
          </div>
          
          <div style="margin-top: 20px; padding: 10px; background: #e7f3ff; border-radius: 5px;">
            <strong style="color: #0066cc;">Status:</strong> 
            <span style="text-transform: capitalize; font-weight: bold;">${status}</span>
          </div>
        </div>
      `,
      width: '600px',
      confirmButtonText: 'Close',
      confirmButtonColor: '#28a745'
    });
  });
}); // <-- Close the document.ready here

// SweetAlert for single delete
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
}); // <-- Close the Clear All handler here

// Reschedule Appointment Modal (SEPARATE HANDLER)
$(document).on('click', '.reschedule-appointment', function(e) {
  e.preventDefault();
  
  var appointmentId = $(this).attr('data-id');
  var studentName = $(this).attr('data-student');
  var currentDate = $(this).attr('data-date');
  var currentTime = $(this).attr('data-time');
  
  Swal.fire({
    title: '🔄 Reschedule Appointment',
    html: `
      <div style="text-align: left; padding: 10px;">
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
          <strong style="color: #666;">Student:</strong> ${studentName}<br>
          <strong style="color: #666;">Current Date:</strong> ${currentDate}<br>
          <strong style="color: #666;">Current Time:</strong> ${currentTime}
        </div>
        
        <form id="rescheduleForm">
          <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">
              New Date <span style="color: red;">*</span>
            </label>
            <input type="date" id="new_date" class="swal2-input" 
                   style="width: 90%; margin: 0;" 
                   min="${new Date().toISOString().split('T')[0]}" required>
          </div>
          
          <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">
              New Time <span style="color: red;">*</span>
            </label>
            <input type="time" id="new_time" class="swal2-input" 
                   style="width: 90%; margin: 0;" required>
          </div>
          
          <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">
              Reason for Rescheduling
            </label>
            <textarea id="reschedule_reason" class="swal2-textarea" 
                      style="width: 90%; margin: 0; height: 100px;" 
                      placeholder="Optional: Explain why you're rescheduling..."></textarea>
          </div>
        </form>
        
        <div style="background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; margin-top: 15px;">
          <small style="color: #856404;">
            <strong>Note:</strong> The student will receive an email notification about the rescheduled appointment.
          </small>
        </div>
      </div>
    `,
    width: '600px',
    showCancelButton: true,
    confirmButtonText: 'Reschedule',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#17a2b8',
    cancelButtonColor: '#6c757d',
    preConfirm: () => {
      const newDate = document.getElementById('new_date').value;
      const newTime = document.getElementById('new_time').value;
      const reason = document.getElementById('reschedule_reason').value;
      
      if (!newDate || !newTime) {
        Swal.showValidationMessage('Please fill in both date and time');
        return false;
      }
      
      const selectedDate = new Date(newDate);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (selectedDate < today) {
        Swal.showValidationMessage('Cannot select a past date');
        return false;
      }
      
      return {
        appointment_id: appointmentId,
        new_date: newDate,
        new_time: newTime,
        reason: reason
      };
    }
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Rescheduling...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      
      $.ajax({
        url: 'counselor_reschedule_function.php',
        type: 'POST',
        data: result.value,
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Rescheduled!',
              text: response.message,
              confirmButtonColor: '#28a745'
            }).then(() => {
              $('#appointmentsTable').DataTable().ajax.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response.message
            });
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
          console.error('Response:', xhr.responseText);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Please try again.'
          });
        }
      });
    }
  });
});
</script>
</body>
</html>