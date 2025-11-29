<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: messages.php');
    exit;
}

$id = $_GET['id'];

// Get message details
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    header('Location: messages.php');
    exit;
}

// Mark as read when viewing
if ($message['status'] === 'new') {
    $updateStmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $updateStmt->execute([$id]);
    $message['status'] = 'read';
}

$page_title = "Message from " . $message['name'];
include '../includes/header.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        
        $_SESSION['success_message'] = "Message status updated!";
        header("Location: message-view.php?id=$id");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating status: " . $e->getMessage();
        header("Location: message-view.php?id=$id");
        exit;
    }
}

// Handle reply (this would integrate with your email system)
if (isset($_POST['send_reply'])) {
    $reply_subject = sanitizeInput($_POST['reply_subject']);
    $reply_message = $_POST['reply_message'];
    
    // In a real application, you would send the email here
    // For now, we'll just mark as replied and show a success message
    
    try {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
        $stmt->execute([$id]);
        $message['status'] = 'replied';
        
        $_SESSION['success_message'] = "Reply sent successfully!";
        header("Location: message-view.php?id=$id");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error sending reply: " . $e->getMessage();
        header("Location: message-view.php?id=$id");
        exit;
    }
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Message Details</h1>
                <p>View and manage contact message</p>
            </div>
            <div>
                <a href="messages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </div>
    </div>

    <div class="message-details">
        <!-- Message Header -->
        <div class="content-section">
            <div class="message-header">
                <div class="sender-info">
                    <h2><?php echo htmlspecialchars($message['name']); ?></h2>
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>
                        </div>
                        <?php if ($message['phone']): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                <?php echo htmlspecialchars($message['phone']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="message-meta">
                    <div class="meta-item">
                        <strong>Type:</strong>
                        <span class="badge badge-primary">
                            <?php 
                            $type_map = [
                                'school' => 'School System',
                                'restaurant' => 'Restaurant & Food',
                                'ecommerce' => 'eCommerce Store',
                                'salon' => 'Salon & Beauty',
                                'realestate' => 'Real Estate',
                                'logistics' => 'Logistics',
                                'events' => 'Events',
                                'portfolio' => 'Portfolio',
                                'other' => 'Other'
                            ];
                            echo $type_map[$message['website_type']] ?? ucfirst($message['website_type']);
                            ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <strong>Received:</strong>
                        <span><?php echo date('F j, Y \a\t g:i A', strtotime($message['created_at'])); ?></span>
                    </div>
                    <form method="POST" class="status-form">
                        <div class="meta-item">
                            <strong>Status:</strong>
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="read" <?php echo $message['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $message['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Message Content -->
        <div class="content-section">
            <h3>Message</h3>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions-grid">
                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: Your inquiry to MotivLab" 
                   class="action-card" target="_blank">
                    <i class="fas fa-reply"></i>
                    <span>Reply via Email</span>
                </a>
                <?php if ($message['phone']): ?>
                <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>" class="action-card">
                    <i class="fas fa-phone"></i>
                    <span>Call Client</span>
                </a>
                <?php endif; ?>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $message['phone']); ?>?text=Hi%20<?php echo urlencode($message['name']); ?>%2C%20regarding%20your%20inquiry%20to%20MotivLab" 
                   class="action-card" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="?delete=<?php echo $message['id']; ?>" class="action-card danger"
                   onclick="return confirm('Are you sure you want to delete this message?')">
                    <i class="fas fa-trash"></i>
                    <span>Delete Message</span>
                </a>
            </div>
        </div>

        <!-- Reply Form -->
        <div class="content-section">
            <h3>Send Reply</h3>
            <form method="POST" class="reply-form">
                <input type="hidden" name="send_reply" value="1">
                
                <div class="form-group">
                    <label for="reply_subject" class="form-label">Subject</label>
                    <input type="text" id="reply_subject" name="reply_subject" class="form-control" 
                           value="Re: Your inquiry to MotivLab" required>
                </div>

                <div class="form-group">
                    <label for="reply_message" class="form-label">Message</label>
                    <textarea id="reply_message" name="reply_message" class="form-control" rows="8" required>
Dear <?php echo htmlspecialchars($message['name']); ?>,

Thank you for your interest in MotivLab. We have received your inquiry regarding our <?php echo $type_map[$message['website_type']] ?? $message['website_type']; ?> services.

One of our representatives will contact you shortly to discuss your project requirements in detail.

Best regards,
MotivLab Team
                    </textarea>
                    <div class="form-text">
                        This will send an email to <?php echo htmlspecialchars($message['email']); ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="prefillQuickReply()">
                        <i class="fas fa-bolt"></i> Quick Reply
                    </button>
                </div>
            </form>
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

.message-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 30px;
    align-items: start;
}

.sender-info h2 {
    margin: 0 0 15px 0;
    color: #1f2937;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
}

.contact-item a {
    color: #64748b;
    text-decoration: none;
}

.contact-item a:hover {
    color: #667eea;
}

.message-meta {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 200px;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.meta-item strong {
    color: #374151;
    font-size: 14px;
}

.status-form {
    margin: 0;
}

.status-select {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    font-size: 14px;
}

.message-content {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    line-height: 1.6;
    color: #4b5563;
    white-space: pre-wrap;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.3s ease;
    text-align: center;
}

.action-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.action-card.danger:hover {
    border-color: #ef4444;
    color: #ef4444;
}

.action-card i {
    font-size: 24px;
    margin-bottom: 8px;
    color: #667eea;
}

.action-card.danger i {
    color: #ef4444;
}

.action-card span {
    font-weight: 600;
    font-size: 14px;
}

.reply-form {
    margin-top: 20px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .message-header {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .message-meta {
        min-width: auto;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function prefillQuickReply() {
    const quickReply = `Hi <?php echo htmlspecialchars($message['name']); ?>,

Thanks for reaching out to MotivLab! We're excited to help you with your <?php echo $type_map[$message['website_type']] ?? $message['website_type']; ?> project.

Could you please share more details about your requirements? This will help us provide you with the best solution and accurate pricing.

Looking forward to hearing from you!

Best,
The MotivLab Team`;

    document.getElementById('reply_message').value = quickReply;
}

// Auto-save draft functionality
let draftTimer;
const replyMessage = document.getElementById('reply_message');
const replySubject = document.getElementById('reply_subject');

if (replyMessage && replySubject) {
    [replyMessage, replySubject].forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(() => {
                const draft = {
                    subject: replySubject.value,
                    message: replyMessage.value,
                    timestamp: new Date().toISOString()
                };
                localStorage.setItem('messageDraft_<?php echo $id; ?>', JSON.stringify(draft));
            }, 1000);
        });
    });

    // Load draft on page load
    const savedDraft = localStorage.getItem('messageDraft_<?php echo $id; ?>');
    if (savedDraft) {
        const draft = JSON.parse(savedDraft);
        if (confirm('Found a saved draft. Would you like to restore it?')) {
            replySubject.value = draft.subject;
            replyMessage.value = draft.message;
        }
    }

    // Clear draft when form is submitted
    document.querySelector('.reply-form').addEventListener('submit', function() {
        localStorage.removeItem('messageDraft_<?php echo $id; ?>');
    });
}
</script>

<?php include '../includes/footer.php'; ?>