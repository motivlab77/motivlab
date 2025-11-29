<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>MotivLab Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="MotivLab">
                <span>MotivLab Admin</span>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3>Main</h3>
                    <ul>
                        <li>
                            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3>Content</h3>
                    <ul>
                        <li>
                            <a href="pages/portfolio.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'portfolio') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-briefcase"></i>
                                <span>Portfolio</span>
                            </a>
                        </li>
                        <li>
                            <a href="pages/blog.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'blog') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-blog"></i>
                                <span>Blog Posts</span>
                            </a>
                        </li>
                        <li>
                            <a href="pages/testimonials.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'testimonials') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-star"></i>
                                <span>Testimonials</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3>Communication</h3>
                    <ul>
                        <li>
                            <a href="pages/messages.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'messages') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                                <?php
                                $newMessages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
                                if ($newMessages > 0): ?>
                                <span class="badge"><?php echo $newMessages; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3>Settings</h3>
                    <ul>
                        <li>
                            <a href="pages/settings.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                                <span>Site Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="pages/homepage.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'homepage') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i>
                                <span>Homepage</span>
                            </a>
                        </li>
                        <li>
                            <a href="pages/users.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                </div>

                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
                            <span class="user-role"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?></span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="pages/change-password.php"><i class="fas fa-key"></i> Change Password</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>