<?php
// University/includes/config.php

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Application settings
define('APP_NAME', 'University Portal');
define('BASE_URL', 'http://localhost/university/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_FILE_TYPES', ['pdf', 'docx', 'pptx', 'xlsx', 'csv']);

// Session security
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => true,    // Enable in HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
?>