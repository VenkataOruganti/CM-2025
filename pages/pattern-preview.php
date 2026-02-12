<?php
/**
 * Pattern Preview Page
 *
 * Displays pattern preview with proper site header/footer.
 * Loads the appropriate pattern file based on the 'pattern' parameter.
 *
 * URL Parameters:
 * - pattern: Pattern type to load (e.g., 'savi' for saree blouse)
 * - customer_id: Customer ID for measurements
 * - item_id: Pattern item ID from portfolio
 * - price: Pattern price (0 = free)
 *
 * Note: Paper size is now selected on pattern-download.php for PDF format only.
 */

session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

// Require login
requireLogin();
$currentUser = getCurrentUser();

// Get parameters
$patternType = isset($_GET['pattern']) ? $_GET['pattern'] : 'savi';
$customerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
$itemId = isset($_GET['item_id']) ? $_GET['item_id'] : '';
$price = isset($_GET['price']) ? floatval($_GET['price']) : 0;

// Fetch item title from database
$itemTitle = __('pattern_preview.page_title');
if ($itemId) {
    try {
        global $pdo;
        $itemStmt = $pdo->prepare("SELECT title FROM pattern_making_portfolio WHERE id = ?");
        $itemStmt->execute([$itemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $itemTitle = $item['title'];
        }
    } catch (Exception $e) {
        // Ignore error, use default title
    }
}

// Validate customer belongs to this user
$customerName = 'Customer';
if ($customerId && $customerId !== 'self') {
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT customer_name FROM customers WHERE id = ? AND boutique_user_id = ?");
        $stmt->execute([$customerId, $currentUser['id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer) {
            $customerName = $customer['customer_name'];
        }
    } catch (Exception $e) {
        // Ignore error, use default name
    }
} elseif ($customerId === 'self') {
    $customerName = $currentUser['username'];
}

// Get pattern file paths from database based on item_id
$patternFile = null;
$patternTitle = 'Pattern';
$pdfDownloadFile = null;
$svgDownloadFile = null;
$patternNotFoundReason = null;

if ($itemId) {
    try {
        global $pdo;
        $patternStmt = $pdo->prepare("SELECT title, preview_file, pdf_download_file, svg_download_file, status FROM pattern_making_portfolio WHERE id = ?");
        $patternStmt->execute([$itemId]);
        $patternData = $patternStmt->fetch(PDO::FETCH_ASSOC);

        if ($patternData) {
            if ($patternData['status'] !== 'active') {
                $patternNotFoundReason = 'inactive';
            } else {
                $patternFile = $patternData['preview_file'];
                $patternTitle = $patternData['title'] ?? 'Pattern';
                $pdfDownloadFile = $patternData['pdf_download_file'];
                $svgDownloadFile = $patternData['svg_download_file'];
            }
        } else {
            $patternNotFoundReason = 'not_found';
        }
    } catch (Exception $e) {
        $patternNotFoundReason = 'db_error';
    }
} else {
    $patternNotFoundReason = 'no_item_id';
}

// No fallback - pattern file must come from database
// If database doesn't have preview_file set, the pattern won't load

// Page setup for header
$pageTitle = $patternTitle . ' Preview';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = 'dashboard.php';
$navBase = '../';
$isLoggedIn = true;
$userType = $currentUser['user_type'];
$activePage = 'pattern-studio';

// Clear session variables to ensure fresh data from customer_id
unset($_SESSION['measurements']);  // Main measurements array used by patternConfig.php
unset($_SESSION['measurement_id']);
unset($_SESSION['cust']);
unset($_SESSION['shoulder']);
unset($_SESSION['fshoulder']);
unset($_SESSION['blength']);
unset($_SESSION['flength']);
unset($_SESSION['chest']);
unset($_SESSION['waist']);
unset($_SESSION['bust']);
unset($_SESSION['apex']);
unset($_SESSION['fndepth']);
unset($_SESSION['bnDepth']);
unset($_SESSION['slength']);
unset($_SESSION['saround']);
unset($_SESSION['sopen']);
unset($_SESSION['armhole']);

// Set customer_id for the pattern file to use
$_GET['customer_id'] = $customerId;

// Set default mode to 'print' when viewing from pattern-preview (unless explicitly set)
if (!isset($_GET['mode'])) {
    $_GET['mode'] = 'print';
}

// Additional styles for the page
$additionalStyles = '
    .pattern-preview-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    .pattern-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #E2E8F0;
    }
    .pattern-preview-header .pattern-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
        color: #1A202C;
        flex: 1;
        text-align: center;
    }
    .pattern-preview-header .customer-info {
        font-size: 0.9rem;
        color: #718096;
    }
    .pattern-preview-header .customer-info strong {
        color: #2D3748;
    }
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #F7FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        color: #4A5568;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .back-button:hover {
        background: #EDF2F7;
        color: #2D3748;
    }
    .pattern-content-wrapper {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .pattern-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: linear-gradient(135deg, #F7FAFC, #EDF2F7);
        border-radius: 12px;
        margin-top: 1.5rem;
    }
    .pattern-actions .price-info {
        font-size: 1.25rem;
        color: #2D3748;
    }
    .pattern-actions .price-info .price {
        font-weight: 700;
        color: #065F46;
    }
    .pattern-actions .price-info .price.free {
        color: #DC2626;
    }
    .btn-buy-pattern {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #1a365d, #2c5282);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-buy-pattern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(26, 54, 93, 0.4);
    }
    /* Constrain pattern content within preview container */
    .pattern-content-wrapper {
        overflow: hidden;
    }
    .pattern-content-wrapper .pattern-grid {
        max-width: 100%;
    }
    .pattern-content-wrapper .pattern-card-body svg,
    .pattern-content-wrapper .svg-container svg {
        max-width: 100%;
        height: auto;
    }
    .pattern-content-wrapper .pattern-card-body {
        overflow: auto;
    }
    .pattern-content-wrapper .pattern-container {
        max-width: 100%;
        box-shadow: none;
        padding: 10px;
    }
    .pattern-error {
        text-align: center;
        padding: 3rem;
        color: #718096;
    }
    .pattern-error i {
        width: 64px;
        height: 64px;
        color: #CBD5E0;
        margin-bottom: 1rem;
    }
    /* Disclaimer Modal Styles */
    .disclaimer-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .disclaimer-modal-overlay.active {
        display: flex;
    }
    .disclaimer-modal {
        background: white;
        border-radius: 16px;
        max-width: 600px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .disclaimer-modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .disclaimer-modal-header .icon-warning {
        width: 40px;
        height: 40px;
        background: #FEE2E2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #DC2626;
    }
    .disclaimer-modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        color: #DC2626;
    }
    .disclaimer-modal-body {
        padding: 1.5rem;
    }
    .disclaimer-modal-body p {
        margin: 0 0 1rem 0;
        color: #4A5568;
        line-height: 1.6;
    }
    .disclaimer-modal-body p:last-child {
        margin-bottom: 0;
    }
    .disclaimer-modal-body .highlight {
        background: #FEF3C7;
        border-left: 4px solid #D97706;
        padding: 1rem;
        border-radius: 0 8px 8px 0;
        margin: 1rem 0;
    }
    .disclaimer-modal-body .highlight p {
        margin: 0;
        font-weight: 500;
        color: #92400E;
        font-size: 0.85rem;
    }
    .disclaimer-modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #E2E8F0;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    .btn-modal-cancel {
        padding: 0.75rem 1.25rem;
        background: #F7FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        color: #4A5568;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-modal-cancel:hover {
        background: #EDF2F7;
    }
    .btn-modal-proceed {
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #DC2626, #B91C1C);
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-modal-proceed:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }
';

