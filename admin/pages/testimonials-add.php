<?php
require_once '../config.php';
$page_title = "Add Testimonial";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_name = sanitizeInput($_POST['client_name']);
    $business_name = sanitizeInput($_POST['business_name']);
    $review_text = sanitizeInput($_POST['review_text']);
    $rating = intval($_POST['rating']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        // Handle file upload
        $client_photo = '';
        if (isset($_FILES['client_photo']) && $_FILES['client_photo']['error'] == 0) {
            $upload = uploadImage($_FILES['client_photo'], '../assets/images/testimonials/');
            if ($upload['success']) {
                $client_photo = $upload['path'];
            } else {
                $_SESSION['error_message'] = $upload['message'];
                header('Location: testimonials-add.php');
                exit;
            }
        }
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO testimonials (client_name, business_name, review_text, rating, client_photo, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$client_name, $business_name, $review_text, $rating, $client_photo, $status]);
        
        $_SESSION['success_message'] = "Testimonial added successfully!";
        header('Location: testimonials-list.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error adding testimonial: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-star"></i> Add Testimonial</h1>
        <p>Add a new client testimonial</p>
    </div>
    <a href="testimonials-list.php" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Testimonials
    </a>
</div>

<?php showMessages(); ?>

<div class="table-container" style="padding: 25px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Client Name *</label>
            <input type="text" name="client_name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Business Name</label>
            <input type="text" name="business_name" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">Review Text *</label>
            <textarea name="review_text" class="form-control" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Rating *</label>
            <select name="rating" class="form-control" required>
                <option value="5">★★★★★ (5 Stars)</option>
                <option value="4">★★★★☆ (4 Stars)</option>
                <option value="3">★★★☆☆ (3 Stars)</option>
                <option value="2">★★☆☆☆ (2 Stars)</option>
                <option value="1">★☆☆☆☆ (1 Star)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Client Photo</label>
            <input type="file" name="client_photo" class="form-control" accept="image/*">
            <small style="color: var(--text-light);">Optional client photo</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Testimonial
            </button>
            <a href="testimonials-list.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
