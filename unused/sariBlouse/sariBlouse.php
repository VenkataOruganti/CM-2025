<?php
/**
 * Saree Blouse Pattern Generator - Version 2
 *
 * REORGANIZED STRUCTURE:
 * 1. CONFIGURATION & DATA LOADING (lines 1-94 from original)
 * 2. ALL CALCULATIONS - Business Logic Layer:
 *    - Front & Back patterns (lines 95-952 from original)
 *    - Patti calculations (MOVED from lines 1458-1711)
 *    - Sleeve calculations (MOVED from lines 2130-2935)
 *    - Pattern data structure & session storage (NEW)
 * 3. PRESENTATION LAYER - Pure HTML/SVG Rendering (lines 955+ from original)
 */
/**
 * Saree Blouse Pattern Generator
 *
 * Structure:
 * 1. CONFIGURATION & DATA LOADING
 * 2. BUSINESS LOGIC (Calculations, Nodes, Paths)
 * 3. PRESENTATION LAYER (HTML/SVG Rendering)
 */

// =============================================================================
// SECTION 1: CONFIGURATION & DATA LOADING
// =============================================================================

// Database and armhole functions
// Handle both direct access and inclusion from pattern-preview.php
if (!isset($pdo)) {
    // Try relative path first, then from root
    $dbPath = __DIR__ . '/../../config/database.php';
    if (!file_exists($dbPath)) {
        $dbPath = $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
    }
    require_once $dbPath;
}

// IMPORTANT: deepNeck.php loads measurements from database and stores in $_SESSION['measurements']
if (!function_exists('findDeepNeckControlPoint')) {
    // Try relative path first, then from root
    $aiPath = __DIR__ . '/../../includes/deepNeck.php';
    if (!file_exists($aiPath)) {
        $aiPath = $_SERVER['DOCUMENT_ROOT'] . '/includes/deepNeck.php';
    }
    require_once $aiPath;
}

// Detect if we're being included from pattern-preview.php
$isIncludedFromPreview = (basename($_SERVER['PHP_SELF']) === 'pattern-preview.php');

// STEP 1: Get parameters from URL
$customerId = $_GET['customer_id'] ?? null;
$measurementId = $_GET['measurement_id'] ?? null;
$mode = $_GET['mode'] ?? 'dev';

// Set mode flags
$isDevMode = ($mode === 'dev');
$isPrintMode = ($mode === 'print');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if measurements are loaded in session by deepNeck.php
if (!isset($_SESSION['measurements'])) {
    die("Error: Measurements not loaded. Please ensure deepNeck.php has loaded the data.");
}

// Load all measurements from session (already loaded by deepNeck.php)
$measurements = $_SESSION['measurements'];
$customerName = $measurements['customer_name'] ?? 'Unknown';

// =============================================================================
// SECTION 2: BUSINESS LOGIC (Calculations, Nodes, Paths)
// =============================================================================

// STEP 2: Extract measurements from session
$bust     = $measurements['bust'];
$chest    = $measurements['chest'];
$waist    = $measurements['waist'];
$bnDepth  = $measurements['bnDepth'];
$armhole  = $measurements['armhole'];
$shoulder = $measurements['shoulder'];
$frontNeckDepth = $measurements['fndepth'];
$fshoulder = $measurements['fshoulder'];
$blength  = $measurements['blength'];
$flength  = $measurements['flength'];
$slength  = $measurements['slength'];
$apex     = $measurements['apex'];
$saround  = $measurements['saround'];
$sopen    = $measurements['sopen'];
$scale    = $measurements['scale'];

// STEP 3: SVG Setup - Margins
// =============================================================================
$marginLeft = 0.5 * $scale;  // 0.5" left margin
$marginTop  = 1.0 * $scale;  // 1.0" top margin

// Origin point (where pattern starts)
$originX = $marginLeft;
$originY = $marginTop;

// STEP 4: Calculate armhole (with origin offset)
// =============================================================================
calculateArmhole(1.0, $originX, $originY);

// STEP 5: NODE SYSTEM - Reference points for pattern building
// =============================================================================
// Nodes are named reference points in ANTI-CLOCKWISE order
// Format: $node['name'] = ['x' => pixels, 'y' => pixels, 'label' => 'description', 'code' => 'PHP formula']

$nodes = [];

// Pre-calculate values needed for nodes
$qWaist = ($waist / 4) + 0.5;
$qBust = ($bust / 4) + 0.5;
$shoulderLine_x = $pointA_x - ($shoulder * $scale);

// Front neck depth calculation (diagonal from a10 to a1)
$a1_horizontal = $shoulderLine_x - $originX;  // horizontal distance from a1 to shoulder line
$a1_diagonal = $frontNeckDepth * $scale;
$a1_vertical = sqrt(pow($a1_diagonal, 2) - pow($a1_horizontal, 2));  // vertical distance
$a1_y = $originY + $a1_vertical;

// Back neck depth calculation (diagonal from z9 to z1)
// Back neck length = bnDepth (diagonal distance from z9 to z1)
// z9 is at (shoulderLine_x, originY), z1 is at (originX, z1_y)
$z1_horizontal = $shoulderLine_x - $originX;  // horizontal distance from z1 to z9
$z1_diagonal = $bnDepth * $scale;  // diagonal back neck length
$z1_vertical = sqrt(pow($z1_diagonal, 2) - pow($z1_horizontal, 2));  // vertical distance
$z1_y = $originY + $z1_vertical;

$midWaistX = ($originX + $originX + ($qWaist * $scale)) / 2;

// Shoulder Mid x-coordinate (needed for a4 and b1)
$shoulderMid_x = (($pointA_x - ($shoulder * $scale)) + $pointA_x) / 2;
$shoulderMid_y = ($originY + $pointA_y) / 2;

// Bottom Tuck Width
$bottomTuckWidth = (($bust - $waist) / 4) + 0.5;

// Anti-clockwise from Origin (top-left):
// a0 (Origin/gray) -> a1 (Front Neck) -> a3 (Front Length) -> a4 (Midpoint waist) -> a5 (Waist)
// -> a6 (Bust point) -> a7 (Intersection) -> a8 (Armhole End) -> a9 (Armhole Corner) -> a10 (Shoulder)
// -> a11 (Shoulder Line) -> a111 (Midpoint neck curve) -> back to a1
// sMid is a reference point (gray) at midpoint of shoulder line

// Node 0: Origin (top-left corner) - GRAY reference point
$nodes['a0'] = [
    'x' => $originX,
    'y' => $originY,
    'label' => 'Origin',
    'color' => 'gray',
    'code' => '$a0 = originX, originY'
];

// Node 1: Front Neck Depth (diagonal distance from a10 to a1)
$nodes['a1'] = [
    'x' => $originX,
    'y' => $originY + $a1_vertical,
    'label' => 'Front Neck',
    'code' => '$a1 = diagonal ' . number_format($frontNeckDepth, 2) . '" from a10'
];

// Node 2: REMOVED - a2 (Apex) no longer in rendered path
// Apex level is now referenced as: a0.y + apex

// Node 3: Front Length point (bottom left corner - vertical down from a1)
$nodes['a3'] = [
    'x' => $originX,
    'y' => $originY + (($flength -1.0) * $scale),
    'label' => 'Front Length',
    'code' => '$a3 = a0.x, a0.y + flength(' . number_format($flength, 2) . '")'
];

// Node 4: Waist curve control point (x = b1.x (shoulder midpoint), y = a11.y + flength + 0.5")
$nodes['a4'] = [
    'x' => $shoulderMid_x,  // Same x as b1 (shoulder midpoint)
    'y' => $originY + (($flength + 0.5) * $scale),  // a11.y = originY, add 0.5" to flength
    'label' => 'a4',
    'code' => '$a4 = b1.x, a11.y + flength(' . number_format($flength, 2) . '") + 0.5"'
];

// Node 5: Waist point (same x as a6, y = a4.y - 1")
$nodes['a5'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $nodes['a4']['y'] - (1.0 * $scale),
    'label' => 'Waist',
    'code' => '$a5 = a6.x, a4.y - 1"'
];

// Node 6: Bust point at apex level
$nodes['a6'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $originY + ($apex * $scale),
    'label' => 'a6',
    'code' => '$a6 = qBust(' . number_format($qBust, 2) . '"), a0.y + apex(' . number_format($apex, 2) . '")'
];

// Node 7: Intersection of armhole level and bust line
$nodes['a7'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $pointC_y,
    'label' => 'a7',
    'code' => '$a7 = qBust(' . number_format($qBust, 2) . '"), a8.y'
];

// Node 8: Armhole End (Point C)
$nodes['a8'] = [
    'x' => $pointC_x,
    'y' => $pointC_y,
    'label' => 'Armhole End',
    'code' => '$a8 = pointC_x, pointC_y'
];

// Node 9: Armhole Corner (45° bend - Point B1)
$nodes['a9'] = [
    'x' => $pointB1_x,
    'y' => $pointB1_y,
    'label' => 'Armhole Corner',
    'code' => '$a9 = pointB1_x, pointB1_y'
];

// Node 91: Armhole Curve Midpoint (AB2 - on curve between a9 and a10)
$nodes['a91'] = [
    'x' => $pointAB2_x,
    'y' => $pointAB2_y,
    'label' => 'Armhole Mid',
    'code' => '$a91 = pointAB2_x, pointAB2_y'
];

// Node 10: Shoulder (top of armhole - Point A)
$nodes['a10'] = [
    'x' => $pointA_x,
    'y' => $pointA_y,
    'label' => 'Shoulder',
    'code' => '$a10 = pointA_x, pointA_y'
];

// Node 11: Shoulder Line (top, end of shoulder near neck)
$nodes['a11'] = [
    'x' => $pointA_x - ($shoulder * $scale),
    'y' => $originY,
    'label' => 'Shoulder Line',
    'code' => '$a11 = a10.x - shoulder(' . number_format($shoulder, 2) . '"), a0.y'
];

// Node 111: Midpoint between a11 and a1 (for neck curve)
// Dynamically centered vertically between a1.y and a11.y
$nodes['a111'] = [
    'x' => $shoulderLine_x,
    'y' => ($nodes['a1']['y'] + $nodes['a11']['y']) / 2,
    'label' => 'a111',
    'code' => '$a111 = a11.x, (a1.y + a11.y) / 2'
];

// Shoulder Mid: Reference point (gray) - midpoint of shoulder line (a11 to a10)
$nodes['sMid'] = [
    'x' => $shoulderMid_x,
    'y' => $shoulderMid_y,
    'label' => 'Shoulder Mid',
    'color' => 'gray',
    'code' => '$sMid = midpoint(a11, a10)'
];

// Node b1: x = sMid.x (aligned with shoulder midpoint), y = apex level
$nodes['b1'] = [
    'x' => $shoulderMid_x,
    'y' => $originY + ($apex * $scale),
    'label' => 'b1',
    'code' => '$b1 = sMid.x, apex'
];

// NOTE: b2 and b4 nodes moved to after waist curve control point calculation (lines 387-423)

// Node b3: x = sMid.x (aligned with shoulder midpoint), y = apex + 1"
$nodes['b3'] = [
    'x' => $shoulderMid_x,
    'y' => $originY + (($apex + 1) * $scale),
    'label' => 'b3',
    'code' => '$b3 = sMid.x, apex + 1"'
];

// sideTuck_Left nodes (near apex level - left side)
// c1 and c3 are vertically centered on b1(y) with 1" gap maintained

// c1: 0.5" above b1(y) (apex level)
$nodes['c1'] = [
    'x' => $originX,
    'y' => $nodes['b1']['y'] - (0.5 * $scale),
    'label' => 'c1',
    'code' => '$c1 = a0.x, b1.y - 0.5"'
];

// c2: (b1.x - a0.x) - 1" from a0, aligned with b1 at apex level
$nodes['c2'] = [
    'x' => n('b1','x') - (1 * $scale),
    'y' => $nodes['b1']['y'],
    'label' => 'c2',
    'code' => '$c2 = b1.x - 1", b1.y'
];

// c3: 0.5" below b1(y) (apex level)
$nodes['c3'] = [
    'x' => $originX,
    'y' => $nodes['b1']['y'] + (0.5 * $scale),
    'label' => 'c3',
    'code' => '$c3 = a0.x, b1.y + 0.5"'
];

// sideTuck_Right nodes (near a6 - right side bust point)
// d1: 0.5" above a6
$nodes['d1'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $originY + ($apex * $scale) - (0.5 * $scale),
    'label' => 'd1',
    'code' => '$d1 = a6.x, a6.y - 0.5"'
];

// d2: 1" right of b1, at apex level
$nodes['d2'] = [
    'x' => n('b1','x') + (1 * $scale),
    'y' => $originY + ($apex * $scale),
    'label' => 'd2',
    'code' => '$d2 = b1.x + 1", apex.y'
];

// d3: 0.5" below a6
$nodes['d3'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $originY + ($apex * $scale) + (0.5 * $scale),
    'label' => 'd3',
    'code' => '$d3 = a6.x, a6.y + 0.5"'
];

// =============================================================================
// WAIST CURVE CONTROL POINT (a3 → a4)
// =============================================================================
// Quadratic bezier curve from a4 to a3, bulging downward towards a3
// Control point positioned at 35% from a3 (closer to a3 for bulge towards a3)
// Bulge depth is proportional to horizontal distance: 30% of a3-a4 distance (increased for better curvature)
$a3_a4_distance = abs(n('a4', 'x') - n('a3', 'x'));
$waistCurveBulgeRatio = 0.30;  // Constant ratio: bulge = 30% of horizontal distance (increased from 25% for better curvature)
$waistCurveBulgeDepth = $a3_a4_distance * $waistCurveBulgeRatio;
$waistCurveControlPointPosition = 0.35;  // 35% from a3, 65% from a4 (closer to a3)

$q_ctrl_left_x = n('a3', 'x') + (n('a4', 'x') - n('a3', 'x')) * $waistCurveControlPointPosition;
$q_ctrl_left_y = n('a3', 'y') + (n('a4', 'y') - n('a3', 'y')) * $waistCurveControlPointPosition + $waistCurveBulgeDepth;

// =============================================================================
// BOTTOM TUCK NODES (b2, b4) - Must be calculated AFTER waist curve control point
// =============================================================================

// Node b2: left side of bottom tuck
// y position calculated to be on the quadratic bezier curve between a4 and a3
$b2_x = $shoulderMid_x - (($bottomTuckWidth / 2) * $scale);
// Quadratic bezier: P(t) = (1-t)²P0 + 2(1-t)t·C + t²P1
// P0 = a4, P1 = a3, C = waist curve control point (calculated above)
$a3_x_val = n('a3', 'x');
$a3_y_val = n('a3', 'y');
$a4_x_val = n('a4', 'x');
$a4_y_val = n('a4', 'y');
$ctrl_x = $q_ctrl_left_x;
$ctrl_y = $q_ctrl_left_y;
// Find t where x = b2_x
$t_b2 = ($a4_x_val - $b2_x) / ($a4_x_val - $a3_x_val);
$b2_y = pow(1-$t_b2, 2) * $a4_y_val + 2*(1-$t_b2)*$t_b2 * $ctrl_y + pow($t_b2, 2) * $a3_y_val;
$nodes['b2'] = [
    'x' => $b2_x,
    'y' => $b2_y,
    'label' => 'b2',
    'code' => '$b2 = sMid.x - (bottomTuckWidth/2), on waist curve'
];

// Node b4: right side of bottom tuck
// y position calculated to be on the waist line (linear interpolation between a4 and a5)
$b4_x = $shoulderMid_x + (($bottomTuckWidth / 2) * $scale);
$a5_x_val = n('a5', 'x');
$a5_y_val = n('a5', 'y');
$b4_y = $a4_y_val + ($b4_x - $a4_x_val) * ($a5_y_val - $a4_y_val) / ($a5_x_val - $a4_x_val);
$nodes['b4'] = [
    'x' => $b4_x,
    'y' => $b4_y,
    'label' => 'b4',
    'code' => '$b4 = sMid.x + (bottomTuckWidth/2), on waist line'
];

// armholeTuck nodes (at a9 - armhole corner, pointing to b1)
// e1: 0.25" above a9 (along the armhole curve direction - toward a10)
$e1_offset = 0.25 * $scale;
$nodes['e1'] = [
    'x' => $pointB1_x - ($e1_offset * cos(deg2rad(45))),
    'y' => $pointB1_y - ($e1_offset * sin(deg2rad(45))),
    'label' => 'e1',
    'code' => '$e1 = a9 - 0.25" toward a10'
];

// e2: 1" from b1 toward a9 (along the b1-a9 line)
// Calculate angle from b1 to a9
$e2_dx = $pointB1_x - n('b1','x');  // a9.x - b1.x
$e2_dy = $pointB1_y - n('b1','y');  // a9.y - b1.y
$e2_angle = atan2($e2_dy, $e2_dx);  // angle from b1 to a9
$e2_dist = 1.0 * $scale;  // 1" along the line
$nodes['e2'] = [
    'x' => n('b1','x') + ($e2_dist * cos($e2_angle)),
    'y' => n('b1','y') + ($e2_dist * sin($e2_angle)),
    'label' => 'e2',
    'code' => '$e2 = 1" from b1 toward a9'
];

// e3: 0.25" below a9 (along the armhole curve direction - toward a8)
$nodes['e3'] = [
    'x' => $pointB1_x + ($e1_offset * cos(deg2rad(45))),
    'y' => $pointB1_y + ($e1_offset * sin(deg2rad(45))),
    'label' => 'e3',
    'code' => '$e3 = a9 + 0.25" toward a8'
];

// =============================================================================
// CUTTING LINE NODES (r = red line seam allowance)
// Uniform 0.5" offset from pattern, following the a-nodes
// =============================================================================
$seamOffset = 0.5;  // 0.5" uniform offset

// r1: aligned with a0.x, 0.5" below a3 (bottom-left corner)
$nodes['r1'] = [
    'x' => n('a0','x'),
    'y' => n('a3','y') + ($seamOffset * $scale),
    'label' => 'r1',
    'color' => 'red',
    'code' => '$r1 = a0.x, a3.y + 0.5"'
];

// r2: 0.5" below a4 (waist curve peak)
$nodes['r2'] = [
    'x' => n('a4','x'),
    'y' => n('a4','y') + ($seamOffset * $scale),
    'label' => 'r2',
    'color' => 'red',
    'code' => '$r2 = a4 + 0.5" down'
];

// r3: 1.5" right of a6.x, 0.5" down from a5 (waist right)
$nodes['r3'] = [
    'x' => n('a6','x') + (1.5 * $scale),
    'y' => n('a5','y') + ($seamOffset * $scale),
    'label' => 'r3',
    'color' => 'red',
    'code' => '$r3 = a6.x + 1.5", a5.y + 0.5"'
];

// r4: 1.5" right of a6 (bust point)
$nodes['r4'] = [
    'x' => n('a6','x') + (1.5 * $scale),
    'y' => n('a6','y'),
    'label' => 'r4',
    'color' => 'red',
    'code' => '$r4 = a6 + 1.5" right'
];

// r5: 1.5" right of a6.x, 0.5" up from a7 (intersection)
$nodes['r5'] = [
    'x' => n('a6','x') + (1.5 * $scale),
    'y' => n('a7','y') - ($seamOffset * $scale),
    'label' => 'r5',
    'color' => 'red',
    'code' => '$r5 = a6.x + 1.5", a7.y - 0.5"'
];

// r6: 0.5" right and 0.5" up from a8 (armhole end)
$nodes['r6'] = [
    'x' => n('a8','x') + ($seamOffset * $scale),
    'y' => n('a8','y') - ($seamOffset * $scale),
    'label' => 'r6',
    'color' => 'red',
    'code' => '$r6 = a8 + 0.5" right, 0.5" up'
];

// r7: 0.5" up from a10 (same offset as r8 from a11)
$nodes['r7'] = [
    'x' => n('a10','x') + (0.5 * $scale),
    'y' => n('a10','y') - ($seamOffset * $scale),
    'label' => 'r7',
    'color' => 'red',
    'code' => '$r7 = a10.x + 0.5", a10.y - 0.5"'
];

// r71: between r7 and r6 at a111(y) level on outer cutting line (armhole side)
$nodes['r71'] = [
    'x' => n('r7','x'),
    'y' => n('a111','y'),
    'label' => 'r71',
    'color' => 'red',
    'code' => '$r71 = r7.x, a111.y'
];

// r8: 0.5" left and 0.5" up from a11 (shoulder line end)
$nodes['r8'] = [
    'x' => n('a11','x') - ($seamOffset * $scale),
    'y' => n('a11','y') - ($seamOffset * $scale),
    'label' => 'r8',
    'color' => 'red',
    'code' => '$r8 = a11.x - 0.5", a11.y - 0.5"'
];

// r9: aligned with a0.x, 0.5" above a1.y (front neck level)
$nodes['r9'] = [
    'x' => n('a0','x'),
    'y' => n('a1','y') - ($seamOffset * $scale),
    'label' => 'r9',
    'color' => 'red',
    'code' => '$r9 = a0.x, a1.y - 0.5"'
];

// r91: midpoint on outer red cutting line between r9 and r8 (at a111 level)
$nodes['r91'] = [
    'x' => n('a111','x') - ($seamOffset * $scale),
    'y' => n('a111','y'),
    'label' => 'r91',
    'color' => 'red',
    'code' => '$r91 = a111.x - 0.5", a111.y'
];

// r10: REMOVED - no longer needed after connecting a1 directly to a3
// $nodes['r10'] = [
//     'x' => n('a0','x'),
//     'y' => n('a2','y'),
//     'label' => 'r10',
//     'color' => 'red',
//     'code' => '$r10 = a0.x, a2.y'
// ];

// Helper function to get node coordinates (front)
function n($name, $coord = null) {
    global $nodes;
    if (!isset($nodes[$name])) return null;
    if ($coord === 'x') return $nodes[$name]['x'];
    if ($coord === 'y') return $nodes[$name]['y'];
    return $nodes[$name];
}

