<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Add Testimonial";
include '../includes/header.php';

// Initialize variables
$client_name = $business_name = $review_text = $rating = $status = '';
$client_photo = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $client_name = sanitizeInput($_POST['client_name']);
    $business_name = sanitizeInput($_POST['business_name']);
    $review_text = sanitizeInput($_POST['review_text']);
    $rating = sanitizeInput($_POST['rating']);
    $status = sanitizeInput($_POST['status']);

    // Validate inputs
    if (empty($client_name)) $errors[] = "Client name is required";
    if (empty($business_name)) $errors[] = "Business name is required";
    if (empty($review_text)) $errors[] = "Review text is required";
    if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $errors[] = "Valid rating (1-5) is required";
    }

    // Handle client photo upload
    if (isset($_FILES['client_photo']) && $_FILES['client_photo']['error'] === 0) {
        $uploadResult = uploadImage($_FILES['client_photo']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $client_photo = $uploadResult['filename'];
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            // Get the next display order
            $orderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 FROM testimonials");
            $orderStmt->execute();
            $display_order = $orderStmt->fetchColumn();

            $stmt = $conn->prepare("
                INSERT INTO testimonials 
                (client_name, business_name, review_text, rating, client_photo, status, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $client_name, $business_name, $review_text, $rating, 
                $client_photo, $status, $display_order
            ]);

            $_SESSION['success_message'] = "Testimonial added successfully!";
            header('Location: testimonials.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Add Testimonial</h1>
        <p>Add a new client testimonial to showcase on your website</p>
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
                    <h3>Client Information</h3>
                    
                    <div class="form-group">
                        <label for="client_name" class="form-label">Client Name *</label>
                        <input type="text" id="client_name" name="client_name" class="form-control" 
                               value="<?php echo htmlspecialchars($client_name); ?>" required 
                               placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label for="business_name" class="form-label">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" class="form-control" 
                               value="<?php echo htmlspecialchars($business_name); ?>" required 
                               placeholder="ABC Company">
                    </div>

                    <div class="form-group">
                        <label for="rating" class="form-label">Rating *</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="rating<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                   <?php echo $rating == $i ? 'checked' : ''; ?> required>
                            <label for="rating<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                <i class="fas fa-star"></i>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <div class="form-text">Select a rating from 1 to 5 stars</div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Client Photo</h3>

                    <div class="form-group">
                        <label for="client_photo" class="form-label">Client Photo</label>
                        <input type="file" id="client_photo" name="client_photo" 
                               class="form-control" accept="image/*"
                               onchange="previewImage(this, 'photoPreview')">
                        <div class="form-text">Recommended: Square image, 200x200px. Max file size: 2MB</div>
                        <div id="photoPreview" class="photo-preview" style="margin-top: 10px;"></div>
                    </div>

                    <div class="preview-card">
                        <h4>Preview</h4>
                        <div class="testimonial-preview">
                            <div class="preview-header">
                                <div id="previewPhoto" class="preview-photo">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="preview-info">
                                    <div id="previewName" class="preview-name">Client Name</div>
                                    <div id="previewBusiness" class="preview-business">Business Name</div>
                                </div>
                                <div class="preview-rating" id="previewRating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="preview-content">
                                <p id="previewText">Review text will appear here...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Text -->
        <div class="content-section">
            <h3>Review Content</h3>

            <div class="form-group">
                <label for="review_text" class="form-label">Review Text *</label>
                <textarea id="review_text" name="review_text" class="form-control" 
                          rows="6" required placeholder="Enter the client's review or testimonial..."
                          oninput="updatePreview()"><?php echo htmlspecialchars($review_text); ?></textarea>
                <div class="form-text">The client's testimonial or review text.</div>
                <div id="review-counter" class="char-counter">500 characters remaining</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Testimonial
            </button>
            <a href="testimonials.php" class="btn btn-secondary">
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

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 24px;
    color: #d1d5db;
    transition: color 0.2s ease;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #f59e0b;
}

.rating-input input:checked + label {
    color: #f59e0b;
}

.photo-preview {
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

.photo-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 50%;
}

.preview-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
}

.preview-card h4 {
    margin: 0 0 15px 0;
    color: #374151;
    font-size: 16px;
}

.testimonial-preview {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.preview-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.preview-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 20px;
    flex-shrink: 0;
}

.preview-photo img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.preview-info {
    flex: 1;
}

.preview-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 2px;
}

.preview-business {
    font-size: 14px;
    color: #64748b;
}

.preview-rating {
    display: flex;
    gap: 2px;
}

.preview-rating i {
    color: #f59e0b;
    font-size: 14px;
}

.preview-rating i:not(.active) {
    color: #d1d5db;
}

.preview-content p {
    margin: 0;
    color: #4b5563;
    line-height: 1.6;
    font-style: italic;
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
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; border-radius: 50%;">`;
            
            // Update preview card
            const previewPhoto = document.getElementById('previewPhoto');
            previewPhoto.innerHTML = `<img src="${e.target.result}" alt="Client Photo">`;
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<div style="color: #64748b;"><i class="fas fa-user fa-2x"></i><br>No photo selected</div>';
        
        // Reset preview card
        const previewPhoto = document.getElementById('previewPhoto');
        previewPhoto.innerHTML = '<i class="fas fa-user"></i>';
    }
}

function updatePreview() {
    const reviewText = document.getElementById('review_text').value;
    const clientName = document.getElementById('client_name').value || 'Client Name';
    const businessName = document.getElementById('business_name').value || 'Business Name';
    
    document.getElementById('previewText').textContent = reviewText || 'Review text will appear here...';
    document.getElementById('previewName').textContent = clientName;
    document.getElementById('previewBusiness').textContent = businessName;
}

// Update preview when client or business name changes
document.addEventListener('DOMContentLoaded', function() {
    const clientNameInput = document.getElementById('client_name');
    const businessNameInput = document.getElementById('business_name');
    const reviewTextInput = document.getElementById('review_text');
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    
    if (clientNameInput) {
        clientNameInput.addEventListener('input', updatePreview);
    }
    
    if (businessNameInput) {
        businessNameInput.addEventListener('input', updatePreview);
    }
    
    // Update rating preview
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const stars = document.querySelectorAll('#previewRating i');
            
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        });
    });
    
    // Character counter for review text
    const reviewCounter = document.getElementById('review-counter');
    if (reviewTextInput && reviewCounter) {
        reviewTextInput.addEventListener('input', function() {
            const remaining = 500 - this.value.length;
            reviewCounter.textContent = `${remaining} characters remaining`;
            reviewCounter.style.color = remaining < 0 ? '#ef4444' : remaining < 50 ? '#f59e0b' : '#64748b';
        });
        
        // Initialize counter
        const remaining = 500 - reviewTextInput.value.length;
        reviewCounter.textContent = `${remaining} characters remaining`;
        reviewCounter.style.color = remaining < 0 ? '#ef4444' : remaining < 50 ? '#f59e0b' : '#64748b';
    }
    
    // Initialize preview
    updatePreview();
});
</script>

<?php include '../includes/footer.php'; ?>