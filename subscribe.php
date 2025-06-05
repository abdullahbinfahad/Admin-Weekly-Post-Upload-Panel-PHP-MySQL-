<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO subscribers (email) VALUES (?) 
                                  ON DUPLICATE KEY UPDATE status = 'active'");
            $stmt->execute([$email]);
            
            $_SESSION['subscribe_success'] = "Thank you for subscribing!";
        } catch (PDOException $e) {
            $_SESSION['subscribe_error'] = "Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['subscribe_error'] = "Invalid email address";
    }
}

header("Location: index.php");
exit;