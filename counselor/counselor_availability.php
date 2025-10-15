<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../session_config.php';
include '../db/dbconn.php';

if (!isset($_SESSION['counselor_id'])) {
    header("Location: counselor_login.php");
    exit;
}

$counselor_id = $_SESSION['counselor_id'];
$alert_message = '';

// Auto-delete past availability
$stmt = $conn->prepare("DELETE FROM counselor_availability WHERE counselor_id = ? AND available_date < CURDATE()");
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$stmt->close();

// Generate schedule for 7 days
if (isset($_POST['generate_week'])) {
    $stmt = $conn->prepare("SELECT MAX(available_date) as last_date FROM counselor_availability WHERE counselor_id = ? AND available_date >= CURDATE()");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $last_date_result = $stmt->get_result();
    $last_date_row = $last_date_result->fetch_assoc();
    $stmt->close();
    
    if ($last_date_row['last_date']) {
        $start_from = strtotime($last_date_row['last_date'] . ' +1 day');
    } else {
        $start_from = strtotime('+1 day');
    }
    
    $added = 0;
    $days_checked = 0;
    
    while ($added < 7 && $days_checked < 20) {
        $date = date('Y-m-d', $start_from);
        $day_of_week = date('N', $start_from);
        
        if ($day_of_week < 6) {
            $stmt = $conn->prepare("SELECT id FROM counselor_availability WHERE counselor_id = ? AND available_date = ? AND start_time = '08:00:00' AND end_time = '17:00:00'");
            $stmt->bind_param("is", $counselor_id, $date);
            $stmt->execute();
            $check_result = $stmt->get_result();
            $stmt->close();
            
            if ($check_result->num_rows == 0) {
                $start_time = '08:00:00';
                $end_time = '17:00:00';
                $is_available = 1;
                
                $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $counselor_id, $date, $start_time, $end_time, $is_available);
                
                if ($stmt->execute()) {
                    $added++;
                }
                $stmt->close();
            }
        }
        
        $start_from = strtotime('+1 day', $start_from);
        $days_checked++;
    }
    
    $alert_message = "Swal.fire({icon:'success',title:'Week Added!',text:'Added $added weekday slot(s) to your schedule.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

// Generate 30 days
if (isset($_POST['generate_schedule'])) {
    $stmt = $conn->prepare("SELECT MAX(available_date) as last_date FROM counselor_availability WHERE counselor_id = ? AND available_date >= CURDATE()");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $last_date_result = $stmt->get_result();
    $last_date_row = $last_date_result->fetch_assoc();
    $stmt->close();
    
    if ($last_date_row['last_date']) {
        $start_from = strtotime($last_date_row['last_date'] . ' +1 day');
    } else {
        $start_from = strtotime('+1 day');
    }
    
    $added = 0;
    $days_checked = 0;
    
    while ($added < 30 && $days_checked < 50) {
        $date = date('Y-m-d', $start_from);
        $day_of_week = date('N', $start_from);
        
        if ($day_of_week < 6) {
            $stmt = $conn->prepare("SELECT id FROM counselor_availability WHERE counselor_id = ? AND available_date = ? AND start_time = '08:00:00' AND end_time = '17:00:00'");
            $stmt->bind_param("is", $counselor_id, $date);
            $stmt->execute();
            $check_result = $stmt->get_result();
            $stmt->close();
            
            if ($check_result->num_rows == 0) {
                $start_time = '08:00:00';
                $end_time = '17:00:00';
                $is_available = 1;
                
                $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $counselor_id, $date, $start_time, $end_time, $is_available);
                
                if ($stmt->execute()) {
                    $added++;
                }
                $stmt->close();
            }
        }
        
        $start_from = strtotime('+1 day', $start_from);
        $days_checked++;
    }
    
    $alert_message = "Swal.fire({icon:'success',title:'30 Days Added!',text:'Added $added weekday slot(s) to your schedule.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

