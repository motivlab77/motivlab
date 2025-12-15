<?php
// Temporary fix for database connection
function getDatabaseConnection() {
    // Try MySQL first
    try {
        $conn = new PDO("mysql:host=127.0.0.1;dbname=motivlab_db;charset=utf8mb4", "motivlab_admin", "Admin@2024Pass");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Fallback to SQLite
        try {
            $sqlite_path = dirname(__FILE__) . '/admin/data/database.sqlite';
            $conn = new PDO("sqlite:" . $sqlite_path);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            createSQLiteTables($conn);
            
            return $conn;
        } catch (PDOException $e2) {
            // Final fallback - return null and handle gracefully
            error_log("Both MySQL and SQLite connections failed: " . $e2->getMessage());
            return null;
        }
    }
}

function createSQLiteTables($conn) {
    $tables = [
        "admin_users" => "CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            full_name TEXT,
            email TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        "portfolio_items" => "CREATE TABLE IF NOT EXISTS portfolio_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            category TEXT,
            featured_image TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        "site_settings" => "CREATE TABLE IF NOT EXISTS site_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $table => $sql) {
        $conn->exec($sql);
    }
    
    // Insert default admin user (password: admin123)
    $stmt = $conn->prepare("INSERT OR IGNORE INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@motivlab.name.ng']);
    
    // Insert default settings
    $default_settings = [
        ['site_name', 'MotivLab'],
        ['hero_headline', 'Professional Website Solutions for Nigerian Businesses'],
        ['contact_email', 'info@motivlab.name.ng']
    ];
    
    foreach ($default_settings as $setting) {
        $stmt = $conn->prepare("INSERT OR IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute($setting);
    }
}
?>
