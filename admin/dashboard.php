<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/PostManager.php';

$auth = new Auth();
$auth->requireLogin();

$postManager = new PostManager();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$filters = [
    'week' => $_GET['week'] ?? null,
    'category' => $_GET['category'] ?? null,
    'search' => $_GET['search'] ?? null
];

$data = $postManager->getPosts($page, $perPage, $filters);
$posts = $data['posts'];
$totalPages = $data['pages'];

// Get filter options
$categories = $postManager->getCategories();
$weeks = $postManager->getWeeks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <div class="dashboard-header">
            <h1>Post Management</h1>
            <a href="add_post.php" class="btn btn-primary">Add New Post</a>
        </div>
        
        <!-- Filter Form -->
        <form method="get" class="filter-form">
            <div class="form-group">
                <label>Week:</label>
                <select name="week">
                    <option value="">All Weeks</option>
                    <?php foreach ($weeks as $week): ?>
                        <option value="<?= $week ?>" <?= $filters['week'] == $week ? 'selected' : '' ?>>
                            Week <?= $week ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Category:</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category ?>" <?= $filters['category'] == $category ? 'selected' : '' ?>>
                            <?= $category ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Search:</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            
            <button type="submit" class="btn">Apply Filters</button>
        </form>
        
        <!-- Posts Table -->
        <table class="posts-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Week</th>
                    <th>Type</th>
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
                        <td><?= $post['week_number'] ?></td>
                        <td><?= $post['content_type'] ?></td>
                        <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                        <td class="actions">
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="delete_post.php?id=<?= $post['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn">Previous</a>
            <?php endif; ?>
            
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn">Next</a>
            <?php endif; ?>
        </div>
        
        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Posts</h3>
                <p><?= $data['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Categories</h3>
                <p><?= count($categories) ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Weeks</h3>
                <p><?= count($weeks) ?></p>
            </div>
        </div>
        
        <!-- Category Distribution Chart -->
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
        
        <script>
        // Category distribution chart
        const ctx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = <?= json_encode(array_count_values($postManager->getCategories())) ?>;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{
                    data: Object.values(categoryData),
                    backgroundColor: [
                        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', 
                        '#9b59b6', '#1abc9c', '#d35400', '#c0392b'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Posts by Category'
                    }
                }
            }
        });
        </script>
    </div>
</body>
</html>