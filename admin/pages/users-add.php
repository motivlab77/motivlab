<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Only super admins can access user management
if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

$page_title = "Add New User";
include '../includes/header.php';

// Initialize variables
$username = $email = $full_name = $role = $status = '';
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
    if (empty($password)) $errors[] = "Password is required";
    if (empty($confirm_password)) $errors[] = "Confirm password is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = "Username or email already exists";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO admin_users 
                (username, email, password, full_name, role, status) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $username, $email, $hashed_password, $full_name, $role, $status
            ]);

            $_SESSION['success_message'] = "User created successfully!";
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
        <h1>Add New User</h1>
        <p>Create a new admin user account</p>
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
                               value="<?php echo htmlspecialchars($username); ?>" required 
                               placeholder="johndoe">
                        <div class="form-text">Unique username for login</div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" required 
                               placeholder="john@example.com">
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($full_name); ?>" required 
                               placeholder="John Doe">
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="content-section">
                    <h3>Security & Permissions</h3>

                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               required minlength="6">
                        <div class="form-text">Minimum 6 characters</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="super_admin" <?php echo $role === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>Editor</option>
                        </select>
                        <div class="form-text">Choose appropriate permissions level</div>
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

        <!-- Role Permissions Preview -->
        <div class="content-section">
            <h3>Role Permissions Preview</h3>
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
                <i class="fas fa-user-plus"></i> Create User
            </button>
            <a href="users.php" class="btn btn-secondary">
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
}

@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
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

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.style.borderColor = '#ef4444';
        } else if (confirmPassword) {
            confirmPasswordInput.style.borderColor = '#10b981';
        } else {
            confirmPasswordInput.style.borderColor = '#e2e8f0';
        }
    }
    
    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
});
</script>

<?php include '../includes/footer.php'; ?>