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

$json_file = "cpu_list.json";
$title_text = "CPUs Database";

if ($type === 'gpu') {
    $json_file = "gpu_list.json";
    $title_text = "GPUs Database";
} elseif ($type === 'motherboard') {
    $json_file = "motherboard_list.json";
    $title_text = "Motherboards Database";
} elseif ($type === 'ram') {
    $json_file = "ram_list.json";
    $title_text = "RAM Database";
} elseif ($type === 'fan') {
    $json_file = "fan_list.json";
    $title_text = "Fans Database";
} elseif ($type === 'psu') {
    $json_file = "psu_list.json";
    $title_text = "PSUs Database";
} elseif ($type === 'cooling') {
    $json_file = "cooling_list.json";
    $title_text = "CPU Coolings Database";
} elseif ($type === 'phone') {
    $json_file = "phone_list.json";
    $title_text = !empty($selected_brand) ? "$selected_brand Phones Database" : "Phones Database";
}

$components = [];

if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $raw_array = json_decode($json_data, true);
    if (is_array($raw_array)) {
        foreach ($raw_array as $brand => $models) {
            if (!empty($selected_brand) && strtolower($brand) !== strtolower($selected_brand)) {
                continue;
            }
            if (is_array($models)) {
                foreach ($models as $name => $specs) {
                    if ($type === 'cpu') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Socket' => $specs['Socket'] ?? 'N/A',
                                'Cores / Threads' => ($specs['Cores'] ?? 'N/A') . ' / ' . ($specs['Threads'] ?? 'N/A'),
                                'Clock Speed' => $specs['Frequency'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'gpu') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Interface' => $specs['Interface'] ?? 'N/A',
                                'VRAM' => $specs['VRAM'] ?? 'N/A',
                                'TDP' => $specs['TDP'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'motherboard') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Socket' => $specs['Socket'] ?? 'N/A',
                                'Chipset' => $specs['Chipset'] ?? 'N/A',
                                'Form Factor' => $specs['Form_Factor'] ?? 'N/A',
                                'RAM Slots' => $specs['RAM_Slots'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'ram') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Type' => $specs['Type'] ?? 'N/A',
                                'Speed' => $specs['Speed'] ?? 'N/A',
                                'Voltage' => $specs['Voltage'] ?? 'N/A',
                                'Format' => $specs['Format'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'fan') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Size' => $specs['Size'] ?? 'N/A',
                                'Speed' => $specs['Speed'] ?? 'N/A',
                                'Airflow' => $specs['Airflow'] ?? 'N/A',
                                'Noise' => $specs['Noise'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'psu') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Wattage' => $specs['Wattage'] ?? 'N/A',
                                'Efficiency' => $specs['Efficiency'] ?? 'N/A',
                                'Modular' => $specs['Modular'] ?? 'N/A',
                                'Form Factor' => $specs['Form_Factor'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'cooling') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Type' => $specs['Type'] ?? 'N/A',
                                'Radiator Size' => $specs['Radiator_Size'] ?? 'N/A',
                                'Fans' => $specs['Fans'] ?? 'N/A',
                                'TDP Rating' => $specs['TDP_Rating'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    } elseif ($type === 'phone') {
                        $components[] = [
                            'name' => $name,
                            'brand' => $brand,
                            'details' => [
                                'Chipset' => $specs['Chipset'] ?? $specs['Chip'] ?? 'N/A',
                                'Screen' => $specs['Screen'] ?? 'N/A',
                                'Camera' => $specs['Camera'] ?? 'N/A',
                                'Battery' => $specs['Battery'] ?? 'N/A',
                                'Released' => $specs['Released'] ?? 'N/A'
                            ]
                        ];
                    }
                }
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
    <title>GEARBOX - Hardware Database</title>
    <link rel="stylesheet" href="style.css">
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

    <div class="fb-wrapper" style="margin-top: 20px; padding: 0 20px;">
        <div class="fb-card" style="margin-bottom: 20px;">
            <h2>Hardware Database - <?php echo $title_text; ?></h2>
            <p style="color: #65676b; margin-top: 5px;">Tracking historical specifications ranging from the year 2000 up until the present day.</p>
        </div>

        <div class="hardware-grid-layout">
            <?php 
            if (!empty($components)) {
                foreach ($components as $item) { 
            ?>
                <div class="fb-card">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div class="specs-wrapper">
                        <div class="specs-row"><strong>Brand:</strong> <?php echo htmlspecialchars($item['brand']); ?></div>
                        <?php foreach ($item['details'] as $key => $val) { ?>
                            <div class="specs-row"><strong><?php echo $key; ?>:</strong> <?php echo htmlspecialchars($val); ?></div>
                        <?php } ?>
                    </div>
                </div>
            <?php 
                }
            } else { 
            ?>
                <div class="fb-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: #65676b;">No results found. Make sure your local JSON database files are present.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>