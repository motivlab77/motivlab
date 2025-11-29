<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: portfolio.php');
    exit;
}

$portfolio_id = $_GET['id'];

// Get portfolio item
$stmt = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
$stmt->execute([$portfolio_id]);
$portfolio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$portfolio) {
    header('Location: portfolio.php');
    exit;
}

$page_title = "Gallery: " . $portfolio['title'];
include '../includes/header.php';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gallery_images'])) {
    $uploaded_files = 0;
    $errors = [];
    
    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['gallery_images']['error'][$key] === 0) {
            $file = [
                'name' => $_FILES['gallery_images']['name'][$key],
                'tmp_name' => $tmp_name,
                'size' => $_FILES['gallery_images']['size'][$key],
                'type' => $_FILES['gallery_images']['type'][$key]
            ];
            
            $uploadResult = uploadImage($file);
            if ($uploadResult['success']) {
                try {
                    // Get the next display order
                    $orderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 FROM portfolio_gallery WHERE portfolio_id = ?");
                    $orderStmt->execute([$portfolio_id]);
                    $display_order = $orderStmt->fetchColumn();
                    
                    $insertStmt = $conn->prepare("INSERT INTO portfolio_gallery (portfolio_id, image_path, display_order) VALUES (?, ?, ?)");
                    $insertStmt->execute([$portfolio_id, $uploadResult['filename'], $display_order]);
                    $uploaded_files++;
                } catch(PDOException $e) {
                    $errors[] = "Error saving image: " . $e->getMessage();
                }
            } else {
                $errors[] = $uploadResult['message'] . " (" . $file['name'] . ")";
            }
        }
    }
    
    if ($uploaded_files > 0) {
        $_SESSION['success_message'] = "Successfully uploaded $uploaded_files images!";
    }
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
    
    header("Location: portfolio-gallery.php?id=$portfolio_id");
    exit;
}

// Handle delete image
if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
    $image_id = $_GET['delete_image'];
    
    try {
        // Get image filename to delete from server
        $stmt = $conn->prepare("SELECT image_path FROM portfolio_gallery WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            // Delete from database
            $deleteStmt = $conn->prepare("DELETE FROM portfolio_gallery WHERE id = ?");
            $deleteStmt->execute([$image_id]);
            
            // Delete file from server
            $file_path = "../assets/images/uploads/" . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            $_SESSION['success_message'] = "Image deleted successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting image: " . $e->getMessage();
    }
    
    header("Location: portfolio-gallery.php?id=$portfolio_id");
    exit;
}

// Handle reordering
if (isset($_POST['reorder'])) {
    $order = $_POST['order'];
    
    try {
        foreach ($order as $position => $image_id) {
            $stmt = $conn->prepare("UPDATE portfolio_gallery SET display_order = ? WHERE id = ?");
            $stmt->execute([$position + 1, $image_id]);
        }
        $_SESSION['success_message'] = "Gallery order updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating order: " . $e->getMessage();
    }
    
    header("Location: portfolio-gallery.php?id=$portfolio_id");
    exit;
}

// Handle caption update
if (isset($_POST['update_caption'])) {
    $image_id = $_POST['image_id'];
    $caption = sanitizeInput($_POST['caption']);
    
    try {
        $stmt = $conn->prepare("UPDATE portfolio_gallery SET caption = ? WHERE id = ?");
        $stmt->execute([$caption, $image_id]);
        $_SESSION['success_message'] = "Caption updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating caption: " . $e->getMessage();
    }
    
    header("Location: portfolio-gallery.php?id=$portfolio_id");
    exit;
}

// Get gallery images
$stmt = $conn->prepare("SELECT * FROM portfolio_gallery WHERE portfolio_id = ? ORDER BY display_order ASC");
$stmt->execute([$portfolio_id]);
$gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Portfolio Gallery</h1>
                <p>Manage images for: <strong><?php echo htmlspecialchars($portfolio['title']); ?></strong></p>
            </div>
            <div>
                <a href="portfolio-edit.php?id=<?php echo $portfolio_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Portfolio
                </a>
            </div>
        </div>
    </div>

    <!-- Upload Section -->
    <div class="content-section">
        <h3>Upload Images</h3>
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <h4>Drag & Drop Images Here</h4>
                <p>or click to browse</p>
                <input type="file" id="gallery_images" name="gallery_images[]" multiple 
                       accept="image/*" style="display: none;" onchange="handleFileSelect(this.files)">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('gallery_images').click()">
                    <i class="fas fa-folder-open"></i> Choose Files
                </button>
            </div>
            <div id="fileList" class="file-list" style="display: none;">
                <h5>Selected Files:</h5>
                <ul id="selectedFiles"></ul>
            </div>
            <div class="upload-actions">
                <button type="submit" class="btn btn-primary" id="uploadBtn" style="display: none;">
                    <i class="fas fa-upload"></i> Upload Images
                </button>
                <div class="form-text">
                    <strong>Requirements:</strong> JPG, PNG, GIF, or WEBP format. Max 5MB per image.
                </div>
            </div>
        </form>
    </div>

    <!-- Gallery Images -->
    <div class="content-section">
        <div class="section-header">
            <h3>Gallery Images (<?php echo count($gallery_images); ?>)</h3>
            <?php if (!empty($gallery_images)): ?>
            <form method="POST" class="reorder-form">
                <input type="hidden" name="reorder" value="1">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Save Order
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (empty($gallery_images)): ?>
        <div class="empty-state">
            <i class="fas fa-images" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h4>No Images Yet</h4>
            <p>Upload some images to showcase this portfolio item.</p>
        </div>
        <?php else: ?>
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($gallery_images as $image): ?>
            <div class="gallery-item" data-id="<?php echo $image['id']; ?>">
                <div class="gallery-image">
                    <img src="../assets/images/uploads/<?php echo htmlspecialchars($image['image_path']); ?>" 
                         alt="Gallery image">
                    <div class="gallery-overlay">
                        <div class="gallery-actions">
                            <button type="button" class="btn-icon" onclick="editCaption(<?php echo $image['id']; ?>, '<?php echo htmlspecialchars($image['caption'] ?? ''); ?>')" title="Edit Caption">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?id=<?php echo $portfolio_id; ?>&delete_image=<?php echo $image['id']; ?>" 
                               class="btn-icon" title="Delete"
                               onclick="return confirm('Are you sure you want to delete this image?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="gallery-handle" title="Drag to reorder">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                </div>
                <div class="gallery-info">
                    <div class="image-name"><?php echo htmlspecialchars($image['image_path']); ?></div>
                    <?php if ($image['caption']): ?>
                    <div class="image-caption"><?php echo htmlspecialchars($image['caption']); ?></div>
                    <?php else: ?>
                    <div class="image-caption empty">No caption</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Caption Edit Modal -->
