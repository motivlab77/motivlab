<?php
require_once '../config.php';
$page_title = "View Message";

// Get message data
$id = $_GET['id'] ?? 0;
$message = null;

try {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        $_SESSION['error_message'] = "Message not found!";
        header('Location: messages.php');
        exit;
    }
    
    // Mark as read if it's new
    if ($message['status'] == 'new') {
        $updateStmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $updateStmt->execute([$id]);
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading message: " . $e->getMessage();
    header('Location: messages.php');
    exit;
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-envelope-open-text"></i> View Message</h1>
        <p>Message from <?php echo htmlspecialchars($message['name']); ?></p>
    </div>
    <a href="messages.php" class="btn">
        <i class="fas fa-arrow-left"></i> Back to Messages
    </a>
</div>

<?php showMessages(); ?>

<div class="table-container" style="padding: 25px;">
    <div style="display: grid; gap: 20px;">
        <div class="form-group">
            <label class="form-label">From</label>
            <div class="form-control" style="background: var(--bg);"><?php echo htmlspecialchars($message['name']); ?> &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;</div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Phone</label>
            <div class="form-control" style="background: var(--bg);"><?php echo htmlspecialchars($message['phone']); ?></div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Date Received</label>
            <div class="form-control" style="background: var(--bg);"><?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Message</label>
            <div class="form-control" style="background: var(--bg); min-height: 150px; white-space: pre-wrap;"><?php echo htmlspecialchars($message['message']); ?></div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Status</label>
            <div class="form-control" style="background: var(--bg);">
                <?php echo ucfirst($message['status']); ?>
                <?php if ($message['status'] != 'replied'): ?>
                    <a href="message-reply.php?id=<?php echo $message['id']; ?>" class="btn btn-small btn-primary" style="margin-left: 10px;">Mark as Replied</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
