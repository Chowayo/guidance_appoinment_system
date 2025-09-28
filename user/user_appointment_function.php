<?php
session_start();
include '../db/dbconn.php';

// Ensure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: user_log_reg.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id   = intval($_POST['student_id']);
    $counselor_id = intval($_POST['counselor_id']);
    $reason       = trim($_POST['reason']);
    $slot         = $_POST['slot'];

    // Split the slot into date & time
    list($date, $time) = explode("|", $slot);

    // 1. Check if the student already has a pending or approved appointment
    $check_student = $conn->prepare("
        SELECT 1 FROM appointments 
        WHERE student_id=? AND status IN ('pending','approved')
    ");
    $check_student->bind_param("i", $student_id);
    $check_student->execute();
    $hasAppointment = $check_student->get_result()->num_rows > 0;
    $check_student->close();

    if ($hasAppointment) {
        echo "<script>
                alert('You already have an appointment booked. You cannot book another.');
                window.location.href='user_appointment.php';
              </script>";
        exit;
    }

    // 2. Check if the counselor slot is already booked
    $check_counselor = $conn->prepare("
        SELECT 1 FROM appointments 
        WHERE counselor_id=? AND date=? AND time=? AND status='approved'
    ");
    $check_counselor->bind_param("iss", $counselor_id, $date, $time);
    $check_counselor->execute();
    $isTaken = $check_counselor->get_result()->num_rows > 0;
    $check_counselor->close();

    if ($isTaken) {
        echo "<script>
                alert('This slot is already taken. Please select another.');
                window.location.href='user_appointment.php';
              </script>";
        exit;
    }

    // 3. Insert new appointment as pending
    $stmt = $conn->prepare("
        INSERT INTO appointments (student_id, counselor_id, date, time, reason, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iisss", $student_id, $counselor_id, $date, $time, $reason);

    if ($stmt->execute()) {
        echo "<script>
                alert('Appointment request submitted successfully!');
                window.location.href='landing_page.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
