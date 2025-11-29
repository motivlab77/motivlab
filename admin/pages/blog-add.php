<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Write Blog Post";
include '../includes/header.php';

// Initialize variables
$title = $slug = $category = $excerpt = $content = $status = '';
$featured_image = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = sanitizeInput($_POST['title']);
    $slug = sanitizeInput($_POST['slug']);
    $category = sanitizeInput($_POST['category']);
    $excerpt = sanitizeInput($_POST['excerpt']);
    $content = $_POST['content'];
    $status = sanitizeInput($_POST['status']);

    // Validate inputs
    if (empty($title)) $errors[] = "Title is required";
    if (empty($slug)) $errors[] = "Slug is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($excerpt)) $errors[] = "Excerpt is required";
    if (empty($content)) $errors[] = "Content is required";

    // Check if slug is unique
    $stmt = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ?");
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
            $author_id = $_SESSION['admin_id'];
            
            $stmt = $conn->prepare("
                INSERT INTO blog_posts 
                (title, slug, category, excerpt, content, featured_image, author_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $slug, $category, $excerpt, $content, 
                $featured_image, $author_id, $status
            ]);

            $post_id = $conn->lastInsertId();
            
            $_SESSION['success_message'] = "Blog post created successfully!";
            header('Location: blog-edit.php?id=' . $post_id);
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
        <h1>Write Blog Post</h1>
        <p>Create a new blog post for your website</p>
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
                    <h3>Post Information</h3>
                    
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
                        <div class="form-text">Used in the URL: yoursite.com/blog/<strong><?php echo htmlspecialchars($slug); ?></strong></div>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="school-management" <?php echo $category === 'school-management' ? 'selected' : ''; ?>>School Management</option>
                            <option value="restaurant-tech" <?php echo $category === 'restaurant-tech' ? 'selected' : ''; ?>>Restaurant Tech</option>
                            <option value="ecommerce" <?php echo $category === 'ecommerce' ? 'selected' : ''; ?>>eCommerce</option>
                            <option value="web-development" <?php echo $category === 'web-development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="digital-marketing" <?php echo $category === 'digital-marketing' ? 'selected' : ''; ?>>Digital Marketing</option>
                            <option value="business-tips" <?php echo $category === 'business-tips' ? 'selected' : ''; ?>>Business Tips</option>
                        </select>
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

            <!-- Right Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Featured Image</h3>

                    <div class="form-group">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input type="file" id="featured_image" name="featured_image" 
                               class="form-control" accept="image/*"
                               onchange="previewImage(this, 'imagePreview')">
                        <div class="form-text">Recommended size: 1200x630px. Max file size: 5MB</div>
                        <div id="imagePreview" class="image-preview" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Fields -->
        <div class="content-section">
            <h3>Post Content</h3>

            <div class="form-group">
                <label for="excerpt" class="form-label">Excerpt *</label>
                <textarea id="excerpt" name="excerpt" class="form-control" 
                          rows="4" required placeholder="Brief summary of the post"><?php echo htmlspecialchars($excerpt); ?></textarea>
                <div class="form-text">This appears in blog listings and search results. Maximum 200 characters.</div>
                <div id="excerpt-counter" class="char-counter">200 characters remaining</div>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">Content *</label>
                <textarea id="content" name="content" class="form-control" 
                          rows="15" placeholder="Write your blog post content here..."><?php echo htmlspecialchars($content); ?></textarea>
                <div class="form-text">Full blog post content with proper formatting.</div>
                <div id="content-counter" class="char-counter">10000 characters remaining</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" name="save_draft" value="draft" class="btn btn-secondary">
                <i class="fas fa-save"></i> Save Draft
            </button>
            <button type="submit" name="publish" value="published" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Publish
            </button>
            <a href="blog.php" class="btn btn-secondary">
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
    const excerpt = document.getElementById('excerpt');
    const content = document.getElementById('content');
    const excerptCounter = document.getElementById('excerpt-counter');
    const contentCounter = document.getElementById('content-counter');

    function updateCounter(textarea, counter, maxLength) {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} characters remaining`;
        counter.style.color = remaining < 0 ? '#ef4444' : remaining < 50 ? '#f59e0b' : '#64748b';
    }

    if (excerpt && excerptCounter) {
        excerpt.addEventListener('input', function() {
            updateCounter(this, excerptCounter, 200);
        });
        updateCounter(excerpt, excerptCounter, 200);
    }

    if (content && contentCounter) {
        content.addEventListener('input', function() {
            updateCounter(this, contentCounter, 10000);
        });
        updateCounter(content, contentCounter, 10000);
    }
});

// Auto-save functionality (basic version)
let autoSaveTimer;
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const fields = form.querySelectorAll('input, textarea, select');
    
    fields.forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // In a real application, you would save to localStorage or send AJAX request
                console.log('Auto-save triggered');
            }, 2000);
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>