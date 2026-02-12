<?php
/**
 * Pattern Download Page
 * Shows download link after free pattern selection or successful payment
 */

session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

// Require login
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Check if user is individual or boutique (both can download patterns)
$isIndividual = $currentUser['user_type'] === 'individual';
$isBoutique = $currentUser['user_type'] === 'boutique';

if (!$isIndividual && !$isBoutique) {
    header('Location: dashboard.php');
    exit;
}

// Get parameters from URL
$itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
$customerId = isset($_GET['customer_id']) ? htmlspecialchars($_GET['customer_id']) : '';
$paperSize = isset($_GET['paper']) ? htmlspecialchars($_GET['paper']) : 'A3';
$paid = isset($_GET['paid']) ? $_GET['paid'] : '0';
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'pdf';
$patternType = isset($_GET['pattern']) ? $_GET['pattern'] : 'savi';

// Fallback: Default download files for all patterns (unified generators)
// Note: All patterns use the same generic generators - pattern type passed via URL parameter
$defaultPdfGenerator = '../patterns/pdfGenerator.php';
$defaultSvgGenerator = '../patterns/svgGenerator.php';

// These will be populated from DB or fallback
$pdfDownloadFile = null;
$svgDownloadFile = null;

if (!$itemId || !$customerId) {
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

    // Get download file paths from database (if available)
    if (!empty($item['pdf_download_file'])) {
        $pdfDownloadFile = $item['pdf_download_file'];
    }
    if (!empty($item['svg_download_file'])) {
        $svgDownloadFile = $item['svg_download_file'];
    }
} catch (Exception $e) {
    header('Location: dashboard.php');
    exit;
}

// Fallback to unified generators if DB columns are empty
if (!$pdfDownloadFile) {
    $pdfDownloadFile = $defaultPdfGenerator;
}
if (!$svgDownloadFile) {
    $svgDownloadFile = $defaultSvgGenerator;
}

// Fetch customer info and measurement ID
$customerName = 'Self Measurements';
$measurementId = 0;

