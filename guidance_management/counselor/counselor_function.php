<?php
session_start();
include '../db/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counselor_id = trim($_POST['counselor_id']);
    $password     = trim($_POST['password']);

    // Fetch counselor data
    $stmt = $conn->prepare("SELECT counselor_id, first_name, last_name, grade_level, password 
                            FROM counselor 
                            WHERE counselor_id=?");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['counselor_id'] = $row['counselor_id'];
            $_SESSION['first_name']   = $row['first_name'];
            $_SESSION['last_name']    = $row['last_name'];
            $_SESSION['grade_level']  = $row['grade_level'];

            // If logging in with default password, force password change
            if (password_verify("123asd", $row['password'])) {
                header("Location: http://localhost/guidance_management/counselor/change_password.php");
                exit;
            }

            // Redirect to student table
            header("Location: counselor_dashboard.php");
            exit;
        } else {
            echo "❌ Invalid password!";
        }
    } else {
        echo "❌ Counselor not found!";
    }

    $stmt->close();
    $conn->close();
}
?>
