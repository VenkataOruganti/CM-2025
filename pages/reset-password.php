<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';
$tokenValid = false;
$tokenExpired = false;
$resetComplete = false;

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $message = 'Invalid password reset link.';
    $messageType = 'error';
} else {
    try {
        global $pdo;

        // Hash the token to compare with stored hash
        $tokenHash = hash('sha256', $token);

        // Look up the token
        $stmt = $pdo->prepare("
            SELECT pr.*, u.username, u.email
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token_hash = ? AND pr.used_at IS NULL
        ");
        $stmt->execute([$tokenHash]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            $message = 'This password reset link is invalid or has already been used.';
            $messageType = 'error';
        } elseif (strtotime($resetRecord['expires_at']) < time()) {
            $tokenExpired = true;
            $message = 'This password reset link has expired. Please request a new one.';
            $messageType = 'error';
        } else {
            $tokenValid = true;
        }
    } catch (PDOException $e) {
        error_log("Password reset token validation error: " . $e->getMessage());
        $message = 'An error occurred. Please try again later.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($password) || empty($confirmPassword)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        try {
            global $pdo;

            // Hash the new password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Update user's password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$passwordHash, $resetRecord['user_id']]);

            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
            $stmt->execute([$resetRecord['id']]);

            $resetComplete = true;
            $tokenValid = false; // Hide the form
            $message = 'Your password has been reset successfully! You can now log in with your new password.';
            $messageType = 'success';

        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
            $message = 'An error occurred while resetting your password. Please try again.';
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
    <title>Reset Password - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Reset Password specific styles -->
    <style>
        .auth-section-reset-password {
            align-items: flex-start;
            padding-top: 8rem;
        }
        .auth-container-reset-password {
            max-width: 450px;
            margin: 0 auto;
        }
        .auth-container-reset-password .auth-content {
            text-align: center;
        }
        .auth-container-reset-password .hero-tag {
            text-align: center;
        }
        .auth-container-reset-password .hero-title {
            text-align: center;
        }
        .auth-container-reset-password .hero-description {
            text-align: center;
            white-space: normal;
        }
        .auth-container-reset-password .separator-line {
            margin: 1.5rem auto;
        }
        .auth-container-reset-password .form-label {
            text-align: left;
            display: block;
        }
        .auth-container-reset-password .form-footer {
            text-align: center;
        }
        .password-requirements {
            background: #F7FAFC;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: left;
        }
        .password-requirements h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            color: #4A5568;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 1.25rem;
            font-size: 0.8rem;
            color: #718096;
        }
        .password-requirements li {
            margin-bottom: 0.25rem;
        }
        .success-card {
            background: linear-gradient(135deg, #48BB78 0%, #38A169 100%);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
        }
        .success-card .success-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .success-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
        }
        .success-card p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            color: #2D3748;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

    <!-- Reset Password Section -->
    <section class="hero auth-section auth-section-reset-password">
        <div class="hero-container auth-container auth-container-reset-password">
            <div class="hero-content auth-content">
                <?php if ($resetComplete): ?>
                    <!-- Success State -->
                    <div class="success-card">
                        <div class="success-icon">
                            <i data-lucide="check" style="width: 32px; height: 32px;"></i>
                        </div>
                        <h3>Password Reset Complete!</h3>
                        <p>Your password has been successfully changed.</p>
                        <a href="login.php" class="btn-login">
                            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                            Go to Login
                        </a>
                    </div>
                <?php elseif ($tokenValid): ?>
                    <!-- Reset Form -->
                    <p class="hero-tag">Account Security</p>
                    <h1 class="hero-title auth-title">
                        <span class="hero-title-accent">Create New Password</span>
                    </h1>
                    <p class="hero-description auth-description">
                        Enter your new password below. Make sure it's secure and memorable.
                    </p>

                    <!-- Separator -->
                    <div class="separator-line"></div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="password-requirements">
                        <h4>Password Requirements:</h4>
                        <ul>
                            <li>At least 8 characters long</li>
                            <li>Recommended: mix of letters, numbers & symbols</li>
                        </ul>
                    </div>

                    <form method="POST" action="" class="auth-form">
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="form-input"
                                       placeholder="Enter your new password" required minlength="8" style="padding-right: 3rem;">
                                <button type="button" class="toggle-password"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="eye" class="eye-icon" style="width: 20px; height: 20px; color: #718096;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                       placeholder="Confirm your new password" required minlength="8" style="padding-right: 3rem;">
                                <button type="button" class="toggle-password"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="eye" class="eye-icon" style="width: 20px; height: 20px; color: #718096;"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-large btn-solid btn-submit">RESET PASSWORD</button>

                        <div class="form-footer">
                            <p>Remember your password? <a href="login.php" class="link-primary" style="color: #dc3545; font-weight: 600;">Login here</a></p>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Error State -->
                    <p class="hero-tag">Account Recovery</p>
                    <h1 class="hero-title auth-title">
                        <span class="hero-title-accent">Reset Password</span>
                    </h1>

                    <!-- Separator -->
                    <div class="separator-line"></div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($tokenExpired): ?>
                        <p style="color: #4A5568; margin: 1rem 0;">
                            Password reset links expire after 1 hour for security reasons.
                        </p>
                    <?php endif; ?>

                    <div style="margin-top: 1.5rem;">
                        <a href="forgot-password.php" class="btn-large btn-solid" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            Request New Reset Link
                        </a>
                    </div>

                    <div class="form-footer" style="margin-top: 1.5rem;">
                        <p>Remember your password? <a href="login.php" class="link-primary" style="color: #dc3545; font-weight: 600;">Login here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('.eye-icon');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    input.type = 'password';
                    icon.setAttribute('data-lucide', 'eye');
                }

                // Re-render icon
                lucide.createIcons();
            });
        });
    </script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
