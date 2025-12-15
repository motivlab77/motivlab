<?php
require_once '../config.php';
$page_title = "Portfolio Management";
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-briefcase"></i> Portfolio Management</h1>
        <p>Manage your portfolio items and projects</p>
    </div>
    <a href="portfolio-add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Item
    </a>
</div>

<?php showMessages(); ?>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $stmt = $conn->query("SELECT * FROM portfolio_items ORDER BY created_at DESC");
                $items = $stmt->fetchAll();
                
                if (empty($items)) {
                    echo '<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-light);">No portfolio items found. <a href="portfolio-add.php">Add your first item</a></td></tr>';
                } else {
                    foreach ($items as $item) {
                        echo '
                        <tr>
                            <td>' . htmlspecialchars($item['title']) . '</td>
                            <td>' . htmlspecialchars($item['category']) . '</td>
                            <td><span style="color: ' . ($item['status'] == 'active' ? 'green' : 'gray') . '">' . ucfirst($item['status']) . '</span></td>
                            <td>' . date('M d, Y', strtotime($item['created_at'])) . '</td>
                            <td>
                                <a href="portfolio-edit.php?id=' . $item['id'] . '" class="btn btn-small btn-success">Edit</a>
                                <a href="portfolio-delete.php?id=' . $item['id'] . '" class="btn btn-small btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
                            </td>
                        </tr>';
                    }
                }
            } catch (Exception $e) {
                echo '<tr><td colspan="5" style="text-align: center; color: red;">Error loading portfolio items</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
