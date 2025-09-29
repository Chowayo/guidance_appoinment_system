<?php
session_start();
include '../db/dbconn.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    $counselor_id = $_SESSION['counselor_id'];

    // status based on the action clicked
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
        header("Location: counselor_appointment.php");
        exit;
    } else {
        echo "Error: Could not update the appointment status.";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: counselor_appointment.php");
    exit;
}
?>