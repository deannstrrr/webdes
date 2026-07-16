<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$logged_in_user = $_SESSION['username'];

$logged_in_query = "SELECT avatar_path FROM users WHERE username = '$logged_in_user'";
$logged_in_result = mysqli_query($conn, $logged_in_query);
$logged_in_data = mysqli_fetch_assoc($logged_in_result);
$logged_in_avatar = !empty($logged_in_data['avatar_path']) ? $logged_in_data['avatar_path'] : '';

$profile_username = isset($_GET['user']) ? mysqli_real_escape_string($conn, trim($_GET['user'])) : $logged_in_user;

$sql = "SELECT * FROM users WHERE username = '$profile_username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    $profile_username = $logged_in_user;
    $sql = "SELECT * FROM users WHERE username = '$profile_username'";
    $result = mysqli_query($conn, $sql);
}

$user_data = mysqli_fetch_assoc($result);

$username = $user_data['username'];
$bio = $user_data['bio'];
$avatar = !empty($user_data['avatar_path']) ? $user_data['avatar_path'] : '';
$cover = !empty($user_data['cover_path']) ? $user_data['cover_path'] : '';

$followers_query = "SELECT COUNT(*) as count FROM follows WHERE following_username = '$username'";
$followers_result = mysqli_query($conn, $followers_query);
$followers_data = mysqli_fetch_assoc($followers_result);
$followers = $followers_data['count'];

$following_query = "SELECT COUNT(*) as count FROM follows WHERE follower_username = '$username'";
$following_result = mysqli_query($conn, $following_query);
$following_data = mysqli_fetch_assoc($following_result);
$following_count = $following_data['count'];

$check_follow_sql = "SELECT id FROM follows WHERE follower_username = '$logged_in_user' AND following_username = '$username'";
$check_follow_res = mysqli_query($conn, $check_follow_sql);
$is_following = (mysqli_num_rows($check_follow_res) > 0);

$links_json = !empty($user_data['custom_links']) ? $user_data['custom_links'] : '[]';
$links_array = json_decode($links_json, true);
if (empty($links_array)) {
    $links_array = [
        ['url' => 'https://gearbox.com', 'title' => 'gearbox.com']
    ];
}

