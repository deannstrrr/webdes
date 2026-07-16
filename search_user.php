<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit();
}

if (isset($_POST['username'])) {
    $search_name = mysqli_real_escape_string($conn, trim($_POST['username']));
    
    if ($search_name === '') {
        echo json_encode([]);
        exit();
    }

    $sql = "SELECT username, bio, avatar_path FROM users WHERE username LIKE '%$search_name%' LIMIT 5";
    $result = mysqli_query($conn, $sql);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = [
            'username' => $row['username'],
            'bio' => $row['bio'],
            'avatar' => $row['avatar_path']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($users);
    exit();
}
?>