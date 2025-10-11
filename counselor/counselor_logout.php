<?php
include '../session_config.php';
include '../db/dbconn.php';
$_SESSION = array();
session_destroy();

if (ini_get(option: "session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        name: session_name(),
        value: '',
        expires_or_options: time() - 42000,
        path: $params["path"],
        domain: $params["domain"],
        secure: $params["secure"],
        httponly: $params["httponly"]
    );
}

header("Location:counselor_login.php");
exit();