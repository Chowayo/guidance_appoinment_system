<?php
include '../session_config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_log_reg.php");
    exit;
}

include '../db/dbconn.php';

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
    header("Location: student_log_reg.php");
    exit;
}

// finding the counselor for this grade level
$grade_level = $student['grade_level'];
$stmt = $conn->prepare("SELECT counselor_id, first_name, last_name 
                        FROM counselor WHERE grade_level=? LIMIT 1");
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$counselor = $stmt->get_result()->fetch_assoc();
$stmt->close();

$sql = "SELECT id, available_date, start_time, end_time 
        FROM counselor_availability 
        WHERE counselor_id=? 
        AND available_date >= CURDATE()
        AND is_available = 1
        ORDER BY available_date, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor['counselor_id']);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['start_time']);
    $end   = new DateTime($row['end_time']);
    $date  = $row['available_date'];

    while ($start < $end) {
        $slot_start = $start->format("H:i:s");
        $slot_end   = $start->modify("+60 minutes")->format("H:i:s");

        // checks if slot already booked
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
  <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
      background-attachment: fixed;
      background-repeat: no-repeat;
      background-size: cover;
      min-height: 100vh;
      position: relative;
      flex: 1;
    }

    body::before {
      content: "";
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: url('logo.jpg') no-repeat center;
      background-size: 800px;
      opacity: 0.08;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
    }

    /* Navbar Styling */
    .navbar {
      background: linear-gradient(90deg, #889700ff, #003d2bff);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      padding: 15px 0;
      position: relative;
      z-index: 10;
    }

    .navbar-brand {
      font-size: 1.1rem;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      color: white !important;
    }

    .navbar-brand:hover {
      transform: scale(1.02);
      opacity: 0.9;
    }

    .logo-navbar {
      height: 45px;
      width: auto;
      transition: all 0.3s ease;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .logo-navbar:hover {
      transform: scale(1.05);
    }

    .nav-item {
      margin: 0 8px;
    }

    .nav-item .btn {
      padding: 8px 20px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .nav-item .btn-light {
      background: white;
      color: #003d2b;
    }

    .nav-item .btn-light:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      border-color: #a7ff7e;
    }

    .nav-link.dropdown-toggle {
      padding: 8px 20px;
      border-radius: 8px;
      transition: all 0.3s ease;
      background: rgba(255,255,255,0.1);
      color: white !important;
    }

    .nav-link.dropdown-toggle:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-2px);
    }

    .dropdown-menu {
      border-radius: 10px;
      border: none;
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
      margin-top: 10px;
    }

    .dropdown-item {
      padding: 10px 20px;
      transition: all 0.3s ease;
    }

    .dropdown-item:hover {
      background: #f8f9fa;
      padding-left: 25px;
    }

    .dropdown-item.text-danger:hover {
      background: #ffe5e5;
    }

    /* Main Content */
    .container {
      position: relative;
      z-index: 1;
      flex: 1;
    }

    /* Page Title */
    h2 {
      font-weight: 700;
      color: #003d2b;
      margin-bottom: 30px;
      text-align: center;
      letter-spacing: 0.5px;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
    }

    .logo {
      height: 50px;
      width: auto;
    }

    /* Form Card */
    .card {
      background: white;
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border-top: 4px solid #28a745;
      position: relative;
      z-index: 1;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    /* Form Labels and Fields */
    .form-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .form-control,
    .form-select {
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #28a745;
      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .form-control:hover,
    .form-select:hover {
      border-color: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.2);
    }

    textarea.form-control {
      resize: vertical;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    textarea.form-control:hover {
      border-color: #17a2b8;
      box-shadow: 0 0 8px rgba(23, 162, 184, 0.2);
    }

    /* Required Field Indicator */
    .required-field::after {
      content: " *";
      color: #dc3545;
      font-weight: bold;
    }

    /* Small Text */
    small.text-muted {
      color: #6c757d !important;
      display: block;
      margin-top: 6px;
      font-size: 0.85rem;
    }

    /* Submit Button */
    .btn-success {
      background: linear-gradient(135deg, #28a745, #20c997);
      border: none;
      padding: 15px 30px;
      font-weight: 700;
      border-radius: 10px;
      transition: all 0.3s ease;
      color: white;
      font-size: 1rem;
    }

    .btn-success:hover {
      background: linear-gradient(135deg, #20c997, #28a745);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
      color: white;
    }

    .btn-success:active {
      transform: translateY(0);
    }

    /* No Slots Message */
    .no-slots-message {
      text-align: center;
      padding: 60px 40px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      position: relative;
      z-index: 1;
    }

    .no-slots-message h3 {
      color: #6c757d;
      font-weight: 600;
      margin: 20px 0 10px;
    }

    .no-slots-message p {
      color: #6c757d;
      margin-top: 10px;
      font-size: 1rem;
    }

    .no-slots-message .btn-primary {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
      margin-top: 25px;
      padding: 10px 30px;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .no-slots-message .btn-primary:hover {
      background: linear-gradient(135deg, #0056b3, #007bff);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }

    /* Urgency Badges */
    .urgency-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-left: 8px;
    }

    .urgency-low {
      background-color: #d4edda;
      color: #155724;
    }

    .urgency-medium {
      background-color: #fff3cd;
      color: #856404;
    }

    .urgency-high {
      background-color: #f8d7da;
      color: #721c24;
    }

    /* Hidden Field */
    #purpose_other_field {
      display: none;
    }

    /* Modal Styling */
    .modal-header {
      background: linear-gradient(90deg, #003d2bff, #889700ff) !important;
      color: white;
      border: none;
    }

    .modal-body {
      background: #f8f9fa;
      border: none;
    }

    .modal-footer {
      background: #f8f9fa;
      border-top: 1px solid #dee2e6;
    }

    /* Footer */
    .footer {
      background: linear-gradient(90deg, #003d2bff, #889700ff);
      color: white;
      padding: 40px 0 20px;
      position: relative;
      z-index: 1;
      width: 100%;
      flex-shrink: 0;
      margin-top: 50px;
    }

    .footer-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
    }

    .footer-contact-row {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 30px;
      text-align: center;
    }

    .footer-contact-item {
      display: flex;
      align-items: center;
      gap: 8px;
      opacity: 0.9;
      font-size: 0.95rem;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      padding-top: 15px;
      text-align: center;
      opacity: 0.8;
      width: 100%;
      font-size: 0.9rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .navbar {
        padding: 10px 0;
      }

      .navbar-brand {
        font-size: 0.95rem;
      }

      h2 {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
        font-size: 1.5rem;
      }

      .logo {
        height: 40px;
      }

      .card {
        padding: 20px;
      }

      .no-slots-message {
        padding: 40px 20px;
      }

      .footer-contact-row {
        flex-direction: column;
        gap: 15px;
      }

      .form-label {
        font-size: 0.9rem;
      }

      small.text-muted {
        font-size: 0.8rem;
      }
    }

    @media (max-width: 576px) {
      .nav-item {
        margin: 5px 0;
      }

      .nav-item .btn {
        width: 100%;
        margin-bottom: 5px;
      }

      h2 {
        font-size: 1.3rem;
        padding: 15px;
      }

      .container {
        padding: 15px;
      }

      .card {
        padding: 15px;
      }

      .btn-success {
        padding: 12px 20px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand fst-italic fw-bold" href="landing_page.php">
      <img src="logo.jpg" alt="Logo" class="logo-navbar me-2">
      EVERGREEN GUIDANCE APPOINTMENT PORTAL
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="btn btn-light btn-sm fw-bold" href="student_appointment.php">Book Appointment</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-light btn-sm fw-bold" href="student_services.php">Services</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-light fw-bold" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            My Account
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">View Account</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger fw-bold" href="student_logout.php" id="logoutBtnNav">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2 class="text-success shadow p-3 mb-5 bg-body rounded p-3 mb-2 bg-success text-success">
    <img src="logo.jpg" alt="Logo" class="logo me-2">Book Appointment
    <img src="logo.jpg" alt="Logo" class="logo me-2">
  </h2>

  <?php if (empty($slots)): ?>
    <div class="no-slots-message">
      <div style="font-size: 60px; color: #ffc107; margin-bottom: 20px;">📅</div>
      <h3 style="color: #6c757d;">No Available Slots</h3>
      <p style="color: #6c757d; margin-top: 10px;">
        There are currently no available appointment slots. Please check back later or contact your counselor.
      </p>
      <a href="landing_page.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
  <?php else: ?>
  <form id="studentAppointmentForm" class="card p-4">
    <input type="hidden" name="counselor_id" value="<?= $counselor['counselor_id'] ?>">
    <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">

    <div class="mb-3">
      <label class="form-label required-field">Purpose of Appointment</label>
      <select name="purpose" id="purpose" class="form-select" required>
        <option value="">-- Select Purpose --</option>
        <option value="Academic concern">Academic concern</option>
        <option value="Personal problem">Personal problem</option>
        <option value="Family issue">Mental Health & Wellness</option>
        <option value="Career guidance">Career guidance</option>
        <option value="Career guidance">Specialized & Administrative Services</option>
        <option value="Counseling follow-up">Counseling follow-up</option>
        <option value="Others">Others</option>
      </select>
    </div>

    <div class="mb-3" id="purpose_other_field">
      <label class="form-label">Please specify</label>
      <input type="text" name="purpose_other" id="purpose_other" class="form-control" placeholder="Enter specific purpose...">
    </div>

    <div class="mb-3">
      <label class="form-label required-field">Urgency Level</label>
      <select name="urgency_level" id="urgency_level" class="form-select" required>
        <option value="">-- Select Urgency --</option>
        <option value="Low">🟢 Low - Can wait for regular scheduling</option>
        <option value="Medium">🟡 Medium - Need attention soon</option>
        <option value="High">🔴 High - Urgent matter</option>
      </select>
      <small class="text-muted">Select the urgency level based on your situation</small>
    </div>

    <div class="mb-3">
      <label class="form-label required-field">Email Address for Confirmation</label>
      <input type="email" name="confirmation_email" id="confirmation_email" class="form-control" 
             value="<?= htmlspecialchars($student['email']) ?>" required 
             placeholder="Enter your email address">
      <small class="text-muted">We'll send appointment confirmation to this email</small>
    </div>

    <div class="mb-3">
      <label class="form-label required-field">Select Available Slot</label>
      <select name="slot" class="form-select" required>
        <option value="">-- Select Time Slot --</option>
        <?php foreach ($slots as $slot): ?>
          <option value="<?= $slot['date'].'|'.$slot['start_time'] ?>">
            <?= date("F j, Y", strtotime($slot['date'])) ?> 
            (<?= date("h:i A", strtotime($slot['start_time'])) ?> - 
             <?= date("h:i A", strtotime($slot['end_time'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">Showing <?= count($slots) ?> available time slot(s)</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Additional Notes/Details (Optional)</label>
      <textarea name="reason" class="form-control" rows="4" placeholder="Provide any additional details about your appointment (optional)..."></textarea>
      <small class="text-muted">Optional: Add any specific details the counselor should know</small>
    </div>

    <button type="submit" class="btn btn-success btn-lg w-100">
      📅 Submit Appointment Request
    </button>
  </form>
  <?php endif; ?>

</div>

 <!-- My Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(90deg, #003d2bff, #889700ff); color: white;">
        <h5 class="modal-title" id="accountModalLabel">My Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container">
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>Full Name:</strong>
              <p><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
            </div>
            <div class="col-md-6">
              <strong>Student ID:</strong>
              <p><?= htmlspecialchars($student['student_id']) ?></p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>Email:</strong>
              <p><?= htmlspecialchars($student['email']) ?></p>
            </div>
            <div class="col-md-6">
              <strong>Grade Level:</strong>
              <p><?= htmlspecialchars($student['grade_level']) ?></p>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <a href="student_change_password.php" class="btn btn-primary fw-bold">🔒 Change Password</a>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

 <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <div class="footer-contact-row">
            <div class="footer-contact-item">
              <span>📍</span>
              <span>123 Education St, Dasmariñas, Cavite</span>
            </div>
            <div class="footer-contact-item">
              <span>📞</span>
              <span>+63 123 456 7890</span>
            </div>
            <div class="footer-contact-item">
              <span>📧</span>
              <span>guidance@evergreen.edu.ph</span>
            </div>
            <div class="footer-contact-item">
              <span>🕒</span>
              <span>Mon - Fri: 8:00 AM - 5:00 PM</span>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 Evergreen Academy Guidance Office. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </footer>

<script>
$(document).ready(function() {
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

  $('#studentAppointmentForm').on('submit', function(e) {
    e.preventDefault();

    if ($('#purpose').val() === 'Others' && $('#purpose_other').val().trim() === '') {
      Swal.fire({
        title: 'Validation Error',
        text: 'Please specify the purpose when selecting "Others"',
        icon: 'warning'
      });
      return;
    }

    var formData = new FormData(this);
    formData.append('action', 'book_appointment');

    Swal.fire({
      title: 'Submitting...',
      text: 'Please wait while we book your appointment.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: 'student_appointment_function.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: 'Success!',
            html: response.message || 'Appointment booked successfully!<br><small>A confirmation email has been sent.</small>',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'student_appointment.php';
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: response.message || 'Failed to book appointment.',
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