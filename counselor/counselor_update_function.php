<?php
session_start();
include '../db/dbconn.php';

// ✅ 1. Check if a counselor is logged in
if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

// ✅ 2. Check if the appointment ID and action are provided in the URL from the link's href
if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    $counselor_id = $_SESSION['counselor_id'];

    // ✅ 3. Determine the new status based on the action clicked
    $new_status = '';
    if ($action == 'approve') {
        $new_status = 'approved';
    } elseif ($action == 'decline') {
        $new_status = 'declined';
    } elseif ($action == 'reschedule') {
        // For now, this just updates the status.
        // You might want to redirect to a different page to pick a new time and date.
        $new_status = 'rescheduled'; 
    } else {
        // If the action is invalid, do nothing and go back.
        header("Location: counselor_appointment.php");
        exit;
    }

    // ✅ 4. Prepare and execute the UPDATE query
    // This query updates the 'status' column in your 'appointments' table.
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = ? 
        WHERE appointment_id = ? AND counselor_id = ?
    ");
    
    // We also check for counselor_id to ensure a counselor can only update their own appointments.
    $stmt->bind_param("sii", $new_status, $appointment_id, $counselor_id);

    // ✅ 5. Redirect back to the appointments page after updating
    if ($stmt->execute()) {
        header("Location: counselor_appointment.php");
        exit;
    } else {
        // Optional: Handle any potential database errors
        echo "Error: Could not update the appointment status.";
    }

    $stmt->close();
    $conn->close();

} else {
    // If required parameters are missing from the URL, just redirect back.
    header("Location: counselor_appointment.php");
    exit;
}
?>