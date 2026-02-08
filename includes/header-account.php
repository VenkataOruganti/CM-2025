<?php
/**
 * =============================================================================
 * ACCOUNT HEADER - Shared Navigation for Dashboard/Account Pages
 * =============================================================================
 *
 * PURPOSE:
 * Provides consistent navigation header for all logged-in user pages including:
 * - dashboard.php (Individual/Boutique)
 * - dashboard-pattern-provider.php
 * - dashboard-wholesaler.php
 * - edit-profile.php
 * - And other authenticated pages
 *
 * REQUIRED VARIABLES (set before including):
 * - $currentUser: Array with user data (from getCurrentUser())
 * - $userTypeLabel: Display label for user type (optional, auto-generated if not set)
 *
 * OPTIONAL VARIABLES:
 * - $pageTitle: Page title for <title> tag
 * - $activePage: Current page identifier for nav highlighting
 * - $additionalStyles: Extra CSS to include in header
 * - $isBoutique: Boolean for boutique-specific menu items
 *
 * USAGE:
 *   <?php
 *   $pageTitle = 'Dashboard';
 *   $activePage = 'dashboard';
 *   include __DIR__ . '/../includes/header-account.php';
 *   ?>
 *
 * =============================================================================
 */

// Initialize language system if not already done
if (!class_exists('Lang')) {
    require_once __DIR__ . '/lang-init.php';
}

// Generate user type label if not provided
if (!isset($userTypeLabel) && isset($currentUser)) {
    $userType = $currentUser['user_type'] ?? 'individual';
    $userTypeLabels = [
        'individual' => __('dashboard.individual_title'),
        'boutique' => __('dashboard.boutique_title'),
        'pattern_provider' => __('dashboard.pattern_provider_title'),
        'wholesaler' => __('dashboard.wholesaler_title'),
    ];
    $userTypeLabel = $userTypeLabels[$userType] ?? ucfirst(str_replace('_', ' ', $userType));
}

