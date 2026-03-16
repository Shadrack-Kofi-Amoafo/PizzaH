<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: Auth.php');
    exit;
}

require_once 'db_connection.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $itemId = $_POST['item_id'] ?? '';
        $name = trim($_POST['item_name'] ?? '');
        $description = trim($_POST['item_description'] ?? '');
        $price = floatval($_POST['item_price'] ?? 0);
        $imageUrl = $_POST['existing_image'] ?? 'Images/pepperoni_cheese_pizza-1.jpg';
        
        // Handle file upload
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'Images/';
            $fileName = time() . '_' . basename($_FILES['item_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Check if it's an image
            $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
            
            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $targetPath)) {
                    $imageUrl = $targetPath;
                }
            }
        }
        
        if ($name && $description && $price > 0) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $imageUrl]);
                    $message = 'Menu item added successfully!';
                    $messageType = 'success';
                } else {
                    $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $imageUrl, $itemId]);
                    $message = 'Menu item updated successfully!';
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $itemId = $_POST['item_id'] ?? '';
        if ($itemId) {
            try {
                $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
                $stmt->execute([$itemId]);
                $message = 'Menu item deleted successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Fetch menu items
try {
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY created_at DESC");
    $menuItems = $stmt->fetchAll();
    
    // Unread notifications count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
    $unreadNotifications = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $menuItems = [];
    $unreadNotifications = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Menu Management | PizzaHut Ghana</title>
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Colors */
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
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
            color: var(--gray-800);
            height: 100vh;
            overflow: hidden;
        }

        a { text-decoration: none; color: inherit; transition: all 0.2s; }
        button { border: none; cursor: pointer; font-family: inherit; background: none; }

        /* --- 2. Layout Structure --- */
        .app-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- 3. Sidebar --- */
        .sidebar {
            display: none;
            width: 16rem;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            flex-direction: column;
            justify-content: space-between;
        }

        @media (min-width: 768px) {
            .sidebar { display: flex; }
        }

        .sidebar-content {
            padding: 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2.5rem;
        }
        .brand-icon { font-size: 1.875rem; }
        .brand-text {
            font-family: var(--font-syne);
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--gray-900);
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-weight: 500;
            color: var(--gray-600);
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
        }

        /* Active State */
        .nav-link.active {
            background-color: var(--red-50);
            color: var(--red-600);
            font-weight: 700;
        }

        .nav-icon { width: 1.25rem; height: 1.25rem; }

        .sidebar-footer { margin-top: auto; }

        .link-logout {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            color: var(--red-600);
            margin-bottom: 1rem;
            transition: background-color 0.2s;
        }
        .link-logout:hover { background-color: var(--red-50); }

        .user-profile {
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 2.5rem; height: 2.5rem;
            border-radius: 50%;
        }

        .user-info-name { font-size: 0.875rem; font-weight: 700; color: var(--gray-900); }
        .user-info-role { font-size: 0.75rem; color: var(--gray-500); }

        /* --- 4. Main Content Area --- */
        .main-content {
            flex: 1;
            overflow-y: auto;
            background-color: var(--gray-50);
            padding: 1.5rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .main-content::-webkit-scrollbar { display: none; }

        @media (min-width: 768px) {
            .main-content { padding: 2.5rem; }
        }

        /* Page Header */
        .page-header {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2.5rem;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .page-header { flex-direction: row; align-items: center; }
        }

        .page-title {
            font-family: var(--font-syne);
            font-size: 1.875rem;
            font-weight: 900;
            color: var(--gray-900);
        }
        .page-subtitle { 
            font-size: 0.875rem; 
            color: var(--gray-500); 
            margin-top: 0.25rem; 
        }

        /* Add New Button */
        .btn-add-new {
            background-color: var(--red-600);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-weight: 700;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-add-new:hover { background-color: var(--red-700); }

        /* --- 5. Menu Table --- */
        .table-container {
            background-color: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--gray-100);
            padding: 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .table-wrapper { overflow-x: auto; }

        .data-table {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            font-weight: 600;
            border-bottom: 1px solid var(--gray-100);
            background-color: var(--gray-50);
        }

        .data-table td {
            padding: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
        }

        .data-table tr:hover td { background-color: rgba(249, 250, 251, 0.5); }

        .menu-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .item-name {
            font-weight: 700;
            color: var(--gray-900);
        }

        .price-tag {
            background-color: var(--red-50);
            color: var(--red-600);
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            display: inline-block;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            background-color: var(--gray-100);
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-edit:hover {
            background-color: var(--gray-200);
            color: var(--gray-900);
        }

        .btn-delete {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--red-600);
            background-color: var(--red-50);
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-delete:hover {
            background-color: var(--red-100);
        }

        /* --- 6. Modal Styles --- */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }
        
        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background-color: var(--white);
            border-radius: 1rem;
            width: 90%;
            max-width: 32rem;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background-color: var(--white);
            border-radius: 1rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-family: var(--font-syne);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .modal-close {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            transition: all 0.2s;
        }
        .modal-close:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: var(--font-roboto);
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--red-600);
            box-shadow: 0 0 0 3px var(--red-50);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-100);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-cancel {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            background-color: var(--gray-100);
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background-color: var(--gray-200);
        }

        .btn-save {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--white);
            background-color: var(--red-600);
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-save:hover {
            background-color: var(--red-700);
        }
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
                    
                    <!-- Menu (Active) -->
                    <a href="menu_admin.php" class="nav-link active">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Menu
                    </a>
                    
                    <!-- Orders -->
                    <a href="orders.php" class="nav-link">
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
            <!-- Success/Error Message -->
            <?php if ($message): ?>
            <div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem; <?php echo $messageType === 'success' ? 'background-color: #d1fae5; color: #065f46;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Menu Items</h1>
                    <p class="page-subtitle">Manage all pizzas, prices, and availability.</p>
                </div>
                <button class="btn-add-new" onclick="openAddModal()">
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Item
                </button>
            </div>

            <!-- Menu Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="menuTableBody">
                            <?php if (empty($menuItems)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">No menu items found. Add your first item!</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($menuItems as $item): ?>
                            <tr data-id="<?php echo $item['id']; ?>">
                                <td><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-thumb"></td>
                                <td class="item-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><span class="price-tag">GH₵<?php echo number_format($item['price'], 2); ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-edit" onclick='openEditModal(<?php echo $item["id"]; ?>, <?php echo json_encode($item["name"]); ?>, <?php echo json_encode($item["description"]); ?>, <?php echo $item["price"]; ?>, <?php echo json_encode($item["image_path"]); ?>)'>Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
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

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="itemModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New Item</h3>
                <button class="modal-close" onclick="closeModal()">
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="itemForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="item_id" id="itemId" value="">
                    <input type="hidden" name="existing_image" id="existingImage" value="">
                    
                    <div class="form-group">
                        <label class="form-label" for="itemName">Item Name</label>
                        <input type="text" class="form-input" id="itemName" name="item_name" placeholder="e.g. Pepperoni Classic" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="itemDescription">Description</label>
                        <textarea class="form-input form-textarea" id="itemDescription" name="item_description" placeholder="Describe the pizza..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="itemPrice">Price (GH₵)</label>
                        <input type="number" class="form-input" id="itemPrice" name="item_price" placeholder="45" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="itemImage">Pizza Image</label>
                        <div id="imagePreviewContainer" style="margin-bottom: 0.75rem; display: none;">
                            <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; max-height: 150px; border-radius: 0.5rem; border: 1px solid var(--gray-200);">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <label for="itemImageFile" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background-color: var(--gray-100); border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); transition: all 0.2s;">
                                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Browse Image
                            </label>
                            <input type="file" id="itemImageFile" name="item_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                            <span id="fileName" style="font-size: 0.875rem; color: var(--gray-500);">No file chosen</span>
                        </div>
                        <p style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--gray-500);">Accepts: JPG, PNG, GIF, WebP, AVIF</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-save" onclick="document.getElementById('itemForm').submit()">Save Item</button>
            </div>
        </div>
    </div>

    <script>
        // Use PHP to handle any success messages with an auto-hide
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert-message');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(alert => {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    });
                }, 3000);
            }
        });

        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const fileName = document.getElementById('fileName');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
                fileName.textContent = input.files[0].name;
            }
        }
        
        // Modal Functions
        function openEditModal(id, name, description, price, image) {
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('itemId').value = id;
            document.getElementById('itemName').value = name;
            document.getElementById('itemDescription').value = description;
            document.getElementById('itemPrice').value = price;
            document.getElementById('existingImage').value = image;
            document.getElementById('itemImageFile').value = '';
            document.getElementById('fileName').textContent = 'Keep existing or choose new';
            
            // Show existing image preview
            const preview = document.getElementById('imagePreview');
            const previewContainer = document.getElementById('imagePreviewContainer');
            // Check if image path is valid
            if(image && image.trim() !== '') {
                 preview.src = image;
                 previewContainer.style.display = 'block';
            } else {
                 previewContainer.style.display = 'none';
            }
            
            document.getElementById('itemModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('itemModal').classList.remove('active');
            document.getElementById('itemModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('itemModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('formAction').value = 'add';
            document.getElementById('itemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemDescription').value = '';
            document.getElementById('itemPrice').value = '';
            document.getElementById('existingImage').value = '';
            document.getElementById('itemImageFile').value = '';
            document.getElementById('fileName').textContent = 'No file chosen';
            document.getElementById('imagePreviewContainer').style.display = 'none';
            document.getElementById('itemModal').style.display = 'flex';
        }
    </script>
</body>
</html>