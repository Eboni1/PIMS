<?php
// Quick test of the updated email method
require_once 'config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer autoloader
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/Exception.php';
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php';
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/SMTP.php';

echo "<h2>Testing Updated Email Method (Same as user_management.php)</h2>";

try {
    $mail = new PHPMailer(true);
    
    // Server settings (same as user_management.php)
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->isSMTP();                           // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                  // Enable SMTP authentication
    $mail->Username   = 'waltielappy@gmail.com'; // SMTP username
    $mail->Password   = 'swmd zjes fubb ffxt';    // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
    $mail->Port       = 465;                   // TCP port to connect to
    
    // Recipients
    $mail->setFrom('waltielappy@gmail.com', 'PIMS System Admin');
    $mail->addAddress('waltielappy@gmail.com'); // Send to self for testing
    
    // Content
    $mail->isHTML(true);  // Set email format to HTML
    $mail->Subject = "PIMS Email Test - Updated Method";
    
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Email Test Successful</h1>
                <p>PIMS - Property Inventory Management System</p>
            </div>
            <div class='content'>
                <p>This is a test email using the same method as user_management.php.</p>
                <p><strong>Configuration:</strong></p>
                <ul>
                    <li>SMTP Host: smtp.gmail.com</li>
                    <li>SMTP Port: 465 (SSL)</li>
                    <li>Encryption: SMTPS</li>
                    <li>Method: Direct PHPMailer (no config file)</li>
                </ul>
                <p>If you receive this email, the forgot password functionality should now work correctly.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->AltBody = "Email Test Successful - This is a test email using the same method as user_management.php.";
    
    echo "<p>Sending test email...</p>";
    $mail->send();
    echo "<p style='color: green;'>✅ Test email sent successfully!</p>";
    echo "<p><strong>Next step:</strong> Try the forgot password functionality at <a href='forgot_password.php'>forgot_password.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Email sending failed: " . $mail->ErrorInfo . "</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
