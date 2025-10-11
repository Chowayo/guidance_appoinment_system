<?php
include '../session_config.php';
include '../db/dbconn.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $counselor_id = $_SESSION['counselor_id'];
    $stmt = null;

    // deleting a single appointment
    if ($action == 'delete_single' && isset($_GET['id'])) {
        $appointment_id = intval($_GET['id']);
        
        $sql = "DELETE FROM appointments WHERE appointment_id = ? AND counselor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $appointment_id, $counselor_id);

    // clearing all appointments
    } elseif ($action == 'clear_all') {
        
        $sql = "DELETE FROM appointments WHERE counselor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $counselor_id);
    }

    if ($stmt) {
        if ($stmt->execute()) {

            header("Location: counselor_appointment.php");
            exit;
        } else {

            echo "Error: Could not perform the delete operation.";
        }
        $stmt->close();
    } else {

        header("Location: counselor_appointment.php");
        exit;
    }
    
    $conn->close();

} else {

    header("Location: counselor_appointment.php");
    exit;
}
?>