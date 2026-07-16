<?php
session_start();
require_once 'db.php';

$provider = $_GET['provider'];
$error = '';
$success = false;

if (isset($_POST['submit_auth'])) {
    $email = $_POST['email'];
    
    $var_replace_at = strpos($email, '@');
    if ($var_replace_at !== false) {
        $username = substr($email, 0, $var_replace_at);
    } else {
        $username = $email;
    }
    
    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $user_data = mysqli_fetch_assoc($check_result);
        $_SESSION['username'] = $user_data['username'];
        
        echo "<script>
            if (window.opener) {
                window.opener.location.href = 'home.php';
                window.close();
            } else {
                window.location.href = 'home.php';
            }
        </script>";
        exit();
    } else {
        $_SESSION['social_reg_email'] = $email;
        $_SESSION['social_reg_username'] = $username;
        
        echo "<script>
            window.location.href = 'social_register.php';
        </script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in with <?php echo ucfirst($provider); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
        }
        body {
            background-color: #121212;
            color: #e3e3e3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .auth-container {
            background-color: #1e1e1e;
            width: 100%;
            max-width: 550px;
            border-radius: 12px;
            border: 1px solid #2d2d2d;
            overflow: hidden;
        }
        .header {
            padding: 16px 24px;
            border-bottom: 1px solid #2d2d2d;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #aaaaaa;
        }
        .main-content {
            display: flex;
            padding: 36px 24px;
            gap: 30px;
        }
        .left-col {
            flex: 1;
        }
        .right-col {
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .gear-svg {
            width: 48px;
            height: 48px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 8px;
            color: #ffffff;
        }
        .subtitle {
            font-size: 14px;
            color: #aaaaaa;
        }
        .subtitle span {
            color: #4fa8ff;
        }
        .input-wrapper {
            position: relative;
            margin-top: 10px;
        }
        .input-wrapper input {
            width: 100%;
            padding: 18px 12px 6px;
            background: transparent;
            border: 1px solid #8a8a8a;
            border-radius: 4px;
            color: #ffffff;
            font-size: 15px;
            outline: none;
        }
        .input-wrapper input:focus {
            border-color: #a8c7fa;
        }
        .input-wrapper label {
            position: absolute;
            left: 12px;
            top: 6px;
            font-size: 11px;
            color: #a8c7fa;
        }
        .forgot-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 13px;
            color: #a8c7fa;
            text-decoration: none;
            font-weight: 500;
        }
        .disclaimer {
            font-size: 12px;
            color: #aaaaaa;
            margin-top: 25px;
            line-height: 1.5;
        }
        .disclaimer a {
            color: #a8c7fa;
            text-decoration: none;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            margin-top: 30px;
        }
        .create-btn {
            background: none;
            border: none;
            color: #a8c7fa;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        .next-btn {
            background-color: #a8c7fa;
            color: #041e49;
            border: none;
            padding: 10px 24px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        .error-alert {
            color: #ff6b6b;
            font-size: 13px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="header">
            <?php if ($provider == 'google') { ?>
                <i class="fa-brands fa-google" style="color: #ffffff;"></i> Sign in with Google
            <?php } else { ?>
                <i class="fa-brands fa-facebook" style="color: #1877f2;"></i> Sign in with Facebook
            <?php } ?>
        </div>

        <div class="main-content">
            <div class="left-col">
                <svg class="gear-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <rect x="46" y="10" width="8" height="12" rx="2" fill="#4fa8ff"/>
                    <rect x="46" y="78" width="8" height="12" rx="2" fill="#4fa8ff"/>
                    <rect x="10" y="46" width="12" height="8" rx="2" fill="#4fa8ff"/>
                    <rect x="78" y="46" width="12" height="8" rx="2" fill="#4fa8ff"/>
                    <rect x="46" y="10" width="8" height="12" rx="2" transform="rotate(45 50 50)" fill="#4fa8ff"/>
                    <rect x="46" y="78" width="8" height="12" rx="2" transform="rotate(45 50 50)" fill="#4fa8ff"/>
                    <rect x="10" y="46" width="12" height="8" rx="2" transform="rotate(45 50 50)" fill="#4fa8ff"/>
                    <rect x="78" y="46" width="12" height="8" rx="2" transform="rotate(45 50 50)" fill="#4fa8ff"/>
                    <circle cx="50" cy="50" r="22" stroke="#4fa8ff" stroke-width="7" fill="none"/>
                    <path d="M 50 39 A 11 11 0 1 0 61 50 L 50 50" stroke="#4fa8ff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
                <h1>Sign in</h1>
                <div class="subtitle">to continue to <span>GEARBOX</span></div>
            </div>

            <div class="right-col">
                <form action="social_auth.php?provider=<?php echo $provider; ?>" method="POST">
                    <?php if (!empty($error)) { ?>
                        <div class="error-alert"><?php echo $error; ?></div>
                    <?php } ?>

                    <div class="input-wrapper">
                        <label for="email">Email or phone</label>
                        <input type="email" id="email" name="email" required autocomplete="off">
                    </div>

                    <a href="#" class="forgot-link">Forgot email?</a>

                    <div class="disclaimer">
                        Before using this app, you can review GEARBOX's <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a>.
                    </div>

                    <div class="actions">
                        <button type="button" class="create-btn">Create account</button>
                        <button type="submit" name="submit_auth" class="next-btn">Next</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        <?php if ($success) { ?>
            window.opener.location.href = "index.php?success=Social registration linkage successful!";
            window.close();
        <?php } ?>
    </script>
</body>
</html>