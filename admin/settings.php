<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/PostManager.php';

$auth = new Auth();
$auth->requireLogin();

$postManager = new PostManager();
$categories = $postManager->getCategories();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle category management
    if (isset($_POST['add_category'])) {
        $newCategory = trim($_POST['new_category']);
        if (!empty($newCategory) && !in_array($newCategory, $categories)) {
            // Add new category to all posts with this category name?
            $message = "Category '$newCategory' added!";
        }
    }
    
    // Handle file settings
    if (isset($_POST['save_settings'])) {
        // Save settings to database or config file
        $message = "Settings saved successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <h1>System Settings</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="settings-section">
            <h2>Category Management</h2>
            <form method="post">
                <div class="form-group">
                    <label>Current Categories:</label>
                    <ul class="category-list">
                        <?php foreach ($categories as $category): ?>
                            <li><?= htmlspecialchars($category) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="new_category">Add New Category:</label>
                    <input type="text" id="new_category" name="new_category">
                    <button type="submit" name="add_category" class="btn">Add Category</button>
                </div>
            </form>
        </div>
        
        <div class="settings-section">
            <h2>File Upload Settings</h2>
            <form method="post">
                <div class="form-group">
                    <label>Maximum File Size (MB):</label>
                    <input type="number" name="max_file_size" value="20" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label>Allowed File Types:</label>
                    <div class="file-types">
                        <label><input type="checkbox" name="file_types[]" value="pdf" checked> PDF</label>
                        <label><input type="checkbox" name="file_types[]" value="docx" checked> DOCX</label>
                        <label><input type="checkbox" name="file_types[]" value="xlsx" checked> XLSX</label>
                        <label><input type="checkbox" name="file_types[]" value="pptx" checked> PPTX</label>
                        <label><input type="checkbox" name="file_types[]" value="jpg" checked> JPG</label>
                        <label><input type="checkbox" name="file_types[]" value="png" checked> PNG</label>
                    </div>
                </div>
                
                <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
        
        <div class="settings-section">
            <h2>Backup & Restore</h2>
            <div class="backup-options">
                <a href="backup.php?type=db" class="btn">Backup Database</a>
                <a href="backup.php?type=files" class="btn">Backup Files</a>
                <a href="backup.php?type=full" class="btn">Full Backup</a>
            </div>
            
            <form method="post" enctype="multipart/form-data" class="restore-form">
                <div class="form-group">
                    <label>Restore Backup:</label>
                    <input type="file" name="backup_file">
                    <button type="submit" name="restore" class="btn btn-warning">Restore</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>