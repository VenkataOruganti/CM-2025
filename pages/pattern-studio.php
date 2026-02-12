<?php
/**
 * ============================================================================
 * PATTERN STUDIO - Measurement Input Form
 * ============================================================================
 *
 * This page allows users to enter body measurements for generating custom-fitted
 * blouse patterns. Features include:
 *
 * - Self/Customer measurement selection (for boutique users)
 * - Category selection (Women, Men, Boy, Girl - currently only Women active)
 * - Pattern type selection (Blouses, Kurtis, etc. - currently only Blouses active)
 * - 14 measurement fields for blouse patterns
 * - Form validation with inline error messages
 * - Duplicate submission prevention
 * - Guest user support (saves to session, prompts login)
 *
 * Layout: 3-column grid
 *   - Column 1: Fields 1-8 (Body measurements)
 *   - Column 2: Fields 9-14 + Notes + Submit button
 *   - Column 3: Measurement guide diagram
 *
 * @author CuttingMaster Team
 * @version 2.0
 * @last-modified 2025-01-02
 * ============================================================================
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/public_measurements_helper.php';
require_once __DIR__ . '/../includes/lang-init.php';

// ============================================================================
// SESSION & AUTH CHECK
// ============================================================================
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

$message = '';
$messageType = '';
$editMeasurement = null;

// ============================================================================
// FETCH BLOUSE PATTERNS FROM DATABASE
// ============================================================================
$blousePatterns = [];
try {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id, title, description, image, price
        FROM pattern_making_portfolio
        WHERE status = 'active'
        ORDER BY display_order ASC, created_at DESC
    ");
    $stmt->execute();
    $blousePatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently fail if table doesn't exist
    error_log("Error fetching blouse patterns: " . $e->getMessage());
}

// ============================================================================
// SUCCESS MESSAGE HANDLING
// ============================================================================
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = __('pattern_studio.success_message');
    $messageType = 'success';
}

// ============================================================================
// EDIT MODE - Load existing measurement for editing
// ============================================================================
if (isset($_GET['edit']) && $isLoggedIn) {
    global $pdo;
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM measurements WHERE id = ? AND user_id = ?");
    $stmt->execute([$editId, $_SESSION['user_id']]);
    $editMeasurement = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================================================
// FORM SUBMISSION - LOGGED IN USER
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {

    // Prevent duplicate submission (browser refresh protection)
    $submissionToken = $_POST['submission_token'] ?? '';
    $lastToken = $_SESSION['last_submission_token'] ?? '';

    if ($submissionToken === $lastToken && !empty($submissionToken)) {
        // Duplicate submission detected - redirect immediately
        if ($userType === 'boutique') {
            header('Location: dashboard.php?success=1');
        } else {
            header('Location: pattern-studio.php?success=1');
        }
        exit;
    }

    try {
        global $pdo;

        // ------------------------------------------------------------------------
        // Collect form data
        // ------------------------------------------------------------------------
        $userId = $_SESSION['user_id'];
        $userType = $_SESSION['user_type'] ?? 'individual';
        $measurementOf = trim($_POST['measurement_of'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $customerName = ($measurementOf === 'customer') ? ucwords(strtolower(trim($_POST['customer_name'] ?? ''))) : null;
        $customerName = $customerName === '' ? null : $customerName;
        $customerReference = ($measurementOf === 'customer') ? trim($_POST['customer_reference'] ?? null) : null;
        $patternType = trim($_POST['pattern_type'] ?? 'blouse');

        // Validation
        if (empty($measurementOf) || empty($category)) {
            throw new Exception('Please select measurement type and category.');
        }

        // ------------------------------------------------------------------------
        // Helper function to clean measurement values
        // Converts to float but stores as int if no decimal portion (removes .00)
        // ------------------------------------------------------------------------
        $cleanMeasurement = function($value) {
            if (empty($value)) return null;
            $floatVal = floatval($value);
            // If the value is a whole number, return as int to avoid .00
            if ($floatVal == intval($floatVal)) {
                return intval($floatVal);
            }
            return $floatVal;
        };

        // ------------------------------------------------------------------------
        // Women-specific measurements (14 fields for blouse)
        // Field names match database columns from saviMeasure.php legacy system
        // ------------------------------------------------------------------------
        $blouseBackLength = $cleanMeasurement($_POST['blength'] ?? null);
        $fullShoulder = $cleanMeasurement($_POST['fshoulder'] ?? null);
        $shoulderStrap = $cleanMeasurement($_POST['shoulder'] ?? null);
        $backNeckDepth = $cleanMeasurement($_POST['bnDepth'] ?? null);
        $frontNeckDepth = $cleanMeasurement($_POST['fndepth'] ?? null);
        $shoulderToApex = $cleanMeasurement($_POST['apex'] ?? null);
        $frontLength = $cleanMeasurement($_POST['flength'] ?? null);
        $upperChest = $cleanMeasurement($_POST['chest'] ?? null);
        $bust = $cleanMeasurement($_POST['bust'] ?? null);
        $waist = $cleanMeasurement($_POST['waist'] ?? null);
        $sleeveLength = $cleanMeasurement($_POST['slength'] ?? null);
        $armRound = $cleanMeasurement($_POST['saround'] ?? null);
        $sleeveEndRound = $cleanMeasurement($_POST['sopen'] ?? null);
        $armhole = $cleanMeasurement($_POST['armhole'] ?? null);

        // Generic measurements (for future Men/Boy/Girl support)
        $hips = $cleanMeasurement($_POST['hips'] ?? null);
        $height = $cleanMeasurement($_POST['height'] ?? null);
        $inseam = $cleanMeasurement($_POST['inseam'] ?? null);
        $thighCircumference = $cleanMeasurement($_POST['thigh_circumference'] ?? null);
        $notes = trim($_POST['notes'] ?? '');

        // Check if updating existing measurement
        $editId = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : null;

        // ------------------------------------------------------------------------
        // Boutique customer handling
        // ------------------------------------------------------------------------
        $customerId = null;
        if ($userType === 'boutique' && $measurementOf === 'customer' && !empty($customerName) && !$editId) {
            // Check if customer already exists for this boutique
            $checkStmt = $pdo->prepare("SELECT id FROM customers WHERE boutique_user_id = ? AND customer_name = ?");
            $checkStmt->execute([$userId, $customerName]);
            $existingCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            } else {
                // Create new customer record
                $customerStmt = $pdo->prepare("INSERT INTO customers (boutique_user_id, customer_name, customer_reference) VALUES (?, ?, ?)");
                $customerStmt->execute([$userId, $customerName, !empty($customerReference) ? $customerReference : null]);
                $customerId = $pdo->lastInsertId();
            }
        }

        // ------------------------------------------------------------------------
        // Database transaction for atomic insert/update
        // ------------------------------------------------------------------------
        $pdo->beginTransaction();

        try {
            if ($editId) {
                // UPDATE existing measurement
                $stmt = $pdo->prepare("
                    UPDATE measurements SET
                        measurement_of = ?, category = ?, pattern_type = ?, customer_name = ?, customer_reference = ?,
                        blength = ?, fshoulder = ?, shoulder = ?, bnDepth = ?,
                        fndepth = ?, apex = ?, flength = ?, chest = ?,
                        bust = ?, waist = ?, hips = ?, height = ?,
                        slength = ?, saround = ?, sopen = ?, armhole = ?,
                        inseam = ?, thigh_circumference = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $measurementOf, $category, $patternType, $customerName, $customerReference,
                    $blouseBackLength, $fullShoulder, $shoulderStrap, $backNeckDepth,
                    $frontNeckDepth, $shoulderToApex, $frontLength, $upperChest,
                    $bust, $waist, $hips, $height,
                    $sleeveLength, $armRound, $sleeveEndRound, $armhole,
                    $inseam, $thighCircumference, !empty($notes) ? $notes : null,
                    $editId, $userId
                ]);

                // Also update the customers table if this measurement has a customer_id
                // This ensures phone number changes are reflected in the dashboard
                $customerUpdateStmt = $pdo->prepare("
                    UPDATE customers c
                    INNER JOIN measurements m ON m.customer_id = c.id
                    SET c.customer_reference = ?, c.customer_name = ?
                    WHERE m.id = ? AND m.user_id = ? AND m.customer_id IS NOT NULL
                ");
                $customerUpdateStmt->execute([$customerReference, $customerName, $editId, $userId]);
            } else {
                // INSERT new measurement
                $stmt = $pdo->prepare("
                    INSERT INTO measurements (
                        user_id, customer_id, measurement_of, category, pattern_type, customer_name, customer_reference,
                        blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest,
                        bust, waist, hips, height, slength, saround, sopen, armhole,
                        inseam, thigh_circumference, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId, $customerId, $measurementOf, $category, $patternType, $customerName, $customerReference,
                    $blouseBackLength, $fullShoulder, $shoulderStrap, $backNeckDepth,
                    $frontNeckDepth, $shoulderToApex, $frontLength, $upperChest,
                    $bust, $waist, $hips, $height,
                    $sleeveLength, $armRound, $sleeveEndRound, $armhole,
                    $inseam, $thighCircumference, !empty($notes) ? $notes : null
                ]);

                // Save anonymous copy for analytics with deduplication (if required fields present)
                if (!empty($bust) && !empty($waist)) {
                    $publicMeasurements = [
                        'blength' => $blouseBackLength,
                        'fshoulder' => $fullShoulder,
                        'shoulder' => $shoulderStrap,
                        'bnDepth' => $backNeckDepth,
                        'fndepth' => $frontNeckDepth,
                        'apex' => $shoulderToApex,
                        'flength' => $frontLength,
                        'chest' => $upperChest,
                        'bust' => $bust,
                        'waist' => $waist,
                        'slength' => $sleeveLength,
                        'saround' => $armRound,
                        'sopen' => $sleeveEndRound,
                        'armhole' => $armhole
                    ];

                    // Use deduplication helper - increments repetition if match found
                    $result = savePublicMeasurement($pdo, $category, $patternType, $publicMeasurements);
                    error_log("Public measurement {$result['status']}: ID={$result['id']}, {$result['message']}");
                }
            }

            $pdo->commit();
            $_SESSION['last_submission_token'] = $submissionToken;

            // Redirect based on action type
            if ($editId) {
                // After updating measurements, always redirect to dashboard
                header("Location: dashboard.php?success=1&action=updated");
                exit;
            }

            // Redirect based on user type for new measurements
            if ($userType === 'boutique') {
                $redirect = $customerId ? "dashboard.php?customer_id={$customerId}&success=1" : "dashboard.php?success=1";
            } else {
                $redirect = "pattern-studio.php?success=1";
            }
            header("Location: $redirect");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
        error_log("Measurement save error: " . $e->getMessage());
    }
}

// ============================================================================
// FORM SUBMISSION - GUEST USER (NOT LOGGED IN)
// Save to session and redirect to login
// ============================================================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLoggedIn) {
    try {
        global $pdo;

        $category = trim($_POST['category'] ?? '');
        $patternType = trim($_POST['pattern_type'] ?? 'blouse');

        // Collect measurements
        $blouseBackLength = !empty($_POST['blength']) ? floatval($_POST['blength']) : null;
        $fullShoulder = !empty($_POST['fshoulder']) ? floatval($_POST['fshoulder']) : null;
        $shoulderStrap = !empty($_POST['shoulder']) ? floatval($_POST['shoulder']) : null;
        $backNeckDepth = !empty($_POST['bnDepth']) ? floatval($_POST['bnDepth']) : null;
        $frontNeckDepth = !empty($_POST['fndepth']) ? floatval($_POST['fndepth']) : null;
        $shoulderToApex = !empty($_POST['apex']) ? floatval($_POST['apex']) : null;
        $frontLength = !empty($_POST['flength']) ? floatval($_POST['flength']) : null;
        $upperChest = !empty($_POST['chest']) ? floatval($_POST['chest']) : null;
        $bust = !empty($_POST['bust']) ? floatval($_POST['bust']) : null;
        $waist = !empty($_POST['waist']) ? floatval($_POST['waist']) : null;
        $sleeveLength = !empty($_POST['slength']) ? floatval($_POST['slength']) : null;
        $armRound = !empty($_POST['saround']) ? floatval($_POST['saround']) : null;
        $sleeveEndRound = !empty($_POST['sopen']) ? floatval($_POST['sopen']) : null;
        $armhole = !empty($_POST['armhole']) ? floatval($_POST['armhole']) : null;
        $hips = !empty($_POST['hips']) ? floatval($_POST['hips']) : null;
        $height = !empty($_POST['height']) ? floatval($_POST['height']) : null;
        $inseam = !empty($_POST['inseam']) ? floatval($_POST['inseam']) : null;
        $thighCircumference = !empty($_POST['thigh_circumference']) ? floatval($_POST['thigh_circumference']) : null;

        // Save anonymous copy for analytics with deduplication
        if (!empty($bust) && !empty($waist)) {
            $publicMeasurements = [
                'blength' => $blouseBackLength,
                'fshoulder' => $fullShoulder,
                'shoulder' => $shoulderStrap,
                'bnDepth' => $backNeckDepth,
                'fndepth' => $frontNeckDepth,
                'apex' => $shoulderToApex,
                'flength' => $frontLength,
                'chest' => $upperChest,
                'bust' => $bust,
                'waist' => $waist,
                'slength' => $sleeveLength,
                'saround' => $armRound,
                'sopen' => $sleeveEndRound,
                'armhole' => $armhole
            ];

            // Use deduplication helper - increments repetition if match found
            $result = savePublicMeasurement($pdo, $category, $patternType, $publicMeasurements);
            error_log("Public measurement (guest) {$result['status']}: ID={$result['id']}, {$result['message']}");
        }

        // Store in session for post-login save
        $_SESSION['pending_measurements'] = [
            'measurement_of' => trim($_POST['measurement_of'] ?? ''),
            'category' => $category,
            'pattern_type' => $patternType,
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'customer_reference' => trim($_POST['customer_reference'] ?? ''),
            'blength' => trim($_POST['blength'] ?? ''),
            'fshoulder' => trim($_POST['fshoulder'] ?? ''),
            'shoulder' => trim($_POST['shoulder'] ?? ''),
            'bnDepth' => trim($_POST['bnDepth'] ?? ''),
            'fndepth' => trim($_POST['fndepth'] ?? ''),
            'apex' => trim($_POST['apex'] ?? ''),
            'flength' => trim($_POST['flength'] ?? ''),
            'chest' => trim($_POST['chest'] ?? ''),
            'bust' => trim($_POST['bust'] ?? ''),
            'slength' => trim($_POST['slength'] ?? ''),
            'saround' => trim($_POST['saround'] ?? ''),
            'sopen' => trim($_POST['sopen'] ?? ''),
            'armhole' => trim($_POST['armhole'] ?? ''),
            'waist' => trim($_POST['waist'] ?? ''),
            'hips' => trim($_POST['hips'] ?? ''),
            'height' => trim($_POST['height'] ?? ''),
            'inseam' => trim($_POST['inseam'] ?? ''),
            'thigh_circumference' => trim($_POST['thigh_circumference'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        header('Location: login.php?return=pattern-studio&action=save_measurements');
        exit;

    } catch (Exception $e) {
        $message = 'Error saving measurements: ' . $e->getMessage();
        $messageType = 'error';
        error_log("Public measurements save error (not logged in): " . $e->getMessage());
    }
}

// Generate unique submission token for form
$submissionToken = bin2hex(random_bytes(16));

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = 'Pattern Studio - Customized Pattern Making from Body Measurements';
$metaDescription = 'Enter body measurements and instantly generate customized tailoring patterns. Our pattern making tool creates precise saree blouse patterns, kurti patterns with perfect fit. Free pattern generator for tailors and boutiques in India.';
$metaKeywords = 'customized pattern making, pattern generator from measurements, saree blouse pattern maker, online pattern making tool, body measurement pattern, custom tailoring patterns, digital pattern making';
$activePage = 'pattern-studio';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';

// Get current user info for header
if ($isLoggedIn) {
    require_once __DIR__ . '/../config/auth.php';
    $currentUser = getCurrentUser();
}

// Page-specific styles
$additionalStyles = <<<'CSS'
        /* ====================================================================
           PATTERN STUDIO PAGE STYLES
           ====================================================================

           Structure:
           1. Container Overrides - Wider container for pattern studio
           2. Form Row Layout - Horizontal layout for radio sections
           3. Customer Details - Expandable customer info section
           4. Section Separator - Horizontal dividers
           5. Three Column Grid - Main form layout
           6. Form Fields - Input styling
           7. Diagram Column - Measurement guide image
           8. Responsive Breakpoints
           ==================================================================== */

        /* --------------------------------------------------------------------
           1. CONTAINER OVERRIDES
           -------------------------------------------------------------------- */
        .auth-container-pattern-studio {
            max-width: 1400px;
        }
        .auth-container-pattern-studio .auth-form {
            max-width: 100%;
        }
        .auth-container-pattern-studio .hero-title,
        .auth-container-pattern-studio .auth-title {
            margin-bottom: 0.25rem;
        }
        .auth-container-pattern-studio .hero-description,
        .auth-container-pattern-studio .auth-description {
            margin-bottom: 15px;
        }

        /* --------------------------------------------------------------------
           2. FORM ROW LAYOUT - Horizontal layout for radio sections
           -------------------------------------------------------------------- */
        .form-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }
        .form-row:first-child {
            margin-top: 0;
        }

        /* Fixed-width labels for alignment */
        .form-label-fixed {
            width: 200px;
            min-width: 200px;
            flex-shrink: 0;
            margin-bottom: 0;
            white-space: nowrap;
        }

        /* Radio button groups */
        .radio-group,
        .category-grid,
        .pattern-type-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
            align-items: center;
        }

        /* Blouse subtype section */
        .blouse-subtype-section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .blouse-subtype-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
            align-items: center;
            padding-left: 1rem;
            border-left: 3px solid #e2e8f0;
        }

        /* Blouse info items (bullet list style) */
        .blouse-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .blouse-info-item i {
            color: #667eea;
            flex-shrink: 0;
        }

        .blouse-info-item.blouse-info-disabled {
            opacity: 0.5;
        }

        .blouse-info-item.blouse-info-disabled i {
            color: #94a3b8;
        }

        /* Disabled options styling */
        .option-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* --------------------------------------------------------------------
           2.5. BLOUSE PATTERN SELECTION GRID - Clickable pattern items
           -------------------------------------------------------------------- */
        #blouse-subtypes {
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .blouse-pattern-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: stretch;
            flex: 1;
        }

        .blouse-pattern-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            min-width: 140px;
        }

        .blouse-pattern-item:hover {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .blouse-pattern-item.selected {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .blouse-pattern-item.selected .pattern-name {
            color: #1a202c;
            font-weight: 600;
        }

        .blouse-pattern-item .pattern-name {
            font-size: 0.9rem;
            color: #1a202c;
            text-align: center;
            font-weight: 500;
        }

        .blouse-pattern-item .pattern-price {
            font-size: 0.75rem;
            color: #718096;
        }

        .blouse-pattern-item .pattern-price.free {
            color: #dc2626;
            font-weight: 700;
        }

        .no-patterns {
            color: #718096;
            font-style: italic;
        }

        /* Measurements Form Header */
        .measurements-form-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .measurements-form-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2D3748;
            margin: 0 0 0.25rem 0;
        }

        .measurements-form-title #pattern-name-text {
            color: #dc2626;
            font-weight: 700;
        }

        .measurements-form-subtitle {
            font-size: 0.8rem;
            color: #718096;
            font-style: italic;
            margin: 0;
        }

        /* --------------------------------------------------------------------
           3. CUSTOMER DETAILS - Expandable section for boutique users
           -------------------------------------------------------------------- */
        .customer-details-row {
            display: none;
            margin-top: 1rem;
            align-items: center;
            gap: 1rem;
        }
        .customer-details-row.visible {
            display: flex;
        }
        .customer-field {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .customer-field label {
            margin-bottom: 0;
            white-space: nowrap;
        }
        .customer-field input {
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            width: 160px;
        }

        /* --------------------------------------------------------------------
           4. SECTION SEPARATOR - Horizontal dividers between sections
           -------------------------------------------------------------------- */
        .section-separator {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }

        /* --------------------------------------------------------------------
           5. THREE COLUMN GRID - Main form layout
           Column 1: Diagram | Column 2: Fields 1-8 | Column 3: Fields 9-14 + Notes
           -------------------------------------------------------------------- */
        .pattern-studio-grid {
            display: grid;
            grid-template-columns: 320px minmax(200px, 280px) minmax(200px, 280px);
            gap: 75px;
            margin-top: 1.5rem;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* --------------------------------------------------------------------
           6. FORM FIELDS - Label and input on same line
           -------------------------------------------------------------------- */
        .form-field {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .form-field label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #4A5568;
            white-space: nowrap;
            min-width: 160px;
        }
        .form-field input,
        .form-field select {
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            width: 100px;
        }
        .form-field input:focus,
        .form-field select:focus {
            outline: none;
            border-color: #805AD5;
        }

        /* Input wrapper for hover hint */
        .input-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .input-wrapper .unit-hint {
            position: absolute;
            right: -65px;
            font-size: 0.75rem;
            color: #718096;
            font-style: italic;
            opacity: 0;
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }
        .input-wrapper:hover .unit-hint,
        .input-wrapper input:focus + .unit-hint {
            opacity: 1;
        }

        /* Validation error messages */
        .error-message {
            color: #E53E3E;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        /* Notes field - stacked layout */
        .form-field.notes-inline {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .form-field.notes-inline textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            resize: vertical;
        }
        .form-field.notes-inline textarea:focus {
            outline: none;
            border-color: #805AD5;
        }

        /* Submit button container */
        .form-field.submit-field {
            margin-top: 1rem;
        }
        .form-field.submit-field .btn-primary {
            padding: 0.75rem 2rem;
            white-space: nowrap;
        }

        /* --------------------------------------------------------------------
           7. DIAGRAM COLUMN - Measurement guide image
           -------------------------------------------------------------------- */
        .diagram-column {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .diagram-column h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: #2D3748;
        }
        .diagram-column img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        /* --------------------------------------------------------------------
           8. RESPONSIVE BREAKPOINTS
           -------------------------------------------------------------------- */

        /* Tablet: 2 columns, diagram moves to top */
        @media (max-width: 1200px) {
            .auth-container-pattern-studio {
                padding: 0 1.5rem;
            }
            .pattern-studio-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
            .diagram-column {
                grid-column: 1 / 3;
                order: -1;
            }
            .form-label-fixed {
                width: 180px;
                min-width: 180px;
            }
        }

        /* Small Tablet / Large Mobile: 992px */
        @media (max-width: 992px) {
            .auth-container-pattern-studio {
                padding: 0 1rem;
            }
            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .form-label-fixed {
                width: auto;
                min-width: auto;
            }
            .radio-group,
            .category-grid,
            .pattern-type-grid {
                flex-wrap: wrap;
                gap: 0.5rem 1rem;
            }
            .customer-details-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .customer-details-row .form-label-fixed {
                display: none;
            }
            .customer-field {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
                width: 100%;
            }
            .customer-field input {
                width: 100% !important;
                max-width: 300px;
            }
            .blouse-subtype-section {
                margin-left: 0;
            }
            .blouse-subtype-row {
                flex-direction: column;
                gap: 0.5rem;
                padding-left: 0.75rem;
            }
            .blouse-pattern-grid {
                justify-content: flex-start;
            }
            .blouse-pattern-item {
                min-width: 120px;
                padding: 0.6rem 0.8rem;
            }
            .measurements-form-title {
                font-size: 1rem;
            }
            .measurements-form-subtitle {
                font-size: 0.75rem;
            }
        }

        /* Mobile: Single column, stacked form fields */
        @media (max-width: 768px) {
            .auth-container-pattern-studio {
                padding: 0 0.75rem;
            }
            .auth-section-padded {
                padding-top: 80px !important;
            }
            .hero-title.auth-title {
                font-size: 2rem;
                margin-top: 10px !important;
                white-space: nowrap;
            }
            .hero-description.auth-description {
                font-size: 1rem;
            }
            .pattern-studio-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                margin-top: 1rem;
            }
            .diagram-column {
                grid-column: 1;
                padding: 1rem;
            }
            .diagram-column h3 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }
            .form-column {
                gap: 0.75rem;
            }
            .form-field {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            .form-field label {
                min-width: auto;
                font-size: 0.85rem;
            }
            .form-field input,
            .form-field select {
                width: 100%;
                padding: 0.625rem 0.75rem;
                font-size: 16px; /* Prevents iOS zoom on focus */
            }
            .form-field.notes-inline textarea {
                font-size: 16px;
                padding: 0.625rem 0.75rem;
            }
            .form-field.submit-field {
                margin-top: 0.5rem;
            }
            .form-field.submit-field .btn-primary {
                width: 100%;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
            .error-message {
                margin-left: 0;
                margin-top: 0.25rem;
            }
            .section-separator {
                margin: 0.75rem 0;
            }
            /* Radio buttons - better touch targets */
            .form-radio-item {
                padding: 0.5rem 0;
            }
            .form-radio-item input[type="radio"] {
                width: 18px;
                height: 18px;
            }
            .form-radio-item label {
                font-size: 0.9rem;
                padding-left: 0.25rem;
            }
            /* Blouse types mobile */
            .blouse-info-item {
                font-size: 0.85rem;
            }
            /* Blouse pattern grid mobile */
            .blouse-pattern-grid {
                gap: 0.5rem;
            }
            .blouse-pattern-item {
                min-width: 100px;
                padding: 0.5rem 0.6rem;
            }
            .blouse-pattern-item .pattern-name {
                font-size: 0.8rem;
            }
            .blouse-pattern-item .pattern-price {
                font-size: 0.7rem;
            }
            /* Measurements form header mobile */
            .measurements-form-header {
                margin-bottom: 0.75rem;
            }
            .measurements-form-title {
                font-size: 0.95rem;
            }
            .measurements-form-subtitle {
                font-size: 0.7rem;
            }
            /* Alert messages */
            .alert {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }
        }

        /* Extra Small Mobile: 480px and below */
        @media (max-width: 480px) {
            .auth-container-pattern-studio {
                padding: 0 0.5rem;
            }
            .hero-title.auth-title {
                font-size: 1.75rem;
                white-space: nowrap;
            }
            .hero-title-accent {
                display: block;
            }
            .hero-description.auth-description {
                font-size: 0.85rem;
                line-height: 1.5;
            }
            .pattern-studio-grid {
                gap: 1rem;
            }
            .diagram-column {
                padding: 0.75rem;
            }
            .diagram-column img {
                max-height: 250px;
                object-fit: contain;
            }
            .form-field input,
            .form-field select {
                padding: 0.75rem;
            }
            .radio-group,
            .category-grid,
            .pattern-type-grid {
                gap: 0.25rem 0.75rem;
            }
            .form-radio-item label {
                font-size: 0.85rem;
            }
            .blouse-subtype-row {
                padding-left: 0.5rem;
                border-left-width: 2px;
            }
            .blouse-info-item {
                font-size: 0.8rem;
            }
            /* Blouse pattern grid extra small */
            .blouse-pattern-item {
                min-width: calc(50% - 0.25rem);
                flex: 1 1 calc(50% - 0.25rem);
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .form-field input,
            .form-field select,
            .form-field textarea {
                min-height: 44px; /* Apple's recommended touch target */
            }
            .btn-primary {
                min-height: 48px;
            }
            .form-radio-item {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }

        /* Landscape mobile optimization */
        @media (max-width: 768px) and (orientation: landscape) {
            .auth-section-padded {
                padding-top: 70px !important;
            }
            .diagram-column {
                display: flex;
                gap: 1rem;
                align-items: flex-start;
            }
            .diagram-column h3 {
                white-space: nowrap;
            }
            .diagram-column img {
                max-height: 200px;
                width: auto;
            }
        }

        /* High DPI / Retina display adjustments */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .form-field input,
            .form-field select {
                border-width: 1px;
            }
        }
CSS;

// Include shared header
include __DIR__ . '/../includes/header.php';
?>

    <!-- ================================================================
         MAIN CONTENT - Pattern Studio Form
         ================================================================ -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container auth-container-pattern-studio">
            <div class="hero-content auth-content">

                <!-- Page Header -->
                <h1 class="hero-title auth-title" style="margin-top: 20px;">
                    <?php _e('pattern_studio.title'); ?> <span class="hero-title-accent"><?php _e('pattern_studio.title_accent'); ?></span>
                </h1>
                <p class="hero-description auth-description">
                    <?php _e('pattern_studio.description'); ?>
                </p>

                <hr class="section-separator">

                <!-- Success/Error Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- ============================================================
                     MEASUREMENTS FORM
                     ============================================================ -->
                <form method="POST" action="" class="auth-form pattern-form" id="measurements-form">

                    <!-- Hidden fields for form handling -->
                    <?php if ($editMeasurement): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $editMeasurement['id']; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="submission_token" value="<?php echo $submissionToken; ?>">

                    <!-- --------------------------------------------------------
                         SECTION: Measurements of (Customer/Self)
                         -------------------------------------------------------- -->
                    <div class="form-row">
                        <label class="form-label form-label-fixed"><?php _e('pattern_studio.measurements_of'); ?></label>
                        <div class="radio-group">
                            <div class="form-radio-item">
                                <input type="radio" id="measurement-customer" name="measurement_of" value="customer"
                                    <?php echo (!$editMeasurement || $editMeasurement['measurement_of'] === 'customer') ? 'checked' : ''; ?> required>
                                <label for="measurement-customer"><?php _e('pattern_studio.customer'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="measurement-self" name="measurement_of" value="self"
                                    <?php echo ($editMeasurement && $editMeasurement['measurement_of'] === 'self') ? 'checked' : ''; ?>>
                                <label for="measurement-self"><?php _e('pattern_studio.self'); ?></label>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details (visible when "Customer" is selected) -->
                    <div id="customer-details-group" class="customer-details-row <?php echo (!$editMeasurement || $editMeasurement['measurement_of'] === 'customer') ? 'visible' : ''; ?>">
                        <div class="form-label-fixed"></div>
                        <div class="customer-field">
                            <label for="customer_name"><?php _e('pattern_studio.customer_name'); ?></label>
                            <input type="text" id="customer_name" name="customer_name" placeholder="<?php echo __('pattern_studio.customer_name_placeholder'); ?>"
                                value="<?php echo $editMeasurement['customer_name'] ?? ''; ?>" style="width: 260px;">
                        </div>
                        <div class="customer-field">
                            <label for="customer_reference"><?php _e('pattern_studio.mobile_number'); ?></label>
                            <input type="text" id="customer_reference" name="customer_reference" placeholder="<?php echo __('pattern_studio.mobile_placeholder'); ?>"
                                value="<?php echo $editMeasurement['customer_reference'] ?? ''; ?>">
                        </div>
                    </div>

                    <hr class="section-separator">

                    <!-- --------------------------------------------------------
                         SECTION: Category (Women/Men/Boy/Girl)
                         Note: Only Women is currently active
                         -------------------------------------------------------- -->
                    <div class="form-row">
                        <label class="form-label form-label-fixed"><?php _e('pattern_studio.category'); ?></label>
                        <div class="category-grid">
                            <div class="form-radio-item">
                                <input type="radio" id="category-women" name="category" value="women"
                                    <?php echo (!$editMeasurement || $editMeasurement['category'] === 'women') ? 'checked' : ''; ?> required>
                                <label for="category-women"><?php _e('pattern_studio.women'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="category-men" name="category" value="men" disabled>
                                <label for="category-men" class="option-disabled"><?php _e('pattern_studio.men'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="category-boy" name="category" value="boy" disabled>
                                <label for="category-boy" class="option-disabled"><?php _e('pattern_studio.boy'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="category-girl" name="category" value="girl" disabled>
                                <label for="category-girl" class="option-disabled"><?php _e('pattern_studio.girl'); ?></label>
                            </div>
                        </div>
                    </div>

                    <hr class="section-separator">

                    <!-- --------------------------------------------------------
                         SECTION: Pattern Type
                         Note: Only Blouses is currently active
                         -------------------------------------------------------- -->
                    <div id="women-pattern-types" class="form-row">
                        <label class="form-label form-label-fixed"><?php _e('pattern_studio.pattern_type'); ?></label>
                        <div class="pattern-type-grid">
                            <div class="form-radio-item">
                                <input type="radio" id="pattern-blouse" name="pattern_type" value="blouse"
                                    <?php echo (!$editMeasurement || ($editMeasurement['pattern_type'] ?? 'blouse') === 'blouse') ? 'checked' : ''; ?> required>
                                <label for="pattern-blouse" style="color: #dc2626;"><?php _e('pattern_studio.blouses'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="pattern-kurti" name="pattern_type" value="kurti" disabled>
                                <label for="pattern-kurti" class="option-disabled"><?php _e('pattern_studio.kurtis'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="pattern-pants" name="pattern_type" value="pants" disabled>
                                <label for="pattern-pants" class="option-disabled"><?php _e('pattern_studio.pants'); ?></label>
                            </div>
                            <div class="form-radio-item">
                                <input type="radio" id="pattern-skirt" name="pattern_type" value="skirt" disabled>
                                <label for="pattern-skirt" class="option-disabled"><?php _e('pattern_studio.skirts'); ?></label>
                            </div>
                        </div>
                    </div>

                    <!-- --------------------------------------------------------
                         SECTION: Blouse Pattern Selection (clickable items from DB)
                         -------------------------------------------------------- -->
                    <div id="blouse-subtypes" class="form-row">
                        <div class="form-label-fixed"></div>
                        <div class="blouse-pattern-grid">
                            <?php if (!empty($blousePatterns)): ?>
                                <?php foreach ($blousePatterns as $index => $pattern): ?>
                                    <div class="blouse-pattern-item <?php echo $index === 0 ? 'selected' : ''; ?>"
                                         data-pattern-id="<?php echo $pattern['id']; ?>"
                                         data-pattern-title="<?php echo htmlspecialchars($pattern['title']); ?>"
                                         onclick="selectBlousePattern(this)">
                                        <span class="pattern-name"><?php echo htmlspecialchars($pattern['title']); ?></span>
                                        <?php if ($pattern['price'] == 0): ?>
                                            <span class="pattern-price free">FREE</span>
                                        <?php else: ?>
                                            <span class="pattern-price">&#8377;<?php echo number_format($pattern['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-patterns">No blouse patterns available.</p>
                            <?php endif; ?>
                        </div>
                        <!-- Hidden input to store selected pattern ID -->
                        <input type="hidden" id="selected_pattern_id" name="selected_pattern_id"
                               value="<?php echo !empty($blousePatterns) ? $blousePatterns[0]['id'] : ''; ?>">
                    </div>

                    <hr class="section-separator">

                    <!-- ============================================================
                         MEASUREMENTS FORM TITLE (includes selected pattern name)
                         ============================================================ -->
                    <div class="measurements-form-header">
                        <p id="selected-pattern-heading" class="measurements-form-title">
                            <?php _e('pattern_studio.enter_measurements_for'); ?> '<span id="pattern-name-text"><?php echo !empty($blousePatterns) ? htmlspecialchars($blousePatterns[0]['title']) : 'Blouse'; ?></span>' <?php _e('pattern_studio.in_inches'); ?>
                        </p>
                        <p class="measurements-form-subtitle"><?php _e('pattern_studio.see_image_reference'); ?></p>
                    </div>

                    <!-- ============================================================
                         THREE COLUMN LAYOUT: Measurement Fields
                         ============================================================ -->
                    <div class="pattern-studio-grid">

                        <!-- ------------------------------------------------
                             COLUMN 1: Measurement Guide Diagram
                             ------------------------------------------------ -->
                        <div class="diagram-column">
                            <h3><?php _e('pattern_studio.measurement_guide'); ?></h3>
                            <img id="measurement-diagram" src="../images/women_diagram.PNG" alt="<?php echo __('pattern_studio.measurement_guide'); ?>">
                        </div>

                        <!-- ------------------------------------------------
                             COLUMN 2: Fields 1-8 (Body Measurements)
                             ------------------------------------------------ -->
                        <div class="form-column" id="blouse-col1">
                            <div class="form-field">
                                <label for="blength"><?php _e('pattern_studio.fields.blouse_back_length'); ?></label>
                                <div class="input-wrapper">
                                    <input type="number" step="0.5" min="10" max="18" id="blength" name="blength"
                                        value="<?php echo $editMeasurement['blength'] ?? ''; ?>">
                                    <span class="unit-hint"><?php _e('pattern_studio.in_inches'); ?></span>
                                </div>
                                <span class="error-message" id="blength-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="fshoulder"><?php _e('pattern_studio.fields.full_shoulder'); ?></label>
                                <div class="input-wrapper">
                                    <input type="number" step="0.5" min="10" max="17" id="fshoulder" name="fshoulder"
                                        value="<?php echo $editMeasurement['fshoulder'] ?? ''; ?>">
                                    <span class="unit-hint"><?php _e('pattern_studio.in_inches'); ?></span>
                                </div>
                                <span class="error-message" id="fshoulder-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="shoulder"><?php _e('pattern_studio.fields.shoulder_strap'); ?></label>
                                <div class="input-wrapper">
                                    <input type="number" step="0.25" min="1" max="5" id="shoulder" name="shoulder"
                                        value="<?php echo $editMeasurement['shoulder'] ?? ''; ?>">
                                    <span class="unit-hint"><?php _e('pattern_studio.in_inches'); ?></span>
                                </div>
                                <span class="error-message" id="shoulder-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="bnDepth"><?php _e('pattern_studio.fields.back_neck_depth'); ?></label>
                                <input type="number" step="0.5" id="bnDepth" name="bnDepth"
                                    value="<?php echo $editMeasurement['bnDepth'] ?? ''; ?>">
                                <span class="error-message" id="bnDepth-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="fndepth"><?php _e('pattern_studio.fields.front_neck_depth'); ?></label>
                                <input type="number" step="0.5" id="fndepth" name="fndepth"
                                    value="<?php echo $editMeasurement['fndepth'] ?? ''; ?>">
                                <span class="error-message" id="fndepth-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="apex"><?php _e('pattern_studio.fields.shoulder_to_apex'); ?></label>
                                <input type="number" step="0.25" id="apex" name="apex"
                                    value="<?php echo $editMeasurement['apex'] ?? ''; ?>">
                                <span class="error-message" id="apex-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="flength"><?php _e('pattern_studio.fields.front_length'); ?></label>
                                <input type="number" step="0.5" id="flength" name="flength"
                                    value="<?php echo $editMeasurement['flength'] ?? ''; ?>">
                                <span class="error-message" id="flength-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="chest"><?php _e('pattern_studio.fields.upper_chest'); ?></label>
                                <input type="number" step="0.5" min="26" max="44" id="chest" name="chest"
                                    placeholder="<?php echo __('pattern_studio.fields.upper_chest_placeholder'); ?>" value="<?php echo $editMeasurement['chest'] ?? ''; ?>">
                                <span class="error-message" id="chest-error"></span>
                            </div>
                        </div>

                        <!-- ------------------------------------------------
                             COLUMN 3: Fields 9-14 + Notes + Submit
                             ------------------------------------------------ -->
                        <div class="form-column" id="blouse-col2">
                            <div class="form-field">
                                <label for="bust"><?php _e('pattern_studio.fields.bust_round'); ?></label>
                                <input type="number" step="0.5" min="28" max="50" id="bust" name="bust"
                                    value="<?php echo $editMeasurement['bust'] ?? ''; ?>">
                                <span class="error-message" id="bust-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="waist"><?php _e('pattern_studio.fields.waist_round'); ?></label>
                                <input type="number" step="0.5" min="26" max="42" id="waist" name="waist"
                                    value="<?php echo $editMeasurement['waist'] ?? ''; ?>">
                                <span class="error-message" id="waist-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="slength"><?php _e('pattern_studio.fields.sleeve_length'); ?></label>
                                <input type="number" step="0.5" id="slength" name="slength"
                                    value="<?php echo $editMeasurement['slength'] ?? ''; ?>">
                                <span class="error-message" id="slength-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="saround"><?php _e('pattern_studio.fields.arm_round'); ?></label>
                                <input type="number" step="0.5" id="saround" name="saround"
                                    value="<?php echo $editMeasurement['saround'] ?? ''; ?>">
                                <span class="error-message" id="saround-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="sopen"><?php _e('pattern_studio.fields.sleeve_end_round'); ?></label>
                                <input type="number" step="0.5" id="sopen" name="sopen"
                                    value="<?php echo $editMeasurement['sopen'] ?? ''; ?>">
                                <span class="error-message" id="sopen-error"></span>
                            </div>
                            <div class="form-field">
                                <label for="armhole"><?php _e('pattern_studio.fields.armhole'); ?></label>
                                <input type="number" step="0.5" id="armhole" name="armhole"
                                    value="<?php echo $editMeasurement['armhole'] ?? ''; ?>">
                                <span class="error-message" id="armhole-error"></span>
                            </div>
                            <div class="form-field notes-inline">
                                <label for="notes"><?php _e('pattern_studio.fields.additional_notes'); ?></label>
                                <textarea id="notes" name="notes" rows="2"><?php echo $editMeasurement['notes'] ?? ''; ?></textarea>
                            </div>
                            <div class="form-field submit-field">
                                <button type="submit" id="submitBtn" class="btn-primary">
                                    <?php echo $editMeasurement ? __('pattern_studio.update_measurements') : __('pattern_studio.generate_pattern'); ?>
                                </button>
                            </div>
                        </div>

                    </div><!-- /.pattern-studio-grid -->

                </form><!-- /#measurements-form -->

            </div><!-- /.hero-content -->
        </div><!-- /.hero-container -->
    </section>

    <!-- Footer Include -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <!-- ================================================================
         JAVASCRIPT
         ================================================================ -->
    <script>
        // ====================================================================
        // NAVBAR SCROLL EFFECT
        // ====================================================================
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // ====================================================================
        // INITIALIZE LUCIDE ICONS
        // ====================================================================
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });

        // ====================================================================
        // BLOUSE PATTERN SELECTION
        // ====================================================================
        function selectBlousePattern(element) {
            // Remove selected class from all pattern items
            const allItems = document.querySelectorAll('.blouse-pattern-item');
            allItems.forEach(item => item.classList.remove('selected'));

            // Add selected class to clicked item
            element.classList.add('selected');

            // Update hidden input with selected pattern ID
            const patternId = element.getAttribute('data-pattern-id');
            const patternTitle = element.getAttribute('data-pattern-title');
            document.getElementById('selected_pattern_id').value = patternId;

            // Update the pattern name in the heading
            const patternNameText = document.getElementById('pattern-name-text');
            if (patternNameText) {
                patternNameText.textContent = patternTitle;
            }
        }

        // ====================================================================
        // FORM FUNCTIONALITY
        // ====================================================================
        document.addEventListener('DOMContentLoaded', function() {

            // ----------------------------------------------------------------
            // CUSTOMER DETAILS TOGGLE
            // Shows/hides customer name and mobile fields
            // ----------------------------------------------------------------
            const measurementSelf = document.getElementById('measurement-self');
            const measurementCustomer = document.getElementById('measurement-customer');
            const customerDetailsGroup = document.getElementById('customer-details-group');
            const customerNameInput = document.getElementById('customer_name');

            function toggleCustomerDetails() {
                if (measurementCustomer.checked) {
                    customerDetailsGroup.classList.add('visible');
                    customerNameInput.required = true;
                } else {
                    customerDetailsGroup.classList.remove('visible');
                    customerNameInput.required = false;
                    customerNameInput.value = '';
                    document.getElementById('customer_reference').value = '';
                }
            }

            measurementSelf.addEventListener('change', toggleCustomerDetails);
            measurementCustomer.addEventListener('change', toggleCustomerDetails);

            // ----------------------------------------------------------------
            // AUTO-ROUNDING FOR MEASUREMENT INPUTS
            // Rules:
            // - Decimal < 0.5 → round to 0.5 (e.g., 14.3 → 14.5)
            // - Decimal > 0.5 → round up to next whole (e.g., 14.7 → 15.0)
            // - Decimal = 0.5 or whole number → keep as is
            // ----------------------------------------------------------------
            function roundMeasurement(value) {
                if (value === '' || isNaN(value)) return value;

                const num = parseFloat(value);
                const wholePart = Math.floor(num);
                const decimalPart = num - wholePart;

                if (decimalPart === 0) {
                    // Already a whole number
                    return num.toFixed(1);
                } else if (decimalPart === 0.5) {
                    // Already at 0.5
                    return num.toFixed(1);
                } else if (decimalPart < 0.5) {
                    // Round to 0.5
                    return (wholePart + 0.5).toFixed(1);
                } else {
                    // Round up to next whole number
                    return (wholePart + 1).toFixed(1);
                }
            }

            // Fields exempt from 0.5" rounding (use 0.25" instead)
            const quarterInchFields = ['shoulder', 'apex'];

            // Round to nearest 0.25" for exempt fields
            function roundToQuarterInch(value) {
                if (value === '' || isNaN(value)) return value;
                const num = parseFloat(value);
                return (Math.round(num * 4) / 4).toFixed(2);
            }

            // Apply rounding to all measurement input fields
            const measurementInputs = document.querySelectorAll('#blouse-col1 input[type="number"], #blouse-col2 input[type="number"]');
            measurementInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value !== '') {
                        // Check if this field should use 0.25" rounding
                        if (quarterInchFields.includes(this.id)) {
                            this.value = roundToQuarterInch(this.value);
                        } else {
                            const rounded = roundMeasurement(this.value);
                            this.value = rounded;
                        }
                    }
                });
            });

            // ----------------------------------------------------------------
            // PATTERN TYPE TOGGLE (Show/Hide Blouse Subtypes & Heading)
            // ----------------------------------------------------------------
            const patternBlouse = document.getElementById('pattern-blouse');
            const patternKurti = document.getElementById('pattern-kurti');
            const patternPants = document.getElementById('pattern-pants');
            const patternSkirt = document.getElementById('pattern-skirt');
            const blouseSubtypes = document.getElementById('blouse-subtypes');
            const patternHeading = document.getElementById('selected-pattern-heading');

            function toggleBlouseSubtypes() {
                if (patternBlouse && patternBlouse.checked) {
                    blouseSubtypes.style.display = 'flex';
                    if (patternHeading) patternHeading.style.display = 'block';
                } else {
                    blouseSubtypes.style.display = 'none';
                    if (patternHeading) patternHeading.style.display = 'none';
                }
            }

            // Add event listeners to pattern type radio buttons
            if (patternBlouse) patternBlouse.addEventListener('change', toggleBlouseSubtypes);
            if (patternKurti) patternKurti.addEventListener('change', toggleBlouseSubtypes);
            if (patternPants) patternPants.addEventListener('change', toggleBlouseSubtypes);
            if (patternSkirt) patternSkirt.addEventListener('change', toggleBlouseSubtypes);

            // Initialize on page load
            toggleBlouseSubtypes();

            // ----------------------------------------------------------------
            // FORM VALIDATION
            // ----------------------------------------------------------------
            const form = document.getElementById('measurements-form');

            function clearError(inputId) {
                const input = document.getElementById(inputId);
                const errorSpan = document.getElementById(inputId + '-error');
                if (input) input.classList.remove('input-error');
                if (errorSpan) errorSpan.textContent = '';
            }

            function showError(inputId, message) {
                const input = document.getElementById(inputId);
                const errorSpan = document.getElementById(inputId + '-error');
                if (input) input.classList.add('input-error');
                if (errorSpan) errorSpan.textContent = message;
            }

            // Translation strings for JavaScript validation
            const validationStrings = {
                required: '<?php echo __('pattern_studio.validation.required'); ?>',
                minValue: '<?php echo __('pattern_studio.validation.min_value'); ?>',
                maxValue: '<?php echo __('pattern_studio.validation.max_value'); ?>'
            };

            function validateField(input) {
                const value = input.value.trim();
                const min = input.getAttribute('min');
                const max = input.getAttribute('max');
                const fieldId = input.id;

                clearError(fieldId);

                if (!value || value === '') {
                    showError(fieldId, validationStrings.required);
                    return false;
                }

                if (input.type === 'number') {
                    const numValue = parseFloat(value);
                    if (min && numValue < parseFloat(min)) {
                        showError(fieldId, validationStrings.minValue.replace('{min}', min));
                        return false;
                    }
                    if (max && numValue > parseFloat(max)) {
                        showError(fieldId, validationStrings.maxValue.replace('{max}', max));
                        return false;
                    }
                }

                return true;
            }

            // Clear errors on input change
            form.querySelectorAll('input[type="number"], input[type="text"], select').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        clearError(this.id);
                    }
                });
                input.addEventListener('change', function() {
                    if (this.value.trim() !== '') {
                        clearError(this.id);
                    }
                });
            });

            // ----------------------------------------------------------------
            // FORM SUBMIT HANDLER
            // Validates all fields and prevents double submission
            // ----------------------------------------------------------------
            form.addEventListener('submit', function(e) {
                // Clear all previous errors
                form.querySelectorAll('.error-message').forEach(span => span.textContent = '');
                form.querySelectorAll('.input-error').forEach(input => input.classList.remove('input-error'));

                let hasErrors = false;
                let firstErrorField = null;

                // Validate all visible inputs in both columns
                const inputs = form.querySelectorAll('#blouse-col1 input[type="number"], #blouse-col2 input[type="number"], #blouse-col2 select');
                inputs.forEach(input => {
                    if (!input.disabled && !validateField(input)) {
                        hasErrors = true;
                        if (!firstErrorField) firstErrorField = input;
                    }
                });

                // Validate customer name if customer is selected
                if (measurementCustomer && measurementCustomer.checked) {
                    if (!customerNameInput.value.trim()) {
                        showError('customer_name', 'Required');
                        hasErrors = true;
                        if (!firstErrorField) firstErrorField = customerNameInput;
                    }
                }

                if (hasErrors) {
                    e.preventDefault();
                    if (firstErrorField) {
                        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstErrorField.focus();
                    }
                    return false;
                }

                // Prevent double submission
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.textContent = '<?php echo __('pattern_studio.generating'); ?>';

                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '<?php echo $editMeasurement ? __('pattern_studio.update_measurements') : __('pattern_studio.generate_pattern'); ?>';
                }, 3000);

                return true;
            });

        });
    </script>
</body>
</html>