<div id="captionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Image Caption</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="captionForm">
                <input type="hidden" name="update_caption" value="1">
                <input type="hidden" name="image_id" id="modalImageId">
                <div class="form-group">
                    <label for="modalCaption" class="form-label">Caption</label>
                    <textarea id="modalCaption" name="caption" class="form-control" rows="3" 
                              placeholder="Enter image caption..."></textarea>
                    <div class="form-text">This caption will be displayed with the image.</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveCaption()">Save Caption</button>
        </div>
    </div>
</div>

<style>
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.upload-form {
    margin-bottom: 30px;
}

.upload-area {
    border: 3px dashed #cbd5e1;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    background: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #667eea;
    background: #f1f5f9;
}

.upload-area.dragover {
    border-color: #667eea;
    background: #e0e7ff;
}

.upload-area i {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 15px;
}

.upload-area h4 {
    margin-bottom: 10px;
    color: #374151;
}

.upload-area p {
    color: #64748b;
    margin-bottom: 20px;
}

.file-list {
    margin-top: 20px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
}

.file-list h5 {
    margin-bottom: 10px;
    color: #374151;
}

.file-list ul {
    list-style: none;
    padding: 0;
}

.file-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    margin-bottom: 5px;
}

.file-name {
    flex: 1;
}

.file-size {
    color: #64748b;
    font-size: 12px;
}

.upload-actions {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.gallery-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.gallery-item.sortable-ghost {
    opacity: 0.4;
}

.gallery-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-actions {
    display: flex;
    gap: 10px;
}

.gallery-handle {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px;
    border-radius: 4px;
    cursor: move;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-handle {
    opacity: 1;
}

.gallery-info {
    padding: 15px;
}

.image-name {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 5px;
    word-break: break-all;
}

.image-caption {
    font-size: 14px;
    color: #374151;
    line-height: 1.4;
}

.image-caption.empty {
    color: #94a3b8;
    font-style: italic;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.reorder-form {
    margin: 0;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: auto;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .upload-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
// File upload handling
let selectedFiles = [];

function handleFileSelect(files) {
    selectedFiles = Array.from(files);
    updateFileList();
    
    if (selectedFiles.length > 0) {
        document.getElementById('uploadBtn').style.display = 'inline-flex';
    } else {
        document.getElementById('uploadBtn').style.display = 'none';
    }
}

function updateFileList() {
    const fileList = document.getElementById('selectedFiles');
    const fileListContainer = document.getElementById('fileList');
    
    fileList.innerHTML = '';
    
    if (selectedFiles.length > 0) {
        fileListContainer.style.display = 'block';
        
        selectedFiles.forEach((file, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="file-name">${file.name}</span>
                <span class="file-size">${formatFileSize(file.size)}</span>
            `;
            fileList.appendChild(li);
        });
    } else {
        fileListContainer.style.display = 'none';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('gallery_images');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(files);
    }
});

// Gallery reordering with Sortable
document.addEventListener('DOMContentLoaded', function() {
    const galleryGrid = document.getElementById('galleryGrid');
    
    if (galleryGrid) {
        new Sortable(galleryGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.gallery-handle',
            onEnd: function(evt) {
                updateHiddenOrderInput();
            }
        });
    }
});

function updateHiddenOrderInput() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    const order = [];
    
    galleryItems.forEach(item => {
        order.push(item.getAttribute('data-id'));
    });
    
    // Remove existing hidden inputs
    document.querySelectorAll('input[name="order[]"]').forEach(input => input.remove());
    
    // Add new hidden inputs
    const form = document.querySelector('.reorder-form');
    order.forEach((id, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order[]';
        input.value = id;
        form.appendChild(input);
    });
}

// Modal functionality
function editCaption(imageId, currentCaption) {
    document.getElementById('modalImageId').value = imageId;
    document.getElementById('modalCaption').value = currentCaption;
    document.getElementById('captionModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('captionModal').style.display = 'none';
}

function saveCaption() {
    document.getElementById('captionForm').submit();
}

// Close modal when clicking outside
document.getElementById('captionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Initialize hidden order inputs on page load
document.addEventListener('DOMContentLoaded', function() {
    updateHiddenOrderInput();
});
</script>

<?php include '../includes/footer.php'; ?>