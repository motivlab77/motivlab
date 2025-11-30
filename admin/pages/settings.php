<?php
require_once '../config.php';
$page_title = "Site Settings";

// Get current settings
$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    $result = $stmt->fetchAll();
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading settings: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8); // Remove 'setting_' prefix
                $setting_value = sanitizeInput($value);
                
                // Update or insert setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$setting_key, $setting_value, $setting_value]);
            }
        }
        
        $_SESSION['success_message'] = "Settings updated successfully!";
        header('Location: settings.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1><i class="fas fa-cog"></i> Site Settings</h1>
        <p>Configure your website settings</p>
    </div>
</div>

<?php showMessages(); ?>

<div class="table-container" style="padding: 25px;">
    <form method="POST">
        <h3 style="margin-bottom: 20px; color: var(--dark);">General Settings</h3>
        
        <div class="form-group">
            <label class="form-label">Site Name</label>
            <input type="text" name="setting_site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Hero Headline</label>
            <input type="text" name="setting_hero_headline" class="form-control" value="<?php echo htmlspecialchars($settings['hero_headline'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Hero Subtext</label>
            <textarea name="setting_hero_subtext" class="form-control" rows="3"><?php echo htmlspecialchars($settings['hero_subtext'] ?? ''); ?></textarea>
        </div>
        
        <h3 style="margin: 30px 0 20px 0; color: var(--dark);">Contact Information</h3>
        
        <div class="form-group">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="setting_contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Contact Email</label>
            <input type="email" name="setting_contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">WhatsApp Number</label>
            <input type="text" name="setting_whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
