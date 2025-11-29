<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Blog Management";
include '../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        // Get blog post to check for featured image
        $stmt = $conn->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete blog post
        $deleteStmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        // Delete featured image if exists
        if ($post && $post['featured_image']) {
            $file_path = "../assets/images/uploads/" . $post['featured_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $_SESSION['success_message'] = "Blog post deleted successfully!";
        header('Location: blog.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting blog post: " . $e->getMessage();
        header('Location: blog.php');
        exit;
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_posts = $_POST['selected_posts'] ?? [];
    
    if (!empty($selected_posts)) {
        try {
            $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
            
            if ($bulk_action === 'delete') {
                // Get featured images first
                $stmt = $conn->prepare("SELECT featured_image FROM blog_posts WHERE id IN ($placeholders)");
                $stmt->execute($selected_posts);
                $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Delete posts
                $deleteStmt = $conn->prepare("DELETE FROM blog_posts WHERE id IN ($placeholders)");
                $deleteStmt->execute($selected_posts);
                
                // Delete featured images
                foreach ($posts as $post) {
                    if ($post['featured_image']) {
                        $file_path = "../assets/images/uploads/" . $post['featured_image'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
                
                $_SESSION['success_message'] = count($selected_posts) . " blog posts deleted successfully!";
            } elseif ($bulk_action === 'publish') {
                $stmt = $conn->prepare("UPDATE blog_posts SET status = 'published' WHERE id IN ($placeholders)");
                $stmt->execute($selected_posts);
                $_SESSION['success_message'] = count($selected_posts) . " blog posts published!";
            } elseif ($bulk_action === 'draft') {
                $stmt = $conn->prepare("UPDATE blog_posts SET status = 'draft' WHERE id IN ($placeholders)");
                $stmt->execute($selected_posts);
                $_SESSION['success_message'] = count($selected_posts) . " blog posts moved to draft!";
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Error performing bulk action: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "No blog posts selected!";
    }
    
    header('Location: blog.php');
    exit;
}

// Get all blog posts with author information
$stmt = $conn->query("
    SELECT b.*, u.full_name as author_name 
    FROM blog_posts b 
    LEFT JOIN admin_users u ON b.author_id = u.id 
    ORDER BY b.created_at DESC
");
$blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Blog Management</h1>
        <p>Manage your blog posts and content</p>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <div class="section-header">
            <h2>All Blog Posts</h2>
            <div>
                <a href="blog-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Write New Post
                </a>
            </div>
        </div>

        <!-- Bulk Actions -->
        <form method="POST" class="bulk-actions" id="bulkActionsForm">
            <div class="bulk-controls">
                <select name="bulk_action" class="form-control">
                    <option value="">Bulk Actions</option>
                    <option value="publish">Publish</option>
                    <option value="draft">Move to Draft</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-secondary" id="applyBulkAction">Apply</button>
            </div>

            <!-- Search and Filter -->
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" id="blogSearch" placeholder="Search blog posts...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-controls">
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="school-management">School Management</option>
                        <option value="restaurant-tech">Restaurant Tech</option>
                        <option value="ecommerce">eCommerce</option>
                        <option value="web-development">Web Development</option>
                        <option value="digital-marketing">Digital Marketing</option>
                        <option value="business-tips">Business Tips</option>
                    </select>
                </div>
            </div>

            <!-- Blog Posts Table -->
            <div class="table-container">
                <table class="data-table" id="blogTable">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Post</th>
                            <th>Author</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($blog_posts)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <i class="fas fa-blog" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                                <p>No blog posts found. <a href="blog-add.php">Write your first blog post</a></p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($blog_posts as $post): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox">
                                </td>
                                <td>
                                    <div class="post-info">
                                        <?php if ($post['featured_image']): ?>
                                        <img src="../assets/images/uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                             class="post-thumbnail">
                                        <?php else: ?>
                                        <div class="post-thumbnail placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div class="post-details">
                                            <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                            <div class="post-excerpt">
                                                <?php echo htmlspecialchars(substr($post['excerpt'] ?? $post['content'], 0, 100)); ?>...
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($post['author_name']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($post['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #64748b;">
                                        <i class="fas fa-eye"></i> <?php echo $post['views']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../blog/<?php echo $post['slug']; ?>.html" target="_blank" class="btn-icon" title="View">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="blog-edit.php?id=<?php echo $post['id']; ?>" class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $post['id']; ?>" class="btn-icon" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this blog post?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<style>
.bulk-actions {
    margin-bottom: 20px;
}

.bulk-controls {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.bulk-controls select {
    min-width: 150px;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 20px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.filter-controls {
    display: flex;
    gap: 10px;
}

.filter-controls select {
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    font-size: 14px;
}

.post-info {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.post-thumbnail {
    width: 60px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.post-thumbnail.placeholder {
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
}

.post-details {
    flex: 1;
    min-width: 0;
}

.post-details strong {
    display: block;
    margin-bottom: 5px;
    color: #1f2937;
}

.post-excerpt {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn-icon {
    width: 32px;
    height: 32px;
    font-size: 12px;
}

#selectAll {
    margin: 0;
}

.post-checkbox {
    margin: 0;
}

@media (max-width: 768px) {
    .table-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        max-width: none;
    }
    
    .filter-controls {
        justify-content: space-between;
    }
    
    .bulk-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .post-info {
        flex-direction: column;
        gap: 10px;
    }
    
    .post-thumbnail {
        width: 100%;
        height: 80px;
    }
}
</style>

<script>
// Bulk actions functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const postCheckboxes = document.querySelectorAll('.post-checkbox');
    const bulkActionsForm = document.getElementById('bulkActionsForm');
    const applyBulkAction = document.getElementById('applyBulkAction');

    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            postCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function(e) {
            const selectedPosts = document.querySelectorAll('.post-checkbox:checked');
            const bulkAction = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedPosts.length === 0) {
                e.preventDefault();
                alert('Please select at least one blog post.');
                return;
            }
            
            if (!bulkAction) {
                e.preventDefault();
                alert('Please select a bulk action.');
                return;
            }
            
            if (bulkAction === 'delete') {
                if (!confirm(`Are you sure you want to delete ${selectedPosts.length} blog post(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    }

    // Filter table functionality
    const searchInput = document.getElementById('blogSearch');
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const table = document.getElementById('blogTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const categoryValue = categoryFilter.value;

        rows.forEach(row => {
            if (row.cells.length < 8) return; // Skip empty state row
            
            const title = row.cells[1].textContent.toLowerCase();
            const category = row.cells[3].textContent.toLowerCase();
            const status = row.cells[4].textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);
            const matchesCategory = !categoryValue || category.includes(categoryValue);

            row.style.display = matchesSearch && matchesStatus && matchesCategory ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (categoryFilter) categoryFilter.addEventListener('change', filterTable);
});
</script>

<?php include '../includes/footer.php'; ?>