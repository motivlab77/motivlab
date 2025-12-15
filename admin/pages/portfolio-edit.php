<?php
require_once '../config.php';
$page_title = "Edit Portfolio Item";

// Get item data
$id = $_GET['id'] ?? 0;
$item = null;

try {
    $stmt = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        $_SESSION['error_message'] = "Portfolio item not found!";
        header('Location: portfolio.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading portfolio item: " . $e->getMessage();
    header('Location: portfolio.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        $featured_image = $item['featured_image'];
        
        // Handle file upload if new image provided
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
            $upload = uploadImage($_FILES['featured_image'], '../assets/images/portfolio/');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            } else {
                $_SESSION['error_message'] = $upload['message'];
                header('Location: portfolio-edit.php?id=' . $id);
                exit;
            }
        }
        
        // Update database
        $stmt = $conn->prepare("UPDATE portfolio_items SET title = ?, description = ?, category = ?, featured_image = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $category, $featured_image, $status, $id]);
        
        $_SESSION['success_message'] = "Portfolio item updated successfully!";
        header('Location: portfolio.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating portfolio item: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-edit"></i> Edit Portfolio Item</h1>
        <p>Update portfolio item details</p>
    </div>
    <a href="portfolio.php" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Portfolio
    </a>
</div>

<?php showMessages(); ?>

<div class="table-container" style="padding: 25px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($item['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Category *</label>
            <select name="category" class="form-control" required>
                <option value="Education" <?php echo $item['category'] == 'Education' ? 'selected' : ''; ?>>Education</option>
                <option value="Restaurant" <?php echo $item['category'] == 'Restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                <option value="eCommerce" <?php echo $item['category'] == 'eCommerce' ? 'selected' : ''; ?>>eCommerce</option>
                <option value="Business" <?php echo $item['category'] == 'Business' ? 'selected' : ''; ?>>Business</option>
                <option value="Portfolio" <?php echo $item['category'] == 'Portfolio' ? 'selected' : ''; ?>>Portfolio</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Featured Image</label>
            <?php if ($item['featured_image']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo htmlspecialchars($item['featured_image']); ?>" alt="Current Image" style="max-width: 200px; border-radius: 5px;">
                </div>
            <?php endif; ?>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <small style="color: var(--text-light);">Leave empty to keep current image</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="active" <?php echo $item['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $item['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Portfolio Item
            </button>
            <a href="portfolio.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
