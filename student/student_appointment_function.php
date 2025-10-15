<?php
include '../session_config.php';
include '../db/dbconn.php';
header('Content-Type: application/json');

include '../db/dbconn.php';
require_once 'appointment_confirmation_email.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['student_id'])) {
        throw new Exception('Session expired. Please log in again.');
    }
    $student_id = intval($_SESSION['student_id']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'book_appointment') {
        throw new Exception('Invalid request method or action.');
    }

    $counselor_id = intval($_POST['counselor_id'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $purpose_other = trim($_POST['purpose_other'] ?? '');
    $urgency_level = trim($_POST['urgency_level'] ?? '');
    $confirmation_email = trim($_POST['confirmation_email'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $slot = $_POST['slot'] ?? '';

    if ($counselor_id <= 0) {
        throw new Exception('Invalid counselor selection.');
    }

    if (empty($purpose)) {
        throw new Exception('Please select a purpose for your appointment.');
    }

    if ($purpose === 'Others') {
        if (empty($purpose_other)) {
            throw new Exception('Please specify the purpose when selecting "Others".');
        }
        $final_purpose = $purpose_other;
    } else {
        $final_purpose = $purpose;
    }

    if (empty($urgency_level) || !in_array($urgency_level, ['Low', 'Medium', 'High'])) {
        throw new Exception('Please select a valid urgency level.');
    }

    if (empty($confirmation_email) || !filter_var($confirmation_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address.');
    }

    if (empty($slot)) {
        throw new Exception('Please select an appointment slot.');
    }

    list($date, $time) = explode("|", $slot);
    if (empty($date) || empty($time)) {
        throw new Exception('Invalid slot selected.');
    }

    // Check if student already has a pending/approved appointment
    $check_student = $conn->prepare("
        SELECT 1 FROM appointments 
        WHERE student_id = ? AND status IN ('pending', 'approved')
    ");
    $check_student->bind_param("i", $student_id);
    $check_student->execute();
    $hasAppointment = $check_student->get_result()->num_rows > 0;
    $check_student->close();

    if ($hasAppointment) {
        throw new Exception('You already have an appointment booked. You cannot book another.');
    }

    // Check if the counselor slot is already booked
    $check_counselor = $conn->prepare("
        SELECT 1 FROM appointments 
        WHERE counselor_id = ? AND date = ? AND time = ? AND status = 'approved'
    ");
    $check_counselor->bind_param("iss", $counselor_id, $date, $time);
    $check_counselor->execute();
    $isTaken = $check_counselor->get_result()->num_rows > 0;
    $check_counselor->close();

    if ($isTaken) {
        throw new Exception('This slot is already taken. Please select another.');
    }

    $stmt = $conn->prepare("
        INSERT INTO appointments (student_id, counselor_id, purpose, urgency_level, confirmation_email, date, time, reason, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iissssss", $student_id, $counselor_id, $final_purpose, $urgency_level, $confirmation_email, $date, $time, $reason);

    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        $stmt->close();

        $infoQuery = "SELECT 
                        s.first_name as student_fname, 
                        s.last_name as student_lname,
                        c.first_name as counselor_fname,
                        c.last_name as counselor_lname
                      FROM student s, counselor c
                      WHERE s.student_id = ? AND c.counselor_id = ?";
        $infoStmt = $conn->prepare($infoQuery);
        $infoStmt->bind_param("ii", $student_id, $counselor_id);
        $infoStmt->execute();
        $infoResult = $infoStmt->get_result();
        $info = $infoResult->fetch_assoc();
        $infoStmt->close();

        $studentName = $info['student_fname'] . ' ' . $info['student_lname'];
        $counselorName = $info['counselor_fname'] . ' ' . $info['counselor_lname'];
        
        $appointmentDetails = [
            'date' => $date,
            'time' => $time,
            'purpose' => $final_purpose,
            'urgency_level' => $urgency_level,
            'counselor_name' => $counselorName
        ];

        $emailResult = sendAppointmentConfirmationEmail($confirmation_email, $studentName, $appointmentDetails);

        $conn->close();

        $response['success'] = true;
        $response['message'] = 'Appointment request submitted successfully! A confirmation email has been sent to ' . $confirmation_email;
        $response['appointment_id'] = $appointment_id;
        
        if (!$emailResult['success']) {
            $response['message'] .= ' (Note: Email notification failed to send, but your appointment is booked.)';
        }

        session_write_close();
    } else {
        throw new Exception('Failed to book appointment: Database error.');
    }

} catch (Exception $e) {
    error_log('Appointment Booking Error: ' . $e->getMessage() . ' | POST Data: ' . print_r($_POST, true));
    
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
exit;
?>