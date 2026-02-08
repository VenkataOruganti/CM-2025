<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validation
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            global $pdo;

            // Rate limiting: Check if user requested too many resets recently (max 3 per hour)
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM password_resets
                WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$email]);
            $rateCheck = $stmt->fetch();

            if ($rateCheck && $rateCheck['count'] >= 3) {
                $message = 'Too many reset requests. Please try again later.';
                $messageType = 'error';
            } else {
                // Check if email exists in database
                $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Generate secure random token
                    $token = bin2hex(random_bytes(32)); // 64 character hex string
                    $tokenHash = hash('sha256', $token); // Store hash in DB for security

                    // Set expiration to 1 hour from now
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Get client info for logging
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

                    // Invalidate any existing unused tokens for this user
                    $stmt = $pdo->prepare("
                        UPDATE password_resets
                        SET used_at = NOW()
                        WHERE user_id = ? AND used_at IS NULL
                    ");
                    $stmt->execute([$user['id']]);

                    // Insert new reset token
                    $stmt = $pdo->prepare("
                        INSERT INTO password_resets (user_id, email, token_hash, expires_at, ip_address, user_agent)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user['id'],
                        $user['email'],
                        $tokenHash,
                        $expiresAt,
                        $ipAddress,
                        $userAgent
                    ]);

                    // Send password reset email (pass the raw token, not the hash)
                    $result = sendPasswordResetEmail($user['email'], $user['username'], $token);

                    if (!$result['success']) {
                        error_log("Password reset email failed for {$email}: " . $result['message']);
                    }
                }

                // Always show success message (security: don't reveal if email exists)
                $message = 'If an account exists with this email, you will receive password reset instructions shortly.';
                $messageType = 'success';
                $email = ''; // Clear the form
            }
        } catch (PDOException $e) {
            // Check if the password_resets table doesn't exist
            if (strpos($e->getMessage(), "password_resets' doesn't exist") !== false) {
                error_log("Password resets table doesn't exist. Please run the migration.");
                $message = 'Password reset is temporarily unavailable. Please try again later.';
            } else {
                error_log("Password reset error: " . $e->getMessage());
                $message = 'An error occurred. Please try again later.';
            }
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Reset Your Account | CuttingMaster</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Reset your CuttingMaster account password. Enter your email to receive secure password reset instructions. Quick and secure account recovery.">
    <meta name="keywords" content="forgot password, password reset, CuttingMaster account recovery, reset login">
    <meta name="author" content="CuttingMaster">
    <meta name="robots" content="noindex, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://cuttingmaster.in/pages/forgot-password.php">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Forgot Password specific styles -->
    <style>
        .auth-section-forgot-password {
            align-items: flex-start;
            padding-top: 8rem;
        }
        .auth-container-forgot-password {
            max-width: 450px;
            margin: 0 auto;
        }
        .auth-container-forgot-password .auth-content {
            text-align: center;
        }
        .auth-container-forgot-password .hero-tag {
            text-align: center;
        }
        .auth-container-forgot-password .hero-title {
            text-align: center;
        }
        .auth-container-forgot-password .hero-description {
            text-align: center;
            white-space: normal;
        }
        .auth-container-forgot-password .separator-line {
            margin: 1.5rem auto;
        }
        .auth-container-forgot-password .form-label {
            text-align: left;
            display: block;
        }
        .auth-container-forgot-password .form-footer {
            text-align: center;
        }
        .auth-container-forgot-password .email-button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .auth-container-forgot-password .email-button-group .form-input {
            width: 100%;
        }
        .auth-container-forgot-password .email-button-group .btn-large {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Background Glow Effects -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo logo-clickable" onclick="window.location.href='../index.php'">
                <img src="../images/cm-logo.svg" alt="CuttingMaster" style="height: 40px; width: auto;">
            </div>
            <div class="nav-links">
                <a href="pattern-studio.php" class="nav-link">PATTERN STUDIO</a>
                <a href="wholesale-catalog.php" class="nav-link">WHOLESALE MARKETPLACE</a>
                <a href="contact-us.php" class="nav-link">CONTACT US</a>
                <a href="login.php" class="btn-secondary btn-link btn-no-border">LOGIN</a>
            </div>
        </div>
    </nav>

    <!-- Forgot Password Section -->
    <section class="hero auth-section auth-section-forgot-password">
        <div class="hero-container auth-container auth-container-forgot-password">
            <div class="hero-content auth-content">
                <p class="hero-tag">Account Recovery</p>
                <h1 class="hero-title auth-title">
                    <span class="hero-title-accent">Forgot Password</span>
                </h1>
                <p class="hero-description auth-description">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>

                <!-- Separator -->
                <div class="separator-line"></div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Enter Your Email Address to get the Password</label>
                        <div class="email-button-group">
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                   placeholder="Enter your email" required autofocus>
                            <button type="submit" class="btn-large btn-solid">SEND RESET LINK</button>
                        </div>
                    </div>

                    <div class="form-footer">
                        <p>Remember your password? <a href="login.php" class="link-primary" style="color: #dc3545; font-weight: 600;">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
