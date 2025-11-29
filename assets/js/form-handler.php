<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $websiteType = htmlspecialchars($_POST['websiteType'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    // Validate
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    // Email settings
    $to = "info@motivlab.name.ng"; // Change this to your email
    $subject = "New Contact Form Submission - $websiteType";
    
    // Email body
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; background: #f8f9fa; }
            .content { background: white; padding: 20px; border-radius: 8px; }
            .label { font-weight: bold; color: #f68b1e; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2>New Contact Form Submission</h2>
                <p><span class='label'>Name:</span> $name</p>
                <p><span class='label'>Email:</span> $email</p>
                <p><span class='label'>Phone:</span> $phone</p>
                <p><span class='label'>Website Type:</span> $websiteType</p>
                <p><span class='label'>Message:</span></p>
                <p>$message</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: MotivLab Website <noreply@motivlab.name.ng>" . "\r\n";
    $headers .= "Reply-To: $email" . "\r\n";
    
    // Send email
    if (mail($to, $subject, $emailBody, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>