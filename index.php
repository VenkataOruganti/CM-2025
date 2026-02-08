<?php
require_once __DIR__ . '/config/session.php';
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang-init.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get current user details if logged in
if ($isLoggedIn) {
    require_once __DIR__ . '/config/auth.php';
    $currentUser = getCurrentUser();
}

// Fetch portfolio items from database (limit 4 per category)
$patternPortfolio = [];
$tailoringPortfolio = [];
$wholesalePortfolio = [];

try {
    // Pattern Making Portfolio
    $stmt = $pdo->prepare("SELECT * FROM pattern_making_portfolio WHERE status = 'active' ORDER BY display_order ASC, created_at DESC LIMIT 4");
    $stmt->execute();
    $patternPortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tailoring Portfolio
    $stmt = $pdo->prepare("SELECT * FROM tailoring_portfolio WHERE status = 'active' ORDER BY display_order ASC, created_at DESC LIMIT 4");
    $stmt->execute();
    $tailoringPortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Wholesale Portfolio
    $stmt = $pdo->prepare("SELECT * FROM wholesale_portfolio WHERE status = 'active' ORDER BY display_order ASC, created_at DESC LIMIT 4");
    $stmt->execute();
    $wholesalePortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Silently fail if tables don't exist
}

// Set header variables
$pageTitle = 'Customized Pattern Making Tool - Generate Tailoring Patterns Online';
$metaDescription = 'CuttingMaster is a customized pattern making tool that generates precise tailoring patterns from your body measurements. Create perfect-fit saree blouse patterns, kurti patterns, and more. Free for individuals, professional tools for boutiques across India.';
$metaKeywords = 'customized pattern making, pattern making tool, online pattern generator, saree blouse pattern maker, tailoring patterns from measurements, custom fit patterns, digital pattern making India, body measurement to pattern';
$cssPath = 'css/styles.css';
$logoPath = 'images/cm-logo.svg';
$logoLink = 'index.php';
$navBase = '';
$activePage = ''; // No active page for home

// Page-specific scripts
$additionalScripts = "
// Product data for each service
const products = {
    'Pattern Making': [
        {
            icon: 'pencil-ruler',
            category: 'Pattern Making',
            name: 'Basic Bodice Pattern',
            description: 'Foundation pattern for custom garments',
            price: '₹45'
        },
        {
            icon: 'layout-template',
            category: 'Pattern Making',
            name: 'Sleeve Pattern Set',
            description: 'Complete sleeve variations collection',
            price: '₹35'
        },
        {
            icon: 'file-text',
            category: 'Pattern Making',
            name: 'Dress Pattern Bundle',
            description: 'Professional grade dress patterns',
            price: '₹89'
        },
        {
            icon: 'layers',
            category: 'Pattern Making',
            name: 'Grading Templates',
            description: 'Multi-size grading system',
            price: '₹125'
        }
    ],
    'Customized Tailoring': [
        {
            icon: 'shirt',
            category: 'Customized Tailoring',
            name: 'Evening Gown',
            description: 'Custom-fitted elegant evening wear',
            price: '₹890'
        },
        {
            icon: 'square-user',
            category: 'Customized Tailoring',
            name: 'Business Suit',
            description: 'Professional attire tailored to fit',
            price: '₹650'
        },
        {
            icon: 'gem',
            category: 'Customized Tailoring',
            name: 'Cocktail Dress',
            description: 'Sophisticated custom cocktail wear',
            price: '₹520'
        },
        {
            icon: 'sparkles',
            category: 'Customized Tailoring',
            name: 'Bridal Ensemble',
            description: 'Customized wedding attire',
            price: '₹1,850'
        }
    ],
    'Wholesale': [
        {
            icon: 'package-2',
            category: 'Wholesale',
            name: 'Bulk Blouse Order',
            description: 'Minimum 50 units per design',
            price: 'From ₹28/unit'
        },
        {
            icon: 'boxes',
            category: 'Wholesale',
            name: 'Collection Package',
            description: 'Complete seasonal collection',
            price: 'From ₹2,500'
        },
        {
            icon: 'truck',
            category: 'Wholesale',
            name: 'Production Run',
            description: 'Large scale manufacturing',
            price: 'Custom Quote'
        },
        {
            icon: 'store',
            category: 'Wholesale',
            name: 'Retailer Partnership',
            description: 'Exclusive boutique collections',
            price: 'From ₹5,000'
        }
    ]
};

// Current active service
let currentService = 'Pattern Making';

