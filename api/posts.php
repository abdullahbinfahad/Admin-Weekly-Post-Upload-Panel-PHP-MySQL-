<?php
require_once '../includes/db_connect.php';
require_once '../includes/PostManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$postManager = new PostManager();

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$week = isset($_GET['week']) ? (int)$_GET['week'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Get posts
$data = $postManager->getPosts($page, $perPage, [
    'week' => $week,
    'category' => $category,
    'search' => $search
]);

// Format response
$response = [
    'success' => true,
    'page' => $page,
    'per_page' => $perPage,
    'total_posts' => $data['total'],
    'total_pages' => $data['pages'],
    'posts' => []
];

foreach ($data['posts'] as $post) {
    $response['posts'][] = [
        'id' => $post['id'],
        'title' => $post['title'],
        'category' => $post['category'],
        'week' => $post['week_number'],
        'type' => $post['content_type'],
        'created_at' => $post['created_at'],
        'content' => $post['content'],
        'file_url' => $post['file_path'],
        'file_size' => $post['file_path'] ? filesize($_SERVER['DOCUMENT_ROOT'] . $post['file_path']) : 0
    ];
}

echo json_encode($response);