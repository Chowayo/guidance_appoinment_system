<?php
include '../session_config.php';
include '../db/dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['counselor_id'])) {
    echo json_encode(['data' => []]);
    exit;
}

$counselor_id = $_SESSION['counselor_id'];

$sql = "SELECT a.appointment_id, a.date, a.time, a.reason, a.status,
               s.first_name, s.last_name, s.grade_level
        FROM appointments a
        JOIN student s ON a.student_id = s.student_id
        WHERE a.counselor_id = ?
        ORDER BY a.date, a.time";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['data' => []]);
    exit;
}

$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'appointment_id' => $row['appointment_id'],
        'student_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']), 
        'grade_level' => $row['grade_level'],
        'time' => $row['time'],
        'reason' => htmlspecialchars($row['reason']),
        'status' => $row['status']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['data' => $data]);
?>