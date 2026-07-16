<?php
require_once 'db.php';

if (isset($_POST['login_submit'])) {
    $identity = $_POST['identity'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE (username = '$identity' OR email = '$identity') AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        session_start();
        $_SESSION['username'] = $user['username'];
        
        header("Location: home.php");
    } else {
        header("Location: index.php?error=Invalid username, email, or password");
    }
} else {
    header("Location: index.php");
}
?>