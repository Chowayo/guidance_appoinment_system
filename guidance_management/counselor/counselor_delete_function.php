<?php
session_start();
include '../db/dbconn.php';


if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

// ✅ 2. Check if an action is specified in the URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $counselor_id = $_SESSION['counselor_id'];
    $stmt = null;

    // ✅ 3. Logic for deleting a single appointment
    if ($action == 'delete_single' && isset($_GET['id'])) {
        $appointment_id = intval($_GET['id']);
        
        // Prepare query to delete one specific appointment owned by this counselor
        $sql = "DELETE FROM appointments WHERE appointment_id = ? AND counselor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $appointment_id, $counselor_id);

    // ✅ 4. Logic for clearing all appointments
    } elseif ($action == 'clear_all') {
        
        // Prepare query to delete all appointments for this counselor
        $sql = "DELETE FROM appointments WHERE counselor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $counselor_id);
    }

    // ✅ 5. Execute the query and redirect
    if ($stmt) {
        if ($stmt->execute()) {
            // On success, go back to the appointments list
            header("Location: counselor_appointment.php");
            exit;
        } else {
            // Optional: Handle any database errors
            echo "Error: Could not perform the delete operation.";
        }
        $stmt->close();
    } else {
        // If action is invalid or ID is missing, just redirect
        header("Location: counselor_appointment.php");
        exit;
    }
    
    $conn->close();

} else {
    // If no action is specified in the URL, redirect back
    header("Location: counselor_appointment.php");
    exit;
}
?>