// Helper function to create triangle snip icon with reference number
// $refNumber: Reference number (e.g., 1, 2, 3...) - appears in print mode
// $label: Label text (e.g., 'a8', 'Front-Armhole') - appears in dev mode only
// $nodeGetter: function to get node (e.g., 'n', 'bn', 'pn', 'sn')
// $nodeName: name of the node - TIP of triangle will be AT this node
// $angle: direction base points away from tip (0/360=right, 90=down, 180=left, 270=up)
// $size: triangle size in inches (default 0.225")
// $offsetX: horizontal offset from node in inches (default 0)
// $offsetY: vertical offset from node in inches (default 0)
function snipIcon($refNumber, $label, $nodeGetter, $nodeName, $angle, $size = 0.225, $offsetX = 0, $offsetY = 0) {
    global $scale, $isPrintMode, $isDevMode;

    // Get node coordinates - this is where the TIP will be (plus offset)
    $tipX = call_user_func($nodeGetter, $nodeName, 'x') + ($offsetX * $scale);
    $tipY = call_user_func($nodeGetter, $nodeName, 'y') + ($offsetY * $scale);

    if ($tipX === null || $tipY === null) return '';

    // Convert to scaled units
    $sizeScaled = $size * $scale;

    // Calculate triangle vertices based on angle
    // The tip is AT the node, base extends away based on angle
    switch($angle) {
        case 0:
        case 360:
            // Tip points RIGHT (at node), base extends LEFT
            $x1 = $tipX;                    // Tip at node
            $y1 = $tipY;
            $x2 = $tipX - $sizeScaled;      // Base left corner (top)
            $y2 = $tipY - ($sizeScaled / 2);
            $x3 = $tipX - $sizeScaled;      // Base left corner (bottom)
            $y3 = $tipY + ($sizeScaled / 2);
            // Label position: after the triangle (to the left of base)
            $labelX = $tipX - $sizeScaled - (0.15 * $scale);
            $labelY = $tipY;
            break;

        case 90:
            // Tip points DOWN (at node), base extends UP
            $x1 = $tipX;                    // Tip at node
            $y1 = $tipY;
            $x2 = $tipX - ($sizeScaled / 2); // Base top corner (left)
            $y2 = $tipY - $sizeScaled;
            $x3 = $tipX + ($sizeScaled / 2); // Base top corner (right)
            $y3 = $tipY - $sizeScaled;
            // Label position: above triangle (above the base)
            $labelX = $tipX;
            $labelY = $tipY - $sizeScaled - (0.15 * $scale);
            break;

        case 180:
            // Tip points LEFT (at node), base extends RIGHT
            $x1 = $tipX;                    // Tip at node
            $y1 = $tipY;
            $x2 = $tipX + $sizeScaled;      // Base right corner (top)
            $y2 = $tipY - ($sizeScaled / 2);
            $x3 = $tipX + $sizeScaled;      // Base right corner (bottom)
            $y3 = $tipY + ($sizeScaled / 2);
            // Label position: after the triangle (to the right of base)
            $labelX = $tipX + $sizeScaled + (0.15 * $scale);
            $labelY = $tipY;
            break;

        case 270:
            // Tip points UP (at node), base extends DOWN
            $x1 = $tipX;                    // Tip at node
            $y1 = $tipY;
            $x2 = $tipX - ($sizeScaled / 2); // Base bottom corner (left)
            $y2 = $tipY + $sizeScaled;
            $x3 = $tipX + ($sizeScaled / 2); // Base bottom corner (right)
            $y3 = $tipY + $sizeScaled;
            // Label position: below triangle (below the base)
            $labelX = $tipX;
            $labelY = $tipY + $sizeScaled + (0.15 * $scale);
            break;

        default:
            return ''; // Invalid angle
    }

    // Build SVG group with triangle and label
    $svg = '<g class="snip-marker">';

    // Triangle (two sides only, no baseline) - always visible in both modes
    // Line from tip to first base corner
    $svg .= sprintf(
        '<line class="snip-triangle" x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333333" stroke-width="0.5"/>',
        $x1, $y1, $x2, $y2
    );
    // Line from tip to second base corner
    $svg .= sprintf(
        '<line class="snip-triangle" x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333333" stroke-width="0.5"/>',
        $x1, $y1, $x3, $y3
    );

    // Label text (visible in dev mode only)
    if ($isDevMode) {
        $svg .= sprintf(
            '<text class="snip-label" x="%.2f" y="%.2f" text-anchor="middle" dominant-baseline="middle" font-size="10px" font-weight="bold" fill="black">%s</text>',
            $labelX, $labelY, htmlspecialchars($label)
        );
    }

    $svg .= '</g>';

    return $svg;
}

// =============================================================================
// SCISSORS ICON HELPER FUNCTION
// =============================================================================
// Creates a scissors icon using SVG path (renders correctly in PDF unlike Unicode ✂)
// $x: X coordinate for the center of the scissors
// $y: Y coordinate for the center of the scissors
// $rotation: Rotation angle in degrees (0=pointing right, 90=pointing down)
// $size: Size of the scissors in inches (default 0.5")
// $color: Fill color (default #333333)
function scissorsIcon($x, $y, $rotation = 0, $size = 0.5, $color = '#333333') {
    global $scale;

    $sizeScaled = $size * $scale;

    // Scissors SVG path (normalized to 24x24 viewBox, will be scaled)
    // This path is from Material Design Icons - scissors
    $scissorsPath = 'M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3h-3z';

    // Scale factor: path is 24x24, scale to desired size
    $pathScale = $sizeScaled / 24;

    // Build SVG group with transform
    $svg = sprintf(
        '<g transform="translate(%.2f, %.2f) rotate(%d) scale(%.4f) translate(-12, -12)">',
        $x, $y, $rotation, $pathScale
    );
    $svg .= sprintf(
        '<path d="%s" fill="%s"/>',
        $scissorsPath, $color
    );
    $svg .= '</g>';

    return $svg;
}

// =============================================================================
// GRAINLINE HELPER FUNCTION
// =============================================================================
// Creates a grainline (straight grain indicator) with arrows at both ends
// $x: X coordinate for the center of the grainline
// $y: Y coordinate for the center of the grainline
// $length: Length of the grainline in inches
// $orientation: 'vertical' or 'horizontal'
function grainLine($x, $y, $length, $orientation = 'vertical') {
    global $scale;

    $lengthScaled = $length * $scale;
    $arrowSize = 0.15 * $scale; // Arrow size

    if ($orientation === 'vertical') {
        // Vertical grainline
        $x1 = $x;
        $y1 = $y - ($lengthScaled / 2);
        $x2 = $x;
        $y2 = $y + ($lengthScaled / 2);

        $svg = '<g class="grainline">';
        // Main line
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x2, $y2);

        // Top arrow
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 - $arrowSize, $y1 + $arrowSize);
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 + $arrowSize);

        // Bottom arrow
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 - $arrowSize);
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 + $arrowSize, $y2 - $arrowSize);

        // Label "GRAINLINE" rotated vertically
        $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="%.2f" font-family="Arial, sans-serif" fill="#000" text-anchor="middle" transform="rotate(-90 %.2f %.2f)">GRAINLINE</text>',
            $x + (0.25 * $scale), $y, 0.15 * $scale, $x + (0.25 * $scale), $y);

        $svg .= '</g>';
    } else {
        // Horizontal grainline
        $x1 = $x - ($lengthScaled / 2);
        $y1 = $y;
        $x2 = $x + ($lengthScaled / 2);
        $y2 = $y;

        $svg = '<g class="grainline">';
        // Main line
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x2, $y2);

        // Left arrow
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 - $arrowSize);
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 + $arrowSize);

        // Right arrow
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 - $arrowSize);
        $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 + $arrowSize);

        // Label "GRAINLINE"
        $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="%.2f" font-family="Arial, sans-serif" fill="#000" text-anchor="middle">GRAINLINE</text>',
            $x, $y - (0.25 * $scale), 0.15 * $scale);

        $svg .= '</g>';
    }

    return $svg;
}

// =============================================================================
// FRONT ARMHOLE PATH - Complex curve from a10 (shoulder) to a8 (armhole end)
// =============================================================================
// Front armhole is already calculated by calculateArmhole() function above (line 158)
// It creates a complex 3-part curve: M-Q-C-Q
// Path: a10 (pointA) → a91 (pointAB2) → a9 (pointB1) → a8 (pointC)
// The $armholeSvgPath variable is already set by calculateArmhole()
// It contains: M pointA Q ctrl1 pointAB2 C ctrl2a ctrl2b pointB1 Q ctrl3 pointC
// Do NOT override it here - it's already correct!

// =============================================================================
// BLOUSE BACK NODES (Independent copy - prefixed with 'z' for back)
// =============================================================================
$backNodes = [];

// Back Node 0: Origin (top-left corner) - GRAY reference point
$backNodes['z0'] = [
    'x' => $originX,
    'y' => $originY,
    'label' => 'Origin',
    'color' => 'gray',
    'code' => '$z0 = originX, originY'
];

// Back Node 1: Back Neck Depth
// z9(y) to z1(y) = back neck length (bnDepth)
$backNodes['z1'] = [
    'x' => $originX,
    'y' => $z1_y,  // Uses bnDepth for back neck depth
    'label' => 'Back Neck',
    'code' => '$z1 = originY + bnDepth (back neck length from z9 to z1)'
];

// Back Node 2: Back Length point (bottom left corner)
$backNodes['z2'] = [
    'x' => $originX,
    'y' => $originY + ($blength * $scale),
    'label' => 'Back Length',
    'code' => '$z2 = z0.x, z0.y + blength'
];

// Back Node 3: Waist point (same x as z4)
$backNodes['z3'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $backNodes['z2']['y'],
    'label' => 'Waist',
    'code' => '$z3 = z4.x, z2.y'
];

// Back Node 4: Bust point at apex level
$backNodes['z4'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $originY + ($apex * $scale),
    'label' => 'z4',
    'code' => '$z4 = qBust, apex'
];

// Back Node 5: Intersection of armhole level and bust line
$backNodes['z5'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $pointC_y,
    'label' => 'z5',
    'code' => '$z5 = qBust, z6.y'
];

// Back Node 6: Armhole End (Point C)
$backNodes['z6'] = [
    'x' => $pointC_x,
    'y' => $pointC_y,
    'label' => 'Armhole End',
    'code' => '$z6 = pointC_x, pointC_y'
];

// Back Node 8: Shoulder (top of armhole - Point A)
$backNodes['z8'] = [
    'x' => $pointA_x,
    'y' => $pointA_y,
    'label' => 'Shoulder',
    'code' => '$z8 = pointA_x, pointA_y'
];

// Back Node 71: Armhole Curve Midpoint (vertically aligned with z8)
$backNodes['z71'] = [
    'x' => $backNodes['z8']['x'],  // Same x as z8 (vertical alignment)
    'y' => $pointAB_y,
    'label' => 'Armhole Mid',
    'code' => '$z71 = z8.x, pointAB_y'
];

// Back Node 9: Shoulder Line (top, end of shoulder near neck)
$backNodes['z9'] = [
    'x' => $pointA_x - ($shoulder * $scale),
    'y' => $originY,
    'label' => 'Shoulder Line',
    'code' => '$z9 = z8.x - shoulder'
];

// Back Node 91: Midpoint between z9 and z1 (for neck curve)
// Dynamically centered vertically between z1.y and z9.y
$backNodes['z91'] = [
    'x' => $shoulderLine_x,
    'y' => ($backNodes['z1']['y'] + $backNodes['z9']['y']) / 2,
    'label' => 'z91',
    'code' => '$z91 = z9.x, (z1.y + z9.y) / 2'
];

// Back Shoulder Mid: Reference point (gray)
$backNodes['zMid'] = [
    'x' => $shoulderMid_x,
    'y' => $shoulderMid_y,
    'label' => 'Shoulder Mid',
    'color' => 'gray',
    'code' => '$zMid = midpoint(z9, z8)'
];

// Back tuck nodes
$backNodes['zb1'] = [
    'x' => $shoulderMid_x,
    'y' => $backNodes['z2']['y'],
    'label' => 'zb1',
    'code' => '$zb1 = zb3.x, z2.y'
];

$backNodes['zb2'] = [
    'x' => $b2_x,
    'y' => $backNodes['zb1']['y'],
    'label' => 'zb2',
    'code' => '$zb2 = zMid.x - (bottomTuckWidth/2), zb1.y'
];

$backNodes['zb3'] = [
    'x' => $shoulderMid_x,
    'y' => $backNodes['zb1']['y'] - (4.5 * $scale),
    'label' => 'zb3',
    'code' => '$zb3 = zMid.x, zb1.y - 4.5"'
];

$backNodes['zb4'] = [
    'x' => $b4_x,
    'y' => $backNodes['zb1']['y'],
    'label' => 'zb4',
    'code' => '$zb4 = zMid.x + (bottomTuckWidth/2), zb1.y'
];

// Back cutting line nodes
$backSeamOffset = 0.5;

$backNodes['zr1'] = [
    'x' => $backNodes['z0']['x'],
    'y' => $backNodes['z2']['y'] + ($backSeamOffset * $scale),
    'label' => 'zr1',
    'color' => 'red',
    'code' => '$zr1 = z0.x, z2.y + 0.5"'
];

$backNodes['zr3'] = [
    'x' => $backNodes['z4']['x'] + (1.5 * $scale),
    'y' => $backNodes['z3']['y'] + ($backSeamOffset * $scale),
    'label' => 'zr3',
    'color' => 'red',
    'code' => '$zr3 = z4.x + 1.5", z3.y + 0.5"'
];

$backNodes['zr4'] = [
    'x' => $backNodes['z4']['x'] + (1.5 * $scale),
    'y' => $backNodes['z4']['y'],
    'label' => 'zr4',
    'color' => 'red',
    'code' => '$zr4 = z4 + 1.5" right'
];

$backNodes['zr5'] = [
    'x' => $backNodes['z4']['x'] + (1.5 * $scale),
    'y' => $backNodes['z5']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr5',
    'color' => 'red',
    'code' => '$zr5 = z4.x + 1.5", z5.y - 0.5"'
];

$backNodes['zr6'] = [
    'x' => $backNodes['z6']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z6']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr6',
    'color' => 'red',
    'code' => '$zr6 = z6 + 0.5" right, 0.5" up'
];

$backNodes['zr71'] = [
    'x' => $backNodes['z71']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z71']['y'],
    'label' => 'zr71',
    'color' => 'red',
    'code' => '$zr71 = z71.x + 0.5", z71.y'
];

$backNodes['zr7'] = [
    'x' => $backNodes['z8']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z8']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr7',
    'color' => 'red',
    'code' => '$zr7 = z8.x + 0.5", z8.y - 0.5"'
];

$backNodes['zr91'] = [
    'x' => $backNodes['z91']['x'] - ($backSeamOffset * $scale),
    'y' => $backNodes['z91']['y'],
    'label' => 'zr91',
    'color' => 'red',
    'code' => '$zr91 = z91.x - 0.5", z91.y'
];

$backNodes['zr8'] = [
    'x' => $backNodes['zr91']['x'],  // Same x as zr91 (inline)
    'y' => $backNodes['z9']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr8',
    'color' => 'red',
    'code' => '$zr8 = zr91.x, z9.y - 0.5"'
];

$backNodes['zr9'] = [
    'x' => $backNodes['z0']['x'],
    'y' => $backNodes['z1']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr9',
    'color' => 'red',
    'code' => '$zr9 = z0.x, z1.y - 0.5"'
];

// Helper function to get back node coordinates
function bn($name, $coord = null) {
    global $backNodes;
    if (!isset($backNodes[$name])) return null;
    if ($coord === 'x') return $backNodes[$name]['x'];
    if ($coord === 'y') return $backNodes[$name]['y'];
    return $backNodes[$name];
}

// STEP 6: SVG Canvas dimensions
// =============================================================================
$viewScale = 2.0;  // View magnification (2.0x bigger display)
// Width needs to accommodate qChest, qWaist, or qBust (whichever is larger) + cutting line margins
// Right side has 1.5" seam allowance, so add extra space
$svgWidthInches = max($qChest, $qWaist, $qBust) + 4;  // +4" for right margin (1.5") + padding
$svgWidth  = $svgWidthInches * $scale * $viewScale;
// Height needs to accommodate front/back length + waist curve + margins + cutting line
// Use max of flength and blength to ensure both patterns fit
// Bottom has 1" seam allowance, top has 1" seam allowance
$svgHeightInches = max($armHoleHeight + 4, $flength + 5, $blength + 5);  // +5" for top/bottom margins + padding
$svgHeight = $svgHeightInches * $scale * $viewScale;

// =============================================================================
// BACK ARMHOLE PATH - Straight line from z8 to z71, then curve to z6
// =============================================================================
// z8 → z71: Straight line
// z71 → z6: Quadratic curve
// Total path length must equal: armhole / 2

// Target length: armhole / 2 (in pixels)
$targetArmholeLength = ($armhole / 2) * $scale;

// Calculate straight line distance from z8 to z71
$z8_z71_length = sqrt(
    pow($backNodes['z71']['x'] - $backNodes['z8']['x'], 2) +
    pow($backNodes['z71']['y'] - $backNodes['z8']['y'], 2)
);

// Required curve length from z71 to z6
$targetCurveLength = $targetArmholeLength - $z8_z71_length;

// Function to calculate quadratic bezier curve length (approximation using segments)
function calculateQuadraticBezierLength($x0, $y0, $cx, $cy, $x1, $y1, $segments = 100) {
    $length = 0;
    $prevX = $x0;
    $prevY = $y0;

    for ($i = 1; $i <= $segments; $i++) {
        $t = $i / $segments;
        $t1 = 1 - $t;

        // Quadratic bezier formula: B(t) = (1-t)²P0 + 2(1-t)tP1 + t²P2
        $x = $t1 * $t1 * $x0 + 2 * $t1 * $t * $cx + $t * $t * $x1;
        $y = $t1 * $t1 * $y0 + 2 * $t1 * $t * $cy + $t * $t * $y1;

        $length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    return $length;
}

// =============================================================================
// Iterative calculation to find optimal control point for z71→z6 curve
// =============================================================================
// This algorithm adjusts BOTH the control point position (along the path)
// AND the downward bulge to achieve the target length with smooth transition
//
// Strategy: Position control point closer to z71 for smoother curve entry
// =============================================================================

// CONFIGURATION: Control point distance ratio from z71 (0-1 range)
// Lower values (0.1-0.2) = smoother transition, gentler curve start
// Higher values (0.3-0.5) = sharper transition, more pronounced curve start
$ctrlDistanceRatio = 0.1; // 10% from z71 towards z6 for smooth transition

// Calculate base control point position along the z71→z6 line
$ctrlXBase = $backNodes['z71']['x'] + $ctrlDistanceRatio * ($backNodes['z6']['x'] - $backNodes['z71']['x']);
$ctrlYBase = $backNodes['z71']['y'] + $ctrlDistanceRatio * ($backNodes['z6']['y'] - $backNodes['z71']['y']);

$z71_z6_ctrl_x = $ctrlXBase; // X remains fixed
$z71_z6_ctrl_y = $ctrlYBase; // Y will be adjusted iteratively for bulge

// Binary search parameters for finding optimal downward bulge
$minOffset = 0;           // Minimum bulge (no curve)
$maxOffset = 4 * $scale;  // Maximum 4 inches downward bulge
$tolerance = 0.1;         // 0.1 pixel accuracy
$maxIterations = 50;      // Maximum iterations to prevent infinite loops

// Binary search iteration to find exact downward offset for target length
for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
    $currentOffset = ($minOffset + $maxOffset) / 2;
    $testCtrlY = $ctrlYBase + $currentOffset; // Test control point Y with current offset

    // Calculate curve length with test control point
    $curveLength = calculateQuadraticBezierLength(
        $backNodes['z71']['x'], $backNodes['z71']['y'],  // Curve start
        $z71_z6_ctrl_x, $testCtrlY,                      // Control point (test)
        $backNodes['z6']['x'], $backNodes['z6']['y']     // Curve end
    );

    // Calculate total path length: straight line (z8→z71) + curve (z71→z6)
    $totalLength = $z8_z71_length + $curveLength;
    $difference = $totalLength - $targetArmholeLength;

    // Check if we've achieved target length within tolerance
    if (abs($difference) < $tolerance) {
        $z71_z6_ctrl_y = $testCtrlY; // Solution found!
        break;
    }

    // Adjust search range based on whether we're too short or too long
    if ($totalLength < $targetArmholeLength) {
        // Curve too short: increase bulge (more downward offset)
        $minOffset = $currentOffset;
    } else {
        // Curve too long: reduce bulge (less downward offset)
        $maxOffset = $currentOffset;
    }
}

// Fallback: Use last calculated value if we didn't converge within max iterations
if (!isset($z71_z6_ctrl_y)) {
    $z71_z6_ctrl_y = $ctrlYBase + (($minOffset + $maxOffset) / 2);
}

$backArmholePath = sprintf(
    "M %.2f,%.2f L %.2f,%.2f Q %.2f,%.2f %.2f,%.2f",
    $backNodes['z8']['x'], $backNodes['z8']['y'],   // Start at z8 (shoulder)
    $backNodes['z71']['x'], $backNodes['z71']['y'], // Straight line to z71 (midpoint)
    $z71_z6_ctrl_x, $z71_z6_ctrl_y,                 // Control point for curve (calculated closer to z71)
    $backNodes['z6']['x'], $backNodes['z6']['y']    // Curve to z6 (armhole end)
);

// Calculate actual path length for verification
$actualCurveLength = calculateQuadraticBezierLength(
    $backNodes['z71']['x'], $backNodes['z71']['y'],
    $z71_z6_ctrl_x, $z71_z6_ctrl_y,
    $backNodes['z6']['x'], $backNodes['z6']['y']
);
$actualTotalLength = $z8_z71_length + $actualCurveLength;
$actualLengthInches = $actualTotalLength / $scale;
$targetLengthInches = $armhole / 2;

// =============================================================================
// BACK RED CUTTING LINE - Armhole path with seam allowance
// =============================================================================
// Calculate red control point with REDUCED bulge (less downward offset than black line)
$zr71_z6_ctrl_x = $ctrlXBase + ($backSeamOffset * $scale);
// Reduce bulge by using only 50% of the black line's offset, plus the seam allowance
$zr71_z6_ctrl_y = $ctrlYBase + (($z71_z6_ctrl_y - $ctrlYBase) * 0.5) + ($backSeamOffset * $scale);

$backRedArmholePath = sprintf(
    "M %.2f,%.2f L %.2f,%.2f Q %.2f,%.2f %.2f,%.2f",
    $backNodes['zr7']['x'], $backNodes['zr7']['y'],   // Start at zr7 (shoulder with seam)
    $backNodes['zr71']['x'], $backNodes['zr71']['y'], // Straight line to zr71
    $zr71_z6_ctrl_x, $zr71_z6_ctrl_y,                 // Control point (offset from black)
    $backNodes['zr6']['x'], $backNodes['zr6']['y']    // Curve to zr6 (armhole end with seam)
);

// Back pattern - RED cutting line (complete outer cutting path)
// This is the full cutting line including neck, shoulder, and armhole
$backRedCuttingPath = sprintf(
    "M %.2f,%.2f Q %.2f,%.2f %.2f,%.2f L %.2f,%.2f L %.2f,%.2f L %.2f,%.2f Q %.2f,%.2f %.2f,%.2f L %.2f,%.2f L %.2f,%.2f L %.2f,%.2f L %.2f,%.2f",
    bn('zr9','x'), bn('zr9','y'),  // Start at zr9 (neck corner)
    bn('zr91','x'), bn('zr9','y'), // Control point for neck curve
    bn('zr91','x'), bn('zr91','y'), // Curve to zr91
    bn('zr8','x'), bn('zr8','y'),  // Line to zr8 (corner at z9, inline with zr91)
    bn('zr7','x'), bn('zr7','y'),  // Line to zr7 (shoulder at z8)
    bn('zr71','x'), bn('zr71','y'), // Line to zr71
    $zr71_z6_ctrl_x, $zr71_z6_ctrl_y, // Control point for armhole curve
    bn('zr6','x'), bn('zr6','y'),  // Curve to zr6
    bn('zr5','x'), bn('zr5','y'),  // Line to zr5
    bn('zr4','x'), bn('zr4','y'),  // Line to zr4
    bn('zr3','x'), bn('zr3','y'),  // Line to zr3
    bn('zr1','x'), bn('zr1','y')   // Line to zr1
);

