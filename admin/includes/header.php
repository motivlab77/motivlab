<?php
require_once 'auth-check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>MotivLab Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #667eea;
            --primary-dark: #5a6fd8;
            --secondary: #764ba2;
            --dark: #2c3e50;
            --dark-light: #34495e;
            --text: #333;
            --text-light: #7f8c8d;
            --border: #e1e5e9;
            --bg: #f5f6fa;
            --white: #ffffff;
            --success: #27ae60;
            --warning: #e67e22;
            --danger: #e74c3c;
            --info: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            font-size: 14px;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Mobile First Sidebar */
        .sidebar {
            width: 70px;
            background: linear-gradient(180deg, var(--dark) 0%, var(--dark-light) 100%);
            color: var(--white);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar.open {
            width: 250px;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar.open .sidebar-header {
            justify-content: flex-start;
            text-align: left;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 700;
            display: none;
        }
        
        .sidebar.open .sidebar-header h2 {
            display: block;
        }
        
        .sidebar-header p {
            font-size: 11px;
            color: #bdc3c7;
            display: none;
        }
        
        .sidebar.open .sidebar-header p {
            display: block;
        }
        
        .sidebar-header .logo-mini {
            font-size: 24px;
            font-weight: bold;
            color: var(--white);
        }
        
        .sidebar.open .logo-mini {
            display: none;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }
        
        .menu-section {
            padding: 15px 20px 10px;
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: 600;
            letter-spacing: 1px;
            display: none;
        }
        
        .sidebar.open .menu-section {
            display: block;
        }
        
        .menu-item {
            margin: 2px 0;
        }
        
        .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }
        
        .menu-item a:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-left-color: var(--info);
        }
        
        .menu-item.active a {
            background: rgba(52, 152, 219, 0.2);
            color: var(--white);
            border-left-color: var(--info);
        }
        
        .menu-item i {
            width: 20px;
            margin-right: 0;
            font-size: 18px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .sidebar.open .menu-item i {
            margin-right: 10px;
        }
        
        .menu-item span {
            display: none;
            font-size: 14px;
        }
        
        .sidebar.open .menu-item span {
            display: inline;
        }
        
        .badge {
            background: var(--danger);
            color: var(--white);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: auto;
            display: none;
        }
        
        .sidebar.open .badge {
            display: inline-block;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 70px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.open + .main-content {
            margin-left: 250px;
        }
        
        .top-header {
            background: var(--white);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-light);
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar-toggle:hover {
            background: var(--bg);
        }
        
        .top-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--info);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 16px;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: 20px;
        }
        
        /* Mobile Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar.open + .main-content .mobile-overlay {
            display: block;
        }
        
        /* Responsive Breakpoints */
        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
            }
            
            .sidebar-header {
                text-align: left;
                justify-content: flex-start;
                height: auto;
                padding: 25px 20px;
            }
            
            .sidebar-header h2,
            .sidebar-header p {
                display: block;
            }
            
            .logo-mini {
                display: none;
            }
            
            .menu-section {
                display: block;
            }
            
            .menu-item i {
                margin-right: 10px;
            }
            
            .menu-item span {
                display: inline;
            }
            
            .badge {
                display: inline-block;
            }
            
            .main-content {
                margin-left: 250px;
            }
            
            .sidebar-toggle {
                display: none;
            }
        }
        
        /* Content Styles */
        .page-header {
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: var(--success);
            color: var(--white);
        }
        
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Alerts */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success);
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger);
        }
        
        /* Table Styles */
        .table-container {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .data-table th {
            background: var(--bg);
            font-weight: 600;
            color: var(--dark);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: var(--white);
            flex-shrink: 0;
        }
        
        .bg-blue { background: linear-gradient(135deg, var(--info), #2980b9); }
        .bg-purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .bg-orange { background: linear-gradient(135deg, var(--warning), #d35400); }
        .bg-green { background: linear-gradient(135deg, var(--success), #229954); }
        .bg-pink { background: linear-gradient(135deg, #e84393, #fd79a8); }
        
        /* Responsive Tables */
        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo-mini">ML</div>
                <div>
                    <h2>MotivLab</h2>
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="../index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-section">Content Management</li>
                
                <li class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['portfolio.php', 'portfolio-add.php', 'portfolio-edit.php']) ? 'active' : ''; ?>">
                    <a href="portfolio.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Portfolio</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['posts-list.php', 'post-add.php', 'post-edit.php']) ? 'active' : ''; ?>">
                    <a href="posts-list.php">
                        <i class="fas fa-blog"></i>
                        <span>Blog Posts</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['testimonials-list.php', 'testimonials-add.php']) ? 'active' : ''; ?>">
                    <a href="testimonials-list.php">
                        <i class="fas fa-star"></i>
                        <span>Testimonials</span>
                    </a>
                </li>
                
                <li class="menu-section">Communication</li>
                
                <li class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['messages.php', 'messages-view.php']) ? 'active' : ''; ?>">
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <?php
                        try {
                            $count = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
                            if ($count > 0) echo '<span class="badge">' . $count . '</span>';
                        } catch (Exception $e) {
                            // Silently fail
                        }
                        ?>
                    </a>
                </li>
                
                <li class="menu-section">System</li>
                
                <li class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['users-list.php', 'users-add.php', 'users-edit.php']) ? 'active' : ''; ?>">
                    <a href="users-list.php">
                        <i class="fas fa-users"></i>
                        <span>Admin Users</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">
                    <a href="media.php">
                        <i class="fas fa-images"></i>
                        <span>Media Library</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Mobile Overlay -->
            <div class="mobile-overlay" id="mobileOverlay"></div>
            
            <header class="top-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                </div>
                
                <div class="header-right">
                    <div class="user-menu">
                        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
