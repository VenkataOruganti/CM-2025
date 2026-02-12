<?php
// Initialize language system
require_once __DIR__ . '/lang-init.php';
?>
<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" dir="<?php echo Lang::getDirection(); ?>">
<head>
    <meta charset="UTF-8">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SF50W5Q0XL"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-SF50W5Q0XL');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CuttingMaster' : 'CuttingMaster - Customized Pattern Making Tool'; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo isset($metaDescription) ? htmlspecialchars($metaDescription) : 'CuttingMaster - Leading customized pattern making tool in India. Generate precise tailoring patterns from body measurements. Perfect-fit saree blouses, kurtis, and garment patterns for tailors and boutiques.'; ?>">
    <meta name="keywords" content="<?php echo isset($metaKeywords) ? htmlspecialchars($metaKeywords) : 'customized pattern making, pattern making tool, tailoring patterns online, custom pattern generator, saree blouse patterns, body measurement patterns, garment patterns India, digital pattern making'; ?>">
    <meta name="author" content="CuttingMaster">
    <meta name="robots" content="<?php echo isset($metaRobots) ? $metaRobots : 'index, follow'; ?>">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CuttingMaster' : 'CuttingMaster - Customized Pattern Making Tool'; ?>">
    <meta property="og:description" content="<?php echo isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Leading customized pattern making tool in India. Generate precise tailoring patterns from body measurements.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://cuttingmaster.in<?php echo $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="https://cuttingmaster.in/images/cm-logo.svg">
    <meta property="og:site_name" content="CuttingMaster">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CuttingMaster' : 'CuttingMaster - Customized Pattern Making Tool'; ?>">
    <meta name="twitter:description" content="<?php echo isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Leading customized pattern making tool in India. Generate precise tailoring patterns from body measurements.'; ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://cuttingmaster.in<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "CuttingMaster",
        "url": "https://cuttingmaster.in",
        "logo": "https://cuttingmaster.in/images/cm-logo.svg",
        "description": "Leading customized pattern making tool in India. Generate precise tailoring patterns from body measurements for saree blouses, kurtis, and garments.",
        "areaServed": {
            "@type": "Country",
            "name": "India"
        },
        "serviceType": ["Pattern Making", "Tailoring Patterns", "Custom Garment Patterns"],
        "knowsLanguage": ["en", "hi", "te"],
        "sameAs": []
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "CuttingMaster",
        "url": "https://cuttingmaster.in",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://cuttingmaster.in/pages/pattern-studio.php?q={search_term_string}",
            "query-input": "required name=search_term_string"
        },
        "inLanguage": ["en", "hi", "te"]
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "CuttingMaster Pattern Studio",
        "applicationCategory": "DesignApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "INR"
        },
        "description": "Online pattern making tool for tailors and boutiques in India. Generate custom-fitted patterns for saree blouses, kurtis, and other garments.",
        "availableLanguage": ["English", "Hindi", "Telugu"]
    }
    </script>

    <!-- Google Fonts (including Noto Sans for Telugu/Hindi and Sirivennela for Telugu titles) -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&family=Noto+Sans:wght@300;400;500;600&family=Noto+Sans+Telugu:wght@300;400;500;600&family=Noto+Sans+Devanagari:wght@300;400;500;600&family=Sirivennela&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : 'css/styles.css'; ?>">

    <?php if (isset($additionalStyles)): ?>
        <!-- Additional page-specific styles -->
        <style><?php echo $additionalStyles; ?></style>
    <?php endif; ?>

    <?php if (isset($additionalHeadScripts)): ?>
        <!-- Additional page-specific head scripts (e.g., reCAPTCHA) -->
        <?php echo $additionalHeadScripts; ?>
    <?php endif; ?>

    <!-- Two-Row Navigation Styles -->
    <style>
        /* Header background - full width */
        #navbar {
            background: linear-gradient(135deg, #f8f6fc 0%, #fff 50%, #f5f9f8 100%);
            box-shadow: 0 2px 10px rgba(139, 123, 168, 0.1);
        }
        #navbar .nav-container {
            padding: 0.75rem 1.5rem;
        }
        #navbar.scrolled {
            background: rgba(248, 246, 252, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px rgba(139, 123, 168, 0.15);
        }

        /* Desktop Two-Row Navigation */
        .nav-row-1 {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .nav-row-1 .nav-link {
            font-size: 0.75rem !important;
            letter-spacing: 0.5px;
            opacity: 0.85;
        }
        .nav-row-1 .lang-switcher-btn {
            font-size: 0.75rem !important;
            padding: 4px 10px;
        }

        .nav-row-2 {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .nav-row-2 .nav-link,
        .nav-row-2 .btn-secondary {
            font-size: 0.9rem !important;
        }

        .nav-rows-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        /* Hamburger Button */
        #hamburgerBtn {
            display: none;
            background: none;
            border: none;
            padding: 12px;
            cursor: pointer;
            z-index: 10000;
            -webkit-tap-highlight-color: transparent;
        }
        #hamburgerBtn span {
            display: block;
            width: 24px;
            height: 3px;
            background: #333;
            margin: 4px 0;
            border-radius: 2px;
        }

        /* MOBILE ONLY */
        @media (max-width: 992px) {
            /* Show hamburger */
            #hamburgerBtn { display: block; }

            /* Nav container layout */
            .nav-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 15px;
            }

            /* Logo */
            .logo img { height: 36px; width: auto; }

            /* Hide rows wrapper by default, show when open */
            .nav-rows-wrapper {
                display: none;
                position: fixed;
                top: 0;
                right: 0;
                width: 280px;
                height: 100vh;
                background: #fff;
                z-index: 9999;
                flex-direction: column;
                align-items: stretch;
                padding-top: 60px;
                box-shadow: -3px 0 15px rgba(0,0,0,0.2);
                overflow-y: auto;
                gap: 0;
            }

            /* Show menu when body has nav-open class */
            body.nav-open .nav-rows-wrapper {
                display: flex;
            }

            /* Stack rows vertically on mobile */
            .nav-row-1,
            .nav-row-2 {
                flex-direction: column;
                align-items: stretch;
                gap: 0;
            }

            .nav-row-1 .nav-link,
            .nav-row-2 .nav-link,
            .nav-row-1 .btn-secondary,
            .nav-row-2 .btn-secondary {
                font-size: 16px !important;
                opacity: 1;
            }

            /* Menu items */
            .nav-rows-wrapper .nav-link,
            .nav-rows-wrapper .btn-secondary {
                display: block;
                padding: 15px 20px;
                color: #333;
                text-decoration: none;
                border-bottom: 1px solid #eee;
                font-size: 16px;
                text-align: left;
                background: none;
                margin: 0;
                border-radius: 0;
            }

            /* Account dropdown */
            .nav-dropdown { border-bottom: 1px solid #eee; }
            .nav-dropdown-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                color: #333;
                text-decoration: none;
                width: 100%;
            }
            .nav-dropdown-menu { display: none; background: #f5f5f5; }
            .nav-dropdown.open .nav-dropdown-menu { display: block; }
            .nav-dropdown-item { padding: 12px 30px; font-size: 14px; text-align: left; }

            /* Language switcher */
            .lang-switcher { margin: 15px 20px; width: auto; }
            .lang-switcher-btn {
                width: 100%;
                padding: 12px 15px !important;
                background: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 16px !important;
                text-align: left;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .lang-menu {
                position: static;
                margin-top: 8px;
                display: none;
                border: 1px solid #ddd;
                border-radius: 6px;
                box-shadow: none;
                opacity: 1;
                visibility: visible;
                transform: none;
            }
            .lang-switcher.open .lang-menu { display: block; }
            .lang-option { padding: 12px 15px; text-align: left; }

            /* Lock scroll when open */
            body.nav-open { overflow: hidden; }
        }

        /* Mobile Hero */
        @media (max-width: 992px) {
            .hero-container {
                display: flex;
                flex-direction: column-reverse;
                gap: 2rem;
            }
            .hero-content { text-align: center; }
            .hero-buttons { justify-content: center; }
            .hero-visual {
                width: 100% !important;
            }
        }
    </style>

    <!-- Telugu-specific styles (loaded last to override all other styles) -->
    <?php if (Lang::current() === 'te'): ?>
    <style>
        .hero-title {
            font-family: 'Sirivennela', 'Noto Sans Telugu', sans-serif !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
            font-size: 83px !important;
        }
        .portfolio-title,
        .section-title,
        .why-title,
        .cta-title {
            font-family: 'Sirivennela', 'Noto Sans Telugu', sans-serif !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
        }
        .hero-title-accent,
        .section-title-accent {
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

    <!-- Overlay removed - was causing iOS issues -->

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo logo-clickable" onclick="window.location.href='<?php echo isset($logoLink) ? $logoLink : 'index.php'; ?>'">
                <img src="<?php echo isset($logoPath) ? $logoPath : 'images/cm-logo.svg'; ?>" alt="CuttingMaster" style="height: 40px; width: auto;">
            </div>

            <!-- Hamburger Button -->
            <button type="button" id="hamburgerBtn" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-rows-wrapper" id="navLinks">
                <!-- Row 1: Contact Us, Resources, Blog, Languages (smaller font) -->
                <div class="nav-row-1">
                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/contact-us.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'contact-us') ? ' active-nav-link' : ''; ?>"><?php echo strtoupper(__('nav.contact')); ?></a>
                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/resources.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'resources') ? ' active-nav-link' : ''; ?>">RESOURCES</a>
                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/blog.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'blog') ? ' active-nav-link' : ''; ?>"><?php echo strtoupper(__('nav.blog')); ?></a>
                    <!-- Language Switcher -->
                    <?php include __DIR__ . '/lang-switcher.php'; ?>
                </div>

                <!-- Row 2: Pattern Studio, Wholesale Marketplace, Login/Register (larger font) -->
                <div class="nav-row-2">
                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/pattern-studio.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'pattern-studio') ? ' active-nav-link' : ''; ?>"><?php echo strtoupper(__('nav.pattern_studio')); ?></a>
                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/wholesale-catalog.php" class="nav-link<?php echo (isset($activePage) && $activePage === 'wholesale-catalog') ? ' active-nav-link' : ''; ?>"><?php echo strtoupper(__('nav.wholesale')); ?></a>
                    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                        <?php if (isset($currentUser)): ?>
                            <div class="nav-dropdown" id="accountDropdown">
                                <a href="#" class="nav-link nav-dropdown-toggle" id="accountToggle">
                                    <?php echo strtoupper(__('nav.account')); ?>
                                    <i data-lucide="chevron-down" class="nav-dropdown-icon"></i>
                                </a>
                                <div class="nav-dropdown-menu">
                                    <!-- Navigation Links -->
                                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/dashboard.php" class="nav-dropdown-link">
                                        <i data-lucide="layout-dashboard" style="width: 14px; height: 14px;"></i>
                                        <?php _e('nav.dashboard'); ?>
                                    </a>
                                    <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/edit-profile.php" class="nav-dropdown-link">
                                        <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                                        <?php _e('dashboard.nav.edit_profile'); ?>
                                    </a>
                                    <div class="nav-dropdown-divider"></div>
                                    <!-- User Info -->
                                    <div class="nav-dropdown-item">
                                        <span class="nav-dropdown-label"><?php _e('dashboard.nav.name'); ?>:</span>
                                        <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                                    </div>
                                    <div class="nav-dropdown-item">
                                        <span class="nav-dropdown-label"><?php _e('dashboard.nav.email'); ?>:</span>
                                        <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                                    </div>
                                    <div class="nav-dropdown-item">
                                        <span class="nav-dropdown-label"><?php _e('dashboard.nav.account_type'); ?>:</span>
                                        <span class="nav-dropdown-value"><?php echo ucfirst(str_replace('_', ' ', $currentUser['user_type'])); ?></span>
                                    </div>
                                    <div class="nav-dropdown-item">
                                        <span class="nav-dropdown-label"><?php _e('dashboard.nav.status'); ?>:</span>
                                        <span class="nav-dropdown-value"><?php echo ucfirst($currentUser['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/logout.php" class="btn-secondary btn-link btn-no-border"><?php echo strtoupper(__('nav.logout')); ?></a>
                    <?php else: ?>
                        <a href="<?php echo isset($navBase) ? $navBase : ''; ?>pages/login.php" class="btn-secondary btn-link btn-no-border"><?php echo __('nav.login'); ?> / <?php echo __('nav.register'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Script v5 - Simplified -->
    <script>
    (function() {
        var hamburger = document.getElementById('hamburgerBtn');
        var body = document.body;

        // Ensure nav is closed on page load
        body.classList.remove('nav-open');

        function toggleNav() {
            body.classList.toggle('nav-open');
        }

        function closeNav() {
            body.classList.remove('nav-open');
        }

        // Hamburger button - simple click handler
        if (hamburger) {
            hamburger.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleNav();
            };
        }

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeNav();
            }
        });

        // Account dropdown toggle
        var accToggle = document.getElementById('accountToggle');
        var accDropdown = document.getElementById('accountDropdown');
        if (accToggle && accDropdown) {
            accToggle.onclick = function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    e.stopPropagation();
                    accDropdown.classList.toggle('open');
                }
            };
        }
    })();
    </script>