// =============================================================================
// DATA EXPORT - Prepare data structures for presentation layer
// =============================================================================
// Convert nodes to JSON for JavaScript access in presentation layer
$nodesJson = json_encode($nodes);
$backNodesJson = json_encode($backNodes);

/**
 * Calculate bounding box of all pattern elements in an array of nodes
 * Returns [minX, minY, maxX, maxY, width, height] in pixels
 * Handles both indexed arrays ([x, y]) and associative arrays (['x' => x, 'y' => y])
 */
function calculatePatternBoundingBox($nodes, $scale, $marginInches = 0.5, $extraRightInches = 0.5, $extraBottomInches = 0.5) {
    $minX = PHP_FLOAT_MAX;
    $minY = PHP_FLOAT_MAX;
    $maxX = PHP_FLOAT_MIN;
    $maxY = PHP_FLOAT_MIN;

    foreach ($nodes as $node) {
        // Handle both indexed arrays (Front/Back) and associative arrays (Patti/Sleeve)
        if (isset($node['x']) && isset($node['y'])) {
            // Associative array (already in pixels)
            $x = $node['x'];
            $y = $node['y'];
        } else {
            // Indexed array (in inches, need to scale)
            $x = $node[0] * $scale;
            $y = $node[1] * $scale;
        }

        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }

    // Add margin (default 0.5", can be customized per pattern)
    $margin = $marginInches * $scale;
    $extraRight = $extraRightInches * $scale;
    $extraBottom = $extraBottomInches * $scale;

    $minXWithMargin = max(0, $minX - $margin);
    $minYWithMargin = max(0, $minY - $margin);
    $maxXWithMargin = $maxX + $margin + $extraRight;    // Add extra space on right
    $maxYWithMargin = $maxY + $margin + $extraBottom;   // Add extra space on bottom

    return [
        'minX' => $minXWithMargin,
        'minY' => $minYWithMargin,
        'maxX' => $maxXWithMargin,
        'maxY' => $maxYWithMargin,
        'width' => $maxXWithMargin - $minXWithMargin,   // Margin already included
        'height' => $maxYWithMargin - $minYWithMargin   // Margin already included
    ];
}

// Calculate bounding boxes for Front and Back patterns with 0.25" margin (reduced from 0.5" to fit portrait A3)
$frontBounds = calculatePatternBoundingBox($nodes, $scale, 0.25);
$backBounds = calculatePatternBoundingBox($backNodes, $scale, 0.25);

// =============================================================================
// END OF SECTION 2: BUSINESS LOGIC
// =============================================================================
// All calculations, nodes, and paths are now complete and stored in variables:
//
// DATA AVAILABLE FOR PRESENTATION:
// - Measurements: $bust, $chest, $waist, $armhole, $shoulder, etc.
// - Scale: $scale (pixels per inch)
// - Origin: $originX, $originY
// - Nodes: $nodes[] (front pattern), $backNodes[] (back pattern)
// - Paths: $armholeSvgPath, $backArmholePath, $backRedArmholePath, etc.
// - Helper functions: n(), bn() for accessing node coordinates
// - Mode flags: $isDevMode, $isPrintMode
//
// NO CALCULATIONS SHOULD HAPPEN BELOW THIS LINE
// =============================================================================

// =============================================================================
// PATTI CALCULATIONS (MOVED from lines 1458-1711)
// =============================================================================
// These calculations were moved here to Section 2 to separate business logic
// from presentation. Previously, these were inline in the presentation layer.
// =============================================================================

// =============================================================================
// BLOUSE PATTI CALCULATIONS
// =============================================================================
// Patti is a rectangular strip used for binding/finishing edges
// Standard width: 2"
// Length: back length - front neck depth

$pattiWidth = 2.0;  // inches
$pattiLength = $blength - $frontNeckDepth;  // back length - front neck depth
$pattiSeamAllowance = 0.5;  // inches

// Second patti dimensions
// Width: a3 to a5 = qBust (bust / 4)
// Height: back length - front length
$patti2Width = $qBust;  // a3 to a5 distance = bust / 4
$patti2Height = $blength - $flength;

// Patti SVG dimensions - use max of both pattis for width
$maxPattiWidth = max($pattiLength, $patti2Width);
$pattiSvgWidth = ($maxPattiWidth + 3) * $scale * $viewScale;  // Extra space for labels
$pattiSvgHeight = ($pattiWidth + $patti2Height + 11) * $scale * $viewScale;  // Height for 2 pattis (extra space: 2.5" top margin + 4.5" gap + 4" bottom)

// Patti origin (with margin - increased Y for snip icon visibility)
$pattiOriginX = 1.0 * $scale;
$pattiOriginY = 2.5 * $scale;  // Increased from 1.0" to 2.5" to accommodate top snip icons

// Patti nodes
$pattiNodes = [];

// p1: Top-left corner
$pattiNodes['p1'] = [
    'x' => $pattiOriginX,
    'y' => $pattiOriginY,
    'label' => 'p1',
    'code' => '$p1 = origin'
];

// p2: Top-right corner
$pattiNodes['p2'] = [
    'x' => $pattiOriginX + ($pattiLength * $scale),
    'y' => $pattiOriginY,
    'label' => 'p2',
    'code' => '$p2 = p1.x + pattiLength'
];

// p3: Bottom-right corner
$pattiNodes['p3'] = [
    'x' => $pattiOriginX + ($pattiLength * $scale),
    'y' => $pattiNodes['p2']['y'] + ($patti2Height * $scale),
    'label' => 'p3',
    'code' => '$p3 = p2.x, p2.y + pattiHeight (blength - flength)'
];

// p4: Bottom-left corner
$pattiNodes['p4'] = [
    'x' => $pattiOriginX,
    'y' => $pattiNodes['p2']['y'] + ($patti2Height * $scale),
    'label' => 'p4',
    'code' => '$p4 = p1.x, p2.y + pattiHeight (blength - flength)'
];

// Center fold line points
$pattiNodes['pf1'] = [
    'x' => $pattiOriginX,
    'y' => $pattiOriginY + (($pattiWidth / 2) * $scale),
    'label' => 'Fold',
    'color' => 'gray',
    'code' => '$pf1 = fold line start'
];

$pattiNodes['pf2'] = [
    'x' => $pattiOriginX + ($pattiLength * $scale),
    'y' => $pattiOriginY + (($pattiWidth / 2) * $scale),
    'label' => 'Fold',
    'color' => 'gray',
    'code' => '$pf2 = fold line end'
];

// Cutting line nodes (seam allowance)
$pattiNodes['pc1'] = [
    'x' => $pattiOriginX - ($pattiSeamAllowance * $scale),
    'y' => $pattiOriginY - ($pattiSeamAllowance * $scale),
    'label' => 'pc1',
    'color' => 'red',
    'code' => '$pc1 = p1 - seam allowance'
];

$pattiNodes['pc2'] = [
    'x' => $pattiOriginX + ($pattiLength * $scale) + ($pattiSeamAllowance * $scale),
    'y' => $pattiOriginY - ($pattiSeamAllowance * $scale),
    'label' => 'pc2',
    'color' => 'red',
    'code' => '$pc2 = p2 + seam allowance'
];

$pattiNodes['pc3'] = [
    'x' => $pattiOriginX + ($pattiLength * $scale) + ($pattiSeamAllowance * $scale),
    'y' => $pattiOriginY + ($pattiWidth * $scale) + ($pattiSeamAllowance * $scale),
    'label' => 'pc3',
    'color' => 'red',
    'code' => '$pc3 = p3 + seam allowance'
];

$pattiNodes['pc4'] = [
    'x' => $pattiOriginX - ($pattiSeamAllowance * $scale),
    'y' => $pattiOriginY + ($pattiWidth * $scale) + ($pattiSeamAllowance * $scale),
    'label' => 'pc4',
    'color' => 'red',
    'code' => '$pc4 = p4 + seam allowance'
];

// Helper function for patti nodes
function pn($name, $coord = null) {
    global $pattiNodes;
    if (!isset($pattiNodes[$name])) return null;
    if ($coord === 'x') return $pattiNodes[$name]['x'];
    if ($coord === 'y') return $pattiNodes[$name]['y'];
    return $pattiNodes[$name];
}

// ========== SECOND PATTI NODES (q prefix) - 5 nodes ==========
// q1, q2, q3 directly reference a3, a4, a5 values
// Width: a3 to a5 = qBust = $patti2Width
// Height: back length - front length = $patti2Height
$patti2OffsetY = ($pattiWidth + 2.0) * $scale;  // 2.0" gap between patti 1 and 2 (reduced from 4.5" to fit portrait A3)

// Get a3, a4, a5, a8 positions directly from nodes
$a3_x = n('a3', 'x');
$a3_y = n('a3', 'y');
$a4_x = n('a4', 'x');
$a4_y = n('a4', 'y');
$a5_x = n('a5', 'x');
$a5_y = n('a5', 'y');
$a8_x = n('a8', 'x');
$a8_y = n('a8', 'y');

// Calculate relative offsets from a3 (all values derived from actual a3, a4, a5, a8)
$a4_x_offset = $a4_x - $a3_x;  // x distance from a3 to a4
$a4_y_offset = $a4_y - $a3_y;  // y distance from a3 to a4
$a5_x_offset = $a5_x - $a3_x;  // x distance from a3 to a5
$a5_y_offset = $a5_y - $a3_y;  // y distance from a3 to a5
$a8_x_offset = $a8_x - $a3_x;  // x distance from a3 to a8
$a8_y_offset = $a8_y - $a3_y;  // y distance from a3 to a8

// q1 = a3 position (baseline origin for patti 2)
$pattiNodes['q1'] = [
    'x' => $pattiOriginX,
    'y' => $pattiOriginY + $patti2OffsetY,
    'label' => 'q1',
    'code' => '$q1 = a3 position'
];

// q2 = a4 position (using exact a3-to-a4 offsets)
$pattiNodes['q2'] = [
    'x' => $pattiOriginX + $a4_x_offset,
    'y' => $pattiOriginY + $patti2OffsetY + $a4_y_offset,
    'label' => 'q2',
    'code' => '$q2 = a4 position (offset from a3)'
];

// q3 = a5 position (using exact a3-to-a5 offsets)
$pattiNodes['q3'] = [
    'x' => $pattiOriginX + $a5_x_offset,
    'y' => $pattiOriginY + $patti2OffsetY + $a5_y_offset,
    'label' => 'q3',
    'code' => '$q3 = a5 position (offset from a3)'
];

// q4: Bottom-left corner
$pattiNodes['q4'] = [
    'x' => $pattiOriginX,
    'y' => $pattiOriginY + ($patti2Height * $scale) + $patti2OffsetY,
    'label' => 'q4',
    'code' => '$q4 = bottom-left'
];

// q5: Bottom-right corner
$pattiNodes['q5'] = [
    'x' => $pattiOriginX + ($patti2Width * $scale),
    'y' => $pattiOriginY + ($patti2Height * $scale) + $patti2OffsetY,
    'label' => 'q5',
    'code' => '$q5 = bottom-right'
];

// ========== CUTTING LINE NODES (qc prefix) - 0.5" seam allowance ==========
$patti2SeamAllowance = 0.5 * $scale;  // 0.5" seam

// qc1: q1 - 0.5" left and up
$pattiNodes['qc1'] = [
    'x' => pn('q1', 'x') - $patti2SeamAllowance,
    'y' => pn('q1', 'y') - $patti2SeamAllowance,
    'label' => 'qc1',
    'code' => '$qc1 = q1 - 0.5" (left, up)',
    'color' => 'red'
];

// qc2: q2 - 0.5" up (on curve)
$pattiNodes['qc2'] = [
    'x' => pn('q2', 'x'),
    'y' => pn('q2', 'y') - $patti2SeamAllowance,
    'label' => 'qc2',
    'code' => '$qc2 = q2 - 0.5" (up)',
    'color' => 'red'
];

// qc3: q3 + 0.5" right, - 0.5" up (relative to q3's depth)
$pattiNodes['qc3'] = [
    'x' => pn('q3', 'x') + $patti2SeamAllowance,
    'y' => pn('q3', 'y') - $patti2SeamAllowance,
    'label' => 'qc3',
    'code' => '$qc3 = q3 + 0.5" (right, up)',
    'color' => 'red'
];

// qc4: q4 - 0.5" left, + 0.5" down
$pattiNodes['qc4'] = [
    'x' => pn('q4', 'x') - $patti2SeamAllowance,
    'y' => pn('q4', 'y') + $patti2SeamAllowance,
    'label' => 'qc4',
    'code' => '$qc4 = q4 + 0.5" (left, down)',
    'color' => 'red'
];

// qc5: q5 + 0.5" right, + 0.5" down
$pattiNodes['qc5'] = [
    'x' => pn('q5', 'x') + $patti2SeamAllowance,
    'y' => pn('q5', 'y') + $patti2SeamAllowance,
    'label' => 'qc5',
    'code' => '$qc5 = q5 + 0.5" (right, down)',
    'color' => 'red'
];

// qc6: Corner point for cutting line (fixed 1" to the right of qc1, same y as qc1)
$pattiNodes['qc6'] = [
    'x' => pn('qc1', 'x') + (1 * $scale),
    'y' => pn('qc1', 'y'),
    'label' => 'qc6',
    'code' => '$qc6 = qc1 + 1" (right)',
    'color' => 'red'
];

// =============================================================================
// PATTI 2: Vertical Guide Line Calculation
// =============================================================================
// Calculate vertical dotted line position on patti2
// This line marks the distance a8(x) - a3(x) from the left edge
// Used as a reference guide for pattern alignment
$a8_a3_distance = n('a8', 'x') - n('a3', 'x');  // Distance from armhole end to front length
$patti2VerticalLineX = pn('q1', 'x') + $a8_a3_distance;  // Position from left edge

// =============================================================================
// DATA EXPORT: Patti nodes to JSON
// =============================================================================
$pattiNodesJson = json_encode($pattiNodes);

// Calculate Patti bounding box with smaller margin (0.25" instead of 0.5" to fit in portrait A3)
// Patti needs tighter bounds to fit: 11.00" width needs to fit in 10.69" portrait usable width
$pattiBounds = calculatePatternBoundingBox($pattiNodes, $scale, 0.25);

// DEBUG: Check Patti bounding box
/*
echo "<pre style='background: #ffffcc; padding: 10px; margin: 10px;'>";
echo "PATTI BOUNDING BOX DEBUG:\n";
echo "  Bounding box: " . number_format($pattiBounds['width'] / $scale, 2) . "\" × " . number_format($pattiBounds['height'] / $scale, 2) . "\"\n";
echo "  Min: (" . number_format($pattiBounds['minX'] / $scale, 2) . "\", " . number_format($pattiBounds['minY'] / $scale, 2) . "\")\n";
echo "  Max: (" . number_format($pattiBounds['maxX'] / $scale, 2) . "\", " . number_format($pattiBounds['maxY'] / $scale, 2) . "\")\n\n";

echo "Checking Patti1 nodes:\n";
$patti1Nodes = ['p1', 'p2', 'p3', 'p4', 'pc1', 'pc2', 'pc3', 'pc4'];
foreach ($patti1Nodes as $name) {
    if (isset($pattiNodes[$name])) {
        $node = $pattiNodes[$name];
        echo "  {$name}: (" . number_format($node['x'] / $scale, 2) . "\", " . number_format($node['y'] / $scale, 2) . "\")\n";
    }
}

echo "\nChecking Patti2 nodes:\n";
$patti2Nodes = ['q1', 'q2', 'q3', 'q4', 'q5', 'qc1', 'qc2', 'qc3', 'qc4', 'qc5'];
foreach ($patti2Nodes as $name) {
    if (isset($pattiNodes[$name])) {
        $node = $pattiNodes[$name];
        echo "  {$name}: (" . number_format($node['x'] / $scale, 2) . "\", " . number_format($node['y'] / $scale, 2) . "\")\n";
    }
}
echo "</pre>";
exit;
*/

// =============================================================================
// SLEEVE CALCULATIONS (MOVED from lines 2130-2935)
// =============================================================================
// These calculations were moved here to Section 2 to separate business logic
// from presentation. Previously, these were inline in the presentation layer.
// =============================================================================

// =============================================================================
// SLEEVE PATTERN CALCULATIONS - Built from scratch
// =============================================================================

// Key measurements from customer data (all in inches):
// $slength - sleeve length (e.g., 18")
// $saround - sleeve around/bicep circumference (e.g., 12")
// $sopen - sleeve opening/wrist circumference (e.g., 9")
// $armhole - armhole circumference (e.g., 15")

// =============================================================================
// TESTING OVERRIDE: Temporarily set saround to 12" for testing
// =============================================================================

// Setup coordinate system
$sleeveTopMargin = 1 * $scale;    // Top margin 1"
$sleeveLeftMargin = 1 * $scale;   // Left margin 1"

// =============================================================================
// HELPER FUNCTION: Cubic Bezier Evaluation
// =============================================================================
if (!function_exists('cubicBezier_sleeve')) {
    function cubicBezier_sleeve($t, $p0, $p1, $p2, $p3) {
        $mt = 1 - $t;
        return $mt * $mt * $mt * $p0 +
               3 * $mt * $mt * $t * $p1 +
               3 * $mt * $t * $t * $p2 +
               $t * $t * $t * $p3;
    }
}

// =============================================================================
// ITERATIVE CAP HEIGHT CALCULATION
// Adjust cap height so that s2→s1→s3 path equals armhole length
// =============================================================================

$useIterativeCapHeight = true;  // Set to false to use fixed 4.5" cap height

if ($useIterativeCapHeight) {
    // Target: s2→s1→s3 must equal armhole measurement (using smooth curves)
    $targetArmholeLength = $armhole * $scale;

    // Binary search parameters for cap height
    $minCapHeight = 2.0 * $scale;   // Minimum 2 inches
    $maxCapHeight = 8.0 * $scale;   // Maximum 8 inches
    $capHeightTolerance = 0.5;      // 0.5 pixel tolerance
    $maxIterations = 50;

    $capHeight = 4.5 * $scale;  // Initial guess
    $converged = false;
    $iterations = 0;

    // Cap point (fixed) - centered over bicep width
    $capX = $sleeveLeftMargin + (($saround * $scale) / 2);
    $capY = $sleeveTopMargin;

    // Wrist points - sleeve length measured from s1 (cap point)
    $wristCenter = $capX;
    $wristHalfWidth = ($sopen * $scale) / 2;
    $leftWristX = $wristCenter - $wristHalfWidth;
    $leftWristY = $capY + ($slength * $scale);  // Measure from s1 level
    $rightWristX = $wristCenter + $wristHalfWidth;
    $rightWristY = $capY + ($slength * $scale);  // Measure from s1 level

    for ($iter = 0; $iter < $maxIterations; $iter++) {
        $iterations = $iter + 1;

        // Calculate shoulder points based on current cap height
        // s2 to s3 distance = saround (bicep measurement)
        $leftShoulderX = $sleeveLeftMargin;
        $leftShoulderY = $sleeveTopMargin + $capHeight;
        $rightShoulderX = $sleeveLeftMargin + ($saround * $scale);
        $rightShoulderY = $sleeveTopMargin + $capHeight;

        // Calculate angles for curves to s1 (cap point)
        $angle_s2_s1 = atan2($capY - $leftShoulderY, $capX - $leftShoulderX);
        $perpendicular_s2_s1 = $angle_s2_s1 + (M_PI / 2);
        $angle_s1_s3 = atan2($rightShoulderY - $capY, $rightShoulderX - $capX);

        // S-CURVE from s2 to s1 using cubic bezier with 2 control points
        $sCurveDepth = 1.3 * $scale;  // Depth of S-curve (1.3")

        // Control point 1: At 35% along path, offset perpendicular (outward)
        $ctrl1_s2s1_t = 0.35;
        $ctrl1_s2s1_base_x = $leftShoulderX + ($capX - $leftShoulderX) * $ctrl1_s2s1_t;
        $ctrl1_s2s1_base_y = $leftShoulderY + ($capY - $leftShoulderY) * $ctrl1_s2s1_t;
        $ctrl1_s2s1_x = $ctrl1_s2s1_base_x + cos($perpendicular_s2_s1) * $sCurveDepth;
        $ctrl1_s2s1_y = $ctrl1_s2s1_base_y + sin($perpendicular_s2_s1) * $sCurveDepth;

        // Control point 2: At 65% along path, offset perpendicular (inward)
        $ctrl2_s2s1_t = 0.65;
        $ctrl2_s2s1_base_x = $leftShoulderX + ($capX - $leftShoulderX) * $ctrl2_s2s1_t;
        $ctrl2_s2s1_base_y = $leftShoulderY + ($capY - $leftShoulderY) * $ctrl2_s2s1_t;
        $ctrl2_s2s1_x = $ctrl2_s2s1_base_x - cos($perpendicular_s2_s1) * $sCurveDepth + (0.3 * $scale);
        $ctrl2_s2s1_y = $ctrl2_s2s1_base_y - sin($perpendicular_s2_s1) * $sCurveDepth - (0.2 * $scale);

        // S-CURVE from s1 to s3 using cubic bezier with 2 control points
        // More bulge near s1, less depth at s3
        $angle_s1_s3 = atan2($rightShoulderY - $capY, $rightShoulderX - $capX);
        $perpendicular_s1_s3 = $angle_s1_s3 + (M_PI / 2);

        // Control point 1: At 25% along path (near s1), large outward bulge
        $ctrl1_s1s3_t = 0.25;
        $ctrl1_s1s3_base_x = $capX + ($rightShoulderX - $capX) * $ctrl1_s1s3_t;
        $ctrl1_s1s3_base_y = $capY + ($rightShoulderY - $capY) * $ctrl1_s1s3_t;
        $ctrl1_s1s3_x = $ctrl1_s1s3_base_x - cos($perpendicular_s1_s3) * (1.5 * $scale);  // 1.5" outward bulge near s1
        $ctrl1_s1s3_y = $ctrl1_s1s3_base_y - sin($perpendicular_s1_s3) * (1.5 * $scale);

        // Control point 2: At 75% along path (near s3), small inward offset
        $ctrl2_s1s3_t = 0.75;
        $ctrl2_s1s3_base_x = $capX + ($rightShoulderX - $capX) * $ctrl2_s1s3_t;
        $ctrl2_s1s3_base_y = $capY + ($rightShoulderY - $capY) * $ctrl2_s1s3_t;
        $ctrl2_s1s3_x = $ctrl2_s1s3_base_x + cos($perpendicular_s1_s3) * (0.2 * $scale);  // 0.2" inward near s3 (reduced depth)
        $ctrl2_s1s3_y = $ctrl2_s1s3_base_y + sin($perpendicular_s1_s3) * (0.2 * $scale);

        // Calculate path lengths
        // s2 to s1 (S-curve using cubic bezier)
        $s2_s1_length = 0;
        $prevX = $leftShoulderX;
        $prevY = $leftShoulderY;
        for ($t = 0.01; $t <= 1.0; $t += 0.01) {
            $x = cubicBezier_sleeve($t, $leftShoulderX, $ctrl1_s2s1_x, $ctrl2_s2s1_x, $capX);
            $y = cubicBezier_sleeve($t, $leftShoulderY, $ctrl1_s2s1_y, $ctrl2_s2s1_y, $capY);
            $s2_s1_length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
            $prevX = $x;
            $prevY = $y;
        }

        // s1 to s3 (S-curve using cubic bezier)
        $s1_s3_length = 0;
        $prevX = $capX;
        $prevY = $capY;
        for ($t = 0.01; $t <= 1.0; $t += 0.01) {
            $x = cubicBezier_sleeve($t, $capX, $ctrl1_s1s3_x, $ctrl2_s1s3_x, $rightShoulderX);
            $y = cubicBezier_sleeve($t, $capY, $ctrl1_s1s3_y, $ctrl2_s1s3_y, $rightShoulderY);
            $s1_s3_length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
            $prevX = $x;
            $prevY = $y;
        }

        // Total path length
        $totalPathLength = $s2_s1_length + $s1_s3_length;
        $lengthError = $totalPathLength - $targetArmholeLength;

        // Check convergence
        if (abs($lengthError) < $capHeightTolerance) {
            $converged = true;
            break;
        }

        // Adjust cap height using binary search
        if ($totalPathLength < $targetArmholeLength) {
            // Path too short, INCREASE cap height (moves s2/s3 down, increases curve length)
            $minCapHeight = $capHeight;
        } else {
            // Path too long, DECREASE cap height (moves s2/s3 up, decreases curve length)
            $maxCapHeight = $capHeight;
        }
        $capHeight = ($minCapHeight + $maxCapHeight) / 2;
    }

    // Final calculations with converged cap height
    // s2 to s3 distance = saround (bicep measurement)
    $leftShoulderX = $sleeveLeftMargin;
    $leftShoulderY = $sleeveTopMargin + $capHeight;
    $rightShoulderX = $sleeveLeftMargin + ($saround * $scale);
    $rightShoulderY = $sleeveTopMargin + $capHeight;

    // Store iteration info
    $capHeightIterations = $iterations;
    $capHeightConverged = $converged;
    $finalCapHeight = $capHeight;
    $finalTotalPathLength = $totalPathLength;

} else {
    // Use fixed cap height (no iteration)
    $capHeight = 4.5 * $scale;

    // Cap centered over bicep width
    $capX = $sleeveLeftMargin + (($saround * $scale) / 2);
    $capY = $sleeveTopMargin;

    // s2 to s3 distance = saround (bicep measurement)
    $leftShoulderX = $sleeveLeftMargin;
    $leftShoulderY = $sleeveTopMargin + $capHeight;
    $rightShoulderX = $sleeveLeftMargin + ($saround * $scale);
    $rightShoulderY = $sleeveTopMargin + $capHeight;

    // Wrist points - sleeve length measured from s1 (cap)
    $wristCenter = $capX;
    $wristHalfWidth = ($sopen * $scale) / 2;
    $leftWristX = $wristCenter - $wristHalfWidth;
    $leftWristY = $capY + ($slength * $scale);  // Measure from s1 level
    $rightWristX = $wristCenter + $wristHalfWidth;
    $rightWristY = $capY + ($slength * $scale);  // Measure from s1 level

    $capHeightIterations = 0;
    $capHeightConverged = false;
    $finalCapHeight = $capHeight;
    $finalTotalPathLength = 0;
}

