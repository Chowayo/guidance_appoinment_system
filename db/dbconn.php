<?php
date_default_timezone_set('Asia/Manila');

if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "guidance_appointment";
} else {

    $servername = "sql307.infinityfree.com";
    $username   = "if0_40171808";
    $password   = "Greeny2025";
    $dbname     = "if0_40171808_guidance_db";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+08:00'");

$conn->set_charset("utf8mb4");
?>
