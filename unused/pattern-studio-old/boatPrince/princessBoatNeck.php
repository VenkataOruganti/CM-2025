<?php
/**
 * =============================================================================
 * PRINCESS BOAT NECK BLOUSE COMPLETE PATTERN - Combined Pattern Display
 * =============================================================================
 *
 * This file combines all 3 pattern components (Front, Back, Sleeve)
 * into a single display page with download functionality.
 *
 * Data Sources:
 * - Mode 1: From URL parameter (customer_id) - loads from database via customers table
 *           Used by: dashboard-boutique.php, pattern-preview.php
 * - Mode 2: From URL parameter (id) - loads from database via measurements table
 *           Used by: direct measurement links
 * - Mode 3: From session variables (set by boatPrinceMeasure.php form submission)
 *           Used by: boatPrince.php measurement form
 *
 * Session Variables Set (for download files):
 * - Front: princeBlouseFront, princeFrontBlouseGreen, princeFrontBlouseRed,
 *          princeFlTucks, princeCurveGray, rightFrTucks, vApex, hApex, fbDart
 * - Back: princeBackBlack, princeBackGreen, princeBackBrown, princeBackRed,
 *         princeBackTucks, backVApex, backHApex
 * - Sleeve: saviBBlack, saviBGray, saviBRed, centerLine
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

// Initialize all measurement variables
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
        error_log("princessBoatComplete.php - customer_id error: " . $e->getMessage());
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
        error_log("princessBoatComplete.php - measurement_id error: " . $e->getMessage());
    }
}

// Mode 3: Load from session (set by boatPrinceMeasure.php form submission in boatPrince.php)
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

// If no data loaded, silently return - the parent page (boatPrince.php) handles
// showing/hiding the design section via JavaScript based on form values
if (!$dataLoaded) {
    // Don't show error - this is normal on initial page load before form submission
    // The JavaScript in boatPrince.php will hide this div anyway
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
$apex1 = $apex * $cIn;

// =============================================================================
// SECTION 3: FRONT PATTERN CALCULATIONS (from boatPrinceFront.php logic)
// =============================================================================

$topMargin = 0;
$topPadding = (0.25 * $cIn);
$fLeft = 0;
$seam = (0.3 * $cIn);

// Bottom Dart calculation
$bDart = (($chest - $waist) / 2);
$_SESSION["fbDart"] = $bDart;

// Bust variance calculation
$bustVar = ($bust - ($chest / 2)) / 2;

// Center tuck calculation: Bust Variance - waist / 4 = (result / 2) + 0.5 (additional margin)
$legWidth = ($bustVar - ($waist / 4));
$legWidth = $legWidth / 2;

// Vertical Apex calculation
$vApex = (($apex + 0.5) * $cIn);
$_SESSION["vApex"] = $vApex;

// Horizontal Apex (Bust Point) Calculation - using chest for princess cut
$bust = $chest; // Princess cut uses chest measurement
$hApex = 3.5 * $cIn;  // Initialize with default (PHP 8+ compatibility)

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

// Front Length adjustment based on chest size
$xLength = 1;  // Initialize with default (PHP 8+ compatibility)
if (($chest >= 30) && ($chest <= 35)) {
    $xLength = 1.25;
} elseif (($chest > 35) && ($chest <= 38)) {
    $xLength = 1.5;
} elseif (($chest > 38) && ($chest <= 44)) {
    $xLength = 2;
} else {
    $xLength = 1;
}

// -------------- Gray reference line ----------------------------
$fntPoint1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
$fntPoint2 = " L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
$fntPoint3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
$fntPoint4 = "L" . $fLeft . "," . $fndepth1;
$fntPoint5 = "L" . $fLeft . "," . ($apex * $cIn);
$fntPoint6 = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);
$fntPoint7 = "L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn);
$fntPoint8 = "L" . ((($waist / 4) + ($legWidth * 2)) * $cIn) . "," . ((($blength + $xLength) - 0.5) * $cIn);
$fntPoint8a = "L" . ($bustVar * $cIn) . "," . $apex1;
$fntPoint9 = "L" . ((($chest / 4) + 0) * $cIn) . "," . $chestVertical;
$fntPoint10 = "L" . ($fshoulder1 / 2) . "," . $chestVertical;
$fntPoint12 = "L" . ($fshoulder1 / 2) . "," . $seam;

$princeFrontBlouseGray = $fntPoint1 . $fntPoint2 . $fntPoint3 . $fntPoint4 . $fntPoint5 . $fntPoint6 . $fntPoint7 . $fntPoint8 . $fntPoint8a . $fntPoint9 . $fntPoint10 . $fntPoint12;
$_SESSION["princeBlouseFront"] = $princeFrontBlouseGray;

// -------------- Black stitch line ----------------------------
$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);
$sg_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
$sg_point6 = "";  // Initialize to empty (PHP 8+ compatibility)

// Princess-cut & Deepneck logic
if ($bnDepth <= 4.5) {
    $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1 . "," . $seam . "," . $fndepth1;
} else {
    $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
    $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1 . "," . $seam . "," . $fndepth1;
}

$sg_point5 = "L" . $fLeft . "," . ($apex * $cIn);
$sg_point5a = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);
// $sg_point6 already initialized above
$sg_point7 = " L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn);
$sg_point8 = "L" . ((($waist / 4) + ($legWidth * 2)) * $cIn) . "," . ((($blength + $xLength) - 0.5) * $cIn);
$sg_point8a = "L" . ($bustVar * $cIn) . "," . ($vApex + (0 * $cIn));
$sg_point9 = " L" . ((((($chest / 4))) + 0.5) * $cIn) . "," . $chestVertical;

// Front Shoulder bottom curve calculation
$frontChestVertical = $chestVertical / 2;  // Initialize with default (PHP 8+ compatibility)
if (($chest > 28) && ($chest <= 32)) {
    $frontChestVertical = $chestVertical / 2;
} elseif (($chest > 32) && ($chest <= 38)) {
    $frontChestVertical = ($chestVertical / 2 + (1 * $cIn));
} elseif ($chest > 38) {
    $frontChestVertical = ($chestVertical / 2 + (1.5 * $cIn));
} else {
    $frontChestVertical = $chestVertical / 2;
}

$sg_point10 = "Q" . (($fshoulder1 / 2) - (0.5 * $cIn)) . "," . $chestVertical . "," . (($fshoulder1 / 2) - (0.4 * $cIn)) . "," . $frontChestVertical;
$sg_point11 = "L" . ($fshoulder1 / 2) . "," . ($topPadding + (0.25 * $cIn));
$sg_point12 = "L" . ((($fshoulder / 2) - $shoulder) * $cIn) . "," . $topPadding;

$princeFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point4 . $sg_point5 . $sg_point5a . $sg_point6 . $sg_point7 . $sg_point8 . $sg_point8a . $sg_point9 . $sg_point10 . $sg_point11 . $sg_point12 . "Z";
$_SESSION["princeFrontBlouseGreen"] = $princeFrontBlouseGreen;

// -------------- Brown dotted line (extra bust design) ----------------------------
$green_point8 = " M" . ((($chest / 4) + 0.5) * $cIn) . "," . ($flength1 + (0.5 * $cIn));
$green_point9 = " L" . ((($chest / 4) + 1) * $cIn) . "," . $chestVertical;
$princeFrontBlouseBrown = $green_point8 . $green_point9;
$_SESSION["princeFrontBlouseBrown"] = $princeFrontBlouseBrown;

// -------------- Red dotted line (seam allowance) ----------------------------
$fnt_point1 = "M" . ((($fshoulder1 / 2) - $shoulder1) - ($seam)) . "," . $topMargin;
$fnt_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
$fnt_point7 = "";  // Initialize to empty (PHP 8+ compatibility)
$fnt_point4 = "Q" . ((($fshoulder1 / 2) - $shoulder1) - $seam) . "," . ($fndepth1 - $seam) . "," . $fLeft . "," . ($fndepth1 - $seam);
$fnt_point5 = " L" . $fLeft . "," . ((($blength + $xLength) + 0.5) * $cIn);
$fnt_point8 = "L" . (((($bust / 4) + 1) + 0.5) * $cIn) . "," . ((($blength + $xLength)) * $cIn);
$fnt_point9 = "L" . (((($chest / 4) + 0.5) + 1.0) * $cIn) . "," . ($chestVertical - (0.5 * $cIn));
$fnt_point10 = "Q" . ((($fshoulder / 2) - 1) * $cIn) . "," . $chestVertical . "," . ((($fshoulder / 2) + 0.5) * $cIn) . "," . $topMargin;

$princeFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point4 . $fnt_point5 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . "Z";
$_SESSION["princeBlouseFrontRed"] = $princeFrontBlouseRed;

// Front Left Tuck
$princeFrontLeftTucks = "M" . $fLeft . "," . (($apex - 0.3) * $cIn) .
    "L" . ($hApex - (1 * $cIn)) . "," . ($vApex - (0.25 * $cIn)) .
    "L" . $fLeft . "," . (($apex + 0.3) * $cIn);
$_SESSION["princeFlTucks"] = $princeFrontLeftTucks;

// Right Center Tuck
$rCenter1 = "M" . ($hApex + (2 * $cIn)) . "," . ($vApex + (0 * $cIn));
$rCenter2 = "L" . ($bustVar * $cIn) . "," . ($vApex + (0 * $cIn));
$rCenter3 = "M" . ($bustVar * $cIn) . "," . ($vApex + (0.5 * $cIn));
$rCenter4 = "L" . ($hApex + (2 * $cIn)) . "," . ($vApex + (0 * $cIn));
$rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
$_SESSION["rightFrTucks"] = $rightCenter;

// Princess Cut Gray Curves (unique to this pattern)
$legWidth = $legWidth + 0.25;

$pcGray1 = "M" . ($hApex - ($legWidth * $cIn)) . "," . (($blength + $xLength) * $cIn);
$pcGray2 = "Q" . ($hApex - ($legWidth * $cIn)) . "," . (($apex + 4) * $cIn) . "," . $hApex . "," . ($apex * $cIn);
$pcGray3 = "Q" . ($hApex + ($legWidth * $cIn)) . "," . (($apex - 2.5) * $cIn) . "," . (($fshoulder1 / 2) + (0.5 * $cIn)) . "," . ($chestVertical - (1 * $cIn));

$pcGray4 = "M" . ($hApex + ($legWidth * $cIn)) . "," . (($blength + $xLength) * $cIn);
$pcGray5 = "L" . ($hApex + ($legWidth * $cIn)) . "," . ($flength1 + $seam + (0.7 * $cIn));
$pcGray6 = "Q" . (($hApex) - 0.5 * $cIn) . "," . (($apex + 1) * $cIn) . "," . $hApex . "," . ($apex * $cIn);
$pcGray7 = "Q" . (($hApex) + 1 * $cIn) . "," . (($apex - 2) * $cIn) . "," . (($fshoulder1 / 2) + (1.0 * $cIn)) . "," . ($chestVertical - (0.5 * $cIn));

$princeCurveGray = $pcGray1 . $pcGray2 . $pcGray3 . $pcGray4 . $pcGray5 . $pcGray6 . $pcGray7;
$_SESSION["princeCurveGray"] = $princeCurveGray;

// =============================================================================
// SECTION 4: BACK PATTERN CALCULATIONS (from boatPrinceBack.php logic)
// =============================================================================

$seam = 0.3 * $cIn;
$neckSeam = 0.2;
$backDart = 1;
$mLeft = 1;
$topMargin = 0;

$backTuckText = $cust;

// Full shoulder adjustment for back pattern
if (($bnDepth > 4) && ($bnDepth < 7)) {
    $fshoulderBack = ($fshoulder - (($bnDepth * 0.25) - 0.25));
} else {
    $fshoulderBack = ($fshoulder - 1.5);
}

// Back labels
$_SESSION["bChestLabel"] = $chestLabel = ((($chest / 4) - 3.5) * $cIn);
$_SESSION["bBackHeight"] = $backHeight = ($blength * $cIn);
$_SESSION["bChestLabel0"] = $chestLabel05 = (($chest / 4) * $cIn);
$_SESSION["bChestLabel05"] = $chestLabel10 = ((($chest / 4) + 0.5) * $cIn);
$_SESSION["bChestLabel10"] = $chestLabel15 = ((($chest / 4) + 1.0) * $cIn);
$_SESSION["bChestLabel15"] = $chestLabel20 = ((($chest / 4) + 1.5) * $cIn);

// -------------- Gray reference line ----------
$bbPoint1 = "M" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . $topPadding;
$bbPoint2 = "L" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . ($topMargin + ($bnDepth / 2) * $cIn);
$bbPoint3 = "L" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . ($topMargin + ($bnDepth * $cIn));
$bbPoint4 = "L" . $seam . "," . (($topMargin + $bnDepth) * $cIn);
$bbPoint5 = "L" . $seam . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$bbPoint6 = "L" . (((($waist / 4) + $backDart) + $mLeft) * $cIn) . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$bbPoint7 = "L" . ((($chest / 4) + $mLeft) * $cIn) . "," . $chestVertical;
$bbPoint8 = "L" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . $chestVertical;
$bbPoint9 = "L" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . (($fshoulderBack / 4) * $cIn);
$bbPoint10 = "L" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . ($topPadding * 2);

$princeBackBlack = $bbPoint1 . $bbPoint2 . $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
$_SESSION["princeBackBlack"] = $princeBackBlack;

// -------------- Black stitch line ----------
$sb_point1 = "M" . ((((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn)) . "," . (0.3 * $cIn);
$sb_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
$sb_point3 = "Q" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . ($bnDepth * $cIn) . "," . $seam . "," . ($bnDepth * $cIn);
$sb_point4 = "L" . $seam . "," . ($bnDepth * $cIn);
$sb_point5 = "M" . $seam . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$sb_point6 = "L" . ((((($waist / 4) + $backDart)) * $cIn) + $seam) . "," . (((($topMargin + $blength) + 0.5)) * $cIn);
$sb_point7 = "L" . (((($chest / 4)) * $cIn) + $seam) . "," . $chestVertical;
$sb_point7a = "L" . (($chest / 4) * $cIn) . "," . $chestVertical;
$sb_point8 = "Q" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . $chestVertical . "," . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . (($chestVertical / 2) + (0.2 * $cIn));
$sb_point9 = "L" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . (($fshoulderBack / 4) * $cIn);
$sb_point10 = "L" . ((($fshoulderBack / 2) + $mLeft) * $cIn) . "," . (0.5 * $cIn);
$sb_point11 = "L" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . (0.25 * $cIn);

$princeBackGreen = $sb_point1 . $sb_point2 . $sb_point3 . $sb_point5 . $sb_point6 . $sb_point7 . $sb_point7a . $sb_point8 . $sb_point9 . $sb_point10 . $sb_point11;
$_SESSION["princeBackGreen"] = $princeBackGreen;

// -------------- Brown line (extra bust design) ----------
$brownPoint6 = "M" . (((($waist / 4) + 0.5) * $cIn) + $seam) . "," . ((($topMargin + $blength) + 0.5) * $cIn);
$brownPoint7 = "L" . (((($chest / 4) - 0.5) * $cIn) + $seam) . "," . $chestVertical;
$princeBackBrown = $brownPoint6 . $brownPoint7;
$_SESSION["princeBackBrown"] = $princeBackBrown;

// -------------- Red line (seam allowance) ----------
$point1 = "M" . ((((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) - $seam) . "," . $topMargin;
$point2 = "";  // Initialize to empty (PHP 8+ compatibility)
$point3 = "Q" . ((((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) - $seam) . "," . ($bnDepth * $cIn) . "," . $seam . "," . (($bnDepth * $cIn) - $seam);
$point4 = "L" . $seam . "," . ($bnDepth * $cIn);
$point5 = "M" . $seam . "," . ((($topMargin + $blength) + 1) * $cIn);
$point6 = "L" . (((($waist / 4) + $backDart) + 1.0) * $cIn) . "," . (((($topMargin + $blength) + 1)) * $cIn);
$point7 = "L" . ((($chest / 4) + 1.0) * $cIn) . "," . (($chestVertical) - (0.3 * $cIn));
$point8 = "Q" . ((($fshoulderBack / 2) + 1) * $cIn) . "," . $chestVertical . "," . (((($fshoulderBack / 2) + 0.5) + $mLeft) * $cIn) . "," . ($chestVertical / 2);
$point9 = "L" . (((($fshoulderBack / 2) + 0.5) + $mLeft) * $cIn) . "," . (($fshoulderBack / 4) * $cIn);
$point10 = "L" . (((($fshoulderBack / 2) + 0.5) + $mLeft) * $cIn) . "," . $topPadding;
$point11 = "L" . (((($fshoulderBack / 2) - $shoulder) + $mLeft) * $cIn) . "," . $topMargin;

$princeBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 . $point10 . $point11;
$_SESSION["princeBackRed"] = $princeBackRed;

// Back Tucks
$backTuckHeight = 3.5 * $cIn;
$bvApex = (($apex + 1) * $cIn);
$_SESSION["backVApex"] = $bvApex;

// Back horizontal apex calculation (using chest)
$bhApex = 3.5 * $cIn;  // Initialize with default (PHP 8+ compatibility)
if (($chest >= 30) && ($chest <= 32)) {
    $bhApex = 3.25 * $cIn;
} elseif (($chest >= 32) && ($chest <= 35)) {
    $bhApex = 3.5 * $cIn;
} elseif (($chest >= 35) && ($chest <= 38)) {
    $bhApex = 3.75 * $cIn;
} elseif (($chest >= 38) && ($chest <= 41)) {
    $bhApex = 4 * $cIn;
} elseif (($chest >= 41) && ($chest <= 44)) {
    $bhApex = 4.25 * $cIn;
} else {
    $bhApex = 3.5 * $cIn;
}
$_SESSION["backHApex"] = $bhApex;

$chestText = $bhApex;
$blengthText = (($blength + 0.5) * $cIn);
$blengthTextLeft = $chestText - (0.5 * $cIn);
$blengthTextRight = $chestText + (0.5 * $cIn);
$tuckHeight = ($blength - $backTuckHeight) * $cIn;
$blengthTextTuck = ($blength - $tuckHeight);

$princeBackTucks = "M" . $blengthTextLeft . "," . ($blengthText + (0.5 * $cIn)) . "L" . $chestText . "," . (($apex + 1) * $cIn) . "L" . $blengthTextRight . "," . ($blengthText + (0.5 * $cIn));
$_SESSION["princeBackTucks"] = $princeBackTucks;

// =============================================================================
// SECTION 5: SLEEVE PATTERN CALCULATIONS (from inc/sleeve.php logic)
// =============================================================================

$p6 = $sleeveTopMargin = $saroundCenter = 0;
$sleeveFLeft = 0.2;
$sleeveSeam = 0.4;
$sleeveTopMargin = 0.6;
$sleeveMLeft = 1;
$armholeAdj = $armhole + 0.5;
$saroundAdj = $saround + 1.5;

$sleeveCapHeight = ($armholeAdj - $saroundAdj);

// Sleeve chest vertical calculation
$sleeveChestVertical = ((($armholeAdj / 2) - 1.5) * $cIn);
$sleeveChestVertical = ($sleeveChestVertical + (0.04 * $cIn));

// Sleeve angle calculation
$sAngle = (3.5 * $cIn);  // Initialize with default (PHP 8+ compatibility)

switch ($sleeveCapHeight) {
    case 1.0:
        $sAngle = (3.25 * $cIn);
        break;
    case 1.5:
        $sAngle = (3.25 * $cIn);
        break;
    case 2:
        $sAngle = (3.5 * $cIn);
        break;
    case 2.5:
        $sAngle = (3.85 * $cIn);
        break;
    case 3:
        $sAngle = (4 * $cIn);
        break;
    case 3.5:
        $sAngle = (4.25 * $cIn);
        break;
    case 4:
        $sAngle = (4.7 * $cIn);
        break;
    default:
        $sAngle = (3.5 * $cIn);
}

// Sleeve center calculation
$slCenter = (($sleeveChestVertical * 2) - ($saroundAdj * $cIn)) / 2;
$slCenter = $slCenter + (1.5 * $cIn);

$slOpenCtr = (($saroundAdj - $sopen) / 2) * $cIn;

// Sleeve Black stitchline
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . $sleeveTopMargin;
$nPoint1a = "Q" . (($sleeveChestVertical / 1.5) + (1 * $cIn)) . "," . ($sleeveTopMargin + (0.2 * $cIn)) . "," . ($sleeveChestVertical * 0.5) . "," . ($sAngle * 0.7);
$nPoint1b = "Q" . ($sleeveChestVertical * 0.3) . "," . $sAngle . "," . $slCenter . "," . $sAngle;
$nPoint2 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength * $cIn);
$nPoint3 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
$nPoint4 = "L" . ($slCenter + ($saroundAdj * $cIn)) . "," . $sAngle;
$nPoint5 = "Q" . ($sleeveChestVertical * 2 - (0.5 * $cIn)) . "," . ($sAngle * 0.3) . "," . ($sleeveChestVertical + (2 * $cIn)) . "," . $sleeveTopMargin;

$sleeveBlack = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . "Z";
$_SESSION["saviBBlack"] = $sleeveBlack;

// Sleeve Gray line (inner reference)
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . $sleeveTopMargin;
$nPoint2 = "L" . $slCenter . "," . $sAngle;
$nPoint3 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength * $cIn);
$nPoint4 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
$nPoint5 = "L" . ($slCenter + ($saroundAdj * $cIn)) . "," . $sAngle;

$sleeveGray = $nPoint1 . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . "Z";
$_SESSION["saviBGray"] = $sleeveGray;

// Sleeve Red line (outer seam allowance)
$nPoint1 = "M" . ($sleeveChestVertical + ((1 + 0.5) * $cIn)) . "," . (-0.5 * $cIn);
$nPoint1a = "Q" . (($sleeveChestVertical / 1.5) + (1 * $cIn)) . "," . $sleeveTopMargin . "," . (($sleeveChestVertical / 2) + (1 * $cIn)) . "," . (($sAngle / 2) - (0.5 * $cIn));
$nPoint1b = "Q" . (($sleeveChestVertical / 2) - (0.5 * $cIn)) . "," . $sAngle . "," . (0 * $cIn) . "," . ($sAngle - (0.5 * $cIn));
$nPoint2 = "L" . (0 * $cIn) . "," . $sAngle;
$nPoint3 = "L" . (0 * $cIn) . "," . $sAngle;

$slCenterRed = (($sleeveChestVertical * 2) - ($saroundAdj * $cIn)) / 2;
$slCenterRed = $slCenterRed + (1.5 * $cIn);

$nPoint4 = "L" . ($slCenterRed - (1 * $cIn)) . "," . ($slength * $cIn);
$nPoint5 = "L" . ($slCenterRed - (1 * $cIn)) . "," . (($slength + 1) * $cIn);
$nPoint6 = "L" . ($slCenterRed + (($saroundAdj + 1) * $cIn)) . "," . (($slength + 1) * $cIn);
$nPoint7 = "L" . ($slCenterRed + (($saroundAdj + 1) * $cIn)) . "," . ($slength * $cIn);
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

?>

<!-- ============================================================================= -->
<!-- SECTION 6: HTML OUTPUT - Pattern Display with 2x2 Grid Layout                 -->
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
.pattern-cell-empty {
    border: 1px dashed #ddd;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-style: italic;
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
            <h4>Front Pattern (Princess Cut)</h4>
            <svg width="100%" height="450" viewbox="-50, 0, 600, 450">
                <g>
                    <path fill="none" stroke="#000000" stroke-width="0.3" stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseGray; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseBrown; ?>" />
                    <text x="<?php echo $hApex; ?>" y="<?php echo $vApex; ?>"><?php echo htmlspecialchars($cust); ?></text>
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeFrontLeftTucks; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#ff0000" stroke-dasharray="5,5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseRed; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeCurveGray; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseGreen; ?>" />
                </g>
            </svg>
        </div>

        <!-- Back Pattern -->
        <div class="pattern-cell">
            <h4>Back Pattern</h4>
            <svg width="100%" height="450" viewbox="-50, -20, 500, 550">
                <g>
                    <path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeBackBlack; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeBackGreen; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $princeBackBrown; ?>" />
                    <text x="<?php echo $bhApex; ?>" y="<?php echo $bvApex; ?>"><?php echo htmlspecialchars($backTuckText); ?></text>
                    <text x="<?php echo $bhApex; ?>" y="<?php echo $bvApex; ?>" transform="rotate(-90, 10, <?php echo $bvApex; ?>)">'< ---- Fold --- >'</text>
                    <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeBackTucks; ?>" />
                    <path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeBackRed; ?>" />
                    <text x="<?php echo $chestLabel; ?>" y="<?php echo $backHeight; ?>" font-size="9">1/4 of Chest-></text>
                    <text x="<?php echo $chestLabel05; ?>" y="<?php echo $backHeight; ?>" font-size="9">Seam</text>
                </g>
            </svg>
        </div>

        <!-- Sleeve Pattern -->
        <div class="pattern-cell">
            <h4>Sleeve Pattern</h4>
            <svg width="100%" height="450" viewbox="-50, -50, 500, 500">
                <g>
                    <path fill="none" stroke="#ff0000" stroke-width="0.2" stroke-dasharray="5,2,3" stroke-miterlimit="10" d="<?php echo $sleeveRed; ?>" />
                    <path fill="none" stroke="#ff0000" stroke-width="0.2" stroke-dasharray="5,2,3" stroke-miterlimit="10" d="<?php echo $centerLine; ?>" />
                    <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $sleeveBlack; ?>" />
                </g>
                <g>
                    <path fill="none" stroke="#a9a9a9" stroke-width="0.5px" stroke-dasharray="5,5" stroke-miterlimit="10" d="<?php echo $sleeveGray; ?>" />
                </g>
            </svg>
        </div>

        <!-- Empty cell (4th quadrant) -->
        <div class="pattern-cell-empty">
            <span>Princess Boat Neck - 3 Pattern Components</span>
        </div>
    </div>

    <!-- Measurement Summary -->
    <div class="measurement-summary">
        <h5>Measurement Summary for: <?php echo htmlspecialchars($cust); ?></h5>
        <div><?php echo htmlspecialchars($_SESSION["measure"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure1"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure2"]); ?></div>
        <div><?php echo htmlspecialchars($_SESSION["measure3"]); ?></div>
    </div>
</div>