// =============================================================================
// SLEEVE NODES - Store all key points for reference and rendering
// =============================================================================

$sleeveNodes = [
    's1' => ['x' => $capX, 'y' => $capY, 'label' => 'Cap', 'color' => '#10B981'],
    's2' => ['x' => $leftShoulderX, 'y' => $leftShoulderY, 'label' => 'Left Shoulder', 'color' => '#10B981'],
    's3' => ['x' => $rightShoulderX, 'y' => $rightShoulderY, 'label' => 'Right Shoulder', 'color' => '#10B981'],
    's4' => ['x' => $leftWristX, 'y' => $leftWristY, 'label' => 'Left Wrist', 'color' => '#10B981'],
    's5' => ['x' => $rightWristX, 'y' => $rightWristY, 'label' => 'Right Wrist', 'color' => '#10B981'],

    // Smooth top nodes - 0.5" below s1, 0.25" horizontal offset
    // HIDDEN: s11 and s12 nodes (replaced with direct s2→s1→s3 smooth curves)
    // 's11' => ['x' => $capX - (0.25 * $scale), 'y' => $capY + (0.5 * $scale), 'label' => 'Left Smooth', 'color' => '#10B981'],
    // 's12' => ['x' => $capX + (0.25 * $scale), 'y' => $capY + (0.5 * $scale), 'label' => 'Right Smooth', 'color' => '#10B981'],

    // Control points for curves (shown in dev mode)
    'c1' => ['x' => $controlX1 = $capX - (($armhole * $scale) * 0.2), 'y' => $controlY1 = $capY + ($capHeight * 0.3), 'label' => 'Control 1', 'color' => '#EAB308'],
    'c2' => ['x' => $controlX2 = $capX + (($armhole * $scale) * 0.2), 'y' => $controlY2 = $capY + ($capHeight * 0.3), 'label' => 'Control 2', 'color' => '#EAB308'],

    // Center point
    'center' => ['x' => $capX, 'y' => ($sleeveTopMargin + $leftWristY) / 2, 'label' => 'Center', 'color' => '#6366F1'],
];

// Red cutting line nodes will be added after red line calculations (see line ~2880)

// Export to JSON for JavaScript rendering
$sleeveNodesJson = json_encode($sleeveNodes);

// Helper function to access sleeve node coordinates
function sn($nodeName, $coord) {
    global $sleeveNodes;
    return $sleeveNodes[$nodeName][$coord] ?? 0;
}

// =============================================================================
// HELPER FUNCTIONS (must be defined before use)
// =============================================================================

// Helper function to calculate distance from a point to a line
if (!function_exists('distanceToLine_sleeve')) {
    function distanceToLine_sleeve($px, $py, $x1, $y1, $x2, $y2) {
        $A = $px - $x1;
        $B = $py - $y1;
        $C = $x2 - $x1;
        $D = $y2 - $y1;

        $dot = $A * $C + $B * $D;
        $len_sq = $C * $C + $D * $D;
        $param = $len_sq != 0 ? $dot / $len_sq : -1;

        if ($param < 0) {
            $xx = $x1;
            $yy = $y1;
        } elseif ($param > 1) {
            $xx = $x2;
            $yy = $y2;
        } else {
            $xx = $x1 + $param * $C;
            $yy = $y1 + $param * $D;
        }

        return sqrt(pow($px - $xx, 2) + pow($py - $yy, 2));
    }
}

// Helper function to evaluate cubic bezier at t
if (!function_exists('cubicBezier_sleeve')) {
    function cubicBezier_sleeve($t, $p0, $p1, $p2, $p3) {
        $mt = 1 - $t;
        return $mt * $mt * $mt * $p0 +
               3 * $mt * $mt * $t * $p1 +
               3 * $mt * $t * $t * $p2 +
               $t * $t * $t * $p3;
    }
}

// =============================================================================
// REUSABLE S-CURVE BUILDER FUNCTION (Iterative)
// =============================================================================
// Builds an S-curve between two points that touches specified parallel lines
// Returns array with control points for cubic bezier curve
// =============================================================================

if (!function_exists('buildSCurve_iterative')) {
    /**
     * Build S-curve with iterative adjustment to touch parallel lines
     *
     * @param float $startX - Start point X coordinate
     * @param float $startY - Start point Y coordinate
     * @param float $endX - End point X coordinate
     * @param float $endY - End point Y coordinate
     * @param float $parallelOffset - Distance of parallel lines from center (e.g., 0.5" in pixels)
     * @param float $ctrl1Position - Position of first control point (0.0 to 1.0, default 0.35)
     * @param float $ctrl2Position - Position of second control point (0.0 to 1.0, default 0.65)
     * @param int $maxIterations - Maximum iterations for convergence (default 100)
     * @param float $tolerance - Convergence tolerance in pixels (default 0.1)
     *
     * @return array - ['ctrl1_x', 'ctrl1_y', 'ctrl2_x', 'ctrl2_y', 'iterations', 'converged']
     */
    function buildSCurve_iterative($startX, $startY, $endX, $endY, $parallelOffset,
                                  $ctrl1Position = 0.35, $ctrl2Position = 0.65,
                                  $maxIterations = 100, $tolerance = 0.1) {

        // Calculate angle and perpendicular for the line
        $angle = atan2($endY - $startY, $endX - $startX);
        $perpendicular = $angle + (M_PI / 2);

        // Initialize control point offsets (start with parallel offset as base)
        $ctrl1_offset = $parallelOffset * 1.5; // Start with 1.5x offset
        $ctrl2_offset = $parallelOffset * 1.5;

        // Binary search bounds for offsets
        $minOffset = 0;
        $maxOffset = $parallelOffset * 5; // Maximum 5x the parallel offset

        $converged = false;
        $iterations = 0;

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $iterations = $iter + 1;

            // Calculate control point 1 base position
            $ctrl1_base_x = $startX + ($endX - $startX) * $ctrl1Position;
            $ctrl1_base_y = $startY + ($endY - $startY) * $ctrl1Position;
            // Offset upward (positive perpendicular)
            $ctrl1_x = $ctrl1_base_x + cos($perpendicular) * $ctrl1_offset;
            $ctrl1_y = $ctrl1_base_y + sin($perpendicular) * $ctrl1_offset;

            // Calculate control point 2 base position
            $ctrl2_base_x = $startX + ($endX - $startX) * $ctrl2Position;
            $ctrl2_base_y = $startY + ($endY - $startY) * $ctrl2Position;
            // Offset downward (negative perpendicular)
            $ctrl2_x = $ctrl2_base_x - cos($perpendicular) * $ctrl2_offset;
            $ctrl2_y = $ctrl2_base_y - sin($perpendicular) * $ctrl2_offset;

            // Sample the curve to find maximum distances to parallel lines
            $maxDistToUpperLine = 0;
            $maxDistToLowerLine = 0;

            for ($t = 0; $t <= 1; $t += 0.02) {
                // Evaluate cubic bezier at parameter t
                $x = cubicBezier_sleeve($t, $startX, $ctrl1_x, $ctrl2_x, $endX);
                $y = cubicBezier_sleeve($t, $startY, $ctrl1_y, $ctrl2_y, $endY);

                // Upper parallel line endpoints
                $upperLineX1 = $startX + cos($perpendicular) * $parallelOffset;
                $upperLineY1 = $startY + sin($perpendicular) * $parallelOffset;
                $upperLineX2 = $endX + cos($perpendicular) * $parallelOffset;
                $upperLineY2 = $endY + sin($perpendicular) * $parallelOffset;

                // Lower parallel line endpoints
                $lowerLineX1 = $startX - cos($perpendicular) * $parallelOffset;
                $lowerLineY1 = $startY - sin($perpendicular) * $parallelOffset;
                $lowerLineX2 = $endX - cos($perpendicular) * $parallelOffset;
                $lowerLineY2 = $endY - sin($perpendicular) * $parallelOffset;

                $distToUpper = distanceToLine_sleeve($x, $y, $upperLineX1, $upperLineY1, $upperLineX2, $upperLineY2);
                $distToLower = distanceToLine_sleeve($x, $y, $lowerLineX1, $lowerLineY1, $lowerLineX2, $lowerLineY2);

                // Track closest approach to each line in respective halves
                if ($t < 0.5) {
                    $maxDistToUpperLine = max($maxDistToUpperLine, $distToUpper);
                } else {
                    $maxDistToLowerLine = max($maxDistToLowerLine, $distToLower);
                }
            }

            // Calculate errors (we want distance = 0, meaning touching)
            $error1 = $maxDistToUpperLine - 0;
            $error2 = $maxDistToLowerLine - 0;

            // Check convergence
            if (abs($error1) < $tolerance && abs($error2) < $tolerance) {
                $converged = true;
                break;
            }

            // Adjust offsets proportionally to reduce error
            // If error is positive, we're too far from the line, need more offset
            $ctrl1_offset += $error1 * 0.3;
            $ctrl2_offset += $error2 * 0.3;

            // Clamp offsets to reasonable bounds
            $ctrl1_offset = max($minOffset, min($maxOffset, $ctrl1_offset));
            $ctrl2_offset = max($minOffset, min($maxOffset, $ctrl2_offset));
        }

        // Return final control points and convergence info
        return [
            'ctrl1_x' => $ctrl1_x,
            'ctrl1_y' => $ctrl1_y,
            'ctrl2_x' => $ctrl2_x,
            'ctrl2_y' => $ctrl2_y,
            'iterations' => $iterations,
            'converged' => $converged,
            'ctrl1_offset' => $ctrl1_offset,
            'ctrl2_offset' => $ctrl2_offset
        ];
    }
}

// =============================================================================
// SLEEVE CAP HEIGHT CALCULATION (Iterative)
// =============================================================================
// Move s1 (cap point) UP or DOWN so that the armhole path length
// s2→s1→s3 equals the FULL armhole measurement
//
// The SHAPE remains the same (S-curve bulge amount stays constant)
// Only the vertical position of s1 changes to match armhole length
//
// TOGGLE: Set to false to use fixed position (faster)
//         Set to true to use iterative calculation (accurate armhole matching)
// =============================================================================

// Parallel offset for curves (constant throughout) - needed for red cutting line
$redOffset = 0.5 * $scale;

$useIterativeCapHeight = false; // Set to true to enable iterative calculation

