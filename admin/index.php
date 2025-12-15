<?php
require_once 'config.php';
// redirectIfNotLoggedIn() is already called in config.php

$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - MotivLab Admin</title>
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
        
        /* Stats Grid - Mobile First */
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
        
        .stat-details {
            flex: 1;
            min-width: 0;
        }
        
        .stat-details h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .stat-details p {
            color: var(--text-light);
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .stat-link {
            color: var(--info);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stat-link:hover {
            text-decoration: underline;
        }
        
        /* Content Sections */
        .content-section {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-header h2 {
            font-size: 18px;
            color: var(--dark);
        }
        
        .btn-small {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-small:hover {
            background: var(--primary-dark);
        }
        
        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        
        .action-btn {
            background: var(--bg);
            border: 2px dashed var(--border);
            padding: 15px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            text-align: center;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background: #e9ecef;
            border-color: var(--info);
            color: var(--info);
            transform: translateY(-2px);
        }
        
        .action-btn i {
            font-size: 20px;
        }
        
        .action-btn span {
            font-size: 12px;
            font-weight: 500;
            line-height: 1.2;
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
        @media (min-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
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
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
            
            .action-btn {
                padding: 20px 15px;
            }
            
            .action-btn span {
                font-size: 13px;
            }
        }
        
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(5, 1fr);
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(6, 1fr);
            }
            
            .dashboard-content {
                padding: 30px;
            }
            
            .content-section {
                padding: 25px;
            }
        }
        
        @media (min-width: 1200px) {
            .stat-card {
                padding: 25px;
            }
            
            .stat-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .stat-details h3 {
                font-size: 28px;
            }
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-10 { gap: 10px; }
        .gap-15 { gap: 15px; }
        .mb-15 { margin-bottom: 15px; }
        .mb-20 { margin-bottom: 20px; }
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
                <li class="menu-item active">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-section">Content Management</li>
                
                <li class="menu-item">
                    <a href="pages/portfolio.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Portfolio</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="pages/posts-list.php">
                        <i class="fas fa-blog"></i>
                        <span>Blog Posts</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="pages/testimonials-list.php">
                        <i class="fas fa-star"></i>
                        <span>Testimonials</span>
                    </a>
                </li>
                
                <li class="menu-section">Communication</li>
                
                <li class="menu-item">
                    <a href="pages/messages.php">
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
                
                <li class="menu-item">
                    <a href="pages/users-list.php">
                        <i class="fas fa-users"></i>
                        <span>Admin Users</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="pages/media.php">
                        <i class="fas fa-images"></i>
                        <span>Media Library</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="pages/settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="logout.php">
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
                    <h1><?php echo $page_title; ?></h1>
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
                <div class="page-header">
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>! Here's what's happening with your website.</p>
                </div>

                <?php showMessages(); ?>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <?php
                    // Fetch counts with error handling
                    $counts = [
                        'portfolio' => 0,
                        'posts' => 0,
                        'messages' => 0,
                        'users' => 0,
                        'testimonials' => 0
                    ];
                    
                    try {
                        $counts['portfolio'] = $conn->query("SELECT COUNT(*) FROM portfolio_items")->fetchColumn();
                        $counts['posts'] = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
                        $counts['messages'] = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
                        $counts['users'] = $conn->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
                        $counts['testimonials'] = $conn->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn();
                    } catch (Exception $e) {
                        // Silently fail - tables might not exist yet
                    }
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-icon bg-blue">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $counts['portfolio']; ?></h3>
                            <p>Portfolio Items</p>
                            <a href="pages/portfolio.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-blog"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $counts['posts']; ?></h3>
                            <p>Blog Posts</p>
                            <a href="pages/posts-list.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-orange">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $counts['messages']; ?></h3>
                            <p>New Messages</p>
                            <a href="pages/messages.php" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-green">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $counts['users']; ?></h3>
                            <p>Admin Users</p>
                            <a href="pages/users-list.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-pink">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $counts['testimonials']; ?></h3>
                            <p>Pending Reviews</p>
                            <a href="pages/testimonials-list.php" class="stat-link">Review <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="pages/portfolio-add.php" class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Portfolio</span>
                        </a>
                        <a href="pages/post-add.php" class="action-btn">
                            <i class="fas fa-pen"></i>
                            <span>Write Blog</span>
                        </a>
                        <a href="pages/testimonials-add.php" class="action-btn">
                            <i class="fas fa-star"></i>
                            <span>Add Testimonial</span>
                        </a>
                        <a href="pages/users-add.php" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        <a href="pages/media.php" class="action-btn">
                            <i class="fas fa-images"></i>
                            <span>Upload Media</span>
                        </a>
                        <a href="pages/settings.php" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const welcomeText = document.querySelector('.welcome-text');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            document.body.style.overflow = '';
        }
        
        // Toggle sidebar
        sidebarToggle.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking overlay
        mobileOverlay.addEventListener('click', closeSidebar);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                closeSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.add('open');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.remove('open');
            }
            
            // Hide welcome text on very small screens
            if (window.innerWidth < 400) {
                welcomeText.style.display = 'none';
            } else {
                welcomeText.style.display = 'block';
            }
        });
        
        // Initialize on load
        window.addEventListener('load', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.add('open');
            }
            
            if (window.innerWidth < 400) {
                welcomeText.style.display = 'none';
            }
        });
    </script>
</body>
</html>
