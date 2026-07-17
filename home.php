<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$logged_in_user = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username = '$logged_in_user'";
$result = mysqli_query($conn, $sql);
$user_data = mysqli_fetch_assoc($result);

$avatar = !empty($user_data['avatar_path']) ? $user_data['avatar_path'] : '';

$spotlight = [
    'tag' => 'HARDWARE RELEASES',
    'title' => 'Next-Generation Architectures Released',
    'description' => 'Explore full architectural breakdowns, historical pricing transitions, and clock efficiency charts dating back to 2000 in our updated hardware catalogue panels.',
    'image_url' => '',
    'redirect_url' => '#'
];

$spotlight_file = "spotlight.json";
if (file_exists($spotlight_file)) {
    $spotlight_data = file_get_contents($spotlight_file);
    $decoded_spotlight = json_decode($spotlight_data, true);
    if (is_array($decoded_spotlight)) {
        $spotlight = array_merge($spotlight, $decoded_spotlight);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEARBOX - Home</title>
    <link rel="stylesheet" href="global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
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
                <path d="M 50 39 A 11 11 0 1 0 61 50 L 50 50" stroke="black" stroke-width="6" stroke-linecap="round" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
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

    <div class="dashboard-landing-container">
        <div class="landing-welcome-row">
            <h2>Welcome to Gearbox!</h2>
        </div>

        <div class="landing-content-layout">
            <a href="<?php echo htmlspecialchars($spotlight['redirect_url']); ?>" class="landing-hero-card" <?php echo ($spotlight['redirect_url'] !== '#') ? 'target="_blank"' : ''; ?> style="text-decoration: none;">
                <div class="hero-image-placeholder" style="<?php echo !empty($spotlight['image_url']) ? "background-image: url('" . htmlspecialchars($spotlight['image_url']) . "'); background-size: cover; background-position: center;" : ""; ?>">
                    <?php if (empty($spotlight['image_url'])) { ?>
                        <i class="fa-solid fa-microchip"></i>
                    <?php } ?>
                </div>
                <div class="hero-text-content">
                    <span class="content-tag"><i class="fa-solid fa-bolt"></i> <?php echo htmlspecialchars($spotlight['tag']); ?></span>
                    <h3><?php echo htmlspecialchars($spotlight['title']); ?></h3>
                    <p><?php echo htmlspecialchars($spotlight['description']); ?></p>
                </div>
            </a>

            <div class="landing-sidebar-list">
                <?php
                $sidebar_news = [
                    [
                        'title' => 'iPhone 17 Pro Max Design Architecture Unveiled',
                        'tag' => 'MOBILE RELEASE',
                        'image_url' => 'https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=300&auto=format&fit=crop',
                        'link' => 'hardware.php?type=phone&brand=Apple'
                    ],
                    [
                        'title' => 'Samsung Teases Galaxy Z Fold 8 Foldable Displays',
                        'tag' => 'MOBILE UPDATES',
                        'image_url' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?q=80&w=300&auto=format&fit=crop',
                        'link' => 'hardware.php?type=phone&brand=Samsung'
                    ],
                    [
                        'title' => 'AMD Launches Ryzen 7 7700X3D Processor Globally',
                        'tag' => 'COMPUTER CPU',
                        'image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?q=80&w=300&auto=format&fit=crop',
                        'link' => 'hardware.php?type=cpu'
                    ],
                    [
                        'title' => 'NVIDIA Prepares GeForce RTX 5060 Ti Transition',
                        'tag' => 'COMPUTER GPU',
                        'image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?q=80&w=300&auto=format&fit=crop',
                        'link' => 'hardware.php?type=gpu'
                    ]
                ];

                $sidebar_file = "sidebar_news.json";
                if (file_exists($sidebar_file)) {
                    $json_data = file_get_contents($sidebar_file);
                    $decoded_data = json_decode($json_data, true);
                    if (is_array($decoded_data)) {
                        $sidebar_news = $decoded_data;
                    }
                }

                foreach ($sidebar_news as $news) {
                ?>
                    <a href="<?php echo htmlspecialchars($news['link']); ?>" class="sidebar-news-item" style="text-decoration: none;">
                        <div class="sidebar-item-text">
                            <h4><?php echo htmlspecialchars($news['title']); ?></h4>
                            <span class="content-tag"><?php echo htmlspecialchars($news['tag']); ?></span>
                        </div>
                        <div class="sidebar-image-thumbnail">
                            <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="Hardware Preview">
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="brands-carousel-section">
        <div class="brands-section-header">
            <h2>Phones</h2>
        </div>
        
        <div class="carousel-viewport" id="brand-carousel-viewport">
            <div class="carousel-track" id="brand-carousel-track">
                <?php
                $brands = [
                    ['name' => 'Apple', 'img' => 'https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=Apple'],
                    ['name' => 'Samsung', 'img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=Samsung'],
                    ['name' => 'Xiaomi', 'img' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=Xiaomi'],
                    ['name' => 'OPPO', 'img' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=OPPO'],
                    ['name' => 'VIVO', 'img' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=VIVO'],
                    ['name' => 'Infinix', 'img' => 'https://images.unsplash.com/photo-1565849906186-07a9b2d75544?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=Infinix'],
                    ['name' => 'Huawei', 'img' => 'https://images.unsplash.com/photo-1546054454-aa26e2b734c7?q=80&w=400&auto=format&fit=crop', 'link' => 'hardware.php?type=phone&brand=Huawei']
                ];

                foreach ($brands as $brand) {
                ?>
                    <div class="carousel-card-wrapper" onclick="handleCardClick(event, '<?php echo $brand['link']; ?>')">
                        <div class="carousel-card-skew">
                            <div class="carousel-card-img" style="background-image: url('<?php echo $brand['img']; ?>');"></div>
                            <div class="carousel-card-overlay">
                                <h3><?php echo htmlspecialchars($brand['name']); ?></h3>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
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

        var viewport = document.getElementById('brand-carousel-viewport');
        var track = document.getElementById('brand-carousel-track');
        var isDown = false;
        var startX;
        var scrollLeft;
        var walkMultiplier = 1.35;
        var hasDragged = false;

        viewport.addEventListener('mousedown', function(e) {
            isDown = true;
            startX = e.pageX - viewport.offsetLeft;
            scrollLeft = viewport.scrollLeft;
            hasDragged = false;
        });
        viewport.addEventListener('mouseleave', function() { isDown = false; });
        viewport.addEventListener('mouseup', function() { isDown = false; });
        viewport.addEventListener('mousemove', function(e) {
            if(!isDown) return;
            e.preventDefault();
            var x = e.pageX - viewport.offsetLeft;
            var walk = (x - startX) * walkMultiplier;
            viewport.scrollLeft = scrollLeft - walk;
            if (Math.abs(walk) > 5) { hasDragged = true; }
        });
        viewport.addEventListener('touchstart', function(e) {
            isDown = true;
            startX = e.touches[0].pageX - viewport.offsetLeft;
            scrollLeft = viewport.scrollLeft;
            hasDragged = false;
        }, { passive: true });
        viewport.addEventListener('touchend', function() { isDown = false; });
        viewport.addEventListener('touchmove', function(e) {
            if(!isDown) return;
            var x = e.touches[0].pageX - viewport.offsetLeft;
            var walk = (x - startX) * walkMultiplier;
            viewport.scrollLeft = scrollLeft - walk;
            if (Math.abs(walk) > 5) { hasDragged = true; }
        }, { passive: true });

        function handleCardClick(event, redirectUrl) {
            if (hasDragged) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            window.location.href = redirectUrl;
        }
    </script>
</body>
</html>