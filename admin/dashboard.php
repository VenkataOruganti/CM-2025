<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$adminUsername = $_SESSION['admin_username'];

// Get statistics
global $pdo;
$stats = [];

// Total public measurements
$stmt = $pdo->query("SELECT COUNT(*) as total FROM public_measurements");
$stats['total_measurements'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Measurements by category
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM public_measurements GROUP BY category");
$stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent submissions (last 7 days)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM public_measurements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['recent_submissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h2 class="admin-logo">CuttingMaster</h2>
            <p class="admin-logo-subtitle">Admin Panel</p>
        </div>

        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-link active">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="public-measurements.php" class="admin-nav-link">
                <i data-lucide="ruler"></i>
                <span>Public Measurements</span>
            </a>

            <div class="admin-nav-section">
                <span class="admin-nav-section-title">Pattern Catalog</span>
            </div>
            <a href="pattern-templates.php" class="admin-nav-link">
                <i data-lucide="settings-2"></i>
                <span>Pattern Templates</span>
            </a>
            <a href="blouse-designs.php?type=front" class="admin-nav-link">
                <i data-lucide="shirt"></i>
                <span>Front Blouse Designs</span>
            </a>
            <a href="blouse-designs.php?type=back" class="admin-nav-link">
                <i data-lucide="flip-horizontal"></i>
                <span>Back Blouse Designs</span>
            </a>
            <a href="predesigned-patterns.php" class="admin-nav-link">
                <i data-lucide="file-text"></i>
                <span>Pre-designed Patterns</span>
            </a>
            <a href="paper-sizes.php" class="admin-nav-link">
                <i data-lucide="printer"></i>
                <span>Paper Sizes</span>
            </a>

            <a href="../index.php" class="admin-nav-link" target="_blank">
                <i data-lucide="external-link"></i>
                <span>View Website</span>
            </a>
            <a href="logout.php" class="admin-nav-link admin-nav-logout">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <i data-lucide="user-circle"></i>
                <span><?php echo htmlspecialchars($adminUsername); ?></span>
            </div>
        </div>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1 class="admin-page-title">Dashboard Overview</h1>
        </header>

        <div class="admin-content">
            <!-- Statistics Cards -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i data-lucide="database"></i>
                    </div>
                    <div class="admin-stat-content">
                        <p class="admin-stat-label">Total Measurements</p>
                        <h2 class="admin-stat-value"><?php echo number_format($stats['total_measurements']); ?></h2>
                    </div>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i data-lucide="trending-up"></i>
                    </div>
                    <div class="admin-stat-content">
                        <p class="admin-stat-label">Last 7 Days</p>
                        <h2 class="admin-stat-value"><?php echo number_format($stats['recent_submissions']); ?></h2>
                    </div>
                </div>

                <?php foreach ($stats['by_category'] as $cat): ?>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="admin-stat-content">
                        <p class="admin-stat-label"><?php echo ucfirst($cat['category']); ?></p>
                        <h2 class="admin-stat-value"><?php echo number_format($cat['count']); ?></h2>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Actions -->
            <div class="admin-card">
                <h2 class="admin-card-title">Quick Actions</h2>
                <div class="admin-actions-grid">
                    <a href="public-measurements.php" class="admin-action-button">
                        <i data-lucide="ruler"></i>
                        <span>View All Measurements</span>
                    </a>
                    <a href="public-measurements.php?export=csv" class="admin-action-button">
                        <i data-lucide="download"></i>
                        <span>Export to CSV</span>
                    </a>
                    <a href="../pages/pattern-studio.php" class="admin-action-button" target="_blank">
                        <i data-lucide="external-link"></i>
                        <span>Open Pattern Studio</span>
                    </a>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="admin-card">
                <h2 class="admin-card-title">Welcome, <?php echo htmlspecialchars($adminUsername); ?>!</h2>
                <p class="admin-card-text">
                    You have access to the CuttingMaster admin dashboard. From here, you can view and analyze all public measurement submissions collected through the Pattern Studio.
                </p>
                <p class="admin-card-text">
                    Use the sidebar navigation to access different sections of the admin panel.
                </p>
            </div>
        </div>
    </main>

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
