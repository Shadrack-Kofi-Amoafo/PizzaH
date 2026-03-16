<?php
session_start();

// Fetch menu items from database
require_once 'db_connection.php';

// Check if admin is logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$adminName = $_SESSION['admin_name'] ?? 'Admin';

try {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY id LIMIT 8");
    $menuItems = $stmt->fetchAll();
} catch(PDOException $e) {
    $menuItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut Ghana</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Palette */
            --red-500: #ef4444;
            --red-600: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --black: #000000;
            
            /* Feature Colors */
            --green-500: #22c55e;
            --blue-500: #3b82f6;
            --orange-500: #f97316;

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
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; transition: color 0.2s; }
        ul { list-style: none; }
        button { border: none; cursor: pointer; font-family: inherit; }

        /* --- 2. Layout Utilities --- */
        .container {
            max-width: 80rem; /* 7xl equivalent */
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .section-py { padding-top: 5rem; padding-bottom: 5rem; }
        .text-center { text-align: center; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .grid { display: grid; }
        
        /* --- 3. Navigation --- */
        .navbar {
            position: relative;
            z-index: 20;
            height: 3rem;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(24px); /* backdrop-blur-xl */
            border-bottom: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: bold;
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
        }
        .nav-link:hover {
            color: var(--gray-900);
            text-decoration: underline;
        }

        .btn-login {
            background-color: var(--red-500);
            color: var(--white);
            padding: 0.5rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s;
            display: inline-flex; /* Ensure flex for centering if needed */
            align-items: center;
            justify-content: center;
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
            border-radius: 9999px; /* Rounded pill shape specifically requested as 'round profile' often implies circular or rounded pill */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s;
            cursor: pointer;
        }
        .btn-profile:hover { background-color: var(--red-600); }
        .profile-avatar {
            width: 2rem; /* slightly larger */
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
        }
        .dropdown-item:hover { background-color: var(--gray-50); }
        .dropdown-item svg { width: 1rem; height: 1rem; }
        .dropdown-divider { height: 1px; background-color: var(--gray-200); }

        /* --- 4. Hero Section --- */
        .hero {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .bg-slider {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.5s ease;
        }
        
        .opacity-0 { opacity: 0; }

        /* Overlays */
        .overlay-base {
            position: absolute; inset: 0;
            background-color: rgba(0, 0, 0, 0.1);
            z-index: 0;
        }
        .overlay-dark {
            position: absolute; inset: 0;
            background: linear-gradient(to right, rgba(17, 24, 39, 0.6), transparent, transparent);
            z-index: 5;
        }
        .overlay-light {
            position: absolute; inset: 0;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.4), transparent);
            z-index: 15;
        }

        .hero-content {
            position: absolute;
            left: 3rem;
            bottom: 8rem;
            z-index: 25;
            max-width: 42rem;
            padding: 1.5rem;
            color: var(--gray-900);
        }

        .hero-title-1 {
            font-family: var(--font-syne);
            font-weight: 600;
            font-size: 2.25rem; /* text-4xl */
            margin-bottom: 0;
            line-height: 1.25;
            white-space: nowrap;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .hero-title-2 {
            font-family: var(--font-syne);
            font-weight: 700;
            font-size: 1.5rem; /* text-2xl */
            margin-bottom: 1.5rem;
            color: var(--gray-700);
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .hero-desc {
            font-size: 1.25rem;
            line-height: 1.625;
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 28rem;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.05));
        }

        .btn-cta {
            background-color: var(--red-500);
            color: var(--white);
            padding: 0.5rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 0.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        .btn-cta:hover { background-color: var(--red-600); }

        .slider-controls {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            margin-left: 0.5rem;
        }

        .dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            background-color: var(--gray-400);
            border: 1px solid var(--white);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .dot:hover { background-color: #fca5a5; /* red-300 */ }
        .dot.active { background-color: var(--red-500); }

        /* Media Queries for Hero Typography */
        @media (min-width: 768px) {
            .hero-title-1 { font-size: 4.5rem; /* text-7xl */ }
            .hero-title-2 { font-size: 2.25rem; /* text-4xl */ }
        }

        /* --- 5. Features Section --- */
        .section-features {
            background: linear-gradient(to bottom, rgba(255,255,255,0.8), #ffffff);
        }

        .section-title {
            font-family: var(--font-syne);
            font-weight: 900;
            color: var(--gray-700);
            font-size: 1.875rem;
            filter: drop-shadow(0 10px 8px rgba(0,0,0,0.04));
        }
        @media (min-width: 768px) { .section-title { font-size: 3rem; } }

        .grid-features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            max-width: 72rem;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        @media (min-width: 768px) { .grid-features { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .grid-features { grid-template-columns: repeat(4, 1fr); } }

        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }
        .feature-card:hover { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }

        .feature-icon { width: 5rem; height: 5rem; margin: 0 auto 1.5rem; }
        .feature-icon.red { color: var(--red-500); }
        .feature-icon.green { color: var(--green-500); }
        .feature-icon.blue { color: var(--blue-500); }
        .feature-icon.orange { color: var(--orange-500); }

        .feature-title { font-family: var(--font-syne); font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; }
        .feature-text { font-size: 1.125rem; color: var(--gray-700); line-height: 1.625; }

        /* --- 6. Menu Section --- */
        .bg-gray-50 { background-color: var(--gray-50); }
        .popular-title { color: var(--gray-900); font-weight: 900; font-family: var(--font-syne); font-size: 2.25rem; }
        @media (min-width: 768px) { .popular-title { font-size: 3rem; } }

        .grid-menu {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2.5rem;
        }
        @media (min-width: 640px) { .grid-menu { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .grid-menu { grid-template-columns: repeat(4, 1fr); } }

        .menu-card {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        /* Special rounded case if needed, otherwise standard square-ish as per your code */
        .rounded-std { border-radius: 0.25rem; }

        .menu-img { height: 12rem; width: 100%; object-fit: cover; }
        .menu-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
        .menu-name { font-family: var(--font-syne); font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--gray-900); }
        .menu-desc { color: var(--gray-600); margin-bottom: 1rem; }
        .menu-footer { margin-top: auto; display: flex; align-items: center; justify-content: space-between; }
        .menu-price { font-size: 1.5rem; font-weight: 700; color: var(--red-500); }
        .btn-order { background-color: var(--black); color: var(--white); padding: 0.25rem 2rem; border-radius: 0.25rem; font-size: 0.875rem; }

        /* --- 7. Chefs Section --- */
        .bg-white { background-color: var(--white); }
        .chef-card { text-align: center; }
        .chef-img-wrapper { display: inline-block; position: relative; margin-bottom: 1.5rem; }
        .chef-img {
            width: 12rem; height: 12rem;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--red-500);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .chef-card:hover .chef-img { transform: scale(1.05); }
        .chef-name { font-family: var(--font-syne); font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .chef-role { color: var(--red-500); font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; }

        /* --- 8. Footer --- */
        .footer {
            background-color: var(--gray-900);
            color: var(--white);
            padding-top: 4rem;
            padding-bottom: 2rem;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        @media (min-width: 768px) { .footer-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .footer-grid { grid-template-columns: repeat(4, 1fr); } }

        .footer-brand { display: flex; align-items: center; gap: 0.5rem; font-family: var(--font-syne); font-weight: 700; font-size: 1.5rem; }
        .text-red { color: var(--red-500); }
        .footer-desc { color: var(--gray-400); line-height: 1.625; margin-top: 1rem; }
        
        .social-links { display: flex; gap: 1rem; margin-top: 0.5rem; }
        .social-icon {
            width: 2.5rem; height: 2.5rem;
            background-color: var(--gray-800);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: background-color 0.2s;
        }
        .social-icon:hover { background-color: var(--red-500); }
        .icon-svg { width: 1.25rem; height: 1.25rem; fill: currentColor; }

        .footer-heading { font-family: var(--font-syne); font-size: 1.125rem; font-weight: 700; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .footer-links li { margin-bottom: 1rem; }
        .footer-links a { color: var(--gray-400); }
        .footer-links a:hover { color: var(--red-500); }

        .newsletter-form { display: flex; flex-direction: column; gap: 0.5rem; }
        .input-dark {
            background-color: var(--gray-800);
            border: none;
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            color: var(--white);
        }
        .input-dark:focus { outline: 2px solid var(--red-500); }
        .btn-sub {
            background-color: var(--red-500);
            color: var(--white);
            font-weight: 700;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .btn-sub:hover { background-color: var(--red-600); }

        .footer-bottom {
            border-top: 1px solid var(--gray-800);
            margin-bottom: 2rem;
        }
        .copyright-row {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 2rem; /* pt-8 equivalent if needed, but mb-8 on hr handles it */
        }
        @media (min-width: 768px) { .copyright-row { flex-direction: row; } }
    </style>
</head>
<body>
    
    <!-- Hero Section -->
    <div class="hero">
        <img 
            id="bg-slider"
            src="images/pepperoni_cheese_pizza-1.jpg" 
            alt="Pizza background" 
            class="bg-slider"
        >
        
        <!-- Overlays -->
        <div class="overlay-base"></div>
        <div class="overlay-dark"></div>
        <div class="overlay-light"></div>
        
        <!-- Navigation -->
        <nav class="navbar">
            <div class="brand">
                <span style="font-size: 1.5rem;">🍕</span>
                <span>PizzaHut</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="menu.php" class="nav-link">Menu</a></li>
                <li><a href="track_order.php" class="nav-link">Track Order</a></li>
                <li><a href="Contact.php" class="nav-link">Contact</a></li>
                <li><a href="About.php" class="nav-link">About</a></li>
                <li>
                    <?php if($isLoggedIn): ?>
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
                </li>
            </ul>
        </nav>

        <!-- Scrolling Text -->
        <div id="scrolling-text-container" style="position: absolute; top: 4rem; left: 0; width: 100%; white-space: nowrap; z-index: 50; overflow: hidden; pointer-events: none;">
            <div id="scrolling-text" style="display: inline-block; color: white; font-family: var(--font-syne); font-weight: 700; font-size: 1.25rem; background: rgba(0,0,0,0.4); padding: 0.5rem 1rem; border-radius: 9999px;">
                Welcome to PizzaHut Ghana! 🍕 Best Pizza in Town! 🔥 Order Now and Get 50% OFF on Tasty Pepperoni! 🌶️ Fast Delivery! 🛵 Call us at 055-123-4567 for support! 📞
            </div>
        </div>

        <!-- Hero Text -->
        <div class="hero-content">
            <h1 class="hero-title-1">Best Pizza</h1>
            <h2 class="hero-title-2">in Ghana</h2>
            <p class="hero-desc">
                Fresh Ghanaian flavors with local ingredients, crispy crust, and hot delivery straight to your door.
            </p>
            
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 1.5rem;">
                <button class="btn-cta" onclick="document.getElementById('popular-menu').scrollIntoView({behavior: 'smooth'});">Check menu</button>
                
                <div class="slider-controls">
                    <button onclick="changeBg(0)" class="dot active"></button>
                    <button onclick="changeBg(1)" class="dot"></button>
                    <button onclick="changeBg(2)" class="dot"></button>
                    <button onclick="changeBg(3)" class="dot"></button>
                    <button onclick="changeBg(4)" class="dot"></button>
                    <button onclick="changeBg(5)" class="dot"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="section-py section-features">
        <div class="text-center" style="margin-bottom: 4rem;">
            <h2 class="section-title">Why Pizza Hut?</h2>
        </div>
        
        <div class="grid-features">
            <!-- Delicious -->
            <div class="feature-card">
                <svg class="feature-icon red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM17 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4h18M12 4v16"></path>
                </svg>
                <h3 class="feature-title">Delicious</h3>
                <p class="feature-text">Fresh Ghanaian flavors with mouthwatering toppings</p>
            </div>

            <!-- Safe Payment -->
            <div class="feature-card">
                <svg class="feature-icon green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 class="feature-title">Safe Payment</h3>
                <p class="feature-text">Secure checkout with trusted payment options</p>
            </div>

            <!-- Home Delivery -->
            <div class="feature-card">
                <svg class="feature-icon blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-4V7m8 4h-4m0 0v10m0-10l-8 4"></path>
                </svg>
                <h3 class="feature-title">Home Delivery</h3>
                <p class="feature-text">Fast & reliable delivery across Accra</p>
            </div>

            <!-- Nutritious -->
            <div class="feature-card">
                <svg class="feature-icon orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <h3 class="feature-title">Nutritious</h3>
                <p class="feature-text">Fresh ingredients packed with goodness</p>
            </div>
        </div>
    </div>

    <!-- Menu Section -->
    <div class="section-py bg-gray-50" id="popular-menu">
        <div class="container">
            <div class="text-center" style="margin-bottom: 3rem;">
                <h2 class="popular-title">Our Popular Pizzas</h2>
            </div>

            <div class="grid-menu">
                <?php if (empty($menuItems)): ?>
                    <!-- Fallback static content if database is empty -->
                    <!-- Card 1 -->
                    <div class="menu-card" onclick="window.location.href='menu.php?item=1'" style="cursor: pointer;">
                        <img src="images/pepperoni_cheese_pizza-1.jpg" alt="Pepperoni Classic" class="menu-img">
                        <div class="menu-body">
                            <h3 class="menu-name">Pepperoni Classic</h3>
                            <p class="menu-desc">Spicy pepperoni with double cheese.</p>
                            <div class="menu-footer">
                                <span class="menu-price">GH₵45</span>
                                <button class="btn-order" onclick="event.stopPropagation(); window.location.href='menu.php?item=1'">Order</button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($menuItems as $item): ?>
                    <div class="menu-card box-shadow-hover" onclick="window.location.href='menu.php?item=<?php echo $item['id']; ?>'" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-img">
                        <div class="menu-body">
                            <h3 class="menu-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="menu-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-footer">
                                <span class="menu-price">GH₵<?php echo number_format($item['price'], 2); ?></span>
                                <button class="btn-order" onclick="event.stopPropagation(); window.location.href='menu.php?item=<?php echo $item['id']; ?>'">Order</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- See more button -->
            <div style="display: flex; justify-content: flex-end;">
                <button class="btn-cta" onclick="window.location.href='menu.php'">See more</button>
            </div>
        </div>
    </div>

    <!-- Chefs Section -->
    <div class="section-py bg-white">
        <div class="container">
            <div class="text-center" style="margin-bottom: 4rem;">
                <h2 class="popular-title">Meet Our Master Chefs</h2>
                <p style="color: var(--gray-600); margin-top: 1rem;">The hands behind the best pizza in Ghana</p>
            </div>

            <div class="grid-features" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <!-- Member 1 -->
                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/i.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">David Ekow Ansah</h3>
                    <p class="chef-role">ID: 0109</p>
                </div>

                <!-- Member 2 -->
                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/Presentation1a.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Ernest Baah Ampadu</h3>
                    <p class="chef-role">ID: 0253</p>
                </div>

                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/WhatsApp Image 2026-02-07 at 1.34.21 AM.jpeg" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Mumuni Adamu Agotiba</h3>
                    <p class="chef-role">ID: 0023</p>
                </div>
                

                <!-- Member 3 -->
                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/pow.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Sonu Bernard</h3>
                    <p class="chef-role">ID: 0031</p>
                </div>

                
                <!-- Member 4 -->
                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/io.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Emmanuel Agyenim Boateng</h3>
                    <p class="chef-role">ID: 0122</p>
                </div>

                <!-- Member 5 -->
               

                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/01ae0715-7b25-4fad-9d35-4de9741a6338.jpg" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Shadrack Amoafo</h3>
                    <p class="chef-role">ID: 0268</p>
                </div>

                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/Picture1s.jpg" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Dadzie Calvin Elorm</h3>
                    <p class="chef-role">ID: 0261</p>
                </div>

                <!-- Member 5 -->
                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/ii.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Frederick Gyesi Gyaban</h3>
                    <p class="chef-role">ID: 0054</p>
                </div>

                 <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/Picture2x.jpg" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Richard Turkson</h3>
                    <p class="chef-role">ID:0213</p>
                </div>

                <div class="chef-card">
                    <div class="chef-img-wrapper">
                        <img src="Images/zz.png" alt="Group Member" class="chef-img">
                    </div>
                    <h3 class="chef-name">Napoleon-Commey</h3>
                    <p class="chef-role">ID: 0236</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                
                <!-- Brand Col -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="footer-brand">
                        <span style="font-size: 1.875rem;">🍕</span>
                        <span>PizzaHut<span class="text-red">.</span>gh</span>
                    </div>
                    <p class="footer-desc">
                        Bringing the world's favorite pizza to the heart of Ghana. Quality ingredients, local flavors, and lightning-fast delivery.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <span class="sr-only">Facebook</span>
                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg>
                        </a>
                        <a href="#" class="social-icon">
                            <span class="sr-only">Instagram</span>
                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7zM12 7a5 5 0 110 10 5 5 0 010-10zm0 2a3 3 0 100 6 3 3 0 000-6z"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Links Col -->
                <div>
                    <h4 class="footer-heading">Quick Menu</h4>
                    <ul class="footer-links">
                        <li><a href="#">Pepperoni Classic</a></li>
                        <li><a href="#">Ghana Special</a></li>
                        <li><a href="#">Cheese Overload</a></li>
                        <li><a href="#">Vegetarian options</a></li>
                    </ul>
                </div>

                <!-- Support Col -->
                <div>
                    <h4 class="footer-heading">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Track Order</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Newsletter Col -->
                <div>
                    <h4 class="footer-heading">Stay Spicy</h4>
                    <p class="footer-desc" style="font-size: 0.875rem; margin-bottom: 1rem;">Subscribe to get secret coupons and new menu alerts.</p>
                    <div class="newsletter-form">
                        <button class="btn-sub" onclick="joinClub()">Join Club</button>
                    </div>
                </div>
            </div>

            <div class="footer-bottom"></div>

            <div class="copyright-row">
                <p>© 2026 PizzaHut Ghana. All rights reserved.</p>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <span>Designed with ❤️ in Accra</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function joinClub() {
            const email = prompt("Please enter your email address to join the Spicy Club:", "");
            if (email) {
                if(email.includes('@')) {
                    alert("Thanks! We've sent a welcome coupon to " + email);
                } else {
                     alert("That doesn't look like a valid email, but thanks for the enthusiasm!");
                }
            }
        }

        const images = [
            'images/pepperoni_cheese_pizza-1.jpg',
            'images/pepperoni-pizza.jpg',
            'images/d1a91648-abc2-41ae-92cc-53d2b48fc80e.avif',
            'images/ranch-chicken-crust-pepperoni-pizza-RDP.jpg',
            'images/Web_2400_Recipe_Thanksgiving-Pepperoni-Pizza-1700x708.jpg',
            'images/pepperoni-pizza.jpg' 
        ];

        let currentIndex = 0;
        let slideInterval;

        function changeBg(index) {
            currentIndex = index;
            const bgImg = document.getElementById('bg-slider');
            const dots = document.querySelectorAll('.dot');

            // 1. Fade Out
            bgImg.classList.add('opacity-0');

            // 2. Wait for fade out (300ms), then swap and fade in
            setTimeout(() => {
                bgImg.src = images[currentIndex];
                
                // Wait for browser to load new src
                bgImg.onload = () => {
                    bgImg.classList.remove('opacity-0');
                };
                
                // Fallback
                bgImg.classList.remove('opacity-0');
            }, 300);

            // 3. Update Dots (Logic updated for semantic CSS)
            dots.forEach((dot, i) => {
                if(i === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });

            resetTimer();
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % images.length;
            changeBg(currentIndex);
        }

        function startTimer() {
            slideInterval = setInterval(nextSlide, 3000);
        }

        function resetTimer() {
            clearInterval(slideInterval);
            startTimer();
        }

        startTimer();
        
        // Profile dropdown toggle
        function toggleDropdown() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.btn-profile') && !event.target.closest('.btn-profile')) {
                var dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
    <script>
        // Simple JS scrolling text
        const textContainer = document.getElementById('scrolling-text');
        let position = window.innerWidth;
        const speed = 2;
        
        function scrollText() {
            position -= speed;
            // Reset when fully off screen to the left
            if (position < -textContainer.offsetWidth) {
                position = window.innerWidth;
            }
            textContainer.style.transform = `translateX(${position}px)`;
            requestAnimationFrame(scrollText);
        }
        
        // Start scrolling
        scrollText();
    </script>
</body>
</html>
