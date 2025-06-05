<?php
// University/admin/add_post.php
session_start();

// Enhanced session check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: auth.php');
    exit;
}

require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/post_functions.php';

$error = '';
$success = '';
$current_week = date('W');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) {
    // CSRF token validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Security error. Please try again.";
    } else {
        $title = sanitize_input($_POST['title']);
        $category = sanitize_input($_POST['category']);
        $content = sanitize_input($_POST['content']);
        $post_type = $_POST['post_type'];
        $file_path = '';

        // Handle file upload
        if ($post_type !== 'text' && isset($_FILES['post_file']) && $_FILES['post_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_extensions = match($post_type) {
                'presentation' => ['ppt', 'pptx', 'pdf'],
                'excel' => ['xls', 'xlsx', 'csv'],
                'video' => ['mp4', 'mov', 'avi'],
                'homework' => ['pdf', 'doc', 'docx', 'txt'],
                default => []
            };

            $errors = validate_upload($_FILES['post_file'], $allowed_extensions);
            
            if (empty($errors)) {
                ensure_upload_dir();
                $filename = sanitize_filename($_FILES['post_file']['name']);
                $destination = UPLOAD_DIR . $filename;
                
                if (move_uploaded_file($_FILES['post_file']['tmp_name'], $destination)) {
                    $file_path = str_replace(__DIR__ . '/../', '', $destination);
                } else {
                    $error = "Failed to upload file";
                }
            } else {
                $error = implode("<br>", $errors);
            }
        }

        if (!$error) {
            // Create the post
            global $conn;
            $stmt = $conn->prepare("INSERT INTO posts (title, category, content, file_path, post_type, week) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $category, $content, $file_path, $post_type, $current_week])) {
                $success = "Post created successfully!";
                // Clear form
                $title = $category = $content = '';
            } else {
                $error = "Failed to create post";
            }
        }
    }
}

// Regenerate CSRF token for new forms
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Post</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .current-week {
            background-color: #e9f7ef;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 class="text-center mb-4">Add Weekly Post</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="current-week">
            <strong>Current Week:</strong> Week <?= $current_week ?> of <?= date('Y') ?>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-section">
                <h3>Post Information</h3>
                <div class="mb-3">
                    <label class="form-label">Title*</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($title ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category*</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <option value="Announcements">Announcements</option>
                        <option value="Lectures">Lectures</option>
                        <option value="Assignments">Assignments</option>
                        <option value="Resources">Resources</option>
                        <option value="Exams">Exams</option>
                        <option value="Homework">Homework</option>
                    </select>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Content Type</h3>
                <div class="mb-3">
                    <label class="form-label">Post Type*</label>
                    <select name="post_type" class="form-select" id="postTypeSelector" onchange="toggleFields()" required>
                        <option value="text">Text Post</option>
                        <option value="presentation">Presentation</option>
                        <option value="excel">Excel/Spreadsheet</option>
                        <option value="video">Video</option>
                        <option value="homework">Homework</option>
                    </select>
                </div>
                
                <div class="mb-3" id="textContentField">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="6"><?= htmlspecialchars($content ?? '') ?></textarea>
                </div>
                
                <div class="mb-3 d-none" id="fileUploadField">
                    <label class="form-label">Upload File*</label>
                    <input type="file" name="post_file" class="form-control">
                    <div class="form-text">
                        Allowed formats: 
                        <span id="fileTypesHint">PDF, DOCX, PPTX, XLSX, CSV, TXT</span>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Create Post</button>
                <a href="manage_posts.php" class="btn btn-secondary">Manage Posts</a>
            </div>
        </form>
    </div>
    
    <script>
    function toggleFields() {
        const type = document.getElementById('postTypeSelector').value;
        const textField = document.getElementById('textContentField');
        const fileField = document.getElementById('fileUploadField');
        const fileHint = document.getElementById('fileTypesHint');
        
        if (type === 'text') {
            textField.classList.remove('d-none');
            fileField.classList.add('d-none');
        } else {
            textField.classList.add('d-none');
            fileField.classList.remove('d-none');
            
            // Update file type hints
            const hints = {
                'presentation': 'PPT, PPTX, PDF',
                'excel': 'XLS, XLSX, CSV',
                'video': 'MP4, MOV, AVI',
                'homework': 'PDF, DOC, DOCX, TXT'
            };
            fileHint.textContent = hints[type] || 'PDF, DOCX, PPTX, XLSX, CSV, TXT';
        }
    }
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>