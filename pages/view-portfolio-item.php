<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require login and check user type
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser || $currentUser['user_type'] !== 'boutique') {
    header('Location: login.php');
    exit;
}

// Get item ID and paper size from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$paperSize = isset($_GET['paper']) ? htmlspecialchars($_GET['paper']) : 'A3';

if (!$itemId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch the portfolio item
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pattern_making_portfolio WHERE id = ? AND status = 'active'");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: dashboard.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .portfolio-view-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .portfolio-view-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4A5568;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: #F7FAFC;
            color: #2D3748;
        }

        .portfolio-view-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .portfolio-view-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background: #F7FAFC;
        }

        .portfolio-view-content {
            padding: 2rem;
        }

        .portfolio-view-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0 0 0.5rem 0;
        }

        .portfolio-view-description {
            color: #718096;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .portfolio-view-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 1rem 0;
            border-top: 1px solid #E2E8F0;
        }

        .portfolio-view-price {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .portfolio-view-price.free {
            color: #DC2626;
        }

        .portfolio-view-price.paid {
            color: #065F46;
        }

        .portfolio-view-paper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4A5568;
            font-size: 0.875rem;
        }

        .portfolio-view-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #4FD1C5, #38B2AC);
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 209, 197, 0.3);
        }

        .btn-secondary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #F7FAFC;
            color: #4A5568;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: 1px solid #E2E8F0;
            transition: all 0.2s;
        }

        .btn-secondary-action:hover {
            background: #EDF2F7;
        }
    </style>
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
                <a href="dashboard.php" class="nav-link active-nav-link">YOUR ACCOUNT</a>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="hero auth-section auth-section-padded" style="align-items: flex-start; padding-top: calc(4.5rem + 40px);">
        <div class="portfolio-view-container">
            <div class="portfolio-view-header">
                <a href="dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    Back to Dashboard
                </a>
            </div>

            <div class="portfolio-view-card">
                <?php if ($item['image']): ?>
                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="portfolio-view-image">
                <?php endif; ?>

                <div class="portfolio-view-content">
                    <h1 class="portfolio-view-title"><?php echo htmlspecialchars($item['title']); ?></h1>

                    <?php if ($item['description']): ?>
                        <p class="portfolio-view-description"><?php echo htmlspecialchars($item['description']); ?></p>
                    <?php endif; ?>

                    <div class="portfolio-view-meta">
                        <span class="portfolio-view-price <?php echo ($item['price'] ?? 0) == 0 ? 'free' : 'paid'; ?>">
                            <?php echo ($item['price'] ?? 0) > 0 ? '₹' . number_format($item['price'], 0) : 'Free'; ?>
                        </span>

                        <span class="portfolio-view-paper">
                            <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                            Paper Size: <?php echo htmlspecialchars($paperSize); ?>
                        </span>
                    </div>

                    <div class="portfolio-view-actions">
                        <?php if (!empty($item['code_page'])): ?>
                            <a href="<?php echo htmlspecialchars($item['code_page']); ?>?paper=<?php echo htmlspecialchars($paperSize); ?>" class="btn-primary-action">
                                <i data-lucide="scissors" style="width: 18px; height: 18px;"></i>
                                Create Pattern with Measurements
                            </a>
                        <?php else: ?>
                            <a href="pattern-studio.php" class="btn-primary-action">
                                <i data-lucide="scissors" style="width: 18px; height: 18px;"></i>
                                Create Pattern with Measurements
                            </a>
                        <?php endif; ?>
                        <a href="dashboard.php" class="btn-secondary-action">
                            <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
                            Browse More Patterns
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        lucide.createIcons();
    </script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