if (isset($_POST['add_custom_slot'])) {
    $custom_date = $_POST['custom_date'];
    $custom_start = $_POST['custom_start'] . ':00';
    $custom_end = $_POST['custom_end'] . ':00';
    
    $input_datetime = strtotime($custom_date . ' ' . $custom_start);
    $current_datetime = time();
    
    if ($input_datetime <= $current_datetime) {
        $alert_message = "Swal.fire({icon:'error',title:'Invalid Date/Time',text:'You can only add future dates and times!'});";
    } elseif (strtotime($custom_start) >= strtotime($custom_end)) {
        $alert_message = "Swal.fire({icon:'error',title:'Invalid Time Range',text:'End time must be after start time!'});";
    } else {
        $stmt = $conn->prepare("SELECT id FROM counselor_availability WHERE counselor_id = ? AND available_date = ? AND start_time = ? AND end_time = ?");
        $stmt->bind_param("isss", $counselor_id, $custom_date, $custom_start, $custom_end);
        $stmt->execute();
        $check_exact_result = $stmt->get_result();
        $stmt->close();
        
        if ($check_exact_result->num_rows > 0) {
            $alert_message = "Swal.fire({icon:'warning',title:'Already Exists',text:'This exact time slot already exists in your schedule!',confirmButtonText:'OK'});";
        } else {
            $stmt = $conn->prepare("SELECT id, start_time, end_time FROM counselor_availability 
                WHERE counselor_id = ? 
                AND available_date = ? 
                AND (
                    (start_time <= ? AND end_time > ?) OR
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                )");
            $stmt->bind_param("isssssss", $counselor_id, $custom_date, $custom_start, $custom_start, $custom_end, $custom_end, $custom_start, $custom_end);
            $stmt->execute();
            $check_overlap_result = $stmt->get_result();
            $stmt->close();
            
            if ($check_overlap_result->num_rows > 0) {
                $overlap_slot = $check_overlap_result->fetch_assoc();
                $overlap_start = date("g:i A", strtotime($overlap_slot['start_time']));
                $overlap_end = date("g:i A", strtotime($overlap_slot['end_time']));
                $alert_message = "Swal.fire({icon:'warning',title:'Time Conflict',text:'This overlaps with an existing slot: $overlap_start - $overlap_end',confirmButtonText:'OK'});";
            } else {
                $is_available = 1;
                $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $counselor_id, $custom_date, $custom_start, $custom_end, $is_available);
                
                if ($stmt->execute()) {
                    $alert_message = "Swal.fire({icon:'success',title:'Custom Slot Added!',text:'Your slot has been added.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
                } else {
                    $alert_message = "Swal.fire({icon:'error',title:'Error',text:'Could not add slot.'});";
                }
                $stmt->close();
            }
        }
    }
}

// Clear all
if (isset($_GET['clear_all']) && $_GET['clear_all'] == 'confirm') {
    $stmt = $conn->prepare("DELETE FROM counselor_availability WHERE counselor_id = ? AND available_date >= CURDATE()");
    $stmt->bind_param("i", $counselor_id);
    
    if ($stmt->execute()) {
        $alert_message = "Swal.fire({icon:'success',title:'All Cleared!',text:'All future availability removed.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
    } else {
        $alert_message = "Swal.fire({icon:'error',title:'Error',text:'Could not clear schedule.'});";
    }
    $stmt->close();
}

