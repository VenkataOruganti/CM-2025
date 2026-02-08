<?php
/**
 * Email Template Preview
 * View all email templates before sending
 */

require_once __DIR__ . '/config/email.php';

$template = $_GET['template'] ?? 'welcome';
$sendTest = isset($_GET['send']);

// Sample data for previews
$sampleUsername = 'Priya';
$sampleUserType = $_GET['user_type'] ?? 'individual';
$sampleResetLink = SITE_URL . '/pages/reset-password.php?token=sample_token_12345';
$sampleOrderDetails = [
    'order_id' => 'CM-2024-0042',
    'items' => [
        ['name' => 'Anarkali Blouse Pattern (Custom)', 'quantity' => 1, 'price' => 299],
        ['name' => 'Lehenga Skirt Pattern (A-Line)', 'quantity' => 1, 'price' => 349],
        ['name' => 'Dupatta Draping Guide', 'quantity' => 1, 'price' => 99],
    ],
    'total' => 747
];

// Sample data for new templates
$sampleOldType = 'individual';
$sampleNewType = $_GET['new_type'] ?? 'boutique';
$sampleMeasurementName = 'My Measurements';
$sampleContactSubject = 'Question about pattern sizing';
$samplePatternName = 'Anarkali Blouse Pattern';
$sampleUpdateType = $_GET['update_type'] ?? 'correction';
$sampleNewsletterData = [
    'subject' => 'New Patterns This Month!',
    'intro' => 'Check out our latest additions to the Pattern Studio!',
    'promotion' => [
        'icon' => '🎉',
        'title' => '20% Off All Patterns',
        'description' => 'Use code WELCOME20 at checkout'
    ],
    'featured_patterns' => [
        ['name' => 'Anarkali Blouse', 'icon' => '👗', 'description' => 'Classic design'],
        ['name' => 'Lehenga Skirt', 'icon' => '✨', 'description' => 'A-line style']
    ]
];

// Get the template HTML
switch ($template) {
    case 'welcome':
        $html = getWelcomeEmailTemplate($sampleUsername, $sampleUserType);
        $title = 'Welcome Email';
        break;
    case 'reset':
        $html = getPasswordResetEmailTemplate($sampleUsername, $sampleResetLink);
        $title = 'Password Reset Email';
        break;
    case 'order':
        $html = getOrderConfirmationEmailTemplate($sampleUsername, $sampleOrderDetails);
        $title = 'Order Confirmation Email';
        break;
    case 'thankyou':
        $html = getThankYouEmailTemplate($sampleUsername);
        $title = 'Thank You Email';
        break;
    case 'upgraded':
        $html = getAccountUpgradedEmailTemplate($sampleUsername, $sampleOldType, $sampleNewType);
        $title = 'Account Upgraded Email';
        break;
    case 'measurement':
        $html = getMeasurementSavedEmailTemplate($sampleUsername, $sampleMeasurementName);
        $title = 'Measurement Saved Email';
        break;
    case 'contact':
        $html = getContactFormResponseEmailTemplate($sampleUsername, $sampleContactSubject);
        $title = 'Contact Form Response Email';
        break;
    case 'pattern_update':
        $html = getPatternUpdateEmailTemplate($sampleUsername, $samplePatternName, $sampleUpdateType);
        $title = 'Pattern Update Email';
        break;
    case 'newsletter':
        $html = getNewsletterEmailTemplate($sampleUsername, $sampleNewsletterData);
        $title = 'Newsletter Email';
        break;
    default:
        $html = '<p>Unknown template</p>';
        $title = 'Unknown';
}

