<?php
session_start();
require_once 'db_connection.php';

$adminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

$order = null;
$error = '';
$searched = false;

if (isset($_GET['order_number'])) {
    $orderNumber = trim($_GET['order_number']);
    $searched = true;
    
    if (!empty($orderNumber)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM customer_orders WHERE order_number = ?");
            $stmt->execute([$orderNumber]);
            $order = $stmt->fetch();
            
            if (!$order) {
                $error = "Order #$orderNumber not found. Please check and try again.";
            }
        } catch (PDOException $e) {
            $error = "System error. Please try again later.";
        }
    } else {
        $error = "Please enter an order number.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - PizzaHut Ghana</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; transition: color 0.2s; }
        ul { list-style: none; }
        button { border: none; cursor: pointer; font-family: inherit; }

        /* --- Navigation --- */
        .navbar {
            position: relative;
            z-index: 20;
            height: 3rem;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            color: var(--red-600);
        }
        .nav-link.active {
            color: var(--red-600);
            font-weight: 700;
        }

        /* --- Main Content --- */
        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .title {
            font-family: var(--font-syne);
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--gray-500);
        }

        .info-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .info-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
        }

        .info-icon {
            font-size: 1.5rem;
        }

        .info-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .info-text {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .track-section {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .btn-track {
            background-color: var(--red-500);
            color: var(--white);
            padding: 0.75rem 3rem;
            font-size: 1.125rem;
            font-weight: 700;
            border-radius: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }
        .btn-track:hover {
            background-color: var(--red-600);
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(239, 68, 68, 0.4);
        }

         /* Profile Dropdown (Copied from other files for consistency) */
        .profile-dropdown { position: relative; display: inline-block; }
        .btn-profile {
            display: flex; align-items: center; gap: 0.5rem;
            background-color: var(--red-500); color: var(--white);
            padding: 0.4rem 1rem; font-size: 0.875rem; font-weight: 600;
            border-radius: 9999px; cursor: pointer;
        }
        .profile-avatar { width: 2rem; height: 2rem; border-radius: 50%; border: 2px solid white; object-fit: cover; }
        .dropdown-content {
            display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem;
            background-color: var(--white); min-width: 160px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem; z-index: 100; overflow: hidden;
        }
        .dropdown-content.show { display: block; }
        .dropdown-item {
            display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem;
            color: var(--gray-700); font-size: 0.875rem; font-weight: 500;
        }
        .dropdown-item:hover { background-color: var(--gray-50); }
        .btn-login {
            background-color: var(--red-500); color: var(--white); padding: 0.5rem 2rem;
            font-size: 0.875rem; font-weight: 600; border-radius: 0.25rem;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="brand">
            <span style="font-size: 1.5rem;">🍕</span>
            <span style="font-family: var(--font-syne);">PizzaHut</span>
        </a>

        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="menu.php" class="nav-link">Menu</a>
            <a href="track_order.php" class="nav-link active">Track Order</a>
            <a href="About.php" class="nav-link">About</a>
            <a href="Contact.php" class="nav-link">Contact</a>
            
            <?php if ($adminLoggedIn): ?>
            <div class="profile-dropdown">
                <button class="btn-profile" onclick="toggleDropdown()">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=fee2e2&color=dc2626&size=64" alt="Profile" class="profile-avatar">
                    <span>Profile</span>
                </button>
                <div class="dropdown-content" id="profileDropdown">
                    <a href="Dashboard.php" class="dropdown-item">Dashboard</a>
                    <a href="logout.php" class="dropdown-item" style="color: var(--red-600);">Logout</a>
                </div>
            </div>
            <?php else: ?>
                <a href="Auth.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="title">Track Your Order</h1>
                <p class="subtitle">Enter your order number to see live status updates</p>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">🛵</div>
                    <div>
                        <div class="info-title">Safety First</div>
                        <div class="info-text">Our riders follow strict hygiene protocols and contactless delivery options are available upon request.</div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">⏱️</div>
                    <div>
                        <div class="info-title">Estimated Time</div>
                        <div class="info-text">Standard delivery time is 30-45 minutes depending on traffic and kitchen volume.</div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">💡</div>
                    <div>
                        <div class="info-title">Need Help?</div>
                        <div class="info-text">If you have issues with your order, please contact our support at orders@pizzahut.com.gh</div>
                    </div>
                </div>
            </div>

            <div class="track-section">
                <form action="track_order.php" method="GET" style="max-width: 400px; margin: 0 auto;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                        <input type="text" name="order_number" placeholder="Enter Order Number (e.g. #ORD-123)" value="<?php echo isset($_GET['order_number']) ? htmlspecialchars($_GET['order_number']) : ''; ?>" 
                            style="flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: 0.5rem; outline: none; font-family: inherit;">
                        <button type="submit" class="btn-track" style="padding: 0.75rem 1.5rem;">Track</button>
                    </div>
                </form>

                <?php if ($searched): ?>
                    <?php if ($error): ?>
                        <div style="background-color: var(--red-500); color: white; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php elseif ($order): ?>
                        <div style="text-align: left; background-color: var(--gray-50); padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem; border: 1px solid var(--gray-200);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                                <h3 style="font-family: var(--font-syne); font-size: 1.25rem;">Order <?php echo htmlspecialchars($order['order_number']); ?></h3>
                                <?php
                                    $statusColor = 'var(--gray-600)';
                                    $statusBg = 'var(--gray-200)';
                                    if ($order['order_status'] == 'pending') { $statusColor = 'var(--red-600)'; $statusBg = '#fee2e2'; }
                                    elseif ($order['order_status'] == 'processing') { $statusColor = '#ea580c'; $statusBg = '#fff7ed'; }
                                    elseif ($order['order_status'] == 'completed') { $statusColor = '#16a34a'; $statusBg = '#f0fdf4'; }
                                ?>
                                <span style="background-color: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 700; font-size: 0.875rem; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </div>
                            
                            <div style="display: grid; gap: 0.5rem; margin-bottom: 1rem;">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Item:</strong> <?php echo htmlspecialchars($order['item_name']); ?></p>
                                <p><strong>Amount:</strong> GH₵ <?php echo number_format($order['item_price'], 2); ?></p>
                                <p><strong>Ordered:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                            </div>

                            <div style="margin-top: 1.5rem;">
                                <div style="height: 4px; background-color: var(--gray-200); border-radius: 2px; position: relative;">
                                    <?php
                                        $width = '5%';
                                        if ($order['order_status'] == 'pending') $width = '25%';
                                        elseif ($order['order_status'] == 'processing') $width = '60%';
                                        elseif ($order['order_status'] == 'completed') $width = '100%';
                                        elseif ($order['order_status'] == 'cancelled') $width = '0%';
                                    ?>
                                    <div style="position: absolute; left: 0; top: 0; height: 100%; width: <?php echo $width; ?>; background-color: var(--red-500); border-radius: 2px; transition: width 0.5s;"></div>
                                </div>
                                <p style="text-align: center; margin-top: 0.5rem; font-size: 0.875rem; color: var(--gray-600);">
                                    <?php 
                                        if ($order['order_status'] == 'pending') echo "We've received your order.";
                                        elseif ($order['order_status'] == 'processing') echo "Our chefs are preparing your delicious pizza!";
                                        elseif ($order['order_status'] == 'completed') echo "Your order has been completed/delivered. Enjoy!";
                                        elseif ($order['order_status'] == 'cancelled') echo "This order was cancelled.";
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById("profileDropdown");
            if(dropdown) dropdown.classList.toggle("show");
        }
        
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
