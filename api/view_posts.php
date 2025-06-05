<?php
// University/posts/view_post.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/post_functions.php';

if (!isset($_GET['id'])) {
    header('Location: view_all.php');
    exit;
}

$post = get_post($_GET['id']);
if (!$post) {
    header('Location: view_all.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <article class="mb-5">
        <header class="mb-4">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="text-muted mb-3">
                <span class="badge bg-secondary"><?= htmlspecialchars($post['category']) ?></span> • 
                Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                <span class="badge bg-info ms-2"><?= ucfirst($post['post_type']) ?></span>
            </div>
        </header>
        
        <div class="post-content mb-4">
            <?php if ($post['post_type'] === 'text' || $post['post_type'] === 'homework'): ?>
                <div class="content-box p-4 bg-light rounded">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
            <?php elseif (!empty($post['file_path'])): ?>
                <div class="file-preview border rounded p-3">
                    <?php if (in_array($post['post_type'], ['presentation', 'excel', 'homework'])): ?>
                        <div class="alert alert-info">
                            <a href="<?= BASE_URL . $post['file_path'] ?>" download 
                               class="btn btn-primary">
                                Download File
                            </a>
                            <span class="ms-2"><?= basename($post['file_path']) ?></span>
                        </div>
                    <?php elseif ($post['post_type'] === 'video'): ?>
                        <div class="ratio ratio-16x9">
                            <video controls class="rounded">
                                <source src="<?= BASE_URL . $post['file_path'] ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($post['content'])): ?>
            <div class="additional-content mt-4 p-3 bg-light rounded">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        <?php endif; ?>
    </article>
    
    <a href="view_all.php" class="btn btn-outline-secondary">
        &larr; Back to All Posts
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>