// Send test email if requested
$message = '';
if ($sendTest) {
    $testEmail = 'ovkrishnareddy@gmail.com';
    switch ($template) {
        case 'welcome':
            $result = sendWelcomeEmail($testEmail, $sampleUsername, $sampleUserType);
            break;
        case 'reset':
            $result = sendEmail($testEmail, $sampleUsername, 'Test: Reset Your CuttingMaster Password', $html, true);
            break;
        case 'order':
            $result = sendOrderConfirmationEmail($testEmail, $sampleUsername, $sampleOrderDetails);
            break;
        case 'thankyou':
            $result = sendThankYouEmail($testEmail, $sampleUsername);
            break;
        case 'upgraded':
            $result = sendAccountUpgradedEmail($testEmail, $sampleUsername, $sampleOldType, $sampleNewType);
            break;
        case 'measurement':
            $result = sendMeasurementSavedEmail($testEmail, $sampleUsername, $sampleMeasurementName);
            break;
        case 'contact':
            $result = sendContactFormResponseEmail($testEmail, $sampleUsername, $sampleContactSubject);
            break;
        case 'pattern_update':
            $result = sendPatternUpdateEmail($testEmail, $sampleUsername, $samplePatternName, $sampleUpdateType);
            break;
        case 'newsletter':
            $result = sendNewsletterEmail($testEmail, $sampleUsername, $sampleNewsletterData);
            break;
    }
    $message = $result['success']
        ? '<div style="background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">Email sent successfully to ' . $testEmail . '</div>'
        : '<div style="background: #fee2e2; color: #dc2626; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">Failed: ' . $result['message'] . '</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preview - <?php echo $title; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
        }
        .toolbar {
            background: #ffffff;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .toolbar h1 {
            font-size: 18px;
            color: #1a1a2e;
        }
        .nav-links {
            display: flex;
            gap: 8px;
        }
        .nav-links a {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .nav-links {
            flex-wrap: wrap;
            justify-content: center;
        }
        .nav-links a.active {
            background: linear-gradient(135deg, #9D4EDD 0%, #E040FB 100%);
            color: white;
        }
        .nav-links a:not(.active) {
            background: #f3f4f6;
            color: #4b5563;
        }
        .nav-links a:not(.active):hover {
            background: #e5e7eb;
        }
        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .btn-send {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-send:hover {
            background: #059669;
        }
        .user-type-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .preview-container {
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .email-frame {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 700px;
            width: 100%;
        }
        .frame-header {
            background: #f9fafb;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #22c55e; }
        .frame-title {
            margin-left: 12px;
            font-size: 13px;
            color: #6b7280;
        }
        .email-content {
            max-height: 80vh;
            overflow-y: auto;
        }
        .message-bar {
            padding: 0 40px;
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>📧 Email Template Preview</h1>
        <div class="nav-links">
            <a href="?template=welcome" class="<?php echo $template === 'welcome' ? 'active' : ''; ?>">Welcome</a>
            <a href="?template=reset" class="<?php echo $template === 'reset' ? 'active' : ''; ?>">Password Reset</a>
            <a href="?template=order" class="<?php echo $template === 'order' ? 'active' : ''; ?>">Order</a>
            <a href="?template=thankyou" class="<?php echo $template === 'thankyou' ? 'active' : ''; ?>">Thank You</a>
            <a href="?template=upgraded" class="<?php echo $template === 'upgraded' ? 'active' : ''; ?>">Upgraded</a>
            <a href="?template=measurement" class="<?php echo $template === 'measurement' ? 'active' : ''; ?>">Measurement</a>
            <a href="?template=contact" class="<?php echo $template === 'contact' ? 'active' : ''; ?>">Contact</a>
            <a href="?template=pattern_update" class="<?php echo $template === 'pattern_update' ? 'active' : ''; ?>">Pattern Update</a>
            <a href="?template=newsletter" class="<?php echo $template === 'newsletter' ? 'active' : ''; ?>">Newsletter</a>
        </div>
        <div class="actions">
            <?php if ($template === 'welcome'): ?>
            <select class="user-type-select" onchange="window.location.href='?template=welcome&user_type='+this.value">
                <option value="individual" <?php echo $sampleUserType === 'individual' ? 'selected' : ''; ?>>Individual</option>
                <option value="boutique" <?php echo $sampleUserType === 'boutique' ? 'selected' : ''; ?>>Boutique</option>
                <option value="wholesaler" <?php echo $sampleUserType === 'wholesaler' ? 'selected' : ''; ?>>Wholesaler</option>
                <option value="pattern_provider" <?php echo $sampleUserType === 'pattern_provider' ? 'selected' : ''; ?>>Pattern Provider</option>
            </select>
            <?php endif; ?>
            <?php if ($template === 'upgraded'): ?>
            <select class="user-type-select" onchange="window.location.href='?template=upgraded&new_type='+this.value">
                <option value="boutique" <?php echo $sampleNewType === 'boutique' ? 'selected' : ''; ?>>To Boutique</option>
                <option value="wholesaler" <?php echo $sampleNewType === 'wholesaler' ? 'selected' : ''; ?>>To Wholesaler</option>
                <option value="pattern_provider" <?php echo $sampleNewType === 'pattern_provider' ? 'selected' : ''; ?>>To Pattern Provider</option>
            </select>
            <?php endif; ?>
            <?php if ($template === 'pattern_update'): ?>
            <select class="user-type-select" onchange="window.location.href='?template=pattern_update&update_type='+this.value">
                <option value="correction" <?php echo $sampleUpdateType === 'correction' ? 'selected' : ''; ?>>Correction</option>
                <option value="improvement" <?php echo $sampleUpdateType === 'improvement' ? 'selected' : ''; ?>>Improvement</option>
                <option value="new_sizes" <?php echo $sampleUpdateType === 'new_sizes' ? 'selected' : ''; ?>>New Sizes</option>
                <option value="bug_fix" <?php echo $sampleUpdateType === 'bug_fix' ? 'selected' : ''; ?>>Bug Fix</option>
            </select>
            <?php endif; ?>
            <a href="?template=<?php echo $template; ?>&user_type=<?php echo $sampleUserType; ?>&new_type=<?php echo $sampleNewType; ?>&update_type=<?php echo $sampleUpdateType; ?>&send=1" class="btn-send">📤 Send Test</a>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="message-bar" style="padding-top: 20px;">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="preview-container">
        <div class="email-frame">
            <div class="frame-header">
                <span class="dot dot-red"></span>
                <span class="dot dot-yellow"></span>
                <span class="dot dot-green"></span>
                <span class="frame-title"><?php echo $title; ?> - Preview</span>
            </div>
            <div class="email-content">
                <?php echo $html; ?>
            </div>
        </div>
    </div>
</body>
</html>
