<?php
require_once '../config.php';
$page_title = "Add Portfolio Item";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        // Handle file upload
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
            $upload = uploadImage($_FILES['featured_image'], '../assets/images/portfolio/');
            if ($upload['success']) {
                $featured_image = $upload['path'];
            } else {
                $_SESSION['error_message'] = $upload['message'];
                header('Location: portfolio-add.php');
                exit;
            }
        }
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO portfolio_items (title, description, category, featured_image, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $featured_image, $status]);
        
        $_SESSION['success_message'] = "Portfolio item added successfully!";
        header('Location: portfolio.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error adding portfolio item: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-plus-circle"></i> Add Portfolio Item</h1>
        <p>Create a new portfolio item</p>
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
            <input type="text" name="title" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Describe the project..."></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Category *</label>
            <select name="category" class="form-control" required>
                <option value="">Select Category</option>
                <option value="Education">Education</option>
                <option value="Restaurant">Restaurant</option>
                <option value="eCommerce">eCommerce</option>
                <option value="Business">Business</option>
                <option value="Portfolio">Portfolio</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Featured Image</label>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <small style="color: var(--text-light);">Recommended: 800x600px, Max 5MB</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Portfolio Item
            </button>
            <a href="portfolio.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
