<?php
// =========================================================================
// MotivLab Admin Dashboard (index.php)
// =========================================================================

// 1. Configuration & Security Check
// The '../' is necessary because the config.php is one level up from this file.
require_once '../config.php'; 
redirectIfNotLoggedIn(); // Ensures only logged-in users can access the dashboard

// The user is logged in. Now, fetch data for the dashboard statistics.

// 2. Data Fetching
try {
    // --- A. Portfolio Statistics ---
    $total_portfolio = $conn->query("SELECT COUNT(*) FROM portfolio_items")->fetchColumn();
    $published_portfolio = $conn->query("SELECT COUNT(*) FROM portfolio_items WHERE status = 'published'")->fetchColumn();

    // --- B. Blog Statistics ---
    $total_posts = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    $published_posts = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();
    $total_views = $conn->query("SELECT SUM(views) FROM blog_posts")->fetchColumn();
    $total_views = $total_views ? $total_views : 0; // Handle NULL if no posts exist

    // --- C. Testimonial Statistics ---
    $total_testimonials = $conn->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
    $active_testimonials = $conn->query("SELECT COUNT(*) FROM testimonials WHERE status = 'active'")->fetchColumn();
    
    // --- D. Message Statistics ---
    $total_messages = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $new_messages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    
    // --- E. User Statistics ---
    $total_users = $conn->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    $active_users = $conn->query("SELECT COUNT(*) FROM admin_users WHERE status = 'active'")->fetchColumn();

    // --- F. Recent Activity (Example: Last 5 published posts) ---
    $stmt_recent_posts = $conn->prepare("
        SELECT id, title, created_at 
        FROM blog_posts 
        WHERE status = 'published' 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt_recent_posts->execute();
    $recent_posts = $stmt_recent_posts->fetchAll();

} catch (PDOException $e) {
    // Set an error message if the query fails (e.g., table missing)
    set_message('error', 'Database Error: Could not load dashboard statistics. ' . $e->getMessage());
    // Fallback to zero values
    $total_portfolio = $published_portfolio = $total_posts = $published_posts = 0;
    $total_views = $total_testimonials = $active_testimonials = $total_messages = $new_messages = 0;
    $total_users = $active_users = 0;
    $recent_posts = [];
}

// 3. HTML Output
$page_title = "Dashboard";
include 'includes/header.php'; 
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> MotivLab Dashboard</h1>
        <p>Welcome back, **<?php echo sanitizeInput($_SESSION['full_name'] ?? 'Admin'); ?>**! Here is an overview of your website.</p>
    </div>

    <?php display_message(); ?>

    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <i class="fas fa-briefcase icon"></i>
            <div class="info">
                <h3><?php echo number_format($total_portfolio); ?></h3>
                <p>Total Portfolio Items</p>
                <span><?php echo number_format($published_portfolio); ?> Published</span>
            </div>
        </div>

        <div class="stat-card stat-info">
            <i class="fas fa-file-alt icon"></i>
            <div class="info">
                <h3><?php echo number_format($total_posts); ?></h3>
                <p>Total Blog Posts</p>
                <span><?php echo number_format($published_posts); ?> Live</span>
            </div>
        </div>
        
        <div class="stat-card stat-secondary">
            <i class="fas fa-chart-line icon"></i>
            <div class="info">
                <h3><?php echo number_format($total_views); ?></h3>
                <p>Total Blog Views</p>
                <span>Avg. views per post: <?php echo $total_posts > 0 ? number_format($total_views / $total_posts, 0) : 0; ?></span>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <i class="fas fa-envelope icon"></i>
            <div class="info">
                <h3><?php echo number_format($total_messages); ?></h3>
                <p>Total Messages</p>
                <span>**<?php echo number_format($new_messages); ?> New**</span>
            </div>
        </div>
    </div>
    
    <div class="widget-row">
        
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fas fa-history"></i> Recent Blog Activity</h2>
                <a href="blog-list.php" class="widget-link">View All</a>
            </div>
            <ul class="activity-list">
                <?php if (count($recent_posts) > 0): ?>
                    <?php foreach ($recent_posts as $post): ?>
                        <li>
                            <i class="fas fa-check-circle success"></i>
                            <a href="blog-edit.php?id=<?php echo $post['id']; ?>">**<?php echo sanitizeInput($post['title']); ?>**</a>
                            <span class="date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="empty-state">No published posts yet.</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="widget system-status-widget">
            <div class="widget-header">
                <h2><i class="fas fa-info-circle"></i> System Status</h2>
                <span class="status-badge status-<?php echo ($total_users > 0 && $total_posts > 0 && $total_portfolio > 0) ? 'success' : 'warning'; ?>">
                    <?php echo ($total_users > 0 && $total_posts > 0 && $total_portfolio > 0) ? 'Operational' : 'Setup Pending'; ?>
                </span>
            </div>
            <ul class="status-list">
                <li><i class="fas fa-database"></i> Database Connection: <span class="status-<?php echo (isset($conn) && $conn) ? 'success' : 'error'; ?>"><?php echo (isset($conn) && $conn) ? 'Connected' : 'Failed'; ?></span></li>
                <li><i class="fas fa-users"></i> Admin Users: <span><?php echo number_format($total_users); ?> (<?php echo number_format($active_users); ?> Active)</span></li>
                <li><i class="fas fa-comments"></i> Active Testimonials: <span><?php echo number_format($active_testimonials); ?> of <?php echo number_format($total_testimonials); ?></span></li>
                <?php 
                // Simple calculation for storage based on media_files table (requires separate query in real-world for accuracy)
                // For now, we'll use a placeholder/estimate unless the user specifically asks for the query.
                $storage_used = 'N/A';
                ?>
                <li><i class="fas fa-hdd"></i> Media Storage: <span><?php echo $storage_used; ?></span></li>
                <li><i class="fas fa-lock"></i> Default Password: <span class="status-error">**CHANGE REQUIRED**</span></li>
            </ul>
        </div>
        
    </div>
</div>

<?php 
// 6. Footer
include 'includes/footer.php'; 
?>