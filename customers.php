<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Customers | PizzaHut Ghana</title>
    
    <style>
        /* --- 1. Variables & Reset --- */
        :root {
            /* Colors */
            --red-50: #fef2f2;
            --red-600: #dc2626;
            
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

        /* Search Bar */
        .search-container { position: relative; flex-shrink: 0; }
        .search-input {
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            outline: none;
            width: 18rem;
            transition: all 0.2s;
        }
        .search-input:focus {
            box-shadow: 0 0 0 2px var(--red-50);
        }
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            color: var(--gray-400);
        }

        /* --- 5. Customers Table --- */
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
            padding-bottom: 0.75rem;
            padding-left: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-400);
            font-weight: 600;
            border-bottom: 1px solid var(--gray-100);
        }

        .data-table td {
            padding: 1rem 0 1rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            border-bottom: 1px solid var(--gray-50);
        }

        .data-table tbody tr:hover td { 
            background-color: rgba(249, 250, 251, 0.5); 
        }

        .data-table td.name { 
            font-weight: 700; 
            color: var(--gray-900); 
        }

        .action-btn {
            color: var(--gray-400);
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: color 0.2s;
        }
        .action-btn:hover {
            color: var(--red-600);
            background-color: var(--red-50);
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
                    
                    <!-- Customers (Active) -->
                    <a href="customers.php" class="nav-link active">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Customers
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="Auth.php" class="link-logout">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                    
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&background=fee2e2&color=dc2626" alt="Profile" class="avatar">
                        <div>
                            <p class="user-info-name">Pizza Admin</p>
                            <p class="user-info-role">Manager</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Customers</h1>
                    <p class="page-subtitle">Manage user accounts and details.</p>
                </div>
                <div class="search-container">
                    <input type="text" placeholder="Search customer..." class="search-input">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Total Orders</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="name">Kofi Mensah</td>
                                <td>kofi@example.com</td>
                                <td>12</td>
                                <td>2 hours ago</td>
                                <td><button class="action-btn">Details</button></td>
                            </tr>
                            <tr>
                                <td class="name">Ama Osei</td>
                                <td>ama@example.com</td>
                                <td>5</td>
                                <td>Yesterday</td>
                                <td><button class="action-btn">Details</button></td>
                            </tr>
                            <tr>
                                <td class="name">Yaw Boateng</td>
                                <td>yaw@example.com</td>
                                <td>28</td>
                                <td>3 days ago</td>
                                <td><button class="action-btn">Details</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
