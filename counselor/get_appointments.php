<?php
header('Content-Type: application/json');
include '../session_config.php';
include '../db/dbconn.php';

if (!isset($_SESSION['counselor_id'])) {
    echo json_encode(["data" => []]);
    exit;
}

$counselor_id = $_SESSION['counselor_id'];

$sql = "SELECT a.appointment_id, a.date, a.time, a.reason, a.status,
               a.purpose, a.urgency_level, a.confirmation_email,
               s.student_id, s.first_name, s.last_name, s.grade_level
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
    $studentName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
    $appointmentDate = date("F j, Y", strtotime($row['date']));
    $appointmentTime = date("h:i A", strtotime($row['time']));
    
    if ($row['status'] === 'pending') {
        $statusBadge = '<span class="badge bg-warning">Pending</span>';
        $actions = "
            <a href='counselor_update_function.php?id={$row['appointment_id']}&action=approve' class='btn btn-sm btn-success mb-1'>✅ Approve</a>
            <a href='counselor_update_function.php?id={$row['appointment_id']}&action=decline' class='btn btn-sm btn-danger mb-1'>❌ Decline</a>
            <a href='#' class='reschedule-appointment btn btn-sm btn-info mb-1' 
               data-id='{$row['appointment_id']}' 
               data-student='{$studentName}' 
               data-date='{$appointmentDate}' 
               data-time='{$appointmentTime}'>
               🔄 Reschedule
            </a>
        ";
    } elseif ($row['status'] === 'approved') {
        $statusBadge = '<span class="badge bg-success">Approved</span>';
        $actions = "
            <a href='#' class='reschedule-appointment btn btn-sm btn-info mb-1' 
               data-id='{$row['appointment_id']}' 
               data-student='{$studentName}' 
               data-date='{$appointmentDate}' 
               data-time='{$appointmentTime}'>
               🔄 Reschedule
            </a>
            <a href='counselor_delete_function.php?action=delete_single&id={$row['appointment_id']}' class='btn btn-sm btn-danger mb-1'>🗑️ Delete</a>
        ";
    } elseif ($row['status'] === 'declined') {
        $statusBadge = '<span class="badge bg-danger">Declined</span>';
        $actions = "
            <a href='counselor_delete_function.php?action=delete_single&id={$row['appointment_id']}' class='btn btn-sm btn-danger mb-1'>🗑️ Delete</a>
        ";
    } else {
        $statusBadge = '<span class="badge bg-info">Rescheduled</span>';
        $actions = "
            <a href='#' class='reschedule-appointment btn btn-sm btn-info mb-1' 
               data-id='{$row['appointment_id']}' 
               data-student='{$studentName}' 
               data-date='{$appointmentDate}' 
               data-time='{$appointmentTime}'>
               🔄 Reschedule Again
            </a>
            <a href='counselor_delete_function.php?action=delete_single&id={$row['appointment_id']}' class='btn btn-sm btn-danger mb-1'>🗑️ Delete</a>
        ";
    }

    $urgencyLevel = $row['urgency_level'] ?? 'Low';
    switch ($urgencyLevel) {
        case 'High':
            $urgencyBadge = '<span class="badge urgency-high">🔴 High</span>';
            break;
        case 'Medium':
            $urgencyBadge = '<span class="badge urgency-medium">🟡 Medium</span>';
            break;
        case 'Low':
        default:
            $urgencyBadge = '<span class="badge urgency-low">🟢 Low</span>';
            break;
    }

    $purpose = $row['purpose'] ?? 'Not specified';
    
    $notes = $row['reason'] ?? 'No additional notes';
    $notesShort = strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
    $notesDisplay = '<span class="notes-cell" title="' . htmlspecialchars($notes) . '">' . htmlspecialchars($notesShort) . '</span>';

    $appointments[] = [
        $count++,
        htmlspecialchars($row['student_id']),
        $studentName,
        htmlspecialchars($row['grade_level']),
        htmlspecialchars($purpose),
        $urgencyBadge,
        $appointmentDate,
        $appointmentTime,
        $notesDisplay,
        $statusBadge,
        $actions
    ];
}

$stmt->close();
$conn->close();

echo json_encode(["data" => $appointments]);
?>