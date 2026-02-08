<?php
/**
 * =============================================================================
 * PRINCESS CUT BLOUSE - FRONT PATTERN
 * =============================================================================
 *
 * Generates the front panel pattern for a princess cut saree blouse.
 * Includes integrated patti (extension panel) below the front.
 *
 * MODES:
 * - Standalone: Full HTML preview (default) - for development/testing
 * - Composite:  Returns SVG + data only (when COMPOSITE_MODE defined)
 *
 * USAGE:
 *   Standalone: princessCutFront.php?customer_id=123&measurement_id=456&mode=dev
 *   Composite:  define('COMPOSITE_MODE', true); include 'princessCutFront.php';
 *
 * EXPORTS (when included):
 *   $frontPatternData - Array with nodes, svg_content, dimensions, curves
 *
 * =============================================================================
 */

// =============================================================================
// SECTION 1: CONFIGURATION & DATA LOADING
// =============================================================================

// Only load config if not already loaded by composite
if (!defined('PATTERN_CONFIG_LOADED')) {
    require_once __DIR__ . '/patternConfig.php';
}

// Calculate armhole (with origin offset) - uses measurements from patternConfig
// Back: 1.5" for bust > 30, 1.25" for bust ≤ 30
$backBottomCurve = ($bust > 30) ? 1.5 : 1.25;

// First, get initial armhole height from calculateArmhole (for halfShoulder, armHoleDepth, etc.)
calculateArmhole($backBottomCurve, $originX, $originY);

// Now curve-fit using the BACK pattern's actual path (z8→z71→z6)
// This ensures the A-B height is correct for the back's armhole length = armhole/2
$backTargetArmhole = ($armhole / 2) * $scale;
$backArmHoleHeight = fitBackArmholePath(
    $armHoleHeight,      // Initial height from calculateArmhole
    $halfShoulder,
    $armHoleDepth,
    $backTargetArmhole,
    $scale,
    $backBottomCurve,
    $originX,
    $originY
);

// Now recalculate front pattern with the back's curve-fitted A-B height
// Front uses its own path style (A→AB2→B1→C) but with the same A-B height as back
// Front: 1.0" for bust > 30, 0.75" for bust ≤ 30
$frontBottomCurve = ($bust > 30) ? 1.0 : 0.75;
calculateArmhole($frontBottomCurve, $originX, $originY, $backArmHoleHeight);

// =============================================================================
// SECTION 2: FRONT PATTERN CALCULATIONS
// =============================================================================

// Pre-calculate values needed for nodes
$shoulderLine_x = $pointA_x - ($shoulder * $scale);

// Front neck depth calculation (diagonal from a11 to a1)
// a11.x = $shoulderLine_x, a11.y = $originY
// a1.x = $originX, a1.y = a11.y + vertical component of diagonal
$a1_horizontal = $shoulderLine_x - $originX;  // horizontal distance from a1.x to a11.x
$a1_diagonal = $frontNeckDepth * $scale;       // the diagonal measurement
$a1_vertical = sqrt(pow($a1_diagonal, 2) - pow($a1_horizontal, 2));  // Pythagorean theorem
$a1_y = $originY + $a1_vertical;  // a1.y = a11.y + vertical offset

// Shoulder Mid x-coordinate (needed for a4 and b1)
$shoulderMid_x = (($pointA_x - ($shoulder * $scale)) + $pointA_x) / 2;
$shoulderMid_y = ($originY + $pointA_y) / 2;

// =============================================================================
// FRONT PATTERN NODES
// =============================================================================
$frontNodes = [];

// Node 0: Origin (top-left corner) - GRAY reference point
$frontNodes['a0'] = [
    'x' => $originX,
    'y' => $originY,
    'label' => 'Origin',
    'color' => 'gray',
    'code' => '$a0 = originX, originY'
];

// Node 1: Front Neck Depth (diagonal distance from a11 to a1)
$frontNodes['a1'] = [
    'x' => $originX,
    'y' => $originY + $a1_vertical,  // a1.y = a11.y + sqrt(diagonal² - horizontal²)
    'label' => 'Front Neck',
    'code' => '$a1 = originX, a11.y + sqrt(diagonal(' . number_format($frontNeckDepth, 2) . '")² - horizontal(' . number_format($a1_horizontal / $scale, 2) . '")²)'
];

// Front Length Y: bottom of front panel (originY + flength - 1")
$frontLengthY = $originY + (($flength - 1.0) * $scale);

// Node a4: REMOVED - waist curve control point values kept as standalone variables
$a4_x = $shoulderMid_x;
$a4_y = $originY + (($flength + 0.5) * $scale);

// Node 5: Waist point (a5.x = a7.x, y = a4.y - 1")
$frontNodes['a5'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $a4_y - (1.0 * $scale),  // temporary, overridden after a3 is defined
    'label' => 'Waist',
    'code' => '$a5 = originX + qBust(' . number_format($qBust, 2) . '"), a4.y - 1"'
];

// Node 7: Intersection of armhole level and bust line
// Same logic as sariBlouse3TFront.php a7
$frontNodes['a7'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $pointC_y,
    'label' => 'a7',
    'code' => '$a7 = originX + qBust(' . number_format($qBust, 2) . '"), pointC_y'
];

// Node 51: REMOVED - a3 now connects directly to a71
// Keeping slope calculation as it may be needed elsewhere
$a4_a5_slope = ($frontNodes['a5']['y'] - $a4_y) / ($frontNodes['a5']['x'] - $a4_x);

// Node 71: 1.5" right of a7 (right edge of side box)
$frontNodes['a71'] = [
    'x' => $frontNodes['a7']['x'] + (1.5 * $scale),
    'y' => $frontNodes['a7']['y'],
    'label' => 'a71',
    'code' => '$a71 = a7.x + 1.5", a7.y'
];

