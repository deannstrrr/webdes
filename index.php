<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX Account Log In</title>
    <link rel="stylesheet" href="global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <body class="login-page-body">
    <div class="login-card">
        <button class="close-btn">&times;</button>
        
        <div class="logo-container">
            <svg class="gear-logo-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <rect x="46" y="10" width="8" height="12" rx="2" fill="black"/>
                <rect x="46" y="78" width="8" height="12" rx="2" fill="black"/>
                <rect x="10" y="46" width="12" height="8" rx="2" fill="black"/>
                <rect x="78" y="46" width="12" height="8" rx="2" fill="black"/>
                
                <rect x="46" y="10" width="8" height="12" rx="2" transform="rotate(45 50 50)" fill="black"/>
                <rect x="46" y="78" width="8" height="12" rx="2" transform="rotate(45 50 50)" fill="black"/>
                <rect x="10" y="46" width="12" height="8" rx="2" transform="rotate(45 50 50)" fill="black"/>
                <rect x="78" y="46" width="12" height="8" rx="2" transform="rotate(45 50 50)" fill="black"/>
                
                <circle cx="50" cy="50" r="22" stroke="black" stroke-width="7" fill="none"/>
                
                <path d="M 50 39 A 11 11 0 1 0 61 50 L 50 50" stroke="black" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
            <div class="logo-text">GEARBOX</div>
        </div>

        <h2>Account Log In</h2>

        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php } ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <input type="text" name="identity" placeholder="Username/Email" required>
            </div>
            
            <div class="input-group password-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePasswordVisibility()">
                    <i class="fa-regular fa-eye-slash" id="eye-icon"></i>
                </span>
            </div>

            <button type="submit" name="login_submit" class="login-btn">Log In</button>
        </form>

        <div class="links-container">
            <a href="register.php" class="link-right">Register Now</a>
        </div>

        <div class="divider">
            <span>More Login Methods</span>
        </div>

        <div class="social-login">
            <a href="social_auth.php?provider=google" onclick="openSocialPopup('google'); return false;" class="social-icon google"><i class="fa-brands fa-google"></i></a>
            <a href="social_auth.php?provider=facebook" onclick="openSocialPopup('facebook'); return false;" class="social-icon facebook"><i class="fa-brands fa-facebook-f"></i></a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var eyeIcon = document.getElementById("eye-icon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            }
        }

        function openSocialPopup(platform) {
            var width = 600;
            var height = 550;
            var left = (screen.width - width) / 2;
            var top = (screen.height - height) / 2;
            
            window.open(
                'social_auth.php?provider=' + platform,
                'SocialAuthPopup',
                'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left + ',resizable=yes,scrollbars=yes'
            );
        }
    </script>
</body>
</html>