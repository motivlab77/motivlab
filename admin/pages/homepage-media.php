<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Homepage Images";
include '../includes/header.php';

// Get current homepage settings
$logo = '';
$hero_image = '';
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM homepage_settings WHERE setting_key IN ('site_logo', 'hero_image')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'site_logo') $logo = $row['setting_value'];
        if ($row['setting_key'] === 'hero_image') $hero_image = $row['setting_value'];
    }
} catch(PDOException $e) {}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_logo = sanitizeInput($_POST['site_logo']);
    $new_hero = sanitizeInput($_POST['hero_image']);
    
    try {
        // Update or insert logo
        $stmt = $conn->prepare("
            INSERT INTO homepage_settings (setting_key, setting_value) 
            VALUES ('site_logo', ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        $stmt->execute([$new_logo, $new_logo]);
        
        // Update or insert hero image
        $stmt = $conn->prepare("
            INSERT INTO homepage_settings (setting_key, setting_value) 
            VALUES ('hero_image', ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        $stmt->execute([$new_hero, $new_hero]);
        
        $_SESSION['success_message'] = "Homepage images updated successfully!";
        header('Location: homepage-media.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating images: " . $e->getMessage();
    }
}

// Get available media files
$stmt = $conn->query("SELECT * FROM media_files ORDER BY created_at DESC");
$media_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Homepage Images</h1>
        <p>Select images for your homepage</p>
    </div>

    <?php showMessages(); ?>

    <form method="POST" class="content-section">
        <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- Logo Selection -->
            <div>
                <h3>Site Logo</h3>
                <div class="image-selector">
                    <div class="current-image">
                        <?php if ($logo): ?>
                        <img src="../../<?php echo $logo; ?>" alt="Current Logo" style="max-height: 100px;">
                        <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No logo selected</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="text" name="site_logo" id="logo_path" value="<?php echo htmlspecialchars($logo); ?>" 
                           class="form-control" readonly placeholder="Select logo from media">
                    
                    <button type="button" class="btn btn-secondary" onclick="openMediaPicker('logo_path')">
                        <i class="fas fa-images"></i> Choose from Media
                    </button>
                </div>
            </div>

            <!-- Hero Image Selection -->
            <div>
                <h3>Hero Section Image</h3>
                <div class="image-selector">
                    <div class="current-image">
                        <?php if ($hero_image): ?>
                        <img src="../../<?php echo $hero_image; ?>" alt="Hero Image" style="max-height: 200px;">
                        <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No hero image selected</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="text" name="hero_image" id="hero_path" value="<?php echo htmlspecialchars($hero_image); ?>" 
                           class="form-control" readonly placeholder="Select hero image from media">
                    
                    <button type="button" class="btn btn-secondary" onclick="openMediaPicker('hero_path')">
                        <i class="fas fa-images"></i> Choose from Media
                    </button>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="media.php" class="btn btn-secondary">
                <i class="fas fa-upload"></i> Upload New Images
            </a>
        </div>
    </form>

    <!-- Quick tip -->
    <div class="content-section" style="background: #f0f9ff; border-left: 4px solid var(--primary-color);">
        <h3><i class="fas fa-lightbulb"></i> Quick Tip</h3>
        <p>Upload images in the Media Manager first, then select them here. Recommended sizes:</p>
        <ul>
            <li><strong>Logo:</strong> 200x200px (square, transparent background PNG)</li>
            <li><strong>Hero Image:</strong> 1920x1080px (wide, high quality)</li>
        </ul>
    </div>
</div>

<!-- Media Picker Modal -->
<div id="mediaPickerModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Select Image</h<h2>Select Image</h2>
            <span class="close" onclick="document.getElementById('mediaPickerModal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body" style="padding: 30px;">
            <div class="media-picker-grid">
                <?php if (empty($media_files)): ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>No Images Available</h3>
                    <p>Upload images in the Media Manager first</p>
                    <a href="media.php" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Go to Media Manager
                    </a>
                </div>
                <?php else: ?>
                    <?php foreach ($media_files as $file): ?>
                    <div class="media-picker-item" onclick="selectImage('<?php echo $file['file_path']; ?>')">
                        <img src="../../<?php echo $file['file_path']; ?>" alt="<?php echo htmlspecialchars($file['original_name']); ?>">
                        <div class="media-picker-name"><?php echo htmlspecialchars($file['original_name']); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.image-selector {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.current-image {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 20px;
    background: #f8fafc;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.current-image img {
    max-width: 100%;
    border-radius: 8px;
}

.no-image {
    text-align: center;
    color: #94a3b8;
}

.no-image i {
    font-size: 48px;
    margin-bottom: 10px;
}

.no-image p {
    margin: 0;
    font-size: 14px;
}

.media-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    max-height: 500px;
    overflow-y: auto;
}

.media-picker-item {
    cursor: pointer;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.media-picker-item:hover {
    border-color: var(--primary-color);
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.media-picker-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.media-picker-name {
    padding: 8px;
    font-size: 12px;
    color: #64748b;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-body {
    max-height: 600px;
    overflow-y: auto;
}
</style>

<script>
let currentInputField = null;

function openMediaPicker(inputId) {
    currentInputField = inputId;
    document.getElementById('mediaPickerModal').style.display = 'block';
}

function selectImage(imagePath) {
    if (currentInputField) {
        document.getElementById(currentInputField).value = imagePath;
        
        // Update preview
        const container = document.getElementById(currentInputField).closest('.image-selector');
        const previewDiv = container.querySelector('.current-image');
        previewDiv.innerHTML = '<img src="../../' + imagePath + '" alt="Selected Image" style="max-height: 200px; max-width: 100%;">';
        
        // Close modal
        document.getElementById('mediaPickerModal').style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>