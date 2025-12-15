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

$page_title = "Pricing: " . $portfolio['title'];
include '../includes/header.php';

// Handle add pricing plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plan'])) {
    $plan_name = sanitizeInput($_POST['plan_name']);
    $price = sanitizeInput($_POST['price']);
    $features = $_POST['features'];
    $delivery_time = sanitizeInput($_POST['delivery_time']);
    $support_level = sanitizeInput($_POST['support_level']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $errors = [];

    // Validate inputs
    if (empty($plan_name)) $errors[] = "Plan name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
    if (empty($features)) $errors[] = "Features are required";

    if (empty($errors)) {
        try {
            // Get the next display order
            $orderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 FROM pricing_plans WHERE portfolio_id = ?");
            $orderStmt->execute([$portfolio_id]);
            $display_order = $orderStmt->fetchColumn();

            $stmt = $conn->prepare("
                INSERT INTO pricing_plans 
                (portfolio_id, plan_name, price, features, delivery_time, support_level, is_featured, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $portfolio_id, $plan_name, $price, $features, $delivery_time, 
                $support_level, $is_featured, $display_order
            ]);

            $_SESSION['success_message'] = "Pricing plan added successfully!";
            header("Location: portfolio-pricing.php?id=$portfolio_id");
            exit;
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Error adding pricing plan: " . $e->getMessage();
            header("Location: portfolio-pricing.php?id=$portfolio_id");
            exit;
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: portfolio-pricing.php?id=$portfolio_id");
        exit;
    }
}

// Handle update pricing plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_plan'])) {
    $plan_id = $_POST['plan_id'];
    $plan_name = sanitizeInput($_POST['plan_name']);
    $price = sanitizeInput($_POST['price']);
    $features = $_POST['features'];
    $delivery_time = sanitizeInput($_POST['delivery_time']);
    $support_level = sanitizeInput($_POST['support_level']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $errors = [];

    // Validate inputs
    if (empty($plan_name)) $errors[] = "Plan name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";
    if (empty($features)) $errors[] = "Features are required";

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE pricing_plans 
                SET plan_name = ?, price = ?, features = ?, delivery_time = ?, 
                    support_level = ?, is_featured = ?
                WHERE id = ? AND portfolio_id = ?
            ");
            $stmt->execute([
                $plan_name, $price, $features, $delivery_time, 
                $support_level, $is_featured, $plan_id, $portfolio_id
            ]);

            $_SESSION['success_message'] = "Pricing plan updated successfully!";
            header("Location: portfolio-pricing.php?id=$portfolio_id");
            exit;
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Error updating pricing plan: " . $e->getMessage();
            header("Location: portfolio-pricing.php?id=$portfolio_id");
            exit;
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: portfolio-pricing.php?id=$portfolio_id");
        exit;
    }
}

// Handle delete pricing plan
if (isset($_GET['delete_plan']) && is_numeric($_GET['delete_plan'])) {
    $plan_id = $_GET['delete_plan'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM pricing_plans WHERE id = ? AND portfolio_id = ?");
        $stmt->execute([$plan_id, $portfolio_id]);
        
        $_SESSION['success_message'] = "Pricing plan deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting pricing plan: " . $e->getMessage();
    }
    
    header("Location: portfolio-pricing.php?id=$portfolio_id");
    exit;
}

// Handle reordering
if (isset($_POST['reorder'])) {
    $order = $_POST['order'];
    
    try {
        foreach ($order as $position => $plan_id) {
            $stmt = $conn->prepare("UPDATE pricing_plans SET display_order = ? WHERE id = ? AND portfolio_id = ?");
            $stmt->execute([$position + 1, $plan_id, $portfolio_id]);
        }
        $_SESSION['success_message'] = "Pricing plans order updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating order: " . $e->getMessage();
    }
    
    header("Location: portfolio-pricing.php?id=$portfolio_id");
    exit;
}

