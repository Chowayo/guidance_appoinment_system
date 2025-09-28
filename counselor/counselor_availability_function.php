<?php
include "../db/dbconn.php";
session_start();

$counselor_id = $_SESSION['counselor_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dates = $_POST['available_date'];
    $starts = $_POST['start_time'];
    $ends   = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO counselor_availability (counselor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($dates); $i++) {
        if (!empty($dates[$i]) && !empty($starts[$i]) && !empty($ends[$i])) {
            $stmt->bind_param("isss", $counselor_id, $dates[$i], $starts[$i], $ends[$i]);
            $stmt->execute();
        }
    }

    echo "<script>alert('Availability saved successfully!'); window.location='set_availability.php';</script>";
}
?>