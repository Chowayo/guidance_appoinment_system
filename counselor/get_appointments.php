<?php
header('Content-Type: application/json');
session_start();
include "../db/dbconn.php";

if (!isset($_SESSION['counselor_id'])) {
    echo json_encode(["data" => []]);
    exit;
}

$counselor_id = $_SESSION['counselor_id'];

// appointments for this counselor
$sql = "SELECT a.appointment_id, a.date, a.time, a.reason, a.status,
               s.first_name, s.last_name, s.grade_level
        FROM appointments a
        JOIN student s ON a.student_id = s.student_id
        WHERE a.counselor_id = ?
        ORDER BY a.date, a.time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
$count = 1;

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'pending') {
        $statusBadge = '<span class="badge bg-warning">Pending</span>';
        $actions = "
            <a href='counselor_update_function.php?id={$row['appointment_id']}&action=approve' class='btn btn-sm btn-success mb-1'>Approve</a>
            <a href='counselor_update_function.php?id={$row['appointment_id']}&action=decline' class='btn btn-sm btn-danger mb-1'>Decline</a>
            <a href='counselor_update_function.php?id={$row['appointment_id']}&action=reschedule' class='btn btn-sm btn-info mb-1'>Reschedule</a>
        ";
    } elseif ($row['status'] === 'approved') {
        $statusBadge = '<span class="badge bg-success">Approved</span>';
        $actions = "<span class='text-muted'>No actions</span>";
    } elseif ($row['status'] === 'declined') {
        $statusBadge = '<span class="badge bg-danger">Declined</span>';
        $actions = "<span class='text-muted'>No actions</span>";
    } else {
        $statusBadge = '<span class="badge bg-info">Rescheduled</span>';
        $actions = "<span class='text-muted'>No actions</span>";
    }

    $appointments[] = [
        $count++,
        htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        htmlspecialchars($row['grade_level']),
        date("F j, Y", strtotime($row['date'])),
        date("h:i A", strtotime($row['time'])),
        htmlspecialchars($row['reason']),
        $statusBadge,
        $actions
    ];
}

$stmt->close();
$conn->close();

echo json_encode(["data" => $appointments]);
