<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Testimonials Management";
include '../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        // Get testimonial to check for client photo
        $stmt = $conn->prepare("SELECT client_photo FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete testimonial
        $deleteStmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        // Delete client photo if exists
        if ($testimonial && $testimonial['client_photo']) {
            $file_path = "../assets/images/uploads/" . $testimonial['client_photo'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $_SESSION['success_message'] = "Testimonial deleted successfully!";
        header('Location: testimonials.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting testimonial: " . $e->getMessage();
        header('Location: testimonials.php');
        exit;
    }
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    
    try {
        $stmt = $conn->prepare("UPDATE testimonials SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Testimonial status updated successfully!";
        header('Location: testimonials.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating testimonial status: " . $e->getMessage();
        header('Location: testimonials.php');
        exit;
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_testimonials = $_POST['selected_testimonials'] ?? [];
    
    if (!empty($selected_testimonials)) {
        try {
            $placeholders = str_repeat('?,', count($selected_testimonials) - 1) . '?';
            
            if ($bulk_action === 'delete') {
                // Get client photos first
                $stmt = $conn->prepare("SELECT client_photo FROM testimonials WHERE id IN ($placeholders)");
                $stmt->execute($selected_testimonials);
                $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Delete testimonials
                $deleteStmt = $conn->prepare("DELETE FROM testimonials WHERE id IN ($placeholders)");
                $deleteStmt->execute($selected_testimonials);
                
                // Delete client photos
                foreach ($testimonials as $testimonial) {
                    if ($testimonial['client_photo']) {
                        $file_path = "../assets/images/uploads/" . $testimonial['client_photo'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
                
                $_SESSION['success_message'] = count($selected_testimonials) . " testimonials deleted successfully!";
            } elseif ($bulk_action === 'activate') {
                $stmt = $conn->prepare("UPDATE testimonials SET status = 'active' WHERE id IN ($placeholders)");
                $stmt->execute($selected_testimonials);
                $_SESSION['success_message'] = count($selected_testimonials) . " testimonials activated!";
            } elseif ($bulk_action === 'deactivate') {
                $stmt = $conn->prepare("UPDATE testimonials SET status = 'inactive' WHERE id IN ($placeholders)");
                $stmt->execute($selected_testimonials);
                $_SESSION['success_message'] = count($selected_testimonials) . " testimonials deactivated!";
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Error performing bulk action: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "No testimonials selected!";
    }
    
    header('Location: testimonials.php');
    exit;
}

// Get all testimonials
$stmt = $conn->query("SELECT * FROM testimonials ORDER BY display_order ASC, created_at DESC");
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Testimonials Management</h1>
        <p>Manage client testimonials and reviews</p>
    </div>

    <!-- Quick Actions -->
    <div class="content-section">
        <div class="section-header">
            <h2>All Testimonials</h2>
            <div>
                <a href="testimonials-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Testimonial
                </a>
            </div>
        </div>

        <!-- Bulk Actions -->
        <form method="POST" class="bulk-actions" id="bulkActionsForm">
            <div class="bulk-controls">
                <select name="bulk_action" class="form-control">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-secondary" id="applyBulkAction">Apply</button>
            </div>

            <!-- Search and Filter -->
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" id="testimonialsSearch" placeholder="Search testimonials...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-controls">
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select id="ratingFilter">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
            </div>

            <!-- Testimonials Grid -->
            <div class="testimonials-grid" id="testimonialsGrid">
                <?php if (empty($testimonials)): ?>
                <div class="empty-state">
                    <i class="fas fa-star" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h4>No Testimonials Yet</h4>
                    <p>Add client testimonials to build trust with your visitors.</p>
                    <a href="testimonials-add.php" class="btn btn-primary">Add First Testimonial</a>
                </div>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card" data-id="<?php echo $testimonial['id']; ?>">
                        <div class="testimonial-header">
                            <div class="client-info">
                                <?php if ($testimonial['client_photo']): ?>
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($testimonial['client_photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" 
                                     class="client-photo">
                                <?php else: ?>
                                <div class="client-photo placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php endif; ?>
                                <div class="client-details">
                                    <h4><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($testimonial['business_name']); ?></p>
                                </div>
                            </div>
                            <div class="testimonial-meta">
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="status-badge badge-<?php echo $testimonial['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($testimonial['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="testimonial-content">
                            <p><?php echo htmlspecialchars($testimonial['review_text']); ?></p>
                        </div>

                        <div class="testimonial-footer">
                            <div class="testimonial-actions">
                                <input type="checkbox" name="selected_testimonials[]" value="<?php echo $testimonial['id']; ?>" class testimonial-checkbox">
                                <a href="testimonials-edit.php?id=<?php echo $testimonial['id']; ?>" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?toggle_status=<?php echo $testimonial['id']; ?>" class="btn-icon" title="<?php echo $testimonial['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="?delete=<?php echo $testimonial['id']; ?>" class="btn-icon" title="Delete" 
                                   onclick="return confirm('Are you sure you want to delete this testimonial?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <div class="drag-handle" title="Drag to reorder">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                            </div>
                            <div class="testimonial-date">
                                Added <?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<style>
.bulk-actions {
    margin-bottom: 20px;
}

.bulk-controls {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.bulk-controls select {
    min-width: 150px;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 20px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.filter-controls {
    display: flex;
    gap: 10px;
}

.filter-controls select {
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    font-size: 14px;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
}

.testimonial-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
}

.testimonial-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.testimonial-card.sortable-ghost {
    opacity: 0.4;
}

.testimonial-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.client-info {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    flex: 1;
}

.client-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.client-photo.placeholder {
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 20px;
}

.client-details h4 {
    margin: 0 0 5px 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}

.client-details p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.testimonial-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.rating {
    display: flex;
    gap: 2px;
}

.rating i {
    color: #d1d5db;
    font-size: 14px;
}

.rating i.active {
    color: #f59e0b;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.testimonial-content {
    margin-bottom: 20px;
}

.testimonial-content p {
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
    font-style: italic;
}

.testimonial-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e2e8f0;
}

.testimonial-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.testimonial-checkbox {
    margin: 0;
}

.drag-handle {
    color: #cbd5e1;
    cursor: move;
    padding: 5px;
    transition: color 0.3s ease;
}

.testimonial-card:hover .drag-handle {
    color: #64748b;
}

.testimonial-date {
    font-size: 12px;
    color: #9ca3af;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

@media (max-width: 768px) {
    .table-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        max-width: none;
    }
    
    .filter-controls {
        justify-content: space-between;
    }
    
    .bulk-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .testimonial-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .testimonial-meta {
        align-items: flex-start;
    }
    
    .testimonial-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
// Bulk actions functionality
document.addEventListener('DOMContentLoaded', function() {
    const bulkActionsForm = document.getElementById('bulkActionsForm');
    const applyBulkAction = document.getElementById('applyBulkAction');

    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function(e) {
            const selectedTestimonials = document.querySelectorAll('.testimonial-checkbox:checked');
            const bulkAction = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedTestimonials.length === 0) {
                e.preventDefault();
                alert('Please select at least one testimonial.');
                return;
            }
            
            if (!bulkAction) {
                e.preventDefault();
                alert('Please select a bulk action.');
                return;
            }
            
            if (bulkAction === 'delete') {
                if (!confirm(`Are you sure you want to delete ${selectedTestimonials.length} testimonial(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    }

    // Testimonials reordering with Sortable
    const testimonialsGrid = document.getElementById('testimonialsGrid');
    
    if (testimonialsGrid) {
        new Sortable(testimonialsGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            onEnd: function(evt) {
                updateTestimonialsOrder();
            }
        });
    }

    // Filter testimonials functionality
    const searchInput = document.getElementById('testimonialsSearch');
    const statusFilter = document.getElementById('statusFilter');
    const ratingFilter = document.getElementById('ratingFilter');
    const testimonialCards = document.querySelectorAll('.testimonial-card');

    function filterTestimonials() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const ratingValue = ratingFilter.value;

        testimonialCards.forEach(card => {
            const clientName = card.querySelector('.client-details h4').textContent.toLowerCase();
            const businessName = card.querySelector('.client-details p').textContent.toLowerCase();
            const reviewText = card.querySelector('.testimonial-content p').textContent.toLowerCase();
            const status = card.querySelector('.status-badge').textContent.toLowerCase();
            const rating = card.querySelectorAll('.rating i.active').length;

            const matchesSearch = clientName.includes(searchTerm) || 
                                businessName.includes(searchTerm) || 
                                reviewText.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);
            const matchesRating = !ratingValue || rating == ratingValue;

            card.style.display = matchesSearch && matchesStatus && matchesRating ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTestimonials);
    if (statusFilter) statusFilter.addEventListener('change', filterTestimonials);
    if (ratingFilter) ratingFilter.addEventListener('change', filterTestimonials);
});

function updateTestimonialsOrder() {
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    const order = [];
    
    testimonialCards.forEach(card => {
        order.push(card.getAttribute('data-id'));
    });
    
    // In a real application, you would send this order to the server via AJAX
    console.log('New order:', order);
    // Example AJAX call:
    /*
    fetch('update-testimonials-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order: order })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Testimonials order updated successfully!', 'success');
        } else {
            showNotification('Error updating order', 'error');
        }
    });
    */
}
</script>

<?php include '../includes/footer.php'; ?>