<?php
session_start();
// Fetch menu items from database
require_once 'db_connection.php';

$adminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

// Get selected item if passed from index.php
$selectedItemId = isset($_GET['item']) ? intval($_GET['item']) : 0;
$selectedItem = null;

try {
    // Fetch all available menu items
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY id");
    $menuItems = $stmt->fetchAll();
    
    // If an item was selected, get its details
    if ($selectedItemId > 0) {
        foreach ($menuItems as $item) {
            if ($item['id'] == $selectedItemId) {
                $selectedItem = $item;
                break;
            }
        }
    }
} catch(PDOException $e) {
    $menuItems = [];
    $selectedItem = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut Ghana - Menu</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Palette */
            --red-50: #fef2f2;
            --red-200: #fecaca; /* Shadow color */
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            --white: #ffffff;
            
            /* Fonts */
            --font-syne: 'Syne', sans-serif;
            --font-roboto: 'Roboto', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-roboto);
            background-color: var(--gray-100);
            color: var(--gray-900);
            height: 100vh;
            overflow: hidden; /* Main body doesn't scroll, inner containers do */
        }

        a { text-decoration: none; color: inherit; transition: color 0.2s; }
        ul { list-style: none; }
        button { border: none; cursor: pointer; font-family: inherit; background: none; }

        /* --- 2. Layout & Utility Classes --- */
        .wrapper {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        /* Background Image & Overlay */
        .bg-fixed {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(31, 41, 55, 0.5); /* gray-800/50 */
            backdrop-filter: blur(12px); /* backdrop-blur-md */
            z-index: 5;
        }

        /* --- 3. Navigation --- */
        .navbar {
            position: relative;
            z-index: 20;
            height: 3rem; /* h-12 */
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-link {
            color: var(--gray-800);
            font-weight: 500;
            font-size: 1rem;
        }
        .nav-link:hover {
            color: var(--gray-900);
            text-decoration: underline;
        }

        /* Red underline for active link */
        .nav-link.active {
            color: var(--red-600);
            text-decoration: none;
            border-bottom: 2px solid var(--red-600);
        }

        .btn-login {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background-color: var(--red-500);
            color: var(--white);
            padding: 0.5rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.25rem; /* rounded */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }
        .btn-login:hover { background-color: var(--red-600); }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .btn-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--red-500);
            color: var(--white);
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 9999px; /* Round profile */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-profile:hover { background-color: var(--red-600); }
        .profile-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            background-color: var(--white);
            min-width: 160px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            z-index: 100;
            overflow: hidden;
            text-align: left;
        }
        .dropdown-content.show { display: block; }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        .dropdown-item:hover { background-color: var(--gray-50); text-decoration: none; }
        .dropdown-item svg { width: 1rem; height: 1rem; }
        .dropdown-divider { height: 1px; background-color: var(--gray-200); }

        /* --- 4. Main Content Area --- */
        .main-content {
            position: absolute;
            top: 3rem; /* below navbar */
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 30;
        }

        .content-container {
            max-width: 80rem;
            margin: 0 auto;
            height: 100%;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
        }

        /* --- 5. Left Section (Food List) --- */
        .food-section {
            flex-basis: 70%;
            min-width: 0;
            overflow-y: auto;
            padding-right: 0.25rem;
            
            /* Hide scrollbar logic */
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .food-section::-webkit-scrollbar { display: none; }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .section-title {
            font-family: var(--font-syne);
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--gray-200);
        }
        .section-subtitle {
            font-size: 0.875rem;
            color: var(--gray-200);
        }
        @media (min-width: 768px) {
            .section-title { font-size: 1.875rem; }
        }

        /* Grid */
        .food-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            padding-bottom: 5rem;
        }
        @media (min-width: 640px) { .food-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1280px) { .food-grid { grid-template-columns: repeat(3, 1fr); } }

        /* Food Card Button */
        .food-card {
            text-align: left;
            background-color: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: box-shadow 0.3s;
            display: block;
            width: 100%;
        }
        .food-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .card-img-wrapper {
            overflow: hidden;
            height: 9rem; /* h-36 */
            width: 100%;
        }
        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .food-card:hover .card-img { transform: scale(1.05); }

        .card-body { padding: 1rem; }
        
        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .card-title {
            font-family: var(--font-syne);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.25;
        }
        .card-price-tag {
            flex-shrink: 0;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--red-600);
            background-color: var(--red-50);
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
        }

        .card-desc {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
            line-height: 1.375;
            min-height: 40px; /* Preserve vertical rhythm */
        }

        .card-footer {
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tap-hint { font-size: 0.75rem; color: var(--gray-500); }
        .card-icon { font-size: 1.25rem; }

        /* --- 6. Right Section (Details Panel) --- */
        .details-aside {
            flex-basis: 30%;
            flex-shrink: 0;
            min-width: 280px;
            max-width: 24rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(24px);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            overflow-y: auto;
            max-height: calc(100vh - 5rem);
            
            /* Hide scrollbar but keep functionality */
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .details-aside::-webkit-scrollbar { display: none; }

        .details-container {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .empty-icon-box {
            width: 3.5rem; height: 3.5rem;
            margin: 0 auto 1rem;
            border-radius: 1rem;
            background-color: var(--red-50);
            display: flex; align-items: center; justify-content: center;
        }
        .empty-title {
            font-family: var(--font-syne);
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--gray-900);
        }
        .empty-text {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }

        .mt-auto { margin-top: auto; }
        
        .btn-disabled {
            width: 100%;
            background-color: var(--gray-200);
            color: var(--gray-500);
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: not-allowed;
        }

        /* Active State (Populated by JS) */
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .details-img {
            width: 100%;
            height: 8rem;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 0.75rem;
        }

        .details-content {
            flex: 1;
        }
        .details-title {
            font-family: var(--font-roboto);
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        .details-desc {
            font-size: 0.8rem;
            color: var(--gray-600);
            line-height: 1.5;
        }

        .details-footer {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--gray-100);
        }
        
        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .label-total {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
        }
        .price-large {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--red-600);
        }

        .btn-add {
            width: 100%;
            background-color: var(--red-600);
            color: var(--white);
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(254, 202, 202, 0.5); /* shadow-red-200 equivalent */
            transition: all 0.2s;
        }
        .btn-add:hover { background-color: var(--red-700); }
        .btn-add:active { transform: scale(0.95); }

    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Background -->
        <img 
            id="bg-slider"
            src="images/pepperoni_cheese_pizza-1.jpg" 
            alt="Pizza background" 
            class="bg-fixed"
        >
        
        <!-- Overlay -->
        <div class="overlay"></div>
        
        <!-- Navbar -->
        <nav class="navbar">
            <a href="index.php" class="brand">
                <span style="font-size: 1.5rem;">🍕</span>
                <span style="font-family: var(--font-syne);">PizzaHut</span>
            </a>

            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link active">Menu</a>
                <a href="track_order.php" class="nav-link">Track Order</a>
                <a href="About.php" class="nav-link">About</a>
                <a href="Contact.php" class="nav-link">Contact</a>
                
                <?php if ($adminLoggedIn): ?>
                <div class="profile-dropdown">
                    <button class="btn-profile" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=fee2e2&color=dc2626&size=64" alt="Profile" class="profile-avatar">
                        <span>Profile</span>
                        <svg style="width: 0.75rem; height: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-content" id="profileDropdown">
                        <a href="Dashboard.php" class="dropdown-item">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item" style="color: var(--red-600);">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </a>
                    </div>
                </div>
                <?php else: ?>
                    <a href="Auth.php" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-container">
                
                <!-- LEFT: Food List -->
                <section class="food-section">
                    <div class="section-header">
                        <h1 class="section-title">Menu</h1>
                        <p class="section-subtitle">Click an item to view details</p>
                    </div>

                    <div class="food-grid">
                        <?php 
                        $icons = ['🍕', '🧀', '🔥', '🥗', '🧀', '🍕', '🌶️', '👨‍🍳'];
                        if(!empty($menuItems)): 
                            foreach($menuItems as $index => $item): 
                                $icon = $icons[$index % count($icons)];
                                $escapedName = htmlspecialchars($item['name'], ENT_QUOTES);
                                $escapedDesc = htmlspecialchars($item['description'], ENT_QUOTES);
                                $escapedImg = htmlspecialchars($item['image_path'], ENT_QUOTES);
                        ?>
                        <button type="button" class="food-card"
                            data-id="<?php echo $item['id']; ?>"
                            data-name="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-image="<?php echo htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-desc="<?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-price="GH₵<?php echo number_format($item['price'], 2); ?>"
                            onclick="showFoodFromData(this)">
                            <div class="card-img-wrapper">
                                <img src="<?php echo $escapedImg; ?>" alt="<?php echo $escapedName; ?>" class="card-img">
                            </div>
                            <div class="card-body">
                                <div class="card-header">
                                    <h3 class="card-title"><?php echo $escapedName; ?></h3>
                                    <span class="card-price-tag">GH₵<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <p class="card-desc"><?php echo substr($escapedDesc, 0, 45) . (strlen($item['description']) > 45 ? '...' : ''); ?></p>
                                <div class="card-footer">
                                    <span class="tap-hint">Tap to view</span>
                                    <span class="card-icon"><?php echo $icon; ?></span>
                                </div>
                            </div>
                        </button>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--gray-500);">
                            <p>No menu items available at the moment.</p>
                        </div>
                        <?php endif; ?>

                        <!-- Card 2 -->
                        <!-- Dynamic cards generated above -->
                    </div>
                </section>

                <!-- RIGHT: Details Panel -->
                <aside class="details-aside">
                    <div id="food-details" class="details-container">
                        <!-- Initial Empty State -->
                        <div class="empty-state">
                            <div class="empty-icon-box">
                                <span style="font-size: 1.5rem;">🍕</span>
                            </div>
                            <h2 class="empty-title">Select a pizza</h2>
                            <p class="empty-text">Details will show here.</p>
                        </div>
                        <div class="mt-auto">
                            <button class="btn-disabled" disabled>
                                Add to cart
                            </button>
                        </div>
                    </div>
                </aside>
                
            </div>
        </main>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("profileDropdown").classList.toggle("show");
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.btn-profile') && !event.target.closest('.btn-profile')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <script>
        function toggleDropdown() {
            document.getElementById("profileDropdown").classList.toggle("show");
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.btn-profile') && !event.target.closest('.btn-profile')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <script>
        // Variable to hold current menu item ID for ordering
        let currentMenuItemId = null;
        
        // Check if there's a pre-selected item from index.php
        <?php if($selectedItem): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showFood(
                <?php echo $selectedItem['id']; ?>,
                '<?php echo htmlspecialchars($selectedItem['name'], ENT_QUOTES); ?>',
                '<?php echo htmlspecialchars($selectedItem['image_path'], ENT_QUOTES); ?>',
                '<?php echo htmlspecialchars($selectedItem['description'], ENT_QUOTES); ?>',
                'GH₵<?php echo number_format($selectedItem['price'], 2); ?>'
            );
        });
        <?php endif; ?>
        
        function showFoodFromData(element) {
            const id = element.getAttribute('data-id');
            const name = element.getAttribute('data-name');
            const image = element.getAttribute('data-image');
            const description = element.getAttribute('data-desc');
            const price = element.getAttribute('data-price');
            showFood(id, name, image, description, price);
        }

        function showFood(id, name, image, description, price) {
            currentMenuItemId = id;
            const details = document.getElementById('food-details');
            
            // Escape name for use in onclick attribute to prevent syntax errors with quotes
            const escapedName = name.replace(/'/g, "\\'");
            
            details.innerHTML = `
                <div class="details-container animate-fade-in">
                    <img src="${image}" alt="${name}" class="details-img">
                    
                    <div class="details-content">
                        <h2 class="details-title">${name}</h2>
                        <p class="details-desc">${description}</p>
                        
                        <div style="margin-top: 0.75rem;">
                            <div style="margin-bottom: 0.5rem;">
                                <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--gray-700); margin-bottom:0.25rem;">Your Name</label>
                                <input type="text" id="custName" placeholder="e.g. Kofi Mensah" style="width:100%; padding:0.5rem; font-size:0.875rem; border:1px solid var(--gray-200); border-radius:0.5rem; font-family:inherit;">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--gray-700); margin-bottom:0.25rem;">Phone Number</label>
                                <input type="tel" id="custPhone" placeholder="e.g. 054 123 4567" style="width:100%; padding:0.5rem; font-size:0.875rem; border:1px solid var(--gray-200); border-radius:0.5rem; font-family:inherit;">
                            </div>
                        </div>
                    </div>

                    <div class="details-footer">
                        <div class="price-row">
                            <span class="label-total">Total</span>
                            <span class="price-large">${price}</span>
                        </div>
                        <button class="btn-add" onclick="addToCart('${escapedName}', '${price}')">
                            Add to Order
                        </button>
                    </div>
                </div>
            `;
        }

        function addToCart(name, price) {
            if (!currentMenuItemId) {
                alert('Please select a menu item first!');
                return;
            }

            const custNameInput = document.getElementById('custName');
            const custPhoneInput = document.getElementById('custPhone');
            
            if (!custNameInput || !custPhoneInput) {
                alert('Form error. Please re-select the item.');
                return;
            }

            const custName = custNameInput.value.trim();
            const custPhone = custPhoneInput.value.trim();
            
            if (!custName || !custPhone) {
                alert('Please enter your name and phone number to order!');
                return;
            }
            
            // Send order to server
            fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    customerName: custName,
                    customerPhone: custPhone,
                    menuItemId: currentMenuItemId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`🍕 Order Placed Successfully!\n\nYour Order Number: ${data.orderNumber}\n\nItem: ${name} (${price})\nName: ${custName}\nPhone: ${custPhone}\n\nPlease save your order number to track your order!`);
                    // Reset form
                    document.getElementById('custName').value = '';
                    document.getElementById('custPhone').value = '';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error. Please try again.');
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
