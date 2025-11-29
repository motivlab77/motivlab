<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$page_title = "Site Settings";
include '../includes/header.php';

// Get current settings
$stmt = $conn->query("SELECT * FROM site_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    // Initialize default settings if none exist
    $settings = [
        'site_name' => 'MotivLab',
        'site_logo' => '',
        'site_favicon' => '',
        'primary_color' => '#f68b1e',
        'secondary_color' => '#2c2c2c',
        'contact_email' => 'info@motivlab.name.ng',
        'contact_phone' => '+234 XXX XXX XXXX',
        'whatsapp_number' => '+234XXXXXXXXXX',
        'address' => 'Lagos, Nigeria',
        'facebook_url' => '',
        'twitter_url' => '',
        'instagram_url' => '',
        'linkedin_url' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Sanitize inputs
    $site_name = sanitizeInput($_POST['site_name']);
    $primary_color = sanitizeInput($_POST['primary_color']);
    $secondary_color = sanitizeInput($_POST['secondary_color']);
    $contact_email = sanitizeInput($_POST['contact_email']);
    $contact_phone = sanitizeInput($_POST['contact_phone']);
    $whatsapp_number = sanitizeInput($_POST['whatsapp_number']);
    $address = sanitizeInput($_POST['address']);
    $facebook_url = sanitizeInput($_POST['facebook_url']);
    $twitter_url = sanitizeInput($_POST['twitter_url']);
    $instagram_url = sanitizeInput($_POST['instagram_url']);
    $linkedin_url = sanitizeInput($_POST['linkedin_url']);

    // Validate inputs
    if (empty($site_name)) $errors[] = "Site name is required";
    if (empty($contact_email)) $errors[] = "Contact email is required";
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid contact email is required";
    if (!empty($facebook_url) && !filter_var($facebook_url, FILTER_VALIDATE_URL)) $errors[] = "Valid Facebook URL is required";
    if (!empty($twitter_url) && !filter_var($twitter_url, FILTER_VALIDATE_URL)) $errors[] = "Valid Twitter URL is required";
    if (!empty($instagram_url) && !filter_var($instagram_url, FILTER_VALIDATE_URL)) $errors[] = "Valid Instagram URL is required";
    if (!empty($linkedin_url) && !filter_var($linkedin_url, FILTER_VALIDATE_URL)) $errors[] = "Valid LinkedIn URL is required";

    // Handle logo upload
    $site_logo = $settings['site_logo'];
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
        $uploadResult = uploadImage($_FILES['site_logo']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $site_logo = $uploadResult['filename'];
            // Delete old logo if exists
            if ($settings['site_logo'] && file_exists("../assets/images/uploads/" . $settings['site_logo'])) {
                unlink("../assets/images/uploads/" . $settings['site_logo']);
            }
        }
    }

    // Handle favicon upload
    $site_favicon = $settings['site_favicon'];
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === 0) {
        $uploadResult = uploadImage($_FILES['site_favicon']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $site_favicon = $uploadResult['filename'];
            // Delete old favicon if exists
            if ($settings['site_favicon'] && file_exists("../assets/images/uploads/" . $settings['site_favicon'])) {
                unlink("../assets/images/uploads/" . $settings['site_favicon']);
            }
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            // Check if settings exist
            $checkStmt = $conn->query("SELECT COUNT(*) FROM site_settings");
            $exists = $checkStmt->fetchColumn();
            
            if ($exists) {
                // Update existing settings
                $stmt = $conn->prepare("
                    UPDATE site_settings SET 
                    site_name = ?, site_logo = ?, site_favicon = ?, primary_color = ?, secondary_color = ?,
                    contact_email = ?, contact_phone = ?, whatsapp_number = ?, address = ?,
                    facebook_url = ?, twitter_url = ?, instagram_url = ?, linkedin_url = ?,
                    updated_at = NOW()
                ");
                $stmt->execute([
                    $site_name, $site_logo, $site_favicon, $primary_color, $secondary_color,
                    $contact_email, $contact_phone, $whatsapp_number, $address,
                    $facebook_url, $twitter_url, $instagram_url, $linkedin_url
                ]);
            } else {
                // Insert new settings
                $stmt = $conn->prepare("
                    INSERT INTO site_settings 
                    (site_name, site_logo, site_favicon, primary_color, secondary_color,
                     contact_email, contact_phone, whatsapp_number, address,
                     facebook_url, twitter_url, instagram_url, linkedin_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $site_name, $site_logo, $site_favicon, $primary_color, $secondary_color,
                    $contact_email, $contact_phone, $whatsapp_number, $address,
                    $facebook_url, $twitter_url, $instagram_url, $linkedin_url
                ]);
            }
            
            $_SESSION['success_message'] = "Site settings updated successfully!";
            header('Location: settings.php');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // Update settings array with new values for form
    $settings = [
        'site_name' => $site_name,
        'site_logo' => $site_logo,
        'site_favicon' => $site_favicon,
        'primary_color' => $primary_color,
        'secondary_color' => $secondary_color,
        'contact_email' => $contact_email,
        'contact_phone' => $contact_phone,
        'whatsapp_number' => $whatsapp_number,
        'address' => $address,
        'facebook_url' => $facebook_url,
        'twitter_url' => $twitter_url,
        'instagram_url' => $instagram_url,
        'linkedin_url' => $linkedin_url
    ];
}
?>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Site Settings</h1>
        <p>Configure your website's appearance and contact information</p>
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

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <div class="settings-tabs">
            <!-- Basic Settings Tab -->
            <input type="radio" name="settings_tab" id="tab_basic" checked>
            <label for="tab_basic" class="tab-label">Basic Settings</label>
            <div class="tab-content">
                <div class="content-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="site_name" class="form-label">Site Name *</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_email" class="form-label">Contact Email *</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>">
                            <div class="form-text">Include country code (e.g., +2348012345678)</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['address']); ?></textarea>
                    </div>
                </div>

                <div class="content-section">
                    <h3>Branding</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="site_logo" class="form-label">Site Logo</label>
                            <?php if ($settings['site_logo']): ?>
                            <div class="current-image">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                     alt="Current logo" style="max-width: 200px; margin-bottom: 10px;">
                                <div class="form-text">Current logo</div>
                            </div>
                            <?php endif; ?>
                            <input type="file" id="site_logo" name="site_logo" class="form-control" accept="image/*">
                            <div class="form-text">Recommended: PNG with transparent background, 200x60px</div>
                        </div>

                        <div class="form-group">
                            <label for="site_favicon" class="form-label">Favicon</label>
                            <?php if ($settings['site_favicon']): ?>
                            <div class="current-image">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($settings['site_favicon']); ?>" 
                                     alt="Current favicon" style="max-width: 32px; margin-bottom: 10px;">
                                <div class="form-text">Current favicon</div>
                            </div>
                            <?php endif; ?>
                            <input type="file" id="site_favicon" name="site_favicon" class="form-control" accept="image/*">
                            <div class="form-text">Recommended: ICO or PNG, 32x32px</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Design Settings Tab -->
            <input type="radio" name="settings_tab" id="tab_design">
            <label for="tab_design" class="tab-label">Design</label>
            <div class="tab-content">
                <div class="content-section">
                    <h3>Color Scheme</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <div class="color-input-group">
                                <input type="color" id="primary_color" name="primary_color" 
                                       value="<?php echo htmlspecialchars($settings['primary_color']); ?>">
                                <input type="text" class="color-hex" 
                                       value="<?php echo htmlspecialchars($settings['primary_color']); ?>"
                                       onchange="document.getElementById('primary_color').value = this.value">
                            </div>
                            <div class="form-text">Main brand color used for buttons and accents</div>
                        </div>

                        <div class="form-group">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <div class="color-input-group">
                                <input type="color" id="secondary_color" name="secondary_color" 
                                       value="<?php echo htmlspecialchars($settings['secondary_color']); ?>">
                                <input type="text" class="color-hex" 
                                       value="<?php echo htmlspecialchars($settings['secondary_color']); ?>"
                                       onchange="document.getElementById('secondary_color').value = this.value">
                            </div>
                            <div class="form-text">Secondary color used for backgrounds and borders</div>
                        </div>
                    </div>

                    <div class="color-preview">
                        <h4>Color Preview</h4>
                        <div class="preview-elements">
                            <div class="preview-item">
                                <div class="preview-primary" style="background-color: <?php echo $settings['primary_color']; ?>"></div>
                                <span>Primary Color</span>
                            </div>
                            <div class="preview-item">
                                <div class="preview-secondary" style="background-color: <?php echo $settings['secondary_color']; ?>"></div>
                                <span>Secondary Color</span>
                            </div>
                            <div class="preview-item">
                                <button class="preview-button" style="background-color: <?php echo $settings['primary_color']; ?>">
                                    Button Example
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Tab -->
            <input type="radio" name="settings_tab" id="tab_social">
            <label for="tab_social" class="tab-label">Social Media</label>
            <div class="tab-content">
                <div class="content-section">
                    <h3>Social Media Links</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="facebook_url" class="form-label">
                                <i class="fab fa-facebook" style="color: #1877f2;"></i> Facebook URL
                            </label>
                            <input type="url" id="facebook_url" name="facebook_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['facebook_url']); ?>" 
                                   placeholder="https://facebook.com/yourpage">
                        </div>

                        <div class="form-group">
                            <label for="twitter_url" class="form-label">
                                <i class="fab fa-twitter" style="color: #1da1f2;"></i> Twitter URL
                            </label>
                            <input type="url" id="twitter_url" name="twitter_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['twitter_url']); ?>" 
                                   placeholder="https://twitter.com/yourhandle">
                        </div>

                        <div class="form-group">
                            <label for="instagram_url" class="form-label">
                                <i class="fab fa-instagram" style="color: #e4405f;"></i> Instagram URL
                            </label>
                            <input type="url" id="instagram_url" name="instagram_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['instagram_url']); ?>" 
                                   placeholder="https://instagram.com/yourprofile">
                        </div>

                        <div class="form-group">
                            <label for="linkedin_url" class="form-label">
                                <i class="fab fa-linkedin" style="color: #0a66c2;"></i> LinkedIn URL
                            </label>
                            <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>" 
                                   placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                <i class="fas fa-undo"></i> Reset to Defaults
            </button>
        </div>
    </form>
</div>

<style>
.settings-tabs {
    margin-bottom: 30px;
}

.tab-label {
    display: inline-block;
    padding: 12px 24px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    margin-right: 5px;
    cursor: pointer;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s ease;
}

.tab-label:hover {
    background: #e2e8f0;
    color: #374151;
}

input[name="settings_tab"] {
    display: none;
}

input[name="settings_tab"]:checked + .tab-label {
    background: white;
    color: #1f2937;
    border-color: #e2e8f0;
    position: relative;
    top: 1px;
    z-index: 1;
}

.tab-content {
    display: none;
    padding: 30px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0 8px 8px 8px;
    margin-top: -1px;
}

input[name="settings_tab"]:checked + .tab-label + .tab-content {
    display: block;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.color-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-input-group input[type="color"] {
    width: 60px;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    cursor: pointer;
}

.color-input-group .color-hex {
    flex: 1;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: monospace;
    font-size: 14px;
}

.color-preview {
    margin-top: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
}

.color-preview h4 {
    margin: 0 0 15px 0;
    color: #374151;
}

.preview-elements {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.preview-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.preview-primary,
.preview-secondary {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
}

.preview-button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    cursor: default;
}

.current-image {
    margin-bottom: 15px;
}

.current-image img {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .tab-label {
        display: block;
        margin-right: 0;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .preview-elements {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Update color preview in real-time
document.addEventListener('DOMContentLoaded', function() {
    const primaryColorInput = document.getElementById('primary_color');
    const secondaryColorInput = document.getElementById('secondary_color');
    
    function updateColorPreview() {
        const primaryColor = primaryColorInput.value;
        const secondaryColor = secondaryColorInput.value;
        
        // Update preview elements
        document.querySelector('.preview-primary').style.backgroundColor = primaryColor;
        document.querySelector('.preview-secondary').style.backgroundColor = secondaryColor;
        document.querySelector('.preview-button').style.backgroundColor = primaryColor;
        
        // Update hex inputs
        document.querySelectorAll('.color-hex')[0].value = primaryColor;
        document.querySelectorAll('.color-hex')[1].value = secondaryColor;
    }
    
    if (primaryColorInput) {
        primaryColorInput.addEventListener('input', updateColorPreview);
    }
    
    if (secondaryColorInput) {
        secondaryColorInput.addEventListener('input', updateColorPreview);
    }
    
    // Sync hex inputs with color inputs
    const hexInputs = document.querySelectorAll('.color-hex');
    hexInputs.forEach((hexInput, index) => {
        hexInput.addEventListener('input', function() {
            const colorInput = index === 0 ? primaryColorInput : secondaryColorInput;
            if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                colorInput.value = this.value.toUpperCase();
                updateColorPreview();
            }
        });
    });
});

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to default values? This cannot be undone.')) {
        document.getElementById('site_name').value = 'MotivLab';
        document.getElementById('primary_color').value = '#f68b1e';
        document.getElementById('secondary_color').value = '#2c2c2c';
        document.getElementById('contact_email').value = 'info@motivlab.name.ng';
        document.getElementById('contact_phone').value = '+234 XXX XXX XXXX';
        document.getElementById('whatsapp_number').value = '+234XXXXXXXXXX';
        document.getElementById('address').value = 'Lagos, Nigeria';
        document.getElementById('facebook_url').value = '';
        document.getElementById('twitter_url').value = '';
        document.getElementById('instagram_url').value = '';
        document.getElementById('linkedin_url').value = '';
        
        // Update preview
        const event = new Event('input');
        document.getElementById('primary_color').dispatchEvent(event);
    }
}

// Auto-format phone numbers
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="text"][id*="phone"], input[type="text"][id*="whatsapp"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove all non-digit characters except +
            let value = this.value.replace(/[^\d+]/g, '');
            
            // Ensure it starts with +
            if (!value.startsWith('+')) {
                value = '+' + value.replace(/^\+/, '');
            }
            
            // Limit to 15 characters (including +)
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            
            this.value = value;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>