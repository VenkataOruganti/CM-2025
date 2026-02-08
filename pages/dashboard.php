<?php
/**
 * =============================================================================
 * UNIFIED DASHBOARD - Main Dashboard for Individual and Boutique Users
 * =============================================================================
 *
 * PURPOSE:
 * This is the unified dashboard page that serves both individual users and
 * boutique users (tailors/fashion businesses). The left column content changes
 * dynamically based on user type, while the right column (Pattern Catalog) is
 * shared between both user types.
 *
 * USER TYPES:
 * - Individual: Shows personal "Your Measurements" in left column
 * - Boutique: Shows "Customer Search" + selected customer measurements
 *
 * PAGE STRUCTURE:
 * ┌─────────────────────────────────────────────────────────────────────────────┐
 * │ Navigation Bar (with user account dropdown)                                 │
 * ├─────────────────────────────────────────────────────────────────────────────┤
 * │ Dashboard Header (Welcome message + title)                                  │
 * ├──────────────────────────┬──────────────────────────────────────────────────┤
 * │ LEFT COLUMN (33%)        │ RIGHT COLUMN (67%)                               │
 * │ [Dynamic by user type]   │ - Print Size Selector                            │
 * │ - Individual: Own Meas.  │ - Tab Navigation (4 tabs)                        │
 * │ - Boutique: Customer     │ - Tab Content:                                   │
 * │   Search + Measurements  │   • Build Your Pattern                           │
 * │                          │   • Pre-designed Patterns                        │
 * │                          │   • Front Blouse Designs                         │
 * │                          │   • Back Blouse Designs                          │
 * └──────────────────────────┴──────────────────────────────────────────────────┘
 *
 * =============================================================================
 */

// -----------------------------------------------------------------------------
// SESSION & AUTHENTICATION
// -----------------------------------------------------------------------------
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

// Require login
requireLogin();
$currentUser = getCurrentUser();

// Determine user type and validate access
$userType = $currentUser['user_type'] ?? '';
$allowedTypes = ['individual', 'boutique'];

if (!$currentUser || !in_array($userType, $allowedTypes)) {
    header('Location: login.php');
    exit;
}

// Set user type specific variables
$isIndividual = ($userType === 'individual');
$isBoutique = ($userType === 'boutique');
$userTypeLabel = $isIndividual ? __('dashboard.individual_title') : __('dashboard.boutique_title');
$dashboardTitle = $isIndividual ? __('dashboard.individual_title') : __('dashboard.boutique_title');

// -----------------------------------------------------------------------------
// MESSAGE HANDLING
// -----------------------------------------------------------------------------
$loginMessage = '';
$loginMessageType = '';

// Check for login message (after saving pending measurements)
if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    $loginMessageType = $_SESSION['login_message_type'];
    unset($_SESSION['login_message']);
    unset($_SESSION['login_message_type']);
}

// Check for success message from pattern-studio
if (isset($_GET['success']) && $_GET['success'] == 1) {
    if (isset($_GET['action']) && $_GET['action'] === 'updated') {
        $loginMessage = __('dashboard.messages.measurements_updated');
    } else {
        $loginMessage = __('dashboard.messages.measurements_saved');
    }
    $loginMessageType = 'success';
}

// Check for upgrade success message
if (isset($_GET['upgraded']) && $_GET['upgraded'] == 1) {
    $loginMessage = __('dashboard.upgrade.success');
    $loginMessageType = 'success';
}

// -----------------------------------------------------------------------------
// DATA FETCHING BASED ON USER TYPE
// -----------------------------------------------------------------------------
global $pdo;

// Variables for Individual users
$selfMeasurements = null;

// Variables for Boutique users
$customers = [];
$selectedCustomerId = null;
$selectedCustomer = null;
$customerMeasurements = [];

