<?php
// Automatically detect base URL (works on localhost and InfinityFree)
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
    $protocol = "https://";
} else {
    $protocol = "http://";
}

$host = $_SERVER['HTTP_HOST'];

// Automatically detect your main project folder (guidance_management)
$pathParts = explode('/', trim(dirname($_SERVER['SCRIPT_NAME']), '/'));
if (in_array('guidance_management', $pathParts)) {
    $basePath = '/guidance_management';
} else {
    $basePath = '';
}

// Construct the final base URL
$base_url = rtrim($protocol . $host . $basePath, '/');

// Make it globally accessible
define('BASE_URL', $base_url);
