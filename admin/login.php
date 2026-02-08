<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = 'Please enter both username and password.';
        $messageType = 'error';
    } else {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Set session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateStmt->execute([$admin['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $message = 'Invalid username or password.';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Login error: ' . $e->getMessage();
            $messageType = 'error';
            error_log("Admin login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <!-- Background Glow Effects -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <!-- Admin Login Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <p class="hero-tag">Administrator</p>
                <h1 class="hero-title auth-title">
                    Admin <span class="hero-title-accent">Login</span>
                </h1>
                <p class="hero-description auth-description">
                    Sign in to access the admin dashboard
                </p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input"
                               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                               placeholder="Enter admin username" required autofocus
                               autocomplete="off" pattern=".*" inputmode="text">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input"
                                   placeholder="Enter admin password" required
                                   autocomplete="current-password"
                                   style="padding-right: 3rem;">
                            <button type="button" id="toggle-password"
                                    style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #718096; padding: 0.5rem; display: flex; align-items: center; justify-content: center;"
                                    aria-label="Toggle password visibility">
                                <i data-lucide="eye" id="eye-icon" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-large btn-solid btn-submit">LOGIN</button>

                    <div class="form-footer">
                        <p><a href="../index.php" class="link-secondary">← Back to Website</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-row-1">
                <h3 class="footer-logo">CuttingMaster Admin</h3>
                <p class="footer-tagline">ADMINISTRATOR PANEL</p>
            </div>
            <div class="footer-row-2">
                <p class="footer-copyright">© <?php echo date('Y'); ?> CuttingMaster. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }

            // Remove any email validation from username field
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.type = 'text';
                usernameInput.removeAttribute('pattern');
                usernameInput.setAttribute('pattern', '.*');

                // Override setCustomValidity if it's being set
                usernameInput.addEventListener('input', function() {
                    this.setCustomValidity('');
                });

                // Prevent form validation errors on username
                usernameInput.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    this.setCustomValidity('');
                });
            }

            // Toggle password visibility
            const togglePasswordBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (togglePasswordBtn && passwordInput && eyeIcon) {
                togglePasswordBtn.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';

                    // Change icon
                    eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');

                    // Re-render the icon
                    if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                        lucide.createIcons();
                    }
                });
            }
        });
    </script>
</body>
</html>
