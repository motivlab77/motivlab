<?php
require_once '../config.php';
$page_title = "Contact Messages";
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
        <p>Manage incoming contact form messages</p>
    </div>
</div>

<?php showMessages(); ?>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
                $messages = $stmt->fetchAll();
                
                if (empty($messages)) {
                    echo '<tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">No messages found.</td></tr>';
                } else {
                    foreach ($messages as $message) {
                        $status_color = $message['status'] == 'new' ? 'red' : ($message['status'] == 'read' ? 'orange' : 'green');
                        $status_text = ucfirst($message['status']);
                        
                        echo '
                        <tr>
                            <td>' . htmlspecialchars($message['name']) . '</td>
                            <td>' . htmlspecialchars($message['email']) . '</td>
                            <td>' . htmlspecialchars($message['phone']) . '</td>
                            <td><span style="color: ' . $status_color . '">' . $status_text . '</span></td>
                            <td>' . date('M d, Y', strtotime($message['created_at'])) . '</td>
                            <td>
                                <a href="messages-view.php?id=' . $message['id'] . '" class="btn btn-small btn-success">View</a>
                                <a href="message-delete.php?id=' . $message['id'] . '" class="btn btn-small btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
                            </td>
                        </tr>';
                    }
                }
            } catch (Exception $e) {
                echo '<tr><td colspan="6" style="text-align: center; color: red;">Error loading messages</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
