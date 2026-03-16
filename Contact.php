<?php
session_start();
// Process contact form submission
require_once 'db_connection.php';

$adminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $messageText = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($fullName) || empty($email) || empty($subject) || empty($messageText)) {
        $message = 'All fields are required!';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address!';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $email, $subject, $messageText]);
            
            $message = 'Thank you! Your message has been sent successfully. We\'ll get back to you soon.';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'An error occurred. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <title>Contact Us | PizzaHut Ghana</title>
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Colors */
            --red-100: #fee2e2;
            --red-500: #ef4444;
            --red-600: #dc2626;
            
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            
            --green-100: #dcfce7;
            --green-500: #22c55e;
            
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
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
            color: var(--gray-900);
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

        /* --- 3. Navigation (Glassmorphism) --- */
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

        /* --- 4. Hero Header --- */
        header {
            background-color: var(--gray-900);
            padding: 5rem 0;
            text-align: center;
        }

        .hero-title {
            font-family: var(--font-syne);
            font-size: 3rem;
            font-weight: 900;
            color: var(--white);
            margin-bottom: 1rem;
        }
        @media (min-width: 768px) {
            .hero-title { font-size: 3.75rem; }
        }

        .hero-subtitle {
            color: var(--gray-400);
            max-width: 40rem;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* --- 5. Main Content --- */
        main {
            margin-top: -3rem;
            margin-bottom: 5rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        @media (min-width: 1024px) {
            .contact-grid { 
                grid-template-columns: repeat(3, 1fr); 
            }
            .contact-grid > div:first-child { grid-column: 1; }
            .contact-grid > div:last-child { grid-column: 2 / 4; }
        }

        /* Contact Cards */
        .contact-card {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-100);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .icon-container {
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-container svg { width: 1.5rem; height: 1.5rem; }

        .icon-phone { background-color: var(--red-100); color: var(--red-500); }
        .icon-email { background-color: var(--blue-100); color: var(--blue-500); }
        .icon-location { background-color: var(--green-100); color: var(--green-500); }

        .contact-info h4 {
            font-family: var(--font-syne);
            font-weight: 700;
            color: var(--gray-900);
        }
        .contact-info p {
            color: var(--gray-600);
            margin-top: 0.25rem;
        }

        /* Contact Form */
        .contact-form {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2rem;
        }
        @media (min-width: 768px) {
            .contact-form { padding: 3rem; }
        }
        .contact-form > div {
            border: 1px solid var(--gray-100);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .form-row { grid-template-columns: repeat(2, 1fr); }
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input,
        .form-select,
        .form-textarea {
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: var(--gray-900);
            transition: all 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--red-500);
        }

        .form-textarea {
            resize: vertical;
            min-height: 8rem;
        }

        .form-submit {
            width: 100%;
            background-color: var(--red-500);
            color: var(--white);
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            transform: translateY(0);
        }
        .form-submit:hover {
            background-color: var(--red-600);
            transform: translateY(-0.25rem);
        }

        /* Contact Cards Spacing */
        .contact-cards { display: flex; flex-direction: column; gap: 1.5rem; }

        /* --- 6. Footer --- */
        footer {
            background-color: var(--white);
            border-top: 1px solid var(--gray-200);
            padding: 2rem 0;
        }

        .footer-content {
            text-align: center;
            color: var(--gray-500);
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-brand">
            <span class="nav-brand-icon">🍕</span>
            <span style="font-family: var(--font-syne);">PizzaHut</span>
        </div>
        
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="menu.php" class="nav-link">Menu</a>
            <a href="track_order.php" class="nav-link">Track Order</a>
            <a href="About.php" class="nav-link">About</a>
            <a href="Contact.php" class="nav-link active">Contact</a>
            
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

    <!-- Hero Header -->
    <header>
        <div class="container">
            <h1 class="hero-title">Get In Touch</h1>
            <p class="hero-subtitle">Have a question about your order or want to share feedback? We're listening.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="contact-grid">
            <!-- Contact Cards -->
            <div class="contact-cards">
                <!-- Phone -->
                <div class="contact-card">
                    <div class="icon-container icon-phone">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <div class="contact-info">
                        <h4>Call Us</h4>
                        <p>+233 20 000 0000</p>
                        <p>+233 30 000 0000</p>
                    </div>
                </div>

                <!-- Email -->
                <div class="contact-card">
                    <div class="icon-container icon-email">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="contact-info">
                        <h4>Email Us</h4>
                        <p>hello@pizzahut.com.gh</p>
                        <p>orders@pizzahut.com.gh</p>
                    </div>
                </div>

                <!-- Location -->
                <div class="contact-card">
                    <div class="icon-container icon-location">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="contact-info">
                        <h4>Visit Us</h4>
                        <p>Oxford Street, Osu</p>
                        <p>Accra, Ghana</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <?php if (!empty($message)): ?>
                <div style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; <?php echo $messageType === 'success' ? 'background-color: var(--green-100); color: #166534;' : 'background-color: var(--red-100); color: #991b1b;'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                <form class="form-group" method="POST" action="">
                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" placeholder="John Doe" class="form-input" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" placeholder="john@example.com" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-select" required>
                            <option value="Order Feedback">Order Feedback</option>
                            <option value="Partnership Inquiry">Partnership Inquiry</option>
                            <option value="Career Opportunities">Career Opportunities</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="5" placeholder="Tell us how we can help..." class="form-textarea" required></textarea>
                    </div>
                    
                    <button type="submit" class="form-submit">Send Message</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                © 2026 PizzaHut Ghana. Quality you can taste.
            </div>
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
    <?php if (!empty($message) && $messageType === 'success'): ?>
    <script>
        // Alert user on successful submission
        setTimeout(function() {
            alert("<?php echo addslashes($message); ?>");
        }, 100);
    </script>
    <?php endif; ?>
</body>
</html>
