<?php
// Test Email Configuration
require_once 'includes/email_config.php';

echo "<h2>Email Configuration Test</h2>";

echo "<h3>Current Settings:</h3>";
echo "SMTP Host: " . SMTP_HOST . "<br>";
echo "SMTP Port: " . SMTP_PORT . "<br>";
echo "SMTP Username: " . SMTP_USERNAME . "<br>";
echo "System Email: " . SYSTEM_EMAIL . "<br>";
echo "Site URL: " . SITE_URL . "<br>";

// Check if credentials are default/placeholder
if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
    echo "<h3 style='color: red;'>⚠️ Configuration Required</h3>";
    echo "<p>The email configuration is using placeholder values. Please update the SMTP settings in includes/email_config.php with your actual Gmail credentials.</p>";
    echo "<p>See EMAIL_SETUP.md for detailed instructions.</p>";
    exit;
}

// Test PHPMailer loading
echo "<h3>Testing PHPMailer Loading:</h3>";
try {
    require_once __DIR__ . '/SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php';
    require_once __DIR__ . '/SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/SMTP.php';
    require_once __DIR__ . '/SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/Exception.php';
    echo "✅ PHPMailer files loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Failed to load PHPMailer: " . $e->getMessage() . "<br>";
    exit;
}

// Test email sending
echo "<h3>Testing Email Sending:</h3>";
try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Enable debug mode
    $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Level $level: $str<br>";
    };
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION;
    $mail->Port       = SMTP_PORT;
    
    // Recipients
    $mail->setFrom(SYSTEM_EMAIL, SYSTEM_EMAIL_NAME);
    $mail->addAddress(SMTP_USERNAME); // Send to self for testing
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = "PIMS Email Test - " . date('Y-m-d H:i:s');
    $mail->Body    = "<h2>Email Test Successful</h2><p>This is a test email from PIMS to verify email configuration.</p>";
    $mail->AltBody = "Email Test Successful - This is a test email from PIMS to verify email configuration.";
    
    $mail->send();
    echo "✅ Test email sent successfully to " . SMTP_USERNAME . "<br>";
    
} catch (Exception $e) {
    echo "❌ Email sending failed: " . $mail->ErrorInfo . "<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Next Steps:</h3>";
echo "<p>If the test fails, check the following:</p>";
echo "<ul>";
echo "<li>Gmail 2-factor authentication is enabled</li>";
echo "<li>You're using an App Password (not regular password)</li>";
echo "<li>SMTP credentials are correct</li>";
echo "<li>Firewall isn't blocking SMTP connections</li>";
echo "</ul>";
?>
