<?php
/**
 * Email Configuration using Brevo (Sendinblue) SMTP
 *
 * Brevo provides a reliable transactional email service with free tier
 * Sign up at: https://www.brevo.com/
 */

// Brevo SMTP Configuration
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587); // Brevo uses port 587 for TLS
define('SMTP_SECURE', 'tls'); // TLS encryption
define('SMTP_USERNAME', '9f7140001@smtp-brevo.com'); // Brevo SMTP login
define('SMTP_PASSWORD', 'xsmtpsib-a035ccfe0bc087e91add3496f59625adf116bda722e47a5e0f7947bf6d107e9c-CLktWh6AL2RspyZC'); // Your Brevo SMTP key
define('SMTP_FROM_EMAIL', 'ovkrishnareddy@gmail.com'); // From email address (must be verified in Brevo)
define('SMTP_FROM_NAME', 'CuttingMaster'); // From name

// Site Configuration for emails (update when moving to production)
define('SITE_URL', 'http://localhost/CM-2025');
define('SITE_NAME', 'CuttingMaster');

/**
 * Send email using Brevo SMTP
 */
function sendEmail($to, $toName, $subject, $body, $isHTML = true) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0; // Disable debug output (set to 2 for troubleshooting)
        $mail->AuthType   = 'LOGIN'; // Force LOGIN auth method for Brevo

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->CharSet = 'UTF-8';

        if (!$isHTML) {
            $mail->AltBody = $body;
        }

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];

    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Send welcome email to new user
 */
