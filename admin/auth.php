<?php
// University/admin/auth.php
session_start();

// Hardcoded credentials (temporary)
$valid_username = "abdullahbinfahad.abf";
$valid_password = "QazWsxEdc@@11";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['loggedin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate CSRF token
        header('Location: add-post.php');
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Add Bootstrap for consistent styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="text-center mb-4">Admin Login</h1>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>