if (isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $stmt = $conn->prepare("UPDATE counselor_availability SET is_available = NOT is_available WHERE id = ? AND counselor_id = ?");
    $stmt->bind_param("ii", $toggle_id, $counselor_id);
    $stmt->execute();
    $stmt->close();
    $alert_message = "Swal.fire({icon:'success',title:'Updated!',text:'Status changed.',timer:1500,showConfirmButton:false}).then(()=>{window.location='counselor_availability.php';});";
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM counselor_availability WHERE id = ? AND counselor_id = ?");
    $stmt->bind_param("ii", $delete_id, $counselor_id);
    $stmt->execute();
    $stmt->close();
    $alert_message = "Swal.fire({icon:'success',title:'Deleted!',text:'Slot removed.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

$stmt = $conn->prepare("SELECT * FROM counselor_availability WHERE counselor_id = ? AND available_date >= CURDATE() ORDER BY available_date, start_time");
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM counselor_availability WHERE counselor_id = ? AND available_date >= CURDATE()");
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$count_result = $stmt->get_result();
$count = $count_result->fetch_assoc()['total'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Availability</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/jquery.dataTables.min.css">
  <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/jquery.dataTables.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
  <style>
  body {
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
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
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    background: #ffffffee;
    transition: transform 0.2s ease;
  }

  .card:hover {
      transform: translateY(-3px);
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
      padding: 6px 12px;
      font-size: 0.9rem;
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

  .btn-info {
      background-color: #17a2b8;
      border: none;
      color: #fff;
  }
  
  .btn-info:hover {
      background-color: #138496;
      transform: scale(1.05);
  }

  .btn-warning {
      background-color: #ffc107;
      border: none;
      color: #000;
  }
  
  .btn-warning:hover {
      background-color: #e0a800;
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

  .btn-success {
      background-color: #28a745;
      border: none;
  }
  
  .btn-success:hover {
      background-color: #218838;
      transform: scale(1.05);
  }

  .logo-navbar {
     height: 40px;
     width: auto;
  }

  .status-available {
      background-color: #d4edda;
      border-left: 4px solid #28a745;
  }

  .status-unavailable {
      background-color: #f8d7da;
      border-left: 4px solid #dc3545;
  }

  .info-box {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
  }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand fst-italic" href="counselor_dashboard.php"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN GUIDANCE COUNSELOR PORTAL</a>
  <div class="ms-auto">
    <span class="navbar-text me-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="container mt-5">
  <h2>Manage Your Availability</h2>

  <div class="info-box">
    <p class="mb-1"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
    <p class="mb-0"><strong>Instructions:</strong> Use quick action buttons or add custom time slots. Mark any dates/times you are not available.</p>
  </div>

  <!-- Quick Action Buttons -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">Quick Actions</h4>
    <div class="row g-3">
      <div class="col-md-4">
        <button type="button" class="btn btn-primary w-100" id="addWeekBtn">
          📆 Add Next 7 Days
        </button>
      </div>
      <div class="col-md-4">
        <button type="button" class="btn btn-success w-100" id="addMonthBtn">
          🗓️ Add Next 30 Days
        </button>
      </div>
      <div class="col-md-4">
        <button type="button" class="btn btn-danger w-100" id="clearAllBtn">
          🗑️ Clear All
        </button>
      </div>
    </div>
  </div>

  <!-- Add Custom Time Slot -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">Add Custom Time Slot</h4>
    <form method="post" id="customSlotForm">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" name="custom_date" id="custom_date" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Start Time</label>
          <input type="time" name="custom_start" id="custom_start" class="form-control"required>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Time</label>
          <input type="time" name="custom_end" id="custom_end" class="form-control"required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" name="add_custom_slot" class="btn btn-warning w-100">
            ➕ Add Slot
          </button>
        </div>
      </div>
      <small class="text-muted mt-2 d-block">Office hours: 8:00 AM - 5:00 PM</small>
    </form>
  </div>

  <?php if ($count == 0): ?>
  <div class="card p-4 mb-4 text-center">
    <h4>No Schedule Generated Yet</h4>
    <p>Use the quick action buttons above to generate your availability schedule.</p>
  </div>
  <?php else: ?>
  <div class="card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Total Upcoming Slots: <span class="badge bg-primary"><?= $count ?></span></h5>
      </div>
    </div>
  </div>

  <div class="card p-4">
    <h3>My Schedule</h3>
    <table class="table table-bordered table-striped" id="availabilityTable">
      <thead>
        <tr>
          <th>Date</th>
          <th>Day</th>
          <th>Time Slot</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): 
            $status_class = $row['is_available'] ? 'status-available' : 'status-unavailable';
            $status_badge = $row['is_available'] ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-danger">Not Available</span>';
            $toggle_text = $row['is_available'] ? 'Mark Unavailable' : 'Mark Available';
            $toggle_btn = $row['is_available'] ? 'btn-warning' : 'btn-success';
            $day_name = date('l', strtotime($row['available_date']));
          ?>
          <tr class="<?= $status_class ?>">
            <td data-order="<?= $row['available_date'] ?>"><?= date('M d, Y', strtotime($row['available_date'])) ?></td>
            <td><strong><?= $day_name ?></strong></td>
            <td>
              <?= date("g:i A", strtotime($row['start_time'])) ?> - 
              <?= date("g:i A", strtotime($row['end_time'])) ?>
            </td>
            <td><?= $status_badge ?></td>
            <td>
              <a href="?toggle_id=<?= $row['id'] ?>" class="btn <?= $toggle_btn ?> btn-sm toggle-btn">
                <?= $toggle_text ?>
              </a>
              <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm delete-btn">
                Delete
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center">No availability slots found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
$(document).ready(function(){
  
  <?php if (!empty($alert_message)): ?>
    <?= $alert_message ?>
  <?php endif; ?>
  
  const today = new Date();
  today.setDate(today.getDate() + 1);
  const tomorrow = today.toISOString().split('T')[0];
  $('#custom_date').attr('min', tomorrow);

  $('#addWeekBtn').on('click', function(){
    Swal.fire({
      title: 'Add Next 7 Days?',
      text: "This will add availability for the next 7 weekdays (8 AM - 5 PM).",
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, add them!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if(result.isConfirmed){
        $('<form method="POST"><input type="hidden" name="generate_week" value="1"></form>')
          .appendTo('body').submit();
      }
    });
  });

  $('#addMonthBtn').on('click', function(){
    Swal.fire({
      title: 'Add Next 30 Days?',
      text: "This will add availability for the next 30 weekdays (8 AM - 5 PM full day).",
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, add them!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if(result.isConfirmed){
        $('<form method="POST"><input type="hidden" name="generate_schedule" value="1"></form>')
          .appendTo('body').submit();
      }
    });
  });

  $('#customSlotForm').on('submit', function(e){
    const selectedDate = $('#custom_date').val();
    const startTime = $('#custom_start').val();
    const endTime = $('#custom_end').val();

    if (!selectedDate || !startTime || !endTime) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Missing Information',
        text: 'Please fill in all fields!',
        confirmButtonText: 'OK'
      });
      return false;
    }

    const selectedDateTime = new Date(selectedDate + 'T' + startTime);
    const now = new Date();

    if (selectedDateTime <= now) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Invalid Date/Time',
        text: 'You can only add future dates and times!',
        confirmButtonText: 'OK'
      });
      return false;
    }

    const [startHour, startMin] = startTime.split(':').map(Number);
    const [endHour, endMin] = endTime.split(':').map(Number);

    const startTotalMinutes = startHour * 60 + startMin;
    const endTotalMinutes = endHour * 60 + endMin;

    if (endTotalMinutes <= startTotalMinutes) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Invalid Time Range',
        text: 'End time must be after start time!',
        confirmButtonText: 'OK'
      });
      return false;
    }

    return true;
  });

  $('#clearAllBtn').on('click', function(e){
    e.preventDefault();
    Swal.fire({
      title: 'Clear All Schedule?',
      text: "This will delete ALL your future availability! This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, clear everything!',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc3545'
    }).then((result) => {
      if(result.isConfirmed){
        window.location.href = '?clear_all=confirm';
      }
    });
  });
  
  <?php if ($count > 0): ?>
  $('#availabilityTable').DataTable({
    "pageLength": 10,
    "lengthMenu": [10, 25, 50, 100],
    "order": [[0, "asc"], [2, "asc"]],
    "columnDefs": [
      { "orderable": false, "targets": 4 }
    ]
  });
  <?php endif; ?>

  $(document).on('click', '.toggle-btn', function(e){
    e.preventDefault();
    var link = $(this).attr('href');
    var action = $(this).text().trim();

    Swal.fire({
      title: 'Confirm Action',
      text: "Do you want to " + action.toLowerCase() + " for this time slot?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, proceed!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if(result.isConfirmed){
        window.location.href = link;
      }
    });
  });

  $(document).on('click', '.delete-btn', function(e){
    e.preventDefault();
    var link = $(this).attr('href');

    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete this time slot from your schedule!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc3545'
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