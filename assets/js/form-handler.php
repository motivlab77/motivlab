<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $message = sanitizeInput($_POST['message']);
    
    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $message]);
        
        // Send email notification (optional)
        // mail($admin_email, "New Contact Form Submission", $message);
        
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
