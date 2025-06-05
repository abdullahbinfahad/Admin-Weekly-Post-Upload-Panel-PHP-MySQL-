<?php
// University/index.php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4">Welcome to <?= APP_NAME ?></h1>
        <p class="lead">University portal for courses, materials, and announcements</p>
        <hr class="my-4">
        <p>Access course materials, submit assignments, and stay updated with announcements</p>
        <a class="btn btn-primary btn-lg" href="posts/view_all.php" role="button">
            View All Posts
        </a>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <h2>Latest Announcements</h2>
            <?php
            require_once __DIR__ . '/includes/post_functions.php';
            $announcements = get_all_posts('Announcements');
            ?>
            <ul class="list-group">
                <?php foreach (array_slice($announcements, 0, 5) as $post): ?>
                    <li class="list-group-item">
                        <a href="posts/view_post.php?id=<?= $post['id'] ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                        <span class="text-muted small ms-2">
                            <?= date('M d', strtotime($post['created_at'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="col-md-6">
            <h2>Upcoming Deadlines</h2>
            <?php
            $homeworks = get_all_posts('Homework');
            ?>
            <ul class="list-group">
                <?php foreach (array_slice($homeworks, 0, 5) as $post): ?>
                    <li class="list-group-item">
                        <a href="posts/view_post.php?id=<?= $post['id'] ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                        <span class="text-muted small ms-2">
                            Due: <?= date('M d', strtotime($post['created_at'] . ' +1 week')) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>