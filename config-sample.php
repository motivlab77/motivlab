<?php
// Sample configuration - Rename to config.php and update values

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');

// Site Configuration
define('SITE_NAME', 'Your Site Name');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// Security - Generate unique keys for production
define('SECURITY_KEY', 'generate-unique-key-here');

// Timezone
date_default_timezone_set('Africa/Lagos');
?>
