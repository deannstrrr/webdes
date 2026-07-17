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

$type = isset($_GET['type']) ? $_GET['type'] : 'cpu';
$brand_name = isset($_GET['brand']) ? $_GET['brand'] : '';
$item_name = isset($_GET['name']) ? $_GET['name'] : '';

$table_map = [
    'cpu' => 'cpus',
    'gpu' => 'gpus',
    'motherboard' => 'motherboards',
    'ram' => 'ram',
    'fan' => 'fans',
    'psu' => 'psus',
    'cooling' => 'cooling',
    'phone' => 'phones'
];

$table = $table_map[$type] ?? 'cpus';
$b_esc = mysqli_real_escape_string($conn, $brand_name);
$n_esc = mysqli_real_escape_string($conn, $item_name);

$item_query = "SELECT * FROM `$table` WHERE LOWER(brand) = LOWER('$b_esc') AND LOWER(name) = LOWER('$n_esc') LIMIT 1";
$item_result = mysqli_query($conn, $item_query);
$row = mysqli_fetch_assoc($item_result);

$specs = null;
if ($row) {
    if ($type === 'cpu') {
        $specs = [
            'Socket' => $row['socket'],
            'Cores / Threads' => $row['cores_threads'],
            'Clock Speed' => $row['clock_speed']
        ];
    } elseif ($type === 'gpu') {
        $specs = [
            'Interface' => $row['interface'],
            'VRAM' => $row['vram'],
            'TDP' => $row['tdp'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'motherboard') {
        $specs = [
            'Socket' => $row['socket'],
            'Chipset' => $row['chipset'],
            'Form Factor' => $row['form_factor'],
            'RAM Slots' => $row['ram_slots'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'ram') {
        $specs = [
            'Type' => $row['type'],
            'Speed' => $row['speed'],
            'Voltage' => $row['voltage'],
            'Format' => $row['format'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'fan') {
        $specs = [
            'Size' => $row['size'],
            'Speed' => $row['speed'],
            'Airflow' => $row['airflow'],
            'Noise' => $row['noise'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'psu') {
        $specs = [
            'Wattage' => $row['wattage'],
            'Efficiency' => $row['efficiency'],
            'Modular' => $row['modular'],
            'Form Factor' => $row['form_factor'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'cooling') {
        $specs = [
            'Type' => $row['type'],
            'Radiator Size' => $row['radiator_size'],
            'Fans' => $row['fans'],
            'TDP Rating' => $row['tdp_rating'],
            'Released' => $row['released']
        ];
    } elseif ($type === 'phone') {
        $specs = [
            'Chipset' => $row['chipset'],
            'Screen' => $row['screen'],
            'Camera' => $row['camera'],
            'Battery' => $row['battery'],
            'Released' => $row['released']
        ];
    }
}

$item_id = md5($brand_name . '_' . $item_name);
$item_id_esc = mysqli_real_escape_string($conn, $item_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_rating') {
    $new_score = isset($_POST['score']) ? intval($_POST['score']) : 5;
    if ($new_score >= 1 && $new_score <= 5) {
        $item_id_esc = mysqli_real_escape_string($conn, $item_id);
        $user_esc = mysqli_real_escape_string($conn, $logged_in_user);
        
        $rating_save_query = "INSERT INTO ratings (item_id, username, score) 
                              VALUES ('$item_id_esc', '$user_esc', '$new_score') 
                              ON DUPLICATE KEY UPDATE score = '$new_score'";
        mysqli_query($conn, $rating_save_query);
    }
}  elseif ($_POST['action'] === 'submit_comment') {
        $text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
        if ($text !== '' || !empty($_FILES['comment_image']['name'])) {
            $attached_image_path = '';
            if (!empty($_FILES['comment_image']['name'])) {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $ext = pathinfo($_FILES['comment_image']['name'], PATHINFO_EXTENSION);
                $target_path = "uploads/" . time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES['comment_image']['tmp_name'], $target_path)) {
                    $attached_image_path = $target_path;
                }
            }

            $text_esc = mysqli_real_escape_string($conn, $text);
            $img_esc = mysqli_real_escape_string($conn, $attached_image_path);
            $avatar_esc = mysqli_real_escape_string($conn, $logged_in_avatar);
            $user_esc = mysqli_real_escape_string($conn, $logged_in_user);
            $current_time = time();

            $comment_save_query = "INSERT INTO comments (item_id, username, avatar, text, image, timestamp) 
                                  VALUES ('$item_id_esc', '$user_esc', '$avatar_esc', '$text_esc', '$img_esc', '$current_time')";
            mysqli_query($conn, $comment_save_query);
        }
    }
    header("Location: item_details.php?type=" . urlencode($type) . "&brand=" . urlencode($brand_name) . "&name=" . urlencode($item_name));
    exit();
}

$total_reviews = 0;
$average_score = 0.0;
$user_current_rating = 0;

$rating_query = "SELECT username, score FROM ratings WHERE item_id = '$item_id_esc'";
$rating_result = mysqli_query($conn, $rating_query);

if ($rating_result) {
    $total_reviews = mysqli_num_rows($rating_result);
    if ($total_reviews > 0) {
        $sum = 0;
        while ($r_row = mysqli_fetch_assoc($rating_result)) {
            $sum += intval($r_row['score']);
            if ($r_row['username'] === $logged_in_user) {
                $user_current_rating = intval($r_row['score']);
            }
        }
        $average_score = round($sum / $total_reviews, 1);
    }
}
$active_comments = [];
$comment_fetch_query = "SELECT username, avatar, text, image, timestamp FROM comments WHERE item_id = '$item_id_esc' ORDER BY timestamp DESC";
$comment_result = mysqli_query($conn, $comment_fetch_query);

if ($comment_result) {
    while ($c_row = mysqli_fetch_assoc($comment_result)) {
        $active_comments[] = $c_row;
    }
}

function formatCommentTime($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    $mins = round($diff / 60);
    if ($mins < 60) return $mins . ' minutes ago';
    $hours = round($mins / 60);
    if ($hours < 24) return $hours . ' hours ago';
    $days = round($hours / 24);
    if ($days < 7) return $days . ' days ago';
    return date('M d, Y', $timestamp);
}

$item_brand_lower = strtolower($brand_name);
$item_image = 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=600&auto=format&fit=crop';

if (strpos($item_brand_lower, 'nvidia') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1yklrt0pnQwBy1uoTESGu-LBVe0UMta49IpBIzgJC8Q&s=10';
} elseif (strpos($item_brand_lower, 'amd') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdfiGsu0n57aCfo_vtrt18sv3u0AXcYjNsvTRsjDzgoA&s=10';
} elseif (strpos($item_brand_lower, 'intel') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVolPd4C7eaay_wIfWXv5jMz7wLz-NapXrK9AI_TVS6A&s=10';
} elseif (strpos($item_brand_lower, 'asus') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRcpq825sKrHizNW2yJKWFzXP6-alz-giYZDt_fmKiQUg&s';
} elseif (strpos($item_brand_lower, 'gigabyte') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTovRkVmEtogRMblZ1aHX06TRCqsZY1eTuAVqe5LqaGk6S4zvmaunw1xE-8&s=10';
} elseif (strpos($item_brand_lower, 'corsair') !== false) {
    $item_image = 'https://cwsmgmt.corsair.com/press/CORSAIRLogo2020_stack_K.png';
} elseif (strpos($item_brand_lower, 'kingston') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKrA9Ik8OR5PfnXl4OjMj4fLd_yqnzkDhlHBR-JBXZk8Am4AmsAHByqVI&s=10';
} elseif (strpos($item_brand_lower, 'crucial') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSh7VbNDP5a0qv1CxBBUfYy1gTEj3OrInQh2pJhQL3GwA&s=10';
} elseif (strpos($item_brand_lower, 'msi') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTjNNAVQcIOjIWGDcnVJp6340ab-kAzQnR-4BY6m5R81Q&s=10';
} elseif (strpos($item_brand_lower, 'apple') !== false) {
    $item_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQdmKeCJ16mi6F3nhrPQeyXoZERh4r6zeNusXO5GbimgXHcDAwRsPD5TdPT&s=10';
} elseif (strpos($item_brand_lower, 'samsung') !== false) {
    $item_image = 'https://images.samsung.com/is/image/samsung/assets/global/about-us/brand/logo/256_144_2.png?$512_N_PNG$';
} elseif (strpos($item_brand_lower, 'xiaomi') !== false) {
    $item_image = 'https://upload.wikimedia.org/wikipedia/commons/a/ae/Xiaomi_logo_%282021-%29.svg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX - <?php echo htmlspecialchars($item_name); ?></title>
    <link rel="stylesheet" href="global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="item_details.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f0f2f5;">

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

            <?php
            $history_list = [];
            if (isset($_SESSION['username'])) {
                $current_user_escaped = mysqli_real_escape_string($conn, trim($_SESSION['username']));
                $user_id_query = "SELECT id FROM users WHERE username = '$current_user_escaped'";
                $user_id_result = mysqli_query($conn, $user_id_query);
                $user_id_data = mysqli_fetch_assoc($user_id_result);
                $current_user_id = isset($user_id_data['id']) ? intval($user_id_data['id']) : 0;

                if ($current_user_id > 0) {
                    $history_sql = "SELECT DISTINCT u.username, u.avatar_path 
                                    FROM messages m
                                    JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
                                    WHERE (m.sender_id = '$current_user_id' OR m.receiver_id = '$current_user_id')
                                      AND u.id != '$current_user_id'
                                    LIMIT 10";
                    $history_result = mysqli_query($conn, $history_sql);
                    if ($history_result) {
                        while ($history_row = mysqli_fetch_assoc($history_result)) {
                            $history_list[] = $history_row;
                        }
                    }
                }
            }
            ?>

            <div class="header-chat-history-wrapper">
                <div class="header-chat-bubble-trigger" onclick="toggleChatHistoryDropdown(event)">
                    <i class="fa-regular fa-paper-plane"></i>
                </div>
                <div class="chat-history-dropdown-menu" id="chat-history-dropdown">
                    <div class="chat-history-header">Chats</div>
                    <div class="chat-history-body">
                        <?php if (!empty($history_list)) { ?>
                            <?php foreach ($history_list as $chat_user) { ?>
                                <div class="chat-history-item" onclick="openFloatingChatFromDropdown('<?php echo htmlspecialchars($chat_user['username']); ?>', '<?php echo htmlspecialchars($chat_user['avatar_path']); ?>')">
                                    <div class="chat-item-avatar">
                                        <?php if (!empty($chat_user['avatar_path'])) { ?>
                                            <img src="<?php echo htmlspecialchars($chat_user['avatar_path']); ?>">
                                        <?php } else { ?>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <?php } ?>
                                    </div>
                                    <div class="chat-item-info">
                                        <div class="chat-item-name">@<?php echo htmlspecialchars($chat_user['username']); ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="chat-history-empty">No recent conversations</div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="header-user-profile-wrapper">
                <div class="header-user-profile" onclick="toggleProfileDropdown(event)">
                    <div class="avatar-placeholder <?php echo !empty($logged_in_avatar) ? 'has-image' : ''; ?>" id="header-avatar-container">
                        <?php if(!empty($logged_in_avatar)) { ?>
                            <img src="<?php echo $logged_in_avatar; ?>" class="uploaded-avatar-img-header">
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

        <div class="gearbox-chat-popup" id="gearbox-chat-box" style="display: none;">
            <div class="chat-header">
                <div class="chat-header-user">
                    <div class="chat-header-avatar"></div>
                    <span class="chat-title-name">Chat</span>
                </div>
                <button class="chat-close-btn" onclick="closeGlobalChatBox()">&times;</button>
            </div>
            <div class="chat-messages-area" id="chat-messages-container"></div>
            <div class="chat-emoji-picker-panel" id="chat-emoji-panel" style="display: none;">
                <span onclick="appendGlobalEmoji('😀')">😀</span>
                <span onclick="appendGlobalEmoji('😁')">😁</span>
                <span onclick="appendGlobalEmoji('😂')">😂</span>
                <span onclick="appendGlobalEmoji('😃')">😃</span>
                <span onclick="appendGlobalEmoji('😄')">😄</span>
                <span onclick="appendGlobalEmoji('😅')">😅</span>
                <span onclick="appendGlobalEmoji('😆')">😆</span>
                <span onclick="appendGlobalEmoji('😉')">😉</span>
                <span onclick="appendGlobalEmoji('😊')">😊</span>
                <span onclick="appendGlobalEmoji('😍')">😍</span>
                <span onclick="appendGlobalEmoji('👍')">👍</span>
                <span onclick="appendGlobalEmoji('❤️')">❤️</span>
            </div>
            <div class="chat-footer-input">
                <div class="chat-input-wrapper">
                    <input type="text" id="chat-message-input" placeholder="Type a message..." onkeydown="checkGlobalChatKey(event)">
                    <button class="chat-emoji-trigger-btn" onclick="toggleGlobalEmojiPanel()">
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
                </div>
                <button class="chat-send-btn" onclick="sendGlobalMessage()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L2 8.66l7.33 2.89L17 5l-6.55 7.67L13.33 22 22 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <div class="product-viewer-container">
        <div class="product-media-column">
            <div class="main-gallery-display">
                <img src="<?php echo htmlspecialchars($item_image); ?>" id="product-primary-img" alt="Hardware Image">
            </div>
            <div class="thumbnail-row">
                <div class="thumbnail-item active" onclick="updatePreviewImage('<?php echo htmlspecialchars($item_image); ?>', this)">
                    <img src="<?php echo htmlspecialchars($item_image); ?>" alt="Preview 1">
                </div>
                <div class="thumbnail-item" onclick="updatePreviewImage('https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=600&auto=format&fit=crop', this)">
                    <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=600&auto=format&fit=crop" alt="Preview 2">
                </div>
            </div>
        </div>

        <div class="product-info-column">
            <span class="product-brand-tag"><?php echo htmlspecialchars($brand_name); ?></span>
            <h1 class="product-title-heading"><?php echo htmlspecialchars($item_name); ?></h1>

            <div class="product-status-row">
                <span class="status-indicator in-stock"><i class="fa-solid fa-circle"></i> In stock</span>
                <span class="sku-details">DATABASE: GEARBOX-<?php echo strtoupper(uniqid()); ?></span>
            </div>

            <div class="display-aggregate-ratings-box">
                <div class="aggregate-stars-row">
                    <?php
                    $full_stars = floor($average_score);
                    $has_half = ($average_score - $full_stars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<i class="fa-solid fa-star active-display-star"></i>';
                        } elseif ($i == $full_stars + 1 && $has_half) {
                            echo '<i class="fa-solid fa-star-half-stroke active-display-star"></i>';
                        } else {
                            echo '<i class="fa-regular fa-star inactive-display-star"></i>';
                        }
                    }
                    ?>
                </div>
                <span class="reviews-metric-label">
                    <strong><?php echo $average_score > 0 ? $average_score : 'Unrated'; ?></strong> 
                    (<?php echo $total_reviews; ?> <?php echo $total_reviews === 1 ? 'Review' : 'Reviews'; ?>)
                </span>
            </div>

            <div class="details-divider"></div>

            <div class="specifications-sheet">
                <h3>Technical Specifications</h3>
                <table class="specs-data-table">
                    <tr>
                        <th>Brand</th>
                        <td><?php echo htmlspecialchars($brand_name); ?></td>
                    </tr>
                    <?php foreach ($specs as $key => $val) { ?>
                        <tr>
                            <th><?php echo htmlspecialchars($key); ?></th>
                            <td><?php echo htmlspecialchars($val); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="interactive-rating-form-block">
                <h4>Submit Your Product Score</h4>
                <form method="POST" id="gearbox-star-submission-form">
                    <input type="hidden" name="action" value="submit_rating">
                    <input type="hidden" name="score" id="selected-star-value" value="<?php echo $user_current_rating > 0 ? $user_current_rating : '5'; ?>">
                    
                    <div class="interactive-star-input-line">
                        <?php for($star = 1; $star <= 5; $star++) { 
                            $star_class = ($star <= $user_current_rating) ? 'fa-solid active-input-star' : 'fa-regular';
                            if ($user_current_rating == 0 && $star <= 5) $star_class = 'fa-solid active-input-star'; 
                        ?>
                            <i class="<?php echo $star_class; ?> fa-star interactive-click-star" data-value="<?php echo $star; ?>" onclick="setFormStarScore(<?php echo $star; ?>)"></i>
                        <?php } ?>
                    </div>
                    <button type="submit" class="submit-score-action-btn">Rate!</button>
                </form>
            </div>

            <a href="hardware.php?type=<?php echo urlencode($type); ?>&brand=<?php echo urlencode($brand_name); ?>" class="add-to-compare-btn" style="text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Return
            </a>
        </div>
    </div>

    <div class="gearbox-comments-container">
        <h3><?php echo count($active_comments); ?> Comments</h3>
        
        <div class="yt-comment-form-wrapper">
            <div class="yt-avatar-frame">
                <?php if (!empty($logged_in_avatar)) { ?>
                    <img src="<?php echo $logged_in_avatar; ?>">
                <?php } else { ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php } ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="yt-comment-input-area">
                <input type="hidden" name="action" value="submit_comment">
                <input type="text" name="comment_text" placeholder="Add a comment..." required autocomplete="off">
                <div class="yt-form-actions-row">
                    <label class="yt-file-upload-trigger">
                        <i class="fa-regular fa-image"></i> Attach Image
                        <input type="file" name="comment_image" accept="image/*" onchange="handleImageSelectionNotice(this)">
                    </label>
                    <span id="file-selected-alert-label"></span>
                    <button type="submit" class="yt-comment-submit-btn">Comment</button>
                </div>
            </form>
        </div>

        <div class="yt-rendered-comments-list">
            <?php foreach ($active_comments as $comment) { ?>
                <div class="yt-comment-row-item">
                    <div class="yt-avatar-frame">
                        <?php if (!empty($comment['avatar'])) { ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar']); ?>">
                        <?php } else { ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php } ?>
                    </div>
                    <div class="yt-comment-content-block">
                        <div class="yt-comment-metadata-line">
                            <span class="yt-commenter-username">@<?php echo htmlspecialchars($comment['username']); ?></span>
                            <span class="yt-comment-time-span"><?php echo formatCommentTime($comment['timestamp']); ?></span>
                        </div>
                        <p class="yt-comment-text-p"><?php echo htmlspecialchars($comment['text']); ?></p>
                        
                        <?php if (!empty($comment['image'])) { ?>
                            <div class="yt-comment-attached-media">
                                <img src="<?php echo htmlspecialchars($comment['image']); ?>" alt="Attached image upload">
                            </div>
                        <?php } ?>

                        <div class="yt-comment-feedback-actions">
                            <button class="yt-action-icon-btn"><i class="fa-regular fa-thumbs-up"></i></button>
                            <span class="yt-action-counter-text">0</span>
                            <button class="yt-action-icon-btn"><i class="fa-regular fa-thumbs-down"></i></button>
                            <button class="yt-action-reply-label-btn">Reply</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        var chatPollingInterval;
        var activeChatUser = '';

        function openFloatingChatFromDropdown(username, avatarUrl) {
            var chatBox = document.getElementById('gearbox-chat-box');
            if (!chatBox) return;
            activeChatUser = username;
            var nameLabel = chatBox.querySelector('.chat-title-name');
            if (nameLabel) nameLabel.innerText = username;
            var avatarImgContainer = chatBox.querySelector('.chat-header-avatar');
            if (avatarImgContainer) {
                if (avatarUrl && avatarUrl !== '') {
                    avatarImgContainer.innerHTML = `<img src="${avatarUrl}" class="chat-avatar-img">`;
                } else {
                    avatarImgContainer.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>`;
                }
            }
            chatBox.style.display = 'flex';
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (chatDropdown) chatDropdown.style.display = 'none';
            loadGlobalChatMessages();
            clearInterval(chatPollingInterval);
            chatPollingInterval = setInterval(loadGlobalChatMessages, 2000);
        }

        function loadGlobalChatMessages() {
            if (!activeChatUser) return;
            var messagesArea = document.getElementById('chat-messages-container');
            if (!messagesArea) return;
            fetch('chat_handler.php?action=fetch&other_user=' + encodeURIComponent(activeChatUser))
            .then(function(response) { return response.json(); })
            .then(function(chatHistory) {
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
                if (wasAtBottom) {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
            });
        }

        function sendGlobalMessage() {
            var inputElement = document.getElementById('chat-message-input');
            var emojiPanel = document.getElementById('chat-emoji-panel');
            var messageText = inputElement.value.trim();
            if (messageText === '' || !activeChatUser) return;
            var formData = new FormData();
            formData.append('receiver', activeChatUser);
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
                    loadGlobalChatMessages();
                }
            });
        }

        function checkGlobalChatKey(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendGlobalMessage();
            }
        }

        function toggleGlobalEmojiPanel() {
            var emojiPanel = document.getElementById('chat-emoji-panel');
            if (emojiPanel) {
                emojiPanel.style.display = (emojiPanel.style.display === 'grid') ? 'none' : 'grid';
            }
        }

        function appendGlobalEmoji(emoji) {
            var inputElement = document.getElementById('chat-message-input');
            if (inputElement) {
                inputElement.value += emoji;
                inputElement.focus();
            }
        }

        function closeGlobalChatBox() {
            var chatBox = document.getElementById('gearbox-chat-box');
            var emojiPanel = document.getElementById('chat-emoji-panel');
            if (chatBox) chatBox.style.display = 'none';
            if (emojiPanel) emojiPanel.style.display = 'none';
            clearInterval(chatPollingInterval);
        }

        function toggleChatHistoryDropdown(event) {
    event.stopPropagation();
    var dropdown = document.getElementById('chat-history-dropdown');
    var profileDropdown = document.getElementById('profile-dropdown');
    var hwDropdown = document.getElementById('hardware-dropdown');
    var phoneDropdown = document.getElementById('phone-dropdown');
    
    if (profileDropdown) profileDropdown.style.display = 'none';
    if (hwDropdown && hwDropdown.classList.contains('show')) hwDropdown.classList.remove('show');
    if (phoneDropdown && phoneDropdown.classList.contains('show')) phoneDropdown.classList.remove('show');

    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
        fetchLatestDropdownHistory();
    }
}

function fetchLatestDropdownHistory() {
    var container = document.getElementById('chat-dropdown-list-container');
    if (!container) return;

    fetch('fetch_chat_users.php')
    .then(function(response) { return response.json(); })
    .then(function(users) {
        container.innerHTML = '';
        
        if (users.length === 0) {
            container.innerHTML = '<div class="chat-history-empty">No recent conversations</div>';
            return;
        }

        users.forEach(function(user) {
            var row = document.createElement('div');
            row.className = 'chat-history-item';
            
            var avatarUrl = user.avatar_path ? user.avatar_path : '';
            var escapedUsername = user.username.replace(/'/g, "\\'");
            
            row.onclick = function() {
                openFloatingChatFromDropdown(user.username, avatarUrl);
            };

            var avatarHtml = '';
            if (avatarUrl !== '') {
                avatarHtml = `<img src="${avatarUrl}">`;
            } else {
                avatarHtml = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                `;
            }

            row.innerHTML = `
                <div class="chat-item-avatar">${avatarHtml}</div>
                <div class="chat-item-info">
                    <div class="chat-item-name">@${user.username}</div>
                </div>
            `;
            container.appendChild(row);
        });
    });
}

        function handleImageSelectionNotice(input) {
            var label = document.getElementById('file-selected-alert-label');
            if (input.files && input.files[0]) {
                label.textContent = "✓ " + input.files[0].name;
            } else {
                label.textContent = "";
            }
        }

        function setFormStarScore(score) {
            document.getElementById('selected-star-value').value = score;
            var stars = document.querySelectorAll('.interactive-click-star');
            stars.forEach(function(star, index) {
                if (index < score) {
                    star.className = 'fa-solid fa-star interactive-click-star active-input-star';
                } else {
                    star.className = 'fa-regular fa-star interactive-click-star';
                }
            });
        }

        function updatePreviewImage(imgUrl, el) {
            document.getElementById('product-primary-img').src = imgUrl;
            document.querySelectorAll('.thumbnail-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
        }

        function toggleHardwareDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('hardware-dropdown');
            var phoneDropdown = document.getElementById('phone-dropdown');
            var profileDropdown = document.getElementById('profile-dropdown');
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (chatDropdown) chatDropdown.style.display = 'none';
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
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (chatDropdown) chatDropdown.style.display = 'none';
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

        function toggleProfileDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('profile-dropdown');
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (chatDropdown) chatDropdown.style.display = 'none';
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        document.addEventListener('click', function() {
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown) dropdown.style.display = 'none';
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (chatDropdown) chatDropdown.style.display = 'none';
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
    </script>
</body>
</html>