$is_own_profile = ($logged_in_user === $username);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX - <?php echo htmlspecialchars($username); ?>'s Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
</head>
<body>

    <header class="main-header" style="border-bottom: 1px solid #000000 !important;">
        <div class="header-logo-group" onclick="location.href='home.php'">
            <svg class="header-gear-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
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
            <div class="header-logo-text">GEARBOX</div>
        </div>

        <nav class="header-nav-tabs">
            <div class="nav-tab-item dropdown-wrapper">
                <span class="nav-tab-link" onclick="toggleHardwareDropdown(event)">Computer <i class="fa-solid fa-chevron-down"></i></span>
                <div class="header-dropdown-menu" id="hardware-dropdown">
                    <a href="hardware.php?type=cpu">CPUs</a>
                    <a href="hardware.php?type=gpu">GPUs</a>
                    <a href="hardware.php?type=motherboard">Motherboards</a>
                    <a href="hardware.php?type=ram">RAM</a>
                    <a href="hardware.php?type=fan">Fans</a>
                    <a href="hardware.php?type=psu">PSUs</a>
                    <a href="hardware.php?type=cooling">CPU Cooling</a>
                </div>
            </div>
            <div class="nav-tab-item dropdown-wrapper">
                <span class="nav-tab-link" onclick="togglePhoneDropdown(event)">Phone <i class="fa-solid fa-chevron-down"></i></span>
                <div class="header-dropdown-menu" id="phone-dropdown">
                    <a href="hardware.php?type=phone&brand=Apple">Apple</a>
                    <a href="hardware.php?type=phone&brand=Samsung">Samsung</a>
                    <a href="hardware.php?type=phone&brand=Xiaomi">Xiaomi</a>
                    <a href="hardware.php?type=phone&brand=OPPO">OPPO</a>
                    <a href="hardware.php?type=phone&brand=VIVO">VIVO</a>
                    <a href="hardware.php?type=phone&brand=Infinix">Infinix</a>
                    <a href="hardware.php?type=phone&brand=Huawei">Huawei</a>
                </div>
            </div>
        </nav>

        <div class="header-right-menu-group" style="margin-left: auto;">
            <div class="header-search-container">
                <div class="header-search-bar">
                    <i class="fa-solid fa-magnifying-glass search-submit-icon"></i>
                    <input type="text" id="global-user-search-input" placeholder="Search Gearbox..." oninput="handleLiveSearch()" autocomplete="off">
                </div>
                <div class="search-results-dropdown" id="search-results-box"></div>
            </div>

            <div class="header-user-profile-wrapper">
                <div class="header-user-profile" onclick="toggleProfileDropdown(event)">
                    <div class="avatar-placeholder <?php echo !empty($avatar) ? 'has-image' : ''; ?>" id="header-avatar-container">
                        <?php if(!empty($avatar)) { ?>
                            <img src="<?php echo $avatar; ?>" class="uploaded-avatar-img-header">
                        <?php } else { ?>
                            <svg id="header-avatar-svg" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="profile-dropdown-menu" id="profile-dropdown">
                    <a href="profile.php"><i class="fa-regular fa-user"></i> View Profile</a>
                    <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a>
                </div>
            </div>
        </div>
    </header>

    <div class="fb-wrapper">
        
        <div class="fb-header-section">
            
            <div class="fb-cover-photo-wrapper">
                <div class="fb-cover-photo" id="cover-display-area">
                    <?php if(!empty($cover)) { ?>
                        <div class="fb-cover-center-image" style="background-image: url('<?php echo $cover; ?>');"></div>
                    <?php } else { ?>
                        <div class="fb-cover-placeholder"></div>
                    <?php } ?>
                </div>
                <?php if ($is_own_profile) { ?>
                    <div class="cover-hover-overlay" onclick="triggerCoverUpload()">
                        <button class="fb-edit-cover-btn"><i class="fa-solid fa-camera"></i> Edit cover photo</button>
                    </div>
                    <input type="file" id="cover-file-input" accept="image/*" onchange="openCropModal(event, 'cover')" style="display: none;">
                <?php } ?>
            </div>
            
            <div class="fb-profile-info-bar">
                
                <div class="fb-avatar-overlap" <?php echo $is_own_profile ? 'onclick="triggerAvatarUpload()"' : ''; ?>>
                    <div id="avatar-display-area" class="avatar-inner-content <?php echo !empty($avatar) ? 'has-image' : ''; ?>">
                        <?php if(!empty($avatar)) { ?>
                            <img src="<?php echo $avatar; ?>" class="uploaded-avatar-img">
                        <?php } else { ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="#8a8a8a" stroke-width="1.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        <?php } ?>
                    </div>
                    <?php if ($is_own_profile) { ?>
                        <div class="avatar-hover-overlay">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <input type="file" id="avatar-file-input" accept="image/*" onchange="openCropModal(event, 'avatar')" style="display: none;">
                    <?php } ?>
                </div>
                
                <div class="fb-meta-details">
                    <div class="editable-row">
                        <h1 id="profile-name" onkeydown="checkEnterKey(event, this)" onblur="disableEditing(this, 'name')"><?php echo htmlspecialchars($username); ?></h1>
                        <?php if ($is_own_profile) { ?>
                            <span class="edit-icon-trigger" onclick="enableEditing('profile-name')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </span>
                        <?php } ?>
                    </div>
                    <p class="fb-follower-counts"><span id="follower-number"><?php echo $followers; ?></span> followers • <span id="following-number"><?php echo $following_count; ?></span> following</p>
                    <div class="editable-row">
                        <p id="profile-bio" class="fb-bio-text" onkeydown="checkEnterKey(event, this)" onblur="disableEditing(this, 'bio')"><?php echo htmlspecialchars($bio); ?></p>
                        <?php if ($is_own_profile) { ?>
                            <span class="edit-icon-trigger" onclick="enableEditing('profile-bio')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </span>
                        <?php } ?>
                    </div>
                </div>

                <div class="fb-action-buttons">
                    <?php if (!$is_own_profile) { ?>
                        <button class="fb-btn fb-btn-monochrome-dark" onclick="toggleChatBox()" title="Message">
                            <svg class="message-paper-plane" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L2 8.66l7.33 2.89L17 5l-6.55 7.67L13.33 22 22 2z"/>
                            </svg>
                        </button>
                        <button id="follow-button" class="fb-btn <?php echo $is_following ? 'fb-btn-monochrome-dark' : 'fb-btn-monochrome-light'; ?>" onclick="handleFollowToggle()" title="<?php echo $is_following ? 'Unfollow' : 'Follow'; ?>">
                            <i class="fa-solid <?php echo $is_following ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <div class="fb-nav-tabs">
                <div class="fb-tabs-left">
                    <a href="#" class="fb-tab active">All</a>
                </div>
            </div>
        </div>

        <div class="fb-body-grid">
            <div class="fb-left-column">
                <div class="fb-card">
                    <div class="editable-row" style="justify-content: space-between; margin-bottom: 16px;">
                        <h3 style="margin-bottom: 0;">Links</h3>
                        <?php if ($is_own_profile) { ?>
                            <span class="edit-icon-trigger" onclick="openLinkModal()" title="Edit Links">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </span>
                        <?php } ?>
                    </div>
                    
                    <div id="profile-links-list-container">
                        <?php foreach ($links_array as $link) { ?>
                            <div class="fb-info-row text-link-item">
                                <i class="fa-solid fa-link"></i> 
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['title']); ?></a>
                            </div>
                        <?php } ?>
                    </div>

                    <h3 style="margin-top: 20px;">Contact info</h3>
                    <div class="fb-info-row">
                        <i class="fa-solid fa-address-card"></i> Profile Info
                    </div>
                </div>
            </div>

            <div class="fb-right-column">
                <div class="fb-card">
                    <h3>Featured</h3>
                    <div class="fb-featured-placeholder">
                        <p>No featured posts to display yet.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if ($is_own_profile) { ?>
        <div class="crop-modal" id="link-edit-modal">
            <div class="crop-modal-content" style="max-width: 500px; max-height: 90vh; overflow-y: auto;">
                <h3>Update Website Links (Max 3)</h3>
                
                <?php for ($i = 0; $i < 3; $i++) { 
                    $curr_url = isset($links_array[$i]['url']) ? $links_array[$i]['url'] : '';
                    $curr_title = isset($links_array[$i]['title']) ? $links_array[$i]['title'] : '';
                ?>
                    <div class="modal-link-row-block" style="border-top: 1px solid #e4e6eb; margin-top: 15px; padding-top: 10px;">
                        <strong style="font-size: 13px; color: #050505;">Link #<?php echo ($i + 1); ?></strong>
                        <div class="link-input-group" style="margin-top: 8px;">
                            <input type="text" id="modal-link-url-<?php echo $i; ?>" class="modal-text-input" placeholder="URL (e.g., youtube.com)" value="<?php echo htmlspecialchars($curr_url); ?>">
                        </div>
                        <div class="link-input-group" style="margin-top: 8px;">
                            <input type="text" id="modal-link-title-<?php echo $i; ?>" class="modal-text-input" placeholder="Short Title (e.g., yt)" value="<?php echo htmlspecialchars($curr_title); ?>">
                        </div>
                    </div>
                <?php } ?>

                <div class="crop-modal-actions" style="margin-top: 24px;">
                    <button class="fb-btn fb-btn-grey" onclick="closeLinkModal()">Cancel</button>
                    <button class="fb-btn fb-btn-blue" onclick="saveCustomLinkData()">Save Links</button>
                </div>
            </div>
        </div>

        <div class="crop-modal" id="crop-modal-popup">
            <div class="crop-modal-content">
                <h3 id="crop-modal-title">Crop Your Photo</h3>
                <div class="crop-image-container">
                    <img id="image-to-crop" src="">
                </div>
                <div class="crop-modal-actions">
                    <button class="fb-btn fb-btn-grey" onclick="closeCropModal()">Cancel</button>
                    <button class="fb-btn fb-btn-blue" onclick="saveCroppedImage()">Apply Crop</button>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if (!$is_own_profile) { ?>
        <div class="gearbox-chat-popup" id="gearbox-chat-box">
            <div class="chat-header">
                <div class="chat-header-user">
                    <div class="chat-header-avatar">
                        <?php if(!empty($avatar)) { ?>
                            <img src="<?php echo $avatar; ?>" class="chat-avatar-img">
                        <?php } else { ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        <?php } ?>
                    </div>
                    <span class="chat-title-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <button class="chat-close-btn" onclick="toggleChatBox()">&times;</button>
            </div>
            
            <div class="chat-messages-area" id="chat-messages-container">
                <div class="chat-bubble received">Hello! Welcome to GEARBOX messaging.</div>
            </div>

            <div class="chat-emoji-picker-panel" id="chat-emoji-panel">
                <span onclick="appendEmojiToInput('😀')">😀</span>
                <span onclick="appendEmojiToInput('😁')">😁</span>
                <span onclick="appendEmojiToInput('😂')">😂</span>
                <span onclick="appendEmojiToInput('😃')">😃</span>
                <span onclick="appendEmojiToInput('😄')">😄</span>
                <span onclick="appendEmojiToInput('😅')">😅</span>
                <span onclick="appendEmojiToInput('😆')">😆</span>
                <span onclick="appendEmojiToInput('😉')">😉</span>
                <span onclick="appendEmojiToInput('😊')">😊</span>
                <span onclick="appendEmojiToInput('😋')">😋</span>
                <span onclick="appendEmojiToInput('😎')">😎</span>
                <span onclick="appendEmojiToInput('😍')">😍</span>
                <span onclick="appendEmojiToInput('😘')">😘</span>
                <span onclick="appendEmojiToInput('🥰')">🥰</span>
                <span onclick="appendEmojiToInput('😗')">😗</span>
                <span onclick="appendEmojiToInput('😙')">😙</span>
                <span onclick="appendEmojiToInput('😚')">😚</span>
                <span onclick="appendEmojiToInput('☺️')">☺️</span>
                <span onclick="appendEmojiToInput('🙂')">🙂</span>
                <span onclick="appendEmojiToInput('🤗')">🤗</span>
                <span onclick="appendEmojiToInput('🤩')">🤩</span>
                <span onclick="appendEmojiToInput('🤔')">🤔</span>
                <span onclick="appendEmojiToInput('🤨')">🤨</span>
                <span onclick="appendEmojiToInput('😐')">😐</span>
                <span onclick="appendEmojiToInput('😑')">😑</span>
                <span onclick="appendEmojiToInput('😶')">😶</span>
                <span onclick="appendEmojiToInput('🙄')">🙄</span>
                <span onclick="appendEmojiToInput('😏')">😏</span>
                <span onclick="appendEmojiToInput('😣')">😣</span>
                <span onclick="appendEmojiToInput('😥')">😥</span>
                <span onclick="appendEmojiToInput('😮')">😮</span>
                <span onclick="appendEmojiToInput('🤐')">🤐</span>
                <span onclick="appendEmojiToInput('😯')">😯</span>
                <span onclick="appendEmojiToInput('😪')">😪</span>
                <span onclick="appendEmojiToInput('😫')">😫</span>
                <span onclick="appendEmojiToInput('😴')">😴</span>
                <span onclick="appendEmojiToInput('😌')">😌</span>
                <span onclick="appendEmojiToInput('😛')">😛</span>
                <span onclick="appendEmojiToInput('😜')">😜</span>
                <span onclick="appendEmojiToInput('😝')">😝</span>
                <span onclick="appendEmojiToInput('🤤')">🤤</span>
                <span onclick="appendEmojiToInput('😒')">😒</span>
                <span onclick="appendEmojiToInput('😓')">😓</span>
                <span onclick="appendEmojiToInput('😔')">😔</span>
                <span onclick="appendEmojiToInput('😕')">😕</span>
                <span onclick="appendEmojiToInput('🙃')">🙃</span>
                <span onclick="appendEmojiToInput('🫠')">🫠</span>
                <span onclick="appendEmojiToInput('🤑')">🤑</span>
                <span onclick="appendEmojiToInput('😲')">😲</span>
                <span onclick="appendEmojiToInput('☹️')">☹️</span>
                <span onclick="appendEmojiToInput('🙁')">🙁</span>
                <span onclick="appendEmojiToInput('😖')">😖</span>
                <span onclick="appendEmojiToInput('😞')">😞</span>
                <span onclick="appendEmojiToInput('😟')">😟</span>
                <span onclick="appendEmojiToInput('😤')">😤</span>
                <span onclick="appendEmojiToInput('😢')">😢</span>
                <span onclick="appendEmojiToInput('😭')">😭</span>
                <span onclick="appendEmojiToInput('😦')">😦</span>
                <span onclick="appendEmojiToInput('😧')">😧</span>
                <span onclick="appendEmojiToInput('😨')">😨</span>
                <span onclick="appendEmojiToInput('😩')">😩</span>
                <span onclick="appendEmojiToInput('🤯')">🤯</span>
                <span onclick="appendEmojiToInput('😬')">😬</span>
                <span onclick="appendEmojiToInput('😮‍💨')">😮‍💨</span>
                <span onclick="appendEmojiToInput('😰')">😰</span>
                <span onclick="appendEmojiToInput('😱')">😱</span>
                <span onclick="appendEmojiToInput('🥵')">🥵</span>
                <span onclick="appendEmojiToInput('🥶')">🥶</span>
                <span onclick="appendEmojiToInput('😳')">😳</span>
                <span onclick="appendEmojiToInput('🤪')">🤪</span>
                <span onclick="appendEmojiToInput('😵')">😵</span>
                <span onclick="appendEmojiToInput('🥴')">🥴</span>
                <span onclick="appendEmojiToInput('😠')">😠</span>
                <span onclick="appendEmojiToInput('😡')">😡</span>
                <span onclick="appendEmojiToInput('🤬')">🤬</span>
                <span onclick="appendEmojiToInput('😷')">😷</span>
                <span onclick="appendEmojiToInput('🤒')">🤒</span>
                <span onclick="appendEmojiToInput('🤕')">🤕</span>
                <span onclick="appendEmojiToInput('🤢')">🤢</span>
                <span onclick="appendEmojiToInput('🤮')">🤮</span>
                <span onclick="appendEmojiToInput('🤧')">🤧</span>
                <span onclick="appendEmojiToInput('😇')">😇</span>
                <span onclick="appendEmojiToInput('🥳')">🥳</span>
                <span onclick="appendEmojiToInput('🥸')">🥸</span>
                <span onclick="appendEmojiToInput('🤫')">🤫</span>
                <span onclick="appendEmojiToInput('🤭')">🤭</span>
                <span onclick="appendEmojiToInput('🫣')">🫣</span>
                <span onclick="appendEmojiToInput('🤫')">🤫</span>
                <span onclick="appendEmojiToInput('🤥')">🤥</span>
                <span onclick="appendEmojiToInput('🫥')">🫥</span>
                <span onclick="appendEmojiToInput('😐')">😐</span>
                <span onclick="appendEmojiToInput('🙌')">🙌</span>
                <span onclick="appendEmojiToInput('👏')">👏</span>
                <span onclick="appendEmojiToInput('👋')">👋</span>
                <span onclick="appendEmojiToInput('👍')">👍</span>
                <span onclick="appendEmojiToInput('👎')">👎</span>
                <span onclick="appendEmojiToInput('👊')">👊</span>
                <span onclick="appendEmojiToInput('✊')">✊</span>
                <span onclick="appendEmojiToInput('✌️')">✌️</span>
                <span onclick="appendEmojiToInput('👌')">👌</span>
                <span onclick="appendEmojiToInput('✋')">✋</span>
                <span onclick="appendEmojiToInput('💪')">💪</span>
                <span onclick="appendEmojiToInput('🙏')">🙏</span>
                <span onclick="appendEmojiToInput('❤️')">❤️</span>
                <span onclick="appendEmojiToInput('🔥')">🔥</span>
                <span onclick="appendEmojiToInput('✨')">✨</span>
                <span onclick="appendEmojiToInput('⭐')">⭐</span>
            </div>

            <div class="chat-footer-input">
                <div class="chat-input-wrapper">
                    <input type="text" id="chat-message-input" placeholder="Type a message..." onkeydown="checkChatKey(event)">
                    <button class="chat-emoji-trigger-btn" onclick="toggleEmojiPanel()" title="Choose an emoji">
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
                </div>
                <button class="chat-send-btn" onclick="sendMessage()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L2 8.66l7.33 2.89L17 5l-6.55 7.67L13.33 22 22 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    <?php } ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        var cropper;
        var imageElement = document.getElementById('image-to-crop');
        var modalPopup = document.getElementById('crop-modal-popup');
        var currentCropTarget = ''; 
        var chatPollingInterval;

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('gearbox-chat-box')) {
                loadChatHistory();
            }
            blendCoverBackground();
        });

        function blendCoverBackground() {
            var coverImgElement = document.querySelector('.fb-cover-center-image');
            if (coverImgElement) {
                var bgStyle = window.getComputedStyle(coverImgElement).backgroundImage;
                var matches = bgStyle.match(/url\(['"]?(.*?)['"]?\)/);
                if (matches && matches[1]) {
                    var imgUrl = matches[1];
                    var img = new Image();
                    img.onload = function() {
                        var canvas = document.createElement('canvas');
                        canvas.width = this.width;
                        canvas.height = this.height;
                        var ctx = canvas.getContext('2d');
                        ctx.drawImage(this, 0, 0);
                        var x = Math.min(10, this.width - 1);
                        var y = Math.min(10, this.height - 1);
                        var pixelData = ctx.getImageData(x, y, 1, 1).data;
                        var detectedColor = 'rgb(' + pixelData[0] + ',' + pixelData[1] + ',' + pixelData[2] + ')';
                        document.querySelector('.fb-cover-photo-wrapper').style.backgroundColor = detectedColor;
                    };
                    img.src = imgUrl;
                }
            }
        }

        function toggleProfileDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        function toggleHardwareDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('hardware-dropdown');
            var phoneDropdown = document.getElementById('phone-dropdown');
            var profileDropdown = document.getElementById('profile-dropdown');
            
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (phoneDropdown) {
                phoneDropdown.classList.remove('show');
                setTimeout(function() { phoneDropdown.style.display = 'none'; }, 250);
            }
            
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                setTimeout(function() { if(!dropdown.classList.contains('show')) dropdown.style.display = 'none'; }, 250);
            } else {
                dropdown.style.display = 'block';
                setTimeout(function() { dropdown.classList.add('show'); }, 10);
            }
        }

        function togglePhoneDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('phone-dropdown');
            var hwDropdown = document.getElementById('hardware-dropdown');
            var profileDropdown = document.getElementById('profile-dropdown');
            
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (hwDropdown) {
                hwDropdown.classList.remove('show');
                setTimeout(function() { hwDropdown.style.display = 'none'; }, 250);
            }
            
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                setTimeout(function() { if(!dropdown.classList.contains('show')) dropdown.style.display = 'none'; }, 250);
            } else {
                dropdown.style.display = 'block';
                setTimeout(function() { dropdown.classList.add('show'); }, 10);
            }
        }

        document.addEventListener('click', function() {
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            var hwDropdown = document.getElementById('hardware-dropdown');
            if (hwDropdown && hwDropdown.classList.contains('show')) {
                hwDropdown.classList.remove('show');
                setTimeout(function() { if(!hwDropdown.classList.contains('show')) hwDropdown.style.display = 'none'; }, 250);
            }
            var pDropdown = document.getElementById('phone-dropdown');
            if (pDropdown && pDropdown.classList.contains('show')) {
                pDropdown.classList.remove('show');
                setTimeout(function() { if(!pDropdown.classList.contains('show')) pDropdown.style.display = 'none'; }, 250);
            }
        });

        <?php if ($is_own_profile) { ?>
        function openLinkModal() {
            document.getElementById('link-edit-modal').style.display = 'flex';
        }

        function closeLinkModal() {
            document.getElementById('link-edit-modal').style.display = 'none';
        }

        function saveCustomLinkData() {
            var formData = new FormData();
            formData.append('field', 'custom_links');

            for (var i = 0; i < 3; i++) {
                var urlVal = document.getElementById('modal-link-url-' + i).value.trim();
                var titleVal = document.getElementById('modal-link-title-' + i).value.trim();

                if (urlVal !== "" && titleVal !== "") {
                    if (!urlVal.startsWith('http://') && !urlVal.startsWith('https://')) {
                        urlVal = 'https://' + urlVal;
                    }
                    formData.append('urls[]', urlVal);
                    formData.append('titles[]', titleVal);
                }
            }

            fetch('save_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(result) {
                if(result === 'success') {
                    var linksContainer = document.getElementById('profile-links-list-container');
                    linksContainer.innerHTML = '';

                    for (var i = 0; i < 3; i++) {
                        var finalUrl = document.getElementById('modal-link-url-' + i).value.trim();
                        var finalTitle = document.getElementById('modal-link-title-' + i).value.trim();

                        if (finalUrl !== "" && finalTitle !== "") {
                            if (!finalUrl.startsWith('http://') && !finalUrl.startsWith('https://')) {
                                finalUrl = 'https://' + finalUrl;
                            }
                            linksContainer.innerHTML += `
                                <div class="fb-info-row text-link-item">
                                    <i class="fa-solid fa-link"></i> 
                                    <a href="${finalUrl}" target="_blank">${finalTitle}</a>
                                </div>
                            `;
                        }
                    }
                    closeLinkModal();
                } else {
                    alert("Error saving link details. Please try again.");
                }
            });
        }
        <?php } ?>

        <?php if (!$is_own_profile) { ?>
        function toggleChatBox() {
            var chatBox = document.getElementById('gearbox-chat-box');
            var emojiPanel = document.getElementById('chat-emoji-panel');
            if (chatBox.style.display === 'flex') {
                chatBox.style.display = 'none';
                if(emojiPanel) emojiPanel.style.display = 'none';
                clearInterval(chatPollingInterval);
            } else {
                chatBox.style.display = 'flex';
                loadChatHistory();
                chatPollingInterval = setInterval(loadChatHistory, 2000);
            }
        }

        function toggleEmojiPanel() {
            var emojiPanel = document.getElementById('chat-emoji-panel');
            if (emojiPanel.style.display === 'grid') {
                emojiPanel.style.display = 'none';
            } else {
                emojiPanel.style.display = 'grid';
            }
        }

        function appendEmojiToInput(emoji) {
            var inputElement = document.getElementById('chat-message-input');
            inputElement.value += emoji;
            inputElement.focus();
        }

        function sendMessage() {
            var inputElement = document.getElementById('chat-message-input');
            var emojiPanel = document.getElementById('chat-emoji-panel');
            var messageText = inputElement.value.trim();
            var receiverProfile = document.getElementById('profile-name').innerText.trim();
            
            if (messageText === '') return;

            var formData = new FormData();
            formData.append('receiver', receiverProfile);
            formData.append('message', messageText);

            fetch('chat_handler.php?action=send', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.status === 'success') {
                    inputElement.value = '';
                    if(emojiPanel) emojiPanel.style.display = 'none';
                    loadChatHistory();
                }
            });
        }

        function checkChatKey(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        }

        function loadChatHistory() {
            var receiverProfile = document.getElementById('profile-name').innerText.trim();
            var messagesArea = document.getElementById('chat-messages-container');

            fetch('chat_handler.php?action=fetch&other_user=' + encodeURIComponent(receiverProfile))
            .then(function(response) { return response.json(); })
            .then(function(chatHistory) {
                var previousScrollHeight = messagesArea.scrollHeight;
                var wasAtBottom = messagesArea.scrollTop + messagesArea.clientHeight >= messagesArea.scrollHeight - 20;

                messagesArea.innerHTML = '';

                if (chatHistory.length === 0) {
                    messagesArea.innerHTML = '<div class="chat-bubble received">Hello! Welcome to GEARBOX messaging.</div>';
                    return;
                }

                chatHistory.forEach(function(chat) {
                    var bubble = document.createElement('div');
                    bubble.className = 'chat-bubble ' + chat.type;
                    bubble.innerText = chat.message;
                    messagesArea.appendChild(bubble);
                });

                if (wasAtBottom || previousScrollHeight === 0) {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
            });
        }

        function scrollToBottom() {
            var messagesArea = document.getElementById('chat-messages-container');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function handleFollowToggle() {
            var profileName = document.getElementById('profile-name').innerText;
            var followButton = document.getElementById('follow-button');
            var followerNumber = document.getElementById('follower-number');
            var currentCount = parseInt(followerNumber.innerText);

            var formData = new FormData();
            formData.append('action', 'toggle_follow');
            formData.append('profile_user', profileName);

            fetch('save_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(result) {
                if (result === 'followed') {
                    followButton.className = 'fb-btn fb-btn-monochrome-dark';
                    followButton.innerHTML = '<i class="fa-solid fa-user-check"></i>';
                    followButton.setAttribute('title', 'Unfollow');
                    followerNumber.innerText = currentCount + 1;
                } else if (result === 'unfollowed') {
                    followButton.className = 'fb-btn fb-btn-monochrome-light';
                    followButton.innerHTML = '<i class="fa-solid fa-user-plus"></i>';
                    followButton.setAttribute('title', 'Follow');
                    followerNumber.innerText = currentCount - 1;
                }
            });
        }
        <?php } ?>

        function enableEditing(id) {
            var element = document.getElementById(id);
            element.setAttribute("contenteditable", "true");
            element.focus();
            
            var range = document.createRange();
            var selection = window.getSelection();
            range.selectNodeContents(element);
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        function disableEditing(element, fieldType) {
            element.removeAttribute("contenteditable");
            var textValue = element.innerText;

            var formData = new FormData();
            formData.append('field', fieldType);
            formData.append('value', textValue);

            fetch('save_profile.php', {
                method: 'POST',
                body: formData
            }).then(function() {
                if (fieldType === 'name') {
                    <?php if (!$is_own_profile) { ?>
                        loadChatHistory();
                    <?php } ?>
                }
            });
        }

        function checkEnterKey(event, element) {
            if (event.key === "Enter") {
                event.preventDefault();
                element.blur();
            }
        }

        function triggerAvatarUpload() {
            document.getElementById('avatar-file-input').click();
        }

        function triggerCoverUpload() {
            document.getElementById('cover-file-input').click();
        }

        function openCropModal(event, target) {
            var file = event.target.files[0];
            if (file) {
                currentCropTarget = target;
                var reader = new FileReader();
                reader.onload = function(e) {
                    imageElement.src = e.target.result;
                    modalPopup.style.display = 'flex';
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    var aspect = (target === 'cover') ? (16 / 9) : (1 / 1);
                    document.getElementById('crop-modal-title').innerText = (target === 'cover') ? 'Crop Your Cover Photo' : 'Crop Your Profile Picture';
                    
                    cropper = new Cropper(imageElement, {
                        aspectRatio: aspect,
                        viewMode: 1,
                        background: false,
                        autoCropArea: 1
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        function closeCropModal() {
            modalPopup.style.display = 'none';
            if(document.getElementById('cover-file-input')) document.getElementById('cover-file-input').value = '';
            if(document.getElementById('avatar-file-input')) document.getElementById('avatar-file-input').value = '';
        }

        function saveCroppedImage() {
            if (!cropper) return;
            
            var canvas;
            if (currentCropTarget === 'cover') {
                canvas = cropper.getCroppedCanvas({ width: 1250, height: 703 });
            } else {
                canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
            }
            
            var base64Image = canvas.toDataURL();

            var formData = new FormData();
            formData.append('upload_type', currentCropTarget);
            formData.append('image_data', base64Image);

            fetch('save_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(savedPath) {
                if(savedPath !== "unauthorized" && savedPath !== "error") {
                    if (currentCropTarget === 'cover') {
                        var tempCtx = canvas.getContext('2d');
                        var pixelData = tempCtx.getImageData(10, 10, 1, 1).data;
                        var detectedColor = 'rgb(' + pixelData[0] + ',' + pixelData[1] + ',' + pixelData[2] + ')';
                        
                        document.querySelector('.fb-cover-photo-wrapper').style.backgroundColor = detectedColor;
                        document.getElementById('cover-display-area').innerHTML = '<div class="fb-cover-center-image" style="background-image: url(\'' + savedPath + '\');"></div>';
                    } else if (currentCropTarget === 'avatar') {
                        var displayArea = document.getElementById('avatar-display-area');
                        displayArea.classList.add("has-image");
                        displayArea.innerHTML = '<img src="' + savedPath + '" class="uploaded-avatar-img">';
                        
                        var headerContainer = document.getElementById('header-avatar-container');
                        headerContainer.classList.add("has-image");
                        headerContainer.innerHTML = '<img src="' + savedPath + '" class="uploaded-avatar-img-header">';
                    }
                }
            });
            
            closeCropModal();
        }

        function handleLiveSearch() {
            var searchInput = document.getElementById('global-user-search-input');
            var resultsBox = document.getElementById('search-results-box');
            var queryValue = searchInput.value.trim();

            if (queryValue === '') {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
                return;
            }

            var formData = new FormData();
            formData.append('username', queryValue);

            fetch('search_user.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(users) {
                resultsBox.innerHTML = '';
                
                if (users.length === 0) {
                    resultsBox.innerHTML = '<div class="search-no-results">No results found</div>';
                    resultsBox.style.display = 'block';
                    return;
                }

                users.forEach(function(user) {
                    var row = document.createElement('div');
                    row.className = 'search-result-item';
                    row.onclick = function() {
                        window.location.href = 'profile.php?user=' + encodeURIComponent(user.username);
                    };

                    var avatarHtml = '';
                    if (user.avatar && user.avatar !== '') {
                        avatarHtml = `<img src="${user.avatar}" class="search-item-img">`;
                    } else {
                        avatarHtml = `
                            <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        `;
                    }

                    row.innerHTML = `
                        <div class="search-item-avatar">${avatarHtml}</div>
                        <div class="search-item-info">
                            <div class="search-item-name">${user.username}</div>
                            <div class="search-item-bio">${user.bio ? user.bio : 'No bio yet'}</div>
                        </div>
                    `;
                    resultsBox.appendChild(row);
                });

                resultsBox.style.display = 'block';
            });
        }

        document.addEventListener('click', function(event) {
            var searchContainer = document.querySelector('.header-search-container');
            var resultsBox = document.getElementById('search-results-box');
            if (resultsBox && !searchContainer.contains(event.target)) {
                resultsBox.style.display = 'none';
            }
        });
    </script>
</body>
</html>