// Portfolio data from database
const portfolioItems = " . json_encode(array_merge(
    array_map(function($item) {
        return [
            'category' => 'Pattern Making',
            'name' => $item['title'],
            'description' => $item['description'] ?? '',
            'price' => $item['price'] ?? 0,
            'image' => $item['image'],
            'icon' => 'pencil-ruler'
        ];
    }, $patternPortfolio),
    array_map(function($item) {
        return [
            'category' => 'Customized Tailoring',
            'name' => $item['title'],
            'description' => $item['category'] ?? '',
            'image' => $item['image'],
            'icon' => 'scissors'
        ];
    }, $tailoringPortfolio),
    array_map(function($item) {
        return [
            'category' => 'Wholesale',
            'name' => $item['title'],
            'description' => $item['description'] ?? '',
            'image' => $item['image'],
            'icon' => 'store',
            'id' => $item['id']
        ];
    }, $wholesalePortfolio)
)) . ";

// Current filter
let currentFilter = 'all';

// Function to render products
function renderProducts(serviceName) {
    const grid = document.getElementById('ecommerce-grid');
    const serviceProducts = products[serviceName] || products['Pattern Making'];

    // Add changing class for transition
    grid.classList.add('changing');

    setTimeout(() => {
        grid.innerHTML = '';

        serviceProducts.forEach(product => {
            const card = document.createElement('div');
            card.className = 'ecommerce-card';
            card.innerHTML = `
                <div class=\"ecommerce-image\">
                    <div class=\"ecommerce-placeholder\">
                        <i data-lucide=\"\${product.icon}\" class=\"ecommerce-icon\" style=\"width: 64px; height: 64px; stroke-width: 1; color: rgba(177, 156, 217, 0.6);\"></i>
                        <p class=\"ecommerce-placeholder-label\">Product Image</p>
                    </div>
                </div>
                <div class=\"ecommerce-info\">
                    <p class=\"ecommerce-category\">\${product.category}</p>
                    <h3 class=\"ecommerce-name\">\${product.name}</h3>
                    <p class=\"ecommerce-description\">\${product.description}</p>
                    <p class=\"ecommerce-price\">\${product.price}</p>
                </div>
            `;
            grid.appendChild(card);
        });

        // Re-initialize Lucide icons for new content
        lucide.createIcons();

        // Remove changing class
        grid.classList.remove('changing');
    }, 300);
}

// Load default products on page load
window.addEventListener('load', function() {
    renderProducts(currentService);
});

// Function to render portfolio items
function renderPortfolio(filter = 'all') {
    console.log('renderPortfolio called with filter:', filter);
    const grid = document.getElementById('portfolio-grid');
    console.log('Grid element:', grid);

    if (!grid) {
        console.error('Portfolio grid element not found!');
        return;
    }

    grid.innerHTML = '';

    const filteredItems = filter === 'all'
        ? portfolioItems
        : portfolioItems.filter(item => item.category === filter);

    console.log('Filtered items:', filteredItems.length);

    filteredItems.forEach((item, index) => {
        const portfolioCard = document.createElement('div');
        portfolioCard.className = 'portfolio-item';
        portfolioCard.style.animationDelay = `\${index * 0.1}s`;

        // Check if item has an image from database
        const imageContent = item.image
            ? `<img src=\"\${item.image}\" alt=\"\${item.name}\" style=\"width: 100%; height: 100%; object-fit: contain; object-position: center; background: #f8f8f8;\">`
            : `<div class=\"portfolio-placeholder\">
                    <i data-lucide=\"\${item.icon}\" class=\"portfolio-icon\" style=\"width: 64px; height: 64px; stroke-width: 1;\"></i>
                    <p class=\"portfolio-placeholder-text\">Product Image</p>
                </div>`;

        // Check item category for appropriate link
        const isWholesale = item.category === 'Wholesale' && item.id;
        const isPatternMaking = item.category === 'Pattern Making';

        // Format price display for Pattern Making items
        const priceDisplay = (item.category === 'Pattern Making' && item.price !== undefined)
            ? (item.price > 0 ? `<p class=\"portfolio-price\">Price(₹) : <span style=\"color: #1A202C;\">\${item.price}</span></p>` : `<p class=\"portfolio-price\">Price(₹) : <span style=\"color: #DC2626; font-weight: 700;\">FREE</span></p>`)
            : '';

        const cardContent = `
            <div class=\"portfolio-image\">
                \${imageContent}
            </div>
            <div class=\"portfolio-overlay\">
                <h3 class=\"portfolio-name\">\${item.name}</h3>
                <p class=\"portfolio-description\">\${item.description}</p>
                \${priceDisplay}
            </div>
        `;

        if (isWholesale) {
            portfolioCard.innerHTML = `<a href=\"pages/wholesale-product.php?id=\${item.id}\" style=\"text-decoration: none; color: inherit; display: block;\">\${cardContent}</a>`;
            portfolioCard.style.cursor = 'pointer';
        } else if (isPatternMaking) {
            portfolioCard.innerHTML = `<a href=\"pages/pattern-studio.php\" style=\"text-decoration: none; color: inherit; display: block;\">\${cardContent}</a>`;
            portfolioCard.style.cursor = 'pointer';
        } else {
            portfolioCard.innerHTML = cardContent;
        }
        grid.appendChild(portfolioCard);
    });

    // Re-initialize Lucide icons for new content
    lucide.createIcons();

    console.log('Portfolio cards rendered and icons initialized');
}

