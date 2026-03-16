<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: Auth.php');
    exit;
}

require_once 'db_connection.php';

// Handle order status update
if (isset($_GET['update_status']) && isset($_GET['order_id'])) {
    $newStatus = $_GET['update_status'];
    $orderId = $_GET['order_id'];
    
    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE customer_orders SET order_status = ?, completed_at = ? WHERE id = ?");
        $completedAt = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;
        $stmt->execute([$newStatus, $completedAt, $orderId]);
    }
    header('Location: orders.php');
    exit;
}

// Handle order deletion
if (isset($_GET['delete_order']) && isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $stmt = $pdo->prepare("DELETE FROM customer_orders WHERE id = ?");
    $stmt->execute([$orderId]);
    header('Location: orders.php');
    exit;
}

// Fetch orders statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'pending'");
    $pendingCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'processing'");
    $processingCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'completed' AND DATE(completed_at) = CURDATE()");
    $completedTodayCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'cancelled'");
    $cancelledCount = $stmt->fetch()['count'];
    
    // Fetch all orders
    $stmt = $pdo->query("SELECT * FROM customer_orders ORDER BY order_date DESC");
    $orders = $stmt->fetchAll();
    
    // Unread notifications count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
    $unreadNotifications = $stmt->fetch()['count'];
    
} catch(PDOException $e) {
    $pendingCount = 0;
    $processingCount = 0;
    $completedTodayCount = 0;
    $cancelledCount = 0;
    $orders = [];
    $unreadNotifications = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | PizzaHut Ghana</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* [Same CSS as dashboard - copied exactly for consistency] */
        :root {
            /* Palette */
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-600: #dc2626;
            --red-700: #b91c1c;

            --green-50: #f0fdf4;
            --green-600: #16a34a;

            --blue-50: #eff6ff;
            --blue-600: #2563eb;

            --orange-50: #fff7ed;
            --orange-600: #ea580c;

            --gray-50: #f9fafb;
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-roboto);
            background-color: var(--gray-50);
            color: var(--gray-800);
            height: 100vh;
            overflow: hidden;
        }
        a { text-decoration: none; color: inherit; transition: all 0.2s; }
        button { border: none; cursor: pointer; font-family: inherit; background: none; }
        ul { list-style: none; }

        /* Layout */
        .app-container { display: flex; height: 100vh; overflow: hidden; }
        .sidebar {
            display: none; width: 16rem; background-color: var(--white);
            border-right: 1px solid var(--gray-200); flex-direction: column;
            justify-content: space-between;
        }
        @media (min-width: 768px) { .sidebar { display: flex; } }
        .sidebar-content { padding: 1.5rem; height: 100%; display: flex; flex-direction: column; }
        .brand { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2.5rem; }
        .brand-icon { font-size: 1.875rem; }
        .brand-text { font-family: var(--font-syne); font-weight: 700; font-size: 1.5rem; color: var(--gray-900); }
        .nav-menu { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-link {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
            border-radius: 0.75rem; font-weight: 500; color: var(--gray-600);
            transition: background-color 0.2s, color 0.2s;
        }
        .nav-link:hover { background-color: var(--gray-50); color: var(--gray-900); }
        .nav-link.active { background-color: var(--red-50); color: var(--red-600); font-weight: 700; }
        .nav-icon { width: 1.25rem; height: 1.25rem; }
        .sidebar-footer { margin-top: auto; }
        .link-logout {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
            border-radius: 0.75rem; font-weight: 700; color: var(--red-600);
            margin-bottom: 1rem; transition: background-color 0.2s;
        }
        .link-logout:hover { background-color: var(--red-50); }
        .user-profile {
            padding-top: 1.5rem; border-top: 1px solid var(--gray-100);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; }
        .user-info-name { font-size: 0.875rem; font-weight: 700; color: var(--gray-900); }
        .user-info-role { font-size: 0.75rem; color: var(--gray-500); }

        /* Main Content */
        .main-content {
            flex: 1; overflow-y: auto; background-color: var(--gray-50); padding: 1.5rem;
            -ms-overflow-style: none; scrollbar-width: none;
        }
        .main-content::-webkit-scrollbar { display: none; }
        @media (min-width: 768px) { .main-content { padding: 2.5rem; } }

        /* Header */
        .header-mobile { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        @media (min-width: 768px) { .header-mobile { display: none; } }
        .hamburger-btn { color: var(--gray-600); }
        .hamburger-icon { width: 1.5rem; height: 1.5rem; }
        .page-header {
            display: flex; flex-direction: column; justify-content: space-between;
            align-items: flex-start; margin-bottom: 2.5rem; gap: 1rem;
        }
        @media (min-width: 768px) { .page-header { flex-direction: row; align-items: center; } }
        .page-title { font-family: var(--font-syne); font-size: 1.875rem; font-weight: 900; color: var(--gray-900); }
        .page-subtitle { font-size: 0.875rem; color: var(--gray-500); margin-top: 0.25rem; }
        .search-container { position: relative; flex-shrink: 0; }
        .search-input {
            padding: 0.625rem 1rem 0.625rem 2.75rem; background-color: var(--white);
            border: 1px solid var(--gray-200); border-radius: 0.75rem; font-size: 0.875rem;
            outline: none; width: 18rem; transition: all 0.2s;
        }
        .search-input:focus { box-shadow: 0 0 0 2px var(--red-50); }
        .search-icon {
            position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
            width: 1rem; height: 1rem; color: var(--gray-400);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2.5rem;
        }
        @media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
        .stat-card {
            background-color: var(--white); padding: 1.5rem; border-radius: 1rem;
            border: 1px solid var(--gray-100); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .icon-box {
            padding: 0.75rem; border-radius: 0.75rem; display: flex;
            align-items: center; justify-content: center;
        }
        .icon-box svg { width: 1.5rem; height: 1.5rem; }
        .bg-red { background-color: var(--red-50); color: var(--red-600); }
        .bg-blue { background-color: var(--blue-50); color: var(--blue-600); }
        .bg-orange { background-color: var(--orange-50); color: var(--orange-600); }
        .bg-green { background-color: var(--green-50); color: var(--green-600); }
        .growth-badge {
            font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 0.25rem;
        }
        .growth-up { background-color: var(--green-50); color: var(--green-600); }
        .growth-down { background-color: var(--red-50); color: var(--red-600); }
        .stat-label { color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.025em; }
        .stat-value { font-family: var(--font-syne); font-size: 1.5rem; font-weight: 900; color: var(--gray-900); margin-top: 0.25rem; }

        /* Orders Table */
        .table-container {
            background-color: var(--white); border-radius: 1rem; border: 1px solid var(--gray-100);
            padding: 1.5rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .table-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .section-heading { font-family: var(--font-syne); font-size: 1.25rem; font-weight: 700; color: var(--gray-900); }
        .table-wrapper { overflow-x: auto; }
        .data-table { width: 100%; text-align: left; border-collapse: collapse; }
        .data-table th {
            padding-bottom: 0.75rem; font-size: 0.75rem; text-transform: uppercase;
            letter-spacing: 0.05em; color: var(--gray-400); font-weight: 600;
            border-bottom: 1px solid var(--gray-100);
        }
        .data-table td {
            padding: 1rem 0; font-size: 0.875rem; font-weight: 500; color: var(--gray-600);
            border-bottom: 1px solid var(--gray-50);
        }
        .data-table tr:hover td { background-color: rgba(249, 250, 251, 0.5); }
        .data-table td.text-dark { color: var(--gray-900); font-weight: 600; }
        .status-pill {
            display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px;
            font-size: 0.75rem; font-weight: 700;
        }
        .status-cooking { background-color: var(--orange-50); color: var(--orange-600); }
        .status-delivered { background-color: var(--green-50); color: var(--green-600); }
        .status-pending { background-color: var(--red-50); color: var(--red-600); }
        .status-out-for-delivery { background-color: var(--blue-50); color: var(--blue-600); }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-content">
                <a href="index.php" class="brand">
                    <span class="brand-icon">🍕</span>
                    <span class="brand-text">PizzaHut</span>
                </a>
                
                <nav class="nav-menu">
                    <!-- Homepage -->
                    <a href="index.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Homepage
                    </a>
                    
                    <!-- Dashboard -->
                    <a href="dashboard.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <!-- Menu -->
                    <a href="menu_admin.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Menu
                    </a>
                    
                    <!-- Orders (Active) -->
                    <a href="orders.php" class="nav-link active">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Orders
                    </a>
                    
                    <!-- Notifications -->
                    <a href="notification.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notifications
                        <?php if ($unreadNotifications > 0): ?>
                        <span style="background-color: var(--red-600); color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 9999px; margin-left: auto;"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="logout.php" class="link-logout">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=fee2e2&color=dc2626" alt="Profile" class="avatar">
                        <div>
                            <p class="user-info-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                            <p class="user-info-role">Manager</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Mobile Header -->
            <div class="header-mobile">
                <span class="brand-text" style="font-size: 1.25rem;">PizzaHut</span>
                <button class="hamburger-btn">
                    <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Orders</h1>
                    <p class="page-subtitle">Manage all customer orders and delivery status.</p>
                </div>
                <div class="search-container">
                    <input type="text" placeholder="Search orders..." class="search-input">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Order Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="icon-box bg-orange">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="stat-label">Processing</p>
                    <h3 class="stat-value"><?php echo $processingCount; ?></h3>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="icon-box bg-blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-3.01V9a4 4 0 11-8 0v-3"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="stat-label">Pending</p>
                    <h3 class="stat-value"><?php echo $pendingCount; ?></h3>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="icon-box bg-red">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="stat-label">Cancelled</p>
                    <h3 class="stat-value"><?php echo $cancelledCount; ?></h3>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="icon-box bg-green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="stat-label">Completed Today</p>
                    <h3 class="stat-value"><?php echo $completedTodayCount; ?></h3>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-container">
                <div class="table-header-row">
                    <h2 class="section-heading">All Orders</h2>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">No orders found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <?php
                                // Determine status class
                                $statusClass = 'status-pending';
                                if ($order['order_status'] === 'processing') $statusClass = 'status-cooking';
                                elseif ($order['order_status'] === 'completed') $statusClass = 'status-delivered';
                                elseif ($order['order_status'] === 'cancelled') $statusClass = 'status-pending';
                                
                                // Format time ago
                                $orderTime = strtotime($order['order_date']);
                                $now = time();
                                $diff = $now - $orderTime;
                                if ($diff < 60) $timeAgo = 'Just now';
                                elseif ($diff < 3600) $timeAgo = floor($diff / 60) . 'm ago';
                                elseif ($diff < 86400) $timeAgo = floor($diff / 3600) . 'h ago';
                                else $timeAgo = floor($diff / 86400) . 'd ago';
                            ?>
                            <tr>
                                <td class="text-dark"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td class="text-dark"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                                <td class="text-dark">GH₵ <?php echo number_format($order['item_price'], 2); ?></td>
                                <td><span class="status-pill <?php echo $statusClass; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                <td><?php echo $timeAgo; ?></td>
                                <td>
                                    <select onchange="updateStatus(<?php echo $order['id']; ?>, this.value)" style="padding: 0.25rem 0.5rem; border-radius: 0.5rem; border: 1px solid var(--gray-200); font-size: 0.75rem;">
                                        <option value="">Change</option>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <a href="orders.php?delete_order=true&order_id=<?php echo $order['id']; ?>" onclick="return confirm('Delete this order?');" style="display: inline-flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: 50%; color: var(--red-600); background-color: var(--red-50); margin-left: 0.5rem; transition: background-color 0.2s;">
                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function updateStatus(orderId, status) {
            if (status) {
                window.location.href = 'orders.php?update_status=' + status + '&order_id=' + orderId;
            }
        }
    </script>
</body>
</html>
