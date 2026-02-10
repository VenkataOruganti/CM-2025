<?php
/**
 * =============================================================================
 * CENTRALIZED MEASUREMENTS LOADER
 * =============================================================================
 *
 * This file serves as the single source of truth for loading blouse measurements
 * from the database and providing them to individual pattern files.
 *
 * USAGE (Pattern Files):
 * ----------------------
 * Method 1: Include with GET parameters (auto-loads from DB)
 *   $_GET['id'] = 123;           // By measurement ID
 *   $_GET['customer_id'] = 456;  // By customer ID
 *   include 'includes/deepNeck.php';
 *   $m = getMeasurements();      // Get all measurements as array
 *
 * Method 2: Load from existing session (for PDF/SVG generation)
 *   include 'includes/deepNeck.php';
 *   $m = loadMeasurementsFromSession();  // Loads from $_SESSION['measurements']
 *
 * Method 3: Direct access to individual variables
 *   include 'includes/deepNeck.php';
 *   echo $bust;      // Direct variable access
 *   echo $armhole;   // All measurement variables are global
 *
 * AVAILABLE MEASUREMENTS:
 * -----------------------
 * Raw (from DB):  blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength,
 *                 slength, saround, sopen, armhole, chest, bust, waist
 * Customer:       customer_name, customer_id, measurement_id
 * Calculated:     armHoleHeight, halfChest, qChest, halfShoulder, armHoleDepth
 *
 * HELPER FUNCTIONS:
 * -----------------
 * - getMeasurements()              : Returns all measurements as associative array
 * - loadMeasurementsFromSession()  : Loads from session, returns measurements array
 * - getShoulderWidth($bust, $fshoulder, $bnDepth) : Calculate half shoulder width
 * - calculateArmhole()             : Calculate armhole curve points
 * - cubicBezier(), quadToCubicControlPoints() : Bezier curve helpers
 *
 * =============================================================================
 */

// =============================================================================
// SECTION 1: LOOKUP TABLES / FUNCTIONS
// =============================================================================

/**
 * Shoulder Width Lookup Table
 * Returns shoulder width (in inches) based on bust size
 *
 * Bust Size Range    Shoulder Width
 * ----------------   --------------
 * 28" - 31"          4.50"
 * 32" - 34"          5.00"
 * 35" - 38"          5.25"
 * 39" - 41"          5.50"
 * 42" and above      5.75"
 */

/**
 * Calculate half shoulder width
 *
 * Logic:
 * 1. If back neck depth <= 4": Use formula (fshoulder/2) - (bnDepth × 0.25)
 * 2. If back neck depth > 4": Use bust-based lookup table
 *
 * @param float $bust Bust measurement in inches
 * @param float $fshoulder Full shoulder measurement in inches
 * @param float $bnDepth Back neck depth in inches
 * @return float Half shoulder width in inches
 */
function getShoulderWidth($bust, $fshoulder, $bnDepth) {
    if ($bnDepth > 3 && $bnDepth < 9) {
        // Formula for medium back neck depth (3" < bnDepth < 9")
        return ($fshoulder / 2) - ($bnDepth * 0.25);
    } else {
        // Bust-based lookup for deeper back neck
        if ($bust >= 41) {
            return 6;
        } elseif ($bust >= 30 && $bust < 41) {
            return 5.5;
        } elseif ($bust < 30) {
            return 5;
        } else {
            return 5.5;
        }
    }
}

/**
 * Calculate armhole height (AB height) based on bust measurement
 * Formula: (bust / 4) - 1.5
 *
 * @param float $bust Bust measurement in inches
 * @return float Armhole height in inches
 */
function getArmHoleHeight($bust) {
    return ($bust / 4) - 1.5;
}

/**
 * Calculate shoulder line Y offset
 * If back neck depth > 8", add 0.10" drop to shoulder line
 *
 * @param float $bnDepth Back neck depth in inches
 * @return float Y offset in inches (0 or 0.10)
 */
function getShoulderLineYOffset($bnDepth) {
    return ($bnDepth > 8) ? 0.10 : 0;
}

/**
 * Calculate sleeve cap dimensions based on waist, armhole, and saround (bicep)
 *
 * Geometry:
 *              S1 (cap top)
 *             /  \
 *            /    \
 *           /      \   D = diagonal (armhole / 2)
 *          /   |    \
 *         /    |H    \  H = cap height (vertical)
 *        /     |      \
 *       /______|_______\
 *      S2      M       S3
 *
 * - S1 = cap top (peak)
 * - S2, S3 = bicep line corners
 * - M = midpoint of S2-S3
 * - Cap Height (H) = vertical distance from S1 to M
 * - Diagonal (D) = S2→S1 or S1→S3 = armhole / 2
 *
 * Logic:
 * 1. Calculate default cap height based on waist (3.0" if ≤30", else 3.5")
 * 2. Calculate sleeveWidth from armhole using Pythagorean theorem
 * 3. If saround > sleeveWidth: use saround, recalculate cap height
 *    (keeps diagonal = armhole/2 for proper armhole fit)
 *
 * @param float $waist Waist measurement in inches
 * @param float $armhole Full armhole measurement in inches
 * @param float $saround Sleeve round (bicep circumference) in inches (optional)
 * @return array [capHeight, diagonal, halfWidth, sleeveWidth, adjusted]
 */
