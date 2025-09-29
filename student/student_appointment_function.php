<?php
session_start();
header('Content-Type: application/json');

include '../db/dbconn.php';

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
    $reason = trim($_POST['reason'] ?? '');
    $slot = $_POST['slot'] ?? '';

    if ($counselor_id <= 0 || empty($reason) || empty($slot)) {
        throw new Exception('Missing required fields (counselor, reason, or slot).');
    }

    list($date, $time) = explode("|", $slot);
    if (empty($date) || empty($time)) {
        throw new Exception('Invalid slot selected.');
    }

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
        INSERT INTO appointments (student_id, counselor_id, date, time, reason, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iisss", $student_id, $counselor_id, $date, $time, $reason);

    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        $stmt->close();
        $conn->close();

        $response['success'] = true;
        $response['message'] = 'Appointment request submitted successfully! Your appointment is pending approval.';
        $response['appointment_id'] = $appointment_id;

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