// Node 8: Armhole End (Point C) - where armhole meets bust line
// Using originX + qBust (same logic as sariBlouse3TFront.php a7)
$frontNodes['a8'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $pointC_y,
    'label' => 'Armhole End',
    'code' => '$a8 = originX + qBust(' . number_format($qBust, 2) . '"), pointC_y'
];

// Node 9: Armhole Corner (45° bend - Point B1)
$frontNodes['a9'] = [
    'x' => $pointB1_x,
    'y' => $pointB1_y,
    'label' => 'Armhole Corner',
    'code' => '$a9 = a10.x + ' . number_format($frontBottomCurve, 2) . '" * cos(45°), pointB.y - ' . number_format($frontBottomCurve, 2) . '" * sin(45°)'
];

// Node 91: Armhole Curve Midpoint (AB2 - on curve between a9 and a10)
$frontNodes['a91'] = [
    'x' => $pointAB2_x,
    'y' => $pointAB2_y,
    'label' => 'Armhole Mid',
    'code' => '$a91 = midpoint(a10, pointB).x - 0.25", midpoint(a10, pointB).y'
];

// Node 10: Shoulder (top of armhole - Point A)
$frontNodes['a10'] = [
    'x' => $pointA_x,
    'y' => $pointA_y,
    'label' => 'Shoulder',
    'code' => '$a10 = originX + halfShoulder(' . number_format($halfShoulder, 2) . '"), originY + 0.5"'
];

// Node 11: Shoulder Line (top, end of shoulder near neck)
$frontNodes['a11'] = [
    'x' => $pointA_x - ($shoulder * $scale),
    'y' => $originY,
    'label' => 'Shoulder Line',
    'code' => '$a11 = a10.x - shoulder(' . number_format($shoulder, 2) . '"), a0.y'
];

// Node 111: Midpoint between a11 and a1 (for neck curve) - y centered
$frontNodes['a111'] = [
    'x' => $shoulderLine_x,
    'y' => ($frontNodes['a1']['y'] + $frontNodes['a11']['y']) / 2,
    'label' => 'a111',
    'code' => '$a111 = a11.x, midpoint(a1.y, a11.y)'
];

// Shoulder Mid: Reference point (gray) - midpoint of shoulder line (a11 to a10)
$frontNodes['sMid'] = [
    'x' => $shoulderMid_x,
    'y' => $shoulderMid_y,
    'label' => 'Shoulder Mid',
    'color' => 'gray',
    'code' => '$sMid = midpoint(a11, a10)'
];

// Node b1: x = sMid.x (aligned with shoulder midpoint), y = apex level - 0.5"
$frontNodes['b1'] = [
    'x' => $shoulderMid_x,
    'y' => $originY + ($apex * $scale),
    'label' => 'b1',
    'code' => '$b1 = sMid.x, apex'
];

// Helper function to get node coordinates (scoped to front pattern)
if (!function_exists('frontNode')) {
    function frontNode($name, $coord = null) {
        global $frontNodes;
        if (!isset($frontNodes[$name])) return null;
        if ($coord === 'x') return $frontNodes[$name]['x'];
        if ($coord === 'y') return $frontNodes[$name]['y'];
        return $frontNodes[$name];
    }
}

// =============================================================================
// WAIST CURVE CONTROL POINT
// =============================================================================
$waistCurveWidth = abs($a4_x - $originX);
$waistCurveBulgeRatio = 0.30;
$waistCurveBulgeDepth = $waistCurveWidth * $waistCurveBulgeRatio;
$waistCurveControlPointPosition = 0.35;

$q_ctrl_left_x = $originX + ($a4_x - $originX) * $waistCurveControlPointPosition;
$q_ctrl_left_y = $frontLengthY + ($a4_y - $frontLengthY) * $waistCurveControlPointPosition + $waistCurveBulgeDepth;

// =============================================================================
// BOTTOM TUCK NODES (b2, b4)
// =============================================================================
$b2_x = $shoulderMid_x - (($bottomTuckWidth / 2) * $scale);
// Node b2: left side of bottom tuck, y = a11.y + flength (full front length from shoulder)
$frontNodes['b2'] = [
    'x' => $b2_x,
    'y' => frontNode('a11', 'y') + ($flength * $scale),
    'label' => 'b2',
    'code' => '$b2 = sMid.x - (bottomTuckWidth/2), a11.y + flength'
];

$b4_x = $shoulderMid_x + (($bottomTuckWidth / 2) * $scale);

// Node b4: right side of bottom tuck, y = b2.y
$frontNodes['b4'] = [
    'x' => $b4_x,
    'y' => frontNode('b2', 'y'),
    'label' => 'b4',
    'code' => '$b4 = sMid.x + (bottomTuckWidth/2), b2.y'
];

// Node b3: REMOVED - b4 now connects directly to b1

// armholeTuck nodes (at a9 - armhole corner, pointing to b1)
// Width of e1-e3 = distance from a8 to a7, with minimum 0.5" constraint
$a8_a7_distance = frontNode('a7','x') - frontNode('a8','x');  // horizontal distance a8 to a7
$minTuckWidth = 0.5 * $scale;  // minimum 0.5" distance between e1-e3
if ($a8_a7_distance < $minTuckWidth) {
    $a8_a7_distance = $minTuckWidth;
}
$e_half_width = $a8_a7_distance / 2;  // half the tuck width (from center a9)

