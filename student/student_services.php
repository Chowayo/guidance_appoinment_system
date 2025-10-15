<?php
include '../session_config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_log_reg.php");
    exit;
}

include '../db/dbconn.php';

$student_id = $_SESSION['student_id'];

// Get student info
$stmt = $conn->prepare("SELECT first_name, last_name FROM student WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guidance Services - Evergreen Academy</title>
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

      .content-wrapper {
          position: relative;
          z-index: 1;
          padding: 30px 15px;
      }

      /* Hero Section with Image */
      .hero-section {
          background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('guidance_hero.jpg') center/cover;
          border-radius: 20px;
          padding: 80px 40px;
          margin-bottom: 40px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.2);
          text-align: center;
          color: white;
          position: relative;
          overflow: hidden;
      }

      .hero-section::before {
          content: "";
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(135deg, rgba(136, 151, 0, 0.3), rgba(0, 61, 43, 0.3));
          z-index: 1;
      }

      .hero-content {
          position: relative;
          z-index: 2;
      }

      .hero-title {
          font-size: 3rem;
          font-weight: 800;
          margin-bottom: 20px;
          text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
      }

      .hero-subtitle {
          font-size: 1.3rem;
          margin-bottom: 30px;
          text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
      }

      .hero-stats {
          display: flex;
          justify-content: center;
          gap: 40px;
          flex-wrap: wrap;
          margin-top: 40px;
      }

      .stat-item {
          background: rgba(255, 255, 255, 0.2);
          backdrop-filter: blur(10px);
          padding: 20px 30px;
          border-radius: 15px;
          border: 2px solid rgba(255, 255, 255, 0.3);
      }

      .stat-number {
          font-size: 2.5rem;
          font-weight: 700;
          display: block;
      }

      .stat-label {
          font-size: 0.9rem;
          opacity: 0.9;
      }

      /* Service Cards with Images */
      .service-category-card {
          background: white;
          border-radius: 20px;
          overflow: hidden;
          margin-bottom: 40px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.1);
          transition: transform 0.3s, box-shadow 0.3s;
      }

      .service-category-card:hover {
          transform: translateY(-10px);
          box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      }

      .service-image-header {
          position: relative;
          height: 300px;
          background-size: cover;
          background-position: center top 30%;
          display: flex;
          align-items: center;
          justify-content: center;
      }

      .service-image-header::before {
          content: "";
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(135deg, rgba(0,0,0,0.5), rgba(0,0,0,0.3));
      }

      .category-header-overlay {
          position: relative;
          z-index: 2;
          text-align: center;
          color: white;
      }

      .category-icon {
          font-size: 4rem;
          margin-bottom: 15px;
          text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
      }

      .category-title {
          font-size: 2rem;
          font-weight: 700;
          margin: 0;
          text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
      }

      .service-content {
          padding: 30px;
      }

      .service-item {
          background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
          border-radius: 12px;
          padding: 20px;
          margin-bottom: 15px;
          transition: all 0.3s;
          border-left: 4px solid transparent;
          position: relative;
          overflow: hidden;
      }

      .service-item::before {
          content: "";
          position: absolute;
          top: 0;
          left: 0;
          width: 4px;
          height: 0;
          background: inherit;
          transition: height 0.3s;
      }

      .service-item:hover {
          background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
          transform: translateX(10px);
          box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }

      .service-item:hover::before {
          height: 100%;
      }

      .service-name {
          font-weight: 600;
          font-size: 1.15rem;
          color: #333;
          margin-bottom: 8px;
          display: flex;
          align-items: center;
          gap: 10px;
      }

      .service-name::before {
          content: "✓";
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 24px;
          height: 24px;
          background: inherit;
          color: white;
          border-radius: 50%;
          font-size: 0.8rem;
          font-weight: bold;
      }

      .service-desc {
          color: #666;
          margin: 0;
          font-size: 0.95rem;
          padding-left: 34px;
      }

      .cta-section {
          background: linear-gradient(rgba(0, 61, 43, 0.9), rgba(136, 151, 0, 0.9)), url('counseling_room.jpg') center/cover;
          color: white;
          border-radius: 20px;
          padding: 60px 40px;
          text-align: center;
          margin-top: 40px;
          margin-bottom: 40px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.2);
          position: relative;
          overflow: hidden;
      }

      .cta-section::before {
          content: "";
          position: absolute;
          top: -50%;
          right: -50%;
          width: 200%;
          height: 200%;
          background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
          animation: pulse 8s ease-in-out infinite;
      }

      @keyframes pulse {
          0%, 100% { transform: scale(1); opacity: 0.5; }
          50% { transform: scale(1.1); opacity: 0.8; }
      }

      .cta-content {
          position: relative;
          z-index: 2;
      }

      .cta-title {
          font-size: 2.5rem;
          font-weight: 700;
          margin-bottom: 20px;
          text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
      }

      .cta-text {
          font-size: 1.2rem;
          margin-bottom: 30px;
          max-width: 800px;
          margin-left: auto;
          margin-right: auto;
      }

      .btn-book-now {
          background: linear-gradient(135deg, #28a745, #20c997);
          color: white;
          padding: 18px 50px;
          border-radius: 50px;
          font-size: 1.2rem;
          font-weight: 700;
          border: none;
          text-decoration: none;
          display: inline-block;
          transition: all 0.3s;
          box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
      }

      .btn-book-now:hover {
          background: linear-gradient(135deg, #20c997, #28a745);
          transform: translateY(-5px) scale(1.05);
          box-shadow: 0 12px 30px rgba(40, 167, 69, 0.6);
          color: white;
      }

      /* Info Cards with Icons */
      .info-cards {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 25px;
          margin-bottom: 40px;
      }

      .info-card {
          background: white;
          border-radius: 15px;
          padding: 30px;
          text-align: center;
          box-shadow: 0 5px 20px rgba(0,0,0,0.1);
          transition: all 0.3s;
      }

      .info-card:hover {
          transform: translateY(-10px);
          box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      }

      .info-card-icon {
          font-size: 3rem;
          margin-bottom: 15px;
      }

      .info-card-title {
          font-size: 1.3rem;
          font-weight: 700;
          color: #003d2b;
          margin-bottom: 10px;
      }

      .info-card-text {
          color: #666;
          font-size: 0.95rem;
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
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .hero-stats {
        gap: 20px;
    }

    .stat-item {
        padding: 15px 20px;
    }

    .category-icon {
        font-size: 3rem;
    }

    .category-title {
        font-size: 1.5rem;
    }

    .service-image-header {
        height: 180px;
    }

    .cta-title {
        font-size: 1.8rem;
    }

    .footer-contact-row {
        flex-direction: column;
        gap: 10px;
    }

    .info-cards {
        grid-template-columns: 1fr;
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

  <div class="content-wrapper">
    <div class="container">
      
      <!-- Hero Section -->
      <div class="hero-section">
        <div class="hero-content">
          <h1 class="hero-title">📖 Guidance & Counseling Services</h1>
          <p class="hero-subtitle">Comprehensive support services designed to help you succeed academically, personally, and professionally</p>
        </div>
      </div>

      <!-- Info Cards -->
      <div class="info-cards">
        <div class="info-card">
          <div class="info-card-icon">🎯</div>
          <h3 class="info-card-title">Personalized Support</h3>
          <p class="info-card-text">One-on-one sessions tailored to your unique needs and goals</p>
        </div>
        <div class="info-card">
          <div class="info-card-icon">🤝</div>
          <h3 class="info-card-title">Confidential & Safe</h3>
          <p class="info-card-text">Your privacy is our priority in a judgment-free environment</p>
        </div>
        <div class="info-card">
          <div class="info-card-icon">⏰</div>
          <h3 class="info-card-title">Flexible Scheduling</h3>
          <p class="info-card-text">Easy online booking that fits your busy schedule</p>
        </div>
        <div class="info-card">
          <div class="info-card-icon">💪</div>
          <h3 class="info-card-title">Proven Results</h3>
          <p class="info-card-text">Track record of helping students achieve their full potential</p>
        </div>
      </div>

      <!-- Academic Services -->
      <div class="service-category-card">
        <div class="service-image-header" style="background-image: url('academic_service.jpg');">
          <div class="category-header-overlay">
            <div class="category-icon">🎓</div>
            <h2 class="category-title">Academic Services</h2>
          </div>
        </div>
        <div class="service-content">
          <div class="service-item" style="border-left-color: #007bff;">
            <div class="service-name" style="color: #007bff;">Study Skills Counseling</div>
            <p class="service-desc">Personalized coaching for note-taking, reading, and study techniques</p>
          </div>
          <div class="service-item" style="border-left-color: #007bff;">
            <div class="service-name" style="color: #007bff;">Time Management Session</div>
            <p class="service-desc">Planning academic schedules, balancing schoolwork</p>
          </div>
          <div class="service-item" style="border-left-color: #007bff;">
            <div class="service-name" style="color: #007bff;">Tutoring Referral</div>
            <p class="service-desc">Recommending subject tutors or peer tutorials</p>
          </div>
          <div class="service-item" style="border-left-color: #007bff;">
            <div class="service-name" style="color: #007bff;">Academic Probation Counseling</div>
            <p class="service-desc">Intervention for failing/low-performing students</p>
          </div>
          <div class="service-item" style="border-left-color: #007bff;">
            <div class="service-name" style="color: #007bff;">Dropout Prevention Session</div>
            <p class="service-desc">For students at risk of leaving school</p>
          </div>
        </div>
      </div>

      <!-- Career Services -->
      <div class="service-category-card">
        <div class="service-image-header" style="background-image: url('career_service.jpg');">
          <div class="category-header-overlay">
            <div class="category-icon">💼</div>
            <h2 class="category-title">Career Services</h2>
          </div>
        </div>
        <div class="service-content">
          <div class="service-item" style="border-left-color: #28a745;">
            <div class="service-name" style="color: #28a745;">Career Interest Assessment</div>
            <p class="service-desc">Standardized test interpretation & counseling</p>
          </div>
          <div class="service-item" style="border-left-color: #28a745;">
            <div class="service-name" style="color: #28a745;">Strand/Track Advising (SHS)</div>
            <p class="service-desc">Choosing appropriate strand (ABM, STEM, HUMSS, TVL)</p>
          </div>
          <div class="service-item" style="border-left-color: #28a745;">
            <div class="service-name" style="color: #28a745;">College Course Counseling</div>
            <p class="service-desc">Aligning student strengths with course/college choices</p>
          </div>
          <div class="service-item" style="border-left-color: #28a745;">
            <div class="service-name" style="color: #28a745;">Scholarship & Application Guidance</div>
            <p class="service-desc">Assistance with requirements & deadlines</p>
          </div>
          <div class="service-item" style="border-left-color: #28a745;">
            <div class="service-name" style="color: #28a745;">Job Application Workshop</div>
            <p class="service-desc">Resume writing, mock interviews, career preparation</p>
          </div>
        </div>
      </div>

      <!-- Personal / Social Services -->
      <div class="service-category-card">
        <div class="service-image-header" style="background-image: url('social_service.jpg');">
          <div class="category-header-overlay">
            <div class="category-icon">🤝</div>
            <h2 class="category-title">Personal / Social Services</h2>
          </div>
        </div>
        <div class="service-content">
          <div class="service-item" style="border-left-color: #fd7e14;">
            <div class="service-name" style="color: #fd7e14;">Peer Relationship Counseling</div>
            <p class="service-desc">Conflict with classmates/friends</p>
          </div>
          <div class="service-item" style="border-left-color: #fd7e14;">
            <div class="service-name" style="color: #fd7e14;">Bullying Intervention Counseling</div>
            <p class="service-desc">Support for victims, behavioral sessions for bullies</p>
          </div>
          <div class="service-item" style="border-left-color: #fd7e14;">
            <div class="service-name" style="color: #fd7e14;">Self-Esteem & Confidence Building</div>
            <p class="service-desc">One-on-one support</p>
          </div>
          <div class="service-item" style="border-left-color: #fd7e14;">
            <div class="service-name" style="color: #fd7e14;">Family Counseling (with parent/guardian)</div>
            <p class="service-desc">Addressing family-related concerns</p>
          </div>
          <div class="service-item" style="border-left-color: #fd7e14;">
            <div class="service-name" style="color: #fd7e14;">Adjustment Counseling</div>
            <p class="service-desc">For new/transferring students</p>
          </div>
        </div>
      </div>

      <!-- Mental Health & Wellness -->
      <div class="service-category-card">
        <div class="service-image-header" style="background-image: url('mental_healths.jpg');">
          <div class="category-header-overlay">
            <div class="category-icon">💚</div>
            <h2 class="category-title">Mental Health & Wellness</h2>
          </div>
        </div>
        <div class="service-content">
          <div class="service-item" style="border-left-color: #20c997;">
            <div class="service-name" style="color: #20c997;">Stress Management Counseling</div>
            <p class="service-desc">Healthy coping mechanisms</p>
          </div>
          <div class="service-item" style="border-left-color: #20c997;">
            <div class="service-name" style="color: #20c997;">Anxiety/Depression Screening & Counseling</div>
            <p class="service-desc">Initial support (referral if needed)</p>
          </div>
          <div class="service-item" style="border-left-color: #20c997;">
            <div class="service-name" style="color: #20c997;">Grief and Loss Counseling</div>
            <p class="service-desc">For bereavement or personal loss</p>
          </div>
          <div class="service-item" style="border-left-color: #20c997;">
            <div class="service-name" style="color: #20c997;">Crisis Intervention Counseling</div>
            <p class="service-desc">Immediate sessions for urgent/emergency cases (Bullying)</p>
          </div>
          <div class="service-item" style="border-left-color: #20c997;">
            <div class="service-name" style="color: #20c997;">Group Counseling Sessions</div>
            <p class="service-desc">Themed small groups (e.g., coping skills, social skills)</p>
          </div>
        </div>
      </div>

      <!-- Specialized & Administrative Services -->
      <div class="service-category-card">
        <div class="service-image-header" style="background-image: url('specialized_service.jpg');">
          <div class="category-header-overlay">
            <div class="category-icon">⚕️</div>
            <h2 class="category-title">Specialized & Administrative Services</h2>
          </div>
        </div>
        <div class="service-content">
          <div class="service-item" style="border-left-color: #6f42c1;">
            <div class="service-name" style="color: #6f42c1;">Students with Special Needs Counseling</div>
            <p class="service-desc">Adjustment plans, IEP support</p>
          </div>
          <div class="service-item" style="border-left-color: #6f42c1;">
            <div class="service-name" style="color: #6f42c1;">Behavioral Intervention Session</div>
            <p class="service-desc">For students with repeated misconduct</p>
          </div>
          <div class="service-item" style="border-left-color: #6f42c1;">
            <div class="service-name" style="color: #6f42c1;">Parental Consultation Appointment</div>
            <p class="service-desc">Progress and behavioral updates with parents</p>
          </div>
          <div class="service-item" style="border-left-color: #6f42c1;">
            <div class="service-name" style="color: #6f42c1;">Follow-up Counseling</div>
            <p class="service-desc">Scheduled after initial sessions for monitoring</p>
          </div>
          <div class="service-item" style="border-left-color: #6f42c1;">
            <div class="service-name" style="color: #6f42c1;">Referral Session</div>
            <p class="service-desc">Documenting and referring to psychologists, psychiatrists, or external agencies</p>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="cta-section">
        <div class="cta-content">
          <h2 class="cta-title">💫 Ready to Take the Next Step?</h2>
          <p class="cta-text">Don't face your challenges alone. Our professional counselors are here to guide, support, and empower you to overcome obstacles and reach your full potential. Book your appointment today and start your journey to success!</p>
          <a href="student_appointment.php" class="btn-book-now">📅 Schedule Your Appointment</a>
        </div>
      </div>

    </div>
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
              <p><?= htmlspecialchars($student_id) ?></p>
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

<!-- Footer -->
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

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p>&copy; 2025 Evergreen Academy Guidance Office. All Rights Reserved.</p>
      </div>
    </div>
  </div>
</footer>

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

  document.getElementById("logoutBtnNav").addEventListener("click", function(e) {
    handleLogout(e, this.href);
  });
  </script>

  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>