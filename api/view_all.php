<?php
// University/posts/view_all.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/post_functions.php';

$categories = get_post_categories();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">All Posts</h1>
    
    <div class="row">
        <!-- Category Navigation -->
        <div class="col-md-3">
            <div class="list-group">
                <a href="?category=all" class="list-group-item list-group-item-action <?= !isset($_GET['category']) || $_GET['category'] === 'all' ? 'active' : '' ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?= urlencode($cat) ?>" class="list-group-item list-group-item-action <?= isset($_GET['category']) && $_GET['category'] === $cat ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Posts List -->
        <div class="col-md-9">
            <?php
            $current_category = $_GET['category'] ?? 'all';
            $posts = ($current_category === 'all') ? 
                get_all_posts() : 
                get_all_posts($current_category);
            ?>
            
            <?php if (empty($posts)): ?>
                <div class="alert alert-info">No posts found in this category</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($posts as $post): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?= htmlspecialchars($post['category']) ?> • 
                                        <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                    </h6>
                                    
                                    <p class="card-text">
                                        <?= substr(strip_tags($post['content']), 0, 150) ?>...
                                    </p>
                                    
                                    <div class="mt-2">
                                        <span class="badge bg-info">
                                            <?= ucfirst($post['post_type']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">
                                        View Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>