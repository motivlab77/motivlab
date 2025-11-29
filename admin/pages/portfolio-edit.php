<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: portfolio.php');
    exit;
}

$id = $_GET['id'];

// Get portfolio item
$stmt = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
$stmt->execute([$id]);
$portfolio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$portfolio) {
    header('Location: portfolio.php');
    exit;
}

$page_title = "Edit Portfolio: " . $portfolio['title'];
include '../includes/header.php';

// Initialize variables
$title = $portfolio['title'];
$slug = $portfolio['slug'];
$category = $portfolio['category'];
$description = $portfolio['description'];
$long_description = $portfolio['long_description'];
$client_name = $portfolio['client_name'];
$completion_date = $portfolio['completion_date'];
$demo_url = $portfolio['demo_url'];
$status = $portfolio['status'];
$featured_image = $portfolio['featured_image'];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = sanitizeInput($_POST['title']);
    $slug = sanitizeInput($_POST['slug']);
    $category = sanitizeInput($_POST['category']);
    $description = sanitizeInput($_POST['description']);
    $long_description = $_POST['long_description'];
    $client_name = sanitizeInput($_POST['client_name']);
    $completion_date = sanitizeInput($_POST['completion_date']);
    $demo_url = sanitizeInput($_POST['demo_url']);
    $status = sanitizeInput($_POST['status']);

    // Validate inputs
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($description)) $errors[] = "Description is required";

    // Check if slug is unique (excluding current item)
    $stmt = $conn->prepare("SELECT id FROM portfolio_items WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $id]);
    if ($stmt->fetch()) {
        $errors[] = "Slug already exists. Please choose a different one.";
    }

    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $uploadResult = uploadImage($_FILES['featured_image']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $featured_image = $uploadResult['filename'];
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE portfolio_items 
                SET title = ?, slug = ?, category = ?, description = ?, long_description = ?, 
                    featured_image = ?, client_name = ?, completion_date = ?, demo_url = ?, status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $slug, $category, $description, $long_description, 
                $featured_image, $client_name, $completion_date, $demo_url, $status,
                $id
            ]);

            $_SESSION['success_message'] = "Portfolio item updated successfully!";
            header('Location: portfolio.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Edit Portfolio Item</h1>
        <p>Update your portfolio item details</p>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <div class="form-grid">
            <!-- Left Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">URL Slug *</label>
                        <input type="text" id="slug" name="slug" class="form-control" 
                               value="<?php echo htmlspecialchars($slug); ?>" required>
                        <div class="form-text">Used in the URL: yoursite.com/portfolio/<strong><?php echo htmlspecialchars($slug); ?></strong></div>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="school" <?php echo $category === 'school' ? 'selected' : ''; ?>>School System</option>
                            <option value="restaurant" <?php echo $category === 'restaurant' ? 'selected' : ''; ?>>Restaurant & Food</option>
                            <option value="ecommerce" <?php echo $category === 'ecommerce' ? 'selected' : ''; ?>>eCommerce Store</option>
                            <option value="salon" <?php echo $category === 'salon' ? 'selected' : ''; ?>>Salon & Beauty</option>
                            <option value="realestate" <?php echo $category === 'realestate' ? 'selected' : ''; ?>>Real Estate</option>
                            <option value="logistics" <?php echo $category === 'logistics' ? 'selected' : ''; ?>>Logistics & Delivery</option>
                            <option value="events" <?php echo $category === 'events' ? 'selected' : ''; ?>>Events & Ticketing</option>
                            <option value="portfolio" <?php echo $category === 'portfolio' ? 'selected' : ''; ?>>Portfolio Website</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" id="client_name" name="client_name" class="form-control" 
                               value="<?php echo htmlspecialchars($client_name); ?>">
                    </div>

                    <div class="form-group">
                        <label for="completion_date" class="form-label">Completion Date</label>
                        <input type="date" id="completion_date" name="completion_date" class="form-control" 
                               value="<?php echo htmlspecialchars($completion_date); ?>">
                    </div>

                    <div class="form-group">
                        <label for="demo_url" class="form-label">Demo URL</label>
                        <input type="url" id="demo_url" name="demo_url" class="form-control" 
                               value="<?php echo htmlspecialchars($demo_url); ?>" 
                               placeholder="https://example.com">
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Media & Status</h3>

                    <div class="form-group">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <?php if ($featured_image): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../assets/images/uploads/<?php echo htmlspecialchars($featured_image); ?>" 
                                 alt="Current featured image" style="max-width: 200px; border-radius: 6px;">
                            <div class="form-text">Current featured image</div>
                        </div>
                        <?php endif; ?>
                        <input type="file" id="featured_image" name="featured_image" 
                               class="form-control" accept="image/*"
                               onchange="previewImage(this, 'imagePreview')">
                        <div class="form-text">Recommended size: 800x600px. Max file size: 5MB</div>
                        <div id="imagePreview" class="image-preview" style="margin-top: 10px;"></div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Fields -->
        <div class="content-section">
            <h3>Content</h3>

            <div class="form-group">
                <label for="description" class="form-label">Short Description *</label>
                <textarea id="description" name="description" class="form-control" 
                          rows="4" required placeholder="Brief description for listings and previews"><?php echo htmlspecialchars($description); ?></textarea>
                <div class="form-text">Maximum 200 characters. This appears in portfolio listings.</div>
                <div id="description-counter" class="char-counter">200 characters remaining</div>
            </div>

            <div class="form-group">
                <label for="long_description" class="form-label">Long Description</label>
                <textarea id="long_description" name="long_description" class="form-control" 
                          rows="8" placeholder="Detailed description for the portfolio detail page"><?php echo htmlspecialchars($long_description); ?></textarea>
                <div class="form-text">Full description that appears on the portfolio detail page.</div>
                <div id="long-description-counter" class="char-counter">5000 characters remaining</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Portfolio Item
            </button>
            <a href="portfolio.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <a href="portfolio-gallery.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                <i class="fas fa-images"></i> Manage Gallery
            </a>
            <a href="portfolio-pricing.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Pricing
            </a>
        </div>
    </form>
</div>

<!-- Include the same CSS and JavaScript as portfolio-add.php -->
<style>
.form-container { max-width: none; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
.form-column { display: flex; flex-direction: column; gap: 30px; }
.image-preview { border: 2px dashed #e2e8f0; border-radius: 8px; padding: 20px; text-align: center; background: #f8fafc; min-height: 100px; display: flex; align-items: center; justify-content: center; }
.image-preview img { max-width: 100%; max-height: 200px; border-radius: 6px; }
.char-counter { font-size: 12px; color: #64748b; margin-top: 5px; text-align: right; }
.form-actions { display: flex; gap: 15px; padding: 20px 0; border-top: 1px solid #e2e8f0; }
@media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } }
</style>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; border-radius: 6px;">`;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<div style="color: #64748b;"><i class="fas fa-image fa-2x"></i><br>No image selected</div>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const description = document.getElementById('description');
    const longDescription = document.getElementById('long_description');
    const descCounter = document.getElementById('description-counter');
    const longDescCounter = document.getElementById('long-description-counter');

    function updateCounter(textarea, counter, maxLength) {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} characters remaining`;
        counter.style.color = remaining < 0 ? '#ef4444' : remaining < 50 ? '#f59e0b' : '#64748b';
    }

    if (description && descCounter) {
        description.addEventListener('input', function() {
            updateCounter(this, descCounter, 200);
        });
        updateCounter(description, descCounter, 200);
    }

    if (longDescription && longDescCounter) {
        longDescription.addEventListener('input', function() {
            updateCounter(this, longDescCounter, 5000);
        });
        updateCounter(longDescription, longDescCounter, 5000);
    }
});
</script>

<?php include '../includes/footer.php'; ?>