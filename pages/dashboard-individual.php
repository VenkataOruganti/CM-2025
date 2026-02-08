<?php
// Redirect to unified dashboard
header('Location: dashboard.php');
exit;

session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require login and check user type
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser || $currentUser['user_type'] !== 'individual') {
    header('Location: login.php');
    exit;
}

// Check for login message (e.g., after saving pending measurements)
$loginMessage = '';
$loginMessageType = '';
if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    $loginMessageType = $_SESSION['login_message_type'];
    // Clear the message from session after reading
    unset($_SESSION['login_message']);
    unset($_SESSION['login_message_type']);
}

// Fetch the latest "self" measurements for this user
global $pdo;
$stmt = $pdo->prepare("
    SELECT * FROM measurements
    WHERE user_id = ? AND measurement_of = 'self'
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$currentUser['id']]);
$selfMeasurements = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Dashboard - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/mimic-banner.php'; ?>

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
                <div class="nav-dropdown">
                    <a href="dashboard-individual.php" class="nav-link active-nav-link nav-dropdown-toggle">
                        YOUR ACCOUNT
                        <i data-lucide="chevron-down" class="nav-dropdown-icon"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Name:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Email:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Account Type:</span>
                            <span class="nav-dropdown-value">Individual</span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Status:</span>
                            <span class="nav-dropdown-value dashboard-status-<?php echo $currentUser['status']; ?>">
                                <?php echo ucfirst($currentUser['status']); ?>
                            </span>
                        </div>
                        <?php if ($currentUser['last_login']): ?>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Last Login:</span>
                            <span class="nav-dropdown-value"><?php echo date('M j, Y g:i A', strtotime($currentUser['last_login'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content dashboard-content-left">
                <h1 class="hero-title auth-title">
                    Welcome <span class="hero-title-accent"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                </h1>

                <?php if ($loginMessage): ?>
                    <div class="alert alert-<?php echo $loginMessageType; ?>">
                        <?php echo htmlspecialchars($loginMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Two Column Layout -->
                <div class="dashboard-two-column">
                    <!-- Left Column: User Info -->
                    <div class="dashboard-sidebar">
                        <!-- Your Measurements -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-title-with-icon">
                                <h2 class="dashboard-card-title">
                                    Your Measurements
                                </h2>
                                <?php if ($selfMeasurements): ?>
                                <a href="pattern-studio.php?edit=<?php echo $selfMeasurements['id']; ?>" class="measurement-edit-icon" title="Edit Measurements">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php if ($selfMeasurements): ?>
                            <?php
                            // Get pattern type label
                            $patternTypeLabels = [
                                'blouse' => 'Blouses',
                                'kurti' => 'Kurtis',
                                'blouse_back' => 'Blouse Back Designs',
                                'blouse_front' => 'Blouse Front Designs',
                                'sleeve' => 'Sleeve Designs',
                                'pants' => 'Pants'
                            ];
                            $patternType = $selfMeasurements['pattern_type'] ?? 'blouse';
                            $patternTypeLabel = $patternTypeLabels[$patternType] ?? ucfirst($patternType);
                            ?>
                            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                <p class="measurement-category" style="margin: 0;">Category: <?php echo ucfirst($selfMeasurements['category']); ?></p>
                                <span style="display: inline-block; background: #E6FFFA; color: #319795; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                                    <?php echo $patternTypeLabel; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="dashboard-info-list">
                                <?php if ($selfMeasurements): ?>
                                    <?php if ($selfMeasurements['category'] === 'women'): ?>
                                        <!-- Women-specific measurements (Blouse) -->
                                        <?php if (!empty($selfMeasurements['blength'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Blouse Back Length:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['blength'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['fshoulder'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Full Shoulder:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['fshoulder'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['shoulder'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Shoulder Strap:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['shoulder'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['bnDepth'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Back Neck Depth:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['bnDepth'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['fndepth'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Front Neck Depth:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['fndepth'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['apex'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Shoulder to Apex:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['apex'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['flength'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Front Length:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['flength'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['chest'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Upper Chest:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['chest'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['bust'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Bust:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['bust'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['waist'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Waist:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['waist'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['slength'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Sleeve Length:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['slength'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['saround'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Arm Round:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['saround'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['sopen'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Sleeve End Round:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['sopen'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['armhole'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Armhole:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['armhole'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Generic measurements for Men/Boy/Girl -->
                                        <?php if (!empty($selfMeasurements['bust'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Chest:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['bust'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($selfMeasurements['waist'])): ?>
                                        <div class="dashboard-info-item">
                                            <span class="dashboard-info-label">Waist:</span>
                                            <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['waist'], 1); ?>"</span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Common fields for all categories -->
                                    <?php if (!empty($selfMeasurements['hips'])): ?>
                                    <div class="dashboard-info-item">
                                        <span class="dashboard-info-label">Hips:</span>
                                        <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['hips'], 1); ?>"</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($selfMeasurements['height'])): ?>
                                    <div class="dashboard-info-item">
                                        <span class="dashboard-info-label">Height:</span>
                                        <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['height'], 1); ?>"</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($selfMeasurements['inseam'])): ?>
                                    <div class="dashboard-info-item">
                                        <span class="dashboard-info-label">Inseam:</span>
                                        <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['inseam'], 1); ?>"</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($selfMeasurements['thigh_circumference'])): ?>
                                    <div class="dashboard-info-item">
                                        <span class="dashboard-info-label">Thigh Circumference:</span>
                                        <span class="dashboard-info-value"><?php echo number_format($selfMeasurements['thigh_circumference'], 1); ?>"</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="dashboard-info-item" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(177, 156, 217, 0.15);">
                                        <span class="dashboard-info-label" style="font-size: 0.75rem; color: #718096;">Last Updated:</span>
                                        <span class="dashboard-info-value" style="font-size: 0.75rem;"><?php echo date('M j, Y', strtotime($selfMeasurements['created_at'])); ?></span>
                                    </div>

                                    <?php if ($selfMeasurements['category'] === 'women'): ?>
                                    <!-- View Pattern Button -->
                                    <div style="margin-top: 1.5rem; text-align: center;">
                                        <a href="pattern-studio/savi/saviComplete.php?id=<?php echo $selfMeasurements['id']; ?>"
                                           class="btn-primary"
                                           style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                            <i data-lucide="scissors" style="width: 18px; height: 18px;"></i>
                                            View Pattern
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="padding: 1.5rem; text-align: center; color: #718096;">
                                        <p style="margin-bottom: 1rem;">No measurements found.</p>
                                        <a href="pattern-studio.php" style="color: #B19CD9; text-decoration: underline;">Submit your measurements</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Quick Actions -->
                    <div class="dashboard-main">
                        <div class="dashboard-card">
                            <h2 class="dashboard-card-title">Quick Actions</h2>
                            <div class="dashboard-actions-grid-individual">
                                <a href="pattern-studio.php" class="dashboard-action-card">
                                    <div class="dashboard-action-icon">
                                        <i data-lucide="scissors"></i>
                                    </div>
                                    <h3 class="dashboard-action-title">Pattern Studio</h3>
                                    <p class="dashboard-action-desc">Submit your measurements for custom patterns</p>
                                </a>

                                <a href="wholesale-catalog.php" class="dashboard-action-card">
                                    <div class="dashboard-action-icon">
                                        <i data-lucide="shopping-bag"></i>
                                    </div>
                                    <h3 class="dashboard-action-title">Browse Catalog</h3>
                                    <p class="dashboard-action-desc">Explore our garment collection</p>
                                </a>

                                <a href="#" class="dashboard-action-card">
                                    <div class="dashboard-action-icon">
                                        <i data-lucide="download"></i>
                                    </div>
                                    <h3 class="dashboard-action-title">My Downloads</h3>
                                    <p class="dashboard-action-desc">View your pattern downloads</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
