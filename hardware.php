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
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';

$title_text = "CPUs Database";
if ($type === 'gpu') {
    $title_text = "GPUs Database";
} elseif ($type === 'motherboard') {
    $title_text = "Motherboards Database";
} elseif ($type === 'ram') {
    $title_text = "RAM Database";
} elseif ($type === 'fan') {
    $title_text = "Fans Database";
} elseif ($type === 'psu') {
    $title_text = "PSUs Database";
} elseif ($type === 'cooling') {
    $title_text = "CPU Coolings Database";
} elseif ($type === 'phone') {
    $title_text = !empty($selected_brand) ? "$selected_brand Phones Database" : "Phones Database";
}

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
$query = "SELECT * FROM `$table`";
if (!empty($selected_brand)) {
    $brand_esc = mysqli_real_escape_string($conn, $selected_brand);
    $query .= " WHERE LOWER(brand) = LOWER('$brand_esc')";
}
$db_result = mysqli_query($conn, $query);

$components = [];
if ($db_result) {
    while ($row = mysqli_fetch_assoc($db_result)) {
        $details = [];
        if ($type === 'cpu') {
            $details = [
                'Socket' => $row['socket'] ?? 'N/A',
                'Cores / Threads' => $row['cores_threads'] ?? 'N/A',
                'Clock Speed' => $row['clock_speed'] ?? 'N/A'
            ];
        } elseif ($type === 'gpu') {
            $details = [
                'Interface' => $row['interface'] ?? 'N/A',
                'VRAM' => $row['vram'] ?? 'N/A',
                'TDP' => $row['tdp'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'motherboard') {
            $details = [
                'Socket' => $row['socket'] ?? 'N/A',
                'Chipset' => $row['chipset'] ?? 'N/A',
                'Form Factor' => $row['form_factor'] ?? 'N/A',
                'RAM Slots' => $row['ram_slots'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'ram') {
            $details = [
                'Type' => $row['type'] ?? 'N/A',
                'Speed' => $row['speed'] ?? 'N/A',
                'Voltage' => $row['voltage'] ?? 'N/A',
                'Format' => $row['format'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'fan') {
            $details = [
                'Size' => $row['size'] ?? 'N/A',
                'Speed' => $row['speed'] ?? 'N/A',
                'Airflow' => $row['airflow'] ?? 'N/A',
                'Noise' => $row['noise'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'psu') {
            $details = [
                'Wattage' => $row['wattage'] ?? 'N/A',
                'Efficiency' => $row['efficiency'] ?? 'N/A',
                'Modular' => $row['modular'] ?? 'N/A',
                'Form Factor' => $row['form_factor'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'cooling') {
            $details = [
                'Type' => $row['type'] ?? 'N/A',
                'Radiator Size' => $row['radiator_size'] ?? 'N/A',
                'Fans' => $row['fans'] ?? 'N/A',
                'TDP Rating' => $row['tdp_rating'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        } elseif ($type === 'phone') {
            $details = [
                'Chipset' => $row['chipset'] ?? 'N/A',
                'Screen' => $row['screen'] ?? 'N/A',
                'Camera' => $row['camera'] ?? 'N/A',
                'Battery' => $row['battery'] ?? 'N/A',
                'Released' => $row['released'] ?? 'N/A'
            ];
        }

        $components[] = [
            'name' => $row['name'],
            'brand' => $row['brand'],
            'details' => $details
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX - Hardware Database</title>
    <link rel="stylesheet" href="global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="hardware.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <div class="chat-history-body" id="chat-dropdown-list-container">
                        <div class="chat-history-empty">Loading conversations...</div>
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

    <div class="fb-wrapper" style="margin-top: 20px; padding: 0 20px;">

        <div class="hardware-grid-layout">
            <?php 
            if (!empty($components)) {
                foreach ($components as $item) { 
                    $item_name_lower = strtolower($item['name']);
                    $item_brand_lower = strtolower($item['brand']);
                    
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
                    } else {
                        $item_image = 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=300&auto=format&fit=crop';
                    }

                    $loop_item_id = md5($item['brand'] . '_' . $item['name']);
                    $card_total_reviews = 0;
                    $card_average_score = 0.0;

                    $rating_q = mysqli_query($conn, "SELECT score FROM ratings WHERE item_id = '$loop_item_id'");
                    if ($rating_q && mysqli_num_rows($rating_q) > 0) {
                        $card_total_reviews = mysqli_num_rows($rating_q);
                        $sum = 0;
                        while($r = mysqli_fetch_assoc($rating_q)) { $sum += $r['score']; }
                        $card_average_score = round($sum / $card_total_reviews, 1);
                    }

                    $detail_url = 'item_details.php?type=' . urlencode($type) . '&brand=' . urlencode($item['brand']) . '&name=' . urlencode($item['name']);
            ?>
                <a href="<?php echo $detail_url; ?>" class="fb-card catalog-card" style="text-decoration: none;">
                    <div class="catalog-card-image-container">
                        <img src="<?php echo htmlspecialchars($item_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="catalog-card-content" style="position: relative;">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="specs-wrapper">
                            <div class="specs-row"><strong>Brand:</strong> <?php echo htmlspecialchars($item['brand']); ?></div>
                            <?php foreach ($item['details'] as $key => $val) { ?>
                                <div class="specs-row"><strong><?php echo $key; ?>:</strong> <?php echo htmlspecialchars($val); ?></div>
                            <?php } ?>
                        </div>
                        
                        <?php if ($card_total_reviews > 0) { ?>
                            <div class="catalog-card-rating-badge">
                                <i class="fa-solid fa-star"></i>
                                <span><?php echo $card_average_score; ?> (<?php echo $card_total_reviews; ?>)</span>
                            </div>
                        <?php } ?>
                    </div>
                </a>
            <?php 
                }
            } else { 
            ?>
                <div class="fb-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: #65676b;">No results found inside the components database structure.</p>
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

        document.addEventListener('click', function() {
            var dropdown = document.getElementById('profile-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            var chatDropdown = document.getElementById('chat-history-dropdown');
            if (chatDropdown) {
                chatDropdown.style.display = 'none';
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