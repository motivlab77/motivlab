<?php
session_start();

// Database Configuration - Updated for your setup
define('DB_HOST', '127.0.0.1');              // Using IP instead of localhost
define('DB_USER', 'motivlab_admin');          // Your application user
define('DB_PASS', 'Admin@2024Pass');      // Your actual password
define('DB_NAME', 'motivlab_db');             // Your database name

// Site Configuration
define('SITE_NAME', 'MotivLab Admin');
define('SITE_URL', 'http://localhost:8080'); // For local development
define('ADMIN_EMAIL', 'info@motivlab.name.ng');

// Timezone
date_default_timezone_set('Africa/Lagos');

// Database Connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function uploadImage($file, $targetDir = '../../assets/images/uploads/') {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File is too large. Max 5MB.'];
    }
    
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $targetFile];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

function showSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>' . $_SESSION['success_message'] . '</div>
              </div>';
        unset($_SESSION['success_message']);
    }
}

function showErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>' . $_SESSION['error_message'] . '</div>
              </div>';
        unset($_SESSION['error_message']);
    }
}

function showMessages() {
    showSuccessMessage();
    showErrorMessage();
}
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>