// Initialize portfolio on page load
window.addEventListener('load', function() {
    console.log('About to call renderPortfolio()');
    try {
        renderPortfolio('Pattern Making');
        console.log('Portfolio initialized successfully');
    } catch(error) {
        console.error('Error initializing portfolio:', error);
    }
});

// Function to update callout visibility based on filter
function updateCallouts(filter) {
    const patternCallout = document.getElementById('pattern-callout');
    const wholesaleCallout = document.getElementById('wholesale-callout');

    // Hide all callouts first
    patternCallout.style.display = 'none';
    wholesaleCallout.style.display = 'none';

    // Show relevant callout based on filter
    if (filter === 'Pattern Making') {
        patternCallout.style.display = 'inline-flex';
    } else if (filter === 'Wholesale') {
        wholesaleCallout.style.display = 'inline-flex';
    }
    // No callout for Customized Tailoring

    lucide.createIcons();
}

// Add filter button listeners
const filterButtons = document.querySelectorAll('.filter-btn');
filterButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all buttons
        filterButtons.forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');

        // Get filter value and render
        const filter = this.getAttribute('data-filter');
        currentFilter = filter;
        renderPortfolio(filter);
        updateCallouts(filter);
    });
});

// Add hover listeners to service cards
const serviceCards = document.querySelectorAll('.service-card');
serviceCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        const serviceTitle = this.getAttribute('data-service');
        if (serviceTitle && serviceTitle !== currentService) {
            currentService = serviceTitle;
            renderProducts(serviceTitle);
        }
    });
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^=\"#\"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
";

