<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Fetch portfolio items
$portfolioItems = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM tailoring_portfolio WHERE status = 'active' ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $portfolioItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Table might not exist yet, silently fail
    $portfolioItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customized Tailoring Services - Custom Garments & Alterations | CuttingMaster</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Premium customized tailoring services at CuttingMaster. Custom-fitted garments, expert alterations, ethnic wear, western fashion, and bridal couture. Experience handcrafted excellence with personalized fitting.">
    <meta name="keywords" content="customized tailoring, custom tailoring India, garment alterations, ethnic wear tailoring, western fashion, bridal couture, custom fitted clothes, tailor near me, professional tailoring services">
    <meta name="author" content="CuttingMaster">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Customized Tailoring Services - CuttingMaster">
    <meta property="og:description" content="Premium customized tailoring services. Custom-fitted garments with handcrafted excellence.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://cuttingmaster.in/pages/tailoring.php">
    <meta property="og:image" content="https://cuttingmaster.in/images/cm-logo.svg">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://cuttingmaster.in/pages/tailoring.php">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .tailoring-hero {
            min-height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 12rem 2rem 0;
        }

        .tailoring-hero-content {
            max-width: 1200px;
        }

        .tailoring-hero .hero-description {
            max-width: 100%;
            margin-top: 0;
            margin-bottom: 0;
            font-size: 1rem;
        }

        .tailoring-hero .hero-title {
            margin-bottom: 0.5rem;
        }

        .tailoring-section {
            padding: 2rem 2rem 5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .tailoring-intro {
            text-align: center;
            margin-bottom: 4rem;
        }

        .tailoring-intro p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: rgba(45, 55, 72, 0.9);
            max-width: 100%;
            margin: 0 auto;
        }

        .services-showcase {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .service-showcase-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .service-showcase-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(177, 156, 217, 0.2);
        }

        .service-showcase-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .service-showcase-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.2), rgba(177, 156, 217, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .service-showcase-icon svg {
            color: #B19CD9;
        }

        .service-showcase-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 500;
            margin: 0;
            color: #2D3748;
        }

        .service-showcase-description {
            color: rgba(45, 55, 72, 0.8);
            line-height: 1.7;
            font-size: 0.875rem;
        }

        /* Portfolio Slider Section */
        .portfolio-slider-section {
            padding: 2rem 0 3rem;
            overflow: hidden;
        }

        .portfolio-slider-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 0 2rem;
        }

        .portfolio-slider-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            color: #2D3748;
        }

        .portfolio-slider-container {
            position: relative;
            width: 100%;
            padding: 0 2rem;
        }

        .portfolio-slider {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 1rem 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .portfolio-slider::-webkit-scrollbar {
            display: none;
        }

        .portfolio-slide {
            flex: 0 0 280px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .portfolio-slide:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(177, 156, 217, 0.25);
        }

        .portfolio-slide img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            object-position: top;
        }

        .portfolio-slide-info {
            padding: 1rem;
            background: white;
        }

        .portfolio-slide-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            font-weight: 500;
            color: #2D3748;
            margin-bottom: 0.25rem;
        }

        .portfolio-slide-category {
            font-size: 0.8rem;
            color: #B19CD9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .no-portfolio-message {
            text-align: center;
            padding: 3rem;
            color: rgba(45, 55, 72, 0.6);
            font-style: italic;
        }

        .process-section {
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.05), rgba(177, 156, 217, 0.05));
            padding: 5rem 2rem;
            margin: 3rem 0;
        }

        .process-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .process-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .process-step {
            text-align: center;
            padding: 2rem;
        }

        .process-step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #FFB6D9, #B19CD9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .process-step-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            color: #2D3748;
        }

        .process-step-text {
            color: rgba(45, 55, 72, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .pricing-section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .pricing-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .pricing-card:hover {
            border-color: #B19CD9;
        }

        .pricing-card.featured {
            border-color: #FFB6D9;
            position: relative;
        }

        .pricing-card.featured::before {
            content: 'Most Popular';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #FFB6D9, #B19CD9);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .pricing-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2D3748;
        }

        .pricing-price {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: #B19CD9;
            margin-bottom: 1rem;
        }

        .pricing-price span {
            font-size: 1rem;
            color: rgba(45, 55, 72, 0.6);
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            text-align: left;
        }

        .pricing-features li {
            padding: 0.5rem 0;
            color: rgba(45, 55, 72, 0.8);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pricing-features li svg {
            color: #B19CD9;
            flex-shrink: 0;
        }

        .cta-section {
            background: linear-gradient(135deg, #2D3748, #1A202C);
            padding: 5rem 2rem;
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            font-weight: 300;
            color: white;
            margin-bottom: 1rem;
        }

        .cta-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #FFB6D9, #B19CD9);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(177, 156, 217, 0.4);
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
                <a href="tailoring.php" class="nav-link active-nav-link">TAILORING</a>
                <a href="wholesale-catalog.php" class="nav-link">WHOLESALE MARKETPLACE</a>
                <a href="contact-us.php" class="nav-link">CONTACT US</a>
                <?php if ($isLoggedIn): ?>
                    <?php
                    require_once __DIR__ . '/../config/auth.php';
                    $currentUser = getCurrentUser();
                    ?>
                    <div class="nav-dropdown">
                        <a href="dashboard.php" class="nav-link nav-dropdown-toggle">
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
                                <span class="nav-dropdown-value"><?php echo ucfirst(str_replace('_', ' ', $currentUser['user_type'])); ?></span>
                            </div>
                            <div class="nav-dropdown-item">
                                <span class="nav-dropdown-label">Status:</span>
                                <span class="nav-dropdown-value"><?php echo ucfirst($currentUser['status']); ?></span>
                            </div>
                        </div>
                    </div>
                    <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary btn-link btn-no-border">LOGIN / REGISTER</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero tailoring-hero">
        <div class="tailoring-hero-content">
            <p class="hero-tag">Customized Tailoring Services</p>
            <h1 class="hero-title">
                Crafted to <span class="hero-title-accent">Perfection</span>
            </h1>
            <p class="hero-description">
                Experience the art of bespoke tailoring. Every garment is meticulously crafted to your exact measurements,<br>ensuring a perfect fit that celebrates your individuality.
            </p>
        </div>
    </section>

    <!-- Portfolio Slider Section -->
    <section class="portfolio-slider-section">
        <div class="portfolio-slider-header">
            <h2 class="portfolio-slider-title">Our <span style="color: #B19CD9; font-style: italic;">Portfolio</span></h2>
        </div>
        <div class="portfolio-slider-container">
            <?php if (!empty($portfolioItems)): ?>
                <div class="portfolio-slider">
                    <?php foreach ($portfolioItems as $item): ?>
                        <div class="portfolio-slide">
                            <img src="../uploads/tailoring-portfolio/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="portfolio-slide-info">
                                <h3 class="portfolio-slide-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="portfolio-slide-category"><?php echo htmlspecialchars($item['category']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-portfolio-message">Portfolio coming soon...</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Services Section -->
    <section class="tailoring-section">
        <!-- Services Showcase -->
        <div class="services-showcase">
            <div class="service-showcase-card">
                <div class="service-showcase-header">
                    <div class="service-showcase-icon">
                        <i data-lucide="heart" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="service-showcase-title">Bridal Collection</h3>
                </div>
                <p class="service-showcase-description">
                    Create the wedding dress of your dreams. Our bridal specialists work closely with you to design
                    a gown that makes your special day unforgettable.
                </p>
            </div>

            <div class="service-showcase-card">
                <div class="service-showcase-header">
                    <div class="service-showcase-icon">
                        <i data-lucide="sun" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="service-showcase-title">Casual Elegance</h3>
                </div>
                <p class="service-showcase-description">
                    Everyday wear that doesn't compromise on style or fit. From blouses to dresses, enjoy comfort
                    with a touch of luxury.
                </p>
            </div>

            <div class="service-showcase-card">
                <div class="service-showcase-header">
                    <div class="service-showcase-icon">
                        <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="service-showcase-title">Business Attire</h3>
                </div>
                <p class="service-showcase-description">
                    Professional suits, blazers, and office wear tailored to project confidence and sophistication in
                    any business setting.
                </p>
            </div>

            <div class="service-showcase-card">
                <div class="service-showcase-header">
                    <div class="service-showcase-icon">
                        <i data-lucide="palette" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="service-showcase-title">Custom Design</h3>
                </div>
                <p class="service-showcase-description">
                    Bring your vision to life with our custom design service. Work with our designers to create
                    unique pieces that reflect your personal style.
                </p>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="process-section">
        <div class="process-container">
            <div class="process-header">
                <p class="section-tag">Our Process</p>
                <h2 class="section-title">
                    From Consultation to <span class="section-title-accent">Creation</span>
                </h2>
            </div>

            <div class="process-steps">
                <div class="process-step">
                    <div class="process-step-number">1</div>
                    <h3 class="process-step-title">Consultation</h3>
                    <p class="process-step-text">
                        Meet with our tailoring experts to discuss your vision, preferences, and requirements for your perfect garment.
                    </p>
                </div>

                <div class="process-step">
                    <div class="process-step-number">2</div>
                    <h3 class="process-step-title">Measurement</h3>
                    <p class="process-step-text">
                        Precise measurements are taken to ensure a flawless fit. We capture every detail for perfect proportions.
                    </p>
                </div>

                <div class="process-step">
                    <div class="process-step-number">3</div>
                    <h3 class="process-step-title">Fabric Selection</h3>
                    <p class="process-step-text">
                        Choose from our curated collection of premium fabrics sourced from the finest mills around the world.
                    </p>
                </div>

                <div class="process-step">
                    <div class="process-step-number">4</div>
                    <h3 class="process-step-title">Crafting</h3>
                    <p class="process-step-text">
                        Our master tailors handcraft your garment with meticulous attention to every stitch and seam.
                    </p>
                </div>

                <div class="process-step">
                    <div class="process-step-number">5</div>
                    <h3 class="process-step-title">Fitting</h3>
                    <p class="process-step-text">
                        Try on your garment for final adjustments. We ensure everything is perfect before completion.
                    </p>
                </div>

                <div class="process-step">
                    <div class="process-step-number">6</div>
                    <h3 class="process-step-title">Delivery</h3>
                    <p class="process-step-text">
                        Receive your finished garment, beautifully packaged and ready to make you look and feel exceptional.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Experience Bespoke?</h2>
            <p class="cta-text">
                Schedule a consultation with our master tailors and begin your journey to perfectly fitted,
                beautifully crafted garments.
            </p>
            <a href="contact-us.php" class="cta-button">BOOK A CONSULTATION</a>
        </div>
    </section>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>

    <?php include __DIR__ . "/../includes/footer.php"; ?>
</body>
</html>