// e1: half of a8-a7 distance above a9 (along the armhole curve direction - toward a10)
$frontNodes['e1'] = [
    'x' => $pointB1_x - ($e_half_width * cos(deg2rad(45))),
    'y' => $pointB1_y - ($e_half_width * sin(deg2rad(45))),
    'label' => 'e1',
    'code' => '$e1 = a9 - (a8-a7)/2 toward a10 = ' . number_format($a8_a7_distance / $scale, 2) . '" total width'
];

// e3: half of a8-a7 distance below a9 (along the armhole curve direction - toward a8)
$frontNodes['e3'] = [
    'x' => $pointB1_x + ($e_half_width * cos(deg2rad(45))),
    'y' => $pointB1_y + ($e_half_width * sin(deg2rad(45))),
    'label' => 'e3',
    'code' => '$e3 = a9 + (a8-a7)/2 toward a8 = ' . number_format($a8_a7_distance / $scale, 2) . '" total width'
];

// Override b1.x = sMid.x - 0.25"
$frontNodes['b1']['x'] = $shoulderMid_x - (0.25 * $scale);
$frontNodes['b1']['code'] = '$b1 = sMid.x - 0.25", apex.y';

// =============================================================================
// CUTTING LINE NODES (r = red line seam allowance)
// Uniform 0.5" offset from pattern, following the a-nodes
// =============================================================================
$seamOffset = 0.5;  // 0.5" uniform offset

// r1: bottom-left cutting corner — x,y overridden after patti nodes
$frontNodes['r1'] = [
    'x' => $originX,
    'y' => $originY,  // temporary, overridden after patti
    'label' => 'r1',
    'color' => 'red',
    'code' => '$r1 = a2.x, a2.y + 0.5"'
];

// Node r2: REMOVED

// r3: a71.x, a3.y + 0.5" (bottom-right cutting line corner) — y overridden after a3 defined
$frontNodes['r3'] = [
    'x' => frontNode('a71','x'),
    'y' => frontNode('a71','y'),  // temporary, overridden after patti
    'label' => 'r3',
    'color' => 'red',
    'code' => '$r3 = a71.x, a3.y + 0.5"'
];

// r5: a71.x + 0.25" (1.75" right of a7.x), 0.25" up from a71 (intersection)
$frontNodes['r5'] = [
    'x' => frontNode('a71','x'),
    'y' => frontNode('a71','y') - (0.25 * $scale),
    'label' => 'r5',
    'color' => 'red',
    'code' => '$r5 = a71.x, a71.y - 0.25"'
];

// =============================================================================
// ARMHOLE CUTTING LINE - exact copy of armhole curve, shifted 0.25" RIGHT and 0.25" UP
// =============================================================================
$cutShiftX = 0.25 * $scale;  // 0.25" to the right (was 0.5, moved -0.25)
$cutShiftY = 0.25 * $scale;  // 0.25" up (was 0.5, moved +0.25 down = less up)

// r6: a8 + 0.25" right, 0.25" up
$frontNodes['r6'] = [
    'x' => frontNode('a8','x') + $cutShiftX,
    'y' => frontNode('a8','y') - $cutShiftY,
    'label' => 'r6',
    'color' => 'red',
    'code' => '$r6 = a8.x + 0.25", a8.y - 0.25"'
];

// r_a9: a9 + 0.25" right, 0.25" up
$frontNodes['r_a9'] = [
    'x' => frontNode('a9','x') + $cutShiftX,
    'y' => frontNode('a9','y') - $cutShiftY,
    'label' => 'r_a9',
    'color' => 'red',
    'code' => '$r_a9 = a9.x + 0.25", a9.y - 0.25"'
];

// r_a91: a91 + 0.25" right, 0.25" up
$frontNodes['r_a91'] = [
    'x' => frontNode('a91','x') + $cutShiftX,
    'y' => frontNode('a91','y') - $cutShiftY,
    'label' => 'r_a91',
    'color' => 'red',
    'code' => '$r_a91 = a91.x + 0.25", a91.y - 0.25"'
];

// r7: a10 + 0.25" right, 0.25" up
$frontNodes['r7'] = [
    'x' => frontNode('a10','x') + $cutShiftX,
    'y' => frontNode('a10','y') - $cutShiftY,
    'label' => 'r7',
    'color' => 'red',
    'code' => '$r7 = a10.x + 0.25", a10.y - 0.25"'
];

// r8: 0.5" left and 0.5" up from a11 (shoulder line end)
$frontNodes['r8'] = [
    'x' => frontNode('a11','x') - (0.25 * $scale),
    'y' => frontNode('a11','y') - (0.25 * $scale),
    'label' => 'r8',
    'color' => 'red',
    'code' => '$r8 = a11.x - 0.25", a11.y - 0.25"'
];

// r9: aligned with a1.x (includes 0.25" ease), 0.25" above a1.y (front neck level)
$frontNodes['r9'] = [
    'x' => frontNode('a1','x'),
    'y' => frontNode('a1','y') - (0.25 * $scale),
    'label' => 'r9',
    'color' => 'red',
    'code' => '$r9 = a1.x (with ease), a1.y - 0.25"'
];

// r91: midpoint on outer red cutting line between r9 and r8 (at a111 level)
$frontNodes['r91'] = [
    'x' => frontNode('a111','x') - (0.25 * $scale),
    'y' => frontNode('a111','y'),
    'label' => 'r91',
    'color' => 'red',
    'code' => '$r91 = a111.x - 0.25", a111.y'
];

// =============================================================================
// PATTI PATTERN CALCULATIONS (positioned below front pattern)
// =============================================================================
$pattiWidth = $qBust;  // bust / 4
$pattiHeight = $blength - $flength;

// Patti origin - positioned below the front pattern, moved 0.5" upward
$pattiGap = 0;  // No gap between front and patti patterns
$pattiOriginX = $originX;
$pattiOriginY = $originY + (($flength + $pattiGap) * $scale) - (0.5 * $scale);  // 0.5" upward

