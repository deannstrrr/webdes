<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

$current_user = $_SESSION['username'];
$current_user_escaped = mysqli_real_escape_string($conn, trim($current_user));

$user_id_query = "SELECT id FROM users WHERE username = '$current_user_escaped'";
$user_id_result = mysqli_query($conn, $user_id_query);
$user_id_data = mysqli_fetch_assoc($user_id_result);
$current_user_id = isset($user_id_data['id']) ? intval($user_id_data['id']) : 0;

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'send' && isset($_POST['receiver']) && isset($_POST['message'])) {
    $receiver_username = mysqli_real_escape_string($conn, trim($_POST['receiver']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    if ($message === '' || $receiver_username === '') {
        echo json_encode(['status' => 'empty']);
        exit();
    }
    
    $rec_query = "SELECT id FROM users WHERE username = '$receiver_username'";
    $rec_result = mysqli_query($conn, $rec_query);
    $rec_data = mysqli_fetch_assoc($rec_result);
    $receiver_id = isset($rec_data['id']) ? intval($rec_data['id']) : 0;
    
    if ($current_user_id === 0 || $receiver_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User identity lookup failed.']);
        exit();
    }
    
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$current_user_id', '$receiver_id', '$message')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

if ($action === 'fetch' && isset($_GET['other_user'])) {
    $other_username = mysqli_real_escape_string($conn, trim($_GET['other_user']));
    
    $other_query = "SELECT id FROM users WHERE username = '$other_username'";
    $other_result = mysqli_query($conn, $other_query);
    $other_data = mysqli_fetch_assoc($other_result);
    $other_user_id = isset($other_data['id']) ? intval($other_data['id']) : 0;
    
    $sql = "SELECT * FROM messages 
            WHERE (sender_id = '$current_user_id' AND receiver_id = '$other_user_id') 
               OR (sender_id = '$other_user_id' AND receiver_id = '$current_user_id') 
            ORDER BY created_at ASC";
            
    $result = mysqli_query($conn, $sql);
    $chat_history = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $chat_history[] = [
                'type' => ($row['sender_id'] == $current_user_id) ? 'sent' : 'received',
                'message' => $row['message']
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($chat_history);
    exit();
}
?>