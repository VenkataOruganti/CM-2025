<?php
/**
 * =============================================================================
 * SAVI BLOUSE COMPLETE PATTERN - Combined Pattern Display
 * =============================================================================
 *
 * This file combines all 4 pattern components (Front, Back, Sleeve, Patti)
 * into a single display page with download functionality.
 *
 * Data Sources:
 * - Mode 1: From URL parameter (customer_id) - loads from database via customers table
 *           Used by: dashboard-boutique.php, pattern-preview.php
 * - Mode 2: From URL parameter (id) - loads from database via measurements table
 *           Used by: direct measurement links
 * - Mode 3: From session variables (set by saviMeasure.php form submission)
 *           Used by: savi.php measurement form
 *
 * Session Variables Set (for download files):
 * - Front: saviBlouseFront, saviFrontBlouseGreen, saviFrontBlouseBrown,
 *          saviBlouseFrontRed, saviFlTucks, saviFbTucks, saviFrTucks,
 *          rightFrTucks, vApex, hApex, fbDart
 * - Back: saviBackBlack, saviBackGreen, saviBackBrown, saviBackRed,
 *         saviBackTucks, backVApex, backHApex, bbPoint11, bbPoint12
 * - Sleeve: saviBBlack, saviBGray, saviBRed, centerLine
 * - Patti: saviPattiBlack, saviPattiRed
 * =============================================================================
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================================================
// SECTION 1: DATA RETRIEVAL - Get measurements from database or session
// =============================================================================

$cIn = 25.4;  // Conversion: 1 inch = 25.4mm
$dataLoaded = false;
$errorMessage = '';

// Initialize all 14 measurement variables
$cust = '';
$shoulder = $fshoulder = $bnDepth = $blength = $waist = $chest = 0;
$bust = $flength = $fndepth = $apex = 0;
$slength = $saround = $sopen = $armhole = 0;

// Helper function to store measurements in session
function storeMeasurementsInSession($cust, $shoulder, $fshoulder, $bnDepth, $blength, $waist, $chest, $bust, $flength, $fndepth, $apex, $slength, $saround, $sopen, $armhole, $cIn) {
    $_SESSION["cust"] = $cust;
    $_SESSION["shoulder"] = $shoulder;
    $_SESSION["shoulder1"] = $shoulder * $cIn;
    $_SESSION["fshoulder"] = $fshoulder;
    $_SESSION["fshoulder1"] = $fshoulder * $cIn;
    $_SESSION["bnDepth"] = $bnDepth;
    $_SESSION["bnDepth1"] = $bnDepth * $cIn;
    $_SESSION["blength"] = $blength;
    $_SESSION["blength1"] = $blength * $cIn;
    $_SESSION["waist"] = $waist;
    $_SESSION["waist1"] = $waist * $cIn;
    $_SESSION["chest"] = $chest;
    $_SESSION["chest1"] = $chest * $cIn;
    $_SESSION["bust"] = $bust;
    $_SESSION["bust1"] = $bust * $cIn;
    $_SESSION["flength"] = $flength;
    $_SESSION["flength1"] = $flength * $cIn;
    $_SESSION["fndepth"] = $fndepth;
    $_SESSION["fndepth1"] = $fndepth * $cIn;
    $_SESSION["apex"] = $apex;
    $_SESSION["apex1"] = $apex * $cIn;
    $_SESSION["slength"] = $slength;
    $_SESSION["slength1"] = $slength * $cIn;
    $_SESSION["saround"] = $saround;
    $_SESSION["saround1"] = $saround * $cIn;
    $_SESSION["sopen"] = $sopen;
    $_SESSION["sopen1"] = $sopen * $cIn;
    $_SESSION["armhole"] = $armhole;
    $_SESSION["armhole1"] = $armhole * $cIn;

    // Set measurement summary strings
    $_SESSION["measure"] = "Shoulder: " . $shoulder . ", Full Shoulder: " . $fshoulder . ", BackNeck Depth: " . $bnDepth;
    $_SESSION["measure1"] = "Back Length: " . $blength . ", Waist: " . $waist . ", Chest: " . $chest;
    $_SESSION["measure2"] = "Front Length: " . $flength . ", Front Neck Depth: " . $fndepth . ", Shoulder to Apex: " . $apex;
    $_SESSION["measure3"] = "Sleeve Length: " . $slength . ", Sleeve Open: " . $sopen . ", Sleeve Round: " . $saround . ", Arm Hole: " . $armhole;
}

// Mode 1: Load from database via customer_id (from dashboard-boutique.php / pattern-preview.php)
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];

    try {
        require_once __DIR__ . '/../../../config/database.php';
        global $pdo;

        if ($customerId === 'self') {
            // Load self measurements for logged-in user - get actual username
            $userId = $_SESSION['user_id'] ?? 0;
            $stmt = $pdo->prepare("
                SELECT m.*, u.username as customer_name
                FROM measurements m
                JOIN users u ON m.user_id = u.id
                WHERE m.user_id = ? AND m.measurement_of = 'self'
                ORDER BY m.created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
        } else {
            // Load customer measurements - get latest measurement for this customer
            $stmt = $pdo->prepare("
                SELECT m.*, c.customer_name
                FROM measurements m
                JOIN customers c ON m.customer_id = c.id
                WHERE m.customer_id = ?
                ORDER BY m.created_at DESC LIMIT 1
            ");
            $stmt->execute([$customerId]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $cust = $row['customer_name'] ?? 'Customer';
            $shoulder = floatval($row['shoulder'] ?? 0);
            $fshoulder = floatval($row['fshoulder'] ?? 0);
            $bnDepth = floatval($row['bnDepth'] ?? 0);
            $blength = floatval($row['blength'] ?? 0);
            $waist = floatval($row['waist'] ?? 0);
            $chest = floatval($row['chest'] ?? 0);
            $bust = floatval($row['bust'] ?? 0);
            $flength = floatval($row['flength'] ?? 0);
            $fndepth = floatval($row['fndepth'] ?? 0);
            $apex = floatval($row['apex'] ?? 0);
            $slength = floatval($row['slength'] ?? 0);
            $saround = floatval($row['saround'] ?? 0);
            $sopen = floatval($row['sopen'] ?? 0);
            $armhole = floatval($row['armhole'] ?? 0);

            storeMeasurementsInSession($cust, $shoulder, $fshoulder, $bnDepth, $blength, $waist, $chest, $bust, $flength, $fndepth, $apex, $slength, $saround, $sopen, $armhole, $cIn);
            $dataLoaded = true;
        } else {
            $errorMessage = "No measurements found for customer ID: $customerId";
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        error_log("saviComplete.php - customer_id error: " . $e->getMessage());
    }
}

// Mode 2: Load from database via measurement_id URL parameter (direct link)
if (!$dataLoaded && isset($_GET['id']) && !empty($_GET['id'])) {
    $measurementId = intval($_GET['id']);

    try {
        require_once __DIR__ . '/../../../config/database.php';
        global $pdo;

        // Join with users table to get username for self measurements
        // Join with customers table to get customer_name for customer measurements
        $stmt = $pdo->prepare("
            SELECT m.*,
                   COALESCE(c.customer_name, m.customer_name, u.username, 'Customer') as customer_name,
                   m.measurement_of
            FROM measurements m
            LEFT JOIN customers c ON m.customer_id = c.id
            LEFT JOIN users u ON m.user_id = u.id
            WHERE m.id = ?
        ");
        $stmt->execute([$measurementId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // For self measurements, use username; for customer measurements, use customer_name
            $cust = $row['customer_name'] ?? 'Customer';
            if ($row['measurement_of'] === 'self' && !empty($row['customer_name'])) {
                $cust = $row['customer_name']; // This will be the username from COALESCE
            }
            $shoulder = floatval($row['shoulder'] ?? 0);
            $fshoulder = floatval($row['fshoulder'] ?? 0);
            $bnDepth = floatval($row['bnDepth'] ?? 0);
            $blength = floatval($row['blength'] ?? 0);
            $waist = floatval($row['waist'] ?? 0);
            $chest = floatval($row['chest'] ?? 0);
            $bust = floatval($row['bust'] ?? 0);
            $flength = floatval($row['flength'] ?? 0);
            $fndepth = floatval($row['fndepth'] ?? 0);
            $apex = floatval($row['apex'] ?? 0);
            $slength = floatval($row['slength'] ?? 0);
            $saround = floatval($row['saround'] ?? 0);
            $sopen = floatval($row['sopen'] ?? 0);
            $armhole = floatval($row['armhole'] ?? 0);

            storeMeasurementsInSession($cust, $shoulder, $fshoulder, $bnDepth, $blength, $waist, $chest, $bust, $flength, $fndepth, $apex, $slength, $saround, $sopen, $armhole, $cIn);
            $dataLoaded = true;
        } else {
            $errorMessage = "Measurement ID $measurementId not found in database.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        error_log("saviComplete.php - measurement_id error: " . $e->getMessage());
    }
}

// Mode 3: Load from session (set by saviMeasure.php form submission in savi.php)
// Check if any of the key session variables are set with valid values
if (!$dataLoaded) {
    // Check for session variables - they may be set as strings or numbers
    $sessionChest = $_SESSION["chest"] ?? 0;

    // Convert to float for comparison (handles both string and numeric values)
    if (is_numeric($sessionChest) && floatval($sessionChest) > 0) {
        $cust = $_SESSION["cust"] ?? 'Customer';
        $shoulder = floatval($_SESSION["shoulder"] ?? 0);
        $fshoulder = floatval($_SESSION["fshoulder"] ?? 0);
        $bnDepth = floatval($_SESSION["bnDepth"] ?? 0);
        $blength = floatval($_SESSION["blength"] ?? 0);
        $waist = floatval($_SESSION["waist"] ?? 0);
        $chest = floatval($_SESSION["chest"] ?? 0);
        $bust = floatval($_SESSION["bust"] ?? 0);
        $flength = floatval($_SESSION["flength"] ?? 0);
        $fndepth = floatval($_SESSION["fndepth"] ?? 0);
        $apex = floatval($_SESSION["apex"] ?? 0);
        $slength = floatval($_SESSION["slength"] ?? 0);
        $saround = floatval($_SESSION["saround"] ?? 0);
        $sopen = floatval($_SESSION["sopen"] ?? 0);
        $armhole = floatval($_SESSION["armhole"] ?? 0);

        $dataLoaded = true;
    }
}

// If no data loaded, silently return - the parent page (savi.php) handles
// showing/hiding the design section via JavaScript based on form values
if (!$dataLoaded) {
    // Don't show error - this is normal on initial page load before form submission
    // The JavaScript in savi.php will hide this div anyway
    return;
}

// =============================================================================
// SECTION 2: DEEP NECK CHEST VERTICAL CALCULATION
// Include the full deepNeckCV.php for accurate armhole-based calculations
// =============================================================================

include __DIR__ . '/../inc/deepNeckCV.php';

// Create calculated variables for pattern (if not already set by deepNeckCV.php)
$shoulder1 = $shoulder * $cIn;
$fshoulder1 = $fshoulder * $cIn;
$fndepth1 = $fndepth * $cIn;
$flength1 = $flength * $cIn;
$waist1 = $waist * $cIn;

// =============================================================================
// SECTION 3: FRONT PATTERN CALCULATIONS (from saviFront.php logic)
// =============================================================================

$topMargin = 0;
$topPadding = (0.25 * $cIn);
$fLeft = 0;
$seam = (0.3 * $cIn);

$bDart = (($chest - $waist) / 2);
$_SESSION["fbDart"] = $bDart;

// Vertical Apex calculation
$vApex = (($apex + 0.5) * $cIn);
$_SESSION["vApex"] = $vApex;

$bustVar = ($bust - ($chest / 2)) / 2;

// Center tuck calculation
$legWidth = ($bustVar - ($waist / 4));
$legWidth = $legWidth / 2;

// Horizontal Apex (Bust Point) Calculation
if (($bust >= 30) && ($bust <= 32)) {
    $hApex = 3.25 * $cIn;
} elseif (($bust > 32) && ($bust <= 35)) {
    $hApex = 3.5 * $cIn;
} elseif (($bust > 35) && ($bust <= 38)) {
    $hApex = 3.75 * $cIn;
} elseif (($bust >= 39) && ($bust <= 41)) {
    $hApex = 4 * $cIn;
} elseif (($bust >= 41) && ($bust <= 44)) {
    $hApex = 4.25 * $cIn;
} else {
    $hApex = 3.5 * $cIn;
}
$_SESSION["hApex"] = $hApex;

// Gray dotted line (reference line)
$fnt_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
$fnt_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
$fnt_point3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
$fnt_point4 = "L" . $fLeft . "," . $fndepth1;
$fnt_point5 = "L" . $fLeft . "," . (($flength - 1.0) * $cIn);

$fnt_point6 = "Q" . (($waist1 / 4) / 4) . "," . ($flength1 + (0.5 * $cIn)) . "," . $hApex . "," . ($flength1 + (0.5 * $cIn));

$fnt_point7 = "L" . ((($waist / 4) + ($legWidth * 2)) * $cIn) . "," . (($flength - 0.5) * $cIn);
$fnt_point8 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn));
$fnt_point9 = "L" . (($chest / 4) * $cIn) . "," . $chestVertical;
$fnt_point10 = "L" . ($fshoulder1 / 2) . "," . $chestVertical;
$fnt_point11 = "L" . ($fshoulder1 / 2) . "," . $seam;

$saviFrontBlouseGray = $fnt_point1 . $fnt_point2 . $fnt_point3 . $fnt_point4 . $fnt_point5 . $fnt_point6 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . $fnt_point11 . "Z";
$_SESSION["saviBlouseFront"] = $saviFrontBlouseGray;

// Black line (stitch line)
$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);

if ($bnDepth <= 4.5) {
    $sg_point2 = "";
    $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1 . "," . $seam . "," . $fndepth1;
} else {
    $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
    $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1 . "," . $seam . "," . $fndepth1;
}

$sg_point5 = "L" . $fLeft . "," . ($apex * $cIn);
$sg_point5a = "L" . $fLeft . "," . ((($flength - 1.0) * $cIn));

$sg_point6 = "Q" . (($waist1 / 4) / 4) . "," . ($flength1 + (0.5 * $cIn)) . "," . $hApex . "," . ($flength1 + (0.5 * $cIn));

$sg_point7 = "L" . ((($waist / 4) + ($legWidth * 2)) * $cIn) . "," . (($flength - 0.5) * $cIn);
$sg_point8 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn));
$sg_point9 = "L" . ((($chest / 4) + 0.5) * $cIn) . "," . $chestVertical;

// Front Shoulder bottom curve calculation
if (($chest > 28) && ($chest <= 32)) {
    $frontChestVertical = $chestVertical / 2;
} elseif (($chest > 32) && ($chest <= 38)) {
    $frontChestVertical = ($chestVertical / 2 + (0.5 * $cIn));
} elseif ($chest > 38) {
    $frontChestVertical = ($chestVertical / 2 + (1 * $cIn));
} else {
    $frontChestVertical = $chestVertical / 2;
}

$sg_point10 = "Q" . (($fshoulder1 / 2) - (0.4 * $cIn)) . "," . ($chestVertical + (0.2 * $cIn)) . "," . (($fshoulder1 / 2) - (0.4 * $cIn)) . "," . $frontChestVertical;
$sg_point11 = "L" . ($fshoulder1 / 2) . "," . ($topPadding + (0.25 * $cIn));
$sg_point12 = "L" . ((($fshoulder / 2) - $shoulder) * $cIn) . "," . $topPadding;

$saviFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point4 . $sg_point5 . $sg_point5a . $sg_point6 . $sg_point7 . $sg_point8 . $sg_point9 . $sg_point10 . $sg_point11 . $sg_point12 . "Z";
$_SESSION["saviFrontBlouseGreen"] = $saviFrontBlouseGreen;

// Brown dotted line (extra bust design)
$green_point8 = "M" . ((($chest / 4) + 0.5) * $cIn) . "," . ($flength1 + (0.5 * $cIn));
$green_point9 = "L" . ((($chest / 4) + 1) * $cIn) . "," . $chestVertical;
$saviFrontBlouseBrown = $green_point8 . $green_point9;
$_SESSION["saviFrontBlouseBrown"] = $saviFrontBlouseBrown;

// Red dotted line (seam allowance)
$fnt_point1 = "M" . ((($fshoulder1 / 2) - $shoulder1) - ($seam)) . "," . $topMargin;

if ($bnDepth <= 4.5) {
    $fnt_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
    $fnt_point3 = "Q" . (($fshoulder1 / 2) - ($shoulder1 + (1.5 * $cIn))) . "," . $fndepth1 . "," . $seam . "," . $fndepth1;
} else {
    $fnt_point2 = "L" . (($fshoulder1 / 2) - ($shoulder1 + (0.3 * $cIn))) . "," . ($fndepth1 / 2);
    $fnt_point3 = "Q" . ((($fshoulder1 / 2) - $shoulder1) - (0.5 * $cIn)) . "," . $fndepth1 . "," . ($seam - (0.3 * $cIn)) . "," . ($fndepth1 - (0.3 * $cIn));
}

$fnt_point4 = "L" . $fLeft . "," . ($flength1 - (0.5 * $cIn));
$fnt_point5 = "Q" . (($waist1 / 4) / 4) . "," . ($flength1 + (1 * $cIn)) . "," . $hApex . "," . (($flength + 1) * $cIn);
$fnt_point6 = "L" . ((($waist / 4) + ($legWidth * 2) + 0.5) * $cIn) . "," . ($flength * $cIn);
$fnt_point7 = "L" . (($bustVar + 0.5) * $cIn) . "," . $vApex;
$fnt_point8 = "L" . ((($chest / 4) + 1) * $cIn) . "," . ($chestVertical - (0.5 * $cIn));
$fnt_point9 = "Q" . ((($fshoulder / 2) - 1.5) * $cIn) . "," . ($chestVertical + (0.5 * $cIn)) . "," . ((($fshoulder / 2) + 0.5) * $cIn) . "," . $topMargin;

$saviFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point3 . $fnt_point4 . $fnt_point5 . $fnt_point6 . $fnt_point7 . $fnt_point8 . $fnt_point9 . "Z";
$_SESSION["saviBlouseFrontRed"] = $saviFrontBlouseRed;

// Front Left Tuck
$saviFrontLeftTucks = "M" . $fLeft . "," . (($apex - 0.3) * $cIn) .
    "L" . ($hApex - (1 * $cIn)) . "," . ($vApex - (0.25 * $cIn)) .
    "L" . $fLeft . "," . (($apex + 0.3) * $cIn);
$_SESSION["saviFlTucks"] = $saviFrontLeftTucks;

// Front Bottom Tuck
$legWidth = $legWidth + 0.25;
$saviFBT01 = "M" . ($hApex - ($legWidth * $cIn)) . "," . ($flength1 + $seam);
$saviFBT02 = "L" . ($hApex + (0.1 * $cIn)) . "," . (($apex + 1.0) * $cIn);
$saviFBT03 = "L" . ($hApex + ($legWidth * $cIn)) . "," . ($flength1 + $seam);
$saviFrontBottomTucks = $saviFBT01 . $saviFBT02 . $saviFBT03;
$_SESSION["saviFbTucks"] = $saviFrontBottomTucks;

// Front Right Tuck
$frontRight1 = "M" . (($fshoulder1 / 2) + (0.5 * $cIn)) . "," . ($chestVertical - (1 * $cIn));

switch ($apex) {
    case 6: $frontRight2 = "L" . ($hApex + (1 * $cIn)) . "," . ($apex * $cIn); break;
    case 7: $frontRight2 = "L" . ($hApex + (1.2 * $cIn)) . "," . ($vApex - (1 * $cIn)); break;
    case 8: $frontRight2 = "L" . ($hApex + (1 * $cIn)) . "," . ($vApex - (1 * $cIn)); break;
    case 9: $frontRight2 = "L" . ($hApex + (1 * $cIn)) . "," . ($vApex - (1 * $cIn)); break;
    case 10: $frontRight2 = "L" . ($hApex + (0.5 * $cIn)) . "," . ($vApex - (1.2 * $cIn)); break;
    case 11: $frontRight2 = "L" . ($hApex + (0.7 * $cIn)) . "," . ($vApex - (1 * $cIn)); break;
    case 12: $frontRight2 = "L" . ($hApex + (0.6 * $cIn)) . "," . ($vApex - (1.2 * $cIn)); break;
    case 13: $frontRight2 = "L" . ($hApex + (0.6 * $cIn)) . "," . ($vApex - (1.2 * $cIn)); break;
    default: $frontRight2 = "L" . ($hApex + (0.5 * $cIn)) . "," . (($apex - 1) * $cIn);
}

$frontRight3 = "L" . (($fshoulder1 / 2) + (1 * $cIn)) . "," . ($chestVertical - (0.6 * $cIn));
$saviFrontRightTucks = $frontRight1 . $frontRight2 . $frontRight3;
$_SESSION["saviFrTucks"] = $saviFrontRightTucks;

// Right Center Tuck
$rCenter1 = "M" . ($hApex + (2 * $cIn)) . "," . ($vApex - (0.25 * $cIn));
$rCenter2 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn));
$rCenter3 = "M" . ($bustVar * $cIn) . "," . ($vApex + (0.25 * $cIn));
$rCenter4 = "L" . ($hApex + (2 * $cIn)) . "," . ($vApex - (0.25 * $cIn));
$rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
$_SESSION["rightFrTucks"] = $rightCenter;

// =============================================================================
// SECTION 4: BACK PATTERN CALCULATIONS (from saviBack.php logic)
// =============================================================================

$seam = 0.3 * $cIn;
$neckSeam = 0.2;
$backDart = 1;
$mLeft = 1;
$topMargin = 0;

$backTuckText = $cust;

// Back pattern - Gray reference line
$bbPoint1 = "M" . (((($fshoulder / 2) - $shoulder) * $cIn) + $seam) . "," . $topPadding;
$bbPoint2 = "L" . (((($fshoulder / 2) - $shoulder) * $cIn) + $seam) . "," . ($topMargin + ($bnDepth / 2) * $cIn);
$bbPoint3 = "L" . (((($fshoulder / 2) - $shoulder) * $cIn) + $seam) . "," . ($topMargin + ($bnDepth * $cIn));
$bbPoint4 = "L" . $seam . "," . (($topMargin + $bnDepth) * $cIn);
$bbPoint5 = "L" . $seam . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$bbPoint6 = "L" . ((((($waist / 4) + $backDart)) * $cIn) + $seam) . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$bbPoint7 = "L" . ((($chest / 4) * $cIn) + $seam) . "," . $chestVertical;
$bbPoint8 = "L" . ((($fshoulder / 2) * $cIn) + $seam) . "," . $chestVertical;
$bbPoint9 = "L" . ((($fshoulder / 2) * $cIn) + $seam) . "," . (($fshoulder / 4) * $cIn);
$bbPoint10 = "L" . ((($fshoulder / 2) * $cIn) + $seam) . "," . ($topPadding * 2);

$saviBackBlack = $bbPoint1 . $bbPoint2 . $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
$_SESSION["saviBackBlack"] = $saviBackBlack;

// Back pattern - Black stitch line
$sb_point1 = "M" . (((($fshoulder / 2) - $shoulder) * $cIn) + $seam) . "," . (0.3 * $cIn);

if ($bnDepth <= 4.5) {
    $sb_point2 = "";
    $sb_point3 = "Q" . ((((($fshoulder / 2) - $shoulder) + 0.3) * $cIn) + $seam) . "," . ($bnDepth * $cIn) . "," . $seam . "," . ($bnDepth * $cIn);
} else {
    $sb_point2 = "L" . ((((($fshoulder / 2) - $shoulder) + 0.2) * $cIn) + $seam) . "," . ($topMargin + ($bnDepth / 2) * $cIn);
    $sb_point3 = "Q" . ((((($fshoulder / 2) - $shoulder) + 0.3) * $cIn) + $seam) . "," . ($bnDepth * $cIn) . "," . $seam . "," . ($bnDepth * $cIn);
}

$sb_point4 = "L" . $seam . "," . ($bnDepth * $cIn);
$sb_point5 = "M" . $seam . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$sb_point6 = "L" . ((((($waist / 4) + $backDart)) * $cIn) + $seam) . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$sb_point7 = "L" . ((($chest / 4) * $cIn) + $seam) . "," . $chestVertical;
$sb_point7a = "L" . (($chest / 4) * $cIn) . "," . $chestVertical;

// Back chest vertical curve
if (($chest > 28) && ($chest <= 32)) {
    $backFrontChestVertical = $chestVertical / 2;
} elseif (($chest > 32) && ($chest <= 38)) {
    $backFrontChestVertical = ($chestVertical / 2 + (0.5 * $cIn));
} elseif ($chest > 38) {
    $backFrontChestVertical = ($chestVertical / 2 + (1 * $cIn));
} else {
    $backFrontChestVertical = $chestVertical / 2;
}

$sb_point8 = "Q" . ((($fshoulder / 2) * $cIn) + $seam) . "," . ($chestVertical - (0.2 * $cIn)) . "," . ((($fshoulder / 2) * $cIn) + $seam) . "," . $backFrontChestVertical;
$sb_point9 = "L" . ((($fshoulder / 2) * $cIn) + $seam) . "," . (($fshoulder / 4) * $cIn);
$sb_point10 = "L" . ((($fshoulder / 2) * $cIn) + $seam) . "," . (0.5 * $cIn);
$sb_point11 = "L" . (((($fshoulder / 2) - $shoulder) * $cIn) + $seam) . "," . (0.25 * $cIn);

$saviBackGreen = $sb_point1 . $sb_point2 . $sb_point3 . $sb_point5 . $sb_point6 . $sb_point7 . $sb_point7a . $sb_point8 . $sb_point9 . $sb_point10 . $sb_point11;
$_SESSION["saviBackGreen"] = $saviBackGreen;

// Back pattern - Brown line (extra bust design)
$brownPoint6 = "M" . (((($waist / 4) + 0.5) * $cIn) + $seam) . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$brownPoint7 = "L" . (((($chest / 4) - 0.5) * $cIn) + $seam) . "," . $chestVertical;
$saviBackBrown = $brownPoint6 . $brownPoint7;
$_SESSION["saviBackBrown"] = $saviBackBrown;

// Back pattern - Red line (seam allowance)
$point1 = "M" . (((($fshoulder / 2) - $shoulder) * $cIn) - ($seam - (0.25 * $cIn))) . "," . $topMargin;

if ($bnDepth <= 4.5) {
    $point2 = "";
    $point3 = "Q" . ((((($fshoulder / 2) - $shoulder)) * $cIn) - $seam) . "," . ($bnDepth * $cIn) . "," . $seam . "," . (($bnDepth * $cIn) - $seam);
} else {
    $point2 = "L" . (((($fshoulder / 2) - $shoulder) * $cIn) - ($seam - (0.5 * $cIn))) . "," . ($topMargin + ($bnDepth / 2) * $cIn);
    $point3 = "Q" . ((((($fshoulder / 2) - $shoulder) + 0.5) * $cIn) - $seam) . "," . ($bnDepth * $cIn) . "," . $seam . "," . (($bnDepth * $cIn) - $seam);
}

$point4 = "L" . $seam . "," . ($bnDepth * $cIn);
$point5 = "M" . $seam . "," . ((($topMargin + $blength) + 1) * $cIn);
$point6 = "L" . (((($waist / 4) + $backDart) + 1.0) * $cIn) . "," . (((($topMargin + $blength) + 1)) * $cIn);
$point7 = "L" . ((($chest / 4) + 1.0) * $cIn) . "," . (($chestVertical) - (0.3 * $cIn));
$point8 = "Q" . ((($fshoulder / 2) + 0.75) * $cIn) . "," . (($chestVertical) - (0.25 * $cIn)) . "," . (((($fshoulder / 2) + 0.5) * $cIn) + $seam) . "," . ($chestVertical / 2);
$point9 = "L" . (((($fshoulder / 2) + 0.5) * $cIn) + $seam) . "," . (($fshoulder / 4) * $cIn);
$point10 = "L" . (((($fshoulder / 2) + 0.5) * $cIn) + $seam) . "," . $topPadding;
$point11 = "L" . ((($fshoulder / 2) - $shoulder) * $cIn) . "," . $topMargin;

$saviBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 . $point10 . $point11;
$_SESSION["saviBackRed"] = $saviBackRed;

// Cut marks
$_SESSION["bbPoint11"] = (((($fshoulder / 2) - $shoulder) * $cIn) + (0.20 * $cIn));
$_SESSION["bbPoint12"] = ($topMargin + (0.05 * $cIn));

// Back tucks
$backTuckHeight = 3.5 * $cIn;
$bvApex = (($apex + 1) * $cIn);
$_SESSION["backVApex"] = $bvApex;

// Back horizontal apex calculation (using chest instead of bust for back)
$bustForBack = $chest;
if (($bustForBack >= 28) && ($bustForBack <= 32)) {
    $bhApex = 3.25 * $cIn;
} elseif (($bustForBack >= 32) && ($bustForBack <= 35)) {
    $bhApex = 3.5 * $cIn;
} elseif (($bustForBack >= 35) && ($bustForBack <= 38)) {
    $bhApex = 3.75 * $cIn;
} elseif (($bustForBack >= 38) && ($bustForBack <= 41)) {
    $bhApex = 4 * $cIn;
} elseif (($bustForBack >= 41) && ($bustForBack <= 44)) {
    $bhApex = 4.25 * $cIn;
} else {
    $bhApex = 3.5 * $cIn;
}
$_SESSION["backHApex"] = $bhApex;

$chestText = $bhApex;
$blengthText = (($blength + 0.5) * $cIn);
$blengthTextLeft = $chestText - (0.5 * $cIn);
$blengthTextRight = $chestText + (0.5 * $cIn);

$saviBackTucks = "M" . $blengthTextLeft . "," . ($blengthText + (0.5 * $cIn)) . "L" . $chestText . "," . (($apex + 1) * $cIn) . "L" . $blengthTextRight . "," . ($blengthText + (0.5 * $cIn));
$_SESSION["saviBackTucks"] = $saviBackTucks;

// Back labels
$_SESSION["bChestLabel"] = ((($chest / 4) - 3.5) * $cIn);
$_SESSION["bBackHeight"] = ($blength * $cIn);

// =============================================================================
// SECTION 5: SLEEVE PATTERN CALCULATIONS (from saviSleeve.php logic)
// =============================================================================

$p6 = $sleeveTopMargin = $saroundCenter = 0;
$sleeveFLeft = 0.2;
$sleeveSeam = 0.4;
$sleeveTopMargin = 0.6;
$sleeveMLeft = 1;
$armholeAdj = $armhole + 0.5;

$sleeveCapHeight = ($armholeAdj - $saround);

// Sleeve chest vertical calculation
$sleeveChestVertical = ((($armholeAdj / 2) - 1.5) * $cIn);
$sleeveChestVertical = ($sleeveChestVertical + (0.04 * $cIn));

// Sleeve angle calculation
$sAngle = 0;
if ($chest < 33) {
    $sAngle = (3.0 * $cIn);
} else {
    $sAngle = (3.5 * $cIn);
}

switch ($sleeveCapHeight) {
    case 1.5: $sAngle = (3.25 * $cIn); break;
    case 2: $sAngle = (3.5 * $cIn); break;
    case 2.5: $sAngle = (3.85 * $cIn); break;
    case 3: $sAngle = (4 * $cIn); break;
    case 3.5: $sAngle = (4.25 * $cIn); break;
}

// Sleeve center calculation
$slCenter = (($sleeveChestVertical * 2) - ($saround * $cIn)) / 2;
$slCenter = $slCenter + (1.5 * $cIn);

$slOpenCtr = (($saround - $sopen) / 2) * $cIn;

// Sleeve Black stitchline
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . $sleeveTopMargin;
$nPoint1a = "Q" . (($sleeveChestVertical / 1.5) + (1 * $cIn)) . "," . ($sleeveTopMargin + (0.2 * $cIn)) . "," . ($sleeveChestVertical * 0.5) . "," . ($sAngle * 0.7);
$nPoint1b = "Q" . ($sleeveChestVertical * 0.3) . "," . $sAngle . "," . $slCenter . "," . $sAngle;
$nPoint2 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength * $cIn);
$nPoint3 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
$nPoint4 = "L" . ($slCenter + ($saround * $cIn)) . "," . $sAngle;
$nPoint5 = "Q" . ($sleeveChestVertical * 2 - (0.5 * $cIn)) . "," . ($sAngle * 0.3) . "," . ($sleeveChestVertical + (2 * $cIn)) . "," . $sleeveTopMargin;

$sleeveBlack = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . "Z";
$_SESSION["saviBBlack"] = $sleeveBlack;

// Sleeve Gray line (inner reference)
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . $sleeveTopMargin;
$nPoint2 = "L" . $slCenter . "," . $sAngle;
$nPoint3 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength * $cIn);
$nPoint4 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
$nPoint5 = "L" . ($slCenter + ($saround * $cIn)) . "," . $sAngle;

$sleeveGray = $nPoint1 . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . "Z";
$_SESSION["saviBGray"] = $sleeveGray;

// Sleeve Red line (outer seam allowance)
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . (-0.5 * $cIn);
$nPoint1a = "Q" . (($sleeveChestVertical / 1.5) + (1 * $cIn)) . "," . $sleeveTopMargin . "," . (($sleeveChestVertical / 2) + (1 * $cIn)) . "," . (($sAngle / 2) - (0.5 * $cIn));
$nPoint1b = "Q" . (($sleeveChestVertical / 2) - (0.5 * $cIn)) . "," . $sAngle . "," . (0 * $cIn) . "," . ($sAngle - (0.5 * $cIn));
$nPoint2 = "L" . (0 * $cIn) . "," . $sAngle;
$nPoint3 = "L" . (0 * $cIn) . "," . $sAngle;

$slCenterRed = (($sleeveChestVertical * 2) - ($saround * $cIn)) / 2;
$slCenterRed = $slCenterRed + (1.5 * $cIn);

$nPoint4 = "L" . ($slCenterRed - (1 * $cIn)) . "," . ($slength * $cIn);
$nPoint5 = "L" . ($slCenterRed - (1 * $cIn)) . "," . (($slength + 1) * $cIn);
$nPoint6 = "L" . ($slCenterRed + (($saround + 1) * $cIn)) . "," . (($slength + 1) * $cIn);
$nPoint7 = "L" . ($slCenterRed + (($saround + 1) * $cIn)) . "," . ($slength * $cIn);
$nPoint8 = "L" . (($sleeveChestVertical * 2) + (3 * $cIn)) . "," . ($sAngle - (0.5 * $cIn));
$nPoint9 = "L" . (($sleeveChestVertical * 2) + (2 * $cIn)) . "," . ($sAngle - (0.5 * $cIn));
$nPoint10 = "Q" . ($sleeveChestVertical * 2 + (1 * $cIn)) . "," . (($sAngle / 2) - ($sleeveTopMargin * $cIn)) . "," . ($sleeveChestVertical + (2 * $cIn)) . "," . (-0.5 * $cIn);

$sleeveRed = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . $nPoint6 . $nPoint7 . $nPoint8 . $nPoint9 . $nPoint10 . "Z";
$_SESSION["saviBRed"] = $sleeveRed;

// Center line
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . $sleeveTopMargin;
$nPoint2 = "L" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . ($slength * $cIn);
$centerLine = $nPoint1 . $nPoint2;
$_SESSION["centerLine"] = $centerLine;

// =============================================================================
// SECTION 6: PATTI PATTERN CALCULATIONS (from saviPatti.php logic)
// =============================================================================

$pattiFLeft = (1 * $cIn);
$pattiTopMargin = 0.5;

// Black border
$bPatti1 = "M" . ($pattiFLeft + (0.25 * $cIn)) . "," . ($pattiTopMargin * $cIn);
$bPatti2 = "L" . ($pattiFLeft + (0.25 * $cIn)) . "," . ((($blength - $flength) + 2) * $cIn);
$bPatti3 = "Q" . ((($blength - $flength) + 0.5) * $cIn) . "," . ((($blength - $flength) + 0.5) * $cIn) . "," . (($chest / 8) * $cIn) . "," . ((($blength - $flength) + 0.5) * $cIn);
$bPatti4 = "L" . (($chest / 4) * $cIn) . "," . ((($blength - $flength) + 1.5) * $cIn);
$bPatti5 = "L" . (($chest / 4) * $cIn) . "," . ($pattiTopMargin * $cIn);

$saviBlousePatti = $bPatti1 . $bPatti2 . $bPatti3 . $bPatti4 . $bPatti5 . "Z";
$_SESSION["saviPattiBlack"] = $saviBlousePatti;

// Red border
$bPattiRed1 = "M" . $pattiFLeft . "," . (0.2 * $cIn);
$bPattiRed2 = "L" . $pattiFLeft . "," . ((($blength - $flength) + 2.5) * $cIn);
$bPattiRed3 = "Q" . ((($blength - $flength) + 1) * $cIn) . "," . ((($blength - $flength) + 0.8) * $cIn) . "," . ((($chest / 8) + 1) * $cIn) . "," . ((($blength - $flength) + 1.0) * $cIn);
$bPattiRed4 = "L" . ((($chest / 4) + 0.25) * $cIn) . "," . ((($blength - $flength) + 2) * $cIn);
$bPattiRed5 = "L" . ((($chest / 4) + 0.25) * $cIn) . "," . (0.2 * $cIn);

$saviBlousePattiRed = $bPattiRed1 . $bPattiRed2 . $bPattiRed3 . $bPattiRed4 . $bPattiRed5 . "Z";

// Hook patti
$pattiTop = 5 * $cIn;
$hPoint1 = "M" . 0 . "," . $pattiTop;
$hPoint2 = "L" . ((($blength - $fndepth) + $pattiFLeft) * $cIn) . "," . $pattiTop;
$hPoint3 = "L" . ((($blength - $fndepth) + $pattiFLeft) * $cIn) . "," . ($pattiTop + (3 * $cIn));
$hPoint4 = "L" . 0 . "," . ($pattiTop + (3 * $cIn));
$hookPatti = $hPoint1 . $hPoint2 . $hPoint3 . $hPoint4 . "Z";

$_SESSION["saviPattiRed"] = $saviBlousePattiRed . $hookPatti;

?>

<!-- ============================================================================= -->
<!-- SECTION 7: HTML OUTPUT - Pattern Display with 2x2 Grid Layout                 -->
<!-- ============================================================================= -->

<style>
.pattern-container {
    width: 100%;
    padding: 20px;
}
.pattern-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}
.pattern-title {
    font-size: 1.5em;
    font-weight: bold;
}
.download-buttons {
    display: flex;
    gap: 10px;
}
.download-buttons .btn {
    padding: 8px 16px;
}
.download-buttons a {
    color: #fff;
    text-decoration: none;
}
.pattern-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.pattern-cell {
    border: 1px solid #ddd;
    padding: 15px;
    background: #fff;
    border-radius: 4px;
}
.pattern-cell h4 {
    margin: 0 0 10px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    font-size: 1.1em;
    color: #333;
}
.pattern-cell ul {
    font-size: 0.85em;
    padding-left: 20px;
    margin: 10px 0;
    color: #666;
}
.pattern-cell svg {
    max-width: 100%;
    height: auto;
}
.measurement-summary {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.9em;
}
.measurement-summary h5 {
    margin: 0 0 10px 0;
}
</style>

<div class="pattern-container">
    <!-- 2x2 Pattern Grid -->
    <div class="pattern-grid">
        <!-- Front Pattern -->
        <div class="pattern-cell">
            <h4>Front Pattern</h4>            
            <svg width="100%" height="350" viewbox="-50, 0, 450, 400">
                <g>
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGray; ?>" />
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseBrown; ?>" />
                    <text x="<?php echo $hApex; ?>" y="<?php echo $vApex; ?>"><?php echo htmlspecialchars($cust); ?></text>
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontLeftTucks; ?>" />
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontRightTucks; ?>" />
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontBottomTucks; ?>" />
                    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseRed; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGreen; ?>" />
                </g>
            </svg>
        </div>

        <!-- Back Pattern -->
        <div class="pattern-cell">
            <h4>Back Pattern</h4>
            <svg width="100%" height="350" viewbox="-50, -20, 400, 550">
                <g>
                    <path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBackBlack; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBackGreen; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $saviBackBrown; ?>" />
                    <text x="<?php echo $bhApex; ?>" y="<?php echo $bvApex; ?>"><?php echo htmlspecialchars($backTuckText); ?></text>
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBackTucks; ?>" />
                    <path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBackRed; ?>" />
                </g>
            </svg>
        </div>

        <!-- Sleeve Pattern -->
        <div class="pattern-cell">
            <h4>Sleeve Pattern</h4>
            <svg width="100%" height="350" viewbox="-50, -50, 500, 400">
                <g>
                    <path fill="none" stroke="#ff0000" stroke-width="0.5" stroke-dasharray="5,2,3" stroke-miterlimit="10" d="<?php echo $sleeveRed; ?>" />
                    <path fill="none" stroke="#ff0000" stroke-width="0.5" stroke-dasharray="5,2,3" stroke-miterlimit="10" d="<?php echo $centerLine; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $sleeveBlack; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#7c7c7cff" stroke-width="0.5" stroke-dasharray="5,5" stroke-miterlimit="10" d="<?php echo $sleeveGray; ?>" />
                </g>
            </svg>
        </div>

        <!-- Patti Pattern -->
        <div class="pattern-cell">
            <h4>Patti (Waistband) Pattern</h4>
            <svg width="100%" height="350" viewbox="-50, -50, 450, 500">
                <g>
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBlousePatti; ?>" />
                    <path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBlousePattiRed; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#ff0000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $hookPatti; ?>" />
                </g>
            </svg>
        </div>
    </div>

    <!-- Measurement Summary -->
    <div class="measurement-summary">
        <h5>Measurement Summary</h5>
        <div><?php echo htmlspecialchars($_SESSION["measure"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure1"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure2"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure3"]); ?></div>
    </div>
</div>