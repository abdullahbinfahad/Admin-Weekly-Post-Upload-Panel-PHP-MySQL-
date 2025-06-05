<?php
// University/admin/edit_post.php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: auth.php');
    exit;
}

require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: manage_posts.php');
    exit;
}

$id = (int)$_GET['id'];
$error = '';
$success = '';

// Get current post data
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: manage_posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    // CSRF token validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Security error. Please try again.";
    } else {
        $title = sanitize_input($_POST['title']);
        $category = sanitize_input($_POST['category']);
        $content = sanitize_input($_POST['content']);
        $post_type = $_POST['post_type'];
        $file_path = $post['file_path'];

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
                    // Delete old file
                    if ($file_path && file_exists(__DIR__ . '/../' . $file_path)) {
                        unlink(__DIR__ . '/../' . $file_path);
                    }
                    $file_path = str_replace(__DIR__ . '/../', '', $destination);
                } else {
                    $error = "Failed to upload file";
                }
            } else {
                $error = implode("<br>", $errors);
            }
        }

        if (!$error) {
            // Update the post
            $stmt = $conn->prepare("UPDATE posts SET 
                                    title = ?, 
                                    category = ?, 
                                    content = ?, 
                                    file_path = ?, 
                                    post_type = ? 
                                    WHERE id = ?");
            if ($stmt->execute([$title, $category, $content, $file_path, $post_type, $id])) {
                $success = "Post updated successfully!";
                // Refresh post data
                $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$id]);
                $post = $stmt->fetch();
            } else {
                $error = "Failed to update post";
            }
        }
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 class="text-center mb-4">Edit Post</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="mb-3">
                <label class="form-label">Title*</label>
                <input type="text" name="title" class="form-control" 
                       value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Category*</label>
                <input type="text" name="category" class="form-control" 
                       value="<?= htmlspecialchars($post['category']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Post Type*</label>
                <select name="post_type" class="form-select" id="postTypeSelector" onchange="toggleFields()">
                    <option value="text" <?= $post['post_type'] === 'text' ? 'selected' : '' ?>>Text Post</option>
                    <option value="presentation" <?= $post['post_type'] === 'presentation' ? 'selected' : '' ?>>Presentation</option>
                    <option value="excel" <?= $post['post_type'] === 'excel' ? 'selected' : '' ?>>Excel/Spreadsheet</option>
                    <option value="video" <?= $post['post_type'] === 'video' ? 'selected' : '' ?>>Video</option>
                    <option value="homework" <?= $post['post_type'] === 'homework' ? 'selected' : '' ?>>Homework</option>
                </select>
            </div>
            
            <div class="mb-3" id="textContentField">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="6"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
            
            <div class="mb-3 <?= $post['post_type'] === 'text' ? 'd-none' : '' ?>" id="fileUploadField">
                <label class="form-label">Current File</label>
                <?php if ($post['file_path']): ?>
                    <div class="mb-2">
                        <a href="<?= BASE_URL . $post['file_path'] ?>" target="_blank">
                            <?= basename($post['file_path']) ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <label class="form-label">Upload New File (optional)</label>
                <input type="file" name="post_file" class="form-control">
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update Post</button>
                <a href="manage_posts.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
    function toggleFields() {
        const type = document.getElementById('postTypeSelector').value;
        const textField = document.getElementById('textContentField');
        const fileField = document.getElementById('fileUploadField');
        
        if (type === 'text') {
            textField.classList.remove('d-none');
            fileField.classList.add('d-none');
        } else {
            textField.classList.add('d-none');
            fileField.classList.remove('d-none');
        }
    }
    // Initialize on load
    document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>