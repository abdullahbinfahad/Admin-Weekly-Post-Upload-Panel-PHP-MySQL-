<?php
// University/admin/manage_posts.php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: auth.php');
    exit;
}

require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

// Handle post deletion
if (isset($_GET['delete']) && isset($_SESSION['csrf_token'])) {
    $id = (int)$_GET['delete'];
    
    // Get file path before deletion
    $stmt = $conn->prepare("SELECT file_path FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    
    // Delete post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if ($stmt->execute([$id])) {
        // Delete associated file
        if (!empty($post['file_path']) && file_exists(__DIR__ . '/../' . $post['file_path'])) {
            unlink(__DIR__ . '/../' . $post['file_path']);
        }
        $_SESSION['success'] = "Post deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete post";
    }
    header('Location: manage_posts.php');
    exit;
}

// Get all posts
$stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll();

// Generate CSRF token for delete links
$_SESSION['manage_csrf'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Posts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .post-type-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Posts</h1>
            <a href="add_post.php" class="btn btn-primary">
                + Add New Post
            </a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">No posts found</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Week</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?= $post['id'] ?></td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['category']) ?></td>
                                <td>
                                    <span class="badge bg-info post-type-badge">
                                        <?= ucfirst($post['post_type']) ?>
                                    </span>
                                </td>
                                <td>Week <?= $post['week'] ?></td>
                                <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" 
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <a href="manage_posts.php?delete=<?= $post['id'] ?>&csrf=<?= $_SESSION['manage_csrf'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>