function getSleeveCapDimensions($waist, $armhole, $saround = 0) {
    // Diagonal = half armhole (S2→S1 or S1→S3) - this is FIXED
    $diagonal = $armhole / 2;

    // Default cap height based on waist
    $defaultCapHeight = ($waist <= 30) ? 3.0 : 3.5;

    // Calculate sleeve width from default cap height
    $defaultHalfWidth = sqrt(($diagonal * $diagonal) - ($defaultCapHeight * $defaultCapHeight));
    $defaultSleeveWidth = 2 * $defaultHalfWidth;

    // Check if saround (bicep) is larger than calculated sleeve width
    $adjusted = false;
    if ($saround > 0 && $saround > $defaultSleeveWidth) {
        // Use saround as the sleeve width
        $sleeveWidth = $saround;
        $halfWidth = $saround / 2;

        // Check if geometry is possible: halfWidth must be < diagonal
        if ($halfWidth >= $diagonal) {
            // Impossible geometry: bicep too large for armhole
            // Fall back to maximum possible width (cap height = 0)
            $halfWidth = $diagonal * 0.95; // Leave small cap height
            $sleeveWidth = 2 * $halfWidth;
            $capHeight = sqrt(($diagonal * $diagonal) - ($halfWidth * $halfWidth));
        } else {
            // Recalculate cap height: H = √(D² - (W/2)²)
            $capHeight = sqrt(($diagonal * $diagonal) - ($halfWidth * $halfWidth));
        }
        $adjusted = true;
    } else {
        // Use default calculated values
        $capHeight = $defaultCapHeight;
        $halfWidth = $defaultHalfWidth;
        $sleeveWidth = $defaultSleeveWidth;
    }

    return [
        'capHeight'   => $capHeight,           // H: vertical S1→M
        'diagonal'    => $diagonal,            // D: S2→S1 or S1→S3
        'halfWidth'   => $halfWidth,           // S2→M or M→S3
        'sleeveWidth' => $sleeveWidth,         // S2→S3 (full width)
        'adjusted'    => $adjusted,            // true if saround override was applied
        'defaultCapHeight' => $defaultCapHeight // Original cap height before adjustment
    ];
}

// -----------------------------------------------------------------------------
// 1.2 Bezier Curve Functions
// -----------------------------------------------------------------------------

/**
 * Calculate cubic bezier point at parameter t
 * @param float $t Parameter (0 to 1)
 * @param float $p0x, $p0y Start point
 * @param float $p1x, $p1y Control point 1
 * @param float $p2x, $p2y Control point 2
 * @param float $p3x, $p3y End point
 * @return array [x, y] coordinates
 */
function cubicBezier($t, $p0x, $p0y, $p1x, $p1y, $p2x, $p2y, $p3x, $p3y) {
    $x = pow(1-$t,3)*$p0x + 3*pow(1-$t,2)*$t*$p1x + 3*(1-$t)*$t*$t*$p2x + pow($t,3)*$p3x;
    $y = pow(1-$t,3)*$p0y + 3*pow(1-$t,2)*$t*$p1y + 3*(1-$t)*$t*$t*$p2y + pow($t,3)*$p3y;
    return [$x, $y];
}

/**
 * Convert quadratic bezier control point to cubic bezier control points
 * Quadratic: P0, Q (control), P2
 * Cubic: P0, C1, C2, P2
 * Where: C1 = P0 + (2/3)(Q - P0), C2 = P2 + (2/3)(Q - P2)
 *
 * @param float $p0x, $p0y Start point
 * @param float $qx, $qy Quadratic control point
 * @param float $p2x, $p2y End point
 * @return array [c1x, c1y, c2x, c2y] Cubic control points
 */
function quadToCubicControlPoints($p0x, $p0y, $qx, $qy, $p2x, $p2y) {
    $c1x = $p0x + (2/3) * ($qx - $p0x);
    $c1y = $p0y + (2/3) * ($qy - $p0y);
    $c2x = $p2x + (2/3) * ($qx - $p2x);
    $c2y = $p2y + (2/3) * ($qy - $p2y);
    return [$c1x, $c1y, $c2x, $c2y];
}

// -----------------------------------------------------------------------------
// 1.3 L-Shape Point Calculation
// -----------------------------------------------------------------------------

/**
 * Calculate all L-shape points for armhole curve
 * @param float $armHoleHeight Vertical height of armhole
 * @param float $halfShoulder Adjusted shoulder width
 * @param float $armHoleDepth Horizontal depth of armhole
 * @param float $scale SVG scale factor (pixels per inch)
 * @param float $armholeBottomCurve Distance from B to B1 at 45° angle (default 1.0")
 * @param float $originX X origin offset (default 50)
 * @param float $originY Y origin offset (default 50)
 * @return array Associative array with all points and control points
 */
function calculateLShapePoints($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve = 1.0, $originX = 50, $originY = 50) {
    global $bnDepth;
    // Point A: Top of L
    // If back neck depth > 8", shoulder aligns with origin (no 0.25" drop)
    $pointA_x = $originX + ($halfShoulder * $scale);
    $pointA_y = ($bnDepth > 8) ? $originY : $originY + (0.25 * $scale);

    // Point B: Corner
    $pointB_x = $originX + ($halfShoulder * $scale);
    $pointB_y = $originY + ($armHoleHeight * $scale);

    // Point AB: Middle between A and B
    $pointAB_x = ($pointA_x + $pointB_x) / 2;
    $pointAB_y = ($pointA_y + $pointB_y) / 2;

    // Point AB2: 0.25" left of AB
    $pointAB2_x = $pointAB_x - (0.25 * $scale);
    $pointAB2_y = $pointAB_y;

    // Point C: End of L
    $pointC_x = $originX + (($halfShoulder + $armHoleDepth) * $scale);
    $pointC_y = $originY + ($armHoleHeight * $scale);

    // Point B1: armholeBottomCurve distance from B at 45°
    $b1_x_offset = $armholeBottomCurve * cos(deg2rad(45));
    $b1_y_offset = $armholeBottomCurve * sin(deg2rad(45));
    $pointB1_x = $pointB_x + ($b1_x_offset * $scale);
    $pointB1_y = $pointB_y - ($b1_y_offset * $scale);

    // Control points for bezier curves
    $ctrl1_x = $pointA_x - (0.25 * $scale);
    $ctrl1_y = $pointAB2_y - (1.0 * $scale);
    $ctrl2a_x = $pointAB2_x;
    $ctrl2a_y = $pointAB2_y + (1.0 * $scale);
    $ctrl2b_x = $pointB1_x - (0.5 * $scale);
    $ctrl2b_y = $pointB1_y;
    $ctrl3_x = $pointB1_x + (0.5 * $scale);
    $ctrl3_y = $pointC_y;

    return [
        'A'  => ['x' => $pointA_x, 'y' => $pointA_y],
        'B'  => ['x' => $pointB_x, 'y' => $pointB_y],
        'AB' => ['x' => $pointAB_x, 'y' => $pointAB_y],
        'AB2'=> ['x' => $pointAB2_x, 'y' => $pointAB2_y],
        'B1' => ['x' => $pointB1_x, 'y' => $pointB1_y],
        'C'  => ['x' => $pointC_x, 'y' => $pointC_y],
        'ctrl1'  => ['x' => $ctrl1_x, 'y' => $ctrl1_y],
        'ctrl2a' => ['x' => $ctrl2a_x, 'y' => $ctrl2a_y],
        'ctrl2b' => ['x' => $ctrl2b_x, 'y' => $ctrl2b_y],
        'ctrl3'  => ['x' => $ctrl3_x, 'y' => $ctrl3_y],
        'b1_offset' => ['x' => $b1_x_offset, 'y' => $b1_y_offset]
    ];
}

