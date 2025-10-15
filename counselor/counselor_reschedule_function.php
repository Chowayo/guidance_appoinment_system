<?php
include '../session_config.php';
include '../db/dbconn.php';
header('Content-Type: application/json');

include '../db/dbconn.php';
require_once 'appointment_status_email.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['counselor_id'])) {
        throw new Exception('Session expired. Please log in again.');
    }
    
    $counselor_id = intval($_SESSION['counselor_id']);
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = trim($_POST['new_date'] ?? '');
    $new_time = trim($_POST['new_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    
    if ($appointment_id <= 0) {
        throw new Exception('Invalid appointment ID.');
    }
    
    if (empty($new_date) || empty($new_time)) {
        throw new Exception('Please provide both new date and time.');
    }
    
    if ($new_date < date('Y-m-d')) {
        throw new Exception('Cannot reschedule to a past date.');
    }
    
    $checkQuery = "SELECT a.*, s.first_name, s.last_name, s.email, 
                   c.first_name as counselor_fname, c.last_name as counselor_lname
                   FROM appointments a
                   JOIN student s ON a.student_id = s.student_id
                   JOIN counselor c ON a.counselor_id = c.counselor_id
                   WHERE a.appointment_id = ? AND a.counselor_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $appointment_id, $counselor_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception('Appointment not found or you do not have permission to reschedule it.');
    }
    
    $appointment = $result->fetch_assoc();
    $checkStmt->close();
    
    $updateQuery = "UPDATE appointments 
                    SET date = ?, time = ?, status = 'rescheduled' 
                    WHERE appointment_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    
    if (!$updateStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $updateStmt->bind_param("ssi", $new_date, $new_time, $appointment_id);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to reschedule appointment: ' . $updateStmt->error);
    }
    
    $updateStmt->close();
    
    $studentName = $appointment['first_name'] . ' ' . $appointment['last_name'];
    $counselorName = $appointment['counselor_fname'] . ' ' . $appointment['counselor_lname'];
    $studentEmail = $appointment['email'];
    
    $appointmentDetails = [
        'appointment_id' => $appointment_id,
        'date' => $new_date,
        'time' => $new_time,
        'purpose' => $appointment['purpose'],
        'urgency_level' => $appointment['urgency_level'],
        'counselor_name' => $counselorName
    ];
    
    $emailResult = sendAppointmentStatusEmail($studentEmail, $studentName, $appointmentDetails, 'rescheduled');
    
    $response['success'] = true;
    $response['message'] = 'Appointment rescheduled successfully! Student has been notified via email.';
    
    if (!$emailResult['success']) {
        $response['message'] .= ' (Note: Email notification failed to send.)';
    }
    
} catch (Exception $e) {
    error_log('Reschedule Appointment Error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
exit;
?>