// =============================================================================
// PATTI NODES (p prefix to avoid conflict with front nodes)
// =============================================================================
$pattiNodes = [];

// Helper function for patti nodes
if (!function_exists('pattiNode')) {
    function pattiNode($name, $coord = null) {
        global $pattiNodes;
        if (!isset($pattiNodes[$name])) return null;
        if ($coord === 'x') return $pattiNodes[$name]['x'];
        if ($coord === 'y') return $pattiNodes[$name]['y'];
        return $pattiNodes[$name];
    }
}

// Get frontLength, a4, a5 offsets for patti shape
$p_a4_x_offset = $a4_x - $originX;
$p_a4_y_offset = $a4_y - $frontLengthY;
$p_a5_x_offset = frontNode('a5', 'x') - $originX;
$p_a5_y_offset = frontNode('a5', 'y') - $frontLengthY;

// p1 REMOVED - patti top-left is now a3 directly (front and patti are joined)
// p2 REMOVED - was reference point only; calculations now use a4 offset directly

// a2/a3 y-coordinate base (was p2.y)
$pattiBottomBaseY = $frontLengthY + $p_a4_y_offset;

// a3 = bottom-right corner of front panel (was p3)
$frontNodes['a3'] = [
    'x' => frontNode('a71', 'x'),
    'y' => $originY + ($blength * $scale),
    'label' => 'a3',
    'code' => '$a3 = a71.x, originY + blength(' . number_format($blength, 2) . '")'
];

// a2 = bottom-left (renamed from p4)
$frontNodes['a2'] = [
    'x' => $originX,
    'y' => $originY + ($blength * $scale),
    'label' => 'a2',
    'code' => '$a2 = originX, originY + blength(' . number_format($blength, 2) . '")'
];

// p5 REMOVED - replaced by a3 (bottom-right of front panel)

// Override a5.y = a3.y (originY + blength)
$frontNodes['a5']['y'] = $frontNodes['a3']['y'];
$frontNodes['a5']['code'] = '$a5 = qBust(' . number_format($qBust, 2) . '"), a3.y';

// Node b5: x = b2.x, y = originY + blength
$frontNodes['b5'] = [
    'x' => frontNode('b2', 'x'),
    'y' => $originY + ($blength * $scale),
    'label' => 'b5',
    'code' => '$b5 = b2.x, originY + blength'
];

// Node b6: x = b4.x, y = originY + blength
$frontNodes['b6'] = [
    'x' => frontNode('b4', 'x'),
    'y' => $originY + ($blength * $scale),
    'label' => 'b6',
    'code' => '$b6 = b4.x, originY + blength'
];

// Override r1 to follow a2 (bottom-left of patti)
$frontNodes['r1']['x'] = $frontNodes['a2']['x'];
$frontNodes['r1']['y'] = $frontNodes['a2']['y'] + (0.5 * $scale);

// Override r3 to follow a3 (bottom-right of front panel)
$frontNodes['r3']['y'] = $frontNodes['a3']['y'] + (0.5 * $scale);

// Vertical guide line position for patti
$patti_a8_fl_distance = frontNode('a8', 'x') - $originX;
$pattiVerticalLineX = $originX + $patti_a8_fl_distance;

// Patti waist curve control points (matching a3→a4 curvature)
// Use same bulge ratio and control point position as front pattern
$patti_a4_offset_x = $originX + $p_a4_x_offset;  // was p2.x
$patti_p1_p2_distance = abs($patti_a4_offset_x - $originX);
$pattiWaistCurveBulgeDepth = $patti_p1_p2_distance * $waistCurveBulgeRatio;
$p_ctrl_x = $originX + ($patti_a4_offset_x - $originX) * $waistCurveControlPointPosition;
$p_ctrl_y = $frontLengthY + ($pattiBottomBaseY - $frontLengthY) * $waistCurveControlPointPosition + $pattiWaistCurveBulgeDepth;

// =============================================================================
// SVG DIMENSIONS (includes both front and patti patterns)
// =============================================================================
$frontSvgWidthInches = ($qBust + 2.5);
// Height = top margin (1") + flength + pattiGap + a4_y_offset (waist curve dip) + pattiHeight + bottom margin (1.5")
// The a4_y_offset accounts for the waist curve extending below flength
$a4_y_offset_inches = $p_a4_y_offset / $scale;  // Convert back to inches
$frontSvgHeightInches = (1.0 + $flength + $pattiGap + $a4_y_offset_inches + $pattiHeight + 1.5);
$frontSvgWidth = $frontSvgWidthInches * $scale;
$frontSvgHeight = $frontSvgHeightInches * $scale;

// Calculate bounding box
$frontBounds = [
    'minX' => 0,
    'minY' => 0,
    'width' => $frontSvgWidth,
    'height' => $frontSvgHeight
];

// =============================================================================
// PRE-CALCULATE ALL CURVE CONTROL POINTS FOR SVG
// (Keeps Section 3 as pure rendering with no calculations)
// =============================================================================

// Cutting line control points
$seamOffsetPx = 0.5 * $scale;
$cut_waist_ctrl_x = $q_ctrl_left_x;
$cut_waist_ctrl_y = $q_ctrl_left_y + (0.25 * $scale);
$cut_neck_ctrl1_x = frontNode('a111','x') - $seamOffsetPx;
$cut_neck_ctrl1_y = frontNode('a1','y');
$cut_neck_ctrl2_x = frontNode('a111','x') - $seamOffsetPx;
$cut_neck_ctrl2_y = frontNode('a11','y');

// Tuck center points
$b_center_x = (frontNode('b2','x') + frontNode('b4','x')) / 2;
$b_center_y = frontNode('b2','y');

