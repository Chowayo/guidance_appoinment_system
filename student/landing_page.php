<?php
include '../session_config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_log_reg.php");
    exit;
}

include '../db/dbconn.php';

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, grade_level FROM student WHERE student_id=?");
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

$upcomingStmt = $conn->prepare("SELECT a.*, c.first_name as counselor_fname, c.last_name as counselor_lname 
                                 FROM appointments a 
                                 JOIN counselor c ON a.counselor_id = c.counselor_id 
                                 WHERE a.student_id = ? AND a.date >= CURDATE()
                                 ORDER BY a.date ASC, a.time ASC LIMIT 3");
$upcomingStmt->bind_param("i", $student_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
$upcomingAppointments = $upcomingResult->fetch_all(MYSQLI_ASSOC);
$upcomingStmt->close();

$conn->close();

$quotes = [
    "Small progress is still progress. Keep going! 💪",
    "Your mental health is a priority, not a luxury. 🌟",
    "It's okay to ask for help. That's what we're here for. 🤝",
    "Every day is a fresh start. You've got this! ☀️",
    "Be kind to yourself. You're doing better than you think. 💚"
];
$dailyQuote = $quotes[array_rand($quotes)];

$guidanceResources = [
    ['icon' => '🧠', 'title' => 'Managing Exam Stress', 'desc' => 'Tips and techniques for handling academic pressure'],
    ['icon' => '💬', 'title' => 'When to Ask for Help', 'desc' => 'Recognizing signs you need support'],
    ['icon' => '🎯', 'title' => 'Planning Your Career Path', 'desc' => 'Explore career options and make informed decisions']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - Evergreen Academy</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <script src="../js/sweetalert2@11.js"></script>
  <style>
      body {
          background: linear-gradient(135deg, #e0eb7dff, #81ffa0ff);
          position: relative;
          background-repeat: no-repeat;
          background-attachment: fixed;
          background-size: cover;
          min-height: 100vh;
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

      .navbar {
          background: linear-gradient(90deg, #889700ff, #003d2bff);
          box-shadow: 0 4px 15px rgba(0,0,0,0.2);
          padding: 15px 0;
      }

      .navbar-brand {
          font-size: 1.1rem;
          letter-spacing: 0.5px;
          transition: all 0.3s;
      }

      .navbar-brand:hover {
          transform: scale(1.02);
          opacity: 0.9;
      }

      .nav-item {
          margin: 0 8px;
      }

      .nav-item .btn {
          padding: 8px 20px;
          border-radius: 8px;
          font-weight: 600;
          transition: all 0.3s;
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
          transition: all 0.3s;
          background: rgba(255,255,255,0.1);
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
          transition: all 0.3s;
      }

      .dropdown-item:hover {
          background: #f8f9fa;
          padding-left: 25px;
      }

      .dropdown-item.text-danger:hover {
          background: #ffe5e5;
      }

      .logo-navbar {
          height: 45px;
          width: auto;
          transition: all 0.3s;
          filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
      }

      .logo-navbar:hover {
          transform: scale(1.05);
      }

      .dashboard-wrapper {
          position: relative;
          z-index: 1;
          padding: 30px 15px;
      }

      .welcome-section {
          background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
          border-radius: 20px;
          padding: 40px;
          margin-bottom: 30px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.15);
          border: 3px solid rgba(136, 151, 0, 0.3);
      }

      .welcome-title {
          font-size: 2.5rem;
          font-weight: 700;
          color: #003d2b;
          margin-bottom: 10px;
      }

      .quote-box {
          background: linear-gradient(135deg, #fff3cd, #ffeaa7);
          padding: 15px 20px;
          border-radius: 15px;
          border-left: 5px solid #ffc107;
          margin-top: 20px;
          font-style: italic;
          font-size: 1.1rem;
      }

      .dashboard-card {
          background: white;
          border-radius: 15px;
          padding: 25px;
          margin-bottom: 20px;
          box-shadow: 0 5px 20px rgba(0,0,0,0.1);
          transition: transform 0.3s, box-shadow 0.3s;
          border-top: 4px solid;
          height: 100%;
      }

      .dashboard-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      }

      .card-upcoming {
          border-top-color: #28a745;
      }

      .card-resources {
          border-top-color: #6f42c1;
      }

      .card-info {
          border-top-color: #ffc107;
      }

      .card-icon {
          font-size: 3rem;
          margin-bottom: 15px;
      }

      .card-title {
          font-size: 1.3rem;
          font-weight: 600;
          margin-bottom: 15px;
          color: #333;
      }

      .appointment-details {
          background: #f8f9fa;
          padding: 20px;
          border-radius: 10px;
          margin-top: 15px;
          margin-bottom: 15px;
      }

      .detail-row {
          display: flex;
          justify-content: space-between;
          padding: 8px 0;
          border-bottom: 1px solid #dee2e6;
      }

      .detail-row:last-child {
          border-bottom: none;
      }

      .detail-label {
          font-weight: 600;
          color: #666;
      }

      .detail-value {
          color: #333;
          font-weight: 500;
          text-align: right;
      }

      .status-badge {
          display: inline-block;
          padding: 5px 15px;
          border-radius: 20px;
          font-size: 0.9rem;
          font-weight: 600;
      }

      .status-approved {
          background: #d4edda;
          color: #155724;
      }

      .status-pending {
          background: #fff3cd;
          color: #856404;
      }

      .status-declined {
          background: #f8d7da;
          color: #721c24;
      }

      .status-rescheduled {
          background: #d1ecf1;
          color: #0c5460;
      }

      .quick-actions {
          display: flex;
          gap: 15px;
          flex-wrap: wrap;
          margin-top: 20px;
      }

      .action-btn {
          flex: 1;
          min-width: 150px;
          padding: 15px 20px;
          border-radius: 10px;
          text-decoration: none;
          text-align: center;
          font-weight: 600;
          transition: all 0.3s;
          border: none;
          cursor: pointer;
          display: inline-block;
      }

      .btn-book {
          background: linear-gradient(135deg, #28a745, #20c997);
          color: white;
      }

      .btn-book:hover {
          background: linear-gradient(135deg, #20c997, #28a745);
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
          color: white;
      }

      .btn-view {
          background: linear-gradient(135deg, #007bff, #0056b3);
          color: white;
      }

      .btn-view:hover {
          background: linear-gradient(135deg, #0056b3, #007bff);
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
          color: white;
      }

      .btn-services {
          background: linear-gradient(135deg, #6f42c1, #5a32a3);
          color: white;
      }

      .btn-services:hover {
          background: linear-gradient(135deg, #5a32a3, #6f42c1);
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(111, 66, 193, 0.3);
          color: white;
      }

      .resource-item {
          background: #f8f9fa;
          border-radius: 10px;
          padding: 15px;
          margin-bottom: 15px;
          transition: all 0.3s;
          cursor: pointer;
          border: 2px solid transparent;
      }

      .resource-item:hover {
          background: #e9ecef;
          border-color: #6f42c1;
          transform: translateX(5px);
      }

      .resource-icon {
          font-size: 2rem;
          margin-right: 15px;
      }

      .resource-title {
          font-weight: 600;
          color: #333;
          margin-bottom: 5px;
      }

      .resource-desc {
          font-size: 0.9rem;
          color: #666;
          margin: 0;
      }

      .info-section {
          background: #f8f9fa;
          border-radius: 10px;
          padding: 20px;
          margin-top: 20px;
          border-left: 5px solid #17a2b8;
      }

      .info-section h4 {
          color: #17a2b8;
          font-weight: 700;
          margin-bottom: 15px;
          font-size: 1.1rem;
      }

      .info-section ul {
          margin-bottom: 0;
          padding-left: 20px;
      }

      .info-section li {
          margin-bottom: 10px;
          color: #333;
      }

      .info-section li:last-child {
          margin-bottom: 0;
      }

      .email-box {
          background: linear-gradient(135deg, #fff3cd, #ffe69c);
          padding: 15px 20px;
          border-radius: 10px;
          margin-bottom: 20px;
          border-left: 5px solid #ffc107;
      }

      .email-box strong {
          color: #856404;
      }

      .appointment-separator {
          border-top: 2px dashed #dee2e6;
          margin: 20px 0;
      }

      .status-note {
          background: #e9ecef;
          padding: 10px 15px;
          border-radius: 8px;
          margin-top: 10px;
          font-size: 0.9rem;
          font-style: italic;
      }

.footer {
  background: linear-gradient(90deg, #003d2bff, #889700ff);
  color: white;
  padding: 40px 0 20px;
  position: relative;
  z-index: 1;
  width: 100%;
}

html, body {
  height: 100%;
  display: flex;
  flex-direction: column;
}

body > *:not(footer) {
  flex: 1 0 auto;
}

footer {
  flex-shrink: 0;
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

@media (max-width: 768px) {
  .footer-contact-row {
    flex-direction: column;
    gap: 10px;
  }
}

  </style>
</head>
<body>

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

  <div class="dashboard-wrapper">
    <div class="container">
      
      <div class="welcome-section">
        <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($student['first_name']); ?>! 👋</h1>
        <p class="lead">Ready to book a new counseling appointment today?</p>
        <div class="quote-box">
          💡 <?= $dailyQuote ?>
        </div>
      </div>

      <div class="row">
        
        <div class="col-lg-6 mb-4">
          <div class="dashboard-card card-upcoming">
            <div class="card-icon text-center">📅</div>
            <h3 class="card-title text-center">Your Appointments</h3>
            
            <?php if (!empty($upcomingAppointments)): ?>
              <?php foreach ($upcomingAppointments as $index => $appointment): ?>
                <?php if ($index > 0): ?>
                  <div class="appointment-separator"></div>
                <?php endif; ?>
                
                <div class="appointment-details">
                  <div class="detail-row">
                    <span class="detail-label">Counselor:</span>
                    <span class="detail-value">
                      <?= htmlspecialchars($appointment['counselor_fname'] . ' ' . $appointment['counselor_lname']) ?>
                    </span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?= date('F j, Y', strtotime($appointment['date'])) ?></span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value"><?= date('h:i A', strtotime($appointment['time'])) ?></span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Purpose:</span>
                    <span class="detail-value"><?= htmlspecialchars($appointment['purpose']) ?></span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span>
                      <?php
                      $status = strtolower($appointment['status']);
                      if ($status === 'approved') {
                          echo '<span class="status-badge status-approved">✅ Approved</span>';
                      } elseif ($status === 'declined') {
                          echo '<span class="status-badge status-declined">❌ Declined</span>';
                      } elseif ($status === 'rescheduled') {
                          echo '<span class="status-badge status-rescheduled">🔄 Rescheduled</span>';
                      } else {
                          echo '<span class="status-badge status-pending">⏳ Pending</span>';
                      }
                      ?>
                    </span>
                  </div>
                  
                  <?php if ($status === 'approved'): ?>
                    <div class="status-note text-success">
                      ✅ Your appointment has been confirmed! Please arrive 5 minutes early.
                    </div>
                  <?php elseif ($status === 'declined'): ?>
                    <div class="status-note text-danger">
                      ❌ This appointment was declined. Please book a different time slot.
                    </div>
                  <?php elseif ($status === 'rescheduled'): ?>
                    <div class="status-note text-info">
                      🔄 This appointment has been rescheduled. Check your email for details.
                    </div>
                  <?php else: ?>
                    <div class="status-note text-warning">
                      ⏳ Waiting for counselor approval. You'll receive an email notification soon.
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <p class="text-muted mb-3">You have no upcoming appointments.</p>
                <a href="student_appointment.php" class="btn btn-success">📅 Book Your Appointment</a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div class="dashboard-card card-info">
            <div class="d-flex align-items-start mb-3">
              <div class="flex-grow-1">
                <h3 class="card-title">Important Information</h3>
              </div>
            </div>

            <div class="info-section">
              <h4>ℹ️ How to Book an Appointment</h4>
              <ul>
                <li><strong>Step 1:</strong> Browse services to identify what support you need</li>
                <li><strong>Step 2:</strong> Click "Book an Appointment"</li>
                <li><strong>Step 3:</strong> Choose date, time, and specify the service</li>
                <li><strong>Step 4:</strong> Wait for email confirmation or check in your dashboard for the status</li>
                <li><strong>Step 5:</strong> Attend your session on the scheduled date</li>
              </ul>
            </div> <br>

            <div class="email-box">
              <p class="mb-2">
                <strong>📢 Email Notifications:</strong> All appointment confirmations and updates will be sent to:
              </p>
              <p class="mb-2">
                <strong>📨 <?= htmlspecialchars($student['email']) ?></strong>
              </p>
              <p class="mb-0">
                <small>Please check your inbox regularly to stay updated.</small>
              </p>
            </div>

            
          </div>
        </div>

      </div>

      <div class="row">
        
        <div class="col-12 mb-4">
          <div class="dashboard-card card-resources">
            <div class="card-icon text-center">📖</div>
            <h3 class="card-title text-center">Services Offered</h3>
            
            <div class="row">
              <?php foreach ($guidanceResources as $resource): ?>
                <div class="col-md-4 mb-3">
                  <div class="resource-item d-flex align-items-center">
                    <div class="resource-icon"><?= $resource['icon'] ?></div>
                    <div>
                      <div class="resource-title"><?= $resource['title'] ?></div>
                      <p class="resource-desc"><?= $resource['desc'] ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-3">
              <a href="student_services.php" class="action-btn btn-services">
                📚 View All Services
              </a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

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
            <i>📍</i>
            <span>123 Education St, Dasmariñas, Cavite</span>
          </div>
          <div class="footer-contact-item">
            <i>📞</i>
            <span>+63 123 456 7890</span>
          </div>
          <div class="footer-contact-item">
            <i>📧</i>
            <span>guidance@evergreen.edu.ph</span>
          </div>
          <div class="footer-contact-item">
            <i>🕒</i>
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

  <script src="../js/bootstrap.bundle.min.js"></script>

  <script>
  function handleLogout(event, url) {
    event.preventDefault();
    Swal.fire({
      title: "Are you sure?",
      text: "You will be logged out.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#003d2b",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Yes, logout"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  }

  document.addEventListener("DOMContentLoaded", function() {
    const logoutBtnNav = document.getElementById("logoutBtnNav");

    if (logoutBtnNav) {
      logoutBtnNav.addEventListener("click", function(e) {
        e.preventDefault();
        Swal.fire({
          title: "Are you sure you want to log out?",
          text: "You'll need to log in again to access your dashboard.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#003d2b",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Yes, log me out",
          cancelButtonText: "Cancel"
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: "Logging out...",
              icon: "success",
              timer: 1200,
              showConfirmButton: false
            });
            setTimeout(() => {
              window.location.href = logoutBtnNav.href;
            }, 1200);
          }
        });
      });
    }
  });
  </script>

</body>
</html>