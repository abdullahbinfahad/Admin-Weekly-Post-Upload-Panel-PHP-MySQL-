<?php
// University/includes/functions.php

require_once 'config.php';

function sanitize_input($data) {
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_filename($name) {
    $name = preg_replace("/[^a-zA-Z0-9_.-]/", '_', basename($name));
    return time() . '_' . $name; // Prepend timestamp for uniqueness
}

function ensure_upload_dir() {
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

// ... (rest of your existing validate_upload function remains identical)
?>