try {
    if ($customerId === 'self') {
        // Self measurements
        $stmt = $pdo->prepare("
            SELECT m.id, u.username as customer_name
            FROM measurements m
            JOIN users u ON m.user_id = u.id
            WHERE m.user_id = ? AND m.measurement_of = 'self'
            ORDER BY m.created_at DESC LIMIT 1
        ");
        $stmt->execute([$currentUser['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $measurementId = $result['id'];
            $customerName = $result['customer_name'] . ' (Self)';
        }
    } else {
        // Customer measurements
        $stmt = $pdo->prepare("
            SELECT m.id, c.customer_name
            FROM measurements m
            JOIN customers c ON m.customer_id = c.id
            WHERE m.customer_id = ? AND c.boutique_user_id = ?
            ORDER BY m.created_at DESC LIMIT 1
        ");
        $stmt->execute([$customerId, $currentUser['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $measurementId = $result['id'];
            $customerName = $result['customer_name'];
        }
    }
} catch (Exception $e) {
    // Use defaults
}

// Check if we have valid measurement
if ($measurementId <= 0) {
    $error = __('pattern_download.no_measurements');
}
?>
<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>" dir="<?php echo Lang::getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('pattern_download.page_title'); ?> <?php echo ($format === 'svg') ? 'SVG' : (($format === 'projector') ? 'Projector PDF' : (($format === 'projector_png') ? 'Projector PNG' : (($format === 'measurement_guide') ? 'Measurement Guide' : 'PDF'))); ?> - <?php echo htmlspecialchars($item['title']); ?> - CuttingMaster</title>

    <!-- Google Fonts (including Noto Sans for Telugu/Hindi) -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&family=Noto+Sans:wght@300;400;500;600&family=Noto+Sans+Telugu:wght@300;400;500;600&family=Noto+Sans+Devanagari:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        /* Mobile Navigation Styles */
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

        body.nav-open #hamburgerBtn span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        body.nav-open #hamburgerBtn span:nth-child(2) {
            opacity: 0;
        }
        body.nav-open #hamburgerBtn span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

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
        }

        body.nav-open {
            overflow: hidden !important;
            position: fixed !important;
            width: 100% !important;
        }

        .download-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .download-header {
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

        .download-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .download-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            text-align: center;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #48BB78, #38A169);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .success-icon svg {
            color: white;
        }

        .download-card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0 0 0.5rem 0;
        }

        .download-card-subtitle {
            color: #718096;
            font-size: 0.875rem;
            margin: 0;
        }

        /* 2-Column Layout */
        .download-content-wrapper {
            display: flex;
            gap: 0;
        }

        .download-left-col {
            flex: 1;
            min-width: 0;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            border-right: 1px solid #E2E8F0;
        }

        .download-preview-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .download-right-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .download-item-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1.5rem;
            background: #F7FAFC;
            border-bottom: 1px solid #E2E8F0;
        }

        .download-item-image {
            display: none; /* Hidden in new layout, using large preview instead */
        }

        .download-item-details h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            color: #2D3748;
            font-weight: 600;
        }

        .download-item-details p {
            margin: 0 0 0.25rem 0;
            font-size: 0.875rem;
            color: #718096;
        }

        /* Responsive: stack on mobile */
        @media (max-width: 768px) {
            .download-content-wrapper {
                flex-direction: column;
            }

            .download-left-col {
                border-right: none;
                border-bottom: 1px solid #E2E8F0;
                padding: 1rem;
            }

            .download-preview-image {
                max-height: 280px;
            }

            .download-container {
                padding: 1rem;
            }
        }

        .download-options {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .download-options h4 {
            margin: 0 0 1rem 0;
            font-size: 0.875rem;
            color: #4A5568;
        }

        /* Adjust error message for 2-column layout */
        .download-right-col .error-message {
            margin: 1.5rem;
        }

        .paper-size-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #EDF2F7;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .paper-size-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .paper-size-details h5 {
            margin: 0;
            font-size: 0.875rem;
            color: #2D3748;
        }

        .paper-size-details p {
            margin: 0;
            font-size: 0.75rem;
            color: #718096;
        }

        .btn-download {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-download svg {
            width: 20px;
            height: 20px;
        }

        .download-footer {
            padding: 1.5rem;
            border-top: 1px solid #E2E8F0;
            text-align: center;
        }

        .download-footer p {
            margin: 0 0 1rem 0;
            font-size: 0.75rem;
            color: #718096;
        }

        .btn-back-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #F7FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            color: #4A5568;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-back-dashboard:hover {
            background: #EDF2F7;
            color: #2D3748;
        }

        .error-message {
            background: #FED7D7;
            border: 1px solid #FC8181;
            color: #C53030;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem;
            text-align: center;
        }

        .error-message p {
            margin: 0;
        }

        /* Download Success Modal */
        .download-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .download-modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .download-modal {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
            position: relative;
        }

        @keyframes slideUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .download-modal-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .download-modal-icon svg {
            color: white;
        }

        .download-modal h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
        }

        .download-modal p {
            margin: 0 0 1.5rem 0;
            color: #64748b;
            text-align: center;
            line-height: 1.6;
        }

        .download-modal-instructions {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .download-modal-instructions h4 {
            margin: 0 0 1rem 0;
            font-size: 0.875rem;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .download-modal-step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .download-modal-step:last-child {
            margin-bottom: 0;
        }

        .download-modal-step-number {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .download-modal-step-text {
            flex: 1;
            font-size: 0.875rem;
            color: #475569;
            line-height: 1.5;
        }

        .download-modal-step-text strong {
            color: #1e293b;
        }

        .download-modal-close {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .download-modal-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Paper Size Selector for PDF */
        .paper-size-selector-wrapper {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #F8FAFC;
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .paper-size-selector-wrapper.error {
            background: #FEF2F2;
            border-color: #EF4444;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .paper-size-selector-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #1E293B;
            font-size: 0.9rem;
        }

        .paper-size-selector-label .required {
            color: #EF4444;
        }

        .paper-size-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .paper-size-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .paper-size-selector-wrapper.error .paper-size-select {
            border-color: #EF4444;
        }

        .paper-size-error-msg {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #FEE2E2;
            border-radius: 6px;
            color: #DC2626;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .paper-size-selector-wrapper.error .paper-size-error-msg {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-download.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Projector Download Modal */
        .projector-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .projector-modal-overlay.show {
            display: flex;
        }

        .projector-modal {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
            display: flex;
            flex-direction: column;
        }

        .projector-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        }

        .projector-modal-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #065F46;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .projector-modal-header p {
            margin: 0;
            font-size: 0.85rem;
            color: #047857;
        }

        .projector-modal-body {
            padding: 1rem;
            overflow-y: auto;
            flex: 1;
        }

        .projector-pattern-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .projector-pattern-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .projector-pattern-item:hover {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }

        .projector-pattern-item.scale-item {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            border-color: #F59E0B;
        }

        .projector-pattern-item.scale-item:hover {
            background: linear-gradient(135deg, #FDE68A 0%, #FCD34D 100%);
        }

        .projector-pattern-icon {
            width: 44px;
            height: 44px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .projector-pattern-item.scale-item .projector-pattern-icon {
            background: #000;
        }

        .projector-pattern-item.scale-item .projector-pattern-icon svg {
            color: white;
        }

        .projector-pattern-info {
            flex: 1;
            min-width: 0;
        }

        .projector-pattern-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1E293B;
        }

        .projector-pattern-info p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748B;
        }

        .projector-pattern-item.scale-item .projector-pattern-info p {
            color: #92400E;
        }

        .projector-download-btn {
            padding: 0.6rem 1rem;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .projector-download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(5, 150, 105, 0.3);
            color: white;
        }

        .projector-download-btn svg {
            width: 16px;
            height: 16px;
        }

        .projector-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #E2E8F0;
            background: #F8FAFC;
        }

        .projector-modal-close {
            width: 100%;
            padding: 0.75rem;
            background: #E2E8F0;
            color: #475569;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .projector-modal-close:hover {
            background: #CBD5E1;
            color: #1E293B;
        }

        .projector-loading {
            text-align: center;
            padding: 2rem;
            color: #64748B;
        }

        .projector-loading svg {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .projector-tip {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #FEF3C7;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.8rem;
            color: #92400E;
        }

        .projector-tip svg {
            flex-shrink: 0;
            margin-top: 1px;
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

            <!-- Hamburger Button -->
            <button type="button" id="hamburgerBtn" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-links" id="navLinks">
                <a href="pattern-studio.php" class="nav-link"><?php _e('dashboard.nav.pattern_studio'); ?></a>
                <a href="wholesale-catalog.php" class="nav-link"><?php _e('dashboard.nav.wholesale'); ?></a>
                <a href="contact-us.php" class="nav-link"><?php _e('dashboard.nav.contact'); ?></a>
                <a href="dashboard.php" class="nav-link active-nav-link"><?php _e('dashboard.nav.your_account'); ?></a>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border"><?php _e('dashboard.nav.logout'); ?></a>

                <!-- Language Switcher -->
                <?php include __DIR__ . '/../includes/lang-switcher.php'; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="hero auth-section auth-section-padded" style="align-items: flex-start; padding-top: calc(4.5rem + 40px);">
        <div class="download-container">
            <div class="download-header">
                <a href="dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    <?php _e('pattern_download.back_to_dashboard'); ?>
                </a>
            </div>

            <div class="download-card">
                <div class="download-card-header">
                    <div class="success-icon">
                        <i data-lucide="check" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h2 class="download-card-title"><?php _e('pattern_download.pattern_ready'); ?></h2>
                    <p class="download-card-subtitle"><?php
                        if ($format === 'svg') {
                            _e('pattern_download.click_to_download_svg');
                        } elseif ($format === 'projector') {
                            _e('pattern_download.click_to_download_projector');
                        } elseif ($format === 'projector_png') {
                            _e('pattern_download.click_to_download_projector_png');
                        } elseif ($format === 'measurement_guide') {
                            _e('pattern_download.click_to_download_measurement_guide');
                        } else {
                            _e('pattern_download.click_to_download_pdf');
                        }
                    ?></p>
                </div>

                <!-- 2-Column Layout -->
                <div class="download-content-wrapper">
                    <!-- Left Column: Large Pattern Image -->
                    <div class="download-left-col">
                        <?php if ($item['image']): ?>
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="download-preview-image">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #E2E8F0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="file-text" style="width: 48px; height: 48px; color: #94A3B8;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column: Info & Download Options -->
                    <div class="download-right-col">
                        <div class="download-item-info">
                            <div class="download-item-details">
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p><?php _e('pattern_download.customer'); ?> <?php echo htmlspecialchars($customerName); ?></p>
                                <p><?php _e('pattern_download.pattern_id'); ?> #<?php echo $measurementId; ?></p>
                            </div>
                        </div>

                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php else: ?>
                    <div class="download-options">
                        <?php
                        // Unified PDF generator handles both single-page (A3) and tiled (A4/Letter) modes
                        $needsTiling = in_array(strtoupper($paperSize), ['A4', 'LETTER']);

                        // Use download files from DB or fallback (already set earlier in the code)
                        // Pass type parameter for dynamic pattern handling
                        $pdfUrl = $pdfDownloadFile . '?id=' . $measurementId . '&paper=' . urlencode($paperSize) . '&type=' . urlencode($patternType);
                        $svgUrl = $svgDownloadFile . '?id=' . $measurementId . '&type=' . urlencode($patternType);
                        $projectorUrl = '../patterns/projectorPDF.php?measurement_id=' . $measurementId . '&type=' . urlencode($patternType);
                        $projectorPngUrl = '../patterns/projectorPNG.php?measurement_id=' . $measurementId . '&type=' . urlencode($patternType);
                        $measurementGuideUrl = '../patterns/measurementGuide.php?id=' . $measurementId . '&type=' . urlencode($item['title']);
                        $isSvgFormat = ($format === 'svg');
                        $isProjectorFormat = ($format === 'projector');
                        $isProjectorPngFormat = ($format === 'projector_png');
                        $isMeasurementGuideFormat = ($format === 'measurement_guide');
                        ?>

                        <?php if (!$isSvgFormat && !$isProjectorFormat && !$isProjectorPngFormat && !$isMeasurementGuideFormat): ?>
                        <!-- Paper Size Selector for PDF -->
                        <div id="paperSizeSelectorWrapper" class="paper-size-selector-wrapper">
                            <label class="paper-size-selector-label">
                                <i data-lucide="printer" style="width: 18px; height: 18px; color: #667eea;"></i>
                                <?php _e('pattern_download.paper_size.label'); ?> <span class="required"><?php _e('pattern_download.paper_size.required'); ?></span>
                            </label>
                            <select id="paperSizeSelect" class="paper-size-select" onchange="onPaperSizeChange()">
                                <option value=""><?php _e('pattern_download.paper_size.placeholder'); ?></option>
                                <option value="A0"><?php _e('pattern_download.paper_size.a0'); ?></option>
                                <option value="A2"><?php _e('pattern_download.paper_size.a2'); ?></option>
                                <option value="A3"><?php _e('pattern_download.paper_size.a3'); ?></option>
                                <option value="A4"><?php _e('pattern_download.paper_size.a4'); ?></option>
                                <option value="LETTER"><?php _e('pattern_download.paper_size.letter'); ?></option>
                                <option value="LEGAL"><?php _e('pattern_download.paper_size.legal'); ?></option>
                                <option value="TABLOID"><?php _e('pattern_download.paper_size.tabloid'); ?></option>
                            </select>
                            <div class="paper-size-error-msg">
                                <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
                                <?php _e('pattern_download.paper_size.error_msg'); ?>
                            </div>
                        </div>

                        <!-- PDF Format Info (shows after selection) -->
                        <div id="paperSizeInfo" class="paper-size-info" style="display: none;">
                            <div class="paper-size-icon">
                                <i data-lucide="printer" style="width: 20px; height: 20px; color: #4FD1C5;"></i>
                            </div>
                            <div class="paper-size-details">
                                <h5><?php _e('pattern_download.paper_size.label'); ?>: <span id="selectedPaperSizeName"></span></h5>
                                <p id="selectedPaperSizeDesc"></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isProjectorFormat): ?>
                        <!-- Projector PDF Format Info -->
                        <div class="paper-size-info" style="margin-bottom: 1rem;">
                            <div class="paper-size-icon" style="background: #D1FAE5;">
                                <i data-lucide="projector" style="width: 20px; height: 20px; color: #059669;"></i>
                            </div>
                            <div class="paper-size-details">
                                <h5><?php _e('pattern_download.projector_pdf.title'); ?></h5>
                                <p style="margin-bottom: 0.5rem;"><?php _e('pattern_download.projector_pdf.description'); ?></p>
                                <p style="font-size: 0.75rem; color: #64748B; margin: 0;"><?php _e('pattern_download.projector_pdf.note'); ?></p>
                            </div>
                        </div>

                        <button type="button" class="btn-download" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);" onclick="showProjectorModal()">
                            <i data-lucide="download"></i>
                            <?php _e('pattern_download.buttons.download_projector_pdf'); ?>
                        </button>

                        <div style="margin-top: 1rem; padding: 1rem; background: #D1FAE5; border-radius: 8px; border: 1px solid #10B981;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 18px; height: 18px; color: #059669; flex-shrink: 0; margin-top: 2px;"></i>
                                <div style="font-size: 0.8rem; color: #065F46;">
                                    <?php _e('pattern_download.projector_pdf.info'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isProjectorPngFormat): ?>
                        <!-- Projector PNG Format Info -->
                        <div class="paper-size-info" style="margin-bottom: 1rem;">
                            <div class="paper-size-icon" style="background: #CFFAFE;">
                                <i data-lucide="image" style="width: 20px; height: 20px; color: #0891B2;"></i>
                            </div>
                            <div class="paper-size-details">
                                <h5><?php _e('pattern_download.projector_png.title'); ?></h5>
                                <p style="margin-bottom: 0.5rem;"><?php _e('pattern_download.projector_png.description'); ?></p>
                                <p style="font-size: 0.75rem; color: #64748B; margin: 0;"><?php _e('pattern_download.projector_png.note'); ?></p>
                            </div>
                        </div>

                        <button type="button" class="btn-download" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);" onclick="showProjectorPngModal()">
                            <i data-lucide="download"></i>
                            <?php _e('pattern_download.buttons.download_projector_png'); ?>
                        </button>

                        <div style="margin-top: 1rem; padding: 1rem; background: #CFFAFE; border-radius: 8px; border: 1px solid #22D3EE;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 18px; height: 18px; color: #0891B2; flex-shrink: 0; margin-top: 2px;"></i>
                                <div style="font-size: 0.8rem; color: #164E63;">
                                    <?php _e('pattern_download.projector_png.info'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isMeasurementGuideFormat): ?>
                        <!-- Measurement Guide Format Info -->
                        <div class="paper-size-info" style="margin-bottom: 1rem;">
                            <div class="paper-size-icon" style="background: #FEF3C7;">
                                <i data-lucide="ruler" style="width: 20px; height: 20px; color: #D97706;"></i>
                            </div>
                            <div class="paper-size-details">
                                <h5><?php _e('pattern_download.measurement_guide.title'); ?></h5>
                                <p style="margin-bottom: 0.5rem;"><?php _e('pattern_download.measurement_guide.description'); ?></p>
                                <p style="font-size: 0.75rem; color: #64748B; margin: 0;"><?php _e('pattern_download.measurement_guide.note'); ?></p>
                            </div>
                        </div>

                        <a href="<?php echo $measurementGuideUrl; ?>" class="btn-download" style="background: linear-gradient(135deg, #D97706 0%, #B45309 100%);">
                            <i data-lucide="download"></i>
                            <?php _e('pattern_download.buttons.download_measurement_guide'); ?>
                        </a>

                        <div style="margin-top: 1rem; padding: 1rem; background: #FEF3C7; border-radius: 8px; border: 1px solid #F59E0B;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 18px; height: 18px; color: #D97706; flex-shrink: 0; margin-top: 2px;"></i>
                                <div style="font-size: 0.8rem; color: #92400E;">
                                    <?php _e('pattern_download.measurement_guide.info'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isSvgFormat): ?>
                        <!-- SVG Download -->
                        <div class="paper-size-info" style="margin-bottom: 1rem;">
                            <div class="paper-size-icon" style="background: #E0F2FE;">
                                <i data-lucide="file-code" style="width: 20px; height: 20px; color: #0284C7;"></i>
                            </div>
                            <div class="paper-size-details">
                                <h5><?php _e('pattern_download.svg_format.title'); ?></h5>
                                <p style="margin-bottom: 0.5rem;"><?php _e('pattern_download.svg_format.description'); ?></p>
                                <p style="font-size: 0.75rem; color: #64748B; margin: 0;"><?php _e('pattern_download.svg_format.note'); ?></p>
                            </div>
                        </div>

                        <a href="<?php echo $svgUrl; ?>" class="btn-download">
                            <i data-lucide="download"></i>
                            <?php _e('pattern_download.buttons.download_svg'); ?>
                        </a>
                        <?php elseif (!$isProjectorFormat && !$isProjectorPngFormat && !$isMeasurementGuideFormat): ?>
                        <!-- PDF Download -->
                        <a id="pdfDownloadBtn" href="#" class="btn-download disabled" onclick="return handlePdfDownload(event)">
                            <i data-lucide="download"></i>
                            <?php _e('pattern_download.buttons.download_pdf'); ?>
                        </a>

                        <?php if ($needsTiling): ?>
                        <div style="margin-top: 1rem; padding: 1rem; background: #FEF3C7; border-radius: 8px; border: 1px solid #F59E0B;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 18px; height: 18px; color: #D97706; flex-shrink: 0; margin-top: 2px;"></i>
                                <div style="font-size: 0.8rem; color: #92400E;">
                                    <strong><?php _e('pattern_download.tiled_printing.title'); ?></strong> <?php _e('pattern_download.tiled_printing.description'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                    </div><!-- /.download-right-col -->
                </div><!-- /.download-content-wrapper -->

                <div class="download-footer">
                    <p><?php _e('pattern_download.footer.message'); ?><br><?php _e('pattern_download.footer.print_scale'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Projector Download Modal -->
    <div id="projectorModal" class="projector-modal-overlay">
        <div class="projector-modal">
            <div class="projector-modal-header">
                <h3>
                    <i data-lucide="projector" style="width: 22px; height: 22px;"></i>
                    <?php _e('pattern_download.projector_modal.title'); ?>
                </h3>
                <p><?php _e('pattern_download.projector_modal.subtitle'); ?></p>
            </div>

            <div class="projector-modal-body">
                <div class="projector-tip">
                    <i data-lucide="lightbulb" style="width: 16px; height: 16px;"></i>
                    <span><strong>Tip:</strong> <?php _e('pattern_download.projector_modal.tip'); ?></span>
                </div>

                <div id="projectorPatternList" class="projector-pattern-list">
                    <div class="projector-loading">
                        <i data-lucide="loader-2" style="width: 32px; height: 32px;"></i>
                        <p><?php _e('pattern_download.projector_modal.loading'); ?></p>
                    </div>
                </div>
            </div>

            <div class="projector-modal-footer">
                <button class="projector-modal-close" onclick="closeProjectorModal()">
                    <?php _e('pattern_download.modal.close'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Projector PNG Download Modal -->
    <div id="projectorPngModal" class="projector-modal-overlay">
        <div class="projector-modal">
            <div class="projector-modal-header" style="background: linear-gradient(135deg, #CFFAFE 0%, #A5F3FC 100%);">
                <h3 style="color: #0E7490;">
                    <i data-lucide="image" style="width: 22px; height: 22px;"></i>
                    <?php _e('pattern_download.projector_png_modal.title'); ?>
                </h3>
                <p style="color: #0891B2;"><?php _e('pattern_download.projector_png_modal.subtitle'); ?></p>
            </div>

            <div class="projector-modal-body">
                <div class="projector-tip" style="background: #CFFAFE; color: #164E63;">
                    <i data-lucide="lightbulb" style="width: 16px; height: 16px;"></i>
                    <span><strong>Tip:</strong> <?php _e('pattern_download.projector_modal.tip'); ?></span>
                </div>

                <div id="projectorPngPatternList" class="projector-pattern-list">
                    <div class="projector-loading">
                        <i data-lucide="loader-2" style="width: 32px; height: 32px;"></i>
                        <p><?php _e('pattern_download.projector_modal.loading'); ?></p>
                    </div>
                </div>
            </div>

            <div class="projector-modal-footer">
                <button class="projector-modal-close" onclick="closeProjectorPngModal()">
                    <?php _e('pattern_download.modal.close'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Download Success Modal -->
    <div id="downloadModal" class="download-modal-overlay">
        <div class="download-modal">
            <div class="download-modal-icon">
                <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
            </div>

            <h3><?php _e('pattern_download.modal.download_started'); ?></h3>
            <p><?php _e('pattern_download.modal.pdf_downloading'); ?></p>

            <div class="download-modal-instructions">
                <h4><?php _e('pattern_download.modal.what_next'); ?></h4>

                <div class="download-modal-step">
                    <div class="download-modal-step-number">1</div>
                    <div class="download-modal-step-text">
                        <strong><?php _e('pattern_download.modal.step1_title'); ?></strong><br>
                        <?php _e('pattern_download.modal.step1_desc'); ?>
                    </div>
                </div>

                <div class="download-modal-step">
                    <div class="download-modal-step-number">2</div>
                    <div class="download-modal-step-text">
                        <strong><?php _e('pattern_download.modal.step2_title'); ?></strong><br>
                        <?php _e('pattern_download.modal.step2_desc'); ?>
                    </div>
                </div>

                <div class="download-modal-step">
                    <div class="download-modal-step-number">3</div>
                    <div class="download-modal-step-text">
                        <strong><?php _e('pattern_download.modal.step3_title'); ?></strong><br>
                        <?php _e('pattern_download.modal.step3_desc'); ?>
                    </div>
                </div>
            </div>

            <button class="download-modal-close" onclick="closeDownloadModal()">
                <?php _e('pattern_download.modal.got_it'); ?>
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const projectorBaseUrl = '<?php echo $projectorUrl; ?>';
        const projectorPngBaseUrl = '<?php echo $projectorPngUrl; ?>';
        const measurementId = <?php echo $measurementId; ?>;
        const pdfBaseUrl = '<?php echo $pdfDownloadFile; ?>?id=<?php echo $measurementId; ?>';

        // Paper size descriptions
        const paperSizeDescriptions = {
            'A0': '<?php echo addslashes(__('pattern_download.paper_size.desc_a0')); ?>',
            'A2': '<?php echo addslashes(__('pattern_download.paper_size.desc_a2')); ?>',
            'A3': '<?php echo addslashes(__('pattern_download.paper_size.desc_a3')); ?>',
            'A4': '<?php echo addslashes(__('pattern_download.paper_size.desc_a4')); ?>',
            'LETTER': '<?php echo addslashes(__('pattern_download.paper_size.desc_letter')); ?>',
            'LEGAL': '<?php echo addslashes(__('pattern_download.paper_size.desc_legal')); ?>',
            'TABLOID': '<?php echo addslashes(__('pattern_download.paper_size.desc_tabloid')); ?>'
        };

        // Translation strings for JavaScript
        const translations = {
            downloadPdf: '<?php echo addslashes(__('pattern_download.buttons.download_pdf')); ?>',
            download: '<?php echo addslashes(__('pattern_download.projector_modal.download')); ?>',
            loading: '<?php echo addslashes(__('pattern_download.projector_modal.loading')); ?>',
            error: '<?php echo addslashes(__('pattern_download.projector_modal.error')); ?>',
            scaleCalibration: '<?php echo addslashes(__('pattern_download.projector_modal.scale_calibration')); ?>',
            scaleDesc: '<?php echo addslashes(__('pattern_download.projector_modal.scale_desc')); ?>',
            front: '<?php echo addslashes(__('pattern_download.projector_modal.front')); ?>',
            frontDesc: '<?php echo addslashes(__('pattern_download.projector_modal.front_desc')); ?>',
            back: '<?php echo addslashes(__('pattern_download.projector_modal.back')); ?>',
            backDesc: '<?php echo addslashes(__('pattern_download.projector_modal.back_desc')); ?>',
            patti: '<?php echo addslashes(__('pattern_download.projector_modal.patti')); ?>',
            pattiDesc: '<?php echo addslashes(__('pattern_download.projector_modal.patti_desc')); ?>',
            sleeve: '<?php echo addslashes(__('pattern_download.projector_modal.sleeve')); ?>',
            sleeveDesc: '<?php echo addslashes(__('pattern_download.projector_modal.sleeve_desc')); ?>'
        };

        // Handle paper size selection change
        function onPaperSizeChange() {
            const select = document.getElementById('paperSizeSelect');
            const wrapper = document.getElementById('paperSizeSelectorWrapper');
            const infoDiv = document.getElementById('paperSizeInfo');
            const downloadBtn = document.getElementById('pdfDownloadBtn');
            const selectedSize = select.value;

            // Remove error state
            wrapper.classList.remove('error');

            if (selectedSize) {
                // Show paper size info
                document.getElementById('selectedPaperSizeName').textContent = selectedSize;
                document.getElementById('selectedPaperSizeDesc').textContent = paperSizeDescriptions[selectedSize] || 'Standard pattern size';
                infoDiv.style.display = 'flex';

                // Enable download button
                downloadBtn.classList.remove('disabled');
                downloadBtn.innerHTML = '<i data-lucide="download"></i> ' + translations.downloadPdf + ' (' + selectedSize + ')';
                lucide.createIcons();
            } else {
                // Hide paper size info
                infoDiv.style.display = 'none';

                // Disable download button
                downloadBtn.classList.add('disabled');
                downloadBtn.innerHTML = '<i data-lucide="download"></i> ' + translations.downloadPdf;
                lucide.createIcons();
            }
        }

        // Handle PDF download click
        function handlePdfDownload(event) {
            const select = document.getElementById('paperSizeSelect');
            const wrapper = document.getElementById('paperSizeSelectorWrapper');
            const selectedSize = select.value;

            if (!selectedSize) {
                event.preventDefault();

                // Show error state
                wrapper.classList.add('error');
                select.focus();

                // Remove error state after animation
                setTimeout(() => {
                    wrapper.classList.remove('error');
                }, 3000);

                return false;
            }

            // Build download URL and navigate
            const downloadUrl = pdfBaseUrl + '&paper=' + encodeURIComponent(selectedSize);
            window.location.href = downloadUrl;

            // Show download modal after a short delay
            setTimeout(function() {
                document.getElementById('downloadModal').classList.add('show');
            }, 500);

            return false;
        }

        // Show download modal when download link is clicked
        function showDownloadModal(event) {
            // Let the download proceed naturally
            // Show modal after a short delay to let download start
            setTimeout(function() {
                document.getElementById('downloadModal').classList.add('show');
            }, 500);
        }

        // Close modal
        function closeDownloadModal() {
            document.getElementById('downloadModal').classList.remove('show');
        }

        // Close modal when clicking outside
        document.getElementById('downloadModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeDownloadModal();
            }
        });

        // Projector Modal Functions
        function showProjectorModal() {
            document.getElementById('projectorModal').classList.add('show');
            loadProjectorPatterns();
            lucide.createIcons();
        }

        function closeProjectorModal() {
            document.getElementById('projectorModal').classList.remove('show');
        }

        // Close projector modal when clicking outside
        document.getElementById('projectorModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeProjectorModal();
            }
        });

        // Load projector patterns via AJAX
        function loadProjectorPatterns() {
            const listContainer = document.getElementById('projectorPatternList');

            // Fetch pattern list from API
            fetch(projectorBaseUrl + '&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        listContainer.innerHTML = `
                            <div style="text-align: center; padding: 2rem; color: #DC2626;">
                                <i data-lucide="alert-circle" style="width: 32px; height: 32px;"></i>
                                <p>${data.error}</p>
                            </div>
                        `;
                        lucide.createIcons();
                        return;
                    }

                    // Build pattern list HTML
                    let html = '';
                    data.patterns.forEach((pattern, index) => {
                        const isScale = pattern.key === 'scale';
                        const downloadUrl = projectorBaseUrl + '&action=download&pattern=' + encodeURIComponent(pattern.key);

                        html += `
                            <div class="projector-pattern-item ${isScale ? 'scale-item' : ''}">
                                <div class="projector-pattern-icon">
                                    <i data-lucide="${isScale ? 'ruler' : 'file-text'}" style="width: 22px; height: 22px; color: ${isScale ? 'white' : '#059669'};"></i>
                                </div>
                                <div class="projector-pattern-info">
                                    <h4>${isScale ? '1. ' : (index + 1) + '. '}${pattern.name}</h4>
                                    <p>${pattern.description}</p>
                                </div>
                                <a href="${downloadUrl}" class="projector-download-btn" onclick="handleProjectorDownload(event)">
                                    <i data-lucide="download"></i>
                                    Download
                                </a>
                            </div>
                        `;
                    });

                    listContainer.innerHTML = html;
                    lucide.createIcons();
                })
                .catch(error => {
                    console.error('Error loading patterns:', error);
                    // Fallback: show default patterns
                    showDefaultPatterns(listContainer);
                });
        }

        // Fallback: Show default pattern list if AJAX fails
        function showDefaultPatterns(container) {
            const defaultPatterns = [
                { key: 'scale', name: translations.scaleCalibration, description: translations.scaleDesc, isScale: true },
                { key: 'front', name: translations.front, description: translations.frontDesc, isScale: false },
                { key: 'back', name: translations.back, description: translations.backDesc, isScale: false },
                { key: 'patti', name: translations.patti, description: translations.pattiDesc, isScale: false },
                { key: 'sleeve', name: translations.sleeve, description: translations.sleeveDesc, isScale: false }
            ];

            let html = '';
            defaultPatterns.forEach((pattern, index) => {
                const downloadUrl = projectorBaseUrl + '&action=download&pattern=' + encodeURIComponent(pattern.key);

                html += `
                    <div class="projector-pattern-item ${pattern.isScale ? 'scale-item' : ''}">
                        <div class="projector-pattern-icon">
                            <i data-lucide="${pattern.isScale ? 'ruler' : 'file-text'}" style="width: 22px; height: 22px; color: ${pattern.isScale ? 'white' : '#059669'};"></i>
                        </div>
                        <div class="projector-pattern-info">
                            <h4>${index + 1}. ${pattern.name}</h4>
                            <p>${pattern.description}</p>
                        </div>
                        <a href="${downloadUrl}" class="projector-download-btn" onclick="handleProjectorDownload(event)">
                            <i data-lucide="download"></i>
                            ${translations.download}
                        </a>
                    </div>
                `;
            });

            container.innerHTML = html;
            lucide.createIcons();
        }

        // Handle projector pattern download click
        function handleProjectorDownload(event) {
            // Let the download proceed
            // Could add tracking/analytics here
        }

        // Projector PNG Modal Functions
        function showProjectorPngModal() {
            document.getElementById('projectorPngModal').classList.add('show');
            loadProjectorPngPatterns();
            lucide.createIcons();
        }

        function closeProjectorPngModal() {
            document.getElementById('projectorPngModal').classList.remove('show');
        }

        // Close projector PNG modal when clicking outside
        document.getElementById('projectorPngModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeProjectorPngModal();
            }
        });

        // Load projector PNG patterns via AJAX
        function loadProjectorPngPatterns() {
            const listContainer = document.getElementById('projectorPngPatternList');

            // Fetch pattern list from PNG API
            fetch(projectorPngBaseUrl + '&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        listContainer.innerHTML = `
                            <div style="text-align: center; padding: 2rem; color: #DC2626;">
                                <i data-lucide="alert-circle" style="width: 32px; height: 32px;"></i>
                                <p>${data.error}</p>
                            </div>
                        `;
                        lucide.createIcons();
                        return;
                    }

                    // Build pattern list HTML
                    let html = '';
                    data.patterns.forEach((pattern, index) => {
                        const isScale = pattern.key === 'scale';
                        const downloadUrl = projectorPngBaseUrl + '&action=download&pattern=' + encodeURIComponent(pattern.key);

                        html += `
                            <div class="projector-pattern-item ${isScale ? 'scale-item' : ''}" style="${!isScale ? 'border-color: #22D3EE;' : ''}">
                                <div class="projector-pattern-icon" style="${!isScale ? 'background: #CFFAFE;' : ''}">
                                    <i data-lucide="${isScale ? 'ruler' : 'image'}" style="width: 22px; height: 22px; color: ${isScale ? 'white' : '#0891B2'};"></i>
                                </div>
                                <div class="projector-pattern-info">
                                    <h4>${isScale ? '1. ' : (index + 1) + '. '}${pattern.name}</h4>
                                    <p>${pattern.description}${data.dpi ? ' • ' + data.dpi + ' DPI' : ''}</p>
                                </div>
                                <a href="${downloadUrl}" class="projector-download-btn" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);">
                                    <i data-lucide="download"></i>
                                    PNG
                                </a>
                            </div>
                        `;
                    });

                    listContainer.innerHTML = html;
                    lucide.createIcons();
                })
                .catch(error => {
                    console.error('Error loading PNG patterns:', error);
                    // Fallback: show default patterns
                    showDefaultPngPatterns(listContainer);
                });
        }

        // Fallback: Show default PNG pattern list if AJAX fails
        function showDefaultPngPatterns(container) {
            const defaultPatterns = [
                { key: 'scale', name: translations.scaleCalibration, description: translations.scaleDesc, isScale: true },
                { key: 'front', name: translations.front, description: translations.frontDesc, isScale: false },
                { key: 'back', name: translations.back, description: translations.backDesc, isScale: false },
                { key: 'patti', name: translations.patti, description: translations.pattiDesc, isScale: false },
                { key: 'sleeve', name: translations.sleeve, description: translations.sleeveDesc, isScale: false }
            ];

            let html = '';
            defaultPatterns.forEach((pattern, index) => {
                const downloadUrl = projectorPngBaseUrl + '&action=download&pattern=' + encodeURIComponent(pattern.key);

                html += `
                    <div class="projector-pattern-item ${pattern.isScale ? 'scale-item' : ''}" style="${!pattern.isScale ? 'border-color: #22D3EE;' : ''}">
                        <div class="projector-pattern-icon" style="${!pattern.isScale ? 'background: #CFFAFE;' : ''}">
                            <i data-lucide="${pattern.isScale ? 'ruler' : 'image'}" style="width: 22px; height: 22px; color: ${pattern.isScale ? 'white' : '#0891B2'};"></i>
                        </div>
                        <div class="projector-pattern-info">
                            <h4>${index + 1}. ${pattern.name}</h4>
                            <p>${pattern.description} • 150 DPI</p>
                        </div>
                        <a href="${downloadUrl}" class="projector-download-btn" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);">
                            <i data-lucide="download"></i>
                            PNG
                        </a>
                    </div>
                `;
            });

            container.innerHTML = html;
            lucide.createIcons();
        }

        // Attach click handler to download button(s) - but not projector button
        document.addEventListener('DOMContentLoaded', function() {
            const downloadButtons = document.querySelectorAll('.btn-download:not([onclick*="showProjectorModal"])');
            downloadButtons.forEach(function(button) {
                button.addEventListener('click', showDownloadModal);
            });
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDownloadModal();
                closeProjectorModal();
                closeProjectorPngModal();
                closeNav();
            }
        });

        // Mobile Navigation
        (function() {
            var hamburger = document.getElementById('hamburgerBtn');
            var body = document.body;

            function openNav() {
                body.classList.add('nav-open');
            }

            function closeNav() {
                body.classList.remove('nav-open');
            }

            window.closeNav = closeNav;

            function toggleNav() {
                if (body.classList.contains('nav-open')) {
                    closeNav();
                } else {
                    openNav();
                }
            }

            if (hamburger) {
                hamburger.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleNav();
                    return false;
                };
            }
        })();
    </script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
