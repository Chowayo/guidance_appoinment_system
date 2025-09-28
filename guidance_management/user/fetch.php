<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "guidance_appointment");
if ($mysqli->connect_errno) {
    echo json_encode(["data"=>[]]);
    exit;
}

$grade_level = $_SESSION['grade_level'] ?? null;

if ($grade_level) {
    $stmt = $mysqli->prepare("SELECT student_id, first_name, last_name, email, grade_level 
                              FROM student 
                              WHERE grade_level = ?");
    $stmt->bind_param("s", $grade_level);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["data" => $data]);
} else {
    echo json_encode(["data"=>[]]);
}
