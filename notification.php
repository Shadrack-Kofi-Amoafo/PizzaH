<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: Auth.php');
    exit;
}

require_once 'db_connection.php';

// Mark message as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = TRUE, read_at = NOW() WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header('Location: notification.php');
    exit;
}

// Delete message if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: notification.php');
    exit;
}

// Fetch all contact messages
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
    
    // Count unread messages
    $stmtCount = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
    $unreadCount = $stmtCount->fetch()['count'];
} catch(PDOException $e) {
    $messages = [];
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | PizzaHut Ghana</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --red-50: #fef2f2;
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
            --font-syne: 'Syne', sans-serif;
            --font-roboto: 'Roboto', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-roboto); background-color: var(--gray-50); color: var(--gray-800); height: 100vh; overflow: hidden; }
        a { text-decoration: none; color: inherit; transition: all 0.2s; }
        button { border: none; cursor: pointer; font-family: inherit; background: none; }

        .app-container { display: flex; height: 100vh; overflow: hidden; }

        .sidebar { display: none; width: 16rem; background-color: var(--white); border-right: 1px solid var(--gray-200); flex-direction: column; justify-content: space-between; }
        @media (min-width: 768px) { .sidebar { display: flex; } }
        .sidebar-content { padding: 1.5rem; height: 100%; display: flex; flex-direction: column; }
        .brand { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2.5rem; }
        .brand-icon { font-size: 1.875rem; }
        .brand-text { font-family: var(--font-syne); font-weight: 700; font-size: 1.5rem; color: var(--gray-900); }

        .nav-menu { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 500; color: var(--gray-600); transition: background-color 0.2s, color 0.2s; }
        .nav-link:hover { background-color: var(--gray-50); color: var(--gray-900); }
        .nav-link.active { background-color: var(--red-50); color: var(--red-600); font-weight: 700; }
        .nav-icon { width: 1.25rem; height: 1.25rem; }

        .notification-badge { background-color: var(--red-600); color: white; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; margin-left: auto; }

        .sidebar-footer { margin-top: auto; }
        .link-logout { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 700; color: var(--red-600); margin-bottom: 1rem; transition: background-color 0.2s; }
        .link-logout:hover { background-color: var(--red-50); }
        .user-profile { padding-top: 1.5rem; border-top: 1px solid var(--gray-100); display: flex; align-items: center; gap: 0.75rem; }
        .avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; }
        .user-info-name { font-size: 0.875rem; font-weight: 700; color: var(--gray-900); }
        .user-info-role { font-size: 0.75rem; color: var(--gray-500); }

        .main-content { flex: 1; overflow-y: auto; background-color: var(--gray-50); padding: 1.5rem; -ms-overflow-style: none; scrollbar-width: none; }
        .main-content::-webkit-scrollbar { display: none; }
        @media (min-width: 768px) { .main-content { padding: 2.5rem; } }

        .page-title { font-family: var(--font-syne); font-size: 1.875rem; font-weight: 900; color: var(--gray-900); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--gray-500); margin-bottom: 2rem; }

        .message-card { background-color: var(--white); border-radius: 1rem; border: 1px solid var(--gray-100); padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s; }
        .message-card:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .message-card.unread { border-left: 4px solid var(--red-600); }
        .message-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; }
        .message-info h3 { font-weight: 700; color: var(--gray-900); margin-bottom: 0.25rem; }
        .message-email { color: var(--blue-600); font-size: 0.875rem; }
        .message-meta { text-align: right; }
        .message-date { font-size: 0.75rem; color: var(--gray-500); }
        .message-subject { display: inline-block; padding: 0.25rem 0.75rem; background-color: var(--blue-50); color: var(--blue-600); font-size: 0.75rem; font-weight: 600; border-radius: 9999px; margin-top: 0.25rem; }
        .message-body { color: var(--gray-600); line-height: 1.6; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-100); }
        .message-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn-action { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s; }
        .btn-mark-read { background-color: var(--green-50); color: var(--green-600); }
        .btn-mark-read:hover { background-color: #dcfce7; }
        .btn-delete { background-color: var(--red-50); color: var(--red-600); }
        .btn-delete:hover { background-color: #fee2e2; }
        
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--gray-500); }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
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
                    <a href="index.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Homepage
                    </a>
                    <a href="Dashboard.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    <a href="menu_admin.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Menu
                    </a>
                    <a href="orders.php" class="nav-link">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Orders
                    </a>
                    <a href="notification.php" class="nav-link active">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Notifications
                        <?php if($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="logout.php" class="link-logout">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
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
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">Messages from customers and visitors</p>
            
            <?php if(empty($messages)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No messages yet</h3>
                <p>When customers send messages through the contact form, they'll appear here.</p>
            </div>
            <?php else: ?>
                <?php foreach($messages as $msg): ?>
                <div class="message-card <?php echo !$msg['is_read'] ? 'unread' : ''; ?>">
                    <div class="message-header">
                        <div class="message-info">
                            <h3><?php echo htmlspecialchars($msg['full_name']); ?></h3>
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="message-email"><?php echo htmlspecialchars($msg['email']); ?></a>
                        </div>
                        <div class="message-meta">
                            <div class="message-date"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></div>
                            <span class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></span>
                        </div>
                    </div>
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <div class="message-actions">
                        <?php if(!$msg['is_read']): ?>
                        <a href="notification.php?mark_read=<?php echo $msg['id']; ?>" class="btn-action btn-mark-read">Mark as Read</a>
                        <?php endif; ?>
                        <a href="notification.php?delete=<?php echo $msg['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
