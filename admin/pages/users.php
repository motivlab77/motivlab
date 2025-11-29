<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Only super admins can access user management
if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

$page_title = "User Management";
include '../includes/header.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prevent deleting own account
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account!";
        header('Location: users.php');
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "User deleted successfully!";
        header('Location: users.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
        header('Location: users.php');
        exit;
    }
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    
    // Prevent deactivating own account
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error_message'] = "You cannot deactivate your own account!";
        header('Location: users.php');
        exit;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE admin_users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "User status updated successfully!";
        header('Location: users.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating user status: " . $e->getMessage();
        header('Location: users.php');
        exit;
    }
}

// Get all users
$stmt = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user statistics
$total_users = count($users);
$active_users = array_filter($users, function($user) {
    return $user['status'] === 'active';
});
$super_admins = array_filter($users, function($user) {
    return $user['role'] === 'super_admin';
});
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>User Management</h1>
        <p>Manage admin users and their permissions</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #667eea;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($active_users); ?></h3>
                <p>Active Users</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($super_admins); ?></h3>
                <p>Super Admins</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_users - count($super_admins); ?></h3>
                <p>Regular Admins</p>
            </div>
        </div>
    </div>

    <!-- Users Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>All Admin Users</h2>
            <div>
                <a href="users-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="table-controls">
            <div class="search-box">
                <input type="text" id="usersSearch" placeholder="Search users...">
                <i class="fas fa-search"></i>
            </div>
            <div class="filter-controls">
                <select id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                </select>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="fas fa-users" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                            <p>No users found. <a href="users-add.php">Add your first admin user</a></p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="<?php echo $user['id'] == $_SESSION['admin_id'] ? 'current-user' : ''; ?>">
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="user-details">
                                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                        <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                        <span class="you-badge">You</span>
                                        <?php endif; ?>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $user['role'] === 'super_admin' ? 'warning' : 
                                         ($user['role'] === 'admin' ? 'primary' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                <?php echo date('M d, Y', strtotime($user['last_login'])); ?>
                                <br>
                                <small style="color: #64748b;">
                                    <?php echo date('h:i A', strtotime($user['last_login'])); ?>
                                </small>
                                <?php else: ?>
                                <span style="color: #94a3b8;">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="users-edit.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                    <a href="?toggle_status=<?php echo $user['id']; ?>" class="btn-icon" title="<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn-icon" title="Delete" 
                                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="btn-icon disabled" title="Cannot modify your own account">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span class="btn-icon disabled" title="Cannot modify your own account">
                                        <i class="fas fa-power-off"></i>
                                    </span>
                                    <span class="btn-icon disabled" title="Cannot modify your own account">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Role Permissions Guide -->
    <div class="content-section">
        <h2>Role Permissions</h2>
        <div class="permissions-grid">
            <div class="permission-card">
                <h3><i class="fas fa-crown"></i> Super Admin</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Full system access</li>
                    <li><i class="fas fa-check"></i> User management</li>
                    <li><i class="fas fa-check"></i> Site settings</li>
                    <li><i class="fas fa-check"></i> All content management</li>
                </ul>
            </div>

            <div class="permission-card">
                <h3><i class="fas fa-user-shield"></i> Admin</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Content management</li>
                    <li><i class="fas fa-check"></i> Portfolio & Blog</li>
                    <li><i class="fas fa-check"></i> Messages & Testimonials</li>
                    <li><i class="fas fa-times"></i> No user management</li>
                    <li><i class="fas fa-times"></i> No site settings</li>
                </ul>
            </div>

            <div class="permission-card">
                <h3><i class="fas fa-edit"></i> Editor</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Blog posts only</li>
                    <li><i class="fas fa-check"></i> Create & edit content</li>
                    <li><i class="fas fa-times"></i> No portfolio management</li>
                    <li><i class="fas fa-times"></i> No user management</li>
                    <li><i class="fas fa-times"></i> No settings access</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 24px;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
}

.user-details strong {
    display: block;
    margin-bottom: 2px;
    color: #1f2937;
}

.you-badge {
    background: #667eea;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    margin-left: 8px;
}

.user-email {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 2px;
}

.username {
    font-size: 12px;
    color: #94a3b8;
}

.current-user {
    background-color: #f0f9ff;
    border-left: 3px solid #667eea;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn-icon {
    width: 32px;
    height: 32px;
    font-size: 12px;
}

.btn-icon.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.permission-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
}

.permission-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.permission-card h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 18px;
}

.permission-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.permission-card li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}

.permission-card li:last-child {
    border-bottom: none;
}

.permission-card .fa-check {
    color: #10b981;
}

.permission-card .fa-times {
    color: #ef4444;
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
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .permissions-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Filter table functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('usersSearch');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('usersTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        const statusValue = statusFilter.value;

        rows.forEach(row => {
            if (row.cells.length < 6) return; // Skip empty state row
            
            const name = row.cells[0].textContent.toLowerCase();
            const email = row.cells[0].querySelector('.user-email').textContent.toLowerCase();
            const role = row.cells[1].textContent.toLowerCase();
            const status = row.cells[2].textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = !roleValue || role.includes(roleValue);
            const matchesStatus = !statusValue || status.includes(statusValue);

            row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (roleFilter) roleFilter.addEventListener('change', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
});
</script>

<?php include '../includes/footer.php'; ?>