if ($isIndividual) {
    // Fetch the latest "self" measurements for this individual user
    $stmt = $pdo->prepare("
        SELECT * FROM measurements
        WHERE user_id = ? AND measurement_of = 'self'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$currentUser['id']]);
    $selfMeasurements = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Boutique: Fetch customers and optionally selected customer measurements
    try {
        $userId = $currentUser['id'];

        // Fetch all customers for dropdown
        $stmt = $pdo->prepare("
            SELECT id, customer_name, customer_reference, created_at
            FROM customers
            WHERE boutique_user_id = ?
            ORDER BY customer_name ASC
        ");
        $stmt->execute([$userId]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if a specific customer is selected
        $selectedCustomerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

        if ($selectedCustomerId) {
            // Fetch full customer details
            $customerStmt = $pdo->prepare("
                SELECT * FROM customers
                WHERE id = ? AND boutique_user_id = ?
            ");
            $customerStmt->execute([$selectedCustomerId, $userId]);
            $selectedCustomer = $customerStmt->fetch(PDO::FETCH_ASSOC);

            // Fetch all measurements for this customer
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
        error_log("Dashboard customer error: " . $e->getMessage());
        $customers = [];
        $customerMeasurements = [];
    }
}

// -----------------------------------------------------------------------------
// PATTERN CATALOG DATA (SHARED FOR BOTH USER TYPES)
// -----------------------------------------------------------------------------
$predesignedPatterns = [];
$patternPortfolio = [];
$frontBlouseDesigns = [];
$backBlouseDesigns = [];

// Fetch Pre-designed Patterns
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

// Fetch Pattern Portfolio (Build Your Pattern)
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

// Fetch Front Blouse Designs
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

// Fetch Back Blouse Designs
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

// Set page configuration for shared header
$pageTitle = $dashboardTitle . ' ' . __('dashboard.title');
$activePage = 'dashboard';

// Additional styles for this page
$additionalStyles = '
        /* Customer Dropdown Styles */
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

        /* Customer List (shown when ≤15 customers) */
        .customer-list {
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
        }

        .customer-list-item {
            padding: 10px 16px;
            cursor: pointer;
            border-bottom: 1px solid #F7FAFC;
            transition: background-color 0.2s;
            text-align: left;
        }

        .customer-list-item:last-child {
            border-bottom: none;
        }

        .customer-list-item:hover {
            background-color: #EDF2F7;
        }

        .customer-list-item.selected {
            background-color: #EBF8FF;
            border-left: 3px solid #3182CE;
        }

        .customer-list-item-name {
            font-weight: 500;
            color: #2D3748;
            font-size: 0.9rem;
        }

        .customer-list-item-ref {
            font-size: 0.8rem;
            color: #718096;
            margin-left: 8px;
        }

        /* Customer Details Card */
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

        .form-group {
            position: relative;
        }

        .customer-card-no-border {
            border: none !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Navigation Dropdown Styles */
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

        /* Tab Navigation Styles */
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

        /* Pattern Catalog Grid */
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
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .pattern-catalog-thumbnail .placeholder-icon {
            color: #CBD5E0;
        }

        .pattern-catalog-info {
            padding: 0.75rem;
            text-align: left;
        }

        .pattern-catalog-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .pattern-catalog-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0;
            text-align: left;
            flex: 1;
            line-height: 1.3;
        }

        .pattern-catalog-meta {
            font-size: 0.75rem;
            color: #718096;
            text-align: left;
        }

        .pattern-catalog-price {
            font-size: 0.875rem;
            font-weight: 600;
            color: #38A169;
            white-space: nowrap;
            text-align: right;
            flex-shrink: 0;
        }

        /* Empty State Styling */
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

        /* Customer Required Error Animation */
        .customer-required-error {
            animation: shake 0.5s ease-in-out;
            box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.4) !important;
            border-color: #F56565 !important;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* Toast Notification - Centered in screen */
        .toast-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: #2D3748;
            color: white;
            padding: 1.25rem 2rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
        }

        .toast-notification.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .toast-warning {
            background: linear-gradient(135deg, #ED8936, #DD6B20);
        }

        .toast-success {
            background: linear-gradient(135deg, #48BB78, #38A169);
        }

        .toast-error {
            background: linear-gradient(135deg, #FC8181, #E53E3E);
        }

        /* Upgrade Banner Hover Effect */
        .upgrade-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Customer Name Header */
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

        /* Pattern Type Badges */
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

        /* Measurements Panels */
        .measurements-panel {
            display: none;
        }

        .measurements-panel.active {
            display: block;
        }

        /* Modal Styles */
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
        /* Bounce animation for download icon */
        @keyframes bounceDown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(6px); }
        }
        .steps-guide-icon {
            animation: bounceDown 1.5s ease-in-out infinite;
        }
        .pattern-preview-card {
            transition: box-shadow 0.2s;
        }
        .pattern-preview-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        #patternGridContainer {
            display: flex !important;
            flex-direction: column;
        }
        .pattern-preview-grid {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 0.5rem;
            padding: 0.5rem;
            min-height: 0;
        }
        .pattern-preview-card {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
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
';

// Include shared account header (includes <!DOCTYPE>, <head>, navigation, and language switcher)
include __DIR__ . '/../includes/header-account.php';
?>
    <!-- Mimic Banner -->
    <?php include __DIR__ . '/../includes/mimic-banner.php'; ?>

    <!-- Main Dashboard Section -->
    <section class="hero auth-section auth-section-padded" style="align-items: flex-start; padding-top: calc(4.5rem + 40px);">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <!-- Dashboard Welcome Message -->
                <p class="hero-description auth-description" style="margin-bottom: 0.5rem; text-align: left; margin-left: 0; margin-right: 0;">
                    <?php echo str_replace('{name}', htmlspecialchars($isBoutique ? ($currentUser['business_name'] ?? $currentUser['username']) : $currentUser['username']), __('dashboard.welcome')); ?>
                </p>

                <!-- Dashboard Title -->
                <h1 class="hero-title auth-title" style="text-align: left; margin-bottom: 15px;">
                    <?php echo $dashboardTitle; ?> <span class="hero-title-accent"><?php _e('dashboard.title'); ?></span>
                </h1>

                <!-- Success/Error Messages -->
                <?php if ($loginMessage): ?>
                    <div class="alert alert-<?php echo $loginMessageType; ?>" style="margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($loginMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Two-Column Dashboard Grid -->
                <div style="display: grid; grid-template-columns: calc(33.33% - 100px) calc(66.67% + 100px); gap: 2rem; margin-bottom: 2rem; align-items: start;">

                    <!-- ==========================================================
                         LEFT COLUMN: DYNAMIC BASED ON USER TYPE
                         ==========================================================
                    -->
                    <div class="customer-card-no-border" style="padding: 0; margin: 0; display: flow-root;">

                    <?php if ($isIndividual): ?>
                        <!-- =====================================================
                             INDIVIDUAL USER: UPGRADE BANNER + YOUR MEASUREMENTS
                             =====================================================
                        -->

                        <!-- Upgrade to Boutique Banner -->
                        <div class="upgrade-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; color: white; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                            <div style="position: relative; z-index: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                                    <h3 style="margin: 0; font-size: 1rem; font-weight: 600;"><?php _e('dashboard.upgrade.title'); ?></h3>
                                </div>
                                <p style="margin: 0 0 1rem 0; font-size: 0.8rem; line-height: 1.5; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <?php _e('dashboard.upgrade.description'); ?>
                                </p>
                                <ul style="margin: 0 0 1rem 0; padding-left: 1.25rem; font-size: 0.8rem; line-height: 1.8; text-align: left; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <li><?php _e('dashboard.upgrade.feature1'); ?></li>
                                    <li><?php _e('dashboard.upgrade.feature2'); ?></li>
                                    <li><?php _e('dashboard.upgrade.feature3'); ?></li>
                                    <li><?php _e('dashboard.upgrade.feature4'); ?></li>
                                </ul>
                                <button type="button" onclick="showUpgradeModal()" class="upgrade-btn" style="background: white; color: #1a1a1a; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: transform 0.2s, box-shadow 0.2s;">
                                    <i data-lucide="arrow-up-circle" style="width: 16px; height: 16px;"></i>
                                    <?php _e('dashboard.upgrade.btn'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="dashboard-card-title-with-icon" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h2 class="dashboard-card-title" style="margin: 0;"><?php _e('dashboard.measurements.your_measurements'); ?></h2>
                            <?php if ($selfMeasurements): ?>
                            <a href="pattern-studio.php?edit=<?php echo $selfMeasurements['id']; ?>" class="measurement-edit-btn" title="<?php _e('dashboard.measurements.edit_title'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($selfMeasurements): ?>
                            <?php
                            $patternTypeLabels = [
                                'blouse' => __('dashboard.pattern_types.blouses'),
                                'kurti' => __('dashboard.pattern_types.kurtis'),
                                'blouse_back' => __('dashboard.pattern_types.blouse_back_designs'),
                                'blouse_front' => __('dashboard.pattern_types.blouse_front_designs'),
                                'sleeve' => __('dashboard.pattern_types.sleeve_designs'),
                                'pants' => __('dashboard.pattern_types.pants')
                            ];
                            $patternType = $selfMeasurements['pattern_type'] ?? 'blouse';
                            $patternTypeLabel = $patternTypeLabels[$patternType] ?? ucfirst($patternType);
                            ?>
                            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem;">
                                <p class="measurement-category" style="margin: 0; font-size: 0.875rem;"><?php _e('dashboard.measurements.category'); ?> <?php echo ucfirst($selfMeasurements['category']); ?></p>
                                <span style="display: inline-block; background: #E6FFFA; color: #319795; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                                    <?php echo $patternTypeLabel; ?>
                                </span>
                            </div>

                            <?php
                            // Define measurements based on category
                            if ($selfMeasurements['category'] === 'women') {
                                $displayMeasurements = [
                                    __('dashboard.measurements.blouse_back_length') => $selfMeasurements['blength'] ?? null,
                                    __('dashboard.measurements.full_shoulder') => $selfMeasurements['fshoulder'] ?? null,
                                    __('dashboard.measurements.shoulder_strap') => $selfMeasurements['shoulder'] ?? null,
                                    __('dashboard.measurements.back_neck_depth') => $selfMeasurements['bnDepth'] ?? null,
                                    __('dashboard.measurements.front_neck_depth') => $selfMeasurements['fndepth'] ?? null,
                                    __('dashboard.measurements.shoulder_to_apex') => $selfMeasurements['apex'] ?? null,
                                    __('dashboard.measurements.front_length') => $selfMeasurements['flength'] ?? null,
                                    __('dashboard.measurements.upper_chest') => $selfMeasurements['chest'] ?? null,
                                    __('dashboard.measurements.bust_round') => $selfMeasurements['bust'] ?? null,
                                    __('dashboard.measurements.waist_round') => $selfMeasurements['waist'] ?? null,
                                    __('dashboard.measurements.sleeve_length') => $selfMeasurements['slength'] ?? null,
                                    __('dashboard.measurements.arm_round') => $selfMeasurements['saround'] ?? null,
                                    __('dashboard.measurements.sleeve_end_round') => $selfMeasurements['sopen'] ?? null,
                                    __('dashboard.measurements.armhole') => $selfMeasurements['armhole'] ?? null,
                                ];
                            } else {
                                $displayMeasurements = [
                                    __('dashboard.measurements.chest') => $selfMeasurements['bust'] ?? null,
                                    __('dashboard.measurements.waist') => $selfMeasurements['waist'] ?? null,
                                    __('dashboard.measurements.hips') => $selfMeasurements['hips'] ?? null,
                                    __('dashboard.measurements.height') => $selfMeasurements['height'] ?? null,
                                ];
                            }
                            ?>

                            <?php foreach ($displayMeasurements as $label => $value): ?>
                                <?php if ($value): ?>
                                <div style="display: flex; padding: 0.25rem 0; border-bottom: 1px solid #E2E8F0; font-size: 0.75rem; line-height: 1.2;">
                                    <span style="font-weight: 500; color: #2D3748; min-width: 160px; text-align: right;"><?php echo $label; ?>:</span>
                                    <span style="color: #000000; margin-left: 0.75rem; font-weight: 500;"><?php echo rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'); ?></span>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <div style="margin-top: 1rem; padding-top: 0.5rem; border-top: 1px solid #E2E8F0;">
                                <span style="font-size: 0.7rem; color: #718096;"><?php _e('dashboard.measurements.last_updated'); ?> <?php echo date('M j, Y', strtotime($selfMeasurements['created_at'])); ?></span>
                            </div>

                        <?php else: ?>
                            <div style="padding: 1.5rem; text-align: center; color: #718096;">
                                <p style="margin-bottom: 1rem;"><?php _e('dashboard.measurements.no_measurements'); ?></p>
                                <a href="pattern-studio.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                                    <?php _e('dashboard.measurements.add_measurements'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- =====================================================
                             BOUTIQUE USER: CUSTOMER SEARCH + MEASUREMENTS
                             =====================================================
                        -->
                        <?php
                        // Determine display mode based on customer count
                        $customerCount = count($customers);
                        $showCustomerList = ($customerCount > 0 && $customerCount <= 15 && !$selectedCustomer);
                        $showSearchField = ($customerCount > 15 || $selectedCustomer);
                        ?>

                        <?php if ($showSearchField): ?>
                        <!-- Searchable Customer Dropdown (shown when >15 customers or customer is selected) -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="text-align: left; margin-bottom: 0;"><?php _e('dashboard.customer.select_customer'); ?></label>
                                <a href="pattern-studio.php" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; letter-spacing: -0.5px;"><?php _e('dashboard.customer.new_customer'); ?></a>
                            </div>
                            <input
                                type="text"
                                id="customerSearch"
                                class="form-input"
                                placeholder="<?php _e('dashboard.customer.search_placeholder'); ?>"
                                autocomplete="off"
                                style="text-align: left;"
                            >
                            <div id="customerDropdown" class="customer-dropdown" style="display: none;"></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($showCustomerList): ?>
                            <!-- Customer List Header with New Customer button (shown when ≤15 customers) -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="text-align: left; margin-bottom: 0;"><?php _e('dashboard.customer.your_customers'); ?> (<?php echo $customerCount; ?>)</label>
                                <a href="pattern-studio.php" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; letter-spacing: -0.5px;"><?php _e('dashboard.customer.new_customer'); ?></a>
                            </div>
                            <!-- Customer List -->
                            <div class="customer-list" id="customerList">
                                <?php foreach ($customers as $customer): ?>
                                    <div class="customer-list-item" onclick="selectCustomer(<?php echo $customer['id']; ?>)">
                                        <span class="customer-list-item-name"><?php echo htmlspecialchars($customer['customer_name']); ?></span>
                                        <?php if ($customer['customer_reference']): ?>
                                            <span class="customer-list-item-ref"><?php echo htmlspecialchars($customer['customer_reference']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

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
                                <a href="pattern-studio.php?edit=<?php echo $customerMeasurements[0]['id']; ?>" class="measurement-edit-btn" title="<?php _e('dashboard.measurements.edit_title'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>

                            <?php if (count($customerMeasurements) > 0): ?>
                                <?php
                                $patternTypeLabels = [
                                    'blouse' => __('dashboard.pattern_types.blouses'),
                                    'kurti' => __('dashboard.pattern_types.kurtis'),
                                    'blouse_back' => __('dashboard.pattern_types.blouse_back_designs'),
                                    'blouse_front' => __('dashboard.pattern_types.blouse_front_designs'),
                                    'sleeve' => __('dashboard.pattern_types.sleeve_designs'),
                                    'pants' => __('dashboard.pattern_types.pants')
                                ];

                                // Group measurements by pattern type
                                $measurementsByType = [];
                                foreach ($customerMeasurements as $measurement) {
                                    $type = $measurement['pattern_type'] ?? 'blouse';
                                    if (!isset($measurementsByType[$type])) {
                                        $measurementsByType[$type] = $measurement;
                                    }
                                }

                                $latestMeasurement = $customerMeasurements[0];
                                $defaultType = $latestMeasurement['pattern_type'] ?? 'blouse';
                                ?>

                                <!-- Pattern Type Badges -->
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

                                <!-- Measurements Display Container -->
                                <div id="measurementsContainer">
                                    <?php foreach ($measurementsByType as $type => $measurement): ?>
                                        <div id="measurements-<?php echo htmlspecialchars($type); ?>"
                                             class="measurements-panel <?php echo $type === $defaultType ? 'active' : ''; ?>">
                                            <?php
                                            if ($type === 'blouse' || $type === 'blouse_front' || $type === 'blouse_back') {
                                                $displayMeasurements = [
                                                    __('dashboard.measurements.blouse_back_length') => $measurement['blength'] ?? null,
                                                    __('dashboard.measurements.full_shoulder') => $measurement['fshoulder'] ?? null,
                                                    __('dashboard.measurements.shoulder_strap') => $measurement['shoulder'] ?? null,
                                                    __('dashboard.measurements.back_neck_depth') => $measurement['bnDepth'] ?? null,
                                                    __('dashboard.measurements.front_neck_depth') => $measurement['fndepth'] ?? null,
                                                    __('dashboard.measurements.shoulder_to_apex') => $measurement['apex'] ?? null,
                                                    __('dashboard.measurements.front_length') => $measurement['flength'] ?? null,
                                                    __('dashboard.measurements.upper_chest') => $measurement['chest'] ?? null,
                                                    __('dashboard.measurements.bust_round') => $measurement['bust'] ?? null,
                                                    __('dashboard.measurements.waist_round') => $measurement['waist'] ?? null,
                                                    __('dashboard.measurements.sleeve_length') => $measurement['slength'] ?? null,
                                                    __('dashboard.measurements.arm_round') => $measurement['saround'] ?? null,
                                                    __('dashboard.measurements.sleeve_end_round') => $measurement['sopen'] ?? null,
                                                    __('dashboard.measurements.armhole') => $measurement['armhole'] ?? null,
                                                ];
                                            } elseif ($type === 'kurti') {
                                                $displayMeasurements = [
                                                    __('dashboard.measurements.kurti_length') => $measurement['blength'] ?? null,
                                                    __('dashboard.measurements.full_shoulder') => $measurement['fshoulder'] ?? null,
                                                    __('dashboard.measurements.shoulder_strap') => $measurement['shoulder'] ?? null,
                                                    __('dashboard.measurements.front_neck_depth') => $measurement['fndepth'] ?? null,
                                                    __('dashboard.measurements.upper_chest') => $measurement['chest'] ?? null,
                                                    __('dashboard.measurements.bust_round') => $measurement['bust'] ?? null,
                                                    __('dashboard.measurements.waist_round') => $measurement['waist'] ?? null,
                                                    __('dashboard.measurements.hip_round') => $measurement['hip'] ?? null,
                                                    __('dashboard.measurements.sleeve_length') => $measurement['slength'] ?? null,
                                                    __('dashboard.measurements.arm_round') => $measurement['saround'] ?? null,
                                                    __('dashboard.measurements.sleeve_end_round') => $measurement['sopen'] ?? null,
                                                ];
                                            } elseif ($type === 'pants') {
                                                $displayMeasurements = [
                                                    __('dashboard.measurements.waist_round') => $measurement['waist'] ?? null,
                                                    __('dashboard.measurements.hip_round') => $measurement['hip'] ?? null,
                                                    __('dashboard.measurements.inseam') => $measurement['inseam'] ?? null,
                                                    __('dashboard.measurements.outseam') => $measurement['outseam'] ?? null,
                                                    __('dashboard.measurements.thigh_round') => $measurement['thigh'] ?? null,
                                                    __('dashboard.measurements.knee_round') => $measurement['knee'] ?? null,
                                                    __('dashboard.measurements.ankle_round') => $measurement['ankle'] ?? null,
                                                ];
                                            } else {
                                                $displayMeasurements = [
                                                    __('dashboard.measurements.blouse_back_length') => $measurement['blength'] ?? null,
                                                    __('dashboard.measurements.full_shoulder') => $measurement['fshoulder'] ?? null,
                                                    __('dashboard.measurements.shoulder_strap') => $measurement['shoulder'] ?? null,
                                                    __('dashboard.measurements.back_neck_depth') => $measurement['bnDepth'] ?? null,
                                                    __('dashboard.measurements.front_neck_depth') => $measurement['fndepth'] ?? null,
                                                    __('dashboard.measurements.shoulder_to_apex') => $measurement['apex'] ?? null,
                                                    __('dashboard.measurements.front_length') => $measurement['flength'] ?? null,
                                                    __('dashboard.measurements.upper_chest') => $measurement['chest'] ?? null,
                                                    __('dashboard.measurements.bust_round') => $measurement['bust'] ?? null,
                                                    __('dashboard.measurements.waist_round') => $measurement['waist'] ?? null,
                                                    __('dashboard.measurements.sleeve_length') => $measurement['slength'] ?? null,
                                                    __('dashboard.measurements.arm_round') => $measurement['saround'] ?? null,
                                                    __('dashboard.measurements.sleeve_end_round') => $measurement['sopen'] ?? null,
                                                    __('dashboard.measurements.armhole') => $measurement['armhole'] ?? null,
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
                                <p style="color: #718096; font-style: italic;"><?php _e('dashboard.customer.no_measurements_recorded'); ?></p>
                            <?php endif; ?>
                        <?php elseif (count($customers) > 0): ?>
                            <!-- Message is now in the label above -->
                        <?php else: ?>
                            <p style="color: #718096; text-align: center; padding: 2rem;">
                                <?php _e('dashboard.customer.no_customers'); ?> <?php _e('dashboard.customer.no_customers_msg'); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>

                    <!-- ==========================================================
                         RIGHT COLUMN: PATTERN CATALOG (SHARED FOR BOTH USERS)
                         ==========================================================
                    -->
                    <div style="padding: 0; margin: 0; display: flow-root;">
                        <!-- Steps to Download Pattern -->
                        <div class="steps-guide" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 1rem;">
                            <!-- Left column: Animated download icon -->
                            <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: linear-gradient(135deg, #0ea5e9, #0369a1); border-radius: 10px;">
                                <i data-lucide="download" class="steps-guide-icon" style="width: 28px; height: 28px; color: #ffffff;"></i>
                            </div>
                            <!-- Right column: Text content -->
                            <div style="flex: 1; text-align: left;">
                                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; color: #0369a1; font-weight: 600;"><?php _e('dashboard.steps_guide.title'); ?></h4>
                                <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.6;">
                                    <strong>Step 01:</strong> <?php _e('dashboard.steps_guide.step1'); ?><br>
                                    <strong>Step 02:</strong> <?php _e('dashboard.steps_guide.step2'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="dashboard-card" style="padding: 1.5rem;">
                        <!-- Tab Navigation -->
                        <div class="pattern-tabs">
                            <button class="pattern-tab active" data-tab="build">
                                <i data-lucide="settings-2" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                <?php _e('dashboard.patterns.build_your_pattern'); ?>
                            </button>
                            <button class="pattern-tab" data-tab="predesigned">
                                <i data-lucide="file-text" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                <?php _e('dashboard.patterns.predesigned_patterns'); ?>
                            </button>
                            <button class="pattern-tab" data-tab="front">
                                <i data-lucide="shirt" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                <?php _e('dashboard.patterns.front_blouse_designs'); ?>
                            </button>
                            <button class="pattern-tab" data-tab="back">
                                <i data-lucide="flip-horizontal" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                <?php _e('dashboard.patterns.back_blouse_designs'); ?>
                            </button>
                        </div>

                        <!-- Tab Content: Build Your Pattern -->
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
                                                <div class="pattern-catalog-title-row">
                                                    <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                                    <span class="pattern-catalog-price" style="<?php echo ($item['price'] ?? 0) == 0 ? 'color: #DC2626;' : ''; ?>">
                                                        <?php echo ($item['price'] ?? 0) > 0 ? '₹' . number_format($item['price'], 0) : __('dashboard.patterns.free'); ?>
                                                    </span>
                                                </div>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="settings-2"></i>
                                    <h3><?php _e('dashboard.patterns.no_custom_patterns'); ?></h3>
                                    <p><?php _e('dashboard.patterns.no_custom_patterns_desc'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Pre-designed Patterns -->
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
                                                <div class="pattern-catalog-title-row">
                                                    <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($pattern['title']); ?></h4>
                                                    <span class="pattern-catalog-price" style="<?php echo $pattern['price'] == 0 ? 'color: #DC2626;' : ''; ?>">
                                                        <?php echo $pattern['price'] > 0 ? '₹' . number_format($pattern['price'], 0) : __('dashboard.patterns.free'); ?>
                                                    </span>
                                                </div>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($pattern['designer_name'] ?? 'CuttingMaster'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="file-text"></i>
                                    <h3><?php _e('dashboard.patterns.no_predesigned_patterns'); ?></h3>
                                    <p><?php _e('dashboard.patterns.no_predesigned_patterns_desc'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Front Blouse Designs -->
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
                                                <div class="pattern-catalog-title-row">
                                                    <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($design['name']); ?></h4>
                                                    <span class="pattern-catalog-price" style="<?php echo $design['price'] == 0 ? 'color: #DC2626;' : ''; ?>">
                                                        <?php echo $design['price'] > 0 ? '₹' . number_format($design['price'], 0) : __('dashboard.patterns.free'); ?>
                                                    </span>
                                                </div>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($design['style_category'] ?? 'Classic'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="shirt"></i>
                                    <h3><?php _e('dashboard.patterns.no_front_designs'); ?></h3>
                                    <p><?php _e('dashboard.patterns.no_front_designs_desc'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Content: Back Blouse Designs -->
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
                                                <div class="pattern-catalog-title-row">
                                                    <h4 class="pattern-catalog-title"><?php echo htmlspecialchars($design['name']); ?></h4>
                                                    <span class="pattern-catalog-price" style="<?php echo $design['price'] == 0 ? 'color: #DC2626;' : ''; ?>">
                                                        <?php echo $design['price'] > 0 ? '₹' . number_format($design['price'], 0) : __('dashboard.patterns.free'); ?>
                                                    </span>
                                                </div>
                                                <p class="pattern-catalog-meta"><?php echo htmlspecialchars($design['style_category'] ?? 'Classic'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-catalog-state">
                                    <i data-lucide="flip-horizontal"></i>
                                    <h3><?php _e('dashboard.patterns.no_back_designs'); ?></h3>
                                    <p><?php _e('dashboard.patterns.no_back_designs_desc'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    </div><!-- End right column wrapper -->
                </div>
            </div>
        </div>
    </section>

<?php
// Prepare JavaScript data
$isIndividualJs = $isIndividual ? 'true' : 'false';

// For individual users, we use 'self' as the customer_id
// For boutique users, we use the selected customer ID
if ($isIndividual) {
    $selectedMeasurementJson = $selfMeasurements ? json_encode([
        'id' => 'self',
        'measurement_id' => $selfMeasurements['id'],
        'name' => $currentUser['username'],
        'type' => 'self'
    ]) : 'null';
} else {
    $selectedMeasurementJson = $selectedCustomer ? json_encode([
        'id' => $selectedCustomerId,
        'name' => $selectedCustomer['customer_name'],
        'reference' => $selectedCustomer['customer_reference'] ?? ''
    ]) : 'null';
}
?>

<!-- Pattern Modal (for Build Your Pattern items) -->
<div id="patternModal" class="modal">
    <div class="modal-content" style="width: 80vw; height: 80vh; max-width: none; display: flex; flex-direction: column;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #E2E8F0; flex-shrink: 0;">
            <h3 style="margin: 0; font-size: 1.25rem;">Generate Pattern</h3>
            <button type="button" onclick="closePatternModal()" style="background: none; border: none; cursor: pointer; color: #718096; padding: 0.25rem;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.5rem; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: grid; grid-template-columns: 0.5fr 1.5fr; gap: 1.5rem; flex: 1; min-height: 0;">
                <!-- LEFT COLUMN: Pattern Info -->
                <div class="pattern-modal-left" style="overflow-y: auto;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #E2E8F0;">
                        <img id="patternModalImage" src="" alt="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <div>
                            <h4 id="patternModalTitle" style="margin: 0 0 0.25rem 0; font-size: 1rem;"></h4>
                            <p id="patternModalDesc" style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #718096; line-height: 1.4;"></p>
                            <p id="patternModalPrice" class="pattern-modal-price"></p>
                        </div>
                    </div>

                    <input type="hidden" id="patternModalItemId" value="">
                    <input type="hidden" id="patternModalCodePage" value="">
                    <input type="hidden" id="patternModalItemPrice" value="">

                    <div id="customerSelectionSection" style="margin-bottom: 1rem;">
                        <div id="selectedCustomerDisplay" style="display: none; padding: 0.625rem; background: #E6FFFA; border-radius: 6px; border: 1px solid #38B2AC;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="font-size: 0.7rem; color: #718096; display: block;"><?php echo $isIndividual ? 'Your Measurements:' : 'Customer:'; ?></span>
                                    <span id="selectedCustomerName" style="font-weight: 600; color: #234E52; font-size: 0.875rem;"></span>
                                </div>
                                <?php if (!$isIndividual): ?>
                                <button type="button" onclick="changeCustomer()" style="background: none; border: none; color: #38B2AC; cursor: pointer; font-size: 0.7rem; text-decoration: underline;">Change</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="hidden" id="selectedCustomerId" value="">

                        <?php if (!$isIndividual): ?>
                        <div id="customerDropdownSection" style="display: none;">
                            <label style="display: block; margin-bottom: 0.375rem; font-weight: 500; font-size: 0.8rem;">Select Customer *</label>
                            <select id="customerSelect" style="width: 100%; padding: 0.625rem; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 0.8rem;">
                                <option value="">-- Select Customer --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['customer_name']); ?> (<?php echo htmlspecialchars($customer['customer_reference'] ?? ''); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="paymentSection" style="display: none; margin-bottom: 1rem; padding: 0.625rem; background: #FEF3C7; border-radius: 6px; border: 1px solid #F59E0B;">
                        <p style="margin: 0; font-size: 0.75rem; color: #92400E;">
                            <i data-lucide="info" style="width: 12px; height: 12px; display: inline; vertical-align: middle;"></i>
                            Paid pattern - payment required.
                        </p>
                    </div>

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

                <!-- RIGHT COLUMN: Pattern Preview -->
                <div class="pattern-modal-right" style="display: flex; flex-direction: column; height: 100%;">
                    <div id="patternLoadingIndicator" style="display: none; text-align: center; padding: 2rem; color: #718096; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20;">
                        <div class="spinner" style="width: 32px; height: 32px; border: 3px solid #E2E8F0; border-top-color: #4FD1C5; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                        <p style="margin: 1rem 0 0 0; font-size: 0.875rem;"><?php _e('dashboard.modal.loading_pattern'); ?></p>
                    </div>

                    <div id="selectCustomerMsg" style="text-align: center; padding: 2rem; color: #718096;">
                        <i data-lucide="user-plus" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #CBD5E0;"></i>
                        <p style="margin: 0; font-size: 0.875rem;"><?php echo $isIndividual ? __('dashboard.modal.add_measurements_preview') : __('dashboard.modal.select_customer_preview'); ?></p>
                    </div>

                    <div id="patternGridContainer" style="display: none; width: 100%; height: 100%; overflow: hidden;">
                        <div class="pattern-preview-grid">
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;"><?php _e('dashboard.modal.front_pattern'); ?></h4>
                                <div id="frontPatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;"><?php _e('dashboard.modal.back_pattern'); ?></h4>
                                <div id="backPatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;"><?php _e('dashboard.modal.sleeve_pattern'); ?></h4>
                                <div id="sleevePatternSvg" class="pattern-svg-container"></div>
                            </div>
                            <div class="pattern-preview-card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.5rem;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: #2D3748; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.25rem;"><?php _e('dashboard.modal.waist_band'); ?></h4>
                                <div id="pattiPatternSvg" class="pattern-svg-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; gap: 1rem; justify-content: flex-end; flex-shrink: 0;">
            <button type="button" onclick="closePatternModal()" style="padding: 0.75rem 1.5rem; background: #F7FAFC; border: 1px solid #E2E8F0; border-radius: 6px; cursor: pointer; font-size: 0.875rem;"><?php _e('dashboard.modal.cancel'); ?></button>
            <button type="button" id="generatePatternBtn" onclick="handleGeneratePattern()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #4FD1C5, #38B2AC); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500;"><?php _e('dashboard.modal.buy_pattern'); ?></button>
        </div>
    </div>
</div>

<!-- Upgrade Account Modal -->
<?php if ($isIndividual): ?>
<div id="upgradeModal" class="modal">
    <div class="modal-content" style="width: 450px; max-width: 90vw;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #E2E8F0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px 12px 0 0;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: white;">
                <i data-lucide="sparkles" style="width: 24px; height: 24px;"></i>
                <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;"><?php _e('dashboard.upgrade.modal_title'); ?></h3>
            </div>
            <button type="button" onclick="closeUpgradeModal()" style="background: rgba(255,255,255,0.2); border: none; cursor: pointer; color: white; padding: 0.25rem; border-radius: 4px;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <p style="margin: 0 0 1.25rem 0; color: #4A5568; font-size: 0.9rem; line-height: 1.6;">
                <?php echo __('dashboard.upgrade.modal_description'); ?>
            </p>

            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                <h4 style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: #166534; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="gift" style="width: 16px; height: 16px;"></i>
                    <?php _e('dashboard.upgrade.what_you_get'); ?>
                </h4>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem; color: #166534; line-height: 1.8;">
                    <li><strong><?php _e('dashboard.upgrade.benefit1'); ?></strong> - <?php _e('dashboard.upgrade.benefit1_desc'); ?></li>
                    <li><strong><?php _e('dashboard.upgrade.benefit2'); ?></strong> - <?php _e('dashboard.upgrade.benefit2_desc'); ?></li>
                    <li><strong><?php _e('dashboard.upgrade.benefit3'); ?></strong> - <?php _e('dashboard.upgrade.benefit3_desc'); ?></li>
                    <li><strong><?php _e('dashboard.upgrade.benefit4'); ?></strong> - <?php _e('dashboard.upgrade.benefit4_desc'); ?></li>
                </ul>
            </div>

            <div style="background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 8px; padding: 0.875rem; margin-bottom: 0.5rem;">
                <p style="margin: 0; font-size: 0.8rem; color: #92400E; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <i data-lucide="info" style="width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px;"></i>
                    <span><?php _e('dashboard.upgrade.info_message'); ?></span>
                </p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" onclick="closeUpgradeModal()" style="padding: 0.75rem 1.5rem; background: #F7FAFC; border: 1px solid #E2E8F0; border-radius: 6px; cursor: pointer; font-size: 0.875rem; color: #4A5568;">
                <?php _e('dashboard.upgrade.maybe_later'); ?>
            </button>
            <button type="button" id="confirmUpgradeBtn" onclick="confirmUpgrade()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="arrow-up-circle" style="width: 16px; height: 16px;"></i>
                <?php _e('dashboard.upgrade.confirm_btn'); ?>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // User type and data
    const isIndividual = <?php echo $isIndividualJs; ?>;
    const customers = <?php echo json_encode($customers); ?>;
    const dashboardSelectedCustomer = <?php echo $selectedMeasurementJson; ?>;

    // Measurement Type Switcher (for boutique users)
    function switchMeasurementType(type, measurementId) {
        document.querySelectorAll('.pattern-type-badge').forEach(badge => {
            badge.classList.remove('active');
        });
        document.querySelector('.pattern-type-badge[data-type="' + type + '"]').classList.add('active');

        document.querySelectorAll('.measurements-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        const targetPanel = document.getElementById('measurements-' + type);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }

        const editBtn = document.querySelector('.measurement-edit-btn');
        if (editBtn && measurementId) {
            editBtn.href = 'pattern-studio.php?edit=' + measurementId;
        }
    }

    // Customer Search Dropdown (for boutique users)
    const customerSearch = document.getElementById('customerSearch');
    const customerDropdown = document.getElementById('customerDropdown');

    if (customerSearch && customerDropdown) {
        customerSearch.addEventListener('focus', function() {
            showCustomerDropdown(this.value);
        });

        customerSearch.addEventListener('input', function() {
            showCustomerDropdown(this.value);
        });

        document.addEventListener('click', function(e) {
            if (!customerSearch.contains(e.target) && !customerDropdown.contains(e.target)) {
                customerDropdown.style.display = 'none';
            }
        });

        function showCustomerDropdown(searchTerm) {
            let filtered = customers.filter(c =>
                c.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (c.customer_reference && c.customer_reference.toLowerCase().includes(searchTerm.toLowerCase()))
            );

            // If more than 15 customers and no search term, show top 15 only
            const maxDisplay = 15;
            let showingLimited = false;
            if (filtered.length > maxDisplay && searchTerm.trim() === '') {
                filtered = filtered.slice(0, maxDisplay);
                showingLimited = true;
            }

            if (filtered.length === 0) {
                customerDropdown.innerHTML = '<div class="customer-dropdown-item" style="color: #718096;"><?php echo addslashes(__('dashboard.customer.no_customers_found')); ?></div>';
            } else {
                let html = filtered.map(c =>
                    '<div class="customer-dropdown-item" onclick="selectCustomer(' + c.id + ')">' +
                        '<span class="customer-dropdown-item-name">' + escapeHtml(c.customer_name) + '</span>' +
                        (c.customer_reference ? '<span class="customer-dropdown-item-ref">' + escapeHtml(c.customer_reference) + '</span>' : '') +
                    '</div>'
                ).join('');

                // Show hint if displaying limited results
                if (showingLimited) {
                    html += '<div class="customer-dropdown-item" style="color: #718096; font-size: 0.8rem; text-align: center; background: #F7FAFC;"><?php echo addslashes(__('dashboard.customer.type_to_search')); ?></div>';
                }

                customerDropdown.innerHTML = html;
            }

            customerDropdown.style.display = 'block';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    function selectCustomer(customerId) {
        window.location.href = 'dashboard.php?customer_id=' + customerId;
    }

    // Pattern Catalog Tab Switching
    document.querySelectorAll('.pattern-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.pattern-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.pattern-tab-content').forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            const tabId = 'tab-' + this.dataset.tab;
            document.getElementById(tabId).classList.add('active');

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });

    // Validation Functions
    function showMeasurementsRequiredError() {
        showToast('<?php echo addslashes(__('dashboard.toast.add_measurements_first')); ?>', 'warning');
    }

    function showCustomerRequiredError() {
        const customerSearchWrapper = document.querySelector('.form-group');
        const customerSearchInput = document.getElementById('customerSearch');

        if (customerSearchWrapper) {
            customerSearchWrapper.classList.add('customer-required-error');
            setTimeout(() => {
                customerSearchWrapper.classList.remove('customer-required-error');
            }, 3000);
        }

        if (customerSearchInput) {
            customerSearchInput.focus();
            customerSearchInput.placeholder = '<?php echo addslashes(__('dashboard.toast.select_customer_required')); ?>';
            setTimeout(() => {
                customerSearchInput.placeholder = '<?php echo addslashes(__('dashboard.customer.search_placeholder')); ?>';
            }, 3000);
        }

        showToast('<?php echo addslashes(__('dashboard.toast.select_customer_first')); ?>', 'warning');
    }

    function showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        // Determine icon based on type
        let icon = 'info';
        if (type === 'warning') icon = 'alert-triangle';
        else if (type === 'success') icon = 'check-circle';
        else if (type === 'error') icon = 'x-circle';

        const toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.innerHTML = '<i data-lucide="' + icon + '" style="width: 20px; height: 20px;"></i><span>' + message + '</span>';

        document.body.appendChild(toast);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        setTimeout(() => toast.classList.add('show'), 10);

        // Keep success messages longer
        const duration = type === 'success' ? 5000 : 4000;
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Pattern Navigation Functions
    function viewPredesignedPattern(patternId) {
        window.location.href = 'view-predesigned-pattern.php?id=' + patternId;
    }

    function viewBlouseDesign(designId, designType) {
        window.location.href = 'view-blouse-design.php?id=' + designId + '&type=' + designType;
    }

    // Pattern Modal Functions
    function openPatternModal(item) {
        // For individual users, check if they have measurements
        if (isIndividual) {
            if (!dashboardSelectedCustomer) {
                showMeasurementsRequiredError();
                return;
            }
        } else {
            // For boutique users, check if customer is selected
            if (!dashboardSelectedCustomer) {
                showCustomerRequiredError();
                return;
            }
        }

        const customerId = dashboardSelectedCustomer.id;
        const measurementId = dashboardSelectedCustomer.measurement_id || '';
        const price = parseFloat(item.price) || 0;
        const patternType = item.code_page || 'savi';

        // Navigate to pattern preview page
        // Include measurement_id for individual users where customer_id = 'self'
        let previewUrl = 'pattern-preview.php'
            + '?pattern=' + encodeURIComponent(patternType)
            + '&customer_id=' + encodeURIComponent(customerId)
            + '&item_id=' + encodeURIComponent(item.id)
            + '&price=' + encodeURIComponent(price);

        if (measurementId) {
            previewUrl += '&measurement_id=' + encodeURIComponent(measurementId);
        }

        window.location.href = previewUrl;
    }

    function changeCustomer() {
        document.getElementById('selectedCustomerDisplay').style.display = 'none';
        document.getElementById('customerDropdownSection').style.display = 'block';
        document.getElementById('selectedCustomerId').value = '';

        document.getElementById('selectCustomerMsg').style.display = 'block';
        document.getElementById('patternGridContainer').style.display = 'none';
    }

    function closePatternModal() {
        document.getElementById('patternModal').classList.remove('active');
    }

    function handleGeneratePattern() {
        let customerId = document.getElementById('selectedCustomerId').value;
        if (!customerId && !isIndividual) {
            customerId = document.getElementById('customerSelect').value;
        }

        if (!customerId) {
            alert(isIndividual ? 'Please add your measurements first' : 'Please select a customer');
            return;
        }

        const codePage = document.getElementById('patternModalCodePage').value;
        const price = parseFloat(document.getElementById('patternModalItemPrice').value);
        const itemId = document.getElementById('patternModalItemId').value;

        if (price > 0) {
            window.location.href = 'pattern-payment.php?item_id=' + itemId + '&customer_id=' + customerId;
        } else {
            window.location.href = 'pattern-download.php?item_id=' + itemId + '&customer_id=' + customerId;
        }
    }

    // Event Listeners
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePatternModal();
        }
    });

    document.getElementById('patternModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePatternModal();
    });

    // Pattern Preview Functions
    let currentPatternData = null;

    function loadPatternPreview(customerId) {
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
                document.getElementById('patternLoadingIndicator').style.display = 'none';

                if (data.success) {
                    currentPatternData = data;

                    const customerName = data.customer_name || 'Customer';
                    document.getElementById('measurementsTitle').textContent = 'Measurements - ' + customerName;

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

                    if (data.patterns) {
                        document.getElementById('frontPatternSvg').innerHTML = data.patterns.front || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                        document.getElementById('backPatternSvg').innerHTML = data.patterns.back || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                        document.getElementById('sleevePatternSvg').innerHTML = data.patterns.sleeve || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                        document.getElementById('pattiPatternSvg').innerHTML = data.patterns.patti || '<p style="color: #718096; font-size: 0.75rem;">Not available</p>';
                    }

                    document.getElementById('patternGridContainer').style.display = 'block';
                    document.getElementById('selectCustomerMsg').style.display = 'none';
                } else {
                    document.getElementById('selectCustomerMsg').innerHTML = '<i data-lucide="alert-circle" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #FC8181;"></i><p style="margin: 0; font-size: 0.875rem; color: #C53030;">' + (data.error || 'Could not load patterns') + '</p>';
                    document.getElementById('selectCustomerMsg').style.display = 'block';
                    lucide.createIcons();
                }
            })
            .catch(error => {
                document.getElementById('patternLoadingIndicator').style.display = 'none';
                document.getElementById('selectCustomerMsg').innerHTML = '<i data-lucide="wifi-off" style="width: 48px; height: 48px; stroke-width: 1; margin-bottom: 1rem; color: #FC8181;"></i><p style="margin: 0; font-size: 0.875rem; color: #C53030;">Failed to load pattern</p>';
                document.getElementById('selectCustomerMsg').style.display = 'block';
                lucide.createIcons();
            });
    }

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

    // =========================================================================
    // UPGRADE ACCOUNT FUNCTIONS (Individual -> Boutique)
    // =========================================================================

    function showUpgradeModal() {
        const modal = document.getElementById('upgradeModal');
        if (modal) {
            modal.classList.add('active');
            lucide.createIcons();
        }
    }

    function closeUpgradeModal() {
        const modal = document.getElementById('upgradeModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    function confirmUpgrade() {
        const btn = document.getElementById('confirmUpgradeBtn');
        const originalContent = btn.innerHTML;

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite;"></div> Upgrading...';

        fetch('ajax-upgrade-account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showToast(data.message, 'success');

                // Close modal
                closeUpgradeModal();

                // Redirect to dashboard after a short delay to show the boutique experience
                setTimeout(() => {
                    window.location.href = 'dashboard.php?upgraded=1';
                }, 1500);
            } else {
                // Show error
                showToast(data.error || 'Failed to upgrade account', 'error');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Upgrade error:', error);
            showToast('An error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    // Close upgrade modal on Escape key and background click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUpgradeModal();
        }
    });

    document.getElementById('upgradeModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeUpgradeModal();
    });

    // Initialize Lucide icons
    lucide.createIcons();
</script>

<!-- Footer -->
<?php include __DIR__ . "/../includes/footer.php"; ?>
