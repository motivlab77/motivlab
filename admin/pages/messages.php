<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Contact Messages";
include '../includes/header.php';

// Handle status update
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = $_GET['mark_read'];
    
    try {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Message marked as read!";
        header('Location: messages.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating message status: " . $e->getMessage();
        header('Location: messages.php');
        exit;
    }
}

if (isset($_GET['mark_replied']) && is_numeric($_GET['mark_replied'])) {
    $id = $_GET['mark_replied'];
    
    try {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Message marked as replied!";
        header('Location: messages.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating message status: " . $e->getMessage();
        header('Location: messages.php');
        exit;
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Message deleted successfully!";
        header('Location: messages.php');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting message: " . $e->getMessage();
        header('Location: messages.php');
        exit;
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_messages = $_POST['selected_messages'] ?? [];
    
    if (!empty($selected_messages)) {
        try {
            $placeholders = str_repeat('?,', count($selected_messages) - 1) . '?';
            
            if ($bulk_action === 'delete') {
                $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                $stmt->execute($selected_messages);
                $_SESSION['success_message'] = count($selected_messages) . " messages deleted successfully!";
            } elseif ($bulk_action === 'mark_read') {
                $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id IN ($placeholders)");
                $stmt->execute($selected_messages);
                $_SESSION['success_message'] = count($selected_messages) . " messages marked as read!";
            } elseif ($bulk_action === 'mark_replied') {
                $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id IN ($placeholders)");
                $stmt->execute($selected_messages);
                $_SESSION['success_message'] = count($selected_messages) . " messages marked as replied!";
            } elseif ($bulk_action === 'export') {
                exportMessagesToCSV($selected_messages, $conn);
                exit;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Error performing bulk action: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "No messages selected!";
    }
    
    header('Location: messages.php');
    exit;
}

// Get message statistics
$total_messages = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$new_messages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$read_messages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn();
$replied_messages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn();

// Get all messages
$stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV Export function
function exportMessagesToCSV($message_ids, $conn) {
    $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id IN ($placeholders) ORDER BY created_at DESC");
    $stmt->execute($message_ids);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=messages_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Add headers
    fputcsv($output, [
        'ID', 'Name', 'Email', 'Phone', 'Website Type', 'Message', 'Status', 'Date'
    ]);
    
    // Add data
    foreach ($messages as $message) {
        fputcsv($output, [
            $message['id'],
            $message['name'],
            $message['email'],
            $message['phone'],
            $message['website_type'],
            $message['message'],
            $message['status'],
            $message['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Contact Messages</h1>
        <p>Manage inquiries and messages from your website visitors</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #667eea;">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_messages; ?></h3>
                <p>Total Messages</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #f68b1e;">
                <i class="fas fa-envelope-open"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $new_messages; ?></h3>
                <p>New Messages</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $read_messages; ?></h3>
                <p>Read</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-reply"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $replied_messages; ?></h3>
                <p>Replied</p>
            </div>
        </div>
    </div>

    <!-- Messages Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>All Messages</h2>
            <div class="header-actions">
                <a href="messages.php?export=all" class="btn btn-secondary" onclick="return confirm('Export all messages to CSV?')">
                    <i class="fas fa-download"></i> Export All
                </a>
            </div>
        </div>

        <!-- Bulk Actions -->
        <form method="POST" class="bulk-actions" id="bulkActionsForm">
            <div class="bulk-controls">
                <select name="bulk_action" class="form-control">
                    <option value="">Bulk Actions</option>
                    <option value="mark_read">Mark as Read</option>
                    <option value="mark_replied">Mark as Replied</option>
                    <option value="delete">Delete</option>
                    <option value="export">Export Selected</option>
                </select>
                <button type="submit" class="btn btn-secondary" id="applyBulkAction">Apply</button>
            </div>

            <!-- Search and Filter -->
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" id="messagesSearch" placeholder="Search messages...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-controls">
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                    </select>
                    <select id="typeFilter">
                        <option value="">All Types</option>
                        <option value="school">School System</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="ecommerce">eCommerce</option>
                        <option value="salon">Salon & Beauty</option>
                        <option value="realestate">Real Estate</option>
                        <option value="logistics">Logistics</option>
                        <option value="events">Events</option>
                        <option value="portfolio">Portfolio</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="table-container">
                <table class="data-table" id="messagesTable">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Contact</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                                <p>No messages yet. Messages from your contact form will appear here.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                            <tr class="message-row <?php echo $message['status'] === 'new' ? 'unread' : ''; ?>" 
                                data-id="<?php echo $message['id']; ?>">
                                <td>
                                    <input type="checkbox" name="selected_messages[]" value="<?php echo $message['id']; ?>" class="message-checkbox">
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                        <div class="contact-details">
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?>
                                            </a>
                                            <?php if ($message['phone']): ?>
                                            <br>
                                            <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($message['phone']); ?>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>
                                        <?php if (strlen($message['message']) > 100): ?>...<?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php 
                                        $type_map = [
                                            'school' => 'School',
                                            'restaurant' => 'Restaurant',
                                            'ecommerce' => 'eCommerce',
                                            'salon' => 'Salon',
                                            'realestate' => 'Real Estate',
                                            'logistics' => 'Logistics',
                                            'events' => 'Events',
                                            'portfolio' => 'Portfolio',
                                            'other' => 'Other'
                                        ];
                                        echo $type_map[$message['website_type']] ?? ucfirst($message['website_type']);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $message['status'] === 'new' ? 'primary' : 
                                             ($message['status'] === 'read' ? 'success' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($message['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                    <br>
                                    <small style="color: #64748b;">
                                        <?php echo date('h:i A', strtotime($message['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="message-view.php?id=<?php echo $message['id']; ?>" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($message['status'] === 'new'): ?>
                                        <a href="?mark_read=<?php echo $message['id']; ?>" class="btn-icon" title="Mark as Read">
                                            <i class="fas fa-envelope-open"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($message['status'] !== 'replied'): ?>
                                        <a href="?mark_replied=<?php echo $message['id']; ?>" class="btn-icon" title="Mark as Replied">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $message['id']; ?>" class="btn-icon" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

.header-actions {
    display: flex;
    gap: 10px;
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

.contact-info strong {
    display: block;
    margin-bottom: 5px;
    color: #1f2937;
}

.contact-details {
    font-size: 12px;
    color: #64748b;
}

.contact-details a {
    color: #64748b;
    text-decoration: none;
}

.contact-details a:hover {
    color: #667eea;
}

.message-preview {
    color: #4b5563;
    line-height: 1.4;
}

.message-row.unread {
    background-color: #f0f9ff;
    border-left: 3px solid #667eea;
}

.message-row.unread td {
    font-weight: 600;
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

#selectAll {
    margin: 0;
}

.message-checkbox {
    margin: 0;
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
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .header-actions .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
// Bulk actions functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const messageCheckboxes = document.querySelectorAll('.message-checkbox');
    const bulkActionsForm = document.getElementById('bulkActionsForm');
    const applyBulkAction = document.getElementById('applyBulkAction');

    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            messageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function(e) {
            const selectedMessages = document.querySelectorAll('.message-checkbox:checked');
            const bulkAction = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedMessages.length === 0) {
                e.preventDefault();
                alert('Please select at least one message.');
                return;
            }
            
            if (!bulkAction) {
                e.preventDefault();
                alert('Please select a bulk action.');
                return;
            }
            
            if (bulkAction === 'delete') {
                if (!confirm(`Are you sure you want to delete ${selectedMessages.length} message(s)? This action cannot be undone.`)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    }

    // Filter table functionality
    const searchInput = document.getElementById('messagesSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const table = document.getElementById('messagesTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;

        rows.forEach(row => {
            if (row.cells.length < 7) return; // Skip empty state row
            
            const name = row.cells[1].textContent.toLowerCase();
            const message = row.cells[2].textContent.toLowerCase();
            const type = row.cells[3].textContent.toLowerCase();
            const status = row.cells[4].textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm) || message.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);
            const matchesType = !typeValue || type.includes(typeValue);

            row.style.display = matchesSearch && matchesStatus && matchesType ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (typeFilter) typeFilter.addEventListener('change', filterTable);

    // Auto-mark as read when clicking on message row
    const messageRows = document.querySelectorAll('.message-row.unread');
    messageRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on checkboxes or action buttons
            if (e.target.type === 'checkbox' || e.target.closest('.action-buttons')) {
                return;
            }
            
            const messageId = this.getAttribute('data-id');
            window.location.href = `message-view.php?id=${messageId}`;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>