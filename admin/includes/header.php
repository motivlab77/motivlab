<?php
// =========================================================================
// MotivLab Admin Panel Header (admin/includes/header.php)
// Includes HTML start, CSS links, Top Bar, and Sidebar Navigation.
// =========================================================================

// Ensure configuration is loaded and the user is logged in
if (!defined('DB_HOST')) {
    require_once '../config.php';
    redirectIfNotLoggedIn();
}

// Get current page for active highlighting in the sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Determine the path to assets. If running from /admin/pages, it needs another ../
$asset_path = (strpos($_SERVER['PHP_SELF'], '/admin/pages/') !== false) ? '../../admin/assets/' : '../admin/assets/';

// Get current user data from session
$user_full_name = sanitizeInput($_SESSION['full_name'] ?? 'MotivLab Admin');
$user_role = sanitizeInput($_SESSION['user_role'] ?? 'Editor');
$user_avatar = $asset_path . 'images/default-avatar.png'; // Placeholder avatar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeInput($page_title ?? 'Dashboard'); ?> - MotivLab Admin</title>
    
    <link rel="stylesheet" href="<?php echo $asset_path; ?>css/admin.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="icon" type="image/x-icon" href="<?php echo $asset_path; ?>images/favicon.ico"> 

</head>
<body>

<div class="app-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <img src="<?php echo $asset_path; ?>images/logo.png" alt="MotivLab Logo" class="logo-img">
                <span class="logo-text">MotivLab CMS</span>
            </div>
            <button class="toggle-btn" id="sidebar-toggle-btn"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                
                <?php if (hasRole('admin') || hasRole('super_admin')): ?>
                <li class="nav-section-title">PORTFOLIO</li>
                <li class="nav-item <?php echo (strpos($current_page, 'portfolio-') !== false) ? 'active' : ''; ?>">
                    <a href="pages/portfolio-list.php"><i class="fas fa-briefcase"></i> All Portfolio</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'portfolio-add.php') ? 'active' : ''; ?>">
                    <a href="pages/portfolio-add.php"><i class="fas fa-plus-circle"></i> Add New</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
                    <a href="pages/categories.php"><i class="fas fa-tags"></i> Categories</a>
                </li>
                <?php endif; ?>

                <li class="nav-section-title">BLOG</li>
                <li class="nav-item <?php echo (strpos($current_page, 'blog-') !== false) ? 'active' : ''; ?>">
                    <a href="pages/blog-list.php"><i class="fas fa-file-alt"></i> All Posts</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'blog-add.php') ? 'active' : ''; ?>">
                    <a href="pages/blog-add.php"><i class="fas fa-pen-nib"></i> New Post</a>
                </li>

                <li class="nav-section-title">CLIENTS & MESSAGES</li>
                <li class="nav-item <?php echo (strpos($current_page, 'testimonials-') !== false) ? 'active' : ''; ?>">
                    <a href="pages/testimonials-list.php"><i class="fas fa-star"></i> Testimonials</a>
                </li>
                <li class="nav-item <?php echo (strpos($current_page, 'message') !== false) ? 'active' : ''; ?>">
                    <a href="pages/messages.php"><i class="fas fa-envelope"></i> Contact Messages</a>
                </li>
            
                <?php if (hasRole('admin') || hasRole('super_admin')): ?>
                <li class="nav-section-title">CONTENT & MEDIA</li>
                <li class="nav-item <?php echo ($current_page == 'media.php') ? 'active' : ''; ?>">
                    <a href="pages/media.php"><i class="fas fa-images"></i> Media Manager</a>
                </li>
                <li class="nav-item nav-dropdown">
                    <a href="#" class="dropdown-toggle"><i class="fas fa-home"></i> Homepage <i class="fas fa-chevron-down dropdown-icon"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="pages/homepage-hero.php">Hero & CTA</a></li>
                        <li><a href="pages/homepage-media.php">Images & Logo</a></li>
                        <li><a href="pages/homepage-features.php">Features</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole('super_admin')): ?>
                <li class="nav-section-title">SETTINGS & ACCESS</li>
                <li class="nav-item <?php echo (strpos($current_page, 'settings.php') !== false) ? 'active' : ''; ?>">
                    <a href="pages/settings.php"><i class="fas fa-cogs"></i> Site Settings</a>
                </li>
                <li class="nav-item <?php echo (strpos($current_page, 'users.php') !== false) ? 'active' : ''; ?>">
                    <a href="pages/users.php"><i class="fas fa-users-cog"></i> Admin Users</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <header class="topbar" id="topbar">
        <button class="toggle-btn" id="topbar-toggle-btn"><i class="fas fa-bars"></i></button>

        <div class="topbar-right">
            <a href="https://motivlab.name.ng" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt"></i> View Site</a>
            
            <div class="user-dropdown-container">
                <div class="user-profile-summary">
                    <img src="<?php echo $user_avatar; ?>" alt="User Avatar" class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo $user_full_name; ?></span>
                        <span class="user-role badge badge-<?php echo str_replace('_', '-', $user_role); ?>"><?php echo ucwords(str_replace('_', ' ', $user_role)); ?></span>
                    </div>
                </div>
                
                <div class="user-dropdown-menu">
                    <a href="pages/profile.php"><i class="fas fa-user-circle"></i> Profile</a>
                    <a href="pages/settings.php"><i class="fas fa-sliders-h"></i> Settings</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="content-area">
