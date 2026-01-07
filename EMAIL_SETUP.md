# Email Setup Guide for PIMS Forgot Password (PHPMailer)

## Overview
The forgot password functionality uses PHPMailer to send actual emails. Here's how to configure it:

## Prerequisites
PHPMailer is already installed in the SYSTEM_ADMIN/PHPMailer folder. No additional installation needed.

## Step 1: Configure Gmail SMTP

### Create Gmail App Password
1. Enable 2-factor authentication on your Gmail account
2. Go to Google Account settings → Security → App passwords
3. Generate a new app password for "PIMS"
4. Copy the generated password (16-character code)

### Update Email Configuration
Edit `includes/email_config.php` and update these settings:

```php
// Gmail SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');     // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');         // App password from above
define('SMTP_ENCRYPTION', 'tls');

// System Email Settings
define('SYSTEM_EMAIL', 'your-email@gmail.com');      // Your Gmail address
define('SYSTEM_EMAIL_NAME', 'PIMS System');          // System name

// Site Settings
define('SITE_URL', 'http://localhost/PIMS');         // Your site URL
define('SITE_NAME', 'PIMS');                          // Your site name
```

## Step 2: Test the Setup

### Verify Database Table
The password_resets table should already be created. If not, run:
```
http://localhost/PIMS/setup_password_resets.php
```

### Test Forgot Password
1. Go to: `http://localhost/PIMS/index.php`
2. Click "Forgot Password"
3. Enter your email address
4. Check your email (including spam folder)

## Alternative SMTP Services

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls');
```

### Yahoo Mail
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yahoo.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

## Troubleshooting

### Common Issues
1. **"SMTP Error: Could not authenticate"**
   - Use Gmail app password (not regular password)
   - Enable 2-factor authentication on Gmail
   - Check for typos in email/password

2. **"SMTP connect() failed"**
   - Check internet connection
   - Verify SMTP host and port
   - Disable firewall temporarily to test

3. **Emails going to spam**
   - Use a real domain email
   - Check email content for spam triggers
   - Add sender to contacts

### Debug Mode
To enable detailed debugging, add this to the sendEmail function:
```php
$mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
$mail->Debugoutput = function($str, $level) {
    error_log("SMTP Level $level: $str");
};
```

### Error Logs
Check these locations for errors:
- PHP error logs: `C:\xampp\apache\logs\error.log`
- PHPMailer errors are logged automatically

## Security Notes
- Password reset links expire after 1 hour
- Tokens are marked as used after successful reset
- Rate limiting prevents abuse (3 attempts per 30 minutes)
- All reset attempts are logged
- Use app passwords, not real passwords

## Production Deployment
1. Use a dedicated email address (not personal Gmail)
2. Set up proper SPF/DKIM records for your domain
3. Consider using transactional email services (SendGrid, Mailgun)
4. Monitor email delivery rates
5. Set up bounce handling

## Support
If you encounter issues:
1. Check the error logs: `C:\xampp\apache\logs\error.log`
2. Verify PHPMailer files exist: `SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php` should exist
3. Test SMTP connection with a mail client
4. Ensure all configuration values are correct
5. Check Gmail app password is valid
