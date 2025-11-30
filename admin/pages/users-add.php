<?php
require_once '../config.php';
$page_title = "Add Admin User";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    
    try {
        // Validate passwords match
        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match!";
            header('Location: users-add.php');
            exit;
        }
        
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch()) {
            $_SESSION['error_message'] = "Username already exists!";
            header('Location: users-add.php');
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $full_name, $email]);
        
        $_SESSION['success_message'] = "Admin user added successfully!";
        header('Location: users-list.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error adding admin user: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-user-plus"></i> Add Admin User</h1>
        <p>Create a new admin user account</p>
    </div>
    <a href="users-list.php" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
</div>

<?php showMessages(); ?>

<div class="table-container" style="padding: 25px;">
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Admin User
            </button>
            <a href="users-list.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