// Include header
include __DIR__ . '/includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <p class="hero-tag"><?php _e('home.hero_tag'); ?></p>
                <h1 class="hero-title">
                    <?php _e('home.hero_title_1'); ?> <span class="hero-title-accent"><?php _e('home.hero_title_2'); ?></span><br>
                    <?php _e('home.hero_title_3'); ?>
                </h1>
                <p class="hero-description">
                    <?php _e('home.hero_description'); ?>
                </p>
                <div class="hero-buttons">
                    <a href="pages/pattern-studio.php" class="btn-large btn-solid"><?php _e('home.btn_pattern_making'); ?></a>
                    <button class="btn-large btn-outline"><?php _e('home.btn_view_portfolio'); ?></button>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-frame">
                    <div class="hero-frame-border-1"></div>
                    <div class="hero-frame-glow"></div>
                    <div class="hero-frame-image">
                        <div class="hero-carousel">
                            <div class="carousel-slides">
                                <div class="carousel-slide active">
                                    <img src="images/carousal1.png" alt="Customized Pattern Making">
                                </div>
                                <div class="carousel-slide">
                                    <img src="images/carousal2.png" alt="Custom Tailoring">
                                </div>
                                <div class="carousel-slide">
                                    <img src="images/carousal3.png" alt="Pattern Design">
                                </div>
                                <div class="carousel-slide">
                                    <img src="images/carousal4.png" alt="Wholesale Collection">
                                </div>
                                <div class="carousel-slide">
                                    <img src="images/carousal5.png" alt="Fashion Craftsmanship">
                                </div>
                            </div>
                            <div class="carousel-indicators">
                                <button class="carousel-dot active" data-slide="0"></button>
                                <button class="carousel-dot" data-slide="1"></button>
                                <button class="carousel-dot" data-slide="2"></button>
                                <button class="carousel-dot" data-slide="3"></button>
                                <button class="carousel-dot" data-slide="4"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-card">
                    <p class="hero-card-quote">
                        "<?php _e('home.hero_quote'); ?>"
                    </p>
                    <div class="hero-card-author">
                        <div class="hero-card-line"></div>
                        <span><?php _e('home.hero_author'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Use Section -->
    <section class="how-to-use">
        <div class="how-to-use-container">
            <div class="section-header">
                <p class="section-tag"><?php _e('home.how_to_use_tag'); ?></p>
                <h2 class="section-title">
                    <?php _e('home.how_to_use_title'); ?> <span class="section-title-accent"><?php _e('home.how_to_use_title_accent'); ?></span> <?php _e('home.how_to_use_title_end'); ?>
                </h2>
                <p class="how-to-use-description"><?php _e('home.how_to_use_description'); ?></p>
            </div>

            <div class="how-to-use-grid">
                <div class="how-to-use-item">
                    <div class="how-to-use-image">
                        <img src="images/step1.jpg" alt="<?php _e('home.step1_title'); ?>">
                    </div>
                    <div class="how-to-use-text">
                        <span class="step-number">01</span>
                        <h3><?php _e('home.step1_title'); ?></h3>
                        <p><?php _e('home.step1_desc'); ?></p>
                    </div>
                </div>
                <div class="how-to-use-item">
                    <div class="how-to-use-image">
                        <img src="images/step2.jpg" alt="<?php _e('home.step2_title'); ?>">
                    </div>
                    <div class="how-to-use-text">
                        <span class="step-number">02</span>
                        <h3><?php _e('home.step2_title'); ?></h3>
                        <p><?php _e('home.step2_desc'); ?></p>
                    </div>
                </div>
                <div class="how-to-use-item">
                    <div class="how-to-use-image">
                        <img src="images/step3.jpg" alt="<?php _e('home.step3_title'); ?>">
                    </div>
                    <div class="how-to-use-text">
                        <span class="step-number">03</span>
                        <h3><?php _e('home.step3_title'); ?></h3>
                        <p><?php _e('home.step3_desc'); ?></p>
                    </div>
                </div>
                <div class="how-to-use-item">
                    <div class="how-to-use-image">
                        <img src="images/step4.jpg" alt="<?php _e('home.step4_title'); ?>">
                    </div>
                    <div class="how-to-use-text">
                        <span class="step-number">04</span>
                        <h3><?php _e('home.step4_title'); ?></h3>
                        <p><?php _e('home.step4_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="pattern-studio" class="portfolio">
        <div class="portfolio-container">
            <div class="portfolio-header">
                <p class="section-tag"><?php _e('home.portfolio_tag'); ?></p>
                <h2 class="portfolio-title">
                    <?php _e('home.portfolio_title'); ?> <span style="color: #B19CD9; font-style: italic;"><?php _e('home.portfolio_title_accent'); ?></span>
                </h2>
            </div>

            <div class="portfolio-filters">
                <button class="filter-btn active" data-filter="Pattern Making"><?php _e('home.filter_pattern'); ?></button>
                <button class="filter-btn" data-filter="Wholesale"><?php _e('home.filter_wholesale'); ?></button>
                <button class="filter-btn" data-filter="Customized Tailoring"><?php _e('home.filter_tailoring'); ?></button>
            </div>

            <div class="portfolio-callouts">
                <button type="button" id="pattern-callout" class="designer-callout" onclick="openDesignerModal()">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                    <span><?php _e('home.callout_designer'); ?></span>
                </button>
                <button type="button" id="wholesale-callout" class="designer-callout" onclick="openWholesalerModal()" style="display: none;">
                    <i data-lucide="store" style="width: 16px; height: 16px;"></i>
                    <span><?php _e('home.callout_wholesaler'); ?></span>
                </button>
            </div>

            <div id="portfolio-grid" class="portfolio-grid">
                <!-- Portfolio items will be dynamically inserted here -->
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="section-header">
            <p class="section-tag"><?php _e('home.services_tag'); ?></p>
            <h2 class="section-title">
                <?php _e('home.services_title'); ?> <span class="section-title-accent"><?php _e('home.services_title_accent'); ?></span>
            </h2>
        </div>

        <div class="services-grid">
            <!-- Service 1 - Pattern Making -->
            <div class="service-card" data-service="Pattern Making">
                <div class="service-card-accent"></div>
                <div class="service-header">
                    <div class="service-icon">
                        <i data-lucide="ruler" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 class="service-title"><?php _e('home.service1_title'); ?></h3>
                </div>
                <p class="service-description">
                    <?php _e('home.service1_desc'); ?>
                </p>
                <div class="service-features">
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service1_feature1'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service1_feature2'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service1_feature3'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service1_feature4'); ?></span>
                    </div>
                </div>
                <button type="button" class="designer-callout" onclick="openDesignerModal()">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                    <span><?php _e('home.callout_designer'); ?></span>
                </button>
                <a href="pages/pattern-studio.php" class="service-link">
                    <?php _e('home.learn_more'); ?>
                    <span>→</span>
                </a>
            </div>

            <!-- Service 2 - Wholesale Marketplace -->
            <div class="service-card" data-service="Wholesale">
                <div class="service-card-accent"></div>
                <div class="service-header">
                    <div class="service-icon">
                        <i data-lucide="package" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 class="service-title"><?php _e('home.service2_title'); ?></h3>
                </div>
                <p class="service-description">
                    <?php _e('home.service2_desc'); ?>
                </p>
                <div class="service-features">
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service2_feature1'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service2_feature2'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service2_feature3'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service2_feature4'); ?></span>
                    </div>
                </div>
                <button type="button" class="designer-callout" onclick="openWholesalerModal()">
                    <i data-lucide="store" style="width: 16px; height: 16px;"></i>
                    <span><?php _e('home.callout_wholesaler'); ?></span>
                </button>
                <a href="pages/wholesale-catalog.php" class="service-link">
                    <?php _e('home.learn_more'); ?>
                    <span>→</span>
                </a>
            </div>

            <!-- Service 3 - Customized Tailoring -->
            <div class="service-card" data-service="Customized Tailoring">
                <div class="service-card-accent"></div>
                <div class="service-header">
                    <div class="service-icon">
                        <i data-lucide="scissors" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 class="service-title"><?php _e('home.service3_title'); ?></h3>
                </div>
                <p class="service-description">
                    <?php _e('home.service3_desc'); ?>
                </p>
                <div class="service-features">
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service3_feature1'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service3_feature2'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service3_feature3'); ?></span>
                    </div>
                    <div class="service-feature">
                        <i data-lucide="check" class="service-feature-icon" style="width: 16px; height: 16px;"></i>
                        <span><?php _e('home.service3_feature4'); ?></span>
                    </div>
                </div>
                <a href="pages/tailoring.php" class="service-link">
                    <?php _e('home.learn_more'); ?>
                    <span>→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-section">
        <div class="why-bg">
            <div class="why-glow-1"></div>
            <div class="why-glow-2"></div>
        </div>

        <div class="why-container">
            <div class="why-content">
                <div>
                    <p class="section-tag"><?php _e('home.why_tag'); ?></p>
                    <h2 class="why-title">
                        <?php _e('home.why_title_1'); ?><br>
                        <span class="section-title-accent"><?php _e('home.why_title_2'); ?></span> <?php _e('home.why_title_3'); ?>
                    </h2>
                </div>
                <p class="why-description">
                    <?php _e('home.why_description'); ?>
                </p>
            </div>

            <div class="why-features">
                <div class="why-feature">
                    <h3 class="why-feature-title"><?php _e('home.why_feature1_title'); ?></h3>
                    <p class="why-feature-desc"><?php _e('home.why_feature1_desc'); ?></p>
                </div>
                <div class="why-feature">
                    <h3 class="why-feature-title"><?php _e('home.why_feature2_title'); ?></h3>
                    <p class="why-feature-desc"><?php _e('home.why_feature2_desc'); ?></p>
                </div>
                <div class="why-feature">
                    <h3 class="why-feature-title"><?php _e('home.why_feature3_title'); ?></h3>
                    <p class="why-feature-desc"><?php _e('home.why_feature3_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2 class="cta-title">
                <?php _e('home.cta_title_1'); ?> <span class="hero-title-accent"><?php _e('home.cta_title_2'); ?></span> <?php _e('home.cta_title_3'); ?>
            </h2>
        </div>
    </section>

    <!-- Designer Benefits Modal -->
    <div id="designerModal" class="designer-modal">
        <div class="designer-modal-content">
            <button type="button" class="designer-modal-close" onclick="closeDesignerModal()">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
            <div class="designer-modal-header">
                <div class="designer-modal-icon">
                    <i data-lucide="sparkles" style="width: 32px; height: 32px;"></i>
                </div>
                <h2 class="designer-modal-title"><?php _e('home.modal_designer_title'); ?> <span style="color: #B19CD9;">CuttingMaster</span></h2>
                <p class="designer-modal-subtitle"><?php _e('home.modal_designer_subtitle'); ?></p>
            </div>
            <div class="designer-modal-benefits">
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_benefit1_title'); ?></h4>
                        <p><?php _e('home.modal_benefit1_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_benefit2_title'); ?></h4>
                        <p><?php _e('home.modal_benefit2_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_benefit3_title'); ?></h4>
                        <p><?php _e('home.modal_benefit3_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_benefit4_title'); ?></h4>
                        <p><?php _e('home.modal_benefit4_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="designer-modal-actions">
                <a href="pages/register.php" class="designer-modal-btn-primary">
                    <?php _e('home.btn_get_started'); ?>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </a>
                <button type="button" class="designer-modal-btn-secondary" onclick="closeDesignerModal()"><?php _e('home.btn_maybe_later'); ?></button>
            </div>
        </div>
    </div>

    <!-- Wholesaler Benefits Modal -->
    <div id="wholesalerModal" class="designer-modal">
        <div class="designer-modal-content">
            <button type="button" class="designer-modal-close" onclick="closeWholesalerModal()">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
            <div class="designer-modal-header">
                <div class="designer-modal-icon">
                    <i data-lucide="store" style="width: 32px; height: 32px;"></i>
                </div>
                <h2 class="designer-modal-title"><?php _e('home.modal_wholesaler_title'); ?> <span style="color: #B19CD9;">CuttingMaster</span></h2>
                <p class="designer-modal-subtitle"><?php _e('home.modal_wholesaler_subtitle'); ?></p>
            </div>
            <div class="designer-modal-benefits">
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="eye" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_wbenefit1_title'); ?></h4>
                        <p><?php _e('home.modal_wbenefit1_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="handshake" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_wbenefit2_title'); ?></h4>
                        <p><?php _e('home.modal_wbenefit2_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_wbenefit3_title'); ?></h4>
                        <p><?php _e('home.modal_wbenefit3_desc'); ?></p>
                    </div>
                </div>
                <div class="designer-benefit">
                    <div class="designer-benefit-icon">
                        <i data-lucide="rocket" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div class="designer-benefit-text">
                        <h4><?php _e('home.modal_wbenefit4_title'); ?></h4>
                        <p><?php _e('home.modal_wbenefit4_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="designer-modal-actions">
                <a href="pages/register.php" class="designer-modal-btn-primary">
                    <?php _e('home.btn_get_started'); ?>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </a>
                <button type="button" class="designer-modal-btn-secondary" onclick="closeWholesalerModal()"><?php _e('home.btn_maybe_later'); ?></button>
            </div>
        </div>
    </div>

    <script>
        // Designer Modal Functions
        function openDesignerModal() {
            document.getElementById('designerModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }

        function closeDesignerModal() {
            document.getElementById('designerModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Wholesaler Modal Functions
        function openWholesalerModal() {
            document.getElementById('wholesalerModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }

        function closeWholesalerModal() {
            document.getElementById('wholesalerModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on outside click
        document.getElementById('designerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDesignerModal();
            }
        });

        document.getElementById('wholesalerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWholesalerModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDesignerModal();
                closeWholesalerModal();
            }
        });

        // Hero Carousel Auto-rotation
        (function() {
            const slides = document.querySelectorAll('.carousel-slide');
            const dots = document.querySelectorAll('.carousel-dot');
            let currentSlide = 0;
            let autoRotateInterval;
            const rotationDelay = 4000; // 4 seconds between slides

            function showSlide(index) {
                // Remove active class from all slides and dots
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                // Add active class to current slide and dot
                slides[index].classList.add('active');
                dots[index].classList.add('active');
                currentSlide = index;
            }

            function nextSlide() {
                const next = (currentSlide + 1) % slides.length;
                showSlide(next);
            }

            function startAutoRotation() {
                autoRotateInterval = setInterval(nextSlide, rotationDelay);
            }

            function stopAutoRotation() {
                clearInterval(autoRotateInterval);
            }

            // Dot click handlers
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    stopAutoRotation();
                    showSlide(index);
                    startAutoRotation();
                });
            });

            // Start auto-rotation
            startAutoRotation();

            // Pause on hover (optional)
            const carousel = document.querySelector('.hero-carousel');
            if (carousel) {
                carousel.addEventListener('mouseenter', stopAutoRotation);
                carousel.addEventListener('mouseleave', startAutoRotation);
            }
        })();
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
