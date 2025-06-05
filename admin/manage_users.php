<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$message = '';

// Add new user
if (isset($_POST['add_user'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $password, $role])) {
        $message = "User added successfully!";
    }
}

// Fetch users
$users = $db->query("SELECT id, username, role, created_at FROM admin_users")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    
    <div class="admin-container">
        <h1>User Management</h1>
        
        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="user-form">
            <h2>Add New User</h2>
            <form method="post">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role">
                        <option value="admin">Administrator</option>
                        <option value="editor">Content Editor</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn">Add User</button>
            </form>
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>