// Bottom tuck curve: b4 → b6 (0.25" bulge to the right)
$b4_b6_midX = (frontNode('b4','x') + frontNode('b6','x')) / 2;
$b4_b6_midY = (frontNode('b4','y') + frontNode('b6','y')) / 2;
$b4_b6_ctrlX = $b4_b6_midX + (0.25 * $scale);
$b4_b6_ctrlY = $b4_b6_midY;

// Princess cut curve: b1 → b2 (0.1" bulge to the left)
$b1_b2_midX = (frontNode('b1','x') + frontNode('b2','x')) / 2;
$b1_b2_midY = (frontNode('b1','y') + frontNode('b2','y')) / 2;
$b1_b2_ctrlX = $b1_b2_midX - (0.1 * $scale);
$b1_b2_ctrlY = $b1_b2_midY;

// Princess cut curve: b1 → e1 (0.5" bulge to the left)
$b1_e1_midX = (frontNode('b1','x') + frontNode('e1','x')) / 2;
$b1_e1_midY = (frontNode('b1','y') + frontNode('e1','y')) / 2;
$b1_e1_ctrlX = $b1_e1_midX - (0.5 * $scale);
$b1_e1_ctrlY = $b1_e1_midY;

// Princess cut curve: b4 → b1 (0.25" bulge to the left)
$b4_b1_midX = (frontNode('b4','x') + frontNode('b1','x')) / 2;
$b4_b1_midY = (frontNode('b4','y') + frontNode('b1','y')) / 2;
$b4_b1_ctrlX = $b4_b1_midX - (0.25 * $scale);
$b4_b1_ctrlY = $b4_b1_midY;

// Princess cut curve: b1 → e3 (0.5" bulge to the left)
$b1_e3_midX = (frontNode('b1','x') + frontNode('e3','x')) / 2;
$b1_e3_midY = (frontNode('b1','y') + frontNode('e3','y')) / 2;
$b1_e3_ctrlX = $b1_e3_midX - (0.5 * $scale);
$b1_e3_ctrlY = $b1_e3_midY;

// Ease text position (centered in side box)
$easeTextX = (frontNode('a7', 'x') + frontNode('a71', 'x')) / 2;
$easeTextY = (frontNode('a7', 'y') + frontNode('a5', 'y')) / 2;

// Grainline position (between b1 and sMid)
$grainX = (frontNode('b1', 'x') + frontNode('sMid', 'x')) / 2;
$grainY = (frontNode('b1', 'y') + frontNode('sMid', 'y')) / 2;
$grainLength = 4;  // 4 inches

// Cutting line curve - exact copy shifted 0.25" right and 0.25" up
// All control points shift 0.25" right and 0.25" up
$cut_ctrl3_x = $ctrl3_x + $cutShiftX;
$cut_ctrl3_y = $ctrl3_y - $cutShiftY;
$cut_ctrl1_x = $ctrl1_x + $cutShiftX;
$cut_ctrl1_y = $ctrl1_y - $cutShiftY;
$cut_ctrl2a_x = $ctrl2a_x + $cutShiftX;
$cut_ctrl2a_y = $ctrl2a_y - $cutShiftY;
$cut_ctrl2b_x = $ctrl2b_x + $cutShiftX;
$cut_ctrl2b_y = $ctrl2b_y - $cutShiftY;

// =============================================================================
// SECTION 3: SVG GENERATION (Pure rendering — no calculations)
// =============================================================================

