<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

// Require login and check user type
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser || $currentUser['user_type'] !== 'pattern_provider') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Provider Dashboard - CuttingMaster</title>

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
                    <a href="dashboard-pattern-provider.php" class="nav-link active-nav-link nav-dropdown-toggle">
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
                            <span class="nav-dropdown-value">Pattern Provider</span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Status:</span>
                            <span class="nav-dropdown-value"><?php echo ucfirst($currentUser['status']); ?></span>
                        </div>
                    </div>
                </div>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <p class="hero-tag">Designer Partner</p>
                <h1 class="hero-title auth-title">
                    Pattern Provider <span class="hero-title-accent">Dashboard</span>
                </h1>
                <p class="hero-description auth-description">
                    Welcome, <?php echo htmlspecialchars($currentUser['business_name']); ?>!
                </p>

                <!-- Business Info Card -->
                <div class="dashboard-card">
                    <h2 class="dashboard-card-title">Provider Information</h2>
                    <div class="dashboard-info-grid">
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Business Name:</span>
                            <span class="dashboard-info-value"><?php echo htmlspecialchars($currentUser['business_name']); ?></span>
                        </div>
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Location:</span>
                            <span class="dashboard-info-value"><?php echo htmlspecialchars($currentUser['business_location']); ?></span>
                        </div>
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Contact:</span>
                            <span class="dashboard-info-value"><?php echo htmlspecialchars($currentUser['mobile_number']); ?></span>
                        </div>
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Email:</span>
                            <span class="dashboard-info-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Account Type:</span>
                            <span class="dashboard-info-value">Pattern Provider / Designer</span>
                        </div>
                        <div class="dashboard-info-item">
                            <span class="dashboard-info-label">Status:</span>
                            <span class="dashboard-info-value dashboard-status-<?php echo $currentUser['status']; ?>">
                                <?php echo ucfirst($currentUser['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <h2 class="dashboard-card-title">Designer Tools</h2>
                    <div class="dashboard-actions-grid">
                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="upload"></i>
                            </div>
                            <h3 class="dashboard-action-title">Upload Patterns</h3>
                            <p class="dashboard-action-desc">Add new pattern designs to the platform</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="layout"></i>
                            </div>
                            <h3 class="dashboard-action-title">My Patterns</h3>
                            <p class="dashboard-action-desc">Manage your pattern library</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="bar-chart"></i>
                            </div>
                            <h3 class="dashboard-action-title">Sales Analytics</h3>
                            <p class="dashboard-action-desc">Track pattern downloads and revenue</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="dollar-sign"></i>
                            </div>
                            <h3 class="dashboard-action-title">Earnings</h3>
                            <p class="dashboard-action-desc">View earnings and payment history</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="star"></i>
                            </div>
                            <h3 class="dashboard-action-title">Reviews & Ratings</h3>
                            <p class="dashboard-action-desc">View customer feedback</p>
                        </a>

                        <a href="contact-us.php" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="headphones"></i>
                            </div>
                            <h3 class="dashboard-action-title">Provider Support</h3>
                            <p class="dashboard-action-desc">Get designer support</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
