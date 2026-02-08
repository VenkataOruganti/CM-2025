<?php
/**
 * =============================================================================
 * BLOUSE MEASUREMENTS LOADER - Deep Neck Pattern with Armhole Curve
 * =============================================================================
 *
 * Loads blouse measurements from database and calculates armhole curve points.
 *
 * USAGE:
 *   $_GET['id'] = 123;           // By measurement ID
 *   $_GET['customer_id'] = 456;  // By customer ID
 *   include 'includes/deepNeck.php';
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

function getShoulderWidth($bust) {
    if ($bust >= 42) {
        return 5.75;
    } elseif ($bust >= 39) {
        return 5.5;
    } elseif ($bust >= 35) {
        return 5.25;
    } elseif ($bust >= 32) {
        return 5.0;
    } else {
        return 4.5;
    }
}
 */

/**
 * Calculate half shoulder width using the revised formula:
 * (full shoulder - (back neck depth / 2)) / 2
 *
 * @param float $fshoulder Full shoulder measurement in inches
 * @param float $bnDepth Back neck depth in inches
 * @return float Half shoulder width in inches
 */
function getShoulderWidth($fshoulder, $bnDepth) {
    return ($fshoulder - ($bnDepth / 2)) / 2;
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
    // Point A: Top of L - 0.5" from top
    $pointA_x = $originX + ($halfShoulder * $scale);
    $pointA_y = $originY + (0.5 * $scale);

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
 */
function calculateArmhole($bottomCurve = 1.0, $originX = 50, $originY = 50) {
    // Access global input variables
    global $bust, $chest, $fshoulder, $bnDepth, $armhole, $scale;

    // Declare all output variables as global
    global $halfShoulder;
    global $halfChest, $qChest;
    global $armHoleHeight, $originalArmHoleHeight, $armHoleDepth;
    global $targetArmhole, $curveLength, $heightAdjustment, $iterations;

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
    $targetArmhole = ($armhole / 2) + 0.5;

    // Step 1: Calculate armhole height
    $armHoleHeight = ($bust / 4) - 1.5;
    $originalArmHoleHeight = $armHoleHeight;

    // Step 2: Calculate chest values
    $qChest = $chest / 4;

    // Step 3: Calculate half shoulder using revised formula
    // Formula: (full shoulder - (back neck depth / 2)) / 2
    $halfShoulder = getShoulderWidth($fshoulder, $bnDepth);

    // Step 4: Calculate armhole depth
    $armHoleDepth = $qChest - $halfShoulder;

    // Step 6: Fit curve to target armhole measurement
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
            // Formula: (bust / 4) - 1.5
            $armHoleHeight = ($bust / 4) - 1.5;

            // Step 2: Calculate half chest and quarter chest
            $halfChest = $chest / 2;
            $qChest = $chest / 4;

            // Step 3: Calculate half shoulder using NEW FORMULA
            // Formula: (full shoulder - (back neck depth / 2)) / 2
            // This accounts for back neck depth directly and uses actual shoulder measurement
            $halfShoulder = getShoulderWidth($fshoulder, $bnDepth);

            // Step 5: Calculate armhole depth
            // Formula: qChest - halfShoulder
            $armHoleDepth = $qChest - $halfShoulder;

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
