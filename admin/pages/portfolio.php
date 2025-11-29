<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Portfolio Management";
include '../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        // Delete portfolio item
        $stmt = $conn->prepare("DELETE FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Portfolio item deleted successfully!";
        header('Location: portfolio.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting portfolio item: " . $e->getMessage();
        header('Location: portfolio.php');
        exit;
    }
}

// Get all portfolio items
$stmt = $conn->query("
    SELECT p.*, COUNT(g.id) as gallery_count 
    FROM portfolio_items p 
    LEFT JOIN portfolio_gallery g ON p.id = g.portfolio_id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$portfolio_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Portfolio Management</h1>
        <p>Manage your portfolio items and showcase your work</p>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <div class="section-header">
            <h2>All Portfolio Items</h2>
            <div>
                <a href="portfolio-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Portfolio
                </a>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="table-controls">
            <div class="search-box">
                <input type="text" id="portfolioSearch" placeholder="Search portfolio items...">
                <i class="fas fa-search"></i>
            </div>
            <div class="filter-controls">
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="school">School Systems</option>
                    <option value="restaurant">Restaurants</option>
                    <option value="ecommerce">eCommerce</option>
                    <option value="salon">Salon & Beauty</option>
                    <option value="realestate">Real Estate</option>
                    <option value="logistics">Logistics</option>
                    <option value="events">Events</option>
                    <option value="portfolio">Portfolio</option>
                </select>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>

        <!-- Portfolio Table -->
        <div class="table-container">
            <table class="data-table" id="portfolioTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Gallery</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($portfolio_items)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fas fa-briefcase" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                            <p>No portfolio items found. <a href="portfolio-add.php">Add your first portfolio item</a></p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($portfolio_items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['featured_image']): ?>
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($item['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px;">
                                <?php else: ?>
                                <div style="width: 60px; height: 40px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="color: #cbd5e1;"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                <?php if ($item['client_name']): ?>
                                <br><small style="color: #64748b;">Client: <?php echo htmlspecialchars($item['client_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo ucfirst($item['category']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $item['status'] === 'published' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #64748b;">
                                    <i class="fas fa-images"></i> <?php echo $item['gallery_count']; ?> images
                                </span>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="portfolio-edit.php?id=<?php echo $item['id']; ?>" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="portfolio-gallery.php?id=<?php echo $item['id']; ?>" class="btn-icon" title="Gallery">
                                        <i class="fas fa-images"></i>
                                    </a>
                                    <a href="portfolio-pricing.php?id=<?php echo $item['id']; ?>" class="btn-icon" title="Pricing">
                                        <i class="fas fa-tags"></i>
                                    </a>
                                    <a href="?delete=<?php echo $item['id']; ?>" class="btn-icon" title="Delete" 
                                       onclick="return confirm('Are you sure you want to delete this portfolio item?')">
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
    </div>
</div>

<style>
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

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn-icon {
    width: 32px;
    height: 32px;
    font-size: 12px;
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
}
</style>

<script>
// Filter table functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('portfolioSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('portfolioTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value;
        const statusValue = statusFilter.value;

        rows.forEach(row => {
            const title = row.cells[1].textContent.toLowerCase();
            const category = row.cells[2].textContent.toLowerCase();
            const status = row.cells[3].textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm);
            const matchesCategory = !categoryValue || category.includes(categoryValue);
            const matchesStatus = !statusValue || status.includes(statusValue);

            row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php include '../includes/footer.php'; ?>