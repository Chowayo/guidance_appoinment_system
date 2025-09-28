<?php
include '../db/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id  = trim($_POST['student_id']); // Make sure this input exists in the form
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $password    = trim($_POST['password']);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if student ID or email already exists
    $stmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ? OR email = ?");
    $stmt->bind_param("is", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "❌ Student ID or email already registered!";
    } else {
        // Insert user
       $grade_level = $_POST['grade_level'];

$stmt = $conn->prepare(
    "INSERT INTO student (student_id, first_name, last_name, email, grade_level, password) 
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssss", $student_id, $first_name, $last_name, $email, $grade_level, $hashed_password);


        if ($stmt->execute()) {
            echo "✅ Registration successful! Redirecting to login...";
            echo "<script>
                setTimeout(function(){
                    window.location.href = 'user_log_reg.php';
                }, 2000);
            </script>";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
