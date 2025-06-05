<div class="admin-nav">
    <div class="admin-brand">Admin Panel</div>
    <ul>
        <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="add_post.php" class="<?= basename($_SERVER['PHP_SELF']) == 'add_post.php' ? 'active' : '' ?>">Add Post</a></li>
        <li><a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">Users</a></li>
        <li><a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">Settings</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</div>