<?php
// Redirect to unified dashboard
header('Location: dashboard.php');
exit;

/**
 * =============================================================================
 * BOUTIQUE DASHBOARD - Main Dashboard for Boutique Users
 * =============================================================================
 *
 * PURPOSE:
 * This is the main dashboard page for boutique users (tailors/fashion businesses).
 * It provides a comprehensive interface for:
 *   1. Customer Management - Search, select, and view customer measurements
 *   2. Pattern Catalog - Browse and select patterns to generate
 *   3. Pattern Generation - Create customized patterns based on customer measurements
 *
 * PAGE STRUCTURE:
 * ┌─────────────────────────────────────────────────────────────────────────────┐
 * │ Navigation Bar (with user account dropdown)                                  │
 * ├─────────────────────────────────────────────────────────────────────────────┤
 * │ Dashboard Header (Welcome message + title)                                   │
 * ├──────────────────────────┬──────────────────────────────────────────────────┤
 * │ LEFT COLUMN (33%)        │ RIGHT COLUMN (67%)                               │
 * │ - Customer Search        │ - Print Size Selector                            │
 * │ - Customer Details       │ - Tab Navigation (4 tabs)                        │
 * │ - Pattern Type Badges    │ - Tab Content:                                   │
 * │ - Measurements Display   │   • Build Your Pattern (portfolio items)         │
 * │                          │   • Pre-designed Patterns                        │
 * │                          │   • Front Blouse Designs                         │
 * │                          │   • Back Blouse Designs                          │
 * └──────────────────────────┴──────────────────────────────────────────────────┘
 * │ Pattern Selection Modal (appears when clicking a pattern)                    │
 * └─────────────────────────────────────────────────────────────────────────────┘
 *
 * USER FLOW:
 * 1. User logs in → Redirected here if they're a "boutique" type user
 * 2. User searches/selects a customer from the dropdown
 * 3. Customer measurements are displayed with pattern type badges
 * 4. User selects a print size (A0, A2, A3, A4)
 * 5. User browses pattern catalog and clicks a pattern
 * 6. Modal opens with pattern preview and customer info
 * 7. User clicks "Download Pattern" (free) or "Buy Pattern" (paid)
 * 8. User is redirected to download page or payment page
 *
 * KEY FEATURES:
 * - Real-time customer search with autocomplete dropdown
 * - Clickable pattern type badges to switch measurement displays
 * - Live pattern preview in modal (SVG-based)
 * - Support for both free and paid patterns
 * - Responsive design for various screen sizes
 *
 * DATABASE TABLES USED:
 * - users: Current logged-in user info
 * - customers: Customer list for the boutique
 * - measurements: Customer body measurements
 * - paper_sizes: Available print sizes
 * - pattern_making_portfolio: "Build Your Pattern" items
 * - predesigned_patterns: Pre-made pattern templates
 * - blouse_designs: Front and back blouse design options
 *
 * RELATED FILES:
 * - pattern-studio.php: For adding new customers/measurements
 * - pattern-download.php: Download page for free patterns
 * - pattern-payment.php: Payment page for paid patterns
 * - ajax-get-pattern-preview.php: AJAX endpoint for pattern SVG preview
 * - saviDownloadPdf.php: PDF generation endpoint
 *
 * =============================================================================
 */

// -----------------------------------------------------------------------------
// SESSION & AUTHENTICATION
// -----------------------------------------------------------------------------
// Start session and load authentication/database configuration
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Ensure user is logged in - redirects to login.php if not authenticated
requireLogin();
$currentUser = getCurrentUser();

// Security check: Only allow "boutique" type users to access this dashboard
// Other user types (admin, pattern_provider, etc.) have their own dashboards
if (!$currentUser || $currentUser['user_type'] !== 'boutique') {
    header('Location: login.php');
    exit;
}

// -----------------------------------------------------------------------------
// SUCCESS/ERROR MESSAGE HANDLING
// -----------------------------------------------------------------------------
// Display success message when redirected from pattern-studio.php after saving
// measurements. The ?success=1 query parameter triggers this message.
$message = '';
$messageType = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = 'Measurements saved successfully!';
    $messageType = 'success';
}

