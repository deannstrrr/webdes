<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

$current_user = $_SESSION['username'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'send' && isset($_POST['receiver']) && isset($_POST['message'])) {
    $receiver = mysqli_real_escape_string($conn, trim($_POST['receiver']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    if ($message === '' || $receiver === '') {
        echo json_encode(['status' => 'empty']);
        exit();
    }
    
    $sql = "INSERT INTO messages (sender, receiver, message) VALUES ('$current_user', '$receiver', '$message')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

if ($action === 'fetch' && isset($_GET['other_user'])) {
    $other_user = mysqli_real_escape_string($conn, trim($_GET['other_user']));
    
    $sql = "SELECT * FROM messages 
            WHERE (sender = '$current_user' AND receiver = '$other_user') 
               OR (sender = '$other_user' AND receiver = '$current_user') 
            ORDER BY created_at ASC";
            
    $result = mysqli_query($conn, $sql);
    $chat_history = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $chat_history[] = [
            'type' => ($row['sender'] === $current_user) ? 'sent' : 'received',
            'message' => $row['message']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($chat_history);
    exit();
}
?>