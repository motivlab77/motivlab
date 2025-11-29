<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Only super admins can access user management
if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = $_GET['id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit;
}

$page_title = "Edit User: " . $user['full_name'];
include '../includes/header.php';

// Initialize variables
$username = $user['username'];
$email = $user['email'];
$full_name = $user['full_name'];
$role = $user['role'];
$status = $user['status'];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $full_name = sanitizeInput($_POST['full_name']);
    $role = sanitizeInput($_POST['role']);
    $status = sanitizeInput($_POST['status']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($full_name)) $errors[] = "Full name is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    
    if ($password && $password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if ($password && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    // Check if username or email already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $id]);
    if ($stmt->fetch()) {
        $errors[] = "Username or email already exists";
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            if ($password) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE admin_users 
                    SET username = ?, email = ?, password = ?, full_name = ?, role = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $role, $status, $id]);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("
                    UPDATE admin_users 
                    SET username = ?, email = ?, full_name = ?, role = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $full_name, $role, $status, $id]);
            }

            $_SESSION['success_message'] = "User updated successfully!";
            header('Location: users.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Edit User</h1>
                <p>Update user account information and permissions</p>
            </div>
            <div>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
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

    <form method="POST" class="form-container">
        <div class="form-grid">
            <!-- Left Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Account Information</h3>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($full_name); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Security & Permissions</h3>

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               minlength="6" placeholder="Leave blank to keep current password">
                        <div class="form-text">Minimum 6 characters. Leave empty to keep current password.</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" placeholder                               class="form-control" placeholder="Confirm new password">
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="super_admin" <?php echo $role === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>Editor</option>
                        </select>
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
        </div>

        <!-- User Activity Information -->
        <div class="content-section">
            <h3>User Activity</h3>
            <div class="activity-info">
                <div class="activity-item">
                    <label>Account Created:</label>
                    <span><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></span>
                </div>
                <?php if ($user['last_login']): ?>
                <div class="activity-item">
                    <label>Last Login:</label>
                    <span><?php echo date('F j, Y \a\t g:i A', strtotime($user['last_login'])); ?></span>
                </div>
                <?php else: ?>
                <div class="activity-item">
                    <label>Last Login:</label>
                    <span class="text-muted">Never logged in</span>
                </div>
                <?php endif; ?>
                <div class="activity-item">
                    <label>User ID:</label>
                    <span>#<?php echo $user['id']; ?></span>
                </div>
            </div>
        </div>

        <!-- Role Permissions Preview -->
        <div class="content-section">
            <h3>Role Permissions</h3>
            <div class="permissions-preview">
                <div class="permission-item" data-role="super_admin">
                    <h4><i class="fas fa-crown"></i> Super Admin</h4>
                    <p>Full access to all system features including user management and site settings.</p>
                </div>
                <div class="permission-item" data-role="admin">
                    <h4><i class="fas fa-user-shield"></i> Admin</h4>
                    <p>Can manage content (portfolio, blog, testimonials, messages) but cannot manage users or change site settings.</p>
                </div>
                <div class="permission-item" data-role="editor">
                    <h4><i class="fas fa-edit"></i> Editor</h4>
                    <p>Can only create and edit blog posts. No access to portfolio, users, or settings.</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update User
            </button>
            <a href="users.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            
            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
            <div style="margin-left: auto; display: flex; gap: 10px;">
                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                    <i class="fas fa-trash"></i> Delete User
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.activity-info {
    display: grid;
    gap: 15px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item label {
    font-weight: 600;
    color: #374151;
}

.activity-item span {
    color: #6b7280;
}

.text-muted {
    color: #9ca3af !important;
    font-style: italic;
}

.permissions-preview {
    display: none;
    gap: 20px;
    margin-top: 20px;
}

.permission-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
}

.permission-item.active {
    background: #f0f9ff;
    border-color: #667eea;
}

.permission-item h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 10px 0;
    color: #1f2937;
}

.permission-item p {
    margin: 0;
    color: #64748b;
    line-height: 1.5;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
    align-items: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions > * {
        width: 100%;
    }
}
</style>

<script>
// Show permissions preview based on selected role
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const permissionsPreview = document.querySelector('.permissions-preview');
    const permissionItems = document.querySelectorAll('.permission-item');

    function updatePermissionsPreview() {
        const selectedRole = roleSelect.value;
        
        if (selectedRole) {
            permissionsPreview.style.display = 'grid';
            permissionsPreview.style.gridTemplateColumns = 'repeat(auto-fit, minmax(300px, 1fr))';
            
            permissionItems.forEach(item => {
                if (item.getAttribute('data-role') === selectedRole) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        } else {
            permissionsPreview.style.display = 'none';
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updatePermissionsPreview);
        updatePermissionsPreview(); // Initialize on page load
    }

    // Password strength and match checking
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.style.borderColor = '#ef4444';
            confirmPasswordInput.style.backgroundColor = '#fef2f2';
        } else if (confirmPassword) {
            confirmPasswordInput.style.borderColor = '#10b981';
            confirmPasswordInput.style.backgroundColor = '#f0fdf4';
        } else {
            confirmPasswordInput.style.borderColor = '#e2e8f0';
            confirmPasswordInput.style.backgroundColor = '';
        }
    }
    
    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }

    // Prevent self-demotion warning
    const currentUserId = <?php echo $_SESSION['admin_id']; ?>;
    const editingUserId = <?php echo $user['id']; ?>;
    
    if (currentUserId === editingUserId) {
        roleSelect.addEventListener('change', function() {
            if (this.value !== 'super_admin') {
                if (!confirm('Warning: Changing your own role from Super Admin may limit your access to certain features. Are you sure?')) {
                    this.value = 'super_admin';
                    updatePermissionsPreview();
                }
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>