// Get pricing plans
$stmt = $conn->prepare("SELECT * FROM pricing_plans WHERE portfolio_id = ? ORDER BY display_order ASC");
$stmt->execute([$portfolio_id]);
$pricing_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Pricing Management</h1>
                <p>Manage pricing plans for: <strong><?php echo htmlspecialchars($portfolio['title']); ?></strong></p>
            </div>
            <div>
                <a href="portfolio-edit.php?id=<?php echo $portfolio_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Portfolio
                </a>
            </div>
        </div>
    </div>

    <!-- Add Pricing Plan Form -->
    <div class="content-section">
        <h3>Add New Pricing Plan</h3>
        <form method="POST" class="pricing-form">
            <input type="hidden" name="add_plan" value="1">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="plan_name" class="form-label">Plan Name *</label>
                    <input type="text" id="plan_name" name="plan_name" class="form-control" 
                           placeholder="e.g., Basic, Standard, Premium" required>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Price (₦) *</label>
                    <input type="number" id="price" name="price" class="form-control" 
                           step="0.01" min="0" placeholder="250000" required>
                </div>

                <div class="form-group">
                    <label for="delivery_time" class="form-label">Delivery Time</label>
                    <input type="text" id="delivery_time" name="delivery_time" class="form-control" 
                           placeholder="e.g., 7-14 days, 2 weeks">
                </div>

                <div class="form-group">
                    <label for="support_level" class="form-label">Support Level</label>
                    <input type="text" id="support_level" name="support_level" class="form-control" 
                           placeholder="e.g., Basic, Priority, 24/7">
                </div>
            </div>

            <div class="form-group">
                <label for="features" class="form-label">Features *</label>
                <textarea id="features" name="features" class="form-control" rows="6" 
                          placeholder="Enter features, one per line&#10;• Feature 1&#10;• Feature 2&#10;• Feature 3" required></textarea>
                <div class="form-text">Enter one feature per line. Use bullet points or checkmarks.</div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1">
                    <span class="checkmark"></span>
                    Mark as featured plan (will be highlighted)
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Pricing Plan
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Pricing Plans -->
    <div class="content-section">
        <div class="section-header">
            <h3>Existing Pricing Plans (<?php echo count($pricing_plans); ?>)</h3>
            <?php if (!empty($pricing_plans)): ?>
            <form method="POST" class="reorder-form">
                <input type="hidden" name="reorder" value="1">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Save Order
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (empty($pricing_plans)): ?>
        <div class="empty-state">
            <i class="fas fa-tags" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h4>No Pricing Plans Yet</h4>
            <p>Add pricing plans to showcase different packages for this portfolio item.</p>
        </div>
        <?php else: ?>
        <div class="pricing-grid" id="pricingGrid">
            <?php foreach ($pricing_plans as $plan): ?>
            <div class="pricing-card <?php echo $plan['is_featured'] ? 'featured' : ''; ?>" data-id="<?php echo $plan['id']; ?>">
                <?php if ($plan['is_featured']): ?>
                <div class="featured-badge">Most Popular</div>
                <?php endif; ?>
                
                <div class="pricing-header">
                    <h4><?php echo htmlspecialchars($plan['plan_name']); ?></h4>
                    <div class="price">₦<?php echo number_format($plan['price'], 0); ?></div>
                </div>

                <div class="pricing-details">
                    <?php if ($plan['delivery_time']): ?>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo htmlspecialchars($plan['delivery_time']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($plan['support_level']): ?>
                    <div class="detail-item">
                        <i class="fas fa-headset"></i>
                        <span><?php echo htmlspecialchars($plan['support_level']); ?> Support</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pricing-features">
                    <?php
                    $features = explode("\n", $plan['features']);
                    foreach ($features as $feature):
                        $feature = trim($feature);
                        if (!empty($feature)):
                    ?>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span><?php echo htmlspecialchars($feature); ?></span>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>

                <div class="pricing-actions">
                    <button type="button" class="btn-icon" onclick="editPlan(<?php echo $plan['id']; ?>)" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="?id=<?php echo $portfolio_id; ?>&delete_plan=<?php echo $plan['id']; ?>" 
                       class="btn-icon" title="Delete"
                       onclick="return confirm('Are you sure you want to delete this pricing plan?')">
                        <i class="fas fa-trash"></i>
                    </a>
                    <div class="drag-handle" title="Drag to reorder">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Plan Modal -->
<div id="editPlanModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Pricing Plan</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editPlanForm">
                <input type="hidden" name="update_plan" value="1">
                <input type="hidden" name="plan_id" id="modalPlanId">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="modalPlanName" class="form-label">Plan Name *</label>
                        <input type="text" id="modalPlanName" name="plan_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="modalPrice" class="form-label">Price (₦) *</label>
                        <input type="number" id="modalPrice" name="price" class="form-control" 
                               step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="modalDeliveryTime" class="form-label">Delivery Time</label>
                        <input type="text" id="modalDeliveryTime" name="delivery_time" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="modalSupportLevel" class="form-label">Support Level</label>
                        <input type="text" id="modalSupportLevel" name="support_level" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="modalFeatures" class="form-label">Features *</label>
                    <textarea id="modalFeatures" name="features" class="form-control" rows="6" required></textarea>
                    <div class="form-text">Enter one feature per line.</div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" id="modalIsFeatured" value="1">
                        <span class="checkmark"></span>
                        Mark as featured plan
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="savePlan()">Update Plan</button>
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

.pricing-form {
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
}

.checkbox-label input {
    margin-right: 10px;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s ease;
}

.checkbox-label input:checked + .checkmark {
    background-color: #667eea;
    border-color: #667eea;
}

.checkbox-label input:checked + .checkmark::after {
    content: '✓';
    color: white;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: bold;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.pricing-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 25px;
    position: relative;
    transition: all 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.pricing-card.featured {
    border-color: #667eea;
    background: linear-gradient(135deg, #fff5eb 0%, #ffe8d6 100%);
    transform: scale(1.02);
}

.pricing-card.featured:hover {
    transform: scale(1.02) translateY(-5px);
}

.pricing-card.sortable-ghost {
    opacity: 0.4;
}

.featured-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #667eea;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pricing-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.pricing-header h4 {
    margin: 0 0 10px 0;
    color: #1f2937;
    font-size: 20px;
    font-weight: 700;
}

.price {
    font-size: 32px;
    font-weight: 800;
    color: #667eea;
}

.pricing-details {
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 14px;
}

.detail-item i {
    color: #667eea;
    width: 16px;
}

.pricing-features {
    margin-bottom: 25px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.feature-item i {
    color: #10b981;
    margin-top: 2px;
    flex-shrink: 0;
}

.pricing-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.drag-handle {
    color: #cbd5e1;
    cursor: move;
    padding: 5px;
    transition: color 0.3s ease;
}

.pricing-card:hover .drag-handle {
    color: #64748b;
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
    max-width: 600px;
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
    
    .pricing-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .pricing-actions {
        flex-wrap: wrap;
        gap: 10px;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
// Pricing plans reordering with Sortable
document.addEventListener('DOMContentLoaded', function() {
    const pricingGrid = document.getElementById('pricingGrid');
    
    if (pricingGrid) {
        new Sortable(pricingGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            onEnd: function(evt) {
                updateHiddenOrderInput();
            }
        });
        
        updateHiddenOrderInput();
    }
});

function updateHiddenOrderInput() {
    const pricingCards = document.querySelectorAll('.pricing-card');
    const order = [];
    
    pricingCards.forEach(card => {
        order.push(card.getAttribute('data-id'));
    });
    
    // Remove existing hidden inputs
    document.querySelectorAll('input[name="order[]"]').forEach(input => input.remove());
    
    // Add new hidden inputs
    const form = document.querySelector('.reorder-form');
    if (form) {
        order.forEach((id, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order[]';
            input.value = id;
            form.appendChild(input);
        });
    }
}

// Modal functionality for editing plans
function editPlan(planId) {
    // In a real application, you would fetch the plan data via AJAX
    // For now, we'll use the data from the card
    const card = document.querySelector(`.pricing-card[data-id="${planId}"]`);
    
    if (card) {
        const planName = card.querySelector('.pricing-header h4').textContent;
        const price = card.querySelector('.price').textContent.replace('₦', '').replace(/,/g, '');
        const features = Array.from(card.querySelectorAll('.feature-item span'))
            .map(span => span.textContent)
            .join('\n');
        
        // Get delivery time and support level (these might need to be stored in data attributes)
        const deliveryTime = ''; // You would get this from data attributes
        const supportLevel = ''; // You would get this from data attributes
        const isFeatured = card.classList.contains('featured');
        
        document.getElementById('modalPlanId').value = planId;
        document.getElementById('modalPlanName').value = planName;
        document.getElementById('modalPrice').value = price;
        document.getElementById('modalDeliveryTime').value = deliveryTime;
        document.getElementById('modalSupportLevel').value = supportLevel;
        document.getElementById('modalFeatures').value = features;
        document.getElementById('modalIsFeatured').checked = isFeatured;
        
        document.getElementById('editPlanModal').style.display = 'flex';
    }
}

function closeModal() {
    document.getElementById('editPlanModal').style.display = 'none';
}

function savePlan() {
    document.getElementById('editPlanForm').submit();
}

// Close modal when clicking outside
document.getElementById('editPlanModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Format price input
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    }
    
    const modalPriceInput = document.getElementById('modalPrice');
    if (modalPriceInput) {
        modalPriceInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>