if ($useIterativeCapHeight) {
// Target length: s2→s1→s3 should equal FULL armhole measurement
// Using the customer's armhole measurement directly (e.g., 15 inches)
$targetArmholeLength = $armhole * $scale;

// Binary search parameters for s1 vertical position
$minS1Offset = -2.0 * $scale;  // s1 can move up 2 inches
$maxS1Offset = 2.0 * $scale;   // s1 can move down 2 inches
$s1OffsetTolerance = 0.5;      // 0.5 pixel tolerance
$maxCapHeightIterations = 50;

// Initial s1 offset (0 = at original capY position)
$s1Offset = 0;

for ($capIter = 0; $capIter < $maxCapHeightIterations; $capIter++) {
    // Move s1 up or down, s11 and s12 follow (maintaining 0.5" below s1)
    $s1_y_adjusted = $capY + $s1Offset;
    $capHeight = 0.5 * $scale; // Fixed distance from s1 to s11/s12

    $s11_x = $capX - (0.5 * $scale);
    $s11_y = $s1_y_adjusted + $capHeight;
    $s12_x = $capX + (0.5 * $scale);
    $s12_y = $s1_y_adjusted + $capHeight;

    // Calculate angle for s2->s11 line
    $angle_s2_s11 = atan2($s11_y - $leftShoulderY, $s11_x - $leftShoulderX);
    $perpendicular_s2_s11 = $angle_s2_s11 + (M_PI / 2);

    // Calculate S-curve control points manually (keep shape constant with 2.5x bulge)
    $ctrl1_t = 0.35;
    $ctrl1_base_x = $leftShoulderX + ($s11_x - $leftShoulderX) * $ctrl1_t;
    $ctrl1_base_y = $leftShoulderY + ($s11_y - $leftShoulderY) * $ctrl1_t;
    $ctrl1_x = $ctrl1_base_x + cos($perpendicular_s2_s11) * ($redOffset * 2.5);
    $ctrl1_y = $ctrl1_base_y + sin($perpendicular_s2_s11) * ($redOffset * 2.5);

    $ctrl2_t = 0.65;
    $ctrl2_base_x = $leftShoulderX + ($s11_x - $leftShoulderX) * $ctrl2_t;
    $ctrl2_base_y = $leftShoulderY + ($s11_y - $leftShoulderY) * $ctrl2_t;
    $ctrl2_x = $ctrl2_base_x - cos($perpendicular_s2_s11) * ($redOffset * 2.5);
    $ctrl2_y = $ctrl2_base_y - sin($perpendicular_s2_s11) * ($redOffset * 2.5);

    // Calculate length of S-curve from s2 to s11 using manual control points
    $s2_s11_length = 0;
    $prevX = $leftShoulderX;
    $prevY = $leftShoulderY;
    for ($t = 0.01; $t <= 1.0; $t += 0.01) {
        $x = cubicBezier_sleeve($t, $leftShoulderX, $ctrl1_x, $ctrl2_x, $s11_x);
        $y = cubicBezier_sleeve($t, $leftShoulderY, $ctrl1_y, $ctrl2_y, $s11_y);
        $s2_s11_length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    // Calculate straight line length from s11 to s12
    $s11_s12_length = sqrt(pow($s12_x - $s11_x, 2) + pow($s12_y - $s11_y, 2));

    // Calculate angle for s12->s3 line
    $angle_s12_s3 = atan2($rightShoulderY - $s12_y, $rightShoulderX - $s12_x);
    $perpendicular_s12_s3 = $angle_s12_s3 + (M_PI / 2);

    // Calculate outward bulge control point for s12→s3
    $ctrl3_t = 0.25;
    $ctrl3_base_x = $s12_x + ($rightShoulderX - $s12_x) * $ctrl3_t;
    $ctrl3_base_y = $s12_y + ($rightShoulderY - $s12_y) * $ctrl3_t;
    $ctrl3_x = $ctrl3_base_x - cos($perpendicular_s12_s3) * ($redOffset * 1.5);
    $ctrl3_y = $ctrl3_base_y - sin($perpendicular_s12_s3) * ($redOffset * 1.5);

    // Calculate length of bulge curve from s12 to s3
    $s12_s3_length = 0;
    $prevX = $s12_x;
    $prevY = $s12_y;
    for ($t = 0.01; $t <= 1.0; $t += 0.01) {
        $x = (1 - $t) * (1 - $t) * $s12_x + 2 * (1 - $t) * $t * $ctrl3_x + $t * $t * $rightShoulderX;
        $y = (1 - $t) * (1 - $t) * $s12_y + 2 * (1 - $t) * $t * $ctrl3_y + $t * $t * $rightShoulderY;
        $s12_s3_length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
        $prevX = $x;
        $prevY = $y;
    }

    // Total armhole path length
    $totalArmholeLength = $s2_s11_length + $s11_s12_length + $s12_s3_length;

    // Calculate error
    $lengthError = $totalArmholeLength - $targetArmholeLength;

    // Check convergence
    if (abs($lengthError) < $s1OffsetTolerance) {
        break; // Converged!
    }

    // Adjust s1 offset using binary search
    if ($totalArmholeLength < $targetArmholeLength) {
        // Path too short, move s1 DOWN (increase offset)
        $minS1Offset = $s1Offset;
    } else {
        // Path too long, move s1 UP (decrease offset)
        $maxS1Offset = $s1Offset;
    }
    $s1Offset = ($minS1Offset + $maxS1Offset) / 2;
}

    // Final s11 and s12 coordinates using converged s1 offset
    $s1_y_adjusted = $capY + $s1Offset;
    $capHeight = 0.5 * $scale; // Fixed distance from s1 to s11/s12

    $s11_x = $capX - (0.5 * $scale);
    $s11_y = $s1_y_adjusted + $capHeight;
    $s12_x = $capX + (0.5 * $scale);
    $s12_y = $s1_y_adjusted + $capHeight;

    // Store iteration info for debug display
    $capHeightIterations = $capIter + 1;
    $finalS1OffsetInches = $s1Offset / $scale;
    $finalCapHeightInches = $capHeight / $scale;
    $finalTotalArmholeLength = $totalArmholeLength;
    $armholeLengthError = $lengthError;
} else {
    // Use fixed cap height (original design - 0.5")
    $capHeight = 0.5 * $scale;

    $s11_x = $capX - (0.5 * $scale);
    $s11_y = $capY + $capHeight;
    $s12_x = $capX + (0.5 * $scale);
    $s12_y = $capY + $capHeight;

    // Set dummy values for display
    $capHeightIterations = 0;
    $finalCapHeightInches = 0.5;
    $finalTotalArmholeLength = 0;
    $armholeLengthError = 0;
}

// =============================================================================
// SLEEVE BLACK LINE - S-curve from s2→s1, smooth curve from s1→s3
// =============================================================================

// Use the control points calculated during iteration
// S-curve: ctrl1_s2s1, ctrl2_s2s1 for s2→s1
// Smooth curve: s1_s3_ctrl for s1→s3

// Build sleeve path
$sleeveBlack = "M" . $leftShoulderX . "," . $leftShoulderY;
// s2 to s1 (S-curve using cubic bezier)
$sleeveBlack .= " C" . $ctrl1_s2s1_x . "," . $ctrl1_s2s1_y . " " . $ctrl2_s2s1_x . "," . $ctrl2_s2s1_y . " " . $capX . "," . $capY;
// s1 to s3 (S-curve using cubic bezier - more bulge near s1, less depth at s3)
$sleeveBlack .= " C" . $ctrl1_s1s3_x . "," . $ctrl1_s1s3_y . " " . $ctrl2_s1s3_x . "," . $ctrl2_s1s3_y . " " . $rightShoulderX . "," . $rightShoulderY;

// s3 to s5 to s4 and back to s2
$sleeveBlack .= " L" . $rightWristX . "," . $rightWristY;          // Line to s5
$sleeveBlack .= " L" . $leftWristX . "," . $leftWristY;            // Line to s4
$sleeveBlack .= " Z";                                              // Close path back to s2

// =============================================================================
// CALCULATE ACTUAL PATH LENGTH: s2→s1→s3
// =============================================================================

// Calculate S-curve length from s2 to s1 (cubic bezier)
$actualS2S1Length = 0;
$prevX = $leftShoulderX;
$prevY = $leftShoulderY;
for ($t = 0.01; $t <= 1.0; $t += 0.01) {
    $x = cubicBezier_sleeve($t, $leftShoulderX, $ctrl1_s2s1_x, $ctrl2_s2s1_x, $capX);
    $y = cubicBezier_sleeve($t, $leftShoulderY, $ctrl1_s2s1_y, $ctrl2_s2s1_y, $capY);
    $actualS2S1Length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
    $prevX = $x;
    $prevY = $y;
}

// Calculate S-curve length from s1 to s3 (cubic bezier)
$actualS1S3Length = 0;
$prevX = $capX;
$prevY = $capY;
for ($t = 0.01; $t <= 1.0; $t += 0.01) {
    $x = cubicBezier_sleeve($t, $capX, $ctrl1_s1s3_x, $ctrl2_s1s3_x, $rightShoulderX);
    $y = cubicBezier_sleeve($t, $capY, $ctrl1_s1s3_y, $ctrl2_s1s3_y, $rightShoulderY);
    $actualS1S3Length += sqrt(pow($x - $prevX, 2) + pow($y - $prevY, 2));
    $prevX = $x;
    $prevY = $y;
}

// Total path length s2→s1→s3
$actualArmholePathLength = $actualS2S1Length + $actualS1S3Length;
$actualArmholePathInches = $actualArmholePathLength / $scale;

// Calculate vertical distance from s1(y) to s2(y) - this is the cap height
$s1ToS2VerticalDistance = abs($leftShoulderY - $capY);
$s1ToS2VerticalInches = $s1ToS2VerticalDistance / $scale;

// Calculate straight line distance from s2 to s3 (horizontal span)
$s2ToS3Distance = sqrt(pow($rightShoulderX - $leftShoulderX, 2) + pow($rightShoulderY - $leftShoulderY, 2));
$s2ToS3Inches = $s2ToS3Distance / $scale;

// Calculate vertical distance from s1(y) to s4(y) - total sleeve length from cap to wrist
$s1ToS4VerticalDistance = abs($leftWristY - $capY);
$s1ToS4VerticalInches = $s1ToS4VerticalDistance / $scale;

// =============================================================================
// SLEEVE GRAY LINE - Inner guide line (straight lines for reference)
// =============================================================================

// SLEEVE GRAY LINE - with smooth curves at cap (s1)
$sleeveGray = "M" . $leftShoulderX . "," . $leftShoulderY;

// Smooth curve from s2 to s1 using quadratic bezier
// Control point is slightly above s1 to create gentle arc
$s2_s1_ctrl_x = ($leftShoulderX + $capX) / 2;
$s2_s1_ctrl_y = ($leftShoulderY + $capY) / 2 - (0.3 * $scale);  // 0.3" above midpoint
$sleeveGray .= " Q" . $s2_s1_ctrl_x . "," . $s2_s1_ctrl_y . " " . $capX . "," . $capY;

// Smooth curve from s1 to s3 using quadratic bezier
$s1_s3_ctrl_x = ($capX + $rightShoulderX) / 2;
$s1_s3_ctrl_y = ($capY + $rightShoulderY) / 2 - (0.3 * $scale);  // 0.3" above midpoint
$sleeveGray .= " Q" . $s1_s3_ctrl_x . "," . $s1_s3_ctrl_y . " " . $rightShoulderX . "," . $rightShoulderY;
$sleeveGray .= " L" . $rightWristX . "," . $rightWristY;
$sleeveGray .= " L" . $leftWristX . "," . $leftWristY;
$sleeveGray .= " Z";

// =============================================================================
// SLEEVE RED LINE - Cutting line with seam allowance (0.5" all around)
// =============================================================================

$seamAllowance = 0.5 * $scale;

// Left shoulder (s2) with seam allowance - extend left and slightly up
$redLeftShoulderX = $leftShoulderX - $seamAllowance - (1.0 * $scale); // Additional 1" left
$redLeftShoulderY = $leftShoulderY - ($seamAllowance * 0.3); // Slight upward offset

// s11 with seam allowance - positioned below s2
$red_s11_x = $s11_x - $seamAllowance;
$red_s11_y = $s11_y;

// Cap (s1) with seam allowance (higher)
$redCapX = $capX;
$redCapY = $capY - $seamAllowance;

// s12 with seam allowance - positioned below s3
$red_s12_x = $s12_x + $seamAllowance;
$red_s12_y = $s12_y;

// Right shoulder (s3) with seam allowance - extend right and slightly up
$redRightShoulderX = $rightShoulderX + $seamAllowance + (1.0 * $scale); // Additional 1" right
$redRightShoulderY = $rightShoulderY - ($seamAllowance * 0.3); // Slight upward offset

// Wrist points with seam allowance
$redLeftWristX = $leftWristX - $seamAllowance - (1.0 * $scale); // Additional 1" left
$redLeftWristY = $leftWristY + $seamAllowance;

$redRightWristX = $rightWristX + $seamAllowance + (1.0 * $scale); // Additional 1" right
$redRightWristY = $rightWristY + $seamAllowance;

// Build the red cutting line path
$sleeveRed = "M" . $redLeftShoulderX . "," . $redLeftShoulderY;

// LEFT S-CURVE: sc2 -> sc1 direct curve (cubic Bezier)
// Calculate angle and perpendicular for sc2 to sc1
$angle_sc2_sc1 = atan2($redCapY - $redLeftShoulderY, $redCapX - $redLeftShoulderX);
$perpendicular_sc2_sc1 = $angle_sc2_sc1 + (M_PI / 2);

// Calculate control points with seam allowance applied
$redSCurveDepth = ($redOffset * 2.0) + (0.3 * $scale) + ($seamAllowance * 0.5); // Slightly more depth for cutting line

// Control point 1 for left curve (at 35% along path)
$red_ctrl1_t = 0.35;
$red_ctrl1_base_x = $redLeftShoulderX + ($redCapX - $redLeftShoulderX) * $red_ctrl1_t;
$red_ctrl1_base_y = $redLeftShoulderY + ($redCapY - $redLeftShoulderY) * $red_ctrl1_t;
$red_ctrl1_x = $red_ctrl1_base_x + cos($perpendicular_sc2_sc1) * $redSCurveDepth;
$red_ctrl1_y = $red_ctrl1_base_y + sin($perpendicular_sc2_sc1) * $redSCurveDepth;

// Control point 2 for left curve (at 65% along path)
$red_ctrl2_t = 0.65;
$red_ctrl2_base_x = $redLeftShoulderX + ($redCapX - $redLeftShoulderX) * $red_ctrl2_t;
$red_ctrl2_base_y = $redLeftShoulderY + ($redCapY - $redLeftShoulderY) * $red_ctrl2_t;
$red_ctrl2_x = $red_ctrl2_base_x - cos($perpendicular_sc2_sc1) * $redSCurveDepth + (0.3 * $scale);
$red_ctrl2_y = $red_ctrl2_base_y - sin($perpendicular_sc2_sc1) * $redSCurveDepth - (0.2 * $scale);

// Direct curve from sc2 to sc1
$sleeveRed .= " C" . $red_ctrl1_x . "," . $red_ctrl1_y . " " . $red_ctrl2_x . "," . $red_ctrl2_y . " " . $redCapX . "," . $redCapY;

// RIGHT OUTWARD BULGE: sc1 -> sc5 direct curve (cubic Bezier)
// Calculate angle and perpendicular for sc1 to sc5
$angle_sc1_sc5 = atan2($redRightShoulderY - $redCapY, $redRightShoulderX - $redCapX);
$perpendicular_sc1_sc5 = $angle_sc1_sc5 + (M_PI / 2);

// Reduced bulge depth for right curve (50% of S-curve depth)
$redBulgeDepth = $redSCurveDepth * 0.5;

// For outward bulge, both control points should be on the same side (negative/downward)
// Control point 3 for right curve (at 35% along sc1->sc5 path)
$red_ctrl3_t = 0.35;
$red_ctrl3_base_x = $redCapX + ($redRightShoulderX - $redCapX) * $red_ctrl3_t;
$red_ctrl3_base_y = $redCapY + ($redRightShoulderY - $redCapY) * $red_ctrl3_t;
$red_ctrl3_x = $red_ctrl3_base_x - cos($perpendicular_sc1_sc5) * $redBulgeDepth;
$red_ctrl3_y = $red_ctrl3_base_y - sin($perpendicular_sc1_sc5) * $redBulgeDepth;

// Control point 4 for right curve (at 65% along sc1->sc5 path)
// Same direction (negative) for outward bulge
$red_ctrl4_t = 0.65;
$red_ctrl4_base_x = $redCapX + ($redRightShoulderX - $redCapX) * $red_ctrl4_t;
$red_ctrl4_base_y = $redCapY + ($redRightShoulderY - $redCapY) * $red_ctrl4_t;
$red_ctrl4_x = $red_ctrl4_base_x - cos($perpendicular_sc1_sc5) * $redBulgeDepth;
$red_ctrl4_y = $red_ctrl4_base_y - sin($perpendicular_sc1_sc5) * $redBulgeDepth;

// Direct curve from sc1 to sc5 with outward bulge
$sleeveRed .= " C" . $red_ctrl3_x . "," . $red_ctrl3_y . " " . $red_ctrl4_x . "," . $red_ctrl4_y . " " . $redRightShoulderX . "," . $redRightShoulderY;

// Down to right wrist
$sleeveRed .= " L" . $redRightWristX . "," . $redRightWristY;

// Across to left wrist
$sleeveRed .= " L" . $redLeftWristX . "," . $redLeftWristY;

// Close path
$sleeveRed .= " Z";

// =============================================================================
// SLEEVE RED CUTTING LINE NODES - Add to sleeveNodes array
// =============================================================================
// Red cutting line nodes (with seam allowance) - sc prefix for "sleeve cutting"
$sleeveNodes['sc1'] = ['x' => $redCapX, 'y' => $redCapY, 'label' => 'Cap+SA', 'color' => '#DC2626'];
$sleeveNodes['sc2'] = ['x' => $redLeftShoulderX, 'y' => $redLeftShoulderY, 'label' => 'L.Shoulder+SA', 'color' => '#DC2626'];
// sc3 removed (s11+SA not needed)
// sc4 removed (s12+SA not needed)
$sleeveNodes['sc5'] = ['x' => $redRightShoulderX, 'y' => $redRightShoulderY, 'label' => 'R.Shoulder+SA', 'color' => '#DC2626'];
$sleeveNodes['sc6'] = ['x' => $redLeftWristX, 'y' => $redLeftWristY, 'label' => 'L.Wrist+SA', 'color' => '#DC2626'];
$sleeveNodes['sc7'] = ['x' => $redRightWristX, 'y' => $redRightWristY, 'label' => 'R.Wrist+SA', 'color' => '#DC2626'];

// Calculate Sleeve bounding box (now that all nodes including cutting line nodes are defined)
$sleeveBounds = calculatePatternBoundingBox($sleeveNodes, $scale);

// =============================================================================
// CENTER FOLD LINE - Vertical line down center
// =============================================================================

$centerFoldLine = "M" . $capX . "," . $capY;
$centerFoldLine .= " L" . $capX . "," . $leftWristY;

// SVG dimensions - based on bicep (saround) width + margins
$sleeveSvgWidth = (($saround + 4) * $scale) * $viewScale;
$sleeveSvgHeight = (($slength + 6) * $scale) * $viewScale;

// =============================================================================
// BUILD PATTERN DATA STRUCTURE (NEW)
// =============================================================================
// Centralized data structure containing all calculated pattern data
// This makes it easy to export, cache, or use in other parts of the application
// =============================================================================

// Calculate scissors positions for patti
$scissors_pc1_pc4_x = (pn('pc1', 'x') + pn('pc4', 'x')) / 2;
$scissors_pc1_pc4_y = (pn('pc1', 'y') + pn('pc4', 'y')) / 2;
$scissors_qc3_qc5_x = (pn('qc3', 'x') + pn('qc5', 'x')) / 2;
$scissors_qc3_qc5_y = (pn('qc3', 'y') + pn('qc5', 'y')) / 2;

// Calculate scissors positions for sleeve
$scissors_sc6_sc7_x = (sn('sc6', 'x') + sn('sc7', 'x')) / 2;
$scissors_sc6_sc7_y = (sn('sc6', 'y') + sn('sc7', 'y')) / 2;

// =============================================================================
// PRINTER TEST SCALE BOX (2" x 2")
// =============================================================================
// Scale box positioned on front pattern for print verification
$scaleSize = 2.0 * $scale;  // 2 inches
$scaleIncrement = 0.5 * $scale;  // 0.5 inch increments
$scaleX = n('r71','x') + (1.5 * $scale);  // Left edge: 1.5" from r71.x
$scaleY = n('r71','y') - ($scaleSize / 2);  // Center aligned with r71.y

$patternData = [
    'metadata' => [
        'customer_id' => $measurements['customer_id'] ?? $customerId ?? null,
        'customer_name' => $customerName,
        'measurement_id' => $measurements['measurement_id'] ?? $measurementId ?? null,
        'generated_at' => time()
    ],
    'measurements' => compact('bust', 'chest', 'waist', 'bnDepth', 'armhole', 'shoulder', 
                              'frontNeckDepth', 'fshoulder', 'blength', 'flength', 'slength', 
                              'apex', 'saround', 'sopen'),
    'configuration' => compact('scale', 'viewScale', 'originX', 'originY', 'marginLeft', 'marginTop'),
    'derived' => compact('qBust', 'qWaist', 'bottomTuckWidth'),
    'front' => [
        'nodes' => $nodes,
        'paths' => ['armhole' => $armholeSvgPath],
        'svg' => compact('svgWidth', 'svgHeight', 'svgWidthInches', 'svgHeightInches'),
        'scale_box' => compact('scaleSize', 'scaleIncrement', 'scaleX', 'scaleY')
    ],
    'back' => [
        'nodes' => $backNodes,
        'paths' => ['armhole' => $backArmholePath, 'cutting' => $backRedCuttingPath]
    ],
    'patti' => [
        'nodes' => $pattiNodes,
        'dimensions' => compact('pattiWidth', 'pattiLength', 'pattiSeamAllowance', 'patti2Width', 'patti2Height'),
        'svg' => compact('pattiSvgWidth', 'pattiSvgHeight', 'maxPattiWidth'),
        'scissors' => compact('scissors_pc1_pc4_x', 'scissors_pc1_pc4_y', 'scissors_qc3_qc5_x', 'scissors_qc3_qc5_y')
    ],
    'sleeve' => [
        'nodes' => $sleeveNodes,
        'paths' => compact('sleeveBlack', 'sleeveGray', 'sleeveRed', 'centerFoldLine'),
        'measurements' => compact('capHeight', 'actualArmholePathInches'),
        'svg' => compact('sleeveSvgWidth', 'sleeveSvgHeight'),
        'scissors' => compact('scissors_sc6_sc7_x', 'scissors_sc6_sc7_y')
    ]
];

// =============================================================================
// SESSION STORAGE (NEW)
// =============================================================================
// Store pattern data in session for reuse and caching
// This enables quick pattern retrieval without recalculation
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cacheKey = "pattern_" . ($measurementId ?? $measurements['measurement_id'] ?? 'latest');
$_SESSION[$cacheKey] = [
    'data' => $patternData,
    'timestamp' => time(),
    'hash' => md5(json_encode($patternData['measurements']))
];
$_SESSION['latest_pattern'] = $cacheKey;

// =============================================================================
// END OF SECTION 2: ALL CALCULATIONS COMPLETE
// =============================================================================
// All business logic is now complete. Variables available for presentation:
//
// Front/Back: $nodes, $backNodes, $armholeSvgPath, $backArmholePath, etc.
// Patti: $pattiNodes, $pattiWidth, $pattiLength, etc.
// Sleeve: $sleeveNodes, $sleeveBlack, $sleeveGray, $sleeveRed, etc.
// Unified: $patternData (contains everything)
//
// NO CALCULATIONS SHOULD HAPPEN BELOW THIS LINE
// =============================================================================

?>
<?php
// =============================================================================
// SECTION 3: PRESENTATION LAYER (HTML/SVG RENDERING)
// =============================================================================
// This section ONLY renders the data calculated in Section 2
// It contains HTML structure, CSS styling, and SVG drawing commands
// All data references use variables/functions defined in Section 2
//
// CHANGES FROM ORIGINAL:
// - Removed inline Patti calculations (now in Section 2)
// - Removed inline Sleeve calculations (now in Section 2)
// - Pure presentation only - no business logic
// =============================================================================
?>
<?php
// =============================================================================
// SECTION 3: PRESENTATION LAYER (HTML/SVG RENDERING)
// =============================================================================
// This section ONLY renders the data calculated in Section 2
// It contains HTML structure, CSS styling, and SVG drawing commands
// All data references use variables/functions defined in Section 2
// =============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pattern - <?php echo htmlspecialchars($customerName); ?></title>
    <!-- ========================================== -->
    <!-- PRESENTATION: CSS STYLES -->
    <!-- ========================================== -->
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        .info { color: #666; margin-bottom: 15px; font-size: 14px; }

        /* Main layout - SVG left, table right */
        .main-layout { display: flex; gap: 20px; align-items: flex-start; }
        .svg-panel { flex: 0 0 auto; display: flex; justify-content: center; }
        .svg-container { border: 1px solid #ddd; background: white; display: inline-block; margin: 0 auto; }

        /* Node table panel */
        .node-panel { flex: 1; min-width: 450px; }
        .node-table { font-size: 11px; }
        .node-table h3 { margin: 0 0 10px 0; font-size: 14px; color: #333; }
        .node-table table { border-collapse: collapse; width: 100%; }
        .node-table th, .node-table td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        .node-table th { background: #f8f8f8; font-weight: 600; }
        .node-table input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 2px 4px;
            font-size: 11px;
            box-sizing: border-box;
        }
        .node-table input:hover { border-color: #ddd; background: #fafafa; }
        .node-table input:focus { border-color: #10B981; background: #fff; outline: none; }
        .node-table .node-name { font-weight: bold; color: #10B981; }
        .node-table .coord-input { width: 45px; text-align: right; }
        .node-table .label-input { width: 100%; }
        .node-table .code-cell { font-family: monospace; font-size: 10px; color: #6366F1; background: #F8FAFC; }

        /* Action buttons */
        .table-actions { margin-top: 10px; display: flex; gap: 8px; }
        .btn { padding: 6px 12px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-add { background: #10B981; color: white; }
        .btn-add:hover { background: #059669; }
        .btn-update { background: #3B82F6; color: white; }
        .btn-update:hover { background: #2563EB; }
        .btn-delete { background: #EF4444; color: white; font-size: 10px; padding: 2px 6px; }
        .btn-delete:hover { background: #DC2626; }

        /* Console panel */
        .console-panel { margin-top: 20px; background: #1E293B; border-radius: 6px; padding: 15px; font-family: monospace; font-size: 11px; color: #E2E8F0; }
        .console-panel h3 { margin: 0 0 10px 0; color: #94A3B8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .console-line { padding: 2px 0; }
        .console-var { color: #10B981; }
        .console-val { color: #F59E0B; }
        .console-comment { color: #64748B; }

        /* 2x2 Pattern Grid Layout */
        .pattern-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 20px;
            margin-top: 20px;
        }

        <?php if ($isIncludedFromPreview): ?>
        /* Scale down for preview mode */
        .pattern-grid {
            gap: 15px;
            margin-top: 0;
        }
        .pattern-cell {
            padding: 10px;
        }
        .pattern-cell h3 {
            font-size: 12px;
            margin: 0 0 8px 0;
        }
        .pattern-cell .cell-info {
            font-size: 10px;
            margin-bottom: 8px;
        }
        .pattern-cell .svg-container {
            max-width: 100%;
        }
        .pattern-cell .svg-container svg {
            max-width: 100%;
            height: auto;
        }
        <?php endif; ?>

        .pattern-cell {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #E2E8F0;
        }

        .pattern-cell h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #334155;
            padding-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
        }

        .pattern-cell .cell-info {
            font-size: 11px;
            color: #64748B;
            margin-bottom: 10px;
        }

        .pattern-cell .svg-container {
            border: 1px solid #ddd;
            background: white;
            display: inline-block;
        }

        /* Cell positions */
        .cell-1x1 { grid-column: 1; grid-row: 1; }  /* Blouse Front */
        .cell-2x1 { grid-column: 2; grid-row: 1; }  /* Blouse Back */
        .cell-1x2 { grid-column: 1; grid-row: 2; }  /* Patti */
        .cell-2x2 { grid-column: 2; grid-row: 2; }  /* Sleeve */

        /* Placeholder for future patterns */
        .pattern-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            background: #F8FAFC;
            border: 2px dashed #CBD5E1;
            border-radius: 6px;
            color: #94A3B8;
        }

        .pattern-placeholder .placeholder-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .pattern-placeholder .placeholder-text {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Snip Marker Styles */
        .snip-marker {
            /* Container for snip triangle and label */
        }

        .snip-triangle {
            /* Triangle shape - always visible */
            fill: #333333;
            stroke: #333333;
            stroke-width: 0.5;
        }

        .snip-ref-number {
            /* Reference number - visible in print mode */
            font-family: Arial, sans-serif;
            font-weight: bold;
            fill: black;
        }

        .snip-label {
            /* Label text - visible in dev mode only */
            font-family: Arial, sans-serif;
            fill: black;
            font-size: 7px;
        }

        /* Hide snip labels in print mode */
        body.print-mode .snip-label {
            display: none;
        }

        /* Hide snip reference numbers in dev mode */
        body.dev-mode .snip-ref-number {
            display: none;
        }

        /* Print/PDF Page Break Styles */
        @media print {
            .page-break {
                page-break-before: always;
                break-before: page;
            }
            .container, .pattern-cell {
                box-shadow: none;
                border: none;
            }
            body {
                background: white;
                margin: 0;
                padding: 10px;
            }
            .pattern-grid {
                gap: 10px;
            }
        }

        /* Screen display for page break indicator */
        @media screen {
            .page-break {
                position: relative;
            }
            .page-break::before {
                content: '--- Page Break (New Page in PDF/Print) ---';
                display: block;
                text-align: center;
                color: #94A3B8;
                font-size: 11px;
                padding: 10px 0;
                margin-bottom: 10px;
                border-top: 2px dashed #E2E8F0;
            }
        }
    </style>
</head>
<body class="<?php echo $isPrintMode ? 'print-mode' : 'dev-mode'; ?>">
    <!-- ========================================== -->
    <!-- PRESENTATION: HTML BODY -->
    <!-- ========================================== -->
    <div class="container">
        <!-- ============================================= -->
        <!-- PRESENTATION: Header with Mode Switcher -->
        <!-- ============================================= -->
        <?php if (!$isIncludedFromPreview): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #E2E8F0;">
            <div>
                <h1 style="margin: 0 0 10px 0;">Saree Blouse Pattern - <?php echo htmlspecialchars($customerName); ?></h1>
                <div class="info">
                    Bust: <?php echo $bust; ?>" |
                    Chest: <?php echo $chest; ?>" |
                    Waist: <?php echo $waist; ?>" |
                    F.Length: <?php echo $flength; ?>" |
                    Armhole: <?php echo $armhole; ?>"
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span style="font-size: 14px; color: #64748B; font-weight: 600;">Mode:</span>
                <a href="?<?php
                    $params = $_GET;
                    $params['mode'] = 'dev';
                    echo http_build_query($params);
                ?>"
                   style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;
                          <?php if ($isDevMode): ?>
                          background: #3B82F6; color: white; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
                          <?php else: ?>
                          background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0;
                          <?php endif; ?>">
                    Dev Mode
                </a>
                <a href="?<?php
                    $params = $_GET;
                    $params['mode'] = 'print';
                    echo http_build_query($params);
                ?>"
                   style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;
                          <?php if ($isPrintMode): ?>
                          background: #10B981; color: white; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
                          <?php else: ?>
                          background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0;
                          <?php endif; ?>">
                    Print Mode
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- 2x2 PATTERN GRID LAYOUT -->
        <div class="pattern-grid">

            <!-- ============================================= -->
            <!-- CELL 1x1: BLOUSE FRONT -->
            <!-- ============================================= -->
            <div class="pattern-cell cell-1x1">
                <h3>1. Blouse Front</h3>
                <div class="cell-info">
                    Armhole: <?php echo $armhole; ?>" (target: <?php echo number_format($targetArmhole, 2); ?>") |
                    Curve: <?php echo number_format($curveLength, 2); ?>"
                </div>
                <!-- ============================================= -->
                <!-- PRESENTATION: Front Pattern SVG -->
                <!-- ============================================= -->
                <div class="svg-container">
                    <?php ob_start(); ?>
                    <svg id="patternSvg" width="<?php echo $svgWidth; ?>" height="<?php echo $svgHeight; ?>"
                         viewBox="<?php echo $frontBounds['minX']; ?> <?php echo $frontBounds['minY']; ?> <?php echo $frontBounds['width']; ?> <?php echo $frontBounds['height']; ?>"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="100%" height="100%" fill="#fff"/>

                        <?php if ($isDevMode): ?>
                        <!-- Margin guides -->
                        <line x1="<?php echo $originX; ?>" y1="0" x2="<?php echo $originX; ?>" y2="<?php echo $svgHeightInches * $scale; ?>" stroke="#eee" stroke-dasharray="4,4"/>
                        <line x1="0" y1="<?php echo $originY; ?>" x2="<?php echo $svgWidthInches * $scale; ?>" y2="<?php echo $originY; ?>" stroke="#eee" stroke-dasharray="4,4"/>
                        <?php endif; ?>

                        <!-- ============================================= -->
                        <!-- PATTERN ELEMENTS -->
                        <!-- ============================================= -->

                        <!-- Armhole curve -->
                        <path d="<?php echo $armholeSvgPath; ?>" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                        <!-- ============================================= -->
                        <!-- SNIP ICONS - Triangle markers at key cutting points -->
                        <!-- ============================================= -->

                        <!-- Snip #1: Front Armhole End (a8) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(1, 'sn1', 'n', 'a8', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #2: Shoulder point (a10) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(2, 'sn2', 'n', 'a10', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #3: Neck-Shoulder junction (a11) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(3, 'sn3', 'n', 'a11', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #4: Center Front Neck (a1) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(4, 'sn4', 'n', 'a1', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #5: Bust Line at Armhole (a7) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(5, 'sn5', 'n', 'a7', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #6: Center Front at Waist (a3) - Triangle points UP (270°) -->
                        <!-- Rotated 180° from 90° (down) to 270° (up), offset 0.5" down from node -->
                        <?php echo snipIcon(6, 'sn6', 'n', 'a3', 270, 0.225, 0, 0.5); ?>

                        <!-- Snip #7: Side Seam at Waist (a5) - Triangle points UP (270°) -->
                        <!-- Rotated 180° from 90° (down) to 270° (up), offset 0.5" down from node -->
                        <?php echo snipIcon(7, 'sn7', 'n', 'a5', 270, 0.225, 0, 0.5); ?>

                        <!-- Snip #8: Side Seam - Triangle points LEFT (180°) -->
                        <!-- X position from r3, Y position from a5 (offset -0.5" from r3) -->
                        <?php echo snipIcon(8, 'sn8', 'n', 'r3', 180, 0.225, 0, -0.5); ?>

                        <!-- Snip #9: Bust Line - Triangle points LEFT (180°) -->
                        <!-- X position from r3 (a7.x + 1.5"), Y position from a7 -->
                        <?php echo snipIcon(9, 'sn9', 'n', 'a7', 180, 0.225, 1.5, 0); ?>

                        <!-- Snip #10: Shoulder - Triangle points LEFT (180°) -->
                        <!-- X position from r7 (a10.x + 0.5"), Y position from a10 -->
                        <?php echo snipIcon(10, 'sn10', 'n', 'a10', 180, 0.225, 0.5, 0); ?>

                        <!-- Snip #11: Neck-Shoulder junction left side (a11) - Triangle points RIGHT (0°) -->
                        <!-- Tip moved 0.5" left of a11, pointing right (opposite to sn10 which points left) -->
                        <?php echo snipIcon(11, 'sn11', 'n', 'a11', 0, 0.225, -0.5, 0); ?>

                        <!-- Shoulder line: a11 to a10 (anti-clockwise: top near neck to shoulder) -->
                        <line x1="<?php echo n('a11','x'); ?>" y1="<?php echo n('a11','y'); ?>"
                              x2="<?php echo n('a10','x'); ?>" y2="<?php echo n('a10','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Front neck curve: a1 to a11 passing through a111 -->
                        <!-- Two quadratic segments: a1->a111 (curves outward) and a111->a11 -->
                        <path d="M <?php echo n('a1','x'); ?>,<?php echo n('a1','y'); ?> Q <?php echo n('a111','x'); ?>,<?php echo n('a1','y'); ?> <?php echo n('a111','x'); ?>,<?php echo n('a111','y'); ?> Q <?php echo n('a111','x'); ?>,<?php echo n('a11','y'); ?> <?php echo n('a11','x'); ?>,<?php echo n('a11','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                        <!-- Center front line: a1 to a3 direct (removed a2 intermediate point) -->
                        <line x1="<?php echo n('a1','x'); ?>" y1="<?php echo n('a1','y'); ?>"
                              x2="<?php echo n('a3','x'); ?>" y2="<?php echo n('a3','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Waist line: quadratic curve a3 → a4, straight line a4 → a5 -->
                        <!-- Control point calculated in Section 2 with constant bulge ratio (15% of a3-a4 distance) -->
                        <path d="M <?php echo n('a4','x'); ?>,<?php echo n('a4','y'); ?>
                                 Q <?php echo $q_ctrl_left_x; ?>,<?php echo $q_ctrl_left_y; ?>
                                   <?php echo n('a3','x'); ?>,<?php echo n('a3','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <line x1="<?php echo n('a4','x'); ?>" y1="<?php echo n('a4','y'); ?>"
                              x2="<?php echo n('a5','x'); ?>" y2="<?php echo n('a5','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- ============================================= -->
                        <!-- TUCK LINES (Both Modes) -->
                        <!-- ============================================= -->
                        <!-- All tucks: Gray stitching lines + Dotted center fold line -->

                        <!-- BOTTOM TUCK: b2 → b3 → b4 -->
                        <!-- Stitching lines (gray solid) -->
                        <line x1="<?php echo n('b2','x'); ?>" y1="<?php echo n('b2','y'); ?>"
                              x2="<?php echo n('b3','x'); ?>" y2="<?php echo n('b3','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <line x1="<?php echo n('b3','x'); ?>" y1="<?php echo n('b3','y'); ?>"
                              x2="<?php echo n('b4','x'); ?>" y2="<?php echo n('b4','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <!-- Center fold line (dotted) - from b3 back to center point between b2 and b4 -->
                        <?php
                        $b_center_x = (n('b2','x') + n('b4','x')) / 2;  // Midpoint x between b2 and b4
                        $b_center_y = n('b2','y');  // Same y as b2 and b4 (waist line)
                        ?>
                        <line x1="<?php echo n('b3','x'); ?>" y1="<?php echo n('b3','y'); ?>"
                              x2="<?php echo $b_center_x; ?>" y2="<?php echo $b_center_y; ?>"
                              stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                        <!-- SIDE TUCK LEFT: c1 → c2 → c3 (apex side) -->
                        <!-- Stitching lines (gray solid) -->
                        <line x1="<?php echo n('c1','x'); ?>" y1="<?php echo n('c1','y'); ?>"
                              x2="<?php echo n('c2','x'); ?>" y2="<?php echo n('c2','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <line x1="<?php echo n('c2','x'); ?>" y1="<?php echo n('c2','y'); ?>"
                              x2="<?php echo n('c3','x'); ?>" y2="<?php echo n('c3','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <!-- Center fold line (dotted) - from c2 back to center point between c1 and c3 -->
                        <?php
                        $c_center_x = n('c1','x');  // Same x as c1 and c3 (left edge)
                        $c_center_y = (n('c1','y') + n('c3','y')) / 2;  // Midpoint between c1 and c3
                        ?>
                        <line x1="<?php echo n('c2','x'); ?>" y1="<?php echo n('c2','y'); ?>"
                              x2="<?php echo $c_center_x; ?>" y2="<?php echo $c_center_y; ?>"
                              stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                        <!-- SIDE TUCK RIGHT: d1 → d2 → d3 (bust side) -->
                        <!-- Stitching lines (gray solid) -->
                        <line x1="<?php echo n('d1','x'); ?>" y1="<?php echo n('d1','y'); ?>"
                              x2="<?php echo n('d2','x'); ?>" y2="<?php echo n('d2','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <line x1="<?php echo n('d2','x'); ?>" y1="<?php echo n('d2','y'); ?>"
                              x2="<?php echo n('d3','x'); ?>" y2="<?php echo n('d3','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <!-- Center fold line (dotted) - from d2 back to center point between d1 and d3 -->
                        <?php
                        $d_center_x = n('d1','x');  // Same x as d1 and d3 (right edge at qBust)
                        $d_center_y = (n('d1','y') + n('d3','y')) / 2;  // Midpoint between d1 and d3
                        ?>
                        <line x1="<?php echo n('d2','x'); ?>" y1="<?php echo n('d2','y'); ?>"
                              x2="<?php echo $d_center_x; ?>" y2="<?php echo $d_center_y; ?>"
                              stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                        <!-- ARMHOLE TUCK: e1 → e2 → e3 at a9 (armhole corner) -->
                        <!-- Stitching lines (gray solid) -->
                        <line x1="<?php echo n('e1','x'); ?>" y1="<?php echo n('e1','y'); ?>"
                              x2="<?php echo n('e2','x'); ?>" y2="<?php echo n('e2','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <line x1="<?php echo n('e2','x'); ?>" y1="<?php echo n('e2','y'); ?>"
                              x2="<?php echo n('e3','x'); ?>" y2="<?php echo n('e3','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <!-- Center fold line (dotted) -->
                        <line x1="<?php echo n('e2','x'); ?>" y1="<?php echo n('e2','y'); ?>"
                              x2="<?php echo n('a9','x'); ?>" y2="<?php echo n('a9','y'); ?>"
                              stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                        <!-- Side seam: a5 to a6 (anti-clockwise: waist up to bust point) -->
                        <line x1="<?php echo n('a5','x'); ?>" y1="<?php echo n('a5','y'); ?>"
                              x2="<?php echo n('a6','x'); ?>" y2="<?php echo n('a6','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Line: a6 to a7 (anti-clockwise: bust point up to intersection) -->
                        <line x1="<?php echo n('a6','x'); ?>" y1="<?php echo n('a6','y'); ?>"
                              x2="<?php echo n('a7','x'); ?>" y2="<?php echo n('a7','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Line: a7 to a8 (anti-clockwise: intersection to armhole end) -->
                        <line x1="<?php echo n('a7','x'); ?>" y1="<?php echo n('a7','y'); ?>"
                              x2="<?php echo n('a8','x'); ?>" y2="<?php echo n('a8','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- ============================================= -->
                        <!-- CUTTING LINE (Seam Allowance) - RED -->
                        <!-- 0.5" offset from pattern, following curves -->
                        <!-- ============================================= -->
                        <?php
                        $seamOffset = 0.5 * $scale;

                        // Offset waist curve control point (0.5" outward from original)
                        $cut_waist_ctrl_x = $q_ctrl_left_x;
                        $cut_waist_ctrl_y = $q_ctrl_left_y + $seamOffset;

                        // Offset neck curve control points (0.5" outward)
                        // Original neck curve: a1 -> a111 -> a11
                        // Control for a1->a111: at (a111.x, a1.y), offset left
                        $cut_neck_ctrl1_x = n('a111','x') - $seamOffset;
                        $cut_neck_ctrl1_y = n('a1','y');
                        // Midpoint of neck curve (offset of a111)
                        $cut_neck_mid_x = n('a111','x') - $seamOffset;
                        $cut_neck_mid_y = n('a111','y') - $seamOffset;
                        // Control for a111->a11: at (a111.x, a11.y), offset up
                        $cut_neck_ctrl2_x = n('a111','x') - $seamOffset;
                        $cut_neck_ctrl2_y = n('a11','y') - $seamOffset;

                        // Offset armhole curve - use the same control points offset outward
                        // The armhole goes: a10 -> a9 -> a8
                        // We need to offset the bezier curve outward by 0.5"
                        // Control point for r6 to r7 curve (offset from a9 - armhole corner)
                        // Closer to a9 for a more pronounced curve
                        $cut_armhole_ctrl_x = n('a9','x') + (0.25 * $scale);
                        $cut_armhole_ctrl_y = n('a9','y') - (0.25 * $scale);
                        ?>

                        <!-- Cutting line path - RED dashed line, 0.5" offset from pattern -->
                        <path d="M <?php echo n('r1','x'); ?>,<?php echo n('r1','y'); ?>
                                 Q <?php echo $cut_waist_ctrl_x; ?>,<?php echo $cut_waist_ctrl_y; ?>
                                   <?php echo n('r2','x'); ?>,<?php echo n('r2','y'); ?>
                                 L <?php echo n('r3','x'); ?>,<?php echo n('r3','y'); ?>
                                 L <?php echo n('r4','x'); ?>,<?php echo n('r4','y'); ?>
                                 L <?php echo n('r5','x'); ?>,<?php echo n('r5','y'); ?>
                                 L <?php echo n('r6','x'); ?>,<?php echo n('r6','y'); ?>
                                 Q <?php echo n('r71','x'); ?>,<?php echo n('r6','y'); ?>
                                   <?php echo n('r71','x'); ?>,<?php echo n('r71','y'); ?>
                                 L <?php echo n('r7','x'); ?>,<?php echo n('r7','y'); ?>
                                 L <?php echo n('r8','x'); ?>,<?php echo n('r8','y'); ?>
                                 L <?php echo n('r91','x'); ?>,<?php echo n('r91','y'); ?>
                                 Q <?php echo n('r91','x'); ?>,<?php echo n('r9','y'); ?>
                                   <?php echo n('r9','x'); ?>,<?php echo n('r9','y'); ?>
                                 L <?php echo n('r1','x'); ?>,<?php echo n('r1','y'); ?>"
                              stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

                        <!-- Scissors icon on front cutting line at r5 -->
                        <?php echo scissorsIcon(n('r5','x'), n('r5','y'), 90, 0.4); ?>

                        <?php if ($isDevMode): ?>
                        <!-- ============================================= -->
                        <!-- MEASUREMENT LABELS (in inches) - Dev Mode Only -->
                        <!-- ============================================= -->

                        <?php
                        // Calculate distances in inches
                        $dist_a1_a111 = $frontNeckDepth / 2;  // Vertical distance a1 to a111
                        $dist_a111_a11_x = (n('a11','x') - n('a111','x')) / $scale;  // Horizontal distance a111 to a11
                        $dist_a111_a11_y = $frontNeckDepth / 2;  // Vertical distance a111 to a11
                        $dist_a11_a10 = $shoulder;  // Shoulder line length (from DB)

                        // Calculate actual curve segment lengths (approximate for quadratic bezier)
                        // For a1 to a111: control point is at (a111.x, a1.y)
                        $seg1_approx = sqrt(pow(n('a111','x') - n('a1','x'), 2) + pow(n('a111','y') - n('a1','y'), 2)) / $scale;
                        // For a111 to a11: control point is at (a111.x, a11.y)
                        $seg2_approx = sqrt(pow(n('a11','x') - n('a111','x'), 2) + pow(n('a11','y') - n('a111','y'), 2)) / $scale;

                        // Diagonal distance from a1 to a11
                        $dist_a1_a11_diagonal = sqrt(pow(n('a11','x') - n('a1','x'), 2) + pow(n('a11','y') - n('a1','y'), 2)) / $scale;

                        // Distance from a3 to a5 via a4 (waist line)
                        $dist_a3_a4 = sqrt(pow(n('a4','x') - n('a3','x'), 2) + pow(n('a4','y') - n('a3','y'), 2)) / $scale;
                        $dist_a4_a5 = sqrt(pow(n('a5','x') - n('a4','x'), 2) + pow(n('a5','y') - n('a4','y'), 2)) / $scale;
                        $dist_a3_a4_a5 = $dist_a3_a4 + $dist_a4_a5;
                        ?>

                        <!-- Diagonal dotted line: a1 to a11 -->
                        <line x1="<?php echo n('a1','x'); ?>" y1="<?php echo n('a1','y'); ?>"
                              x2="<?php echo n('a11','x'); ?>" y2="<?php echo n('a11','y'); ?>"
                              stroke="#9CA3AF" stroke-width="1" stroke-dasharray="4,3"/>

                        <!-- Measurement: a1 to a11 diagonal -->
                        <text x="<?php echo (n('a1','x') + n('a11','x')) / 2 + 5; ?>" y="<?php echo (n('a1','y') + n('a11','y')) / 2; ?>"
                              font-size="10" fill="#6B7280">
                            <?php echo number_format($dist_a1_a11_diagonal, 2); ?>"
                        </text>


                        <!-- Measurement: a111 to a11 segment -->
                        <text x="<?php echo (n('a111','x') + n('a11','x')) / 2 - 10; ?>" y="<?php echo n('a11','y') - 8; ?>"
                              font-size="10" fill="#6B7280">
                            <?php echo number_format($seg2_approx, 2); ?>"
                        </text>

                        <!-- Measurement: Shoulder line a11 to a10 -->
                        <text x="<?php echo (n('a11','x') + n('a10','x')) / 2 - 10; ?>" y="<?php echo n('a10','y') + 15; ?>"
                              font-size="10" fill="#6B7280">
                            <?php echo number_format($dist_a11_a10, 2); ?>"
                        </text>

                        <!-- ============================================= -->
                        <!-- NODE MARKERS (rendered by JavaScript) -->
                        <!-- ============================================= -->
                        <g id="nodeMarkers"></g>
                        <?php endif; ?>

                        <!-- ============================================= -->
                        <!-- GRAINLINE -->
                        <!-- ============================================= -->
                        <?php
                        // Position grainline between b1 and sMid points
                        $grainX = (n('b1', 'x') + n('sMid', 'x')) / 2; // Midpoint between b1 and sMid horizontally
                        $grainY = (n('b1', 'y') + n('sMid', 'y')) / 2; // Midpoint between b1 and sMid vertically
                        $grainLength = 4; // 4 inches long
                        echo grainLine($grainX, $grainY, $grainLength, 'vertical');
                        ?>

                    </svg>
                    <?php
                    $frontSVG = ob_get_clean();
                    echo $frontSVG;
                    ?>
                </div>
            </div>
            <!-- END CELL 1x1: BLOUSE FRONT -->

            <!-- ============================================= -->
            <!-- CELL 1x2: BLOUSE PATTI -->
            <!-- ============================================= -->
            <div class="pattern-cell cell-1x2">
                <h3>2. Blouse Patti</h3>
                <div class="cell-info">
                    Length: <?php echo number_format($flength + 2, 2); ?>" | Width: 2.5"
                </div>

        <?php
        // =============================================================================
        // PATTI CALCULATIONS MOVED TO SECTION 2 (formerly lines 1458-1711)
        // =============================================================================
        // All Patti calculations are now in Section 2 above
        // This section only renders the SVG using those calculated values
        // =============================================================================
        ?>

        <!-- ============================================= -->
        <!-- PRESENTATION: Patti Pattern SVG -->
        <!-- ============================================= -->
        <div class="svg-container" style="border: 1px solid #ddd; background: white; display: inline-block;">
            <?php ob_start(); ?>
            <svg id="pattiSvg" width="<?php echo $pattiSvgWidth; ?>" height="<?php echo $pattiSvgHeight; ?>"
                 viewBox="<?php echo $pattiBounds['minX']; ?> <?php echo $pattiBounds['minY']; ?> <?php echo $pattiBounds['width']; ?> <?php echo $pattiBounds['height']; ?>"
                 xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#fff"/>

                <!-- Patti Rectangle (main pattern) -->
                <rect x="<?php echo pn('p1','x'); ?>" y="<?php echo pn('p1','y'); ?>"
                      width="<?php echo $pattiLength * $scale; ?>" height="<?php echo $pattiWidth * $scale; ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                <!-- Center Fold Line (dashed) -->
                <line x1="<?php echo pn('pf1','x'); ?>" y1="<?php echo pn('pf1','y'); ?>"
                      x2="<?php echo pn('pf2','x'); ?>" y2="<?php echo pn('pf2','y'); ?>"
                      stroke="#6366F1" stroke-width="1" stroke-dasharray="8,4"/>

                <!-- Fold Line Label -->
                <text x="<?php echo (pn('pf1','x') + pn('pf2','x')) / 2; ?>" y="<?php echo pn('pf1','y') - 5; ?>"
                      font-size="10" fill="#6366F1" text-anchor="middle">FOLD LINE</text>

                <!-- Cutting Line (seam allowance) - RED dashed -->
                <rect x="<?php echo pn('pc1','x'); ?>" y="<?php echo pn('pc1','y'); ?>"
                      width="<?php echo ($pattiLength + $pattiSeamAllowance * 2) * $scale; ?>"
                      height="<?php echo ($pattiWidth + $pattiSeamAllowance * 2) * $scale; ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

                <!-- ============================================= -->
                <!-- SNIP ICONS: PATTI 1 (Ref #50-53) -->
                <!-- ============================================= -->

                <!-- Snip #50: Patti top left corner (p1) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(50, 'sn50', 'pn', 'p1', 90, 0.225, 0, -0.5); ?>

                <!-- Snip #51: Patti top right corner (p2) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(51, 'sn51', 'pn', 'p2', 90, 0.225, 0, -0.5); ?>

                <!-- Snip #52: Patti bottom left corner (p4) - Triangle points UP (270°) -->
                <?php echo snipIcon(52, 'sn52', 'pn', 'p4', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #53: Patti bottom right corner (p3) - Triangle points UP (270°) -->
                <?php echo snipIcon(53, 'sn53', 'pn', 'p3', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #62: Patti left side at p1 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(62, 'sn62', 'pn', 'p1', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #63: Patti left side at p4 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(63, 'sn63', 'pn', 'p4', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #64: Patti right side at p2 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(64, 'sn64', 'pn', 'p2', 180, 0.225, 0.5, 0); ?>

                <!-- Snip #65: Patti right side at p3 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(65, 'sn65', 'pn', 'p3', 180, 0.225, 0.5, 0); ?>

                <!-- Scissors icon on patti cutting line -->
                <!-- Scissors between pc2 and pc3 (bottom red line) -->
                <?php
                $scissors_pc2_pc3_x = (pn('pc2','x') + pn('pc3','x')) / 2;
                $scissors_pc2_pc3_y = (pn('pc2','y') + pn('pc3','y')) / 2;
                echo scissorsIcon($scissors_pc2_pc3_x, $scissors_pc2_pc3_y, 90, 0.4);
                ?>

                <?php if ($isDevMode): ?>
                <!-- Measurement Labels (DEV MODE ONLY) -->
                <!-- Length measurement (top) -->
                <line x1="<?php echo pn('p1','x'); ?>" y1="<?php echo pn('p1','y') - 10; ?>"
                      x2="<?php echo pn('p2','x'); ?>" y2="<?php echo pn('p2','y') - 10; ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <line x1="<?php echo pn('p1','x'); ?>" y1="<?php echo pn('p1','y') - 15; ?>"
                      x2="<?php echo pn('p1','x'); ?>" y2="<?php echo pn('p1','y') - 5; ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <line x1="<?php echo pn('p2','x'); ?>" y1="<?php echo pn('p2','y') - 15; ?>"
                      x2="<?php echo pn('p2','x'); ?>" y2="<?php echo pn('p2','y') - 5; ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <text x="<?php echo (pn('p1','x') + pn('p2','x')) / 2; ?>" y="<?php echo pn('p1','y') - 15; ?>"
                      font-size="10" fill="#6B7280" text-anchor="middle">
                    <?php echo number_format($pattiLength, 2); ?>"
                </text>

                <!-- Width measurement (left) -->
                <line x1="<?php echo pn('p1','x') - 10; ?>" y1="<?php echo pn('p1','y'); ?>"
                      x2="<?php echo pn('p4','x') - 10; ?>" y2="<?php echo pn('p4','y'); ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <line x1="<?php echo pn('p1','x') - 15; ?>" y1="<?php echo pn('p1','y'); ?>"
                      x2="<?php echo pn('p1','x') - 5; ?>" y2="<?php echo pn('p1','y'); ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <line x1="<?php echo pn('p4','x') - 15; ?>" y1="<?php echo pn('p4','y'); ?>"
                      x2="<?php echo pn('p4','x') - 5; ?>" y2="<?php echo pn('p4','y'); ?>"
                      stroke="#9CA3AF" stroke-width="1"/>
                <text x="<?php echo pn('p1','x') - 18; ?>" y="<?php echo (pn('p1','y') + pn('p4','y')) / 2 + 3; ?>"
                      font-size="10" fill="#6B7280" text-anchor="end">
                    <?php echo number_format($pattiWidth, 2); ?>"
                </text>

                <!-- Node markers for patti 1 (p-prefix nodes only) (DEV MODE ONLY) -->
                <g id="pattiNodeMarkers">
                    <?php
                    $patti1Nodes = ['p1', 'p2', 'p3', 'p4', 'pf1', 'pf2', 'pc1', 'pc2', 'pc3', 'pc4'];
                    foreach ($patti1Nodes as $name):
                        $node = $pattiNodes[$name];
                        $nodeColor = '#10B981';
                        if (isset($node['color'])) {
                            if ($node['color'] === 'gray') $nodeColor = '#9CA3AF';
                            if ($node['color'] === 'red') $nodeColor = '#DC2626';
                        }
                    ?>
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                            fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
                    <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
                          font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7">
                        <?php echo $name; ?>
                    </text>
                    <?php endforeach; ?>
                </g>
                <?php endif; ?>

                <!-- ========== SECOND PATTI (5 nodes) ========== -->

                <!-- Patti 2 outline (3 sides only - top edge is the curve) -->
                <!-- Width: a3 to a5 = qBust = <?php echo number_format($qBust, 2); ?>" (bust/4 = <?php echo $bust; ?>/4) -->
                <!-- Height: back length - front length = <?php echo number_format($blength, 2); ?>" - <?php echo number_format($flength, 2); ?>" = <?php echo number_format($patti2Height, 2); ?>" -->
                <!-- Left side: q1 to q4 -->
                <line x1="<?php echo pn('q1','x'); ?>" y1="<?php echo pn('q1','y'); ?>"
                      x2="<?php echo pn('q4','x'); ?>" y2="<?php echo pn('q4','y'); ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- Bottom: q4 to q5 -->
                <line x1="<?php echo pn('q4','x'); ?>" y1="<?php echo pn('q4','y'); ?>"
                      x2="<?php echo pn('q5','x'); ?>" y2="<?php echo pn('q5','y'); ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- Right side: q5 to q3 -->
                <line x1="<?php echo pn('q5','x'); ?>" y1="<?php echo pn('q5','y'); ?>"
                      x2="<?php echo pn('q3','x'); ?>" y2="<?php echo pn('q3','y'); ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                <!-- Waist curve q1-q2-q3 (mirrors a3-a4-a5 exactly) -->
                <?php
                // Quadratic bezier from q2 to q1 (same as a4 to a3)
                // Control point: midpoint x between q1 and q2, curve depth = 25% of a4-a3 y-offset
                $curveDepth = abs($a4_y_offset) * 0.25;  // Dynamic curve depth based on a4-a3 distance
                $q_ctrl_q1q2_x = (pn('q1','x') + pn('q2','x')) / 2;
                $q_ctrl_q1q2_y = pn('q2','y') + $curveDepth;
                ?>
                <!-- Quadratic curve: q2 → q1 (curved) -->
                <path d="M <?php echo pn('q2','x'); ?>,<?php echo pn('q2','y'); ?>
                         Q <?php echo $q_ctrl_q1q2_x; ?>,<?php echo $q_ctrl_q1q2_y; ?>
                           <?php echo pn('q1','x'); ?>,<?php echo pn('q1','y'); ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <!-- Straight line: q2 → q3 -->
                <line x1="<?php echo pn('q2','x'); ?>" y1="<?php echo pn('q2','y'); ?>"
                      x2="<?php echo pn('q3','x'); ?>" y2="<?php echo pn('q3','y'); ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                <!-- Vertical dotted line: Distance from left edge = a8(x) - a3(x) -->
                <!-- Height matches q3 to q5 (top-right to bottom-right) -->
                <line x1="<?php echo $patti2VerticalLineX; ?>" y1="<?php echo pn('q3','y'); ?>"
                      x2="<?php echo $patti2VerticalLineX; ?>" y2="<?php echo pn('q5','y'); ?>"
                      stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                <!-- Red cutting line with 0.5" seam allowance (using qc1-qc6 nodes) -->
                <!-- Left side: qc1 to qc4 -->
                <line x1="<?php echo pn('qc1','x'); ?>" y1="<?php echo pn('qc1','y'); ?>"
                      x2="<?php echo pn('qc4','x'); ?>" y2="<?php echo pn('qc4','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>
                <!-- Bottom: qc4 to qc5 -->
                <line x1="<?php echo pn('qc4','x'); ?>" y1="<?php echo pn('qc4','y'); ?>"
                      x2="<?php echo pn('qc5','x'); ?>" y2="<?php echo pn('qc5','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>
                <!-- Right side: qc5 to qc3 -->
                <line x1="<?php echo pn('qc5','x'); ?>" y1="<?php echo pn('qc5','y'); ?>"
                      x2="<?php echo pn('qc3','x'); ?>" y2="<?php echo pn('qc3','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>
                <!-- qc1 to qc6: straight line -->
                <line x1="<?php echo pn('qc1','x'); ?>" y1="<?php echo pn('qc1','y'); ?>"
                      x2="<?php echo pn('qc6','x'); ?>" y2="<?php echo pn('qc6','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>
                <?php
                // qc6 to qc2: curved line (same curvature as q1-q2: control point at midpoint x, dynamic curve depth)
                $qc6_qc2_ctrl_x = (pn('qc6','x') + pn('qc2','x')) / 2;
                $qc6_qc2_ctrl_y = pn('qc2','y') + $curveDepth;  // Uses same $curveDepth as q1-q2
                ?>
                <!-- qc6 to qc2: curve -->
                <path d="M <?php echo pn('qc6','x'); ?>,<?php echo pn('qc6','y'); ?>
                         Q <?php echo $qc6_qc2_ctrl_x; ?>,<?php echo $qc6_qc2_ctrl_y; ?>
                           <?php echo pn('qc2','x'); ?>,<?php echo pn('qc2','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>
                <!-- Top straight: qc2 to qc3 -->
                <line x1="<?php echo pn('qc2','x'); ?>" y1="<?php echo pn('qc2','y'); ?>"
                      x2="<?php echo pn('qc3','x'); ?>" y2="<?php echo pn('qc3','y'); ?>"
                      stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>

                <!-- ============================================= -->
                <!-- SNIP ICONS: PATTI 2 (Ref #54-58) -->
                <!-- ============================================= -->

                <!-- Snip #54: Patti2 top left corner (q1) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(54, 'sn54', 'pn', 'q1', 90, 0.225, 0, -0.6); ?>

                <!-- Snip #55: Patti2 top right corner (q3) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(55, 'sn55', 'pn', 'q3', 90, 0.225, 0, -0.6); ?>

                <!-- Snip #56: Patti2 bottom left corner (q4) - Triangle points UP (270°) -->
                <?php echo snipIcon(56, 'sn56', 'pn', 'q4', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #57: Patti2 bottom right corner (q5) - Triangle points UP (270°) -->
                <?php echo snipIcon(57, 'sn57', 'pn', 'q5', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #58: Patti2 left side at q1 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(58, 'sn58', 'pn', 'q1', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #59: Patti2 left side at q4 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(59, 'sn59', 'pn', 'q4', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #60: Patti2 right side at q3 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(60, 'sn60', 'pn', 'q3', 180, 0.225, 0.5, 0); ?>

                <!-- Snip #61: Patti2 right side at q5 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(61, 'sn61', 'pn', 'q5', 180, 0.225, 0.5, 0); ?>

                <!-- Scissors icon on patti 2 cutting line -->
                <!-- Scissors between qc3 and qc5 (right side red line) -->
                <?php
                $scissors_qc3_qc5_x = (pn('qc3','x') + pn('qc5','x')) / 2;
                $scissors_qc3_qc5_y = (pn('qc3','y') + pn('qc5','y')) / 2;
                echo scissorsIcon($scissors_qc3_qc5_x, $scissors_qc3_qc5_y, 90, 0.4);
                ?>

                <?php if ($isDevMode): ?>
                <!-- Patti 2 Node markers (q1, q2, q3 top; q4, q5 bottom) (DEV MODE ONLY) -->
                <g id="patti2NodeMarkers">
                    <?php
                    $patti2Nodes = ['q1', 'q2', 'q3', 'q4', 'q5'];
                    foreach ($patti2Nodes as $name):
                        $node = $pattiNodes[$name];
                        $nodeColor = '#10B981';
                    ?>
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                            fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
                    <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
                          font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7">
                        <?php echo $name; ?>
                    </text>
                    <?php endforeach; ?>
                </g>

                <!-- Cutting line node markers (qc1-qc6, red) (DEV MODE ONLY) -->
                <g id="patti2CuttingNodeMarkers">
                    <?php
                    $patti2CuttingNodes = ['qc1', 'qc2', 'qc3', 'qc4', 'qc5', 'qc6'];
                    foreach ($patti2CuttingNodes as $name):
                        $node = $pattiNodes[$name];
                        $nodeColor = '#DC2626';  // red
                    ?>
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                            fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
                    <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
                          font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7">
                        <?php echo $name; ?>
                    </text>
                    <?php endforeach; ?>
                </g>
                <?php endif; ?>

                <!-- ============================================= -->
                <!-- GRAINLINE (Patti 1 and Patti 2) -->
                <!-- ============================================= -->
                <?php
                // Patti 1 grainline (horizontal, centered, moved down 0.5")
                $grainX1 = (pn('p1', 'x') + pn('p2', 'x')) / 2; // Centered horizontally
                $grainY1 = ((pn('p1', 'y') + pn('p4', 'y')) / 2) + (0.5 * $scale); // Centered vertically + 0.5" down
                $grainLength1 = 3; // 3 inches long
                echo grainLine($grainX1, $grainY1, $grainLength1, 'horizontal');

                // Patti 2 grainline (horizontal, centered, moved down 1.0")
                $grainX2 = (pn('q1', 'x') + pn('q3', 'x')) / 2; // Centered horizontally
                $grainY2 = ((pn('q1', 'y') + pn('q4', 'y')) / 2) + (1.0 * $scale); // Centered vertically + 1.0" down
                $grainLength2 = 3; // 3 inches long
                echo grainLine($grainX2, $grainY2, $grainLength2, 'horizontal');
                ?>

            </svg>
            <?php
            $pattiSVG = ob_get_clean();
            echo $pattiSVG;
            ?>
        </div>

                <!-- Patti Specifications (compact) -->
                <div style="margin-top: 10px; padding: 8px; background: #F8FAFC; border-radius: 4px; font-size: 11px;">
                    <span style="color: #64748B;">Cut Size:</span>
                    <span style="font-weight: 600; color: #DC2626;">
                        <?php echo number_format($pattiLength + $pattiSeamAllowance * 2, 2); ?>" × <?php echo number_format($pattiWidth + $pattiSeamAllowance * 2, 2); ?>"
                    </span>
                    <span style="color: #94A3B8; margin-left: 10px;">(includes <?php echo number_format($pattiSeamAllowance, 1); ?>" seam)</span>
                </div>
            </div>
            <!-- END CELL 1x2: BLOUSE PATTI -->

            <!-- ============================================= -->
            <!-- CELL 2x1: BLOUSE BACK (Independent nodes: z-prefix) -->
            <!-- ============================================= -->
            <div class="pattern-cell cell-2x1">
                <h3>3. Blouse Back</h3>
                <div class="cell-info">
                    Armhole: <?php echo $armhole; ?>" (target: <?php echo number_format($targetArmhole, 2); ?>") |
                    Curve: <?php echo number_format($curveLength, 2); ?>"
                </div>
                <!-- ============================================= -->
                <!-- PRESENTATION: Back Pattern SVG -->
                <!-- ============================================= -->
                <div class="svg-container">
                    <?php ob_start(); ?>
                    <svg id="patternSvgBack" width="<?php echo $svgWidth; ?>" height="<?php echo $svgHeight; ?>"
                         viewBox="<?php echo $backBounds['minX']; ?> <?php echo $backBounds['minY']; ?> <?php echo $backBounds['width']; ?> <?php echo $backBounds['height']; ?>"
                         xmlns="http://www.w3.org/2000/svg">
                        <rect width="100%" height="100%" fill="#fff"/>

                        <!-- ============================================= -->
                        <!-- PATTERN ELEMENTS (BACK - uses bn() for z-nodes) -->
                        <!-- ============================================= -->

                        <!-- Back Armhole curve (z6 → z71 → z8) -->
                        <path d="<?php echo $backArmholePath; ?>" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                        <!-- Shoulder line: z9 to z8 -->
                        <line x1="<?php echo bn('z9','x'); ?>" y1="<?php echo bn('z9','y'); ?>"
                              x2="<?php echo bn('z8','x'); ?>" y2="<?php echo bn('z8','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Back neck curve: z1 to z9 passing through z91 -->
                        <path d="M <?php echo bn('z1','x'); ?>,<?php echo bn('z1','y'); ?> Q <?php echo bn('z91','x'); ?>,<?php echo bn('z1','y'); ?> <?php echo bn('z91','x'); ?>,<?php echo bn('z91','y'); ?> Q <?php echo bn('z91','x'); ?>,<?php echo bn('z9','y'); ?> <?php echo bn('z9','x'); ?>,<?php echo bn('z9','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                        <!-- Center back line: z1 to z2 -->
                        <line x1="<?php echo bn('z1','x'); ?>" y1="<?php echo bn('z1','y'); ?>"
                              x2="<?php echo bn('z2','x'); ?>" y2="<?php echo bn('z2','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Waist line: straight line z2 → z3 -->
                        <line x1="<?php echo bn('z2','x'); ?>" y1="<?php echo bn('z2','y'); ?>"
                              x2="<?php echo bn('z3','x'); ?>" y2="<?php echo bn('z3','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- ============================================= -->
                        <!-- TUCK LINES (Both Modes) -->
                        <!-- ============================================= -->
                        <!-- All tucks: Gray stitching lines + Dotted center fold line -->

                        <!-- BOTTOM TUCK: zb2 → zb3 → zb4 -->
                        <!-- Stitching lines (gray solid) -->
                        <line x1="<?php echo bn('zb2','x'); ?>" y1="<?php echo bn('zb2','y'); ?>"
                              x2="<?php echo bn('zb3','x'); ?>" y2="<?php echo bn('zb3','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <line x1="<?php echo bn('zb3','x'); ?>" y1="<?php echo bn('zb3','y'); ?>"
                              x2="<?php echo bn('zb4','x'); ?>" y2="<?php echo bn('zb4','y'); ?>"
                              stroke="#808080" stroke-width="0.5"/>
                        <!-- Center fold line (dotted) - from zb3 back to center point between zb2 and zb4 -->
                        <?php
                        $zb_center_x = (bn('zb2','x') + bn('zb4','x')) / 2;  // Midpoint x between zb2 and zb4
                        $zb_center_y = bn('zb2','y');  // Same y as zb2 and zb4 (waist line)
                        ?>
                        <line x1="<?php echo bn('zb3','x'); ?>" y1="<?php echo bn('zb3','y'); ?>"
                              x2="<?php echo $zb_center_x; ?>" y2="<?php echo $zb_center_y; ?>"
                              stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

                        <!-- Side seam: z3 to z4 -->
                        <line x1="<?php echo bn('z3','x'); ?>" y1="<?php echo bn('z3','y'); ?>"
                              x2="<?php echo bn('z4','x'); ?>" y2="<?php echo bn('z4','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Line: z4 to z5 -->
                        <line x1="<?php echo bn('z4','x'); ?>" y1="<?php echo bn('z4','y'); ?>"
                              x2="<?php echo bn('z5','x'); ?>" y2="<?php echo bn('z5','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Line: z5 to z6 -->
                        <line x1="<?php echo bn('z5','x'); ?>" y1="<?php echo bn('z5','y'); ?>"
                              x2="<?php echo bn('z6','x'); ?>" y2="<?php echo bn('z6','y'); ?>"
                              stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- ============================================= -->
                        <!-- SNIP ICONS: BACK PATTERN (Ref #25-37) -->
                        <!-- ============================================= -->

                        <!-- Snip #25: Back Armhole End (z6) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(25, 'sn25', 'bn', 'z6', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #26: Back Shoulder point (z8) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(26, 'sn26', 'bn', 'z8', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #27: Back Neck-Shoulder junction (z9) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(27, 'sn27', 'bn', 'z9', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #28: Center Back Neck (z1) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(28, 'sn28', 'bn', 'z1', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #29: Back Bust Line at Armhole (z5) - Triangle points DOWN (90°) -->
                        <?php echo snipIcon(29, 'sn29', 'bn', 'z5', 90, 0.225, 0, -0.5); ?>

                        <!-- Snip #30: Center Back at Waist (z2) - Triangle points UP (270°) -->
                        <?php echo snipIcon(30, 'sn30', 'bn', 'z2', 270, 0.225, 0, 0.5); ?>

                        <!-- Snip #31: Back Side Seam at Waist (z3) - Triangle points UP (270°) -->
                        <?php echo snipIcon(31, 'sn31', 'bn', 'z3', 270, 0.225, 0, 0.5); ?>

                        <!-- Snip #32: Back Side Seam (z3) - Triangle points LEFT (180°) -->
                        <?php echo snipIcon(32, 'sn32', 'bn', 'z3', 180, 0.225, 1.5, 0); ?>

                        <!-- Snip #33: Back Bust Line (z5) - Triangle points LEFT (180°) -->
                        <?php echo snipIcon(33, 'sn33', 'bn', 'z5', 180, 0.225, 1.5, 0); ?>

                        <!-- Snip #34: Back Shoulder (z8) - Triangle points LEFT (180°) -->
                        <?php echo snipIcon(34, 'sn34', 'bn', 'z8', 180, 0.225, 0.5, 0); ?>

                        <!-- Snip #35: Back Neck-Shoulder left side (z9) - Triangle points RIGHT (0°) -->
                        <?php echo snipIcon(35, 'sn35', 'bn', 'z9', 0, 0.225, -0.5, 0); ?>

                        <!-- ============================================= -->
                        <!-- CUTTING LINE (Seam Allowance) - RED -->
                        <!-- ============================================= -->

                        <!-- Cutting line path - RED dashed line (with new armhole curved path) -->
                        <?php
                        // NOTE: $backRedCuttingPath is now calculated in SECTION 2 (around line 943)
                        // No calculation needed here - just using the pre-calculated path
                        ?>
                        <path d="<?php echo $backRedCuttingPath; ?>"
                              stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

                        <!-- Scissors icon on cutting line -->
                        <!-- Scissors at zr5 (right side straight line) - back pattern -->
                        <?php echo scissorsIcon(bn('zr5','x'), bn('zr5','y'), 90, 0.4); ?>

                        <?php if ($isDevMode): ?>
                        <!-- ============================================= -->
                        <!-- MEASUREMENT LABELS (Dev Mode Only) -->
                        <!-- ============================================= -->

                        <!-- Diagonal dotted line: z1 to z9 -->
                        <line x1="<?php echo bn('z1','x'); ?>" y1="<?php echo bn('z1','y'); ?>"
                              x2="<?php echo bn('z9','x'); ?>" y2="<?php echo bn('z9','y'); ?>"
                              stroke="#9CA3AF" stroke-width="1" stroke-dasharray="4,3"/>

                        <!-- Measurement: z1 to z9 diagonal -->
                        <?php
                        $back_dist_z1_z9_diagonal = sqrt(pow(bn('z9','x') - bn('z1','x'), 2) + pow(bn('z9','y') - bn('z1','y'), 2)) / $scale;
                        ?>
                        <text x="<?php echo (bn('z1','x') + bn('z9','x')) / 2 + 5; ?>" y="<?php echo (bn('z1','y') + bn('z9','y')) / 2; ?>"
                              font-size="10" fill="#6B7280">
                            <?php echo number_format($back_dist_z1_z9_diagonal, 2); ?>"
                        </text>

                        <!-- Measurement: Shoulder line z9 to z8 -->
                        <text x="<?php echo (bn('z9','x') + bn('z8','x')) / 2 - 10; ?>" y="<?php echo bn('z8','y') + 15; ?>"
                              font-size="10" fill="#6B7280">
                            <?php echo number_format($shoulder, 2); ?>"
                        </text>

                        <!-- Measurement: Back Armhole Path z8→z71→z6 -->
                        <text x="<?php echo bn('z71','x') + 15; ?>" y="<?php echo (bn('z8','y') + bn('z6','y')) / 2; ?>"
                              font-size="7" fill="#EF4444" font-weight="bold">
                            Armhole: <?php echo number_format($actualLengthInches, 2); ?>" (Target: <?php echo number_format($targetLengthInches, 2); ?>")
                        </text>
                        <?php endif; ?>

                        <?php if ($isDevMode): ?>
                        <!-- ============================================= -->
                        <!-- NODE MARKERS (Back nodes) -->
                        <!-- ============================================= -->
                        <?php foreach ($backNodes as $name => $node): ?>
                        <?php
                            $nodeColor = '#10B981';  // Default green
                            if (isset($node['color'])) {
                                if ($node['color'] === 'gray') $nodeColor = '#9CA3AF';
                                elseif ($node['color'] === 'red') $nodeColor = '#DC2626';
                            }
                        ?>
                        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                                fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
                        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
                              font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7">
                            <?php echo $name; ?>
                        </text>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- ============================================= -->
                        <!-- GRAINLINE -->
                        <!-- ============================================= -->
                        <?php
                        // Position grainline between zb3 and zMid points
                        $grainX = (bn('zb3', 'x') + bn('zMid', 'x')) / 2; // Midpoint between zb3 and zMid horizontally
                        $grainY = (bn('zb3', 'y') + bn('zMid', 'y')) / 2; // Midpoint between zb3 and zMid vertically
                        $grainLength = 4; // 4 inches long
                        echo grainLine($grainX, $grainY, $grainLength, 'vertical');
                        ?>

                    </svg>
                    <?php
                    $backSVG = ob_get_clean();
                    echo $backSVG;
                    ?>
                </div>
            </div>
            <!-- END CELL 2x1: BLOUSE BACK -->

            <!-- ============================================= -->
            <!-- CELL 2x2: SLEEVE PATTERN -->
            <!-- ============================================= -->
            <div class="pattern-cell cell-2x2">
                <h3>4. Sleeve</h3>
                <?php if ($isDevMode): ?>
                <div class="cell-info">
                    Sleeve Length: <?php echo number_format($slength, 2); ?>" |
                    Sleeve Around: <?php echo number_format($saround, 2); ?>" |
                    Sleeve Open: <?php echo number_format($sopen, 2); ?>" |
                    Armhole: <?php echo number_format($armhole, 2); ?>"
                </div>
                <?php endif; ?>


        <?php
        // =============================================================================
        // SLEEVE CALCULATIONS MOVED TO SECTION 2 (formerly lines 2130-2935)
        // =============================================================================
        // All Sleeve calculations are now in Section 2 above
        // This section only renders the SVG using those calculated values
        // =============================================================================
        ?>


        <!-- ============================================= -->
        <!-- PRESENTATION: Sleeve Pattern SVG -->
        <!-- ============================================= -->
        <div class="svg-container" style="border: <?php echo $isDevMode ? '1px solid #ddd' : 'none'; ?>; background: white; display: inline-block;">
            <?php ob_start(); ?>
            <svg id="sleeveSvg"
                 width="<?php echo $sleeveSvgWidth; ?>"
                 height="<?php echo $sleeveSvgHeight; ?>"
                 viewBox="<?php echo $sleeveBounds['minX']; ?> <?php echo $sleeveBounds['minY']; ?> <?php echo $sleeveBounds['width']; ?> <?php echo $sleeveBounds['height']; ?>"
                 xmlns="http://www.w3.org/2000/svg">
                <rect width="100%" height="100%" fill="#fff"/>

                <!-- RED CUTTING LINE (with seam allowance) -->
                <path d="<?php echo $sleeveRed; ?>"
                      stroke="#DC2626" stroke-width="0.5" fill="none" stroke-dasharray="6,3"/>

                <!-- ============================================= -->
                <!-- SNIP ICONS: SLEEVE PATTERN (Ref #75-84) -->
                <!-- ============================================= -->

                <!-- Snip #75: Sleeve cap top (s1) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(75, 'sn75', 'sn', 's1', 90, 0.225, 0, -0.5); ?>

                <!-- Snip #76: Left shoulder (s2) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(76, 'sn76', 'sn', 's2', 90, 0.225, 0, -0.5); ?>

                <!-- Snip #77: Right shoulder (s3) - Triangle points DOWN (90°) -->
                <?php echo snipIcon(77, 'sn77', 'sn', 's3', 90, 0.225, 0, -0.5); ?>

                <!-- Snip #78: Left wrist (s4) - Triangle points UP (270°) -->
                <?php echo snipIcon(78, 'sn78', 'sn', 's4', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #79: Right wrist (s5) - Triangle points UP (270°) -->
                <?php echo snipIcon(79, 'sn79', 'sn', 's5', 270, 0.225, 0, 0.5); ?>

                <!-- Snip #80: Left side at s2 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(80, 'sn80', 'sn', 's2', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #81: Left side at s4 - Triangle points RIGHT (0°) -->
                <?php echo snipIcon(81, 'sn81', 'sn', 's4', 0, 0.225, -0.5, 0); ?>

                <!-- Snip #82: Right side at s3 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(82, 'sn82', 'sn', 's3', 180, 0.225, 0.5, 0); ?>

                <!-- Snip #83: Right side at s5 - Triangle points LEFT (180°) -->
                <?php echo snipIcon(83, 'sn83', 'sn', 's5', 180, 0.225, 0.5, 0); ?>

                <!-- Scissors icon on sleeve cutting line at sc1 -->
                <?php echo scissorsIcon(sn('sc1','x'), sn('sc1','y'), 0, 0.4); ?>

                <!-- MAIN OUTLINE (black with rounded top) -->
                <path d="<?php echo $sleeveBlack; ?>"
                      stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

                <?php if ($isDevMode): ?>
                <!-- STRAIGHT DOTTED LINE: s2 -> s1 -> s3 (DEV MODE ONLY) -->
                <line x1="<?php echo sn('s2', 'x'); ?>" y1="<?php echo sn('s2', 'y'); ?>"
                      x2="<?php echo sn('s1', 'x'); ?>" y2="<?php echo sn('s1', 'y'); ?>"
                      stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>
                <line x1="<?php echo sn('s1', 'x'); ?>" y1="<?php echo sn('s1', 'y'); ?>"
                      x2="<?php echo sn('s3', 'x'); ?>" y2="<?php echo sn('s3', 'y'); ?>"
                      stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>
                <?php endif; ?>

                <?php if ($isDevMode): ?>
                <!-- RED PARALLEL LINES: Above and below s2->s1->s3 (DEV MODE ONLY) -->
                <?php
                $redOffset = 0.5 * $scale; // 0.5 inch offset above and below

                // Calculate angle for s2->s1 line
                $angle_s2_s1 = atan2(sn('s1', 'y') - sn('s2', 'y'), sn('s1', 'x') - sn('s2', 'x'));
                $perpendicular_s2_s1 = $angle_s2_s1 + (M_PI / 2);

                // Calculate angle for s1->s3 line
                $angle_s1_s3 = atan2(sn('s3', 'y') - sn('s1', 'y'), sn('s3', 'x') - sn('s1', 'x'));
                $perpendicular_s1_s3 = $angle_s1_s3 + (M_PI / 2);

                // Red line above s2->s1
                $s2_above_x = sn('s2', 'x') + cos($perpendicular_s2_s1) * $redOffset;
                $s2_above_y = sn('s2', 'y') + sin($perpendicular_s2_s1) * $redOffset;
                $s1_above_left_x = sn('s1', 'x') + cos($perpendicular_s2_s1) * $redOffset;
                $s1_above_left_y = sn('s1', 'y') + sin($perpendicular_s2_s1) * $redOffset;

                // Red line below s2->s1
                $s2_below_x = sn('s2', 'x') - cos($perpendicular_s2_s1) * $redOffset;
                $s2_below_y = sn('s2', 'y') - sin($perpendicular_s2_s1) * $redOffset;
                $s1_below_left_x = sn('s1', 'x') - cos($perpendicular_s2_s1) * $redOffset;
                $s1_below_left_y = sn('s1', 'y') - sin($perpendicular_s2_s1) * $redOffset;

                // Red line above s1->s3
                $s1_above_right_x = sn('s1', 'x') + cos($perpendicular_s1_s3) * $redOffset;
                $s1_above_right_y = sn('s1', 'y') + sin($perpendicular_s1_s3) * $redOffset;
                $s3_above_x = sn('s3', 'x') + cos($perpendicular_s1_s3) * $redOffset;
                $s3_above_y = sn('s3', 'y') + sin($perpendicular_s1_s3) * $redOffset;

                // Red line below s1->s3
                $s1_below_right_x = sn('s1', 'x') - cos($perpendicular_s1_s3) * $redOffset;
                $s1_below_right_y = sn('s1', 'y') - sin($perpendicular_s1_s3) * $redOffset;
                $s3_below_x = sn('s3', 'x') - cos($perpendicular_s1_s3) * $redOffset;
                $s3_below_y = sn('s3', 'y') - sin($perpendicular_s1_s3) * $redOffset;
                ?>

                <!-- Red lines s2->s1 (above and below) -->
                <line x1="<?php echo $s2_above_x; ?>" y1="<?php echo $s2_above_y; ?>"
                      x2="<?php echo $s1_above_left_x; ?>" y2="<?php echo $s1_above_left_y; ?>"
                      stroke="#DC2626" stroke-width="0.5"/>
                <line x1="<?php echo $s2_below_x; ?>" y1="<?php echo $s2_below_y; ?>"
                      x2="<?php echo $s1_below_left_x; ?>" y2="<?php echo $s1_below_left_y; ?>"
                      stroke="#DC2626" stroke-width="0.5"/>

                <!-- Red lines s1->s3 (above and below) -->
                <line x1="<?php echo $s1_above_right_x; ?>" y1="<?php echo $s1_above_right_y; ?>"
                      x2="<?php echo $s3_above_x; ?>" y2="<?php echo $s3_above_y; ?>"
                      stroke="#DC2626" stroke-width="0.5"/>
                <line x1="<?php echo $s1_below_right_x; ?>" y1="<?php echo $s1_below_right_y; ?>"
                      x2="<?php echo $s3_below_x; ?>" y2="<?php echo $s3_below_y; ?>"
                      stroke="#DC2626" stroke-width="0.5"/>
                <?php endif; ?>

                <?php if ($isDevMode): ?>
                <!-- ============================================= -->
                <!-- NODE MARKERS (Non-cutting sleeve nodes) -->
                <!-- ============================================= -->
                <?php foreach ($sleeveNodes as $name => $node): ?>
                    <?php if (strpos($name, 'sc') !== 0): // Skip cutting nodes ?>
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2.5"
                            fill="<?php echo $node['color']; ?>" stroke="#fff" stroke-width="1"/>
                    <text x="<?php echo $node['x'] + 5; ?>" y="<?php echo $node['y'] - 5; ?>"
                          font-size="7" fill="<?php echo $node['color']; ?>" font-weight="bold">
                        <?php echo $name; ?>
                    </text>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- ============================================= -->
                <!-- CUTTING LINE NODE MARKERS (Red, sc1-sc7 except sc3, sc4) -->
                <!-- ============================================= -->
                <g id="sleeveCuttingNodeMarkers">
                    <?php
                    $sleeveCuttingNodes = ['sc1', 'sc2', 'sc5', 'sc6', 'sc7'];
                    foreach ($sleeveCuttingNodes as $name):
                        if (isset($sleeveNodes[$name])):
                            $node = $sleeveNodes[$name];
                            $nodeColor = '#DC2626';  // red
                    ?>
                    <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                            fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
                    <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
                          font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7">
                        <?php echo $name; ?>
                    </text>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </g>

                <!-- ============================================= -->
                <!-- GUIDE LINES (Connect control points to show curves) -->
                <!-- ============================================= -->
                <!-- Left curve guide -->
                <line x1="<?php echo sn('s2', 'x'); ?>" y1="<?php echo sn('s2', 'y'); ?>"
                      x2="<?php echo sn('c1', 'x'); ?>" y2="<?php echo sn('c1', 'y'); ?>"
                      stroke="#EAB308" stroke-width="0.5" stroke-dasharray="2,2" opacity="0.5"/>
                <line x1="<?php echo sn('c1', 'x'); ?>" y1="<?php echo sn('c1', 'y'); ?>"
                      x2="<?php echo sn('s1', 'x'); ?>" y2="<?php echo sn('s1', 'y'); ?>"
                      stroke="#EAB308" stroke-width="0.5" stroke-dasharray="2,2" opacity="0.5"/>

                <!-- Right curve guide -->
                <line x1="<?php echo sn('s1', 'x'); ?>" y1="<?php echo sn('s1', 'y'); ?>"
                      x2="<?php echo sn('c2', 'x'); ?>" y2="<?php echo sn('c2', 'y'); ?>"
                      stroke="#EAB308" stroke-width="0.5" stroke-dasharray="2,2" opacity="0.5"/>
                <line x1="<?php echo sn('c2', 'x'); ?>" y1="<?php echo sn('c2', 'y'); ?>"
                      x2="<?php echo sn('s3', 'x'); ?>" y2="<?php echo sn('s3', 'y'); ?>"
                      stroke="#EAB308" stroke-width="0.5" stroke-dasharray="2,2" opacity="0.5"/>

                <!-- ============================================= -->
                <!-- PATTERN LABELS -->
                <!-- ============================================= -->

                <!-- ============================================= -->
                <!-- MEASUREMENTS DISPLAY -->
                <!-- ============================================= -->
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 35; ?>"
                      font-size="9" fill="#666" text-anchor="middle">
                    Length: <?php echo number_format($slength, 1); ?>" |
                    Bicep: <?php echo number_format($saround, 1); ?>" |
                    Wrist: <?php echo number_format($sopen, 1); ?>"
                </text>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 50; ?>"
                      font-size="10" fill="#0066CC" text-anchor="middle" font-weight="bold">
                    s2→s1→s3 = <?php echo number_format($actualArmholePathInches, 2); ?>" | Target Armhole: <?php echo number_format($armhole, 2); ?>"
                </text>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 65; ?>"
                      font-size="8" fill="#666" text-anchor="middle">
                    s2→s11: <?php echo number_format($actualS2S1Length / $scale, 2); ?>" |
                    s11→s12: <?php echo number_format(0 / $scale, 2); ?>" |
                    s12→s3: <?php echo number_format($actualS1S3Length / $scale, 2); ?>"
                </text>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 80; ?>"
                      font-size="9" fill="#059669" text-anchor="middle" font-weight="bold">
                    Cap Height: s1(y) to s2(y) = <?php echo number_format($s1ToS2VerticalInches, 2); ?>"
                </text>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 95; ?>"
                      font-size="9" fill="#7C3AED" text-anchor="middle" font-weight="bold">
                    s2 to s3 (straight) = <?php echo number_format($s2ToS3Inches, 2); ?>"
                </text>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 110; ?>"
                      font-size="9" fill="#DC2626" text-anchor="middle" font-weight="bold">
                    Sleeve Length: s1(y) to s4(y) = <?php echo number_format($s1ToS4VerticalInches, 2); ?>" (Target: <?php echo number_format($slength, 2); ?>")
                </text>
                <?php if ($useIterativeCapHeight): ?>
                <text x="<?php echo $capX; ?>" y="<?php echo sn('s4', 'y') + 125; ?>"
                      font-size="8" fill="<?php echo $capHeightConverged ? '#059669' : '#DC2626'; ?>" text-anchor="middle">
                    <?php echo $capHeightConverged ? '✓' : '✗'; ?> Converged in <?php echo $capHeightIterations; ?> iterations |
                    Error: <?php echo number_format(abs($finalTotalPathLength - $targetArmholeLength) / $scale, 3); ?>"
                </text>
                <?php endif; ?>
                <?php endif; ?>

                <!-- ============================================= -->
                <!-- GRAINLINE -->
                <!-- ============================================= -->
                <?php
                // Position grainline horizontally centered
                $grainX = sn('s1', 'x'); // Center of sleeve
                $grainY = (sn('s1', 'y') + sn('s4', 'y')) / 2; // Midpoint between top and bottom
                $grainLength = 5; // 5 inches long
                echo grainLine($grainX, $grainY, $grainLength, 'horizontal');
                ?>

            </svg>
            <?php
            $sleeveSVG = ob_get_clean();
            echo $sleeveSVG;
            ?>
        </div>

            </div>
            <!-- END CELL 2x2: SLEEVE -->

        </div>
        <!-- END PATTERN GRID -->

        <?php
        // =============================================================================
        // UPDATE SESSION WITH CAPTURED SVG CONTENT
        // =============================================================================
        // Now that all SVGs have been rendered and captured, update the pattern data
        // in the session to include the SVG content for each pattern piece
        // =============================================================================

        // Update the pattern data with SVG content
        $patternData['front']['svg_content'] = $frontSVG;
        $patternData['back']['svg_content'] = $backSVG;
        $patternData['patti']['svg_content'] = $pattiSVG;
        $patternData['sleeve']['svg_content'] = $sleeveSVG;

        // Update the session with the complete pattern data including SVG content
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cacheKey = "pattern_" . ($measurementId ?? $measurements['measurement_id'] ?? 'latest');
        $_SESSION[$cacheKey] = [
            'data' => $patternData,
            'timestamp' => time(),
            'hash' => md5(json_encode($patternData['measurements']))
        ];
        $_SESSION['latest_pattern'] = $cacheKey;
        ?>

        <!-- CUSTOMER MEASUREMENTS PANEL -->
        <?php if (!$isIncludedFromPreview): ?>
        <div class="customer-measurements-panel" style="margin-top: 30px; padding: 20px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
            <h3 style="margin: 0 0 15px 0; color: #1E293B; font-size: 16px; border-bottom: 2px solid #3B82F6; padding-bottom: 8px;">Customer Measurements (inches)</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <tr>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold; width: 15%;">Bust</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; width: 10%;"><?php echo number_format($bust, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold; width: 15%;">Chest</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; width: 10%;"><?php echo number_format($chest, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold; width: 15%;">Waist</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; width: 10%;"><?php echo number_format($waist, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold; width: 15%;">Shoulder</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; width: 10%;"><?php echo number_format($shoulder, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Armhole</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($armhole, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Apex</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($apex, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Back Length</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($blength, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Front Length</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($flength, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Sleeve Length</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($slength, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Sleeve Around</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($saround, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Sleeve Opening</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($sopen, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">F. Shoulder</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($fshoulder, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Back Neck Depth</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($bnDepth, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #EFF6FF; font-weight: bold;">Front Neck Depth</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($frontNeckDepth, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #F0FDF4; font-weight: bold; color: #166534;">Patti Width</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($pattiWidth, 2); ?>"</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0; background: #F0FDF4; font-weight: bold; color: #166534;">Patti Length</td>
                    <td style="padding: 6px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($pattiLength, 2); ?>"</td>
                </tr>
            </table>

            <!-- CALCULATED/DERIVED VALUES -->
            <h3 style="margin: 20px 0 10px 0; color: #1E293B; font-size: 14px; border-bottom: 2px solid #10B981; padding-bottom: 6px;">Calculated Values</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #ECFDF5; font-weight: bold; width: 25%;">Quarter Bust (qBust)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; width: 25%;"><?php echo number_format($qBust, 2); ?>" = (<?php echo number_format($bust, 2); ?> / 4) + 0.5"</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #ECFDF5; font-weight: bold; width: 25%;">Quarter Waist (qWaist)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; width: 25%;"><?php echo number_format($qWaist, 2); ?>" = (<?php echo number_format($waist, 2); ?> / 4) + 0.5"</td>
                </tr>
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #ECFDF5; font-weight: bold;">Waist Curve Bulge Ratio</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">30% (constant)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #ECFDF5; font-weight: bold;">Waist Curve Bulge Depth</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;"><?php echo number_format($waistCurveBulgeDepth / $scale, 2); ?>" = 30% × a3-a4 distance</td>
                </tr>
            </table>

            <!-- KEY NODE FORMULAS -->
            <h3 style="margin: 20px 0 10px 0; color: #1E293B; font-size: 14px; border-bottom: 2px solid #8B5CF6; padding-bottom: 6px;">Key Node Formulas (Revised)</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold; width: 15%;">a4(x)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; width: 35%;">b1(x) = sMid = <?php echo number_format($shoulderMid_x / $scale, 2); ?>"</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold; width: 15%;">a4(y)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; width: 35%;">a11(y) + flength + 0.5" = <?php echo number_format(($originY + (($flength + 0.5) * $scale)) / $scale, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;">a5(y)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">a4(y) - 1" = <?php echo number_format(($nodes['a4']['y'] - (1.0 * $scale)) / $scale, 2); ?>"</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;">c1(y)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">b1.y - 0.5" = <?php echo number_format($nodes['c1']['y'] / $scale, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;">c3(y)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">b1.y + 0.5" = <?php echo number_format($nodes['c3']['y'] / $scale, 2); ?>"</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;">c2(y)</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">b1(y) = apex = <?php echo number_format($nodes['c2']['y'] / $scale, 2); ?>"</td>
                </tr>
                <tr>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;">c1-c3 gap</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;">1.0" (0.5" + 0.5")</td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0; background: #F5F3FF; font-weight: bold;"></td>
                    <td style="padding: 4px 12px; border: 1px solid #E2E8F0;"></td>
                </tr>
            </table>

            <p style="margin: 10px 0 0 0; font-size: 10px; color: #94A3B8; font-style: italic;">* This panel can be removed later</p>
        </div>
        <!-- END CUSTOMER MEASUREMENTS PANEL -->
        <?php endif; ?>

    </div>

    <script>
        // Initialize nodes from PHP
        const scale = <?php echo $scale; ?>;
        let nodes = <?php echo $nodesJson; ?>;
        let nodeCounter = Object.keys(nodes).length;

        // Render table rows
        function renderTable() {
            const tbody = document.getElementById('nodeTableBody');
            if (!tbody) return;  // Table hidden, skip rendering
            tbody.innerHTML = '';

            for (const [name, node] of Object.entries(nodes)) {
                const xInch = (node.x / scale).toFixed(2);
                const yInch = (node.y / scale).toFixed(2);
                const code = node.code || '';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="node-name">${name}</td>
                    <td><input type="text" class="coord-input" value="${xInch}" onchange="updateNode('${name}', 'x', this.value)"></td>
                    <td><input type="text" class="coord-input" value="${yInch}" onchange="updateNode('${name}', 'y', this.value)"></td>
                    <td><input type="text" class="label-input" value="${node.label}" onchange="updateNode('${name}', 'label', this.value)"></td>
                    <td class="code-cell">${code}</td>
                    <td><button class="btn btn-delete" onclick="deleteNode('${name}')">X</button></td>
                `;
                tbody.appendChild(row);
            }
        }

        // Update node value
        function updateNode(name, prop, value) {
            if (prop === 'x') {
                nodes[name].x = parseFloat(value) * scale;
            } else if (prop === 'y') {
                nodes[name].y = parseFloat(value) * scale;
            } else if (prop === 'label') {
                nodes[name].label = value;
            }
            renderNodeMarkers();
        }

        // Add new node
        function addNode() {
            nodeCounter++;
            const newName = 'a' + nodeCounter;
            nodes[newName] = {
                x: <?php echo $originX; ?>,
                y: <?php echo $originY; ?>,
                label: 'New Node'
            };
            renderTable();
            renderNodeMarkers();
        }

        // Delete node
        function deleteNode(name) {
            if (confirm('Delete node ' + name + '?')) {
                delete nodes[name];
                renderTable();
                renderNodeMarkers();
            }
        }

        // Render node markers on SVG (for a specific container)
        function renderNodeMarkersTo(containerId) {
            const g = document.getElementById(containerId);
            if (!g) return;
            g.innerHTML = '';

            for (const [name, node] of Object.entries(nodes)) {
                // Determine color (use node.color if set, otherwise default green)
                let nodeColor = '#10B981';  // Default green
                if (node.color === 'gray') {
                    nodeColor = '#9CA3AF';
                } else if (node.color === 'red') {
                    nodeColor = '#DC2626';
                }

                // Circle marker
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', node.x);
                circle.setAttribute('cy', node.y);
                circle.setAttribute('r', 2);
                circle.setAttribute('fill', nodeColor);
                circle.setAttribute('stroke', '#fff');
                circle.setAttribute('stroke-width', 1);
                g.appendChild(circle);

                // Label
                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', node.x + 3);
                text.setAttribute('y', node.y - 3);
                text.setAttribute('font-size', 8);
                text.setAttribute('fill', nodeColor);
                text.setAttribute('font-weight', '300');
                text.setAttribute('opacity', '0.7');
                text.textContent = name;
                g.appendChild(text);
            }
        }

        // Render node markers on both front and back SVGs
        function renderNodeMarkers() {
            renderNodeMarkersTo('nodeMarkers');
            renderNodeMarkersTo('nodeMarkersBack');
        }

        // Update pattern (placeholder for future use)
        function updatePattern() {
            console.log('Current nodes:', nodes);
            alert('Nodes updated! Check console for data.');
        }

        // Initial render
        renderTable();
        renderNodeMarkers();
    </script>
</body>
</html>
