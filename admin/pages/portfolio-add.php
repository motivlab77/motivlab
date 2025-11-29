<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Add Portfolio Item";
include '../includes/header.php';

// Initialize variables
$title = $slug = $category = $description = $long_description = $client_name = $completion_date = $demo_url = $status = '';
$featured_image = '';
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

    // Check if slug is unique
    $stmt = $conn->prepare("SELECT id FROM portfolio_items WHERE slug = ?");
    $stmt->execute([$slug]);
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

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO portfolio_items 
                (title, slug, category, description, long_description, featured_image, client_name, completion_date, demo_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $slug, $category, $description, $long_description, 
                $featured_image, $client_name, $completion_date, $demo_url, $status
            ]);

            $portfolio_id = $conn->lastInsertId();
            
            $_SESSION['success_message'] = "Portfolio item added successfully!";
            header('Location: portfolio-edit.php?id=' . $portfolio_id);
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Auto-generate slug from title
if (empty($slug) && !empty($title)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Add Portfolio Item</h1>
        <p>Create a new portfolio item to showcase your work</p>
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
                               value="<?php echo htmlspecialchars($title); ?>" required 
                               oninput="generateSlug(this.value)">
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
                <i class="fas fa-save"></i> Save Portfolio Item
            </button>
            <a href="portfolio.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.form-container {
    max-width: none;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.form-column {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.image-preview {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f8fafc;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 6px;
}

.char-counter {
    font-size: 12px;
    color: #64748b;
    margin-top: 5px;
    text-align: right;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function generateSlug(title) {
    if (!document.getElementById('slug').value) {
        const slug = title.toLowerCase()
            .trim()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    }
}

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

// Character counters
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