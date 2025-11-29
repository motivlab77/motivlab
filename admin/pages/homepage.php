<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Homepage Content";
include '../includes/header.php';

// Get current homepage settings
$stmt = $conn->query("SELECT setting_key, setting_value FROM homepage_settings");
$homepage_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Initialize default values if not set
$default_settings = [
    'hero_headline' => 'Grow Your Business With Smart, Modern Websites',
    'hero_subtext' => 'Beautiful designs, fast performance, and powerful admin dashboards.',
    'hero_image' => 'hero-mockup.png',
    'why_us_title' => 'Why Choose MotivLab',
    'why_us_subtitle' => 'What makes us different from other web agencies',
    'cta_title' => 'Ready to Grow Your Business Online?',
    'cta_text' => 'Join hundreds of Nigerian businesses already succeeding with MotivLab websites'
];

foreach ($default_settings as $key => $value) {
    if (!isset($homepage_settings[$key])) {
        $homepage_settings[$key] = $value;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Sanitize inputs
    $hero_headline = sanitizeInput($_POST['hero_headline']);
    $hero_subtext = sanitizeInput($_POST['hero_subtext']);
    $why_us_title = sanitizeInput($_POST['why_us_title']);
    $why_us_subtitle = sanitizeInput($_POST['why_us_subtitle']);
    $cta_title = sanitizeInput($_POST['cta_title']);
    $cta_text = sanitizeInput($_POST['cta_text']);

    // Validate inputs
    if (empty($hero_headline)) $errors[] = "Hero headline is required";
    if (empty($hero_subtext)) $errors[] = "Hero subtext is required";
    if (empty($why_us_title)) $errors[] = "Why Us title is required";
    if (empty($cta_title)) $errors[] = "CTA title is required";

    // Handle hero image upload
    $hero_image = $homepage_settings['hero_image'];
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
        $uploadResult = uploadImage($_FILES['hero_image']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $hero_image = $uploadResult['filename'];
            // Delete old hero image if exists and it's not the default
            if ($homepage_settings['hero_image'] && $homepage_settings['hero_image'] !== 'hero-mockup.png' && file_exists("../assets/images/uploads/" . $homepage_settings['hero_image'])) {
                unlink("../assets/images/uploads/" . $homepage_settings['hero_image']);
            }
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $settings_to_update = [
                'hero_headline' => $hero_headline,
                'hero_subtext' => $hero_subtext,
                'hero_image' => $hero_image,
                'why_us_title' => $why_us_title,
                'why_us_subtitle' => $why_us_subtitle,
                'cta_title' => $cta_title,
                'cta_text' => $cta_text
            ];
            
            foreach ($settings_to_update as $key => $value) {
                $stmt = $conn->prepare("
                    INSERT INTO homepage_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = "Homepage content updated successfully!";
            header('Location: homepage.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // Update settings array with new values for form
    $homepage_settings = array_merge($homepage_settings, [
        'hero_headline' => $hero_headline,
        'hero_subtext' => $hero_subtext,
        'hero_image' => $hero_image,
        'why_us_title' => $why_us_title,
        'why_us_subtitle' => $why_us_subtitle,
        'cta_title' => $cta_title,
        'cta_text' => $cta_text
    ]);
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Homepage Content</h1>
        <p>Manage the content and appearance of your homepage</p>
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
        <!-- Hero Section -->
        <div class="content-section">
            <h2>Hero Section</h2>
            <p class="section-description">The main banner section that visitors see first</p>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="hero_headline" class="form-label">Headline *</label>
                    <input type="text" id="hero_headline" name="hero_headline" class="form-control" 
                           value="<?php echo htmlspecialchars($homepage_settings['hero_headline']); ?>" required>
                    <div class="form-text">Main headline that grabs attention</div>
                </div>

                <div class="form-group">
                    <label for="hero_subtext" class="form-label">Subtext *</label>
                    <textarea id="hero_subtext" name="hero_subtext" class="form-control" rows="3" required><?php echo htmlspecialchars($homepage_settings['hero_subtext']); ?></textarea>
                    <div class="form-text">Supporting text that explains your value proposition</div>
                </div>
            </div>

            <div class="form-group">
                <label for="hero_image" class="form-label">Hero Image</label>
                <?php if ($homepage_settings['hero_image']): ?>
                <div class="current-image">
                    <img src="../assets/images/<?php echo $homepage_settings['hero_image'] === 'hero-mockup.png' ? '' : 'uploads/'; ?><?php echo htmlspecialchars($homepage_settings['hero_image']); ?>" 
                         alt="Current hero image" style="max-width: 300px; margin-bottom: 10px;">
                    <div class="form-text">Current hero image</div>
                </div>
                <?php endif; ?>
                <input type="file" id="hero_image" name="hero_image" class="form-control" accept="image/*">
                <div class="form-text">Recommended: 1200x800px, showing your product or service</div>
            </div>

            <div class="preview-section">
                <h4>Preview</h4>
                <div class="hero-preview" style="background: linear-gradient(135deg, #fff5eb 0%, #ffe8d6 100%); padding: 40px; border-radius: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
                        <div>
                            <h2 style="font-size: 2.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 20px; line-height: 1.2;" id="previewHeadline">
                                <?php echo htmlspecialchars($homepage_settings['hero_headline']); ?>
                            </h2>
                            <p style="font-size: 1.25rem; color: #666; margin-bottom: 30px;" id="previewSubtext">
                                <?php echo htmlspecialchars($homepage_settings['hero_subtext']); ?>
                            </p>
                            <div style="display: flex; gap: 15px;">
                                <button style="background: #f68b1e; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: default;">
                                    View Portfolio
                                </button>
                                <button style="background: #2c2c2c; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: default;">
                                    Request a Demo
                                </button>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <?php if ($homepage_settings['hero_image']): ?>
                            <img src="../assets/images/<?php echo $homepage_settings['hero_image'] === 'hero-mockup.png' ? '' : 'uploads/'; ?><?php echo htmlspecialchars($homepage_settings['hero_image']); ?>" 
                                 alt="Hero preview" style="max-width: 100%; border-radius: 8px;">
                            <?php else: ?>
                            <div style="background: #e2e8f0; height: 200px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                Hero Image Preview
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Us Section -->
        <div class="content-section">
            <h2>Why Choose Us Section</h2>
            <p class="section-description">Section highlighting your unique value proposition</p>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="why_us_title" class="form-label">Section Title *</label>
                    <input type="text" id="why_us_title" name="why_us_title" class="form-control" 
                           value="<?php echo htmlspecialchars($homepage_settings['why_us_title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="why_us_subtitle" class="form-label">Section Subtitle</label>
                    <input type="text" id="why_us_subtitle" name="why_us_subtitle" class="form-control" 
                           value="<?php echo htmlspecialchars($homepage_settings['why_us_subtitle']); ?>">
                    <div class="form-text">Optional subtitle for more context</div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="content-section">
            <h2>Call-to-Action Section</h2>
            <p class="section-description">The final section encouraging visitors to take action</p>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="cta_title" class="form-label">CTA Title *</label>
                    <input type="text" id="cta_title" name="cta_title" class="form-control" 
                           value="<?php echo htmlspecialchars($homepage_settings['cta_title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="cta_text" class="form-label">CTA Text</label>
                    <textarea id="cta_text" name="cta_text" class="form-control" rows="2"><?php echo htmlspecialchars($homepage_settings['cta_text']); ?></textarea>
                    <div class="form-text">Supporting text for the call-to-action</div>
                </div>
            </div>

            <div class="preview-section">
                <h4>Preview</h4>
                <div class="cta-preview" style="background: linear-gradient(135deg, #f68b1e 0%, #ff9d3f 100%); color: white; padding: 60px 40px; border-radius: 12px; text-align: center;">
                    <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 15px;" id="previewCtaTitle">
                        <?php echo htmlspecialchars($homepage_settings['cta_title']); ?>
                    </h2>
                    <p style="font-size: 1.125rem; margin-bottom: 30px; opacity: 0.95;" id="previewCtaText">
                        <?php echo htmlspecialchars($homepage_settings['cta_text']); ?>
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <button style="background: white; color: #f68b1e; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: default;">
                            Get Started
                        </button>
                        <button style="background: transparent; color: white; border: 2px solid white; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: default;">
                            Request Pricing
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Homepage Content
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                <i class="fas fa-undo"></i> Reset to Defaults
            </button>
        </div>
    </form>
</div>

<style>
.section-description {
    color: #64748b;
    margin-bottom: 25px;
    font-size: 14px;
}

.preview-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.preview-section h4 {
    margin-bottom: 15px;
    color: #374151;
}

.hero-preview, .cta-preview {
    border: 1px solid #e2e8f0;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .hero-preview > div {
        grid-template-columns: 1fr !important;
        text-align: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Update preview in real-time
document.addEventListener('DOMContentLoaded', function() {
    const headlineInput = document.getElementById('hero_headline');
    const subtextInput = document.getElementById('hero_subtext');
    const whyUsTitleInput = document.getElementById('why_us_title');
    const whyUsSubtitleInput = document.getElementById('why_us_subtitle');
    const ctaTitleInput = document.getElementById('cta_title');
    const ctaTextInput = document.getElementById('cta_text');
    
    function updatePreview() {
        document.getElementById('previewHeadline').textContent = headlineInput.value;
        document.getElementById('previewSubtext').textContent = subtextInput.value;
        document.getElementById('previewCtaTitle').textContent = ctaTitleInput.value;
        document.getElementById('previewCtaText').textContent = ctaTextInput.value;
    }
    
    if (headlineInput) headlineInput.addEventListener('input', updatePreview);
    if (subtextInput) subtextInput.addEventListener('input', updatePreview);
    if (ctaTitleInput) ctaTitleInput.addEventListener('input', updatePreview);
    if (ctaTextInput) ctaTextInput.addEventListener('input', updatePreview);
});

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all homepage content to default values?')) {
        document.getElementById('hero_headline').value = 'Grow Your Business With Smart, Modern Websites';
        document.getElementById('hero_subtext').value = 'Beautiful designs, fast performance, and powerful admin dashboards.';
        document.getElementById('why_us_title').value = 'Why Choose MotivLab';
        document.getElementById('why_us_subtitle').value = 'What makes us different from other web agencies';
        document.getElementById('cta_title').value = 'Ready to Grow Your Business Online?';
        document.getElementById('cta_text').value = 'Join hundreds of Nigerian businesses already succeeding with MotivLab websites';
        
        // Update preview
        const event = new Event('input');
        document.getElementById('hero_headline').dispatchEvent(event);
    }
}

// Character counters for text areas
document.addEventListener('DOMContentLoaded', function() {
    const textAreas = document.querySelectorAll('textarea');
    
    textAreas.forEach(textarea => {
        const maxLength = textarea.id === 'hero_subtext' ? 200 : 
                         textarea.id === 'cta_text' ? 150 : 500;
        
        // Create counter element
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.fontSize = '12px';
        counter.style.color = '#64748b';
        counter.style.marginTop = '5px';
        counter.style.textAlign = 'right';
        
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 0 ? '#ef4444' : remaining < 20 ? '#f59e0b' : '#64748b';
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initialize
    });
});
</script>

<?php include '../includes/footer.php'; ?>