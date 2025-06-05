<?php
// University/includes/post_functions.php

require_once 'db_connect.php';
require_once 'functions.php';

function create_post($title, $category, $content, $file_path, $post_type) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO posts (title, category, content, file_path, post_type) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$title, $category, $content, $file_path, $post_type]);
}

function update_post($id, $title, $category, $content, $file_path, $post_type) {
    global $conn;
    $stmt = $conn->prepare("UPDATE posts SET title=?, category=?, content=?, file_path=?, post_type=? WHERE id=?");
    return $stmt->execute([$title, $category, $content, $file_path, $post_type, $id]);
}

function delete_post($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    return $stmt->execute([$id]);
}

function get_post($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_all_posts($category = null) {
    global $conn;
    
    if ($category) {
        $stmt = $conn->prepare("SELECT * FROM posts WHERE category=? ORDER BY created_at DESC");
        $stmt->execute([$category]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC");
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

function get_post_categories() {
    global $conn;
    $stmt = $conn->prepare("SELECT DISTINCT category FROM posts ORDER BY category");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>