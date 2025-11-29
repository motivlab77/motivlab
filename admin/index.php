<?php
require_once 'config.php';
redirectIfNotLoggedIn();

// Get statistics
$totalPortfolio = $conn->query("SELECT COUNT(*) FROM portfolio_items")->fetchColumn();
$totalBlogs = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$totalMessages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$totalTestimonials = $conn->query("SELECT COUNT(*) FROM testimonials WHERE status = 'active'")->fetchColumn();

// Get recent messages
$recentMessages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo $_SESSION['admin_name']; ?>!</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #667eea;">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalPortfolio; ?></h3>
                <p>Portfolio Items</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #f68b1e;">
                <i class="fas fa-blog"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalBlogs; ?></h3>
                <p>Blog Posts</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalMessages; ?></h3>
                <p>New Messages</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $totalTestimonials; ?></h3>
                <p>Testimonials</p>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="content-section">
        <div class="section-header">
            <h2>Recent Contact Messages</h2>
            <a href="pages/messages.php" class="btn-secondary">View All</a>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Website Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentMessages)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px;">No messages yet</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentMessages as $message): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                            <td><?php echo htmlspecialchars($message['website_type']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $message['status'] === 'new' ? 'primary' : 'success'; ?>">
                                    <?php echo ucfirst($message['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="pages/message-view.php?id=<?php echo $message['id']; ?>" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-grid">
            <a href="pages/portfolio-add.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <span>Add Portfolio Item</span>
            </a>
            <a href="pages/blog-add.php" class="action-card">
                <i class="fas fa-pen"></i>
                <span>Write Blog Post</span>
            </a>
            <a href="pages/testimonials-add.php" class="action-card">
                <i class="fas fa-star"></i>
                <span>Add Testimonial</span>
            </a>
            <a href="pages/settings.php" class="action-card">
                <i class="fas fa-cog"></i>
                <span>Site Settings</span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>