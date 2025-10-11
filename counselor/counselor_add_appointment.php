<?php
session_start();
include "../db/dbconn.php";

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

$counselor_id = $_SESSION['counselor_id'];
$grade_level = $_SESSION['grade_level'];

// Get students for this grade level
$sql = "SELECT student_id, first_name, last_name, email, grade_level 
        FROM student 
        WHERE grade_level = ? AND is_verified = 1
        ORDER BY last_name, first_name";
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
  <title>Add Appointment - Counselor</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <!-- Select2 CSS for searchable dropdown -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  
  <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
      padding: 20px;
    }

    .card {
      max-width: 800px;
      margin: 40px auto;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      background: white;
      padding: 40px;
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
      font-weight: 700;
    }

    .form-label {
      font-weight: 600;
      color: #555;
    }

    .required-field::after {
      content: " *";
      color: red;
    }

    .btn-submit {
      background: #28a745;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      transition: all 0.3s;
    }

    .btn-submit:hover {
      background: #218838;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }

    #purpose_other_field {
      display: none;
    }

    .logo {
      height: 60px;
      width: auto;
      display: block;
      margin: 0 auto 20px;
    }

    /* Select2 custom styling */
    .select2-container--bootstrap-5 .select2-selection--single {
      min-height: 38px;
      border-radius: 5px;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection--single {
      border-color: #80bdff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .select2-search__field {
      font-size: 14px;
    }
  </style>
</head>
<body>

<div class="card">
  <img src="logo.jpg" alt="Logo" class="logo">
  <h2>Add New Appointment</h2>

  <form id="addAppointmentForm">
    <input type="hidden" name="counselor_id" value="<?= $counselor_id ?>">
    <input type="hidden" name="action" value="counselor_add_appointment">

    <!-- Select Student - SEARCHABLE -->
    <div class="mb-3">
      <label class="form-label required-field">Select Student</label>
      <select name="student_id" id="student_id" class="form-select" required>
        <option value="">-- Select or Search Student --</option>
        <?php while ($student = $students->fetch_assoc()): ?>
          <option value="<?= $student['student_id'] ?>" 
                  data-email="<?= htmlspecialchars($student['email']) ?>">
            <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?> 
            (ID: <?= $student['student_id'] ?>) - <?= htmlspecialchars($student['email']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <small class="text-muted">Type to search by name, ID, or email</small>
    </div>

    <!-- Confirmation Email (auto-filled) -->
    <div class="mb-3">
      <label class="form-label required-field">Student Email</label>
      <input type="email" name="confirmation_email" id="confirmation_email" 
             class="form-control" required readonly>
      <small class="text-muted">Email will be auto-filled when you select a student</small>
    </div>

    <!-- Purpose -->
    <div class="mb-3">
      <label class="form-label required-field">Purpose of Appointment</label>
      <select name="purpose" id="purpose" class="form-select" required>
        <option value="">-- Select Purpose --</option>
        <option value="Academic concern">Academic concern</option>
        <option value="Personal problem">Personal problem</option>
        <option value="Family issue">Family issue</option>
        <option value="Career guidance">Career guidance</option>
        <option value="Counseling follow-up">Counseling follow-up</option>
        <option value="Others">Others</option>
      </select>
    </div>

    <!-- Others - Specify -->
    <div class="mb-3" id="purpose_other_field">
      <label class="form-label">Please specify</label>
      <input type="text" name="purpose_other" id="purpose_other" 
             class="form-control" placeholder="Enter specific purpose...">
    </div>

    <!-- Urgency Level -->
    <div class="mb-3">
      <label class="form-label required-field">Urgency Level</label>
      <select name="urgency_level" class="form-select" required>
        <option value="">-- Select Urgency --</option>
        <option value="Low">Low - Can wait for regular scheduling</option>
        <option value="Medium">Medium - Need attention soon</option>
        <option value="High">High - Urgent matter</option>
      </select>
    </div>

    <!-- Date -->
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label required-field">Appointment Date</label>
        <input type="date" name="appointment_date" class="form-control" 
               min="<?= date('Y-m-d') ?>" required>
        <small class="text-muted">Cannot select past dates</small>
      </div>

      <!-- Time -->
      <div class="col-md-6 mb-3">
        <label class="form-label required-field">Appointment Time</label>
        <input type="time" name="appointment_time" class="form-control" required>
      </div>
    </div>

    <!-- Additional Notes -->
    <div class="mb-3">
      <label class="form-label">Additional Notes</label>
      <textarea name="reason" class="form-control" rows="4" 
                placeholder="Add any additional details (optional)..."></textarea>
    </div>

    <!-- Auto-approve option -->
    <div class="mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="auto_approve" 
               id="auto_approve" value="1" checked>
        <label class="form-check-label" for="auto_approve">
          Automatically approve this appointment
        </label>
      </div>
    </div>

    <!-- Buttons -->
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-submit flex-fill">
        Create Appointment
      </button>
      <a href="counselor_appointment.php" class="btn btn-cancel flex-fill">
        Cancel
      </a>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  // Initialize Select2 with search functionality
  $('#student_id').select2({
    theme: "bootstrap-5",
    placeholder: "-- Select or Search Student --",
    allowClear: true,
    width: '100%',
    matcher: function(params, data) {
      // Custom matcher for better search
      if ($.trim(params.term) === '') {
        return data;
      }

      var term = params.term.toLowerCase();
      var text = data.text.toLowerCase();

      if (text.indexOf(term) > -1) {
        return data;
      }

      return null;
    }
  });

  // Auto-fill email when student is selected
  $('#student_id').on('change', function() {
    var email = $(this).find(':selected').data('email');
    $('#confirmation_email').val(email || '');
  });

  // Show/hide "Others" field
  $('#purpose').on('change', function() {
    if ($(this).val() === 'Others') {
      $('#purpose_other_field').slideDown();
      $('#purpose_other').prop('required', true);
    } else {
      $('#purpose_other_field').slideUp();
      $('#purpose_other').prop('required', false);
      $('#purpose_other').val('');
    }
  });

  // Form submission
  $('#addAppointmentForm').on('submit', function(e) {
    e.preventDefault();

    // Validate
    if ($('#purpose').val() === 'Others' && $('#purpose_other').val().trim() === '') {
      Swal.fire({
        title: 'Validation Error',
        text: 'Please specify the purpose when selecting "Others"',
        icon: 'warning'
      });
      return;
    }

    var formData = new FormData(this);

    Swal.fire({
      title: 'Creating Appointment...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: 'counselor_appointment_add_function.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: 'Success!',
            html: response.message || 'Appointment created successfully!',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'counselor_appointment.php';
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: response.message || 'Failed to create appointment.',
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