// Generate SVG content
ob_start();
?>
<svg id="frontPatternSvg"
     viewBox="<?php echo $frontBounds['minX']; ?> <?php echo $frontBounds['minY']; ?> <?php echo $frontBounds['width']; ?> <?php echo $frontBounds['height']; ?>"
     xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
    <rect width="100%" height="100%" fill="#fff"/>

    <?php if ($isDevMode): ?>
    <!-- Margin guides -->
    <line x1="<?php echo $originX; ?>" y1="0" x2="<?php echo $originX; ?>" y2="<?php echo $frontSvgHeight; ?>" stroke="#eee" stroke-dasharray="4,4"/>
    <line x1="0" y1="<?php echo $originY; ?>" x2="<?php echo $frontSvgWidth; ?>" y2="<?php echo $originY; ?>" stroke="#eee" stroke-dasharray="4,4"/>
    <?php endif; ?>

    <!-- Armhole curve -->
    <path d="<?php echo $armholeSvgPath; ?>" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Shoulder line: a11 to a10 -->
    <line x1="<?php echo frontNode('a11','x'); ?>" y1="<?php echo frontNode('a11','y'); ?>"
          x2="<?php echo frontNode('a10','x'); ?>" y2="<?php echo frontNode('a10','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Front neck: curve a1 to a111, then straight line a111 to a11 -->
    <path d="M <?php echo frontNode('a1','x'); ?>,<?php echo frontNode('a1','y'); ?> Q <?php echo frontNode('a111','x'); ?>,<?php echo frontNode('a1','y'); ?> <?php echo frontNode('a111','x'); ?>,<?php echo frontNode('a111','y'); ?> L <?php echo frontNode('a11','x'); ?>,<?php echo frontNode('a11','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Center front line: a1 to a2 (straight vertical) -->
    <line x1="<?php echo frontNode('a1','x'); ?>" y1="<?php echo frontNode('a1','y'); ?>"
          x2="<?php echo frontNode('a2','x'); ?>" y2="<?php echo frontNode('a2','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- BOTTOM TUCK: b4 → b6 (curve with 0.25" bulge to the right) -->
    <path d="M <?php echo frontNode('b4','x'); ?>,<?php echo frontNode('b4','y'); ?>
             Q <?php echo $b4_b6_ctrlX; ?>,<?php echo $b4_b6_ctrlY; ?>
               <?php echo frontNode('b6','x'); ?>,<?php echo frontNode('b6','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- PRINCESS CUT LINE: e1 → b1 → b2 → b5 (black solid line) -->
    <!-- b2 to b5 (straight line) -->
    <line x1="<?php echo frontNode('b2','x'); ?>" y1="<?php echo frontNode('b2','y'); ?>"
          x2="<?php echo frontNode('b5','x'); ?>" y2="<?php echo frontNode('b5','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>
             Q <?php echo $b1_b2_ctrlX; ?>,<?php echo $b1_b2_ctrlY; ?>
               <?php echo frontNode('b2','x'); ?>,<?php echo frontNode('b2','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <path d="M <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>
             Q <?php echo $b1_e1_ctrlX; ?>,<?php echo $b1_e1_ctrlY; ?>
               <?php echo frontNode('e1','x'); ?>,<?php echo frontNode('e1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- PRINCESS CUT LINE: b4 → b1 → e3 (black solid line) -->
    <path d="M <?php echo frontNode('b4','x'); ?>,<?php echo frontNode('b4','y'); ?>
             Q <?php echo $b4_b1_ctrlX; ?>,<?php echo $b4_b1_ctrlY; ?>
               <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <path d="M <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>
             Q <?php echo $b1_e3_ctrlX; ?>,<?php echo $b1_e3_ctrlY; ?>
               <?php echo frontNode('e3','x'); ?>,<?php echo frontNode('e3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Side seam: a5 to a7 to a8 -->
    <line x1="<?php echo frontNode('a5','x'); ?>" y1="<?php echo frontNode('a5','y'); ?>"
          x2="<?php echo frontNode('a7','x'); ?>" y2="<?php echo frontNode('a7','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo frontNode('a7','x'); ?>" y1="<?php echo frontNode('a7','y'); ?>"
          x2="<?php echo frontNode('a8','x'); ?>" y2="<?php echo frontNode('a8','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Side box: a7 -> a71 -->
    <line x1="<?php echo frontNode('a7','x'); ?>" y1="<?php echo frontNode('a7','y'); ?>"
          x2="<?php echo frontNode('a71','x'); ?>" y2="<?php echo frontNode('a71','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Ease text (in side box) -->
    <text x="<?php echo $easeTextX; ?>" y="<?php echo $easeTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(90, <?php echo $easeTextX; ?>, <?php echo $easeTextY; ?>)">---- ease -----</text>

    <!-- CUTTING LINE (RED) - armhole is exact copy of A→AB2→B1→C shifted 0.5" right -->
    <path d="M <?php echo frontNode('r1','x'); ?>,<?php echo frontNode('r1','y'); ?>
             L <?php echo frontNode('r3','x'); ?>,<?php echo frontNode('r3','y'); ?>
             L <?php echo frontNode('r5','x'); ?>,<?php echo frontNode('r5','y'); ?>
             L <?php echo frontNode('r6','x'); ?>,<?php echo frontNode('r6','y'); ?>
             Q <?php echo $cut_ctrl3_x; ?>,<?php echo $cut_ctrl3_y; ?>
               <?php echo frontNode('r_a9','x'); ?>,<?php echo frontNode('r_a9','y'); ?>
             C <?php echo $cut_ctrl2b_x; ?>,<?php echo $cut_ctrl2b_y; ?>
               <?php echo $cut_ctrl2a_x; ?>,<?php echo $cut_ctrl2a_y; ?>
               <?php echo frontNode('r_a91','x'); ?>,<?php echo frontNode('r_a91','y'); ?>
             Q <?php echo $cut_ctrl1_x; ?>,<?php echo $cut_ctrl1_y; ?>
               <?php echo frontNode('r7','x'); ?>,<?php echo frontNode('r7','y'); ?>
             L <?php echo frontNode('r8','x'); ?>,<?php echo frontNode('r8','y'); ?>
             L <?php echo frontNode('r91','x'); ?>,<?php echo frontNode('r91','y'); ?>
             Q <?php echo frontNode('r91','x'); ?>,<?php echo frontNode('r9','y'); ?>
               <?php echo frontNode('r9','x'); ?>,<?php echo frontNode('r9','y'); ?>
             L <?php echo frontNode('r1','x'); ?>,<?php echo frontNode('r1','y'); ?>"
          stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

    <!-- Scissors Icon on front cutting line at r5 -->
    <?php echo scissorsIcon(frontNode('r5','x'), frontNode('r5','y'), 90, 0.4, '#333333', 'r5'); ?>

    <!-- Grainline -->
    <?php echo grainLine($grainX, $grainY, $grainLength, 'vertical'); ?>

    <?php if ($isPrintMode): ?>
    <!-- X markers at node positions (Print Mode Only) -->
    <text x="<?php echo frontNode('a8', 'x'); ?>" y="<?php echo frontNode('a8', 'y'); ?>"
          font-size="6" text-anchor="middle" dominant-baseline="middle" fill="#000">X</text>
    <text x="<?php echo frontNode('a7', 'x'); ?>" y="<?php echo frontNode('a7', 'y'); ?>"
          font-size="6" text-anchor="middle" dominant-baseline="middle" fill="#000">X</text>
    <?php endif; ?>

    <?php if ($isDevMode): ?>
    <!-- ARMHOLE CONSTRUCTION POINTS (Gray - Dev Mode Only) -->
    <!-- These show the L-shape calculation points from deepNeck.php -->
    <?php
    // Point A: Top of armhole (shoulder point)
    $armhole_A_x = $pointA_x;
    $armhole_A_y = $pointA_y;

    // Point B: Corner of L-shape (at halfShoulder, armHoleHeight)
    $armhole_B_x = $pointB_x;
    $armhole_B_y = $pointB_y;

    // Point AB: Midpoint between A and B
    $armhole_AB_x = $pointAB_x;
    $armhole_AB_y = $pointAB_y;

    // Point AB2: 0.25" left of AB (creates inward curve)
    $armhole_AB2_x = $pointAB2_x;
    $armhole_AB2_y = $pointAB2_y;

    // Point B1: 1" from B at 45° angle (bottom curve transition)
    $armhole_B1_x = $pointB1_x;
    $armhole_B1_y = $pointB1_y;

    // Point C: End of armhole
    $armhole_C_x = $pointC_x;
    $armhole_C_y = $pointC_y;
    ?>

    <!-- L-Shape construction lines (gray dashed) -->
    <!-- Vertical line: A to B -->
    <line x1="<?php echo $armhole_A_x; ?>" y1="<?php echo $armhole_A_y; ?>"
          x2="<?php echo $armhole_B_x; ?>" y2="<?php echo $armhole_B_y; ?>"
          stroke="#9CA3AF" stroke-width="0.5" stroke-dasharray="3,3"/>

    <!-- Horizontal line: B to C -->
    <line x1="<?php echo $armhole_B_x; ?>" y1="<?php echo $armhole_B_y; ?>"
          x2="<?php echo $armhole_C_x; ?>" y2="<?php echo $armhole_C_y; ?>"
          stroke="#9CA3AF" stroke-width="0.5" stroke-dasharray="3,3"/>

    <!-- 45° line: B to B1 -->
    <line x1="<?php echo $armhole_B_x; ?>" y1="<?php echo $armhole_B_y; ?>"
          x2="<?php echo $armhole_B1_x; ?>" y2="<?php echo $armhole_B1_y; ?>"
          stroke="#9CA3AF" stroke-width="0.5" stroke-dasharray="3,3"/>

    <!-- Construction point markers (gray circles with labels) -->
    <!-- Point A -->
    <circle cx="<?php echo $armhole_A_x; ?>" cy="<?php echo $armhole_A_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_A_x + 8; ?>" y="<?php echo $armhole_A_y - 5; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">A</text>

    <!-- Point B -->
    <circle cx="<?php echo $armhole_B_x; ?>" cy="<?php echo $armhole_B_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_B_x + 8; ?>" y="<?php echo $armhole_B_y + 12; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">B</text>

    <!-- Point AB (midpoint) -->
    <circle cx="<?php echo $armhole_AB_x; ?>" cy="<?php echo $armhole_AB_y; ?>" r="2"
            fill="#9CA3AF" stroke="none"/>
    <text x="<?php echo $armhole_AB_x + 8; ?>" y="<?php echo $armhole_AB_y + 3; ?>"
          font-size="8" fill="#9CA3AF">AB</text>

    <!-- Point AB2 (0.25" left of AB) -->
    <circle cx="<?php echo $armhole_AB2_x; ?>" cy="<?php echo $armhole_AB2_y; ?>" r="2"
            fill="#9CA3AF" stroke="none"/>
    <text x="<?php echo $armhole_AB2_x - 18; ?>" y="<?php echo $armhole_AB2_y + 3; ?>"
          font-size="8" fill="#9CA3AF">AB2</text>

    <!-- Point B1 (45° from B) -->
    <circle cx="<?php echo $armhole_B1_x; ?>" cy="<?php echo $armhole_B1_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_B1_x + 5; ?>" y="<?php echo $armhole_B1_y - 5; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">B1</text>
    <!-- Armhole curve length at B1 (total of A→AB2→B1→C) -->
    <text x="<?php echo $armhole_B1_x; ?>" y="<?php echo $armhole_B1_y + 18; ?>"
          font-size="8" fill="#6366F1" text-anchor="middle" font-weight="bold">
        Curve: <?php echo number_format($curveLength, 2); ?>"
    </text>

    <!-- Point C -->
    <circle cx="<?php echo $armhole_C_x; ?>" cy="<?php echo $armhole_C_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_C_x + 5; ?>" y="<?php echo $armhole_C_y + 12; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">C</text>

    <!-- Dimension labels -->
    <!-- armHoleHeight label (A to B distance) -->
    <text x="<?php echo $armhole_A_x + 20; ?>" y="<?php echo ($armhole_A_y + $armhole_B_y) / 2; ?>"
          font-size="7" fill="#9CA3AF" text-anchor="start"
          transform="rotate(90, <?php echo $armhole_A_x + 20; ?>, <?php echo ($armhole_A_y + $armhole_B_y) / 2; ?>)">
        <?php echo number_format($armHoleHeight, 2); ?>"
    </text>

    <!-- armHoleDepth label (B to C distance) -->
    <text x="<?php echo ($armhole_B_x + $armhole_C_x) / 2; ?>" y="<?php echo $armhole_B_y + 15; ?>"
          font-size="7" fill="#9CA3AF" text-anchor="middle">
        <?php echo number_format($armHoleDepth, 2); ?>"
    </text>

    <!-- Node Labels (Dev Mode Only) -->
    <?php foreach ($frontNodes as $name => $node): ?>
        <?php if (!isset($node['color']) || $node['color'] !== 'red'): ?>
        <?php
            $nodeColor = '#10B981';  // Default green
            if (isset($node['color'])) {
                if ($node['color'] === 'gray') $nodeColor = '#9CA3AF';
            }
        ?>
        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                fill="<?php echo $nodeColor; ?>" stroke="#fff" stroke-width="1"/>
        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="<?php echo $nodeColor; ?>" font-weight="300" opacity="0.7"><?php echo $name; ?></text>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Cutting line nodes (red) -->
    <?php foreach ($frontNodes as $name => $node): ?>
        <?php if (isset($node['color']) && $node['color'] === 'red'): ?>
        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                fill="#DC2626" stroke="#fff" stroke-width="1"/>
        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="#DC2626" font-weight="300" opacity="0.7"><?php echo $name; ?></text>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- ========== PATTI PATTERN (below front) ========== -->

    <!-- a2 to a3 (bottom) -->
    <line x1="<?php echo frontNode('a2','x'); ?>" y1="<?php echo frontNode('a2','y'); ?>"
          x2="<?php echo frontNode('a3','x'); ?>" y2="<?php echo frontNode('a3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- a3 to a71 (right side - direct connection) -->
    <line x1="<?php echo frontNode('a3','x'); ?>" y1="<?php echo frontNode('a3','y'); ?>"
          x2="<?php echo frontNode('a71','x'); ?>" y2="<?php echo frontNode('a71','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <?php if ($isDevMode): ?>
    <!-- Patti Node Labels (Dev Mode Only) -->
    <?php foreach ($pattiNodes as $name => $node): ?>
        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                fill="#10B981" stroke="#fff" stroke-width="1"/>
        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="#10B981" font-weight="300" opacity="0.7"><?php echo $name; ?></text>
    <?php endforeach; ?>
    <?php endif; ?>
</svg>
<?php
$frontSvgContent = ob_get_clean();

// =============================================================================
// SECTION 4: EXPORT DATA (For composites & PDF)
// =============================================================================
$frontPatternData = [
    'name' => 'FRONT + PATTI',
    'type' => 'princessCutFront',
    'nodes' => $frontNodes,
    'pattiNodes' => $pattiNodes,
    'svg_content' => $frontSvgContent,
    'dimensions' => [
        'width' => $frontSvgWidth,
        'height' => $frontSvgHeight,
        'widthInches' => $frontSvgWidthInches,
        'heightInches' => $frontSvgHeightInches
    ],
    'bounds' => $frontBounds,
    'patti' => [
        'width' => $pattiWidth,
        'height' => $pattiHeight,
        'originX' => $pattiOriginX,
        'originY' => $pattiOriginY
    ],
    'curves' => [
        'b4_b6' => ['ctrlX' => $b4_b6_ctrlX, 'ctrlY' => $b4_b6_ctrlY],
        'b1_b2' => ['ctrlX' => $b1_b2_ctrlX, 'ctrlY' => $b1_b2_ctrlY],
        'b1_e1' => ['ctrlX' => $b1_e1_ctrlX, 'ctrlY' => $b1_e1_ctrlY],
        'b4_b1' => ['ctrlX' => $b4_b1_ctrlX, 'ctrlY' => $b4_b1_ctrlY],
        'b1_e3' => ['ctrlX' => $b1_e3_ctrlX, 'ctrlY' => $b1_e3_ctrlY],
        'cut_waist' => ['ctrlX' => $cut_waist_ctrl_x, 'ctrlY' => $cut_waist_ctrl_y],
        'cut_neck1' => ['ctrlX' => $cut_neck_ctrl1_x, 'ctrlY' => $cut_neck_ctrl1_y],
        'cut_neck2' => ['ctrlX' => $cut_neck_ctrl2_x, 'ctrlY' => $cut_neck_ctrl2_y]
    ]
];

// =============================================================================
// SECTION 5: STANDALONE PREVIEW (Only if not in composite mode)
// =============================================================================
if (defined('COMPOSITE_MODE')) {
    // Composite is including us - just return, data is set
    return;
}

// Standalone preview mode - render full HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Princess Cut Front - <?php echo htmlspecialchars($customerName); ?></title>
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }
        body { font-family: Arial, sans-serif; padding: 10px; background: #f5f5f5; box-sizing: border-box; }
        .pattern-container { background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; }
        h1 { color: #333; margin: 0 0 5px 0; font-size: 20px; }
        .info { color: #666; margin-bottom: 10px; font-size: 13px; }
        .svg-container { border: 1px solid #ddd; background: white; flex: 1; min-height: 0; }
        .svg-container svg { width: 100%; height: 100%; }

        /* Mode Switcher */
        .mode-switcher { display: flex; gap: 8px; margin-bottom: 8px; }
        .mode-btn { padding: 6px 14px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; border-radius: 4px; font-size: 13px; text-decoration: none; color: #333; }
        .mode-btn:hover { background: #e0e0e0; }
        .mode-btn.active { background: #333; color: white; border-color: #333; }
    </style>
</head>
<body>
    <div class="pattern-container">
        <h1>Princess Cut Front Pattern</h1>

        <!-- Mode Switcher -->
        <div class="mode-switcher">
            <?php
            $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
            $params = $_GET;
            ?>
            <a href="<?php $params['mode'] = 'dev'; echo $baseUrl . '?' . http_build_query($params); ?>"
               class="mode-btn <?php echo $isDevMode ? 'active' : ''; ?>">Dev</a>
            <a href="<?php $params['mode'] = 'print'; echo $baseUrl . '?' . http_build_query($params); ?>"
               class="mode-btn <?php echo $isPrintMode ? 'active' : ''; ?>">Print</a>
        </div>

        <?php if ($isDevMode): ?>
        <div class="info">
            Customer: <?php echo htmlspecialchars($customerName); ?> |
            Bust: <?php echo $bust; ?>" |
            Armhole: <?php echo $armhole; ?>" |
            Patti: <?php echo number_format($pattiWidth, 2); ?>" x <?php echo number_format($pattiHeight, 2); ?>"
        </div>
        <?php endif; ?>

        <div class="svg-container">
            <?php echo $frontSvgContent; ?>
        </div>
    </div>
</body>
</html>
