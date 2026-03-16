<?php
session_start();
$adminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts: Syne for headings, Roboto for body -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <title>About - PizzaHut Ghana</title>
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Colors */
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
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
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        a { 
            text-decoration: none; 
            color: inherit; 
            transition: all 0.2s ease; 
        }

        /* --- 2. Layout Utilities --- */
        .container {
            max-width: 80rem; /* max-w-7xl */
            margin: 0 auto;
            padding: 0 1.5rem; /* px-6 */
        }

        .section-py { padding: 2.5rem 0; } /* py-10 */
        
        /* --- 3. Navigation --- */
        nav {
            position: relative;
            z-index: 20;
            background-color: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(24px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.7);
            height: 3rem;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .nav-brand-icon { font-size: 1.5rem; }

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
        
        /* Red underline for active link */
        .nav-link.active {
            color: var(--red-600);
            text-decoration: none;
            border-bottom: 2px solid var(--red-600);
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
            display: inline-flex;
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

        /* --- 4. Header Section --- */
        header {
            background-color: var(--white);
        }

        .header-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: center;
        }
        @media (min-width: 768px) {
            .header-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--red-600);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .hero-title {
            font-family: var(--font-syne);
            font-weight: 900;
            color: var(--gray-900);
            font-size: 1.5rem;
            margin-top: 0.5rem;
        }
        @media (min-width: 768px) {
            .hero-title { font-size: 1.875rem; }
        }

        .hero-desc {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 1rem;
            line-height: 1.625;
        }

        .btn-group {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.75rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--red-600);
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s;
        }
        .btn-primary:hover { background-color: var(--red-700); }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gray-300);
            color: var(--gray-800);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
        }
        .btn-secondary:hover { border-color: var(--gray-400); }

        /* Stats Card */
        .stats-card {
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .stats-title {
            font-family: var(--font-syne);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stats-grid {
            margin-top: 1.25rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-item {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .stat-label { 
            font-size: 0.75rem; 
            color: var(--gray-500); 
        }
        .stat-value { 
            margin-top: 0.25rem; 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: var(--gray-900); 
        }

        .stats-footer {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 1rem;
        }

        /* --- 5. Main Content --- */
        .story-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .story-grid { 
                grid-template-columns: 1fr 2fr; 
            }
            .story-grid > div:first-child { grid-column: 1; }
            .story-grid > div:last-child { grid-column: 2 / 4; }
        }

        .story-section {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .section-title {
            font-family: var(--font-syne);
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--gray-900);
        }

        .section-subtitle {
            font-family: var(--font-syne);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .section-text {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
            line-height: 1.625;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .content-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* FAQ Section */
        .faq-card {
            border-radius: 1rem;
            border: 1px solid var(--gray-200);
            padding: 1.5rem;
        }

        .faq-grid {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .faq-grid { grid-template-columns: repeat(3, 1fr); }
        }

        /* CTA Section */
        .cta-section {
            background-color: var(--gray-900);
            color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .cta-section { flex-direction: row; align-items: center; }
        }

        .cta-title {
            font-family: var(--font-syne);
            font-size: 1.25rem;
            font-weight: 900;
        }

        .cta-text {
            font-size: 0.875rem;
            color: var(--gray-300);
            margin-top: 0.5rem;
        }

        .cta-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .cta-btn-primary {
            background-color: var(--white);
            color: var(--gray-900);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            transition: background-color 0.2s;
        }
        .cta-btn-primary:hover { background-color: var(--gray-100); }

        .cta-btn-secondary {
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
        }
        .cta-btn-secondary:hover { 
            background-color: rgba(255, 255, 255, 0.1); 
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--gray-200);
            background-color: var(--white);
        }

        .footer-content {
            padding: 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .footer-content { flex-direction: row; }
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        .footer-text {
            font-size: 0.75rem;
            color: var(--gray-500);
        }
    </style>
</head>

<body>
    <!-- Nav -->
    <nav>
        <div class="nav-brand">
            <span class="nav-brand-icon">🍕</span>
            <span style="font-family: var(--font-syne);">PizzaHut</span>
        </div>
        
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="menu.php" class="nav-link">Menu</a>
            <a href="track_order.php" class="nav-link">Track Order</a>
            <a href="About.php" class="nav-link active">About</a>
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

    <!-- Header -->
    <header>
        <div class="container section-py">
            <div class="header-grid">
                <div>
                    <p class="badge">About us</p>
                    <h1 class="hero-title">Pizza made for Ghanaian cravings</h1>
                    <p class="hero-desc">
                        We serve pizza that fits your mood—crispy, cheesy, and packed with flavor.
                        From quick lunch bites to family nights, we keep it simple: good food, clean service, and fast delivery.
                    </p>

                    <div class="btn-group">
                        <a href="Menu.php" class="btn-primary">View menu</a>
                        <a href="Contact.php" class="btn-secondary">Talk to us</a>
                    </div>
                </div>

                <!-- Small stats card -->
                <div class="stats-card">
                    <h2 class="stats-title">What we focus on</h2>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <p class="stat-label">Quality</p>
                            <p class="stat-value">Fresh ingredients</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Speed</p>
                            <p class="stat-value">Fast delivery</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Safety</p>
                            <p class="stat-value">Clean kitchen</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Taste</p>
                            <p class="stat-value">Bold flavors</p>
                        </div>
                    </div>

                    <p class="stats-footer">
                        Built for busy days, chill nights, and everything in between.
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main>
        <!-- Our story -->
        <section class="container section-py">
            <div class="story-grid">
                <div>
                    <h2 class="section-title">Our story</h2>
                    <p class="section-text">
                        We started with one goal: make pizza that feels premium but still accessible.
                        Today we're focused on consistent taste, reliable delivery, and friendly service.
                    </p>
                </div>

                <div class="content-grid">
                    <div class="story-section">
                        <h3 class="section-subtitle">What we believe</h3>
                        <p class="section-text">
                            Great food comes from good ingredients, simple processes, and attention to detail.
                        </p>
                    </div>

                    <div class="story-section">
                        <h3 class="section-subtitle">How we serve</h3>
                        <p class="section-text">
                            We keep ordering easy, payment safe, and delivery smooth—so you can just enjoy.
                        </p>
                    </div>

                    <div class="story-section">
                        <h3 class="section-subtitle">For every vibe</h3>
                        <p class="section-text">
                            Solo cravings, date nights, office lunch, or family hangouts—there's a box for it.
                        </p>
                    </div>

                    <div class="story-section">
                        <h3 class="section-subtitle">Local love</h3>
                        <p class="section-text">
                            We're inspired by Ghana—our flavors, energy, and the way we enjoy food together.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ-ish -->
        <section style="border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200);">
            <div class="container section-py">
                <h2 class="section-title">Quick answers</h2>

                <div class="faq-grid">
                    <div class="faq-card">
                        <h3 class="section-subtitle">Do you deliver?</h3>
                        <p class="section-text">Yes—fast delivery across Accra (and nearby areas depending on distance).</p>
                    </div>
                    <div class="faq-card">
                        <h3 class="section-subtitle">How do I order?</h3>
                        <p class="section-text">Pick a pizza from the Menu, confirm details, then place your order.</p>
                    </div>
                    <div class="faq-card">
                        <h3 class="section-subtitle">Is payment safe?</h3>
                        <p class="section-text">We support secure payment options—no stress, just chop.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="container section-py">
            <div class="cta-section">
                <div>
                    <h2 class="cta-title">Ready to order?</h2>
                    <p class="cta-text">Check the menu and pick your next favourite.</p>
                </div>

                <div class="cta-buttons">
                    <a href="Menu.php" class="cta-btn-primary">Go to menu</a>
                    <a href="Contact.php" class="cta-btn-secondary">Contact us</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <span>🍕</span>
                <span style="font-family: var(--font-syne);">PizzaHut Ghana</span>
            </div>
            <p class="footer-text">© 2026 PizzaHut Ghana. All rights reserved.</p>
        </div>
    </footer>
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
</body>
</html>
