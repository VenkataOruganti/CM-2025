<?php
/**
 * Edit Profile Page
 * Allows users to update their profile information
 */

session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require login
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        global $pdo;

        $userId = $currentUser['id'];
        $userType = $currentUser['user_type'];

        // Common fields for all user types
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobileNumber = trim($_POST['mobile_number'] ?? '');

        // Validation
        if (empty($username) || empty($email)) {
            throw new Exception('Name and email are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Check if email is already used by another user
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $userId]);
        if ($checkEmail->fetch()) {
            throw new Exception('This email is already registered to another account.');
        }

        // Build update query based on user type
        if ($userType === 'boutique') {
            $businessName = trim($_POST['business_name'] ?? '');
            $businessLocation = trim($_POST['business_location'] ?? '');

            if (empty($businessName)) {
                throw new Exception('Business name is required.');
            }

            $stmt = $pdo->prepare("
                UPDATE users SET
                    username = ?,
                    email = ?,
                    mobile_number = ?,
                    business_name = ?,
                    business_location = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $username,
                $email,
                $mobileNumber,
                $businessName,
                $businessLocation,
                $userId
            ]);
        } else {
            // Individual user
            $stmt = $pdo->prepare("
                UPDATE users SET
                    username = ?,
                    email = ?,
                    mobile_number = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $username,
                $email,
                $mobileNumber,
                $userId
            ]);
        }

        // Refresh current user data in session
        $_SESSION['username'] = $username;

        $message = 'Profile updated successfully!';
        $messageType = 'success';

        // Refresh user data
        $currentUser = getCurrentUser();

    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Determine dashboard URL based on user type
$dashboardUrl = 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .profile-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2D3748;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row.single {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4A5568;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #805AD5;
        }

        .form-group input:disabled {
            background-color: #F7FAFC;
            color: #718096;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-cancel {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: #4A5568;
            background: white;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #F7FAFC;
            border-color: #CBD5E0;
        }

        .help-text {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
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
                <a href="<?php echo $dashboardUrl; ?>" class="nav-link active-nav-link">YOUR ACCOUNT</a>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- Edit Profile Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <h1 class="hero-title auth-title" style="margin-top: 20px;">
                    Edit <span class="hero-title-accent">Profile</span>
                </h1>
                <p class="hero-description auth-description">
                    Update your account information
                </p>

                <!-- Success/Error Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1.5rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <form method="POST" action="" class="profile-form">

                    <!-- Account Information -->
                    <div class="form-section">
                        <h3 class="form-section-title">Account Information</h3>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="account_type">Account Type</label>
                            <input type="text" id="account_type"
                                value="<?php echo ucfirst(str_replace('_', ' ', $currentUser['user_type'])); ?>" disabled>
                            <p class="help-text">Account type cannot be changed</p>
                        </div>
                    </div>

                    <?php if ($currentUser['user_type'] === 'boutique'): ?>
                    <!-- Business Information (Boutique Users) -->
                    <div class="form-section">
                        <h3 class="form-section-title">Business Information</h3>

                        <div class="form-group">
                            <label for="business_name">Business Name *</label>
                            <input type="text" id="business_name" name="business_name"
                                value="<?php echo htmlspecialchars($currentUser['business_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Owner Name *</label>
                                <input type="text" id="username" name="username"
                                    value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="mobile_number">Mobile Number</label>
                                <input type="tel" id="mobile_number" name="mobile_number"
                                    value="<?php echo htmlspecialchars($currentUser['mobile_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="business_location">Business Location</label>
                            <input type="text" id="business_location" name="business_location"
                                value="<?php echo htmlspecialchars($currentUser['business_location'] ?? ''); ?>"
                                placeholder="City, State">
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- Personal Information (Individual Users) -->
                    <div class="form-section">
                        <h3 class="form-section-title">Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Full Name *</label>
                                <input type="text" id="username" name="username"
                                    value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="mobile_number">Mobile Number</label>
                                <input type="tel" id="mobile_number" name="mobile_number"
                                    value="<?php echo htmlspecialchars($currentUser['mobile_number'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <a href="<?php echo $dashboardUrl; ?>" class="btn-cancel">Cancel</a>
                    </div>

                </form>

            </div>
        </div>
    </section>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Initialize Lucide icons
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
