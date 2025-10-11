<?php
// FILE: session_config.php
// Place in: D:\xampp\htdocs\guidance_management\session_config.php
// Set session configuration BEFORE starting the session

// Set session lifetime BEFORE session_start()
ini_set('session.gc_maxlifetime', 365 * 24 * 60 * 60);
ini_set('session.cookie_lifetime', 365 * 24 * 60 * 60);

// Now start the session
session_start();

?>