<?php
// University/create_files.php

// Create db_connect.php
$db_content = <<<'EOD'
<?php
// University/includes/db_connect.php

\$host = 'localhost';
\$db   = 'university_db';
\$user = 'db_user';
\$pass = 'secure_password';
\$charset = 'utf8mb4';

\$dsn = "mysql:host=\$host;dbname=\$db;charset=\$charset";
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    \$conn = new PDO(\$dsn, \$user, \$pass, \$options);
} catch (PDOException \$e) {
    error_log("Database connection failed: " . \$e->getMessage());
    die("Database connection error. Please try again later.");
}
EOD;

// Create functions.php
$func_content = <<<'EOD'
<?php
// University/includes/functions.php

function sanitize_input(\$data) {
    \$data = trim(\$data);
    \$data = stripslashes(\$data);
    \$data = htmlspecialchars(\$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return \$data;
}

function sanitize_filename(\$name) {
    \$name = preg_replace("/[^a-zA-Z0-9_.-]/", '_', \$name);
    \$name = basename(\$name);
    return \$name;
}

function ensure_upload_dir(\$path) {
    if (!file_exists(\$path)) {
        mkdir(\$path, 0755, true);
    }
}

function validate_upload(\$file, \$allowed_extensions) {
    \$errors = [];
    \$max_size = 20 * 1024 * 1024; // 20MB
    
    if (\$file['error'] !== UPLOAD_ERR_OK) {
        \$errors[] = "Upload error: " . \$file['error'];
        return \$errors;
    }
    
    if (\$file['size'] > \$max_size) {
        \$errors[] = "File exceeds 20MB limit";
    }
    
    \$file_ext = strtolower(pathinfo(\$file['name'], PATHINFO_EXTENSION));
    if (!in_array(\$file_ext, \$allowed_extensions)) {
        \$errors[] = "Invalid file type. Allowed: " . implode(', ', \$allowed_extensions);
    }
    
    \$finfo = finfo_open(FILEINFO_MIME_TYPE);
    \$mime = finfo_file(\$finfo, \$file['tmp_name']);
    finfo_close(\$finfo);
    
    \$allowed_mimes = [
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pdf' => 'application/pdf',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain'
    ];
    
    if (!in_array(\$mime, \$allowed_mimes)) {
        \$errors[] = "Invalid file content detected";
    }
    
    return \$errors;
}
EOD;

// Create the includes directory if it doesn't exist
if (!is_dir('includes')) {
    mkdir('includes', 0755, true);
}

// Save the files
file_put_contents('includes/db_connect.php', $db_content);
file_put_contents('includes/functions.php', $func_content);

echo "Files created successfully! Please delete this script immediately.";