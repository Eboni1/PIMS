<?php
// Gmail App Password Setup Helper
session_start();
require_once 'config.php';
require_once 'includes/email_config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Setup - PIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%); min-height: 100vh; }
        .setup-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .step-card { border-left: 4px solid #191BA9; margin-bottom: 1rem; }
        .code-block { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card setup-card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="bi bi-envelope-fill"></i> Gmail Email Setup for PIMS</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Current Status:</strong> 
                            <?php if (SMTP_PASSWORD === 'xxxx xxxx xxxx xxxx'): ?>
                                <span class="text-warning">App password needs to be configured</span>
                            <?php else: ?>
                                <span class="text-success">App password is configured</span>
                            <?php endif; ?>
                        </div>

                        <h5><i class="bi bi-list-ol"></i> Step-by-Step Setup</h5>
                        
                        <div class="step-card p-3 mb-3">
                            <h6><i class="bi bi-1-circle"></i> Enable 2-Factor Authentication</h6>
                            <ol class="mb-0">
                                <li>Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                                <li>Enable "2-Step Verification" if not already enabled</li>
                                <li>Follow the setup process</li>
                            </ol>
                        </div>

                        <div class="step-card p-3 mb-3">
                            <h6><i class="bi bi-2-circle"></i> Generate App Password</h6>
                            <ol class="mb-0">
                                <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                                <li>Select "Mail" for the app</li>
                                <li>Select "Other (Custom name)" and type "PIMS"</li>
                                <li>Click "Generate"</li>
                                <li>Copy the 16-character password (format: xxxx xxxx xxxx xxxx)</li>
                            </ol>
                        </div>

                        <div class="step-card p-3 mb-3">
                            <h6><i class="bi bi-3-circle"></i> Update Configuration</h6>
                            <p>Edit the file <code>includes/email_config.php</code> and replace the placeholder:</p>
                            <div class="code-block">
                                define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Replace with your app password
                            </div>
                        </div>

                        <div class="step-card p-3 mb-3">
                            <h6><i class="bi bi-4-circle"></i> Test Configuration</h6>
                            <p>After updating the password, test the email sending:</p>
                            <div class="d-flex gap-2">
                                <a href="test_email.php" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Test Email
                                </a>
                                <a href="forgot_password.php" class="btn btn-success">
                                    <i class="bi bi-key"></i> Test Forgot Password
                                </a>
                            </div>
                        </div>

                        <hr>

                        <h5><i class="bi bi-shield-check"></i> Security Notes</h5>
                        <ul>
                            <li>Never use your regular Gmail password - always use App Passwords</li>
                            <li>App passwords are more secure and can be revoked individually</li>
                            <li>Keep the app password confidential - treat it like a password</li>
                            <li>You can revoke app passwords anytime from Google Account settings</li>
                        </ul>

                        <h5><i class="bi bi-tools"></i> Troubleshooting</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <strong>Common Issues:</strong>
                                    <ul class="mb-0">
                                        <li>"Could not authenticate" → Check app password</li>
                                        <li>"SMTP connect failed" → Check internet/firewall</li>
                                        <li>Emails in spam → Use proper domain email</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>Quick Solutions:</strong>
                                    <ul class="mb-0">
                                        <li>Regenerate app password if needed</li>
                                        <li>Check 2FA is enabled</li>
                                        <li>Verify email format: xxxx xxxx xxxx xxxx</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
