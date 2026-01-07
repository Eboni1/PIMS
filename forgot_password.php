<?php
session_start();
require_once 'config.php';

// Site Settings (same as user_management.php)
define('SITE_URL', 'http://localhost/PIMS');
define('SITE_NAME', 'PIMS');
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer autoloader
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/Exception.php';
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/PHPMailer.php';
require_once 'SYSTEM_ADMIN/PHPMailer/PHPMailer-7.0.0/src/SMTP.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Rate limiting for password reset requests
if (!isset($_SESSION['reset_attempts'])) {
    $_SESSION['reset_attempts'] = 0;
    $_SESSION['last_reset_attempt'] = time();
}

// Lockout after 3 reset attempts for 30 minutes
if ($_SESSION['reset_attempts'] >= 3) {
    $time_diff = time() - $_SESSION['last_reset_attempt'];
    if ($time_diff < 1800) { // 30 minutes
        $error = "Too many reset attempts. Please try again in " . ceil((1800 - $time_diff) / 60) . " minutes.";
    } else {
        $_SESSION['reset_attempts'] = 0;
        $_SESSION['last_reset_attempt'] = time();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        // Input validation and sanitization
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        // Validate email format
        if (!$email) {
            $error = "Invalid email format.";
            $_SESSION['reset_attempts']++;
            $_SESSION['last_reset_attempt'] = time();
        } else {
            try {
                // Check if email exists in database
                $stmt = $conn->prepare("SELECT id, username, email, is_active FROM users WHERE email = ? LIMIT 1");
                
                if ($stmt === false) {
                    throw new Exception("Database error. Please try again later.");
                }
                
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Check if account is active
                    if (!$user['is_active']) {
                        $error = "Account is deactivated. Please contact administrator.";
                        $_SESSION['reset_attempts']++;
                        $_SESSION['last_reset_attempt'] = time();
                    } else {
                        // Generate secure reset token
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        // Clear any existing tokens for this email
                        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                        $delete_stmt->bind_param("s", $email);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                        
                        // Save token to database
                        $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                        $insert_stmt->bind_param("sss", $email, $token, $expiry);
                        
                        if ($insert_stmt->execute()) {
                            // Generate reset link
                            $reset_link = SITE_URL . "/reset_password.php?token=" . $token . "&email=" . urlencode($email);
                            
                            // Send email using PHPMailer (same method as user_management.php)
                            try {
                                $mail = new PHPMailer(true);
                                
                                // Server settings
                                $mail->SMTPDebug = 0;                      // Disable verbose debug output
                                $mail->isSMTP();                           // Send using SMTP
                                $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
                                $mail->SMTPAuth   = true;                  // Enable SMTP authentication
                                $mail->Username   = 'waltielappy@gmail.com'; // SMTP username
                                $mail->Password   = 'swmd zjes fubb ffxt';    // SMTP password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
                                $mail->Port       = 465;                   // TCP port to connect to
                                
                                // Recipients
                                $mail->setFrom('waltielappy@gmail.com', 'PIMS System Admin');
                                $mail->addAddress($email, $user['username']);
                                
                                // Content
                                $mail->isHTML(true);  // Set email format to HTML
                                $mail->Subject = "Password Reset Request - PIMS";
                                
                                $mail->Body = "
                                <html>
                                <head>
                                    <style>
                                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                        .header { background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                                        .button { display: inline-block; background: #191BA9; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                                        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                                        .security-note { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                                    </style>
                                </head>
                                <body>
                                    <div class='container'>
                                        <div class='header'>
                                            <h1>🔐 Password Reset Request</h1>
                                            <p>PIMS - Property Inventory Management System</p>
                                        </div>
                                        <div class='content'>
                                            <p>Hello <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
                                            <p>We received a request to reset your password for your PIMS account.</p>
                                            
                                            <div class='security-note'>
                                                <strong>🔒 Security Notice:</strong> This password reset link will expire in 1 hour for your security. If you didn't request this reset, please ignore this email.
                                            </div>
                                            
                                            <p style='text-align: center;'>
                                                <a href='$reset_link' class='button'>Reset My Password</a>
                                            </p>
                                            
                                            <p>Or copy and paste this link into your browser:</p>
                                            <p style='background: #f0f0f0; padding: 10px; border-radius: 5px; word-break: break-all;'>
                                                $reset_link
                                            </p>
                                            
                                            <p><strong>Important:</strong></p>
                                            <ul>
                                                <li>This link expires after 1 hour</li>
                                                <li>If you didn't request this reset, please contact your administrator</li>
                                                <li>Never share this link with anyone</li>
                                            </ul>
                                        </div>
                                        <div class='footer'>
                                            <p>This is an automated message from PIMS. Please do not reply to this email.</p>
                                            <p>&copy; " . date('Y') . " PIMS. All rights reserved.</p>
                                        </div>
                                    </div>
                                </body>
                                </html>";
                                
                                $mail->AltBody = "
                                Password Reset Request - PIMS
                                
                                Hello " . $user['username'] . ",
                                
                                We received a request to reset your password for your PIMS account.
                                
                                Click this link to reset your password: $reset_link
                                
                                Important:
                                - This link expires after 1 hour
                                - If you didn't request this reset, please contact your administrator
                                - Never share this link with anyone
                                
                                This is an automated message. Please do not reply.
                                ";
                                
                                $mail->send();
                                $success = "Password reset link has been sent to your email address. Please check your inbox (and spam folder).";
                                
                                // Log successful password reset request
                                error_log("Password reset email sent to user ID: " . $user['id'] . " from IP: " . $_SERVER['REMOTE_ADDR']);
                                
                            } catch (Exception $e) {
                                $error = "Failed to send reset email. Please try again later.";
                                $_SESSION['reset_attempts']++;
                                $_SESSION['last_reset_attempt'] = time();
                                
                                error_log("Failed to send password reset email to user ID: " . $user['id'] . ": " . $mail->ErrorInfo);
                            }
                        } else {
                            $error = "Database error. Please try again later.";
                            $_SESSION['reset_attempts']++;
                            $_SESSION['last_reset_attempt'] = time();
                            
                            error_log("Failed to save password reset token for user ID: " . $user['id']);
                        }
                        
                        $insert_stmt->close();
                        
                        // Reset attempts on successful request
                        $_SESSION['reset_attempts'] = 0;
                        $_SESSION['last_reset_attempt'] = time();
                    }
                } else {
                    // Don't reveal if email exists or not for security
                    $success = "If an account with that email exists, a password reset link has been sent.";
                    
                    // Log attempt for unknown email
                    error_log("Password reset requested for unknown email: " . $email . " from IP: " . $_SERVER['REMOTE_ADDR']);
                }
                
                $stmt->close();
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "System error. Please try again later.";
            }
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIMS - Forgot Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/index.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .split-screen {
            display: flex;
            height: 100vh;
            width: 100vw;
        }
        
        .carousel-section {
            flex: 1;
            background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            height: 100vh;
        }
        
        .forgot-section {
            flex: 1;
            background: linear-gradient(135deg, #F7F3F3 0%, #C1EAF2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            height: 100vh;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .split-screen {
                flex-direction: column;
                height: 100vh;
            }
            
            .carousel-section {
                height: 35vh;
                flex: none;
            }
            
            .forgot-section {
                height: 65vh;
                flex: none;
            }
        }
        
        @media (max-width: 576px) {
            .carousel-section {
                height: 30vh;
                padding: 0.5rem;
            }
            
            .forgot-section {
                height: 70vh;
                padding: 0.5rem;
            }
        }
        
        @media (max-width: 400px) {
            .carousel-section {
                height: 20vh;
            }
            
            .forgot-section {
                height: 80vh;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Carousel Section -->
        <div class="carousel-section">
            <div class="carousel-content">
                <div id="featureCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="2"></button>
                        <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="3"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="text-center text-white p-4">
                                <div class="display-1 mb-3">
                                    <i class="bi bi-key"></i>
                                </div>
                                <h3 class="carousel-title">Password Recovery</h3>
                                <p class="lead">
                                    Secure password recovery system - Reset your PIMS account password safely and quickly with our automated recovery process.
                                </p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="text-center text-white p-4">
                                <div class="display-1 mb-3">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3 class="carousel-title">Secure & Reliable</h3>
                                <p class="lead">
                                    Enterprise-grade security with encrypted reset links that expire after 1 hour, ensuring your account remains protected.
                                </p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="text-center text-white p-4">
                                <div class="display-1 mb-3">
                                    <i class="bi bi-envelope-check"></i>
                                </div>
                                <h3 class="carousel-title">Email Delivery</h3>
                                <p class="lead">
                                    Instant email delivery with secure reset links sent directly to your registered email address for quick access.
                                </p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="text-center text-white p-4">
                                <div class="display-1 mb-3">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h3 class="carousel-title">Time-Sensitive</h3>
                                <p class="lead">
                                    Reset links automatically expire after 1 hour for enhanced security, protecting your account from unauthorized access.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Forgot Password Section -->
        <div class="forgot-section">
            <div class="row w-100">
                <div class="col-12 col-md-8 col-lg-6 mx-auto">
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-header bg-primary text-white text-center rounded-top-4">
                            <div class="mb-3">
                                <div class="logo-circle">
                                    <img src="img/trans_logo.png" alt="PIMS Logo" class="img-fluid" style="max-height: 60px; border-radius: 8px;">
                                </div>
                            </div>
                            <h6 class="mb-0">PILAR INVENTORY MANAGEMENT SYSTEM</h6>
                            <small>Forgot Password</small>
                        </div>
                        <div class="card-body">
                            <?php if ($success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!$success): ?>
                                <div class="instructions">
                                    <h6><i class="bi bi-info-circle"></i> Instructions</h6>
                                    <p>Enter your email address and we'll send you a link to reset your password. The link will expire after 1 hour for security reasons.</p>
                                </div>
                                
                                <form method="POST" action="" id="forgotForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                        <label for="email"><i class="bi bi-envelope"></i> Email Address</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                        <i class="bi bi-send"></i> Send Reset Link
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <div class="text-center mt-3">
                                <hr>
                                <a href="index.php" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> Back to Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission loading state
        const forgotForm = document.getElementById('forgotForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (forgotForm) {
            forgotForm.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<span style="opacity: 0;">Sending...</span>';
            });
        }
        
        // Input focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    </script>
</body>
</html>
