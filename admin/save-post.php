<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Please login first!");
}

// =====================
// DATABASE CONFIGURATION
// =====================
$db_host = 'localhost';       // Change to your database host
$db_user = 'your_db_user';    // Change to your database username
$db_pass = 'your_db_password';// Change to your database password
$db_name = 'university_db';   // Database name

// =====================
// CREATE DATABASE AND TABLE
// =====================
try {
    // Connect without specifying database
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $conn->exec("USE `$db_name`");
    
    // Create posts table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `posts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            `content_type` VARCHAR(20) NOT NULL,
            `content` TEXT,
            `file_path` VARCHAR(255),
            `week_number` TINYINT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

// =====================
// HELPER FUNCTIONS
// =====================
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_filename($name) {
    return preg_replace("/[^a-zA-Z0-9_.-]/", '_', basename($name));
}

function validate_upload($file, $allowed_extensions) {
    $max_size = 20 * 1024 * 1024; // 20MB
    $errors = [];
    
    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ["Upload error code: " . $file['error']];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = "File exceeds 20MB limit";
    }
    
    // Check file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        $errors[] = "Invalid file type. Allowed: " . implode(', ', $allowed_extensions);
    }
    
    return $errors;
}

// =====================
// FORM PROCESSING
// =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Process form data
$title = sanitize_input($_POST['title'] ?? '');
$category = sanitize_input($_POST['category'] ?? '');
$week = filter_input(INPUT_POST, 'week', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 52, 'default' => date('W')]
]);
$content_type = $_POST['content_type'] ?? '';

// Validate required fields
if (empty($title) || empty($category) || empty($content_type)) {
    die("All required fields must be filled!");
}

// Content processing
$content = '';
$file_path = '';

if ($content_type === 'text') {
    $content = sanitize_input($_POST['content'] ?? '');
} elseif ($content_type === 'video') {
    $content = filter_var($_POST['video_url'] ?? '', FILTER_VALIDATE_URL);
    if (!$content) die("Invalid video URL format");
} else {
    // File upload handling
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $allowed_types = [
            'presentation' => ['ppt', 'pptx', 'pdf'],
            'excel' => ['xls', 'xlsx', 'csv'],
            'homework' => ['doc', 'docx', 'pdf', 'txt']
        ];
        
        // Validate upload
        $errors = validate_upload($file, $allowed_types[$content_type]);
        if (!empty($errors)) {
            die(implode("<br>", $errors));
        }
        
        // Prepare upload directory
        $upload_dir = __DIR__ . '/../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate safe filename
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '_' . sanitize_filename($file['name']);
        $target_path = $upload_dir . $file_name;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $file_path = '/University/uploads/' . $file_name; // Web-accessible path
        } else {
            die("File upload failed. Check directory permissions.");
        }
    } else {
        die("File upload error: " . $_FILES['file']['error']);
    }
}

// Save to database
try {
    $stmt = $conn->prepare("INSERT INTO posts (title, category, content_type, content, file_path, week_number) 
                            VALUES (:title, :category, :ctype, :content, :fpath, :week)");
    
    $stmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':ctype' => $content_type,
        ':content' => $content,
        ':fpath' => $file_path,
        ':week' => $week
    ]);
    
    header("Location: add-post.php?success=1");
    exit;

} catch(PDOException $e) {
    die("Error saving post: " . $e->getMessage());
}
?>