function sendWelcomeEmail($email, $username, $userType) {
    $subject = "Welcome to CuttingMaster!";

    $body = getWelcomeEmailTemplate($username, $userType);

    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $username, $resetToken) {
    $subject = "Reset Your CuttingMaster Password";

    // Build reset link - use the actual domain in production
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $resetLink = $baseUrl . "/CM-2025/pages/reset-password.php?token=" . urlencode($resetToken);

    $body = getPasswordResetEmailTemplate($username, $resetLink);

    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get base email styles (shared across all templates)
 */
function getEmailBaseStyles() {
    return '
    <style>
        /* Reset & Base */
        body, table, td, p, a, li { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a2e;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f8f9fa;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        /* Header Styles */
        .header {
            padding: 20px 40px;
            border-bottom: 1px solid #e2e8f0;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            text-align: left;
        }

        .header-logo {
            font-family: "Georgia", "Times New Roman", serif;
            font-size: 22px;
            font-weight: 400;
            color: #9D4EDD;
            margin: 0;
            letter-spacing: 1px;
        }

        .header-tagline {
            color: #718096;
            font-size: 11px;
            margin: 2px 0 0 0;
            letter-spacing: 0.5px;
        }

        .header-icon {
            font-size: 28px;
        }

        /* Content Styles */
        .content {
            padding: 48px 40px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a2e;
            margin: 0 0 24px 0;
        }

        .message {
            font-size: 16px;
            color: #4a5568;
            margin: 0 0 24px 0;
            line-height: 1.7;
        }

        /* Button Styles */
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%);
            color: #ffffff !important;
            padding: 16px 48px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .btn-secondary {
            display: inline-block;
            background: #ffffff;
            color: #9D4EDD !important;
            padding: 14px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid #9D4EDD;
        }

        /* Card/Box Styles */
        .info-box {
            background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%);
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .warning-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 0 12px 12px 0;
            padding: 20px 24px;
            margin: 24px 0;
        }

        .warning-box strong {
            color: #b45309;
        }

        .warning-box p {
            color: #92400e;
            margin: 8px 0 0 0;
            font-size: 14px;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 32px 0;
        }

        /* Footer Styles */
        .footer {
            background: #f8f9fa;
            padding: 32px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-brand {
            font-family: "Georgia", "Times New Roman", serif;
            font-size: 18px;
            color: #9D4EDD;
            margin: 0 0 16px 0;
            letter-spacing: 1px;
        }

        .footer-links {
            margin: 16px 0;
        }

        .footer-links a {
            color: #718096;
            text-decoration: none;
            font-size: 13px;
            margin: 0 12px;
        }

        .footer-links a:hover {
            color: #9D4EDD;
        }

        .footer-copy {
            color: #a0aec0;
            font-size: 12px;
            margin: 16px 0 0 0;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: #e2e8f0;
            border-radius: 50%;
            margin: 0 6px;
            line-height: 36px;
            color: #718096;
            text-decoration: none;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-muted { color: #718096; }
        .text-small { font-size: 14px; }
        .mt-0 { margin-top: 0; }
        .mb-0 { margin-bottom: 0; }
        .mb-16 { margin-bottom: 16px; }
        .mb-24 { margin-bottom: 24px; }

        /* Link fallback box */
        .link-fallback {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            word-break: break-all;
            font-size: 12px;
            color: #718096;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .header { padding: 32px 24px; }
            .content { padding: 32px 24px; }
            .footer { padding: 24px; }
            .btn-primary { padding: 14px 32px; }
        }
    </style>';
}

/**
 * Get password reset email HTML template
 */
function getPasswordResetEmailTemplate($username, $resetLink) {
    $styles = getEmailBaseStyles();

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Your Password - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">🔐</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Password Reset</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Hello ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    We received a request to reset the password for your CuttingMaster account.
                    No worries — it happens to the best of us!
                </p>

                <p class="message">
                    Click the button below to create a new password:
                </p>

                <div class="text-center" style="margin: 32px 0;">
                    <a href="' . htmlspecialchars($resetLink) . '" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Reset My Password</a>
                </div>

                <div class="warning-box">
                    <strong>⏰ This link expires in 1 hour</strong>
                    <p>For your security, this password reset link will only work for the next 60 minutes.</p>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted">
                    If you didn\'t request this password reset, you can safely ignore this email.
                    Your password will remain unchanged.
                </p>

                <div class="link-fallback">
                    <strong>Having trouble with the button?</strong><br><br>
                    Copy and paste this link into your browser:<br>
                    <span style="color: #9D4EDD;">' . htmlspecialchars($resetLink) . '</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail($email, $username, $orderDetails) {
    $subject = "Order Confirmation - CuttingMaster #" . $orderDetails['order_id'];

    $body = getOrderConfirmationEmailTemplate($username, $orderDetails);

    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get order confirmation email HTML template
 */
function getOrderConfirmationEmailTemplate($username, $orderDetails) {
    $styles = getEmailBaseStyles();

    $itemsHtml = '';
    if (!empty($orderDetails['items'])) {
        foreach ($orderDetails['items'] as $item) {
            $itemsHtml .= '
            <tr>
                <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1a1a2e;">' . htmlspecialchars($item['name']) . '</td>
                <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; text-align: center; color: #4a5568;">' . intval($item['quantity']) . '</td>
                <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; text-align: right; color: #1a1a2e; font-weight: 500;">₹' . number_format($item['price'], 2) . '</td>
            </tr>';
        }
    }

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Confirmed - CuttingMaster</title>
    ' . $styles . '
    <style>
        .order-badge {
            background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%);
            color: #ffffff;
            padding: 20px 32px;
            border-radius: 12px;
            text-align: center;
            margin: 24px 0;
            box-shadow: 0 4px 16px rgba(157, 78, 221, 0.3);
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            border-radius: 12px;
            overflow: hidden;
        }
        .order-table th {
            background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%);
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #1a1a2e;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .total-row td {
            background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%);
            padding: 20px 16px !important;
            font-weight: 700;
            border-bottom: none !important;
        }
        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">✓</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Order Confirmation</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Hi ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    Great news! We\'ve received your order and it\'s being processed.
                    Here\'s a summary of what you ordered:
                </p>

                <div class="order-badge">
                    <span style="font-size: 13px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px;">Order Number</span><br>
                    <span style="font-size: 28px; font-weight: 700; letter-spacing: 2px;">#' . htmlspecialchars($orderDetails['order_id']) . '</span>
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                        <tr class="total-row">
                            <td colspan="2" style="text-align: right; font-size: 16px;">Total Amount:</td>
                            <td style="text-align: right; font-size: 22px; color: #9D4EDD;">₹' . number_format($orderDetails['total'], 2) . '</td>
                        </tr>
                    </tbody>
                </table>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    Have questions about your order?<br>
                    <a href="' . SITE_URL . '/pages/contact-us.php" style="color: #9D4EDD; text-decoration: none; font-weight: 600;">Contact our support team</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send thank you email after successful delivery/completion
 */
function sendThankYouEmail($email, $username) {
    $subject = "Thank You for Shopping with CuttingMaster!";

    $body = getThankYouEmailTemplate($username);

    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get thank you email HTML template
 */
function getThankYouEmailTemplate($username) {
    $styles = getEmailBaseStyles();

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Thank You - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">💜</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Thank You</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content text-center">
                <h2 class="greeting">Dear ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    Thank you for choosing <strong>CuttingMaster</strong>! We\'re honored that you trusted us
                    with your tailoring and pattern-making needs.
                </p>

                <div class="info-box">
                    <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.8;">
                        ✨ We hope you\'re delighted with your purchase<br>
                        💬 Your feedback means the world to us<br>
                        🎯 Your satisfaction is our top priority
                    </p>
                </div>

                <p class="message">
                    We\'d love to hear about your experience. Your feedback helps us
                    continue to improve and serve you better.
                </p>

                <div style="margin: 32px 0;">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Explore More Patterns</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted">
                    Looking forward to serving you again soon!<br>
                    <span style="color: #9D4EDD;">— The CuttingMaster Team</span>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/wholesale-catalog.php">Wholesale</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    Follow us for the latest updates and collections!
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Get welcome email HTML template
 */
function getWelcomeEmailTemplate($username, $userType) {
    $styles = getEmailBaseStyles();

    $userTypeLabels = [
        'individual' => 'Individual User',
        'boutique' => 'Boutique / Tailor',
        'wholesaler' => 'Wholesaler',
        'pattern_provider' => 'Pattern Provider'
    ];

    $userTypeLabel = $userTypeLabels[$userType] ?? 'Member';

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to CuttingMaster</title>
    ' . $styles . '
    <style>
        .welcome-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #ffffff;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
        }
        .feature-icon {
            font-size: 28px;
            flex-shrink: 0;
        }
        .feature-item strong {
            display: block;
            color: #1a1a2e;
            font-size: 16px;
            margin-bottom: 4px;
        }
        .feature-item p {
            margin: 0;
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }
        .feature-horizontal {
            text-align: center;
            padding: 16px 8px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .feature-horizontal .feature-icon {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
        }
        .feature-horizontal strong {
            display: block;
            color: #1a1a2e;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .feature-horizontal p {
            margin: 0;
            color: #718096;
            font-size: 12px;
            line-height: 1.4;
        }
        .section-title {
            color: #1a1a2e;
            font-size: 16px;
            margin: 28px 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #9D4EDD;
            display: inline-block;
        }
        .wholesale-grid td {
            padding: 8px;
        }
        .wholesale-item {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            text-align: center;
        }
        .wholesale-item .item-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .wholesale-item strong {
            display: block;
            color: #1a1a2e;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .wholesale-item p {
            margin: 0;
            color: #718096;
            font-size: 11px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">🎉</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Welcome</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Welcome, ' . htmlspecialchars($username) . '!</h2>

                <p class="message">
                    We\'re thrilled to have you join the CuttingMaster community!
                    Your account has been successfully created.
                </p>

                <div class="text-center" style="margin: 28px 0;">
                    <span class="welcome-badge">✓ Registered as ' . htmlspecialchars($userTypeLabel) . '</span>
                </div>

                <div class="divider"></div>

                <h3 style="color: #1a1a2e; font-size: 18px; margin: 24px 0 16px 0;">Here\'s what you can do:</h3>

                <!-- Core Features - Horizontal Layout -->
                <div style="background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%); border-radius: 16px; padding: 16px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="33%" style="padding: 8px;">
                                <div class="feature-horizontal">
                                    <span class="feature-icon">📐</span>
                                    <strong>Pattern Studio</strong>
                                    <p>Custom-fitted patterns based on your measurements</p>
                                </div>
                            </td>
                            <td width="33%" style="padding: 8px;">
                                <div class="feature-horizontal">
                                    <span class="feature-icon">📏</span>
                                    <strong>Save Measurements</strong>
                                    <p>Store for quick pattern generation</p>
                                </div>
                            </td>
                            <td width="33%" style="padding: 8px;">
                                <div class="feature-horizontal">
                                    <span class="feature-icon">⬇️</span>
                                    <strong>Download Patterns</strong>
                                    <p>PDF patterns ready to print and cut</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Wholesale Marketplace Section -->
                <h3 class="section-title">🏪 Wholesale Marketplace</h3>
                <p class="message text-small" style="margin-bottom: 16px;">
                    Browse curated collections from verified wholesalers at competitive prices.
                </p>

                <table class="wholesale-grid" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="50%">
                            <div class="wholesale-item">
                                <div class="item-icon">👗</div>
                                <strong>Ready-to-Stitch Kits</strong>
                                <p>Pre-cut fabric with patterns included</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="wholesale-item">
                                <div class="item-icon">🧵</div>
                                <strong>Fabric Collections</strong>
                                <p>Premium fabrics at wholesale rates</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <div class="wholesale-item">
                                <div class="item-icon">✂️</div>
                                <strong>Tailoring Supplies</strong>
                                <p>Tools and accessories for professionals</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="wholesale-item">
                                <div class="item-icon">📦</div>
                                <strong>Bulk Orders</strong>
                                <p>Special pricing for large quantities</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="text-center" style="margin: 36px 0;">
                    <a href="' . SITE_URL . '/pages/login.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Login to Your Account</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    Need help getting started?<br>
                    <a href="' . SITE_URL . '/pages/contact-us.php" style="color: #9D4EDD; text-decoration: none; font-weight: 600;">Contact our support team</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/wholesale-catalog.php">Wholesale</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send account upgraded email
 */
function sendAccountUpgradedEmail($email, $username, $oldType, $newType) {
    $subject = "Your CuttingMaster Account Has Been Upgraded!";
    $body = getAccountUpgradedEmailTemplate($username, $oldType, $newType);
    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get account upgraded email HTML template
 */
function getAccountUpgradedEmailTemplate($username, $oldType, $newType) {
    $styles = getEmailBaseStyles();

    $typeLabels = [
        'individual' => 'Individual',
        'boutique' => 'Boutique / Tailor',
        'wholesaler' => 'Wholesaler',
        'pattern_provider' => 'Pattern Provider'
    ];

    $oldLabel = $typeLabels[$oldType] ?? $oldType;
    $newLabel = $typeLabels[$newType] ?? $newType;

    // New features based on upgraded type
    $newFeatures = '';
    switch ($newType) {
        case 'boutique':
            $newFeatures = '
                <tr>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">👥</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Customer Management</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">Save client measurements</p>
                        </div>
                    </td>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">📦</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Wholesale Access</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">Special business rates</p>
                        </div>
                    </td>
                </tr>';
            break;
        case 'wholesaler':
            $newFeatures = '
                <tr>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🏪</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Seller Dashboard</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">List your catalog</p>
                        </div>
                    </td>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">📊</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Sales Analytics</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">Track your performance</p>
                        </div>
                    </td>
                </tr>';
            break;
        case 'pattern_provider':
            $newFeatures = '
                <tr>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">✏️</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Pattern Upload</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">Sell your designs</p>
                        </div>
                    </td>
                    <td width="50%" style="padding: 8px;">
                        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                            <div style="font-size: 28px; margin-bottom: 8px;">💰</div>
                            <strong style="display: block; color: #1a1a2e; font-size: 13px;">Earn Revenue</strong>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 11px;">From every download</p>
                        </div>
                    </td>
                </tr>';
            break;
    }

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Account Upgraded - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">⬆️</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Account Upgraded</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Congratulations, ' . htmlspecialchars($username) . '!</h2>

                <p class="message">
                    Your CuttingMaster account has been successfully upgraded. You now have access to more features and capabilities!
                </p>

                <div style="background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%); border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="40%" style="text-align: center;">
                                <span style="color: #718096; font-size: 12px; text-transform: uppercase;">From</span><br>
                                <strong style="color: #1a1a2e; font-size: 16px;">' . htmlspecialchars($oldLabel) . '</strong>
                            </td>
                            <td width="20%" style="text-align: center;">
                                <span style="font-size: 24px;">→</span>
                            </td>
                            <td width="40%" style="text-align: center;">
                                <span style="color: #718096; font-size: 12px; text-transform: uppercase;">To</span><br>
                                <strong style="color: #9D4EDD; font-size: 16px;">' . htmlspecialchars($newLabel) . '</strong>
                            </td>
                        </tr>
                    </table>
                </div>

                <h3 style="color: #1a1a2e; font-size: 16px; margin: 24px 0 12px 0;">New Features Unlocked:</h3>

                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    ' . $newFeatures . '
                </table>

                <div class="text-center" style="margin: 32px 0;">
                    <a href="' . SITE_URL . '/pages/dashboard.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Explore Your Dashboard</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    Questions about your new features?<br>
                    <a href="' . SITE_URL . '/pages/contact-us.php" style="color: #9D4EDD; text-decoration: none; font-weight: 600;">Contact our support team</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send measurement saved email (for first-time users)
 */
function sendMeasurementSavedEmail($email, $username, $measurementName) {
    $subject = "Your First Measurement Has Been Saved!";
    $body = getMeasurementSavedEmailTemplate($username, $measurementName);
    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get measurement saved email HTML template
 */
function getMeasurementSavedEmailTemplate($username, $measurementName) {
    $styles = getEmailBaseStyles();

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Measurement Saved - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">📏</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Measurement Saved</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Great job, ' . htmlspecialchars($username) . '!</h2>

                <p class="message">
                    You\'ve saved your first measurement profile: <strong>' . htmlspecialchars($measurementName) . '</strong>
                </p>

                <div style="background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%); border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
                    <span style="font-size: 48px;">✓</span>
                    <p style="margin: 12px 0 0 0; color: #166534; font-weight: 600; font-size: 16px;">Measurement Profile Created</p>
                </div>

                <h3 style="color: #1a1a2e; font-size: 16px; margin: 24px 0 12px 0;">What\'s Next?</h3>

                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="33%" style="padding: 8px;">
                            <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                                <div style="font-size: 28px; margin-bottom: 8px;">📐</div>
                                <strong style="display: block; color: #1a1a2e; font-size: 12px;">Generate Patterns</strong>
                            </div>
                        </td>
                        <td width="33%" style="padding: 8px;">
                            <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                                <div style="font-size: 28px; margin-bottom: 8px;">⬇️</div>
                                <strong style="display: block; color: #1a1a2e; font-size: 12px;">Download PDFs</strong>
                            </div>
                        </td>
                        <td width="33%" style="padding: 8px;">
                            <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                                <div style="font-size: 28px; margin-bottom: 8px;">✂️</div>
                                <strong style="display: block; color: #1a1a2e; font-size: 12px;">Start Cutting</strong>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="text-center" style="margin: 32px 0;">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Create Your First Pattern</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    Tip: You can save multiple measurement profiles for different people!
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send contact form auto-reply email
 */
function sendContactFormResponseEmail($email, $username, $subject) {
    $emailSubject = "We've Received Your Message - CuttingMaster";
    $body = getContactFormResponseEmailTemplate($username, $subject);
    return sendEmail($email, $username, $emailSubject, $body, true);
}

/**
 * Get contact form response email HTML template
 */
function getContactFormResponseEmailTemplate($username, $originalSubject) {
    $styles = getEmailBaseStyles();

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Message Received - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">📬</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Message Received</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Hello ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    Thank you for reaching out to us! We\'ve received your message and our team will get back to you as soon as possible.
                </p>

                <div style="background: #f7fafc; border-radius: 12px; padding: 20px; margin: 24px 0; border-left: 4px solid #9D4EDD;">
                    <p style="margin: 0; color: #718096; font-size: 12px; text-transform: uppercase;">Your Subject</p>
                    <p style="margin: 8px 0 0 0; color: #1a1a2e; font-size: 16px; font-weight: 500;">' . htmlspecialchars($originalSubject) . '</p>
                </div>

                <div style="background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%); border-radius: 16px; padding: 24px; margin: 24px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="50%" style="padding: 8px; text-align: center;">
                                <div style="font-size: 24px; margin-bottom: 8px;">⏱️</div>
                                <strong style="display: block; color: #1a1a2e; font-size: 14px;">Response Time</strong>
                                <p style="margin: 4px 0 0 0; color: #718096; font-size: 12px;">Within 24-48 hours</p>
                            </td>
                            <td width="50%" style="padding: 8px; text-align: center;">
                                <div style="font-size: 24px; margin-bottom: 8px;">📧</div>
                                <strong style="display: block; color: #1a1a2e; font-size: 14px;">Check Inbox</strong>
                                <p style="margin: 4px 0 0 0; color: #718096; font-size: 12px;">We\'ll reply to this email</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    While you wait, explore our <a href="' . SITE_URL . '/pages/pattern-studio.php" style="color: #9D4EDD; text-decoration: none; font-weight: 600;">Pattern Studio</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send pattern update notification email
 */
function sendPatternUpdateEmail($email, $username, $patternName, $updateType) {
    $subject = "Pattern Update: " . $patternName;
    $body = getPatternUpdateEmailTemplate($username, $patternName, $updateType);
    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get pattern update email HTML template
 */
function getPatternUpdateEmailTemplate($username, $patternName, $updateType) {
    $styles = getEmailBaseStyles();

    $updateMessages = [
        'correction' => 'We\'ve made corrections to improve accuracy.',
        'improvement' => 'We\'ve enhanced the pattern with improvements.',
        'new_sizes' => 'New size options have been added.',
        'bug_fix' => 'We\'ve fixed an issue with this pattern.'
    ];

    $updateMessage = $updateMessages[$updateType] ?? 'This pattern has been updated.';

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pattern Updated - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">🔄</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Pattern Update</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Hi ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    Good news! A pattern you\'ve downloaded has been updated.
                </p>

                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
                    <span style="font-size: 36px;">📐</span>
                    <p style="margin: 12px 0 0 0; color: #92400e; font-weight: 600; font-size: 18px;">' . htmlspecialchars($patternName) . '</p>
                    <p style="margin: 8px 0 0 0; color: #a16207; font-size: 14px;">' . $updateMessage . '</p>
                </div>

                <div style="background: #f0fdf4; border-radius: 12px; padding: 16px; margin: 24px 0; border: 1px solid #bbf7d0;">
                    <p style="margin: 0; color: #166534; font-size: 14px;">
                        <strong>💡 Tip:</strong> Download the updated version for the best results. Your previous measurements are still saved.
                    </p>
                </div>

                <div class="text-center" style="margin: 32px 0;">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Download Updated Pattern</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    Questions about this update?<br>
                    <a href="' . SITE_URL . '/pages/contact-us.php" style="color: #9D4EDD; text-decoration: none; font-weight: 600;">Contact our support team</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send newsletter email
 */
function sendNewsletterEmail($email, $username, $newsletterData) {
    $subject = $newsletterData['subject'] ?? "What's New at CuttingMaster";
    $body = getNewsletterEmailTemplate($username, $newsletterData);
    return sendEmail($email, $username, $subject, $body, true);
}

/**
 * Get newsletter email HTML template
 */
function getNewsletterEmailTemplate($username, $data) {
    $styles = getEmailBaseStyles();

    // Build featured patterns section
    $patternsHtml = '';
    if (!empty($data['featured_patterns'])) {
        foreach ($data['featured_patterns'] as $pattern) {
            $patternsHtml .= '
            <td width="50%" style="padding: 8px;">
                <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 8px;">' . ($pattern['icon'] ?? '📐') . '</div>
                    <strong style="display: block; color: #1a1a2e; font-size: 14px;">' . htmlspecialchars($pattern['name']) . '</strong>
                    <p style="margin: 4px 0 0 0; color: #718096; font-size: 12px;">' . htmlspecialchars($pattern['description'] ?? '') . '</p>
                </div>
            </td>';
        }
    }

    // Build promotion section if exists
    $promoHtml = '';
    if (!empty($data['promotion'])) {
        $promoHtml = '
        <div style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
            <span style="font-size: 32px;">' . ($data['promotion']['icon'] ?? '🎉') . '</span>
            <p style="margin: 12px 0 0 0; color: #ffffff; font-weight: 700; font-size: 20px;">' . htmlspecialchars($data['promotion']['title']) . '</p>
            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">' . htmlspecialchars($data['promotion']['description']) . '</p>
        </div>';
    }

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Newsletter - CuttingMaster</title>
    ' . $styles . '
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <table class="header-inner" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header-icon" style="width: 50px;">📰</td>
                        <td class="header-left">
                            <h1 class="header-logo">CuttingMaster</h1>
                            <p class="header-tagline">Newsletter</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Hi ' . htmlspecialchars($username) . ',</h2>

                <p class="message">
                    ' . htmlspecialchars($data['intro'] ?? 'Here\'s what\'s new at CuttingMaster this month!') . '
                </p>

                ' . $promoHtml . '

                ' . (!empty($patternsHtml) ? '
                <h3 style="color: #1a1a2e; font-size: 16px; margin: 24px 0 12px 0; padding-bottom: 8px; border-bottom: 2px solid #9D4EDD; display: inline-block;">✨ Featured Patterns</h3>

                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>' . $patternsHtml . '</tr>
                </table>
                ' : '') . '

                <div class="text-center" style="margin: 32px 0;">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php" class="btn-primary" style="background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%); color: #ffffff; padding: 16px 48px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; display: inline-block;">Explore All Patterns</a>
                </div>

                <div class="divider"></div>

                <p class="message text-small text-muted text-center">
                    You\'re receiving this because you subscribed to our newsletter.<br>
                    <a href="' . SITE_URL . '/pages/unsubscribe.php" style="color: #9D4EDD; text-decoration: none;">Unsubscribe</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-brand">CuttingMaster</p>
                <div class="footer-links">
                    <a href="' . SITE_URL . '/pages/pattern-studio.php">Pattern Studio</a>
                    <a href="' . SITE_URL . '/pages/wholesale-catalog.php">Wholesale</a>
                    <a href="' . SITE_URL . '/pages/contact-us.php">Contact Us</a>
                </div>
                <p class="footer-copy">
                    © ' . date('Y') . ' CuttingMaster. All rights reserved.<br>
                    This is an automated message. Please do not reply.
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
}
?>
