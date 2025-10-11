<?php
include '../session_config.php';
include '../db/dbconn.php';
if (isset($_SESSION['student_id']) || isset($_SESSION['counselor_id'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
    echo json_encode(['status' => 'ok']);
}
?>