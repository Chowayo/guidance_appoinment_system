<?php
// Set timezone first
date_default_timezone_set('Asia/Manila');

// Your existing database connection
$servername = "localhost";
$username = "root"; // or your username
$password = ""; // or your password
$dbname = "guidance_appointment";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Synchronize MySQL timezone with PHP
$conn->query("SET time_zone = '+08:00'");

// Optional: Set charset
$conn->set_charset("utf8mb4");
?>