// Determine user type flags
$isBoutique = isset($currentUser) && ($currentUser['user_type'] ?? '') === 'boutique';
$isPatternProvider = isset($currentUser) && ($currentUser['user_type'] ?? '') === 'pattern_provider';
$isWholesaler = isset($currentUser) && ($currentUser['user_type'] ?? '') === 'wholesaler';
?>
<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" dir="<?php echo Lang::getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CuttingMaster' : 'CuttingMaster'; ?></title>

    <!-- Google Fonts (including Noto Sans for Telugu/Hindi and Sirivennela for Telugu titles) -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&family=Noto+Sans:wght@300;400;500;600&family=Noto+Sans+Telugu:wght@300;400;500;600&family=Noto+Sans+Devanagari:wght@300;400;500;600&family=Sirivennela&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : '../css/styles.css'; ?>">

    <?php if (isset($additionalStyles)): ?>
        <!-- Additional page-specific styles -->
        <style><?php echo $additionalStyles; ?></style>
    <?php endif; ?>

    <!-- Mobile Navigation Styles v3 -->
    <style>
        /* Hamburger Button - Always hidden on desktop */
        #hamburgerBtn {
            display: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: none;
            border: none;
            padding: 10px;
            margin: 0;
            cursor: pointer;
            z-index: 9999;
            position: relative;
        }

        #hamburgerBtn span {
            display: block;
            width: 22px;
            height: 2px;
            background: #333;
            margin: 5px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* X animation when open */
        body.nav-open #hamburgerBtn span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        body.nav-open #hamburgerBtn span:nth-child(2) {
            opacity: 0;
        }
        body.nav-open #hamburgerBtn span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Dark overlay */
        #navOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9000;
        }

        body.nav-open #navOverlay {
            display: none;
        }

        /* MOBILE STYLES */
        @media (max-width: 992px) {
            .nav-container {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding: 12px 16px !important;
            }

            #hamburgerBtn {
                display: block !important;
            }

            .logo {
                flex-shrink: 0;
            }

            .logo img {
                height: 34px !important;
                width: auto !important;
            }

            /* Slide-out menu panel */
            .nav-links {
                position: fixed !important;
                top: 0 !important;
                right: -100% !important;
                width: 280px !important;
                max-width: 85vw !important;
                height: 100% !important;
                background: #fff !important;
                z-index: 9500 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                align-items: stretch !important;
                padding: 70px 0 30px 0 !important;
                box-shadow: -2px 0 10px rgba(0,0,0,0.2) !important;
                transition: right 0.3s ease !important;
                overflow-y: auto !important;
            }

            /* Show menu when body has nav-open */
            body.nav-open .nav-links {
                right: 0 !important;
            }

            .nav-links .nav-link {
                display: block !important;
                padding: 16px 20px !important;
                color: #333 !important;
                text-decoration: none !important;
                border-bottom: 1px solid #eee !important;
                font-size: 15px !important;
            }

            .nav-links .nav-link:active {
                background: #f5f5f5 !important;
            }

            .nav-links .btn-secondary {
                display: block !important;
                margin: 0 !important;
                padding: 16px 20px !important;
                text-align: left !important;
                background: transparent !important;
                color: #333 !important;
                border-radius: 0 !important;
                text-decoration: none !important;
                border-bottom: 1px solid #eee !important;
                font-size: 15px !important;
            }

            /* Account dropdown mobile */
            .nav-dropdown {
                border-bottom: 1px solid #eee !important;
            }

            .nav-dropdown-toggle {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 16px 20px !important;
                color: #333 !important;
                text-decoration: none !important;
                width: 100% !important;
            }

            .nav-dropdown-menu {
                display: none !important;
                background: #f9f9f9 !important;
            }

            .nav-dropdown.open .nav-dropdown-menu {
                display: block !important;
            }

            .nav-dropdown-item {
                padding: 12px 30px !important;
                font-size: 14px !important;
            }

            /* Language switcher mobile */
            .lang-switcher {
                margin: 20px !important;
                width: calc(100% - 40px) !important;
            }

            .lang-switcher-btn {
                width: 100% !important;
                justify-content: center !important;
                padding: 14px !important;
                background: #f3f4f6 !important;
                border: 1px solid #ddd !important;
                border-radius: 8px !important;
            }

            .lang-menu {
                position: relative !important;
                top: auto !important;
                right: auto !important;
                margin-top: 8px !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                display: none !important;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                border-radius: 8px !important;
            }

            .lang-switcher.open .lang-menu {
                display: block !important;
            }

            .lang-option {
                padding: 14px 16px !important;
            }
        }

        /* Lock body scroll when nav open */
        body.nav-open {
            overflow: hidden !important;
            position: fixed !important;
            width: 100% !important;
        }
    </style>

    <!-- Telugu-specific styles -->
    <?php if (Lang::current() === 'te'): ?>
    <style>
        .hero-title {
            font-family: 'Sirivennela', 'Noto Sans Telugu', sans-serif !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
        }
        .hero-title-accent {
            font-family: 'Sirivennela', 'Noto Sans Telugu', sans-serif !important;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <!-- Background Glow Effects -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <!-- Dark Overlay -->
    <div id="navOverlay"></div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo logo-clickable" onclick="window.location.href='../index.php'">
                <img src="../images/cm-logo.svg" alt="CuttingMaster" style="height: 40px; width: auto;">
            </div>

            <!-- Hamburger Button -->
            <button type="button" id="hamburgerBtn" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-links" id="navLinks">
                <a href="pattern-studio.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'pattern-studio') ? ' active-nav-link' : ''; ?>"><?php _e('dashboard.nav.pattern_studio'); ?></a>
                <a href="wholesale-catalog.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'wholesale-catalog') ? ' active-nav-link' : ''; ?>"><?php _e('dashboard.nav.wholesale'); ?></a>
                <a href="contact-us.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'contact-us') ? ' active-nav-link' : ''; ?>"><?php _e('dashboard.nav.contact'); ?></a>

                <!-- Account Dropdown -->
                <div class="nav-dropdown" id="accountDropdown">
                    <a href="dashboard.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'dashboard') ? ' active-nav-link' : ''; ?> nav-dropdown-toggle" id="accountToggle">
                        <?php _e('dashboard.nav.your_account'); ?>
                        <i data-lucide="chevron-down" class="nav-dropdown-icon"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <?php if ($isBoutique): ?>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label"><?php _e('dashboard.nav.business_name'); ?></span>
                                <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['business_name'] ?? ''); ?></span>
                            </div>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label"><?php _e('dashboard.nav.owner_name'); ?></span>
                                <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            </div>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label"><?php _e('dashboard.nav.location'); ?></span>
                                <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['business_location'] ?? ''); ?></span>
                            </div>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label"><?php _e('dashboard.nav.mobile'); ?></span>
                                <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['mobile_number'] ?? ''); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label"><?php _e('dashboard.nav.name'); ?></span>
                                <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label"><?php _e('dashboard.nav.email'); ?></span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label"><?php _e('dashboard.nav.account_type'); ?></span>
                            <span class="nav-dropdown-value"><?php echo $userTypeLabel; ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label"><?php _e('dashboard.nav.status'); ?></span>
                            <span class="nav-dropdown-value"><?php echo ucfirst($currentUser['status']); ?></span>
                        </div>
                        <div class="nav-dropdown-divider"></div>
                        <a href="edit-profile.php" class="nav-dropdown-link">
                            <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                            <?php _e('dashboard.nav.edit_profile'); ?>
                        </a>
                    </div>
                </div>

                <a href="logout.php" class="btn-secondary btn-link btn-no-border"><?php _e('dashboard.nav.logout'); ?></a>

                <!-- Language Switcher -->
                <?php include __DIR__ . '/lang-switcher.php'; ?>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Script -->
    <script>
    (function() {
        var hamburger = document.getElementById('hamburgerBtn');
        var overlay = document.getElementById('navOverlay');
        var body = document.body;

        function openNav() {
            body.classList.add('nav-open');
        }

        function closeNav() {
            body.classList.remove('nav-open');
        }

        function toggleNav() {
            if (body.classList.contains('nav-open')) {
                closeNav();
            } else {
                openNav();
            }
        }

        // Hamburger click
        if (hamburger) {
            hamburger.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleNav();
                return false;
            };
        }

        // Overlay click to close
        if (overlay) {
            overlay.onclick = function() {
                closeNav();
            };
        }

        // ESC key to close
        document.onkeydown = function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeNav();
            }
        };

        // Account dropdown toggle
        var accToggle = document.getElementById('accountToggle');
        var accDropdown = document.getElementById('accountDropdown');
        if (accToggle && accDropdown) {
            accToggle.onclick = function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    accDropdown.classList.toggle('open');
                }
            };
        }
    })();
    </script>
