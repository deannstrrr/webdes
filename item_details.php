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

$json_file = "cpu_list.json";
if ($type === 'gpu') $json_file = "gpu_list.json";
elseif ($type === 'motherboard') $json_file = "motherboard_list.json";
elseif ($type === 'ram') $json_file = "ram_list.json";
elseif ($type === 'fan') $json_file = "fan_list.json";
elseif ($type === 'psu') $json_file = "psu_list.json";
elseif ($type === 'cooling') $json_file = "cooling_list.json";
elseif ($type === 'phone') $json_file = "phone_list.json";

$specs = null;
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $raw_array = json_decode($json_data, true);
    if (is_array($raw_array) && isset($raw_array[$brand_name][$item_name])) {
        $specs = $raw_array[$brand_name][$item_name];
    }
}

if (!$specs) {
    echo "Item not found.";
    exit();
}

$item_id = md5($brand_name . '_' . $item_name);
$ratings_db_file = "ratings_data.json";
$ratings_log = [];

if (file_exists($ratings_db_file)) {
    $ratings_log = json_decode(file_get_contents($ratings_db_file), true);
    if (!is_array($ratings_log)) {
        $ratings_log = [];
    }
}

$comments_db_file = "comments_data.json";
$comments_log = [];

if (file_exists($comments_db_file)) {
    $comments_log = json_decode(file_get_contents($comments_db_file), true);
    if (!is_array($comments_log)) {
        $comments_log = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_rating') {
        $new_score = isset($_POST['score']) ? intval($_POST['score']) : 5;
        if ($new_score >= 1 && $new_score <= 5) {
            if (!isset($ratings_log[$item_id])) {
                $ratings_log[$item_id] = [];
            }
            $ratings_log[$item_id][$logged_in_user] = $new_score;
            file_put_contents($ratings_db_file, json_encode($ratings_log));
        }
    } elseif ($_POST['action'] === 'submit_comment') {
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

            $new_comment = [
                'username' => $logged_in_user,
                'avatar' => $logged_in_avatar,
                'text' => $text,
                'image' => $attached_image_path,
                'timestamp' => time()
            ];

            if (!isset($comments_log[$item_id])) {
                $comments_log[$item_id] = [];
            }
            array_unshift($comments_log[$item_id], $new_comment);
            file_put_contents($comments_db_file, json_encode($comments_log));
        }
    }
    header("Location: item_details.php?type=" . urlencode($type) . "&brand=" . urlencode($brand_name) . "&name=" . urlencode($item_name));
    exit();
}

$total_reviews = 0;
$average_score = 0.0;
$user_current_rating = 0;

if (isset($ratings_log[$item_id]) && is_array($ratings_log[$item_id])) {
    $item_scores = $ratings_log[$item_id];
    $total_reviews = count($item_scores);
    if ($total_reviews > 0) {
        $average_score = round(array_sum($item_scores) / $total_reviews, 1);
    }
    if (isset($item_scores[$logged_in_user])) {
        $user_current_rating = $item_scores[$logged_in_user];
    }
}

$active_comments = isset($comments_log[$item_id]) ? $comments_log[$item_id] : [];

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
    <link rel="stylesheet" href="style.css">
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

        function toggleProfileDropdown(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        document.addEventListener('click', function() {
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown) dropdown.style.display = 'none';
        });
    </script>
</body>
</html>