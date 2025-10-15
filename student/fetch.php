<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../session_config.php';

$host = "sql307.infinityfree.com";
$user = "if0_40171808";
$pass = "Greeny2025";
$db   = "if0_40171808_guidance_db";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die(json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]));
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
?>
