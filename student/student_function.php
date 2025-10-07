<?php
include '../db/dbconn.php';
require_once 'email_verification.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = intval(trim($_POST['student_id'])); 
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $password    = trim($_POST['password']);
    $grade_level = trim($_POST['grade_level']);

    // SERVER-SIDE PASSWORD VALIDATION
    $errors = [];
    
    // Check minimum length (8 characters)
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        echo "Password validation failed:\n";
        foreach ($errors as $error) {
            echo "- " . $error . "\n";
        }
        exit;
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if student ID or email already exists
    $stmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ? OR email = ?");
    $stmt->bind_param("is", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "already registered"; // Match the JavaScript check
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $stmt->close();
    
    // Generate verification token
    $verificationToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insert student with verification fields
    $insertStmt = $conn->prepare(
        "INSERT INTO student (student_id, first_name, last_name, email, grade_level, password, verification_token, token_expiry, is_verified) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)"
    );
    $insertStmt->bind_param("isssssss", $student_id, $first_name, $last_name, $email, $grade_level, $hashed_password, $verificationToken, $tokenExpiry);

    if ($insertStmt->execute()) {
        // Send verification email
        $studentName = $first_name . ' ' . $last_name;
        $emailResult = sendVerificationEmail($email, $studentName, $verificationToken);
        
        if ($emailResult['success']) {
            echo "Registration successful";
        } else {
            echo "Registration successful but failed to send verification email. Please contact support.";
        }
    } else {
        echo "Error: " . $insertStmt->error;
    }

    $insertStmt->close();
    $conn->close();
}
?>