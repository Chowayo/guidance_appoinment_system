<?php
include '../session_config.php';
include '../db/dbconn.php';

header('Content-Type: application/json');

include '../db/dbconn.php';
require_once 'appointment_confirmation_email.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['counselor_id'])) {
        throw new Exception('Session expired. Please log in again.');
    }

    $counselor_id = intval($_SESSION['counselor_id']);
    $student_id = intval($_POST['student_id'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $purpose_other = trim($_POST['purpose_other'] ?? '');
    $urgency_level = trim($_POST['urgency_level'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $confirmation_email = trim($_POST['confirmation_email'] ?? '');
    $auto_approve = isset($_POST['auto_approve']) ? 1 : 0;

    // Validation
    if ($student_id <= 0) {
        throw new Exception('Please select a student.');
    }

    if (empty($purpose)) {
        throw new Exception('Please select a purpose for the appointment.');
    }

    // Handle "Others" purpose
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

    if (empty($appointment_date)) {
        throw new Exception('Please select an appointment date.');
    }

    // Check if date is not in the past
    if ($appointment_date < date('Y-m-d')) {
        throw new Exception('Cannot book appointments for past dates.');
    }

    if (empty($appointment_time)) {
        throw new Exception('Please select an appointment time.');
    }

    if (empty($confirmation_email) || !filter_var($confirmation_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address.');
    }

    // Set status based on auto_approve
    $status = $auto_approve ? 'approved' : 'pending';

    // Check if student already has a pending/approved appointment
    $checkQuery = "SELECT 1 FROM appointments 
                   WHERE student_id = ? AND status IN ('pending', 'approved')";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $student_id);
    $checkStmt->execute();
    $hasAppointment = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();

    if ($hasAppointment) {
        throw new Exception('This student already has a pending or approved appointment.');
    }

    // Insert appointment
    $insertQuery = "INSERT INTO appointments 
                    (student_id, counselor_id, purpose, urgency_level, confirmation_email, 
                     date, time, reason, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iisssssss", 
        $student_id, 
        $counselor_id, 
        $final_purpose, 
        $urgency_level, 
        $confirmation_email, 
        $appointment_date, 
        $appointment_time, 
        $reason, 
        $status
    );

    if ($insertStmt->execute()) {
        $appointment_id = $insertStmt->insert_id;
        $insertStmt->close();

        // Get student and counselor information
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
    'date' => $appointment_date,
    'time' => $appointment_time,
    'purpose' => $final_purpose,
    'urgency_level' => $urgency_level,
    'counselor_name' => $counselorName,
    'status' => $status  // ← Make sure this line exists!
];

        // Send confirmation email
        $emailResult = sendAppointmentConfirmationEmail($confirmation_email, $studentName, $appointmentDetails);

        $conn->close();

        $statusText = $auto_approve ? 'created and approved' : 'created (pending approval)';
        $response['success'] = true;
        $response['message'] = "Appointment {$statusText} successfully! Confirmation email sent to {$confirmation_email}";
        
        if (!$emailResult['success']) {
            $response['message'] .= ' (Note: Email notification failed to send.)';
        }

    } else {
        throw new Exception('Failed to create appointment: Database error.');
    }

} catch (Exception $e) {
    error_log('Counselor Add Appointment Error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>