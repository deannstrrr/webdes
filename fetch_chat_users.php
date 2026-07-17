<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit();
}

$current_user = $_SESSION['username'];
$current_user_escaped = mysqli_real_escape_string($conn, trim($current_user));

$user_id_query = "SELECT id FROM users WHERE username = '$current_user_escaped'";
$user_id_result = mysqli_query($conn, $user_id_query);
$user_id_data = mysqli_fetch_assoc($user_id_result);
$current_user_id = isset($user_id_data['id']) ? intval($user_id_data['id']) : 0;

$history_list = [];

if ($current_user_id > 0) {
    $history_sql = "SELECT DISTINCT u.username, u.avatar_path 
                    FROM messages m
                    JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
                    WHERE (m.sender_id = '$current_user_id' OR m.receiver_id = '$current_user_id')
                      AND u.id != '$current_user_id'
                    LIMIT 10";
    $history_result = mysqli_query($conn, $history_sql);
    if ($history_result) {
        while ($row = mysqli_fetch_assoc($history_result)) {
            $history_list[] = $row;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($history_list);
exit();
?>