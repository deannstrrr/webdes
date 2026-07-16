<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    echo "unauthorized";
    exit();
}

$current_user = $_SESSION['username'];

if (isset($_POST['action']) && $_POST['action'] == 'toggle_follow') {
    $profile_user = mysqli_real_escape_string($conn, $_POST['profile_user']);
    
    $check_sql = "SELECT id FROM follows WHERE follower_username = '$current_user' AND following_username = '$profile_user'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        $delete_sql = "DELETE FROM follows WHERE follower_username = '$current_user' AND following_username = '$profile_user'";
        if (mysqli_query($conn, $delete_sql)) {
            echo "unfollowed";
        }
    } else {
        $insert_sql = "INSERT INTO follows (follower_username, following_username) VALUES ('$current_user', '$profile_user')";
        if (mysqli_query($conn, $insert_sql)) {
            echo "followed";
        }
    }
    exit();
}

if (isset($_POST['field'])) {
    $field = $_POST['field'];
    
    if ($field == 'name') {
        $value = mysqli_real_escape_string($conn, trim($_POST['value']));
        $sql = "UPDATE users SET username = '$value' WHERE username = '$current_user'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['username'] = $value; 
            echo "success";
        }
    } elseif ($field == 'bio') {
        $value = mysqli_real_escape_string($conn, trim($_POST['value']));
        $sql = "UPDATE users SET bio = '$value' WHERE username = '$current_user'";
        if (mysqli_query($conn, $sql)) {
            echo "success";
        }
    } elseif ($field == 'custom_links') {
        $urls = isset($_POST['urls']) ? $_POST['urls'] : [];
        $titles = isset($_POST['titles']) ? $_POST['titles'] : [];
        
        $sanitized_links = [];
        
        for ($i = 0; $i < min(3, count($urls)); $i++) {
            $url = mysqli_real_escape_string($conn, trim($urls[$i]));
            $title = mysqli_real_escape_string($conn, trim($titles[$i]));
            
            if ($url !== "" && $title !== "") {
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
                $sanitized_links[] = [
                    'url' => $url,
                    'title' => $title
                ];
            }
        }
        $json_value = mysqli_real_escape_string($conn, json_encode($sanitized_links));
        
        $sql = "UPDATE users SET custom_links = '$json_value' WHERE username = '$current_user'";
        if (mysqli_query($conn, $sql)) {
            echo "success";
        }
    } elseif ($field == 'create_social_password') {
        $raw_password = trim($_POST['password']);
        if (strlen($raw_password) < 6) {
            echo "error";
            exit();
        }
        
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$current_user'";
        if (mysqli_query($conn, $sql)) {
            echo "success";
        }
    }
    exit();
}

if (isset($_POST['upload_type']) && isset($_POST['image_data'])) {
    $type = $_POST['upload_type'];
    $data = $_POST['image_data'];
    
    list($header, $data) = explode(';', $data);
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);
    
    $folder = "uploads/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $filename = $folder . $type . "_" . time() . "_" . uniqid() . ".png";
    
    if (file_put_contents($filename, $data)) {
        if ($type == 'avatar') {
            $sql = "UPDATE users SET avatar_path = '$filename' WHERE username = '$current_user'";
        } else {
            $sql = "UPDATE users SET cover_path = '$filename' WHERE username = '$current_user'";
        }
        
        if (mysqli_query($conn, $sql)) {
            echo $filename; 
        }
    }
    exit();
}
?>