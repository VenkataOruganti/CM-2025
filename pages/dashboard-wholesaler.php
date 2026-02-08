<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

// Require login and check user type
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser || $currentUser['user_type'] !== 'wholesaler') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wholesaler Dashboard - CuttingMaster</title>

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
                    <a href="dashboard-wholesaler.php" class="nav-link active-nav-link nav-dropdown-toggle">
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
                            <span class="nav-dropdown-value">Wholesaler</span>
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
                <p class="hero-tag">Wholesale Partner</p>
                <h1 class="hero-title auth-title">
                    Wholesaler <span class="hero-title-accent">Dashboard</span>
                </h1>
                <p class="hero-description auth-description">
                    Welcome, <?php echo htmlspecialchars($currentUser['business_name']); ?>!
                </p>

                <!-- Business Info Card -->
                <div class="dashboard-card">
                    <h2 class="dashboard-card-title">Business Information</h2>
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
                            <span class="dashboard-info-value">Garment Wholesaler</span>
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
                    <h2 class="dashboard-card-title">Wholesale Tools</h2>
                    <div class="dashboard-actions-grid">
                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="package"></i>
                            </div>
                            <h3 class="dashboard-action-title">Upload Catalog</h3>
                            <p class="dashboard-action-desc">Add garments to your wholesale catalog</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="grid"></i>
                            </div>
                            <h3 class="dashboard-action-title">My Catalog</h3>
                            <p class="dashboard-action-desc">Manage your product listings</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="shopping-cart"></i>
                            </div>
                            <h3 class="dashboard-action-title">Orders</h3>
                            <p class="dashboard-action-desc">View and manage wholesale orders</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="users"></i>
                            </div>
                            <h3 class="dashboard-action-title">Buyers</h3>
                            <p class="dashboard-action-desc">Manage buyer relationships</p>
                        </a>

                        <a href="#" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="trending-up"></i>
                            </div>
                            <h3 class="dashboard-action-title">Sales Reports</h3>
                            <p class="dashboard-action-desc">View sales analytics and insights</p>
                        </a>

                        <a href="contact-us.php" class="dashboard-action-card">
                            <div class="dashboard-action-icon">
                                <i data-lucide="headphones"></i>
                            </div>
                            <h3 class="dashboard-action-title">Wholesale Support</h3>
                            <p class="dashboard-action-desc">Get dedicated wholesale support</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
