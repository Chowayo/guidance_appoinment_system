<?php
include '../db/dbconn.php';

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    $stmt = $conn->prepare("DELETE FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        echo "Student deleted successfully";
    } else {
        echo "Error deleting student";
    }

    $stmt->close();
    $conn->close();
}