// -----------------------------------------------------------------------------
// CUSTOMER DATA FETCHING
// -----------------------------------------------------------------------------
// Fetch all customers belonging to this boutique and optionally load
// the selected customer's measurements if ?customer_id= is in the URL
try {
    global $pdo;
    $userId = $currentUser['id'];

    // Fetch all customers for dropdown - sorted alphabetically by name
    // These customers were created by this boutique user
    $stmt = $pdo->prepare("
        SELECT id, customer_name, customer_reference, created_at
        FROM customers
        WHERE boutique_user_id = ?
        ORDER BY customer_name ASC
    ");
    $stmt->execute([$userId]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if a specific customer is selected via URL parameter
    // When user clicks a customer from dropdown, page reloads with ?customer_id=X
    $selectedCustomerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;
    $customerMeasurements = [];
    $selectedCustomer = null;

    if ($selectedCustomerId) {
        // Fetch full customer details - verify ownership with boutique_user_id
        $customerStmt = $pdo->prepare("
            SELECT * FROM customers
            WHERE id = ? AND boutique_user_id = ?
        ");
        $customerStmt->execute([$selectedCustomerId, $userId]);
        $selectedCustomer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        // Fetch all measurements for this customer
        // A customer can have multiple measurement sets (different pattern types)
        if ($selectedCustomer) {
            $measurementsStmt = $pdo->prepare("
                SELECT * FROM measurements
                WHERE customer_id = ? AND user_id = ?
                ORDER BY created_at DESC
            ");
            $measurementsStmt->execute([$selectedCustomerId, $userId]);
            $customerMeasurements = $measurementsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (Exception $e) {
    // Log error but don't break the page - show empty customer list instead
    error_log("Dashboard boutique customer error: " . $e->getMessage());
    $customers = [];
    $customerMeasurements = [];
}

// -----------------------------------------------------------------------------
// PATTERN CATALOG DATA INITIALIZATION
// -----------------------------------------------------------------------------
// Initialize all pattern catalog arrays with empty defaults
// Each data source is fetched in a separate try-catch so one missing table
// doesn't prevent the entire page from loading
$predesignedPatterns = [];
$patternPortfolio = [];
$frontBlouseDesigns = [];
$backBlouseDesigns = [];

// -----------------------------------------------------------------------------
// FETCH PRE-DESIGNED PATTERNS
// -----------------------------------------------------------------------------
// Pre-designed patterns are complete pattern sets created by pattern providers
// Only "approved" patterns are shown to boutique users
// Featured patterns appear first, then sorted by manual order and date
try {
    $predesignedStmt = $pdo->query("
        SELECT * FROM predesigned_patterns
        WHERE status = 'approved'
        ORDER BY is_featured DESC, sort_order ASC, created_at DESC
        LIMIT 20
    ");
    $predesignedPatterns = $predesignedStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table may not exist yet
}

// -----------------------------------------------------------------------------
// FETCH PATTERN PORTFOLIO (Build Your Pattern)
// -----------------------------------------------------------------------------
// These are the main pattern items shown in the "Build Your Pattern" tab
// They represent customizable pattern templates that use customer measurements
// to generate personalized patterns (savi patterns, etc.)
try {
    $portfolioStmt = $pdo->query("
        SELECT * FROM pattern_making_portfolio
        WHERE status = 'active'
        ORDER BY display_order ASC, created_at DESC
    ");
    $patternPortfolio = $portfolioStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table may not exist yet
}

// -----------------------------------------------------------------------------
// FETCH FRONT BLOUSE DESIGNS
// -----------------------------------------------------------------------------
// Specific front blouse design variations (neck styles, etc.)
// These can be combined with back designs for custom blouses
try {
    $frontDesignsStmt = $pdo->query("
        SELECT * FROM blouse_designs
        WHERE design_type = 'front' AND status = 'approved' AND is_active = 1
        ORDER BY is_featured DESC, sort_order ASC
        LIMIT 20
    ");
    $frontBlouseDesigns = $frontDesignsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table may not exist yet
}

// -----------------------------------------------------------------------------
// FETCH BACK BLOUSE DESIGNS
// -----------------------------------------------------------------------------
// Specific back blouse design variations
// These can be combined with front designs for custom blouses
try {
    $backDesignsStmt = $pdo->query("
        SELECT * FROM blouse_designs
        WHERE design_type = 'back' AND status = 'approved' AND is_active = 1
        ORDER BY is_featured DESC, sort_order ASC
        LIMIT 20
    ");
    $backBlouseDesigns = $backDesignsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table may not exist yet
}

// =============================================================================
// END OF PHP DATA LOADING - HTML RENDERING BEGINS BELOW
// =============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique Dashboard - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <!--
    ==========================================================================
    CUSTOM CSS STYLES
    ==========================================================================
    These styles are specific to the dashboard and supplement the main styles.css

    STYLE ORGANIZATION:
    1. Customer Dropdown - Autocomplete search dropdown styling
    2. Customer Details Card - Selected customer info display
    3. Measurements Table - Body measurements display
    4. Navigation Dropdown - Account info dropdown in navbar
    5. Tab Navigation - Pattern catalog tab system
    6. Paper Size Selector - Print size dropdown styling
    7. Pattern Catalog Grid - Pattern card grid layout
    8. Empty States - "No items" placeholder styling
    9. Error Animation - Customer selection error feedback
    10. Toast Notifications - Popup message styling
    11. Customer Header - Selected customer name display
    12. Pattern Type Badges - Clickable measurement type switchers
    13. Measurements Panels - Show/hide measurement sections
    ==========================================================================
    -->
    <style>
        /* =====================================================================
           1. CUSTOMER DROPDOWN STYLES
           =====================================================================
           Autocomplete dropdown that appears when searching for customers.
           Shows matching customers with name and reference number.
        */
        .customer-dropdown {
            position: absolute;
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
            z-index: 1000;
            margin-top: 4px;
        }

        .customer-dropdown-item {
            padding: 6px 16px;
            cursor: pointer;
            border-bottom: 1px solid #F7FAFC;
            transition: background-color 0.2s;
            text-align: left;
            font-size: 0.875rem;
            line-height: 1.2;
        }

        .customer-dropdown-item:last-child {
            border-bottom: none;
        }

        .customer-dropdown-item:hover {
            background-color: #F7FAFC;
        }

        .customer-dropdown-item-name {
            font-weight: 500;
            color: #2D3748;
            display: inline;
        }

        .customer-dropdown-item-ref {
            font-size: 0.875rem;
            color: #718096;
            display: inline;
            margin-left: 8px;
        }

        /* =====================================================================
           2. CUSTOMER DETAILS CARD
           =====================================================================
           Container for displaying selected customer's information.
           Has a light gray background to distinguish from main content.
        */
        .customer-details-card {
            background: #F7FAFC;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .measurements-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .measurements-table th {
            background: #4FD1C5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }

        .measurements-table td {
            padding: 12px;
            border-bottom: 1px solid #E2E8F0;
        }

        .measurements-table tbody tr:last-child td {
            border-bottom: none;
        }

        .measurements-table tbody tr:hover {
            background-color: #F7FAFC;
        }

        .form-group {
            position: relative;
        }

        .customer-card-no-border {
            border: none !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* =====================================================================
           4. NAVIGATION DROPDOWN STYLES
           =====================================================================
           Account dropdown in the navigation bar showing user info.
           Displays business name, owner, location, mobile, email, etc.
        */
        .nav-dropdown-divider {
            border-top: 1px solid #E2E8F0;
            margin: 8px 0;
        }

        .nav-dropdown-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            color: #4FD1C5;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .nav-dropdown-link:hover {
            background-color: #F7FAFC;
            color: #319795;
        }

        /* =====================================================================
           5. TAB NAVIGATION STYLES
           =====================================================================
           Pattern catalog tab system for switching between:
           - Build Your Pattern (customizable templates)
           - Pre-designed Patterns (ready-made patterns)
           - Front Blouse Designs
           - Back Blouse Designs

           Tab switching is handled by JavaScript - see switchMeasurementType()
        */
        .pattern-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 0.5rem;
        }

        .pattern-tab {
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            color: #718096;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .pattern-tab:hover {
            color: #4FD1C5;
            background: #F7FAFC;
        }

        .pattern-tab.active {
            color: #1A202C;
            background: #E6FFFA;
            border-bottom: 2px solid #4FD1C5;
            margin-bottom: -2px;
            font-weight: 600;
        }

        .pattern-tab-content {
            display: none;
        }

        .pattern-tab-content.active {
            display: block;
        }

        /* =====================================================================
           6. PATTERN CATALOG GRID
           =====================================================================
           Responsive grid layout for displaying pattern cards.
           Each card shows thumbnail, title, description, and price.
           Clicking a card opens the pattern modal for preview/purchase.
        */
        .pattern-catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .pattern-catalog-item {
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
            cursor: pointer;
        }

        .pattern-catalog-item:hover {
            border-color: #4FD1C5;
            box-shadow: 0 4px 12px rgba(79, 209, 197, 0.15);
            transform: translateY(-2px);
        }

        .pattern-catalog-thumbnail {
            width: 100%;
            height: 140px;
            background: #F7FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .pattern-catalog-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pattern-catalog-thumbnail .placeholder-icon {
            color: #CBD5E0;
        }

        .pattern-catalog-info {
            padding: 0.75rem;
        }

        .pattern-catalog-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0 0 0.25rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pattern-catalog-meta {
            font-size: 0.75rem;
            color: #718096;
        }

        .pattern-catalog-price {
            font-size: 0.875rem;
            font-weight: 600;
            color: #38A169;
            margin-top: 0.25rem;
        }

        /* =====================================================================
           8. EMPTY STATE STYLING
           =====================================================================
           Placeholder shown when a tab has no patterns to display.
           Centers an icon and message to inform users.
        */
        .empty-catalog-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #718096;
        }

        .empty-catalog-state i {
            width: 48px;
            height: 48px;
            color: #CBD5E0;
            margin-bottom: 1rem;
        }

        .empty-catalog-state h3 {
            color: #4A5568;
            margin-bottom: 0.5rem;
        }

        /* =====================================================================
           9. CUSTOMER REQUIRED ERROR ANIMATION
           =====================================================================
           Visual feedback when user tries to generate pattern without
           selecting a customer first. Shows shake animation and red border.
           Triggered by showCustomerRequiredError() function.
        */
        .customer-required-error {
            animation: shake 0.5s ease-in-out;
            box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.4) !important;
            border-color: #F56565 !important;
        }

        .customer-required-error input {
            color: #C53030 !important;
        }

        .customer-required-error input::placeholder {
            color: #E53E3E !important;
            font-weight: 500;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* =====================================================================
           10. TOAST NOTIFICATION
           =====================================================================
           Popup notifications that appear at the top center of the screen.
           Used for warnings and info messages (e.g., "Select customer first").
           Created dynamically by showToast() function.
        */
        .toast-notification {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background: #2D3748;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .toast-notification.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast-warning {
            background: linear-gradient(135deg, #ED8936, #DD6B20);
        }

        .toast-notification svg {
            flex-shrink: 0;
        }

        /* =====================================================================
           11. CUSTOMER NAME HEADER
           =====================================================================
           Displays selected customer's name and reference number.
           Includes an edit button to modify measurements.
           Located at the top of the left column when customer is selected.
        */
        .customer-name-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .customer-name-text {
            color: #E53E3E;
            font-weight: 600;
            font-size: 0.875rem;
            margin: 0;
            text-align: left;
        }

        .customer-reference {
            font-weight: 500;
            font-size: 0.8rem;
            color: #718096;
        }

        .measurement-edit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #F7FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            color: #1A202C;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .measurement-edit-btn:hover {
            background: #EDF2F7;
            border-color: #CBD5E0;
            transform: scale(1.05);
        }

        .measurement-edit-btn svg {
            width: 18px;
            height: 18px;
            stroke: #1A202C;
        }

        /* =====================================================================
           12. PATTERN TYPE BADGES
           =====================================================================
           Clickable pill-shaped badges for switching between measurement types.
           Example: "Blouses", "Kurtis", "Pants"
           When clicked, the corresponding measurements panel is shown.
           Handled by switchMeasurementType() JavaScript function.
        */
        .pattern-type-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
            justify-content: flex-start;
        }

        .pattern-type-badge {
            display: inline-block;
            background: #EDF2F7;
            color: #4A5568;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pattern-type-badge:hover {
            background: #E2E8F0;
            color: #2D3748;
        }

        .pattern-type-badge.active {
            background: #E6FFFA;
            color: #319795;
            border-color: #81E6D9;
        }

        /* =====================================================================
           13. MEASUREMENTS PANELS
           =====================================================================
           Container divs for each pattern type's measurements.
           Only one panel is visible at a time (controlled by .active class).
           Panels are shown/hidden by switchMeasurementType() function.
        */
        .measurements-panel {
            display: none;
        }

        .measurements-panel.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- =========================================================================
         MIMIC BANNER (Optional)
         =========================================================================
         Development/demo banner - can be removed in production
    -->
    <?php include __DIR__ . '/../includes/mimic-banner.php'; ?>

    <!-- =========================================================================
         BACKGROUND EFFECTS
         =========================================================================
         Decorative gradient glow circles for visual appeal
    -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <!-- =========================================================================
         NAVIGATION BAR
         =========================================================================
         Main site navigation with:
         - Logo (clickable, returns to home)
         - Pattern Studio link
         - Wholesale Marketplace link
         - Contact Us link
         - Your Account dropdown (shows user info + edit profile link)
         - Logout button
    -->
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
                    <a href="dashboard-boutique.php" class="nav-link active-nav-link nav-dropdown-toggle">
                        YOUR ACCOUNT
                        <i data-lucide="chevron-down" class="nav-dropdown-icon"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Business Name:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['business_name']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Owner Name:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Location:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['business_location']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Mobile:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['mobile_number']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Email:</span>
                            <span class="nav-dropdown-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Account Type:</span>
                            <span class="nav-dropdown-value">Boutique</span>
                        </div>
                        <div class="nav-dropdown-item">
                            <span class="nav-dropdown-label">Status:</span>
                            <span class="nav-dropdown-value"><?php echo ucfirst($currentUser['status']); ?></span>
                        </div>
                        <div class="nav-dropdown-divider"></div>
                        <a href="edit-profile.php" class="nav-dropdown-link">
                            <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                            Edit Profile
                        </a>
                    </div>
                </div>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- =========================================================================
         MAIN DASHBOARD SECTION
         =========================================================================
         Two-column layout:
         - LEFT COLUMN (33%): Customer selection + measurements display
         - RIGHT COLUMN (67%): Pattern catalog with tabs
    -->
    <section class="hero auth-section auth-section-padded" style="align-items: flex-start; padding-top: calc(4.5rem + 40px);">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <!-- Dashboard Welcome Message -->
                <p class="hero-description auth-description" style="margin-bottom: 0.5rem; text-align: left; margin-left: 0; margin-right: 0;">
                    Welcome, <?php echo htmlspecialchars($currentUser['business_name']); ?>!
                </p>

                <!-- Dashboard Title -->
                <h1 class="hero-title auth-title" style="text-align: left; margin-bottom: 15px;">
                    Boutique <span class="hero-title-accent">Dashboard</span>
                </h1>

                <!-- Success/Error Messages (shown after measurement save, etc.) -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- ===============================================================
                     TWO-COLUMN DASHBOARD GRID
                     ===============================================================
                     - Left column: 33% width - Customer management
                     - Right column: 67% width - Pattern catalog
                     - Gap: 2rem between columns
                     - align-items: start - columns align to top
                -->
                <div style="display: grid; grid-template-columns: calc(33.33% - 100px) calc(66.67% + 100px); gap: 2rem; margin-bottom: 2rem; align-items: start;">

                    <!-- ==========================================================
                         LEFT COLUMN: CUSTOMER MANAGEMENT
                         ==========================================================
                         Contains:
                         1. Customer search input with autocomplete dropdown
                         2. "New Customer" button (links to pattern-studio.php)
                         3. Selected customer details (name, reference)
                         4. Pattern type badges (Blouses, Kurtis, etc.)
                         5. Measurements display panels
                    -->
                    <div class="dashboard-card customer-card-no-border" style="padding-top: 0;">
                    <!-- Searchable Customer Dropdown -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="text-align: left; margin-bottom: 0;">Select Customer:</label>
                            <a href="pattern-studio.php" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; letter-spacing: -0.5px;">New Customer</a>
                        </div>
                        <input
                            type="text"
                            id="customerSearch"
                            class="form-input"
                            placeholder="Search by Name / Mobile No."
                            autocomplete="off"
                            style="text-align: left;"
                        >
                        <div id="customerDropdown" class="customer-dropdown" style="display: none;"></div>
                    </div>

                    <!-- ==========================================================
                         SELECTED CUSTOMER DETAILS SECTION
                         ==========================================================
                         This section only displays when a customer is selected
                         via the URL parameter ?customer_id=X

                         Shows:
                         - Customer name and reference number
                         - Edit button (links to pattern-studio.php?edit=ID)
                         - Pattern type badges (for switching measurement views)
                         - Measurements display based on selected pattern type
                    -->
                    <?php if ($selectedCustomer): ?>
                        <!-- Customer Name Header with Edit Button -->
                        <div class="customer-name-header">
                            <p class="customer-name-text">
                                <?php echo htmlspecialchars($selectedCustomer['customer_name']); ?>
                                <?php if ($selectedCustomer['customer_reference']): ?>
                                    <span class="customer-reference">- <?php echo htmlspecialchars($selectedCustomer['customer_reference']); ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if (count($customerMeasurements) > 0): ?>
                            <a href="pattern-studio.php?edit=<?php echo $customerMeasurements[0]['id']; ?>" class="measurement-edit-btn" title="Edit Measurements">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- =======================================================
                             MEASUREMENTS DISPLAY SECTION
                             =======================================================
                             Shows customer measurements grouped by pattern type.

                             FLOW:
                             1. Group measurements by pattern_type (blouse, kurti, pants)
                             2. Display clickable badges for each pattern type
                             3. Show measurement panels (only active one is visible)
                             4. Clicking a badge switches the visible panel

                             MEASUREMENT MAPPING:
                             Each pattern type shows different measurements:
                             - Blouse: blength, fshoulder, shoulder, bnDepth, fndepth,
                                       apex, flength, chest, bust, waist, slength, etc.
                             - Kurti: Similar to blouse + hip
                             - Pants: waist, hip, inseam, outseam, thigh, knee, ankle
                        -->
                        <?php if (count($customerMeasurements) > 0): ?>
                            <?php
                            // Human-readable labels for pattern types
                            // These map database values to display names
                            $patternTypeLabels = [
                                'blouse' => 'Blouses',
                                'kurti' => 'Kurtis',
                                'blouse_back' => 'Blouse Back Designs',
                                'blouse_front' => 'Blouse Front Designs',
                                'sleeve' => 'Sleeve Designs',
                                'pants' => 'Pants'
                            ];

                            // Group measurements by pattern type
                            // A customer can have multiple measurement sets (one per pattern type)
                            // We only show the most recent measurement for each type
                            $measurementsByType = [];
                            foreach ($customerMeasurements as $measurement) {
                                $type = $measurement['pattern_type'] ?? 'blouse';
                                // Only keep the first (most recent) measurement for each type
                                if (!isset($measurementsByType[$type])) {
                                    $measurementsByType[$type] = $measurement;
                                }
                            }

                            // Determine which pattern type to show by default
                            // Uses the most recently added measurement's type
                            $latestMeasurement = $customerMeasurements[0];
                            $defaultType = $latestMeasurement['pattern_type'] ?? 'blouse';
                            ?>

                            <!-- Pattern Type Badges (Clickable)
                                 ==========================================
                                 Each badge represents a pattern type with measurements.
                                 Clicking a badge:
                                 1. Adds .active class to the clicked badge
                                 2. Shows the corresponding measurements-panel
                                 3. Updates the edit button link to the correct measurement ID
                            -->
                            <div class="pattern-type-badges">
                                <?php foreach ($measurementsByType as $type => $measurement): ?>
                                    <button type="button"
                                            class="pattern-type-badge <?php echo $type === $defaultType ? 'active' : ''; ?>"
                                            data-type="<?php echo htmlspecialchars($type); ?>"
                                            data-measurement-id="<?php echo $measurement['id']; ?>"
                                            onclick="switchMeasurementType('<?php echo htmlspecialchars($type); ?>', <?php echo $measurement['id']; ?>)">
                                        <?php echo $patternTypeLabels[$type] ?? ucfirst($type); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Measurements Display Container
                                 ==========================================
                                 Contains one panel per pattern type.
                                 Only the panel with .active class is visible.
                                 Each panel shows relevant measurements for that type.
                            -->
                            <div id="measurementsContainer">
                                <?php foreach ($measurementsByType as $type => $measurement): ?>
                                    <div id="measurements-<?php echo htmlspecialchars($type); ?>"
                                         class="measurements-panel <?php echo $type === $defaultType ? 'active' : ''; ?>">
                                        <?php
                                        // Define measurements based on pattern type
                                        if ($type === 'blouse' || $type === 'blouse_front' || $type === 'blouse_back') {
                                            $displayMeasurements = [
                                                'Blouse Back Length (1)' => $measurement['blength'] ?? null,
                                                'Full Shoulder (2)' => $measurement['fshoulder'] ?? null,
                                                'Shoulder Strap (3)' => $measurement['shoulder'] ?? null,
                                                'Back Neck Depth (4)' => $measurement['bnDepth'] ?? null,
                                                'Front Neck Depth (5)' => $measurement['fndepth'] ?? null,
                                                'Shoulder to Apex (6)' => $measurement['apex'] ?? null,
                                                'Front Length (7)' => $measurement['flength'] ?? null,
                                                'Upper Chest (8)' => $measurement['chest'] ?? null,
                                                'Bust Round (9)' => $measurement['bust'] ?? null,
                                                'Waist Round (10)' => $measurement['waist'] ?? null,
                                                'Sleeve Length (11)' => $measurement['slength'] ?? null,
                                                'Arm Round (12)' => $measurement['saround'] ?? null,
                                                'Sleeve End Round (13)' => $measurement['sopen'] ?? null,
                                                'Armhole (14)' => $measurement['armhole'] ?? null,
                                            ];
                                        } elseif ($type === 'kurti') {
                                            $displayMeasurements = [
                                                'Kurti Length' => $measurement['blength'] ?? null,
                                                'Full Shoulder' => $measurement['fshoulder'] ?? null,
                                                'Shoulder Strap' => $measurement['shoulder'] ?? null,
                                                'Front Neck Depth' => $measurement['fndepth'] ?? null,
                                                'Upper Chest' => $measurement['chest'] ?? null,
                                                'Bust Round' => $measurement['bust'] ?? null,
                                                'Waist Round' => $measurement['waist'] ?? null,
                                                'Hip Round' => $measurement['hip'] ?? null,
                                                'Sleeve Length' => $measurement['slength'] ?? null,
                                                'Arm Round' => $measurement['saround'] ?? null,
                                                'Sleeve End Round' => $measurement['sopen'] ?? null,
                                            ];
                                        } elseif ($type === 'pants') {
                                            $displayMeasurements = [
                                                'Waist Round' => $measurement['waist'] ?? null,
                                                'Hip Round' => $measurement['hip'] ?? null,
                                                'Inseam' => $measurement['inseam'] ?? null,
                                                'Outseam' => $measurement['outseam'] ?? null,
                                                'Thigh Round' => $measurement['thigh'] ?? null,
                                                'Knee Round' => $measurement['knee'] ?? null,
                                                'Ankle Round' => $measurement['ankle'] ?? null,
                                            ];
                                        } else {
                                            // Default to blouse measurements
                                            $displayMeasurements = [
                                                'Blouse Back Length (1)' => $measurement['blength'] ?? null,
                                                'Full Shoulder (2)' => $measurement['fshoulder'] ?? null,
                                                'Shoulder Strap (3)' => $measurement['shoulder'] ?? null,
                                                'Back Neck Depth (4)' => $measurement['bnDepth'] ?? null,
                                                'Front Neck Depth (5)' => $measurement['fndepth'] ?? null,
                                                'Shoulder to Apex (6)' => $measurement['apex'] ?? null,
                                                'Front Length (7)' => $measurement['flength'] ?? null,
                                                'Upper Chest (8)' => $measurement['chest'] ?? null,
                                                'Bust Round (9)' => $measurement['bust'] ?? null,
                                                'Waist Round (10)' => $measurement['waist'] ?? null,
                                                'Sleeve Length (11)' => $measurement['slength'] ?? null,
                                                'Arm Round (12)' => $measurement['saround'] ?? null,
                                                'Sleeve End Round (13)' => $measurement['sopen'] ?? null,
                                                'Armhole (14)' => $measurement['armhole'] ?? null,
                                            ];
                                        }
                                        ?>
                                        <?php foreach ($displayMeasurements as $label => $value): ?>
                                            <div style="display: flex; padding: 0.25rem 0; border-bottom: 1px solid #E2E8F0; font-size: 0.75rem; line-height: 1.2;">
                                                <span style="font-weight: 500; color: #2D3748; min-width: 160px; text-align: right;"><?php echo $label; ?>:</span>
                                                <span style="color: #000000; margin-left: 0.75rem; font-weight: 500;"><?php echo $value ? rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') : 'N/A'; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <p style="color: #718096; font-style: italic;">No measurements recorded for this customer yet.</p>
                        <?php endif; ?>
                    <?php elseif (count($customers) > 0): ?>
                        <!-- Message is now in the label above -->
                    <?php else: ?>
                        <p style="color: #718096; text-align: center; padding: 2rem;">
                            No customers yet. Visit <a href="pattern-studio.php" style="color: #4FD1C5; text-decoration: none;">Pattern Studio</a>
                            to add measurements for your first customer.
                        </p>
                    <?php endif; ?>
                    </div>

                    <!-- ==========================================================
                         RIGHT COLUMN: PATTERN CATALOG
                         ==========================================================
                         Contains:
                         1. Print Size selector (A0, A2, A3, A4)
                         2. Tab navigation for pattern types
                         3. Tab content panels:
                            - Build Your Pattern (portfolio items)
                            - Pre-designed Patterns
                            - Front Blouse Designs
                            - Back Blouse Designs

                         USER FLOW:
                         1. User selects print size from dropdown
                         2. User clicks a tab to switch pattern category
                         3. User clicks a pattern card
                         4. Pattern modal opens with preview
                         5. User confirms and is redirected to download/payment
                    -->
                    <div class="dashboard-card" style="padding: 1.5rem;">
                        <!-- Tab Navigation
                             ==============
                             Four tabs for different pattern categories:
                             1. Build Your Pattern - Customizable pattern templates
                             2. Pre-designed Patterns - Ready-made patterns by designers
                             3. Front Blouse Designs - Front-specific design variations
                             4. Back Blouse Designs - Back-specific design variations

                             Tab switching is handled by JavaScript click listeners.
                             Each tab has a data-tab attribute matching the content div ID.
                        -->
                        <div class="pattern-tabs">
                            <button class="pattern-tab active" data-tab="build">
                                <i data-lucide="settings-2" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                Build Your Pattern
                            </button>
                            <button class="pattern-tab" data-tab="predesigned">
                                <i data-lucide="file-text" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                Pre-designed Patterns
                            </button>
                            <button class="pattern-tab" data-tab="front">
                                <i data-lucide="shirt" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                Front Blouse Designs
                            </button>
                            <button class="pattern-tab" data-tab="back">
                                <i data-lucide="flip-horizontal" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                Back Blouse Designs
                            </button>
                        </div>

                        <!-- =====================================================
                             TAB CONTENT PANELS
                             =====================================================
                             Only one tab content is visible at a time (has .active class).
                             Each panel contains a grid of pattern cards.
                             Clicking a card triggers the corresponding action function.
                        -->

                        <!-- Tab Content: Build Your Pattern
                             ==================================
                             Shows items from pattern_making_portfolio table.
                             These are customizable patterns that use customer measurements.
                             Clicking a card calls openPatternModal() which opens the
                             pattern preview modal.
                        -->
                        <div id="tab-build" class="pattern-tab-content active">
                            <?php if (!empty($patternPortfolio)): ?>
                                <div class="pattern-catalog-grid">
                                    <?php foreach ($patternPortfolio as $item): ?>
                                        <div class="pattern-catalog-item" onclick="openPatternModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>)">
                                            <div class="pattern-catalog-thumbnail">
                                                <?php if ($item['image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                                <?php else: ?>
                                                    <i data-lucide="settings-2" class="placeholder-icon" style="width: 48px; height: 48px;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pattern-catalog-info">
                                                <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                                <p class="pattern-catalog-price" style="<?php echo ($item['price'] ?? 0) == 0 ? 'color: #DC2626;' : ''; ?>">
                                                    <?php echo ($item['price'] ?? 0) > 0 ? '₹' . number_format($item['price'], 0) : 'Free'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="settings-2"></i>
                                    <h3>No Custom Patterns Yet</h3>
                                    <p>Custom pattern designs will appear here once added.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Pre-designed Patterns
                             ===================================
                             Shows items from predesigned_patterns table.
                             These are complete patterns created by pattern providers.
                             Clicking a card calls viewPredesignedPattern() which
                             redirects to view-predesigned-pattern.php
                        -->
                        <div id="tab-predesigned" class="pattern-tab-content">
                            <?php if (!empty($predesignedPatterns)): ?>
                                <div class="pattern-catalog-grid">
                                    <?php foreach ($predesignedPatterns as $pattern): ?>
                                        <div class="pattern-catalog-item" onclick="viewPredesignedPattern(<?php echo $pattern['id']; ?>)">
                                            <div class="pattern-catalog-thumbnail">
                                                <?php if ($pattern['thumbnail']): ?>
                                                    <img src="../uploads/patterns/thumbnails/<?php echo htmlspecialchars($pattern['thumbnail']); ?>" alt="<?php echo htmlspecialchars($pattern['title']); ?>">
                                                <?php else: ?>
                                                    <i data-lucide="file-text" class="placeholder-icon" style="width: 48px; height: 48px;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pattern-catalog-info">
                                                <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($pattern['title']); ?></h4>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($pattern['designer_name'] ?? 'CuttingMaster'); ?></p>
                                                <p class="pattern-catalog-price">
                                                    <?php echo $pattern['price'] > 0 ? '₹' . number_format($pattern['price'], 0) : 'Free'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="file-text"></i>
                                    <h3>No Pre-designed Patterns Yet</h3>
                                    <p>Pre-designed patterns will appear here once added by pattern providers.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Front Blouse Designs
                             ==================================
                             Shows front-type items from blouse_designs table.
                             These are neck/front variations for blouses.
                             Clicking a card calls viewBlouseDesign() with type='front'
                             which redirects to view-blouse-design.php
                        -->
                        <div id="tab-front" class="pattern-tab-content">
                            <?php if (!empty($frontBlouseDesigns)): ?>
                                <div class="pattern-catalog-grid">
                                    <?php foreach ($frontBlouseDesigns as $design): ?>
                                        <div class="pattern-catalog-item" onclick="viewBlouseDesign(<?php echo $design['id']; ?>, 'front')">
                                            <div class="pattern-catalog-thumbnail">
                                                <?php if ($design['thumbnail']): ?>
                                                    <img src="../uploads/patterns/designs/<?php echo htmlspecialchars($design['thumbnail']); ?>" alt="<?php echo htmlspecialchars($design['name']); ?>">
                                                <?php else: ?>
                                                    <i data-lucide="shirt" class="placeholder-icon" style="width: 48px; height: 48px;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pattern-catalog-info">
                                                <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($design['name']); ?></h4>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($design['style_category'] ?? 'Classic'); ?></p>
                                                <p class="pattern-catalog-price">
                                                    <?php echo $design['price'] > 0 ? '₹' . number_format($design['price'], 0) : 'Free'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="shirt"></i>
                                    <h3>No Front Blouse Designs Yet</h3>
                                    <p>Front blouse design options will appear here once added.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Back Blouse Designs
                             ==================================
                             Shows back-type items from blouse_designs table.
                             These are back variations for blouses.
                             Clicking a card calls viewBlouseDesign() with type='back'
                             which redirects to view-blouse-design.php
                        -->
                        <div id="tab-back" class="pattern-tab-content">
                            <?php if (!empty($backBlouseDesigns)): ?>
                                <div class="pattern-catalog-grid">
                                    <?php foreach ($backBlouseDesigns as $design): ?>
                                        <div class="pattern-catalog-item" onclick="viewBlouseDesign(<?php echo $design['id']; ?>, 'back')">
                                            <div class="pattern-catalog-thumbnail">
                                                <?php if ($design['thumbnail']): ?>
                                                    <img src="../uploads/patterns/designs/<?php echo htmlspecialchars($design['thumbnail']); ?>" alt="<?php echo htmlspecialchars($design['name']); ?>">
                                                <?php else: ?>
                                                    <i data-lucide="flip-horizontal" class="placeholder-icon" style="width: 48px; height: 48px;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pattern-catalog-info">
                                                <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($design['name']); ?></h4>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($design['style_category'] ?? 'Classic'); ?></p>
                                                <p class="pattern-catalog-price">
                                                    <?php echo $design['price'] > 0 ? '₹' . number_format($design['price'], 0) : 'Free'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="flip-horizontal"></i>
                                    <h3>No Back Blouse Designs Yet</h3>
                                    <p>Back blouse design options will appear here once added.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
/**
 * =============================================================================
 * JAVASCRIPT DATA & FUNCTIONS
 * =============================================================================
 *
 * This section prepares PHP data for JavaScript and defines all the
 * interactive functionality for the dashboard.
 *
 * DATA PASSED TO JAVASCRIPT:
 * - customers: Array of customer objects for dropdown
 * - dashboardSelectedCustomer: Currently selected customer (or null)
 *
 * MAIN FUNCTIONS:
 * 1. switchMeasurementType() - Switches visible measurement panel
 * 2. selectCustomer() - Handles customer selection from dropdown
 * 3. showCustomerDropdown() - Shows/filters customer autocomplete
 * 4. checkPaperSize() - Validates paper size is selected
 * 5. showCustomerRequiredError() - Shows error when no customer selected
 * 6. showToast() - Displays notification messages
 * 7. openPatternModal() - Opens pattern preview modal
 * 8. handleGeneratePattern() - Processes pattern generation request
 * 9. loadPatternPreview() - Fetches pattern SVG preview via AJAX
 * 10. selectPatternType() - Switches between front/back/sleeve/patti tabs
 *
 * =============================================================================
 */

// Prepare selected customer data for JavaScript
// This allows the modal to know if a customer is already selected
$selectedCustomerJson = $selectedCustomer ? json_encode([
    'id' => $selectedCustomerId,
    'name' => $selectedCustomer['customer_name'],
    'reference' => $selectedCustomer['customer_reference'] ?? ''
]) : 'null';

// Build JavaScript code as a PHP string
// This will be echoed into a <script> tag below
$additionalScripts = "
    // ==========================================================================
    // CUSTOMER DATA FROM PHP
    // ==========================================================================
    // customers: All customers for this boutique (for dropdown)
    // dashboardSelectedCustomer: Currently selected customer object or null
    const customers = " . json_encode($customers) . ";
    const dashboardSelectedCustomer = " . $selectedCustomerJson . ";

    // ==========================================================================
    // MEASUREMENT TYPE SWITCHER
    // ==========================================================================
    // Switches the visible measurement panel when clicking pattern type badges.
    // @param type - Pattern type key (e.g., blouse, kurti, pants)
    // @param measurementId - ID of the measurement record for edit link
    // FLOW:
    // 1. Remove .active from all badges
    // 2. Add .active to clicked badge
    // 3. Hide all measurement panels
    // 4. Show the panel matching the clicked type
    // 5. Update the edit button href to point to correct measurement
    function switchMeasurementType(type, measurementId) {
        // Update badge active states
        document.querySelectorAll('.pattern-type-badge').forEach(badge => {
            badge.classList.remove('active');
        });
        document.querySelector('.pattern-type-badge[data-type=\"' + type + '\"]').classList.add('active');

        // Update measurement panels
        document.querySelectorAll('.measurements-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        const targetPanel = document.getElementById('measurements-' + type);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }

        // Update the edit button link if it exists
        const editBtn = document.querySelector('.measurement-edit-btn');
        if (editBtn && measurementId) {
            editBtn.href = 'pattern-studio.php?edit=' + measurementId;
        }
    }

    // ==========================================================================
    // CUSTOMER SEARCH DROPDOWN
    // ==========================================================================
    // Autocomplete dropdown for customer search.
    // BEHAVIOR:
    // - Shows dropdown on input focus
    // - Filters customers as user types
    // - Searches by name and reference number
    // - Clicking a customer reloads page with ?customer_id=X
    const customerSearch = document.getElementById('customerSearch');
    const customerDropdown = document.getElementById('customerDropdown');

    if (customerSearch && customerDropdown) {
        // Show dropdown on focus
        customerSearch.addEventListener('focus', function() {
            showCustomerDropdown(this.value);
        });

        // Filter on input
        customerSearch.addEventListener('input', function() {
            showCustomerDropdown(this.value);
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!customerSearch.contains(e.target) && !customerDropdown.contains(e.target)) {
                customerDropdown.style.display = 'none';
            }
        });

        function showCustomerDropdown(searchTerm) {
            const filtered = customers.filter(c =>
                c.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (c.customer_reference && c.customer_reference.toLowerCase().includes(searchTerm.toLowerCase()))
            );

            if (filtered.length === 0) {
                customerDropdown.innerHTML = '<div class=\"customer-dropdown-item\" style=\"color: #718096;\">No customers found</div>';
            } else {
                customerDropdown.innerHTML = filtered.map(c =>
                    '<div class=\"customer-dropdown-item\" onclick=\"selectCustomer(' + c.id + ')\">' +
                        '<span class=\"customer-dropdown-item-name\">' + escapeHtml(c.customer_name) + '</span>' +
                        (c.customer_reference ? '<span class=\"customer-dropdown-item-ref\">' + escapeHtml(c.customer_reference) + '</span>' : '') +
                    '</div>'
                ).join('');
            }

            customerDropdown.style.display = 'block';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // ==========================================================================
    // CUSTOMER SELECTION HANDLER
    // ==========================================================================
    // Handles customer selection from the dropdown.
    // Reloads the page with the selected customer ID as a URL parameter.
    // This causes the PHP code to fetch and display that customers measurements.
    // @param customerId - The ID of the selected customer
    function selectCustomer(customerId) {
        window.location.href = 'dashboard-boutique.php?customer_id=' + customerId;
    }

    // ==========================================================================
    // PATTERN CATALOG TAB SWITCHING
    // ==========================================================================
    // Tab switching for the pattern catalog.
    // Shows/hides tab content based on clicked tab.
    // TABS:
    // - build: Build Your Pattern (portfolio items)
    // - predesigned: Pre-designed Patterns
    // - front: Front Blouse Designs
    // - back: Back Blouse Designs
    document.querySelectorAll('.pattern-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active from all tabs
            document.querySelectorAll('.pattern-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.pattern-tab-content').forEach(c => c.classList.remove('active'));

            // Add active to clicked tab
            this.classList.add('active');
            const tabId = 'tab-' + this.dataset.tab;
            document.getElementById(tabId).classList.add('active');

            // Re-initialize lucide icons for newly visible content
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });

    // ==========================================================================
    // ERROR FEEDBACK FUNCTIONS
    // ==========================================================================
    // Shows visual error feedback when user tries to generate pattern
    // without selecting a customer first.
    // EFFECTS:
    // 1. Adds shake animation to customer search wrapper
    // 2. Changes placeholder text to error message
    // 3. Shows toast notification with warning
    // 4. Resets after 3 seconds
    function showCustomerRequiredError() {
        // Find the customer search input area
        const customerSearchWrapper = document.querySelector('.customer-search-wrapper');
        const customerSearch = document.getElementById('customerSearch');

        // Add error highlight class
        if (customerSearchWrapper) {
            customerSearchWrapper.classList.add('customer-required-error');

            // Remove error class after 3 seconds
            setTimeout(() => {
                customerSearchWrapper.classList.remove('customer-required-error');
            }, 3000);
        }

        // Focus on customer search input
        if (customerSearch) {
            customerSearch.focus();
            customerSearch.placeholder = 'Please select a customer first!';

            // Reset placeholder after 3 seconds
            setTimeout(() => {
                customerSearch.placeholder = 'Search customer by name or reference...';
            }, 3000);
        }

        // Show toast notification
        showToast('Please select a customer before generating a pattern', 'warning');
    }

    // Creates and displays a toast notification at the top of the screen.
    // Used for warnings, errors, and info messages.
    // @param message - The message to display
    // @param type - Notification type (info or warning)
    // BEHAVIOR:
    // - Removes any existing toast first
    // - Creates new toast element with icon and message
    // - Animates in with CSS transition
    // - Auto-hides after 4 seconds
    function showToast(message, type = 'info') {
        // Remove existing toast if any
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.innerHTML = '<i data-lucide=\"' + (type === 'warning' ? 'alert-triangle' : 'info') + '\" style=\"width: 20px; height: 20px;\"></i><span>' + message + '</span>';

        // Add to page
        document.body.appendChild(toast);

        // Initialize lucide icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto-hide after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ==========================================================================
    // PATTERN NAVIGATION FUNCTIONS
    // ==========================================================================

    // Navigates to view a pre-designed pattern.
    // Called when clicking a card in the Pre-designed Patterns tab.
    // @param patternId - ID of the predesigned_patterns record
    function viewPredesignedPattern(patternId) {
        window.location.href = 'view-predesigned-pattern.php?id=' + patternId;
    }

    // Navigates to view a blouse design (front or back).
    // Called when clicking a card in Front/Back Blouse Designs tabs.
    // @param designId - ID of the blouse_designs record
    // @param designType - front or back
    function viewBlouseDesign(designId, designType) {
        window.location.href = 'view-blouse-design.php?id=' + designId + '&type=' + designType;
    }

    // ==========================================================================
    // PATTERN MODAL FUNCTIONS
    // ==========================================================================

    // Opens the pattern preview modal for Build Your Pattern items.
    // Shows pattern info, customer info, and live SVG preview.
    // @param item - Pattern portfolio item object from PHP
    //   - id: Portfolio item ID
    //   - title: Pattern title
    //   - image: Thumbnail image path
    //   - description: Pattern description
    //   - price: Pattern price (0 = free)
    //   - code_page: Reference to code that generates the pattern
    // FLOW:
    // Opens pattern preview in a new page instead of modal
    // 1. Check if customer is selected (show error if not)
    // 2. Navigate to pattern-preview.php with required parameters
    function openPatternModal(item) {
        // Check if customer is selected BEFORE proceeding
        if (!dashboardSelectedCustomer) {
            // Show error message and highlight customer selection area
            showCustomerRequiredError();
            return;
        }

        const customerId = dashboardSelectedCustomer.id;
        const price = parseFloat(item.price) || 0;

        // Determine pattern type from code_page (e.g., 'savi', 'churidar')
        // Default to 'savi' if not specified
        const patternType = item.code_page || 'savi';

        // Build URL and navigate to pattern preview page
        const previewUrl = 'pattern-preview.php'
            + '?pattern=' + encodeURIComponent(patternType)
            + '&customer_id=' + encodeURIComponent(customerId)
            + '&item_id=' + encodeURIComponent(item.id)
            + '&price=' + encodeURIComponent(price);

        window.location.href = previewUrl;
    }

    // Allows user to change the selected customer within the modal.
    // Hides the current customer display and shows the dropdown.
    // Also resets the pattern preview.
    function changeCustomer() {
        document.getElementById('selectedCustomerDisplay').style.display = 'none';
        document.getElementById('customerDropdownSection').style.display = 'block';
        document.getElementById('selectedCustomerId').value = '';

        // Reset pattern preview
        document.getElementById('selectCustomerMsg').style.display = 'block';
        document.getElementById('expandPatternBtn').style.display = 'none';
        if (typeof resetPatternPreviews === 'function') {
            resetPatternPreviews();
        }
    }

    // Closes the pattern modal.
    // Removes .active class which hides the modal via CSS.
    function closePatternModal() {
        document.getElementById('patternModal').classList.remove('active');
    }

    // Handles the Download Pattern or Buy Pattern button click.
    // Determines whether to redirect to payment or download page.
    // FLOW:
    // 1. Get customer ID from modal (pre-selected or dropdown)
    // 2. Validate customer is selected
    // 3. Check pattern price
    // 4. If price > 0: Redirect to pattern-payment.php
    // 5. If price = 0: Redirect to pattern-download.php
    function handleGeneratePattern() {
        // Get customer ID from either pre-selected or dropdown
        let customerId = document.getElementById('selectedCustomerId').value;
        if (!customerId) {
            customerId = document.getElementById('customerSelect').value;
        }

        if (!customerId) {
            alert('Please select a customer or self measurements');
            return;
        }

        const codePage = document.getElementById('patternModalCodePage').value;
        const price = parseFloat(document.getElementById('patternModalItemPrice').value);
        const itemId = document.getElementById('patternModalItemId').value;

        if (price > 0) {
            // Paid pattern - redirect to payment/checkout page
            window.location.href = 'pattern-payment.php?item_id=' + itemId + '&customer_id=' + customerId;
        } else {
            // Free pattern - go directly to download page
            window.location.href = 'pattern-download.php?item_id=' + itemId + '&customer_id=' + customerId;
        }
    }

    // ==========================================================================
    // EVENT LISTENERS FOR MODAL
    // ==========================================================================

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePatternModal();
        }
    });

    // Close modal when clicking on the backdrop (outside modal content)
    document.getElementById('patternModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePatternModal();
    });
";
?>

<!-- =============================================================================
     PATTERN SELECTION MODAL
     =============================================================================
     Two-column modal that opens when clicking a "Build Your Pattern" item.

     STRUCTURE:
     ┌─────────────────────────────────────────────────────────────────────────┐
     │ Modal Header (title + close button)                                      │
     ├───────────────────────────────────┬─────────────────────────────────────┤
     │ LEFT COLUMN (30%)                 │ RIGHT COLUMN (70%)                  │
     │ - Pattern thumbnail + info        │ - Pattern type tabs (Front/Back/    │
     │ - Price display                   │   Sleeve/Patti)                     │
     │ - Paper size display              │ - SVG pattern preview area          │
     │ - Customer selection/display      │ - Loading indicator                 │
     │ - Payment info (if paid)          │ - Error messages                    │
     │ - Measurements summary            │                                     │
     ├───────────────────────────────────┴─────────────────────────────────────┤
     │ Modal Footer (Cancel + Download/Buy button)                              │
     └─────────────────────────────────────────────────────────────────────────┘

     KEY IDS:
     - patternModal: Main modal container
     - patternModalTitle: Pattern title text
     - patternModalImage: Pattern thumbnail
     - patternModalPrice: Price display
     - selectedCustomerDisplay: Shows selected customer info
     - patternSvgContainer: SVG preview container
     - generatePatternBtn: Main action button
-->
<div id="patternModal" class="modal">
    <div class="modal-content" style="width: 80vw; height: 80vh; max-width: none; display: flex; flex-direction: column;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #E2E8F0; flex-shrink: 0;">
            <h3 style="margin: 0; font-size: 1.25rem;">Generate Pattern</h3>
            <button type="button" onclick="closePatternModal()" style="background: none; border: none; cursor: pointer; color: #718096; padding: 0.25rem;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.5rem; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <!-- Two Column Layout -->
            <div style="display: grid; grid-template-columns: 0.5fr 1.5fr; gap: 1.5rem; flex: 1; min-height: 0;">

                <!-- LEFT COLUMN: Pattern Info & Options -->
                <div class="pattern-modal-left" style="overflow-y: auto;">
                    <!-- Pattern Info -->
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #E2E8F0;">
                        <img id="patternModalImage" src="" alt="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <div>
                            <h4 id="patternModalTitle" style="margin: 0 0 0.25rem 0; font-size: 1rem;"></h4>
                            <p id="patternModalDesc" style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #718096; line-height: 1.4;"></p>
                            <p id="patternModalPrice" class="pattern-modal-price"></p>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" id="patternModalItemId" value="">
                    <input type="hidden" id="patternModalCodePage" value="">
                    <input type="hidden" id="patternModalItemPrice" value="">

                    <!-- Customer Selection -->
                    <div id="customerSelectionSection" style="margin-bottom: 1rem;">
                        <!-- Selected Customer Display -->
                        <div id="selectedCustomerDisplay" style="display: none; padding: 0.625rem; background: #E6FFFA; border-radius: 6px; border: 1px solid #38B2AC;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="font-size: 0.7rem; color: #718096; display: block;">Customer:</span>
                                    <span id="selectedCustomerName" style="font-weight: 600; color: #234E52; font-size: 0.875rem;"></span>
                                </div>
                                <button type="button" onclick="changeCustomer()" style="background: none; border: none; color: #38B2AC; cursor: pointer; font-size: 0.7rem; text-decoration: underline;">Change</button>
                            </div>
                        </div>
                        <input type="hidden" id="selectedCustomerId" value="">

                        <!-- Customer Dropdown -->
                        <div id="customerDropdownSection" style="display: none;">
                            <label style="display: block; margin-bottom: 0.375rem; font-weight: 500; font-size: 0.8rem;">Select Customer *</label>
                            <select id="customerSelect" style="width: 100%; padding: 0.625rem; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 0.8rem;">
                                <option value="">-- Select Customer --</option>
                                <option value="self">Self (My Measurements)</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['customer_name']); ?> (<?php echo htmlspecialchars($customer['customer_reference'] ?? ''); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div id="paymentSection" style="display: none; margin-bottom: 1rem; padding: 0.625rem; background: #FEF3C7; border-radius: 6px; border: 1px solid #F59E0B;">
                        <p style="margin: 0; font-size: 0.75rem; color: #92400E;">
                            <i data-lucide="info" style="width: 12px; height: 12px; display: inline; vertical-align: middle;"></i>
                            Paid pattern - payment required.
                        </p>
                    </div>

                    <!-- Measurements Summary (shown when loaded) -->
                    <div id="measurementsSummary" style="display: none; padding: 0.625rem; background: #F0FDF4; border-radius: 6px; border: 1px solid #BBF7D0;">
                        <h5 id="measurementsTitle" style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #166534; border-bottom: 1px solid #BBF7D0; padding-bottom: 0.375rem;">Measurements</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.2rem; font-size: 0.65rem; color: #4A5568;">
                            <span>Bust: <strong id="measBust">-</strong>"</span>
                            <span>Chest: <strong id="measChest">-</strong>"</span>
                            <span>Waist: <strong id="measWaist">-</strong>"</span>
                            <span>Shoulder: <strong id="measShoulder">-</strong>"</span>
                            <span>F.Shoulder: <strong id="measFshoulder">-</strong>"</span>
                            <span>Armhole: <strong id="measArmhole">-</strong>"</span>
                            <span>F.Length: <strong id="measFlength">-</strong>"</span>
                            <span>B.Length: <strong id="measBlength">-</strong>"</span>
                            <span>F.N.Depth: <strong id="measFndepth">-</strong>"</span>
                            <span>B.N.Depth: <strong id="measBndepth">-</strong>"</span>
                            <span>Apex: <strong id="measApex">-</strong>"</span>
                            <span>S.Length: <strong id="measSlength">-</strong>"</span>
                            <span>S.Around: <strong id="measSaround">-</strong>"</span>
                            <span>S.Opening: <strong id="measSopen">-</strong>"</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Pattern Preview - 2x2 Grid Layout -->
                <div class="pattern-modal-right" style="display: flex; flex-direction: column; height: 100%;">
                    <!-- Loading indicator -->
                    <div id="patternLoadingIndicator" style="display: none; text-align: center; padding: 2rem; color: #718096; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20;">
                        <div class="spinner" style="width: 32px; height: 32px; border: 3px solid #E2E8F0; border-top-color: #4FD1C5; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                        <p style="margin: 1rem 0 0 0; font-size: 0.875rem;">Loading pattern...</p>
                    </div>

                    <!-- Select customer message -->
                    <div id="selectCustomerMsg" style="text-align: center; padding: 2rem; color: #718096;">
                        <i data-lucide="user-plus" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #CBD5E0;"></i>
                        <p style="margin: 0; font-size: 0.875rem;">Select a customer to preview pattern</p>
                    </div>

                    <!-- Pattern Preview Grid - 2x2 Layout like saviComplete.php -->
                    <div id="patternGridContainer" style="display: none; width: 100%; height: 100%; overflow: hidden;">
                        <div class="pattern-preview-grid">
                            <!-- Front Pattern -->
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;">Front Pattern</h4>
                                <div id="frontPatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <!-- Back Pattern -->
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;">Back Pattern</h4>
                                <div id="backPatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <!-- Sleeve Pattern -->
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;">Sleeve Pattern</h4>
                                <div id="sleevePatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <!-- Patti Pattern -->
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;">Waist Band (Patti)</h4>
                                <div id="pattiPatternSvg" class="pattern-svg-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; gap: 1rem; justify-content: flex-end; flex-shrink: 0;">
            <button type="button" onclick="closePatternModal()" style="padding: 0.75rem 1.5rem; background: #F7FAFC; border: 1px solid #E2E8F0; border-radius: 6px; cursor: pointer; font-size: 0.875rem;">Cancel</button>
            <button type="button" id="generatePatternBtn" onclick="handleGeneratePattern()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500;">Buy Pattern</button>
        </div>
    </div>
</div>

<style>
    .pattern-modal-price {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }
    .pattern-modal-price.free {
        color: #DC2626;
    }
    .pattern-modal-price.paid {
        color: #065F46;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        max-height: 90vh;
        overflow-y: auto;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    /* Pattern Preview Grid Cards */
    .pattern-preview-card {
        transition: box-shadow 0.2s;
    }
    .pattern-preview-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    /* Pattern grid container - fills available space */
    #patternGridContainer {
        display: flex !important;
        flex-direction: column;
    }
    /* Pattern preview grid - 2x2 layout using full height */
    .pattern-preview-grid {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 0.5rem;
        padding: 0.5rem;
        min-height: 0;
    }
    /* Pattern cards fill their grid cell */
    .pattern-preview-card {
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    /* SVG container - fills card space below title */
    .pattern-svg-container {
        flex: 1;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pattern-svg-container svg {
        width: 100%;
        height: 100%;
    }
    /* Responsive for smaller screens */
    @media (max-width: 768px) {
        #patternModal .modal-content {
            max-width: 95% !important;
        }
        #patternModal .modal-body > div {
            grid-template-columns: 1fr !important;
        }
        .pattern-preview-grid {
            grid-template-columns: 1fr !important;
            grid-template-rows: repeat(4, 1fr);
        }
    }
</style>

<script>
<?php echo $additionalScripts; ?>

// =============================================================================
// PATTERN PREVIEW FUNCTIONS (MODAL)
// =============================================================================
// These functions handle the live SVG pattern preview in the modal.
// Patterns are fetched via AJAX from ajax-get-pattern-preview.php

/**
 * Storage for loaded pattern data.
 * Contains SVG strings for front, back, sleeve, and patti patterns.
 * @type {Object|null}
 */
let currentPatternData = null;

/**
 * Fetches pattern SVG preview from the server via AJAX.
 * Called when modal opens with a customer selected, or when
 * customer is changed in the modal dropdown.
 *
 * @param {string|number} customerId - Customer ID or 'self'
 *
 * AJAX ENDPOINT: ajax-get-pattern-preview.php
 *
 * RESPONSE FORMAT:
 * {
 *   success: boolean,
 *   customer_name: string,
 *   patterns: {
 *     front: string (SVG),
 *     back: string (SVG),
 *     sleeve: string (SVG),
 *     patti: string (SVG)
 *   },
 *   measurements: {
 *     bust: number,
 *     chest: number,
 *     waist: number,
 *     ...etc
 *   }
 * }
 */
function loadPatternPreview(customerId) {
    console.log('loadPatternPreview called with customerId:', customerId);
    if (!customerId) {
        document.getElementById('selectCustomerMsg').style.display = 'block';
        document.getElementById('patternGridContainer').style.display = 'none';
        document.getElementById('measurementsSummary').style.display = 'none';
        currentPatternData = null;
        return;
    }

    document.getElementById('selectCustomerMsg').style.display = 'none';
    document.getElementById('patternLoadingIndicator').style.display = 'block';
    document.getElementById('patternGridContainer').style.display = 'none';

    fetch('ajax-get-pattern-preview.php?customer_id=' + customerId)
        .then(response => response.json())
        .then(data => {
            console.log('Pattern preview response:', data);
            document.getElementById('patternLoadingIndicator').style.display = 'none';

            if (data.success) {
                currentPatternData = data;

                // Update measurements title with customer name
                const customerName = data.customer_name || 'Customer';
                document.getElementById('measurementsTitle').textContent = 'Measurements - ' + customerName;

                // Show measurements summary
                if (data.measurements) {
                    document.getElementById('measBust').textContent = data.measurements.bust;
                    document.getElementById('measChest').textContent = data.measurements.chest;
                    document.getElementById('measWaist').textContent = data.measurements.waist;
                    document.getElementById('measShoulder').textContent = data.measurements.shoulder;
                    document.getElementById('measFshoulder').textContent = data.measurements.fshoulder;
                    document.getElementById('measArmhole').textContent = data.measurements.armhole;
                    document.getElementById('measFlength').textContent = data.measurements.flength;
                    document.getElementById('measBlength').textContent = data.measurements.blength;
                    document.getElementById('measFndepth').textContent = data.measurements.fndepth;
                    document.getElementById('measBndepth').textContent = data.measurements.bnDepth;
                    document.getElementById('measApex').textContent = data.measurements.apex;
                    document.getElementById('measSlength').textContent = data.measurements.slength;
                    document.getElementById('measSaround').textContent = data.measurements.saround;
                    document.getElementById('measSopen').textContent = data.measurements.sopen;
                    document.getElementById('measurementsSummary').style.display = 'block';
                }

                // Populate all 4 pattern SVG containers (2x2 grid layout)
                if (data.patterns) {
                    document.getElementById('frontPatternSvg').innerHTML = data.patterns.front || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                    document.getElementById('backPatternSvg').innerHTML = data.patterns.back || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                    document.getElementById('sleevePatternSvg').innerHTML = data.patterns.sleeve || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                    document.getElementById('pattiPatternSvg').innerHTML = data.patterns.patti || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                }

                // Show the grid container
                document.getElementById('patternGridContainer').style.display = 'block';
                document.getElementById('selectCustomerMsg').style.display = 'none';
            } else {
                console.error('Pattern preview error:', data.error);
                document.getElementById('selectCustomerMsg').innerHTML = '<i data-lucide="alert-circle" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #FC8181;"></i><p style="margin: 0; font-size: 0.875rem; color: #C53030;">' + (data.error || 'Could not load patterns') + '</p>';
                document.getElementById('selectCustomerMsg').style.display = 'block';
                lucide.createIcons();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('patternLoadingIndicator').style.display = 'none';
            document.getElementById('selectCustomerMsg').innerHTML = '<i data-lucide="wifi-off" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #FC8181;"></i><p style="margin: 0; font-size: 0.875rem; color: #C53030;">Failed to load pattern</p>';
            document.getElementById('selectCustomerMsg').style.display = 'block';
            lucide.createIcons();
        });
}

/**
 * Event listener for customer dropdown changes in the modal.
 * When user selects a different customer, loads new pattern preview.
 * Now displays all 4 patterns in a 2x2 grid layout.
 */
document.getElementById('customerSelect')?.addEventListener('change', function() {
    const customerId = this.value;
    if (customerId) {
        loadPatternPreview(customerId);
    } else {
        document.getElementById('selectCustomerMsg').style.display = 'block';
        document.getElementById('patternGridContainer').style.display = 'none';
        document.getElementById('measurementsSummary').style.display = 'none';
        currentPatternData = null;
    }
});
</script>

<!-- =============================================================================
     FOOTER
     =============================================================================
     Includes common footer elements (copyright, links, etc.)
-->
<?php include __DIR__ . "/../includes/footer.php"; ?>
