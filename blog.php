<?php
require_once 'config.php';

// Get blog posts
try {
    $blog_posts = $conn->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
    $settings = [];
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    $result = $stmt->fetchAll();
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $blog_posts = [];
    $settings = ['site_name' => 'MotivLab'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - <?php echo htmlspecialchars($settings['site_name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <!-- Your navigation -->
    </nav>

    <section class="blog-header">
        <div class="container">
            <h1>Our Blog</h1>
            <p>Latest insights and updates</p>
        </div>
    </section>

    <section class="blog-posts">
        <div class="container">
            <div class="posts-grid">
                <?php if (!empty($blog_posts)): ?>
                    <?php foreach ($blog_posts as $post): ?>
                    <article class="blog-post">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                        <div class="post-content">
                            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <div class="post-meta">
                                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <a href="blog-single.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No blog posts yet. Check back soon!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
</body>
</html>
