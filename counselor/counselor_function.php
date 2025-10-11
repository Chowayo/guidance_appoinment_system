<?php
include '../session_config.php';
include '../db/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counselor_id = trim($_POST['counselor_id']);
    $password     = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT counselor_id, first_name, last_name, grade_level, password 
                            FROM counselor 
                            WHERE counselor_id=?");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['counselor_id'] = $row['counselor_id'];
            $_SESSION['first_name']   = $row['first_name'];
            $_SESSION['last_name']    = $row['last_name'];
            $_SESSION['grade_level']  = $row['grade_level'];

            // default password and required to change pass on first login
            if (password_verify("123asd", $row['password'])) {
                header("Location: ../counselor/change_password.php");
                exit;
            }

            $_SESSION['success'] = "";
            header("Location: counselor_login.php?redirect=dashboard");
            exit;

        } else {
            $_SESSION['error'] = "❌ Counselor ID or Password is Invalid!";
            header("Location: counselor_login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "❌ Counselor ID or Password is Invalid!";
        header("Location: counselor_login.php");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