// -----------------------------------------------------------------------------
// 1.4 Curve Length Calculation
// -----------------------------------------------------------------------------

/**
 * Calculate curve length for armhole (A → AB2 → B1 → C)
 * @param float $armHoleHeight Vertical height of armhole
 * @param float $halfShoulder Adjusted shoulder width
 * @param float $armHoleDepth Horizontal depth of armhole
 * @param float $scale SVG scale factor (pixels per inch)
 * @param float $armholeBottomCurve Distance from B to B1 at 45° angle (default 1.0")
 * @return float Curve length in inches
 */
function calculateCurveLength($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve = 1.0) {
    $samples = 100;
    $points = calculateLShapePoints($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve);

    $curveLength = 0;

    // Segment 1: A to AB2 (converted from quadratic to cubic)
    list($c1x, $c1y, $c2x, $c2y) = quadToCubicControlPoints(
        $points['A']['x'], $points['A']['y'],
        $points['ctrl1']['x'], $points['ctrl1']['y'],
        $points['AB2']['x'], $points['AB2']['y']
    );
    $prevX = $points['A']['x'];
    $prevY = $points['A']['y'];
    for ($i = 1; $i <= $samples; $i++) {
        $t = $i / $samples;
        list($x, $y) = cubicBezier($t,
            $points['A']['x'], $points['A']['y'],
            $c1x, $c1y, $c2x, $c2y,
            $points['AB2']['x'], $points['AB2']['y']);
        $curveLength += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    // Segment 2: AB2 to B1 (already cubic)
    $prevX = $points['AB2']['x'];
    $prevY = $points['AB2']['y'];
    for ($i = 1; $i <= $samples; $i++) {
        $t = $i / $samples;
        list($x, $y) = cubicBezier($t,
            $points['AB2']['x'], $points['AB2']['y'],
            $points['ctrl2a']['x'], $points['ctrl2a']['y'],
            $points['ctrl2b']['x'], $points['ctrl2b']['y'],
            $points['B1']['x'], $points['B1']['y']);
        $curveLength += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    // Segment 3: B1 to C (converted from quadratic to cubic)
    list($c3x, $c3y, $c4x, $c4y) = quadToCubicControlPoints(
        $points['B1']['x'], $points['B1']['y'],
        $points['ctrl3']['x'], $points['ctrl3']['y'],
        $points['C']['x'], $points['C']['y']
    );
    $prevX = $points['B1']['x'];
    $prevY = $points['B1']['y'];
    for ($i = 1; $i <= $samples; $i++) {
        $t = $i / $samples;
        list($x, $y) = cubicBezier($t,
            $points['B1']['x'], $points['B1']['y'],
            $c3x, $c3y, $c4x, $c4y,
            $points['C']['x'], $points['C']['y']);
        $curveLength += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    return $curveLength / $scale; // Return in inches
}

// -----------------------------------------------------------------------------
// 1.5 Curve Fitting Function
// -----------------------------------------------------------------------------

/**
 * Adjust armHoleHeight to match target armhole length
 * @param float $armHoleHeight Initial armhole height
 * @param float $halfShoulder Adjusted shoulder width
 * @param float $armHoleDepth Horizontal depth of armhole
 * @param float $targetArmhole Target armhole measurement
 * @param float $scale SVG scale factor (pixels per inch)
 * @param float $armholeBottomCurve Distance from B to B1 at 45° angle (default 1.0")
 * @param float $tolerance Tolerance in inches (default 0.01)
 * @param int $maxIterations Maximum iterations (default 100)
 * @return array [adjustedHeight, curveLength, heightAdjustment, iterations]
 */
function fitArmholeCurve($armHoleHeight, $halfShoulder, $armHoleDepth, $targetArmhole, $scale, $armholeBottomCurve = 1.0, $tolerance = 0.01, $maxIterations = 100) {
    $originalHeight = $armHoleHeight;
    $iteration = 0;
    $heightAdjustment = 0;

    $curveLength = calculateCurveLength($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve);

    // If curve length is greater than target, reduce height iteratively
    while ($curveLength > $targetArmhole + $tolerance && $iteration < $maxIterations) {
        $armHoleHeight -= 0.05;
        $heightAdjustment += 0.05;
        $curveLength = calculateCurveLength($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve);
        $iteration++;
    }

    // If curve length is less than target, increase height iteratively
    while ($curveLength < $targetArmhole - $tolerance && $iteration < $maxIterations) {
        $armHoleHeight += 0.05;
        $heightAdjustment -= 0.05;
        $curveLength = calculateCurveLength($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $armholeBottomCurve);
        $iteration++;
    }

    return [
        'adjustedHeight' => $armHoleHeight,
        'originalHeight' => $originalHeight,
        'curveLength' => $curveLength,
        'heightAdjustment' => $heightAdjustment,
        'iterations' => $iteration
    ];
}

// -----------------------------------------------------------------------------
// 1.6 Main Calculation Function - Sets global variables for pattern files
// -----------------------------------------------------------------------------

/**
 * Calculate armhole curve and set all variables globally
 *
 * USAGE:
 *   // Set your measurements first
 *   $bust = 36;
 *   $chest = 34;
 *   $bnDepth = 5.0;
 *   $armhole = 8.5;  // target armhole
 *   $scale = 25.4;
 *
 *   // Call the function
 *   calculateArmhole();
 *
 *   // Now use these variables directly:
 *   // $halfShoulder, $armHoleHeight, $armHoleDepth
 *   // $pointA_x, $pointA_y, $pointB1_x, $pointC_x, etc.
 *   // $armholeSvgPath (ready-to-use SVG path)
 *
 * @param float $bottomCurve Optional: B-B1 distance (default 1.0")
 * @param float $originX Optional: SVG origin X (default 50)
 * @param float $originY Optional: SVG origin Y (default 50)
 * @param float|null $fixedHeight Optional: Fixed armhole height (skips curve fitting if provided)
 */
function calculateArmhole($bottomCurve = 1.0, $originX = 50, $originY = 50, $fixedHeight = null) {
    // Access global input variables
    global $bust, $chest, $waist, $fshoulder, $bnDepth, $armhole, $scale;

    // Declare all output variables as global
    global $halfShoulder;
    global $halfChest, $qChest, $qBust, $qWaist;
    global $armHoleHeight, $originalArmHoleHeight, $armHoleDepth;
    global $targetArmhole, $curveLength, $heightAdjustment, $iterations;
    global $backArmHoleHeight;  // Back pattern's curve-fitted A-B height

    // SVG Points
    global $pointA_x, $pointA_y;
    global $pointB_x, $pointB_y;
    global $pointAB_x, $pointAB_y;
    global $pointAB2_x, $pointAB2_y;
    global $pointB1_x, $pointB1_y;
    global $pointC_x, $pointC_y;
    global $ctrl1_x, $ctrl1_y;
    global $ctrl2a_x, $ctrl2a_y;
    global $ctrl2b_x, $ctrl2b_y;
    global $ctrl3_x, $ctrl3_y;
    global $b1_x_offset, $b1_y_offset;

    // Ready-to-use SVG path
    global $armholeSvgPath;

    // Set defaults if not provided
    $scale = $scale ?? 25.4;
    $armhole = $armhole ?? 0;

    // Calculate target armhole for curve fitting
    // Customer enters full armhole, we use half + 0.5" ease
    $targetArmhole = ($armhole / 2);

    // Step 1: Calculate armhole height
    $armHoleHeight = getArmHoleHeight($bust);
    $originalArmHoleHeight = $armHoleHeight;

    // Step 2: Calculate quarter values (single source of truth)
    $qChest = $chest / 4;
    $qBust = ($bust / 4);    // Quarter bust (no ease)
    $qWaist = ($waist / 4);  // Quarter waist (no ease)

    // Step 3: Calculate half shoulder using revised formula
    // Formula: (full shoulder - (back neck depth / 2)) / 2
    $halfShoulder = getShoulderWidth($bust, $fshoulder, $bnDepth);

    // Step 4: Calculate armhole depth (using qBust for better fit at bust line)
    $armHoleDepth = $qBust - $halfShoulder; // changed from qChest to qBust

    // Step 6: Fit curve to target armhole measurement (or use fixed height if provided)
    if ($fixedHeight !== null) {
        // Use the provided fixed height - skip curve fitting
        $armHoleHeight = $fixedHeight;
        $curveLength = calculateCurveLength($armHoleHeight, $halfShoulder, $armHoleDepth, $scale, $bottomCurve);
        $heightAdjustment = $originalArmHoleHeight - $armHoleHeight;
        $iterations = 0;
    } else {
        // Fit curve to target armhole measurement
        $curveResult = fitArmholeCurve(
            $armHoleHeight,
            $halfShoulder,
            $armHoleDepth,
            $targetArmhole,
            $scale,
            $bottomCurve
        );

        $armHoleHeight = $curveResult['adjustedHeight'];
        $curveLength = $curveResult['curveLength'];
        $heightAdjustment = $curveResult['heightAdjustment'];
        $iterations = $curveResult['iterations'];
    }

    // Step 7: Get all SVG points
    $points = calculateLShapePoints(
        $armHoleHeight,
        $halfShoulder,
        $armHoleDepth,
        $scale,
        $bottomCurve,
        $originX,
        $originY
    );

    // Extract to individual variables
    $pointA_x = $points['A']['x'];
    $pointA_y = $points['A']['y'];
    $pointB_x = $points['B']['x'];
    $pointB_y = $points['B']['y'];
    $pointAB_x = $points['AB']['x'];
    $pointAB_y = $points['AB']['y'];
    $pointAB2_x = $points['AB2']['x'];
    $pointAB2_y = $points['AB2']['y'];
    $pointB1_x = $points['B1']['x'];
    $pointB1_y = $points['B1']['y'];
    $pointC_x = $points['C']['x'];
    $pointC_y = $points['C']['y'];
    $ctrl1_x = $points['ctrl1']['x'];
    $ctrl1_y = $points['ctrl1']['y'];
    $ctrl2a_x = $points['ctrl2a']['x'];
    $ctrl2a_y = $points['ctrl2a']['y'];
    $ctrl2b_x = $points['ctrl2b']['x'];
    $ctrl2b_y = $points['ctrl2b']['y'];
    $ctrl3_x = $points['ctrl3']['x'];
    $ctrl3_y = $points['ctrl3']['y'];
    $b1_x_offset = $points['b1_offset']['x'];
    $b1_y_offset = $points['b1_offset']['y'];

    // Generate ready-to-use SVG path
    $armholeSvgPath = sprintf(
        'M %.2f,%.2f Q %.2f,%.2f %.2f,%.2f C %.2f,%.2f %.2f,%.2f %.2f,%.2f Q %.2f,%.2f %.2f,%.2f',
        $pointA_x, $pointA_y,
        $ctrl1_x, $ctrl1_y, $pointAB2_x, $pointAB2_y,
        $ctrl2a_x, $ctrl2a_y, $ctrl2b_x, $ctrl2b_y, $pointB1_x, $pointB1_y,
        $ctrl3_x, $ctrl3_y, $pointC_x, $pointC_y
    );

    // Step 8: Calculate BACK pattern's A-B height using binary search
    // Back pattern uses z8→z71→z6 path with 0.25" z8 offset
    $backTargetArmhole = ($armhole / 2) * $scale;
    $backTolerance = 0.05 * $scale;  // 0.05" tolerance
    $minHeight = 3.0;
    $maxHeight = 12.0;
    $backArmHoleHeight = 6.0;  // Start with 6" and adjust

    for ($i = 0; $i < 50; $i++) {
        $midHeight = ($minHeight + $maxHeight) / 2;
        $currentLength = calculatePathThroughB1(
            $midHeight, $halfShoulder, $armHoleDepth, $scale, $bottomCurve, $originX, $originY
        );

        $diff = $currentLength - $backTargetArmhole;

        if (abs($diff) < $backTolerance) {
            $backArmHoleHeight = $midHeight;
            break;
        }

        if ($currentLength < $backTargetArmhole) {
            $minHeight = $midHeight;
        } else {
            $maxHeight = $midHeight;
        }

        $backArmHoleHeight = $midHeight;
    }
}

// -----------------------------------------------------------------------------
// 1.7 Back Pattern Armhole Path Calculation (z8→z71→z6)
// -----------------------------------------------------------------------------

/**
 * Calculate cubic bezier curve length
 * Used by back pattern's z8→z71→z6 path calculation
 */
if (!function_exists('calcCubicLength')) {
    function calcCubicLength($x0, $y0, $c1x, $c1y, $c2x, $c2y, $x1, $y1, $segments = 50) {
        $length = 0;
        $prevX = $x0;
        $prevY = $y0;
        for ($i = 1; $i <= $segments; $i++) {
            $t = $i / $segments;
            $t1 = 1 - $t;
            $x = $t1*$t1*$t1*$x0 + 3*$t1*$t1*$t*$c1x + 3*$t1*$t*$t*$c2x + $t*$t*$t*$x1;
            $y = $t1*$t1*$t1*$y0 + 3*$t1*$t1*$t*$c1y + 3*$t1*$t*$t*$c2y + $t*$t*$t*$y1;
            $length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
            $prevX = $x;
            $prevY = $y;
        }
        return $length;
    }
}

/**
 * Calculate path length for back pattern: z8 → z71 (line) → z6 (cubic curve through B1)
 * This is the actual path used by the back pattern's armhole
 */
if (!function_exists('calculatePathThroughB1')) {
    function calculatePathThroughB1($armHoleHt, $halfShldr, $armHoleDepth, $scl, $bottomCurve, $originX, $originY) {
        global $bnDepth;
        // Point z8: top of armhole
        // If back neck depth > 8", shoulder aligns with origin (no 0.25" drop)
        $z8_x = $originX + ($halfShldr * $scl);
        $z8_y = ($bnDepth > 8) ? $originY : $originY + (0.25 * $scl);

        // Point B (corner at armhole height)
        $B_x = $z8_x;
        $B_y = $originY + ($armHoleHt * $scl);

        // Point z71: midpoint between z8 and B
        $z71_x = $z8_x;
        $z71_y = ($z8_y + $B_y) / 2;

        // Point B1: 45° from B
        $b1_offset = $bottomCurve * cos(deg2rad(45)) * $scl;
        $B1_x = $B_x + $b1_offset;
        $B1_y = $B_y - $b1_offset;

        // Point z6: end of armhole
        $z6_x = $originX + (($halfShldr + $armHoleDepth) * $scl);
        $z6_y = $B_y;

        // Segment 1: z8 to z71 (straight line)
        $seg1 = sqrt(pow($z71_x - $z8_x, 2) + pow($z71_y - $z8_y, 2));

        // Segment 2: z71 to z6 (cubic bezier through B1)
        $t = 0.5;
        $t1 = 1 - $t;
        $coef_start = $t1 * $t1 * $t1;
        $coef_c1 = 3 * $t1 * $t1 * $t;
        $coef_c2 = 3 * $t1 * $t * $t;
        $coef_end = $t * $t * $t;

        $ctrl1_x = $z71_x;  // Vertical tangent at start
        $ctrl2_y = $z6_y;   // Horizontal tangent at end

        $ctrl2_x = ($B1_x - $coef_start*$z71_x - $coef_c1*$ctrl1_x - $coef_end*$z6_x) / $coef_c2;
        $ctrl1_y = ($B1_y - $coef_start*$z71_y - $coef_c2*$ctrl2_y - $coef_end*$z6_y) / $coef_c1;

        $seg2 = calcCubicLength($z71_x, $z71_y, $ctrl1_x, $ctrl1_y, $ctrl2_x, $ctrl2_y, $z6_x, $z6_y);

        return $seg1 + $seg2;
    }
}

/**
 * Fit back pattern's armhole curve to target length
 * Uses the z8→z71→z6 path (different from front's A→AB2→B1→C path)
 *
 * @param float $initialHeight Starting A-B height
 * @param float $halfShoulder Half shoulder width
 * @param float $armHoleDepth Armhole depth
 * @param float $targetArmhole Target armhole length (armhole/2)
 * @param float $scale SVG scale factor
 * @param float $bottomCurve B-B1 distance at 45°
 * @param float $originX SVG origin X
 * @param float $originY SVG origin Y
 * @return float Curve-fitted A-B height
 */
if (!function_exists('fitBackArmholePath')) {
    function fitBackArmholePath($initialHeight, $halfShoulder, $armHoleDepth, $targetArmhole, $scale, $bottomCurve, $originX, $originY) {
        $backArmHoleHeight = $initialHeight;
        $tolerance = 0.5;
        $maxIterations = 100;

        for ($i = 0; $i < $maxIterations; $i++) {
            $currentLength = calculatePathThroughB1(
                $backArmHoleHeight, $halfShoulder, $armHoleDepth, $scale, $bottomCurve, $originX, $originY
            );

            $diff = $currentLength - $targetArmhole;

            if (abs($diff) < $tolerance) {
                break;
            }

            if ($currentLength < $targetArmhole) {
                $backArmHoleHeight += 0.05;
            } else {
                $backArmHoleHeight -= 0.05;
            }
        }

        return $backArmHoleHeight;
    }
}

// =============================================================================
// SECTION 2: VARIABLE INITIALIZATION
// =============================================================================

// Request parameters
$measurementId = $_GET['id'] ?? null;
$customerId = $_GET['customer_id'] ?? null;

// -----------------------------------------------------------------------------
// 2.1 Raw Measurements from Database (14 blouse measurements)
// Only initialize if not already set (allows override for testing)
// -----------------------------------------------------------------------------
$blength   = $blength ?? 0;    // Blouse Length
$fshoulder = $fshoulder ?? 0;    // Full Shoulder
$shoulder  = $shoulder ?? 0;    // Shoulder Width
$bnDepth   = $bnDepth ?? 0;    // Back Neck Depth
$fndepth   = $fndepth ?? 0;    // Front Neck Depth
$apex      = $apex ?? 0;    // Apex Point
$flength   = $flength ?? 0;    // Front Length
$chest     = $chest ?? 0;    // Chest Measurement
$bust      = $bust ?? 0;    // Bust Measurement
$waist     = $waist ?? 0;    // Waist Measurement
$slength   = $slength ?? 0;    // Sleeve Length
$saround   = $saround ?? 0;    // Sleeve Around
$sopen     = $sopen ?? 0;    // Sleeve Opening
$armhole   = $armhole ?? 0;    // Armhole Measurement
$cust      = $cust ?? '';   // Customer Name

// -----------------------------------------------------------------------------
// 2.2 Calculated Values for Armhole Curve
// Only initialize if not already set (allows override for testing)
// -----------------------------------------------------------------------------
$halfShoulder   = $halfShoulder ?? 0;    // Half shoulder width: (shoulder - bnDepth/2) / 2
$halfChest      = $halfChest ?? 0;    // Half of chest measurement
$qChest         = $qChest ?? 0;      // Quarter of chest measurement (chest / 4)
$qBust          = $qBust ?? 0;       // Quarter bust with 0.5" ease: (bust / 4) + 0.5
$qWaist         = $qWaist ?? 0;      // Quarter waist with 0.5" ease: (waist / 4) + 0.5
$armHoleHeight  = $armHoleHeight ?? 0;    // Vertical height of armhole curve
$armHoleDepth   = $armHoleDepth ?? 0;    // Horizontal depth of armhole curve (qChest - halfShoulder)

// =============================================================================
// SECTION 3: DATABASE LOADING & SESSION STORAGE
// =============================================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($measurementId || $customerId) {
    try {
        global $pdo;

        // Build query based on available ID
        if ($measurementId) {
            $stmt = $pdo->prepare("
                SELECT m.*, c.customer_name
                FROM measurements m
                LEFT JOIN customers c ON m.customer_id = c.id
                WHERE m.id = ?
            ");
            $stmt->execute([$measurementId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT m.*, c.customer_name
                FROM measurements m
                LEFT JOIN customers c ON m.customer_id = c.id
                WHERE m.customer_id = ?
                ORDER BY m.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$customerId]);
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // -----------------------------------------------------------------
            // 3.1 Load Raw Measurements
            // -----------------------------------------------------------------
            $blength   = floatval($data['blength'] ?? 0);
            $fshoulder = floatval($data['fshoulder'] ?? 0);
            $shoulder  = floatval($data['shoulder'] ?? 0);
            $bnDepth   = floatval($data['bnDepth'] ?? 0);
            $fndepth   = floatval($data['fndepth'] ?? 0);
            $apex      = floatval($data['apex'] ?? 0);
            $flength   = floatval($data['flength'] ?? 0);
            $slength   = floatval($data['slength'] ?? 0);
            $saround   = floatval($data['saround'] ?? 0);
            $sopen     = floatval($data['sopen'] ?? 0);
            $armhole   = floatval($data['armhole'] ?? 0);
            $cust      = $data['customer_name'] ?? '';

            // Round chest, bust, waist to nearest whole number
            $chest     = round(floatval($data['chest'] ?? 0));
            $bust      = round(floatval($data['bust'] ?? 0));
            $waist     = round(floatval($data['waist'] ?? 0));

            // -----------------------------------------------------------------
            // 3.2 Calculate Derived Values for Armhole Curve
            // -----------------------------------------------------------------

            // Step 1: Calculate armhole height
            $armHoleHeight = getArmHoleHeight($bust);

            // Step 2: Calculate quarter values (single source of truth)
            $halfChest = $chest / 2;
            $qChest = $chest / 4;
            $qBust = ($bust / 4);    // Quarter bust (no ease)
            $qWaist = ($waist / 4);  // Quarter waist (no ease)

            // Step 3: Calculate half shoulder using NEW FORMULA
            // Formula: (full shoulder - (back neck depth / 2)) / 2
            // This accounts for back neck depth directly and uses actual shoulder measurement
            $halfShoulder = getShoulderWidth($bust, $fshoulder, $bnDepth);

            // Step 5: Calculate armhole depth (using qBust for better fit at bust line)
            // Formula: qBust - halfShoulder
            $armHoleDepth = $qBust - $halfShoulder;

            // -----------------------------------------------------------------
            // 3.3 Store All Measurements in Session (Global Access)
            // -----------------------------------------------------------------
            $_SESSION['measurements'] = [
                // Raw measurements from database
                'blength'   => $blength,
                'fshoulder' => $fshoulder,
                'shoulder'  => $shoulder,
                'bnDepth'   => $bnDepth,
                'fndepth'   => $fndepth,
                'apex'      => $apex,
                'flength'   => $flength,
                'slength'   => $slength,
                'saround'   => $saround,
                'sopen'     => $sopen,
                'armhole'   => $armhole,
                'chest'     => $chest,
                'bust'      => $bust,
                'waist'     => $waist,

                // Customer info
                'customer_name' => $cust,
                'customer_id'   => $data['customer_id'] ?? null,
                'measurement_id' => $data['id'] ?? null,

                // Calculated derived values
                'armHoleHeight' => $armHoleHeight,
                'halfChest'     => $halfChest,
                'qChest'        => $qChest,
                'qBust'         => $qBust,
                'qWaist'        => $qWaist,
                'halfShoulder'  => $halfShoulder,
                'armHoleDepth'  => $armHoleDepth,

                // Metadata
                'loaded_at'     => time(),
                'scale'         => 25.4
            ];

            // -----------------------------------------------------------------
            // 3.4 Display Measurements Table (Console Output)
            // -----------------------------------------------------------------
            if (php_sapi_name() === 'cli' || (isset($_GET['debug']) && $_GET['debug'] === 'measurements')) {
                echo "\n";
                echo "================================================================================\n";
                echo "MEASUREMENTS FROM DATABASE & CALCULATED VARIABLES\n";
                echo "================================================================================\n\n";

                echo "DATABASE MEASUREMENTS:\n";
                echo str_repeat("-", 80) . "\n";
                printf("%-25s | %-15s | %-30s\n", "Field", "Value", "Description");
                echo str_repeat("-", 80) . "\n";
                printf("%-25s | %15.2f | %-30s\n", "blength", $blength, "Blouse Length");
                printf("%-25s | %15.2f | %-30s\n", "fshoulder", $fshoulder, "Front Shoulder");
                printf("%-25s | %15.2f | %-30s\n", "shoulder", $shoulder, "Shoulder Width");
                printf("%-25s | %15.2f | %-30s\n", "bnDepth", $bnDepth, "Back Neck Depth");
                printf("%-25s | %15.2f | %-30s\n", "fndepth", $fndepth, "Front Neck Depth");
                printf("%-25s | %15.2f | %-30s\n", "apex", $apex, "Apex");
                printf("%-25s | %15.2f | %-30s\n", "flength", $flength, "Front Length");
                printf("%-25s | %15.2f | %-30s\n", "slength", $slength, "Sleeve Length");
                printf("%-25s | %15.2f | %-30s\n", "saround", $saround, "Sleeve Around");
                printf("%-25s | %15.2f | %-30s\n", "sopen", $sopen, "Sleeve Opening");
                printf("%-25s | %15.2f | %-30s\n", "armhole", $armhole, "Armhole");
                printf("%-25s | %15.2f | %-30s\n", "chest", $chest, "Chest (rounded)");
                printf("%-25s | %15.2f | %-30s\n", "bust", $bust, "Bust (rounded)");
                printf("%-25s | %15.2f | %-30s\n", "waist", $waist, "Waist (rounded)");
                printf("%-25s | %15s | %-30s\n", "customer_name", $cust, "Customer Name");
                echo str_repeat("-", 80) . "\n\n";

                echo "CALCULATED VARIABLES:\n";
                echo str_repeat("-", 80) . "\n";
                printf("%-25s | %-15s | %-30s\n", "Variable", "Value", "Formula/Description");
                echo str_repeat("-", 80) . "\n";
                printf("%-25s | %15.2f | %-30s\n", "armHoleHeight", $armHoleHeight, "(bust / 4) - 1.5");
                printf("%-25s | %15.2f | %-30s\n", "halfChest", $halfChest, "chest / 2");
                printf("%-25s | %15.2f | %-30s\n", "qChest", $qChest, "chest / 4");
                printf("%-25s | %15.2f | %-30s\n", "halfShoulder", $halfShoulder, "(shoulder - bnDepth/2) / 2");
                printf("%-25s | %15.2f | %-30s\n", "armHoleDepth", $armHoleDepth, "qChest - halfShoulder");
                echo str_repeat("-", 80) . "\n\n";

                echo "SHOULDER CALCULATION (NEW FORMULA):\n";
                echo str_repeat("-", 80) . "\n";
                printf("Formula: halfShoulder = (shoulder - (bnDepth / 2)) / 2\n");
                printf("shoulder = %.2f\n", $shoulder);
                printf("bnDepth = %.2f\n", $bnDepth);
                printf("halfShoulder = (%.2f - (%.2f / 2)) / 2\n", $shoulder, $bnDepth);
                printf("halfShoulder = (%.2f - %.2f) / 2\n", $shoulder, $bnDepth / 2);
                printf("halfShoulder = %.2f / 2\n", $shoulder - ($bnDepth / 2));
                printf("halfShoulder = %.2f\n", $halfShoulder);
                echo str_repeat("-", 80) . "\n";
                echo "================================================================================\n\n";
            }
        }
    } catch (Exception $e) {
        // Silent fail - variables remain at default 0
    }
}

// =============================================================================
// SECTION 4: CONVENIENCE FUNCTIONS FOR PATTERN FILES
// =============================================================================

/**
 * Get all measurements as an associative array
 *
 * This is the recommended way for pattern files to access measurements.
 * Returns measurements from session if available, otherwise from global variables.
 *
 * USAGE:
 *   include 'includes/deepNeck.php';
 *   $m = getMeasurements();
 *   echo $m['bust'];          // Access bust measurement
 *   echo $m['armhole'];       // Access armhole measurement
 *   echo $m['customer_name']; // Access customer name
 *
 * @return array Associative array with all measurements
 */
function getMeasurements() {
    // Prefer session data if available
    if (isset($_SESSION['measurements']) && !empty($_SESSION['measurements'])) {
        return $_SESSION['measurements'];
    }

    // Fall back to global variables
    global $blength, $fshoulder, $shoulder, $bnDepth, $fndepth, $apex, $flength;
    global $slength, $saround, $sopen, $armhole, $chest, $bust, $waist, $cust;
    global $armHoleHeight, $halfChest, $qChest, $qBust, $qWaist, $halfShoulder, $armHoleDepth;
    global $customerId, $measurementId;

    return [
        // Raw measurements from database
        'blength'   => $blength,
        'fshoulder' => $fshoulder,
        'shoulder'  => $shoulder,
        'bnDepth'   => $bnDepth,
        'fndepth'   => $fndepth,
        'apex'      => $apex,
        'flength'   => $flength,
        'slength'   => $slength,
        'saround'   => $saround,
        'sopen'     => $sopen,
        'armhole'   => $armhole,
        'chest'     => $chest,
        'bust'      => $bust,
        'waist'     => $waist,

        // Customer info
        'customer_name'  => $cust,
        'customer_id'    => $customerId,
        'measurement_id' => $measurementId,

        // Calculated derived values
        'armHoleHeight' => $armHoleHeight,
        'halfChest'     => $halfChest,
        'qChest'        => $qChest,
        'qBust'         => $qBust,
        'qWaist'        => $qWaist,
        'halfShoulder'  => $halfShoulder,
        'armHoleDepth'  => $armHoleDepth,

        // Metadata
        'loaded_at' => time(),
        'scale'     => 25.4
    ];
}

/**
 * Load measurements from session and populate global variables
 *
 * Use this when generating PDF/SVG from a previously loaded pattern.
 * Extracts session data back into global variables for backward compatibility.
 *
 * USAGE:
 *   include 'includes/deepNeck.php';
 *   $m = loadMeasurementsFromSession();
 *   if ($m) {
 *       // Measurements loaded successfully
 *       echo $bust;  // Global variable now available
 *   }
 *
 * @return array|false Returns measurements array on success, false if no session data
 */
function loadMeasurementsFromSession() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if measurements exist in session
    if (!isset($_SESSION['measurements']) || empty($_SESSION['measurements'])) {
        return false;
    }

    $m = $_SESSION['measurements'];

    // Populate global variables for backward compatibility
    global $blength, $fshoulder, $shoulder, $bnDepth, $fndepth, $apex, $flength;
    global $slength, $saround, $sopen, $armhole, $chest, $bust, $waist, $cust;
    global $armHoleHeight, $halfChest, $qChest, $qBust, $qWaist, $halfShoulder, $armHoleDepth;
    global $customerId, $measurementId;

    // Raw measurements
    $blength   = $m['blength'] ?? 0;
    $fshoulder = $m['fshoulder'] ?? 0;
    $shoulder  = $m['shoulder'] ?? 0;
    $bnDepth   = $m['bnDepth'] ?? 0;
    $fndepth   = $m['fndepth'] ?? 0;
    $apex      = $m['apex'] ?? 0;
    $flength   = $m['flength'] ?? 0;
    $slength   = $m['slength'] ?? 0;
    $saround   = $m['saround'] ?? 0;
    $sopen     = $m['sopen'] ?? 0;
    $armhole   = $m['armhole'] ?? 0;
    $chest     = $m['chest'] ?? 0;
    $bust      = $m['bust'] ?? 0;
    $waist     = $m['waist'] ?? 0;

    // Customer info
    $cust          = $m['customer_name'] ?? '';
    $customerId    = $m['customer_id'] ?? null;
    $measurementId = $m['measurement_id'] ?? null;

    // Calculated values
    $armHoleHeight = $m['armHoleHeight'] ?? 0;
    $halfChest     = $m['halfChest'] ?? 0;
    $qChest        = $m['qChest'] ?? 0;
    $qBust         = $m['qBust'] ?? 0;
    $qWaist        = $m['qWaist'] ?? 0;
    $halfShoulder  = $m['halfShoulder'] ?? 0;
    $armHoleDepth  = $m['armHoleDepth'] ?? 0;

    return $m;
}

/**
 * Check if measurements are loaded (either from DB or session)
 *
 * @return bool True if measurements are available
 */
function hasMeasurements() {
    // Check session first
    if (isset($_SESSION['measurements']) && !empty($_SESSION['measurements']['bust'])) {
        return true;
    }

    // Check global variables
    global $bust;
    return isset($bust) && $bust > 0;
}

/**
 * Get a single measurement value
 *
 * @param string $key The measurement key (e.g., 'bust', 'armhole')
 * @param mixed $default Default value if measurement not found
 * @return mixed The measurement value or default
 */
function getMeasurement($key, $default = 0) {
    $m = getMeasurements();
    return $m[$key] ?? $default;
}
