<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['social_reg_email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['social_reg_email'];
$suggested_username = $_SESSION['social_reg_username'];
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $chosen_username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($chosen_username) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $check_sql = "SELECT id FROM users WHERE username = '$chosen_username'";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $error_message = "Username is already taken. Please choose another.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_sql = "INSERT INTO users (username, email, password, bio, custom_links) 
                           VALUES ('$chosen_username', '$email', '$hashed_password', 'Welcome to my Gearbox profile page.', '[]')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['username'] = $chosen_username;
                
                unset($_SESSION['social_reg_email']);
                unset($_SESSION['social_reg_username']);
                
                echo "<script>
                    if (window.opener) {
                        window.opener.location.href = 'home.php';
                        window.close();
                    } else {
                        window.location.href = 'home.php';
                    }
                </script>";
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX - Complete Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif;">

    <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <h2 style="margin-bottom: 10px; color: #050505; text-align: center;">One Last Step!</h2>
        <p style="font-size: 14px; color: #65676b; text-align: center; margin-bottom: 24px;">Complete your account creation by choosing a username and password linked to <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
        
        <?php if (!empty($error_message)) { ?>
            <div style="background-color: #ffebe9; color: #cc0000; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; border: 1px solid #ffccd0;">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <form action="social_register.php" method="POST">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #65676b; margin-bottom: 6px;">Username</label>
                <input type="text" name="username" class="modal-text-input" value="<?php echo htmlspecialchars($suggested_username); ?>" required style="background-color: #ffffff;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #65676b; margin-bottom: 6px;">Password</label>
                <input type="password" name="password" class="modal-text-input" placeholder="Min 6 characters" required style="background-color: #ffffff;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #65676b; margin-bottom: 6px;">Confirm Password</label>
                <input type="password" name="confirm_password" class="modal-text-input" placeholder="Repeat your password" required style="background-color: #ffffff;">
            </div>
            <button type="submit" class="fb-btn fb-btn-blue" style="width: 100%; padding: 12px; font-size: 14px;">Complete Registration</button>
        </form>
    </div>

</body>
</html>