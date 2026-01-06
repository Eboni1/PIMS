<?php
// Simple PHP mail test script
echo "<h2>PHP Mail Configuration Test</h2>";

// Check PHP mail configuration
echo "<h3>PHP Configuration:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>";
echo "<tr><td>OS</td><td>" . PHP_OS . "</td></tr>";
echo "<tr><td>mail() function exists</td><td>" . (function_exists('mail') ? 'Yes' : 'No') . "</td></tr>";
echo "<tr><td>sendmail_path</td><td>" . ini_get('sendmail_path') . "</td></tr>";
echo "<tr><td>SMTP (from php.ini)</td><td>" . ini_get('SMTP') . "</td></tr>";
echo "<tr><td>smtp_port (from php.ini)</td><td>" . ini_get('smtp_port') . "</td></tr>";
echo "<tr><td>sendmail_from</td><td>" . ini_get('sendmail_from') . "</td></tr>";
echo "<tr><td>safe_mode</td><td>" . ini_get('safe_mode') . "</td></tr>";
echo "<tr><td>disable_functions</td><td>" . ini_get('disable_functions') . "</td></tr>";
echo "</table>";

// Test basic mail function
echo "<h3>Mail Function Test:</h3>";
$test_email = $_GET['email'] ?? 'test@example.com';
$test_subject = 'PIMS Mail Test - ' . date('Y-m-d H:i:s');
$test_message = 'This is a test email from PIMS to verify PHP mail functionality.';

echo "<p><strong>Test Email:</strong> " . htmlspecialchars($test_email) . "</p>";
echo "<p><strong>Test Subject:</strong> " . htmlspecialchars($test_subject) . "</p>";

// Test 1: Basic mail() function
echo "<h4>Test 1: Basic mail() function</h4>";
$headers = 'From: noreply@pims.com' . "\r\n" .
           'Reply-To: noreply@pims.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

$mail_result = mail($test_email, $test_subject, $test_message, $headers);

if ($mail_result) {
    echo "<p style='color: green;'>✅ Basic mail() test: SUCCESS</p>";
} else {
    echo "<p style='color: red;'>✗ Basic mail() test: FAILED</p>";
    echo "<p><strong>Error:</strong> Mail function returned false. Check your mail server configuration.</p>";
}

// Test 2: PHPMailer with mail()
echo "<h4>Test 2: PHPMailer with mail() transport</h4>";
try {
    require_once 'PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php';
    require_once 'PHPMailer/PHPMailer-7.0.0/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isMail();
    $mail->setFrom('noreply@pims.com', 'PIMS System');
    $mail->addAddress($test_email);
    $mail->Subject = $test_subject . ' (PHPMailer)';
    $mail->Body = '<p>This is a test email from PIMS using PHPMailer with mail() transport.</p>';
    $mail->AltBody = 'This is a test email from PIMS using PHPMailer with mail() transport.';
    
    $phpmailer_result = $mail->send();
    
    if ($phpmailer_result) {
        echo "<p style='color: green;'>✅ PHPMailer test: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>✗ PHPMailer test: FAILED</p>";
        echo "<p><strong>PHPMailer Error:</strong> " . $mail->ErrorInfo . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ PHPMailer test: EXCEPTION</p>";
    echo "<p><strong>Exception:</strong> " . $e->getMessage() . "</p>";
}

// Recommendations
echo "<h3>Recommendations:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>If mail is not working:</h4>";
echo "<ul>";
echo "<li><strong>XAMPP/WAMP:</strong> Configure sendmail in php.ini or use a mail service like MailHog for testing</li>";
echo "<li><strong>Linux:</strong> Install and configure Postfix, Sendmail, or Exim</li>";
echo "<li><strong>Windows:</strong> Configure SMTP in php.ini or use a mail server</li>";
echo "<li><strong>Testing:</strong> Use services like Mailtrap.io or MailHog for development</li>";
echo "</ul>";

echo "<h4>For XAMPP:</h4>";
echo "<ul>";
echo "<li>Edit <code>C:\xampp\php\php.ini</code></li>";
echo "<li>Set <code>sendmail_path = \"C:\xampp\sendmail\sendmail.exe -t\"</code></li>";
echo "<li>Or install <code>mailtodisk</code> for local testing</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='../SYSTEM_ADMIN/system_settings.php'>← Back to System Settings</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3, h4 {
    color: #333;
}
table {
    margin: 10px 0;
    background: white;
}
th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
th {
    background: #f2f2f2;
    font-weight: bold;
}
code {
    background: #f4f4f4;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
