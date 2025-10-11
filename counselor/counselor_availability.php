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
$conn->query("DELETE FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date < CURDATE()");

// Generate schedule for 1 day
if (isset($_POST['generate_one_day'])) {
    $start_date = date('Y-m-d', strtotime('+1 day'));
    $day_of_week = date('N', strtotime($start_date));
    
    if ($day_of_week < 6) {
        $check = $conn->query("SELECT id FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date='$start_date' AND start_time='08:00:00' AND end_time='17:00:00'");
        
        if ($check->num_rows == 0) {
            if ($conn->query("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES ($counselor_id, '$start_date', '08:00:00', '17:00:00', 1)")) {
                $alert_message = "Swal.fire({icon:'success',title:'Tomorrow Added!',text:'Added 1 time slot.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
            } else {
                $alert_message = "Swal.fire({icon:'error',title:'Error',text:'Database error: " . addslashes($conn->error) . "'});";
            }
        } else {
            $alert_message = "Swal.fire({icon:'info',title:'Already Exists',text:'Tomorrow already has a schedule!'});";
        }
    } else {
        $alert_message = "Swal.fire({icon:'warning',title:'Weekend',text:'Tomorrow is a weekend. No schedule added.'});";
    }
}

// Generate schedule for 7 days
if (isset($_POST['generate_week'])) {
    // Find the last scheduled date
    $last_date_result = $conn->query("SELECT MAX(available_date) as last_date FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date >= CURDATE()");
    $last_date_row = $last_date_result->fetch_assoc();
    
    if ($last_date_row['last_date']) {
        // Start from the day after the last scheduled date
        $start_from = strtotime($last_date_row['last_date'] . ' +1 day');
    } else {
        // No schedule exists, start from tomorrow
        $start_from = strtotime('+1 day');
    }
    
    $added = 0;
    $days_checked = 0;
    
    // Keep adding until we get 7 weekdays
    while ($added < 7 && $days_checked < 20) { // Safety limit of 20 days to check
        $date = date('Y-m-d', $start_from);
        $day_of_week = date('N', $start_from);
        
        if ($day_of_week < 6) { // Weekday
            $check = $conn->query("SELECT id FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date='$date' AND start_time='08:00:00' AND end_time='17:00:00'");
            if ($check->num_rows == 0) {
                if ($conn->query("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES ($counselor_id, '$date', '08:00:00', '17:00:00', 1)")) {
                    $added++;
                }
            }
        }
        
        $start_from = strtotime('+1 day', $start_from);
        $days_checked++;
    }
    
    $alert_message = "Swal.fire({icon:'success',title:'Week Added!',text:'Added $added weekday slot(s) to your schedule.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

// Generate 30 days
if (isset($_POST['generate_schedule'])) {
    // Find the last scheduled date
    $last_date_result = $conn->query("SELECT MAX(available_date) as last_date FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date >= CURDATE()");
    $last_date_row = $last_date_result->fetch_assoc();
    
    if ($last_date_row['last_date']) {
        // Start from the day after the last scheduled date
        $start_from = strtotime($last_date_row['last_date'] . ' +1 day');
    } else {
        // No schedule exists, start from tomorrow
        $start_from = strtotime('+1 day');
    }
    
    $added = 0;
    $days_checked = 0;
    
    // Keep adding until we get 30 weekdays
    while ($added < 30 && $days_checked < 50) { // Safety limit of 50 days to check
        $date = date('Y-m-d', $start_from);
        $day_of_week = date('N', $start_from);
        
        if ($day_of_week < 6) { // Weekday
            // Full day slot: 8 AM - 5 PM
            $check = $conn->query("SELECT id FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date='$date' AND start_time='08:00:00' AND end_time='17:00:00'");
            if ($check->num_rows == 0) {
                if ($conn->query("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES ($counselor_id, '$date', '08:00:00', '17:00:00', 1)")) {
                    $added++;
                }
            }
        }
        
        $start_from = strtotime('+1 day', $start_from);
        $days_checked++;
    }
    
    $alert_message = "Swal.fire({icon:'success',title:'30 Days Added!',text:'Added $added weekday slot(s) to your schedule.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

// Add custom slot
if (isset($_POST['add_custom_slot'])) {
    $custom_date = $_POST['custom_date'];
    $custom_start = $_POST['custom_start'];
    $custom_end = $_POST['custom_end'];
    
    $input_datetime = strtotime($custom_date . ' ' . $custom_start);
    $current_datetime = time();
    
    $start_hour = (int)date('H', strtotime($custom_start));
    $end_hour = (int)date('H', strtotime($custom_end));
    $end_minute = (int)date('i', strtotime($custom_end));
    
    if ($input_datetime <= $current_datetime) {
        $alert_message = "Swal.fire({icon:'error',title:'Invalid Date/Time',text:'You can only add future dates and times!'});";
    } elseif ($start_hour < 8 || $start_hour >= 17 || $end_hour < 8 || $end_hour > 17 || ($end_hour == 17 && $end_minute > 0)) {
        $alert_message = "Swal.fire({icon:'error',title:'Outside Office Hours',text:'Time must be within 8:00 AM - 5:00 PM!'});";
    } elseif (strtotime($custom_start) >= strtotime($custom_end)) {
        $alert_message = "Swal.fire({icon:'error',title:'Invalid Time Range',text:'End time must be after start time!'});";
    } else {
        // Check if exact slot already exists
        $check_exact = $conn->query("SELECT id FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date='$custom_date' AND start_time='$custom_start' AND end_time='$custom_end'");
        
        if ($check_exact->num_rows > 0) {
            $alert_message = "Swal.fire({icon:'warning',title:'Already Exists',text:'This exact time slot already exists in your schedule!',confirmButtonText:'OK'});";
        } else {
            // Check for overlapping slots on the same date
            $check_overlap = $conn->query("SELECT id, start_time, end_time FROM counselor_availability 
                WHERE counselor_id=$counselor_id 
                AND available_date='$custom_date' 
                AND (
                    (start_time <= '$custom_start' AND end_time > '$custom_start') OR
                    (start_time < '$custom_end' AND end_time >= '$custom_end') OR
                    (start_time >= '$custom_start' AND end_time <= '$custom_end')
                )");
            
            if ($check_overlap->num_rows > 0) {
                $overlap_slot = $check_overlap->fetch_assoc();
                $overlap_start = date("g:i A", strtotime($overlap_slot['start_time']));
                $overlap_end = date("g:i A", strtotime($overlap_slot['end_time']));
                $alert_message = "Swal.fire({icon:'warning',title:'Time Conflict',text:'This overlaps with an existing slot: $overlap_start - $overlap_end',confirmButtonText:'OK'});";
            } else {
                $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time, is_available) VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param("isss", $counselor_id, $custom_date, $custom_start, $custom_end);
                
                if ($stmt->execute()) {
                    $alert_message = "Swal.fire({icon:'success',title:'Custom Slot Added!',text:'Your slot has been added.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
                } else {
                    $alert_message = "Swal.fire({icon:'error',title:'Error',text:'Could not add slot.'});";
                }
            }
        }
    }
}

// Clear all
if (isset($_GET['clear_all']) && $_GET['clear_all'] == 'confirm') {
    if ($conn->query("DELETE FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date >= CURDATE()")) {
        $alert_message = "Swal.fire({icon:'success',title:'All Cleared!',text:'All future availability removed.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
    } else {
        $alert_message = "Swal.fire({icon:'error',title:'Error',text:'Could not clear schedule.'});";
    }
}

// Toggle
if (isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $conn->query("UPDATE counselor_availability SET is_available = NOT is_available WHERE id=$toggle_id AND counselor_id=$counselor_id");
    $alert_message = "Swal.fire({icon:'success',title:'Updated!',text:'Status changed.',timer:1500,showConfirmButton:false}).then(()=>{window.location='counselor_availability.php';});";
}

// Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM counselor_availability WHERE id=$delete_id AND counselor_id=$counselor_id");
    $alert_message = "Swal.fire({icon:'success',title:'Deleted!',text:'Slot removed.',confirmButtonText:'OK'}).then(()=>{window.location='counselor_availability.php';});";
}

// Fetch data
$result = $conn->query("SELECT * FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date >= CURDATE() ORDER BY available_date, start_time");
$count = $conn->query("SELECT COUNT(*) as total FROM counselor_availability WHERE counselor_id=$counselor_id AND available_date >= CURDATE()")->fetch_assoc()['total'];
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
  <a class="navbar-brand fst-italic" href="counselor_dashboard.php"><img src="logo.jpg" alt="Logo" class="logo-navbar me-2">EVERGREEN INTEGRATED HIGHSCHOOL</a>
  <div class="ms-auto">
    <span class="navbar-text me-3 fw-bold">Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</span>
    <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="container mt-5">
  <h2>Manage Your Availability</h2>

  <div class="info-box">
    <h5><i class="bi bi-info-circle"></i> How It Works:</h5>
    <p class="mb-1"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
    <p class="mb-0"><strong>Instructions:</strong> Use quick generate buttons or add custom time slots. Mark any dates/times you are NOT available.</p>
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
    <h4 class="mb-3">Add Custom Time Slot (Emergency/Special)</h4>
    <form method="post" id="customSlotForm">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" name="custom_date" id="custom_date" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Start Time</label>
          <input type="time" name="custom_start" id="custom_start" class="form-control" min="08:00" max="17:00" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Time</label>
          <input type="time" name="custom_end" id="custom_end" class="form-control" min="08:00" max="17:00" required>
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
  
  // Set minimum date to tomorrow for custom slot
  const today = new Date();
  today.setDate(today.getDate() + 1);
  const tomorrow = today.toISOString().split('T')[0];
  $('#custom_date').attr('min', tomorrow);

  // Add Week Button
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

  // Add Month Button
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

  // Validate custom slot form
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

    const selectedDateTime = new Date(selectedDate + ' ' + startTime);
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

    if (startHour < 8 || startHour >= 17 || endHour < 8 || endHour > 17 || (endHour === 17 && endMin > 0)) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Outside Office Hours',
        text: 'Time must be within office hours (8:00 AM - 5:00 PM)!',
        confirmButtonText: 'OK'
      });
      return false;
    }

    const start = new Date('1970-01-01 ' + startTime);
    const end = new Date('1970-01-01 ' + endTime);

    if (end <= start) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Invalid Time Range',
        text: 'End time must be after start time!',
        confirmButtonText: 'OK'
      });
      return false;
    }
  });

  // Confirm Clear All
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

  // SweetAlert for Toggle
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

  // SweetAlert for Delete
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