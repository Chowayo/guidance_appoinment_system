<?php
include '../session_config.php';
include '../db/dbconn.php';
require_once 'appointment_status_email.php';

if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    $counselor_id = $_SESSION['counselor_id'];

    $detailsQuery = "SELECT a.*, s.first_name, s.last_name, s.email, 
                            c.first_name as counselor_fname, c.last_name as counselor_lname
                     FROM appointments a
                     JOIN student s ON a.student_id = s.student_id
                     JOIN counselor c ON a.counselor_id = c.counselor_id
                     WHERE a.appointment_id = ? AND a.counselor_id = ?";
    $detailsStmt = $conn->prepare($detailsQuery);
    $detailsStmt->bind_param("ii", $appointment_id, $counselor_id);
    $detailsStmt->execute();
    $appointmentData = $detailsStmt->get_result()->fetch_assoc();
    $detailsStmt->close();

    if (!$appointmentData) {
        $_SESSION['message'] = "Appointment not found or access denied.";
        $_SESSION['message_type'] = "danger";
        header("Location: counselor_appointment.php");
        exit;
    }

    $new_status = '';
    if ($action == 'approve') {
        $new_status = 'approved';
    } elseif ($action == 'decline') {
        $new_status = 'declined';
    } elseif ($action == 'reschedule') {
        $new_status = 'rescheduled'; 
    } else {
        header("Location: counselor_appointment.php");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = ? 
        WHERE appointment_id = ? AND counselor_id = ?
    ");
    
    $stmt->bind_param("sii", $new_status, $appointment_id, $counselor_id);

    if ($stmt->execute()) {
        $stmt->close();
        
        $studentName = $appointmentData['first_name'] . ' ' . $appointmentData['last_name'];
        $studentEmail = $appointmentData['confirmation_email'] ?? $appointmentData['email'];
        $counselorName = $appointmentData['counselor_fname'] . ' ' . $appointmentData['counselor_lname'];

        $appointmentDetails = [
            'date' => $appointmentData['date'],
            'time' => $appointmentData['time'],
            'purpose' => $appointmentData['purpose'] ?? 'Not specified',
            'counselor_name' => $counselorName
        ];

        if (!empty($studentEmail)) {
            $emailResult = sendAppointmentStatusEmail($studentEmail, $studentName, $appointmentDetails, $new_status);
            
            if ($emailResult['success']) {
                $_SESSION['message'] = "Appointment " . $new_status . " successfully! Email notification sent to student.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Appointment " . $new_status . " successfully! (Email notification failed to send.)";
                $_SESSION['message_type'] = "success";
            }
        } else {
            $_SESSION['message'] = "Appointment " . $new_status . " successfully! (No email address available.)";
            $_SESSION['message_type'] = "success";
        }
        
        $conn->close();
        header("Location: counselor_appointment.php");
        exit;
        
    } else {
        $_SESSION['message'] = "Error: Could not update the appointment status.";
        $_SESSION['message_type'] = "danger";
        $stmt->close();
        $conn->close();
        header("Location: counselor_appointment.php");
        exit;
    }

} else {
    header("Location: counselor_appointment.php");
    exit;
}
?>