<?php
require_once 'config.php';

// Get website content from database
$settings = [];
$portfolio_items = [];
$testimonials = [];

try {
    // Get site settings
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    $result = $stmt->fetchAll();
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get portfolio items
    $portfolio_items = $conn->query("SELECT * FROM portfolio_items WHERE status = 'active' ORDER BY created_at DESC LIMIT 6")->fetchAll();
    
    // Get testimonials
    $testimonials = $conn->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3")->fetchAll();
    
} catch (Exception $e) {
    // Fallback to default content
    $settings = [
        'site_name' => 'MotivLab',
        'hero_headline' => 'Professional Website Solutions for Nigerian Businesses',
        'hero_subtext' => 'Get modern, fast websites with powerful admin dashboards. Schools, Restaurants, eCommerce, and more.',
        'contact_phone' => '+234 812 345 6789',
        'contact_email' => 'info@motivlab.name.ng',
        'whatsapp_number' => '+2348123456789'
    ];
}

// Include your HTML template
include 'index-template.php';
?>
