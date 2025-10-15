<?php
date_default_timezone_set('Asia/Manila');

include '../session_config.php';
include '../db/dbconn.php';

require_once 'email_verification.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = intval($_POST["student_id"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $grade_level = trim($_POST["grade_level"]);
    
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        echo "Password validation failed:\n" . implode("\n", $errors);
        exit;
    }
    
    $checkStmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ? OR email = ?");
    $checkStmt->bind_param("is", $student_id, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo "This Student ID or Email is already registered";
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $verificationToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $conn->prepare("INSERT INTO student (student_id, first_name, last_name, email, grade_level, password, verification_token, token_expiry, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("isssssss", $student_id, $first_name, $last_name, $email, $grade_level, $hashed_password, $verificationToken, $tokenExpiry);
    
    if ($stmt->execute()) {
        $studentFullName = $first_name . ' ' . $last_name;
        $emailResult = sendVerificationEmail($email, $studentFullName, $verificationToken);
        
        if ($emailResult['success']) {
            echo "Registration successful! Please check your email to verify your account.";
        } else {
            echo "Registration successful but failed to send verification email. Please contact support.";
        }
    } else {
        echo "Registration failed: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method";
}
?>