include __DIR__ . '/../includes/header.php';
?>

<main style="padding-top: 80px; min-height: calc(100vh - 200px);">
    <div class="pattern-preview-container">
        <!-- Header with back button, title and customer info -->
        <div class="pattern-preview-header">
            <div>
                <a href="dashboard.php<?php echo $customerId ? '?customer_id=' . urlencode($customerId) : ''; ?>" class="back-button">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    <?php _e('pattern_preview.back_to_dashboard'); ?>
                </a>
            </div>
            <h1 class="pattern-title"><?php echo htmlspecialchars($itemTitle); ?> <span style="color: <?php echo $price > 0 ? '#065F46' : '#DC2626'; ?>; font-size: 0.7em;">(<?php echo $price > 0 ? 'Rs. ' . number_format($price, 0) : __('pattern_preview.free'); ?>)</span></h1>
            <div class="customer-info">
                <?php _e('pattern_preview.customer'); ?> <strong><?php echo htmlspecialchars($customerName); ?></strong>
            </div>
        </div>

        <!-- Pattern Content -->
        <div class="pattern-content-wrapper">
            <?php
            // Check if we have customer_id to load measurements
            $hasMeasurementSource = !empty($customerId) || !empty($_GET['measurement_id']);

            // Resolve pattern file path - handle both relative and absolute paths
            $resolvedPatternPath = null;
            if ($patternFile) {
                // If path starts with /, it's relative to document root
                if (strpos($patternFile, '/') === 0) {
                    $resolvedPatternPath = $_SERVER['DOCUMENT_ROOT'] . $patternFile;
                } elseif (strpos($patternFile, '../') === 0) {
                    // Path starts with ../ - resolve relative to current directory
                    $resolvedPatternPath = realpath(__DIR__ . '/' . $patternFile);
                    // If realpath fails, try without it
                    if (!$resolvedPatternPath) {
                        $resolvedPatternPath = __DIR__ . '/' . $patternFile;
                    }
                } else {
                    // Regular relative path
                    $resolvedPatternPath = __DIR__ . '/' . $patternFile;
                }
            }
            ?>
            <?php if (!$hasMeasurementSource): ?>
                <div class="pattern-error">
                    <i data-lucide="alert-circle"></i>
                    <h3>Customer Not Selected</h3>
                    <p>No customer selected. Please select a customer from the dashboard before viewing this pattern.</p>
                    <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">
                        <a href="dashboard.php" style="color: #3B82F6;">← Go to Dashboard</a>
                    </p>
                </div>
            <?php elseif ($resolvedPatternPath && file_exists($resolvedPatternPath)): ?>
                <?php include $resolvedPatternPath; ?>
            <?php else: ?>
                <div class="pattern-error">
                    <i data-lucide="alert-circle"></i>
                    <h3><?php _e('pattern_preview.pattern_not_found'); ?></h3>
                    <?php if ($patternNotFoundReason === 'no_item_id'): ?>
                        <p>No pattern item ID provided.</p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">Please select a pattern from the dashboard.</p>
                    <?php elseif ($patternNotFoundReason === 'not_found'): ?>
                        <p>Pattern item (ID: <?php echo htmlspecialchars($itemId); ?>) not found in the database.</p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">The pattern may have been deleted or the ID is incorrect.</p>
                    <?php elseif ($patternNotFoundReason === 'inactive'): ?>
                        <p>This pattern is currently inactive.</p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">Please contact the administrator to activate this pattern.</p>
                    <?php elseif ($patternNotFoundReason === 'db_error'): ?>
                        <p>Database error occurred while loading the pattern.</p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">Please try again later or contact support.</p>
                    <?php elseif (empty($patternFile)): ?>
                        <p>Pattern preview file is not configured in the admin panel.</p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">Please set the "Preview File" path in Admin → Pattern Catalog for item "<?php echo htmlspecialchars($patternTitle); ?>".</p>
                    <?php elseif ($resolvedPatternPath): ?>
                        <p>Pattern file not found at: <code style="background: #F1F5F9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($patternFile); ?></code></p>
                        <p style="font-size: 0.85rem; color: #94A3B8; margin-top: 0.5rem;">Resolved path: <?php echo htmlspecialchars($resolvedPatternPath); ?></p>
                    <?php else: ?>
                        <p><?php echo str_replace('{type}', htmlspecialchars($patternType), __('pattern_preview.pattern_not_available')); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Bar -->
        <div class="pattern-actions">
            <div class="price-info">
                <?php if ($price > 0): ?>
                    <?php _e('pattern_preview.price'); ?> <span class="price">Rs. <?php echo number_format($price, 0); ?></span>
                <?php else: ?>
                    <span class="price free"><?php _e('pattern_preview.free'); ?></span>
                <?php endif; ?>
            </div>

            <?php
            // Build the action URLs (paper size is selected on pattern-download.php for PDF format)
            $baseParams = '?item_id=' . urlencode($itemId)
                        . '&customer_id=' . urlencode($customerId)
                        . '&pattern=' . urlencode($patternType);

            if ($price > 0) {
                // Paid pattern - go to payment page first
                $pdfUrl = 'pattern-payment.php' . $baseParams . '&format=pdf';
                $svgUrl = 'pattern-payment.php' . $baseParams . '&format=svg';
                $projectorPdfUrl = 'pattern-payment.php' . $baseParams . '&format=projector';
                $projectorPngUrl = 'pattern-payment.php' . $baseParams . '&format=projector_png';
                $measurementGuideUrl = 'pattern-payment.php' . $baseParams . '&format=measurement_guide';
                $pdfButtonText = __('pattern_preview.buttons.buy_pdf');
                $svgButtonText = __('pattern_preview.buttons.buy_svg');
                $projectorPdfButtonText = __('pattern_preview.buttons.buy_projector_pdf');
                $projectorPngButtonText = __('pattern_preview.buttons.buy_projector_png');
                $buttonIcon = 'shopping-cart';
            } else {
                // Free pattern - go directly to download
                $pdfUrl = 'pattern-download.php' . $baseParams . '&format=pdf';
                $svgUrl = 'pattern-download.php' . $baseParams . '&format=svg';
                $projectorPdfUrl = 'pattern-download.php' . $baseParams . '&format=projector';
                $projectorPngUrl = 'pattern-download.php' . $baseParams . '&format=projector_png';
                $measurementGuideUrl = 'pattern-download.php' . $baseParams . '&format=measurement_guide';
                $pdfButtonText = __('pattern_preview.buttons.download_pdf');
                $svgButtonText = __('pattern_preview.buttons.download_svg');
                $projectorPdfButtonText = __('pattern_preview.buttons.download_projector_pdf');
                $projectorPngButtonText = __('pattern_preview.buttons.download_projector_png');
                $buttonIcon = 'download';
            }
            ?>

            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <!-- Download Label -->
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #374151; font-weight: 600; font-size: 0.95rem;">
                    <i data-lucide="<?php echo $buttonIcon; ?>" style="width: 20px; height: 20px;"></i>
                    <span><?php echo ($buttonIcon === 'download') ? __('pattern_preview.buttons.download_label') : __('pattern_preview.buttons.buy_label'); ?>:</span>
                </div>

                <!-- PDF -->
                <button type="button" class="btn-buy-pattern" onclick="showDisclaimerModal('pdf')">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                    PDF
                </button>

                <!-- SVG -->
                <button type="button" class="btn-buy-pattern" style="background: linear-gradient(135deg, #667eea, #764ba2);" onclick="showDisclaimerModal('svg')">
                    <i data-lucide="code" style="width: 18px; height: 18px;"></i>
                    SVG (ZIP)
                </button>

                <!-- Projector PDF -->
                <button type="button" class="btn-buy-pattern" style="background: linear-gradient(135deg, #059669, #047857);" onclick="showDisclaimerModal('projector_pdf')">
                    <i data-lucide="projector" style="width: 18px; height: 18px;"></i>
                    Projector PDF
                </button>

                <!-- Projector PNG -->
                <button type="button" class="btn-buy-pattern" style="background: linear-gradient(135deg, #0891B2, #0E7490);" onclick="showDisclaimerModal('projector_png')">
                    <i data-lucide="image" style="width: 18px; height: 18px;"></i>
                    Projector PNG
                </button>

                <!-- Measurement Guide -->
                <button type="button" class="btn-buy-pattern" style="background: linear-gradient(135deg, #D97706, #B45309);" onclick="showDisclaimerModal('measurement_guide')">
                    <i data-lucide="ruler" style="width: 18px; height: 18px;"></i>
                    <?php _e('pattern_preview.buttons.measurement_guide'); ?>
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Disclaimer Modal -->
<div id="disclaimerModal" class="disclaimer-modal-overlay">
    <div class="disclaimer-modal">
        <div class="disclaimer-modal-header">
            <div class="icon-warning">
                <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
            </div>
            <h3><?php _e('pattern_preview.disclaimer.title'); ?></h3>
        </div>
        <div class="disclaimer-modal-body">
            <p style="white-space: nowrap;"><?php _e('pattern_preview.disclaimer.intro'); ?></p>
            <div class="highlight">
                <p><?php _e('pattern_preview.disclaimer.warning'); ?></p>
            </div>
            <p><strong><?php _e('pattern_preview.disclaimer.before_cutting'); ?></strong></p>
            <ul style="margin: 0.5rem 0 0 1.25rem; color: #4A5568; line-height: 1.8; font-size: 0.85rem;">
                <li><?php _e('pattern_preview.disclaimer.check_1'); ?></li>
                <li><?php _e('pattern_preview.disclaimer.check_2'); ?></li>
                <li><?php _e('pattern_preview.disclaimer.check_3'); ?></li>
                <li><?php _e('pattern_preview.disclaimer.check_4'); ?></li>
                <li><?php _e('pattern_preview.disclaimer.check_5'); ?></li>
            </ul>
        </div>
        <div class="disclaimer-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="hideDisclaimerModal()"><?php _e('pattern_preview.disclaimer.cancel_btn'); ?></button>
            <button type="button" class="btn-modal-proceed" id="proceedBtn">
                <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                <?php _e('pattern_preview.disclaimer.proceed_btn'); ?>
            </button>
        </div>
    </div>
</div>

<script>
    // Store URLs for the modal
    const downloadUrls = {
        pdf: '<?php echo $pdfUrl; ?>',
        svg: '<?php echo $svgUrl; ?>',
        projector_pdf: '<?php echo $projectorPdfUrl; ?>',
        projector_png: '<?php echo $projectorPngUrl; ?>',
        measurement_guide: '<?php echo $measurementGuideUrl; ?>'
    };

    let selectedFormat = '';

    function showDisclaimerModal(format) {
        selectedFormat = format;
        document.getElementById('disclaimerModal').classList.add('active');
        // Re-render icons in modal
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function hideDisclaimerModal() {
        document.getElementById('disclaimerModal').classList.remove('active');
        selectedFormat = '';
    }

    // Handle proceed button click
    document.getElementById('proceedBtn').addEventListener('click', function() {
        if (selectedFormat && downloadUrls[selectedFormat]) {
            window.location.href = downloadUrls[selectedFormat];
        }
    });

    // Close modal when clicking outside
    document.getElementById('disclaimerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDisclaimerModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDisclaimerModal();
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
