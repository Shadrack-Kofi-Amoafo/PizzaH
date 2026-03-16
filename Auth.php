<?php
session_start();
require_once 'db_connection.php';

$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: Dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'login';
    
    if ($action === 'login') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['full_name'];
                    $_SESSION['admin_email'] = $user['email'];
                    
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    header('Location: Dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } catch(PDOException $e) {
                $error = 'An error occurred. Please try again.';
            }
        }
    } elseif ($action === 'signup') {
        $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already registered.';
                } else {
                    // Create new admin user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $username = strtolower(str_replace(' ', '_', $fullName)) . rand(100, 999);
                    
                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $hashedPassword, $fullName, $email]);
                    
                    $success = 'Account created successfully! You can now log in.';
                }
            } catch(PDOException $e) {
                $error = 'An error occurred. Please try again.';
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
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <title>Login / Sign Up | PizzaHut Ghana</title>
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Colors */
            --red-100: #fee2e2;
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-900: #111827;
            
            --white: #ffffff;
            --black: #000000;
            
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
            background-color: var(--white);
            height: 100vh;
            overflow: hidden;
        }

        /* --- 2. Main Layout --- */
        .auth-container {
            display: flex;
            height: 100vh;
        }

        /* Left Side: Hero Image */
        .hero-section {
            display: none;
            width: 50%;
            position: relative;
            background-image: url('images/pepperoni_cheese_pizza-1.jpg');
            background-size: cover;
            background-position: center;
        }
        @media (min-width: 1024px) {
            .hero-section { display: block; }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.6), transparent);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
        }

        .hero-title {
            font-family: var(--font-syne);
            font-size: 3rem;
            font-weight: 900;
            color: var(--white);
            margin-bottom: 1rem;
            filter: drop-shadow(0 1.2px 1.2px rgba(0,0,0,0.8));
            line-height: 1.1;
        }

        .hero-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.125rem;
            max-width: 28rem;
        }

        /* Right Side: Form */
        .form-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1rem 2rem;
        }
        @media (min-width: 768px) { .form-section { padding: 1rem 4rem; } }
        @media (min-width: 1024px) { .form-section { padding: 1rem 6rem; } }

        /* Skip Link */
        .skip-link {
            position: absolute;
            top: 2rem;
            right: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--gray-900);
            transition: color 0.2s;
        }
        .skip-link:hover { color: var(--red-600); }

        /* Logo */
        .logo {
            margin-bottom: 2.5rem;
        }
        .logo-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .logo-icon {
            font-size: 1.875rem;
            transition: transform 0.3s;
        }
        .logo-link:hover .logo-icon { transform: rotate(12deg); }
        .logo-text {
            font-family: var(--font-syne);
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--gray-900);
        }

        /* Form Header */
        .form-header {
            margin-bottom: 2rem;
        }
        .form-title {
            font-family: var(--font-syne);
            font-size: 1.875rem;
            font-weight: 900;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        .form-subtitle {
            color: var(--gray-500);
        }

        /* Form Fields */
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .field-hidden { display: none; }

        .field-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .field-input {
            width: 100%;
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            color: var(--gray-900);
            font-size: 1rem;
        }
        .field-input::placeholder {
            color: var(--gray-400);
        }
        .field-input:focus {
            outline: none;
            background-color: var(--white);
            box-shadow: 0 0 0 2px var(--red-100);
            border-color: var(--red-500);
        }
        .field-input,
        .field-input:focus {
            transition: all 0.2s;
        }

        /* Forgot Password */
        .forgot-password {
            display: flex;
            justify-content: flex-end;
        }
        .forgot-link {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--red-600);
            transition: color 0.2s;
        }
        .forgot-link:hover { color: var(--red-700); }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            background-color: var(--red-600);
            color: var(--white);
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.1);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            transform: translateY(0);
        }
        .submit-btn:hover {
            background-color: var(--red-700);
        }
        .submit-btn:active {
            transform: scale(0.99);
        }

        /* Toggle Link */
        .toggle-section {
            text-align: center;
            color: var(--gray-500);
            margin-top: 2rem;
        }
        .toggle-btn {
            color: var(--red-600);
            font-weight: 700;
            margin-left: 0.25rem;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .toggle-btn:hover {
            text-decoration: underline;
        }

        /* Form Spacing */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <!-- Left Side: Image (Hidden on mobile, visible on lg screens) -->
        <div class="hero-section">
            <div class="hero-overlay">
                <h1 class="hero-title">
                    Hot & Fresh <br>Every Time.
                </h1>
                <p class="hero-subtitle">
                    Join PizzaHut Ghana today and get exclusive deals delivered straight to your door.
                </p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="form-section">
            <!-- Home Link -->
            <a href="index.php" class="skip-link">
                <span>Skip</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>

            <!-- Logo -->
            <div class="logo">
                <a href="index.php" class="logo-link">
                    <span class="logo-icon">🍕</span>
                    <span class="logo-text">PizzaHut</span>
                </a>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2 id="form-title" class="form-title">Welcome Back</h2>
                <p id="form-subtitle" class="form-subtitle">Log in to your account to continue.</p>
            </div>

            <?php if (!empty($error)): ?>
            <div style="padding: 0.75rem 1rem; margin-bottom: 1rem; background-color: #fee2e2; color: #991b1b; border-radius: 0.5rem; font-size: 0.875rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
            <div style="padding: 0.75rem 1rem; margin-bottom: 1rem; background-color: #dcfce7; color: #166534; border-radius: 0.5rem; font-size: 0.875rem;">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <!-- Dynamic Form -->
            <form id="auth-form" class="form-group" method="POST" action="">
                <input type="hidden" name="action" id="form-action" value="login">
                
                <!-- Name Field (Only for Sign Up) -->
                <div id="name-field" class="form-field field-hidden">
                    <label class="field-label">Full Name</label>
                    <input type="text" name="full_name" placeholder="John Doe" class="field-input">
                </div>

                <div class="form-field">
                    <label class="field-label">Email</label>
                    <input type="email" name="email" placeholder="you@example.com" class="field-input" required>
                </div>

                <div class="form-field">
                    <label class="field-label">Password</label>
                    <input type="password" name="password" placeholder="••••••••" class="field-input" required>
                </div>

                <!-- Confirm Password (Only for Sign Up) -->
                <div id="confirm-password-field" class="form-field field-hidden">
                    <label class="field-label">Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="••••••••" class="field-input">
                </div>

                <!-- Forgot Password (Only for Login) -->
                <div id="forgot-password" class="forgot-password">
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button id="submit-btn" type="submit" class="submit-btn">
                    Log In
                </button>
            </form>

            <!-- Toggle Link -->
            <p class="toggle-section">
                <span id="toggle-text">New to PizzaHut?</span> 
                <button onclick="toggleAuthMode()" id="toggle-btn" class="toggle-btn">Create account</button>
            </p>
        </div>
    </div>

    <script>
        let isLogin = true;

        function toggleAuthMode() {
            isLogin = !isLogin;

            // Elements
            const title = document.getElementById('form-title');
            const subtitle = document.getElementById('form-subtitle');
            const submitBtn = document.getElementById('submit-btn');
            const toggleText = document.getElementById('toggle-text');
            const toggleBtn = document.getElementById('toggle-btn');
            const forgotPass = document.getElementById('forgot-password');
            const formAction = document.getElementById('form-action');
            
            // Fields to toggle
            const nameField = document.getElementById('name-field');
            const confirmPassField = document.getElementById('confirm-password-field');

            if (isLogin) {
                // Switch to Login Mode
                title.innerText = "Welcome Back";
                subtitle.innerText = "Log in to your account to continue.";
                submitBtn.innerText = "Log In";
                toggleText.innerText = "New to PizzaHut?";
                toggleBtn.innerText = "Create account";
                formAction.value = "login";
                
                nameField.classList.add('field-hidden');
                confirmPassField.classList.add('field-hidden');
                forgotPass.classList.remove('field-hidden');
            } else {
                // Switch to Sign Up Mode
                title.innerText = "Create Account";
                subtitle.innerText = "Join us for hot pizza and exclusive deals.";
                submitBtn.innerText = "Sign Up";
                toggleText.innerText = "Already have an account?";
                toggleBtn.innerText = "Log in";
                formAction.value = "signup";
                
                nameField.classList.remove('field-hidden');
                confirmPassField.classList.remove('field-hidden');
                forgotPass.classList.add('field-hidden');
            }
        }
    </script>
</body>
</html>
