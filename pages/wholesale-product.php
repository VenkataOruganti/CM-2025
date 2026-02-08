<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId === 0) {
    header('Location: wholesale-catalog.php');
    exit;
}

// Fetch product details with vendor info
$product = null;
$vendor = null;
try {
    $stmt = $pdo->prepare("
        SELECT wp.*, u.username as vendor_name, u.mobile_number as vendor_mobile,
               u.business_name as vendor_company, u.business_location as vendor_location
        FROM wholesale_portfolio wp
        LEFT JOIN users u ON wp.vendor_id = u.id
        WHERE wp.id = ? AND wp.status = 'active'
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $product['vendor_id']) {
        $vendor = [
            'name' => $product['vendor_name'],
            'mobile' => $product['vendor_mobile'],
            'company' => $product['vendor_company'],
            'location' => $product['vendor_location']
        ];
    }
} catch(PDOException $e) {
    $product = null;
}

if (!$product) {
    header('Location: wholesale-catalog.php');
    exit;
}

// Fetch variants for this product
$variants = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM wholesale_variants WHERE product_id = ? AND status = 'active' ORDER BY display_order ASC, created_at DESC");
    $stmt->execute([$productId]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $variants = [];
}
?>
<?php
// Prepare dynamic SEO meta tags based on product
$productTitle = htmlspecialchars($product['title']);
$productDescription = !empty($product['description']) ? htmlspecialchars(substr($product['description'], 0, 150)) : 'Quality wholesale product';
$productCategory = !empty($product['category']) ? htmlspecialchars($product['category']) : 'Wholesale';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $productTitle; ?> - Wholesale | CuttingMaster</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Buy <?php echo $productTitle; ?> wholesale at CuttingMaster. <?php echo $productDescription; ?>. Bulk ordering available for boutiques and retailers across India.">
    <meta name="keywords" content="<?php echo $productTitle; ?>, <?php echo $productCategory; ?> wholesale, bulk order, wholesale India, boutique suppliers">
    <meta name="author" content="CuttingMaster">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $productTitle; ?> - Wholesale | CuttingMaster">
    <meta property="og:description" content="Buy <?php echo $productTitle; ?> wholesale. <?php echo $productDescription; ?>">
    <meta property="og:type" content="product">
    <meta property="og:url" content="https://cuttingmaster.in/pages/wholesale-product.php?id=<?php echo $productId; ?>">
    <?php if (!empty($product['image'])): ?>
    <meta property="og:image" content="https://cuttingmaster.in/<?php echo htmlspecialchars($product['image']); ?>">
    <?php else: ?>
    <meta property="og:image" content="https://cuttingmaster.in/images/cm-logo.svg">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="https://cuttingmaster.in/pages/wholesale-product.php?id=<?php echo $productId; ?>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .product-hero {
            padding: 10rem 2rem 3rem;
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.05), rgba(177, 156, 217, 0.05));
        }

        .product-hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .product-image-container {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.1), rgba(177, 156, 217, 0.1));
        }

        .product-image-container img {
            width: 100%;
            height: auto;
            display: block;
            object-position: top;
        }

        .product-details {
            padding: 2rem 0;
        }

        .product-category-badge {
            display: inline-block;
            background: linear-gradient(135deg, #FFB6D9, #B19CD9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .product-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            font-weight: 400;
            color: #2D3748;
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #B19CD9;
            margin-bottom: 0.25rem;
        }

        .product-tax-note {
            font-size: 0.8rem;
            color: rgba(45, 55, 72, 0.6);
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .product-description {
            color: rgba(45, 55, 72, 0.8);
            line-height: 1.8;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .product-meta {
            border-top: 1px solid rgba(177, 156, 217, 0.2);
            padding-top: 1.5rem;
        }

        .product-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: rgba(45, 55, 72, 0.7);
            font-size: 0.8rem;
        }

        .product-meta-item i {
            color: #B19CD9;
            width: 14px;
            height: 14px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #B19CD9;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #8B7BA8;
        }

        /* Variants Section */
        .variants-section {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .variants-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .variants-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            color: #2D3748;
        }

        .variants-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .variant-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .variant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(177, 156, 217, 0.2);
        }

        .variant-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .variant-image-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.1), rgba(177, 156, 217, 0.1));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(177, 156, 217, 0.5);
        }

        .variant-info {
            padding: 1.25rem;
        }

        .variant-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 500;
            color: #2D3748;
            margin-bottom: 0.5rem;
        }

        .variant-description {
            font-size: 0.85rem;
            color: rgba(45, 55, 72, 0.7);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .variant-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .variant-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: rgba(177, 156, 217, 0.1);
            border-radius: 4px;
            font-size: 0.75rem;
            color: #8B7BA8;
        }

        .variant-price {
            font-size: 1rem;
            font-weight: 600;
            color: #B19CD9;
        }

        .no-variants {
            text-align: center;
            padding: 3rem;
            color: rgba(45, 55, 72, 0.6);
            font-style: italic;
            grid-column: 1 / -1;
        }

        .inquiry-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #FFB6D9, #B19CD9);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
        }

        .inquiry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(177, 156, 217, 0.4);
        }

        /* Variants Section (below Inquiry button) */
        .variants-inline-section {
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(177, 156, 217, 0.2);
        }

        .variants-inline-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 400;
            color: #2D3748;
            margin-bottom: 1.5rem;
        }

        .variants-inline-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .variant-card-vertical {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .variant-card-vertical:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(177, 156, 217, 0.2);
        }

        .variant-card-image {
            width: 100%;
            height: 156px;
            object-fit: cover;
            object-position: top;
            display: block;
        }

        .variant-card-placeholder {
            width: 100%;
            height: 156px;
            background: linear-gradient(135deg, rgba(255, 182, 217, 0.15), rgba(177, 156, 217, 0.15));
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(177, 156, 217, 0.5);
        }

        .variant-card-info {
            padding: 0.75rem;
        }

        .variant-card-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0 0 0.35rem 0;
        }

        .variant-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 0.35rem;
        }

        .variant-card-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.15rem 0.4rem;
            background: rgba(177, 156, 217, 0.1);
            border-radius: 4px;
            font-size: 0.65rem;
            color: #8B7BA8;
        }

        .variant-card-price {
            font-size: 0.9rem;
            font-weight: 600;
            color: #B19CD9;
            margin: 0;
        }

        @media (max-width: 576px) {
            .variants-inline-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Contact Info Section (inline) */
        .contact-inline-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(177, 156, 217, 0.2);
        }

        .contact-inline-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .contact-inline-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .contact-inline-item.icon-only {
            align-items: center;
            opacity: 0.4;
        }

        .contact-label {
            font-size: 0.7rem;
            color: rgba(45, 55, 72, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.15rem;
        }

        .contact-value {
            font-size: 0.9rem;
            color: #2D3748;
            font-weight: 500;
        }

        @media (max-width: 576px) {
            .contact-inline-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .product-hero-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .variants-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .variants-grid {
                grid-template-columns: 1fr;
            }

            .product-title {
                font-size: 1.8rem;
            }
        }

        /* Disclaimer Section (inline) */
        .disclaimer-inline-section {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(177, 156, 217, 0.15);
        }

        .disclaimer-inline-header {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
            color: rgba(139, 123, 168, 0.7);
            font-weight: 500;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .disclaimer-inline-text {
            color: rgba(0, 0, 0, 0.9);
            font-size: 0.7rem;
            line-height: 1.5;
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
                <!-- <a href="tailoring.php" class="nav-link">TAILORING</a> -->
                <a href="wholesale-catalog.php" class="nav-link active-nav-link">WHOLESALE MARKETPLACE</a>
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

    <!-- Product Hero Section -->
    <section class="product-hero">
        <div class="product-hero-container">
            <div>
                <a href="wholesale-catalog.php" class="back-link">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Back to Catalog
                </a>
                <div class="product-image-container">
                    <?php if ($product['image']): ?>
                        <img id="main-product-image" src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" data-original-src="../<?php echo htmlspecialchars($product['image']); ?>">
                    <?php else: ?>
                        <div style="width: 100%; height: 500px; background: linear-gradient(135deg, rgba(255, 182, 217, 0.1), rgba(177, 156, 217, 0.1)); display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="package" style="width: 80px; height: 80px; color: rgba(177, 156, 217, 0.4);"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-details">
                <span class="product-category-badge"><?php echo htmlspecialchars($product['category'] ?? 'Wholesale'); ?></span>
                <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                <?php
                $rawPrice = preg_replace('/^From\s*/i', '', $product['price'] ?? 'Contact for pricing');
                // Format numbers with commas (e.g., ₹28000 -> ₹28,000)
                $formattedPrice = preg_replace_callback('/(\d+)/', function($matches) {
                    return number_format((int)$matches[1]);
                }, $rawPrice);
                ?>
                <p class="product-price"><?php echo htmlspecialchars($formattedPrice); ?></p>
                <p class="product-tax-note">Taxes as Applicable</p>
                <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Premium wholesale product with customizable options.')); ?></p>

                <div class="product-meta">
                    <div class="product-meta-item">
                        <i data-lucide="package"></i>
                        <span>Minimum order quantities apply</span>
                    </div>
                    <div class="product-meta-item">
                        <i data-lucide="truck"></i>
                        <span>Bulk shipping available</span>
                    </div>
                    <div class="product-meta-item">
                        <i data-lucide="palette"></i>
                        <span><?php echo count($variants); ?> variant(s) available</span>
                    </div>
                </div>

                <!-- Variants Section -->
                <?php if (!empty($variants)): ?>
                <div class="variants-inline-section">
                    <div class="variants-inline-grid">
                        <?php foreach ($variants as $variant): ?>
                            <div class="variant-card-vertical" <?php if ($variant['image']): ?>data-variant-image="../uploads/wholesale-variants/<?php echo htmlspecialchars($variant['image']); ?>"<?php endif; ?>>
                                <?php if ($variant['image']): ?>
                                    <img src="../uploads/wholesale-variants/<?php echo htmlspecialchars($variant['image']); ?>" alt="<?php echo htmlspecialchars($variant['name']); ?>" class="variant-card-image">
                                <?php else: ?>
                                    <div class="variant-card-placeholder">
                                        <i data-lucide="image" style="width: 28px; height: 28px;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="variant-card-info">
                                    <?php if ($variant['name']): ?>
                                        <p class="variant-card-name"><?php echo htmlspecialchars($variant['name']); ?></p>
                                    <?php endif; ?>
                                    <div class="variant-card-meta">
                                        <?php if ($variant['color']): ?>
                                            <span class="variant-card-tag">
                                                <i data-lucide="palette" style="width: 10px; height: 10px;"></i>
                                                <?php echo htmlspecialchars($variant['color']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($variant['size']): ?>
                                            <span class="variant-card-tag">
                                                <i data-lucide="ruler" style="width: 10px; height: 10px;"></i>
                                                <?php echo htmlspecialchars($variant['size']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($variant['price']): ?>
                                        <p class="variant-card-price"><?php echo htmlspecialchars($variant['price']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Info Section (inline) -->
                <div class="contact-inline-section">
                    <h3 class="variants-inline-title">Vendor Contact <span style="color: #B19CD9; font-style: italic;">Information</span></h3>
                    <div class="contact-inline-grid">
                        <?php if ($vendor): ?>
                        <div class="contact-inline-item <?php echo empty($vendor['name']) ? 'icon-only' : ''; ?>">
                            <i data-lucide="user" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <?php if (!empty($vendor['name'])): ?>
                            <div>
                                <div class="contact-label">Contact Person</div>
                                <div class="contact-value"><?php echo htmlspecialchars($vendor['name']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="contact-inline-item <?php echo empty($vendor['mobile']) ? 'icon-only' : ''; ?>">
                            <i data-lucide="phone" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <?php if (!empty($vendor['mobile'])): ?>
                            <div>
                                <div class="contact-label">Mobile Number</div>
                                <div class="contact-value"><?php echo htmlspecialchars($vendor['mobile']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="contact-inline-item <?php echo empty($vendor['company']) ? 'icon-only' : ''; ?>">
                            <i data-lucide="building-2" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <?php if (!empty($vendor['company'])): ?>
                            <div>
                                <div class="contact-label">Company Name</div>
                                <div class="contact-value"><?php echo htmlspecialchars($vendor['company']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="contact-inline-item <?php echo empty($vendor['location']) ? 'icon-only' : ''; ?>">
                            <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <?php if (!empty($vendor['location'])): ?>
                            <div>
                                <div class="contact-label">Location</div>
                                <div class="contact-value"><?php echo htmlspecialchars($vendor['location']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="contact-inline-item">
                            <i data-lucide="user" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <div>
                                <div class="contact-label">Contact Person</div>
                                <div class="contact-value">Sivakumari</div>
                            </div>
                        </div>
                        <div class="contact-inline-item icon-only">
                            <i data-lucide="phone" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <div>
                                <div class="contact-label">Mobile Number</div>
                            </div>
                        </div>
                        <div class="contact-inline-item">
                            <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #B19CD9;"></i>
                            <div>
                                <div class="contact-label">Location</div>
                                <div class="contact-value">Miyapur, Hyderabad</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Disclaimer Section (inline) -->
                <div class="disclaimer-inline-section">
                    <div class="disclaimer-inline-header">
                        <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                        <span>Disclaimer</span>
                    </div>
                    <p class="disclaimer-inline-text">
                        This is a promotional space provided by CuttingMaster. All product information, pricing, and vendor details
                        are provided by the respective vendors. CuttingMaster acts solely as a platform to showcase wholesale products
                        and does not take any responsibility for any errors, inaccuracies, or issues arising from transactions
                        between buyers and vendors. Users are advised to verify all details directly with the vendor before making
                        any purchase decisions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
        lucide.createIcons();

        // Variant hover effect - swap main image
        const mainImage = document.getElementById('main-product-image');
        const variantCards = document.querySelectorAll('.variant-card-vertical');

        if (mainImage) {
            const originalSrc = mainImage.getAttribute('data-original-src');

            variantCards.forEach(card => {
                const variantImageSrc = card.getAttribute('data-variant-image');

                if (variantImageSrc) {
                    card.addEventListener('mouseenter', function() {
                        mainImage.src = variantImageSrc;
                    });

                    card.addEventListener('mouseleave', function() {
                        mainImage.src = originalSrc;
                    });
                }
            });
        }
    </script>
</body>
</html>
