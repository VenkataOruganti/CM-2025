<?php
/**
 * =============================================================================
 * SAREE BLOUSE FRONT PATTERN
 * =============================================================================
 *
 * Generates the front panel pattern for a saree blouse.
 *
 * MODES:
 * - Standalone: Full HTML preview (default) - for development/testing
 * - Composite:  Returns SVG + data only (when COMPOSITE_MODE defined)
 *
 * USAGE:
 *   Standalone: sariBlouseFront.php?customer_id=123&measurement_id=456&mode=dev
 *   Composite:  define('COMPOSITE_MODE', true); include 'sariBlouseFront.php';
 *
 * EXPORTS (when included):
 *   $frontPatternData - Array with nodes, svg_content, dimensions
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

// Front neck depth calculation (diagonal from a10 to a1)
$a1_horizontal = $shoulderLine_x - $originX;
$a1_diagonal = $frontNeckDepth * $scale;
$a1_vertical = sqrt(pow($a1_diagonal, 2) - pow($a1_horizontal, 2));
$a1_y = $originY + $a1_vertical;

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

// Node 1: Front Neck Depth (diagonal distance from a10 to a1)
$frontNodes['a1'] = [
    'x' => $originX,
    'y' => $originY + $a1_vertical,
    'label' => 'Front Neck',
    'code' => '$a1 = originX, diagonal ' . number_format($frontNeckDepth, 2) . '" from a10'
];

// Node 3: Front Length point (bottom left corner - vertical down from a1)
$frontNodes['a3'] = [
    'x' => $originX - (0.25 * $scale),
    'y' => $originY + (($flength - 1.0) * $scale),
    'label' => 'Front Length',
    'code' => '$a3 = originX - 0.25", a0.y + flength(' . number_format($flength, 2) . '")'
];

// Node 4: Waist curve control point (x = sMid.x - bottomTuckWidth/2, y = a11.y + flength + 0.5")
$frontNodes['a4'] = [
    'x' => $shoulderMid_x - (($bottomTuckWidth / 2) * $scale),
    'y' => $originY + (($flength + 0.5) * $scale),
    'label' => 'a4',
    'code' => '$a4 = sMid.x - (bottomTuckWidth/2), a11.y + flength(' . number_format($flength, 2) . '") + 0.5"'
];

// Node 41: right side of bottom tuck on waist line (symmetric to a4)
// a41.x = shoulderMid_x + (bottomTuckWidth / 2) * scale
$frontNodes['a41'] = [
    'x' => $shoulderMid_x + (($bottomTuckWidth / 2) * $scale),
    'y' => $originY + (($flength + 0.5) * $scale),
    'label' => 'a41',
    'code' => '$a41 = sMid.x + (bottomTuckWidth/2), a4.y'
];

// Node 5: Waist point (originX + qBust * scale, a4.y - 1")
$frontNodes['a5'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $frontNodes['a4']['y'] - (1.0 * $scale),
    'label' => 'Waist',
    'code' => '$a5 = originX + qBust(' . number_format($qBust, 2) . '"), a4.y - 1"'
];

// Node 7: Armhole End (Point C - at bust line level)
$frontNodes['a7'] = [
    'x' => $originX + ($qBust * $scale),
    'y' => $pointC_y,
    'label' => 'Armhole End',
    'code' => '$a7 = originX + qBust(' . number_format($qBust, 2) . '"), pointC_y'
];

// Node 51: 1.5" right of a5, on the same angle as a4-a5 line
$a4_a5_slope = ($frontNodes['a5']['y'] - $frontNodes['a4']['y']) / ($frontNodes['a5']['x'] - $frontNodes['a4']['x']);
$frontNodes['a51'] = [
    'x' => $frontNodes['a5']['x'] + (1.5 * $scale),
    'y' => $frontNodes['a5']['y'] + ($a4_a5_slope * 1.5 * $scale),
    'label' => 'a51',
    'code' => '$a51 = a5.x + 1.5", a5.y + (slope × 1.5")'
];

// Node 71: 1.5" right of a7 (right edge of side box)
$frontNodes['a71'] = [
    'x' => $frontNodes['a7']['x'] + (1.5 * $scale),
    'y' => $frontNodes['a7']['y'],
    'label' => 'a71',
    'code' => '$a71 = a7.x + 1.5", a7.y'
];

// Node 9: Armhole Corner (45° bend - Point B1)
$frontNodes['a9'] = [
    'x' => $pointB1_x,
    'y' => $pointB1_y,
    'label' => 'Armhole Corner',
    'code' => '$a9 = pointB1_x, pointB1_y'
];

// Node 91: Armhole Curve Midpoint (AB2 - on curve between a9 and a10)
$frontNodes['a91'] = [
    'x' => $pointAB2_x,
    'y' => $pointAB2_y,
    'label' => 'Armhole Mid',
    'code' => '$a91 = pointAB2_x, pointAB2_y'
];

// Node 10: Shoulder (top of armhole - Point A)
$frontNodes['a10'] = [
    'x' => $pointA_x,
    'y' => $pointA_y,
    'label' => 'Shoulder',
    'code' => '$a10 = pointA_x, pointA_y'
];

// Node 11: Shoulder Line (top, end of shoulder near neck)
$frontNodes['a11'] = [
    'x' => $pointA_x - ($shoulder * $scale),
    'y' => $originY,
    'label' => 'Shoulder Line',
    'code' => '$a11 = a10.x - shoulder(' . number_format($shoulder, 2) . '"), a0.y'
];

// Node 111: Midpoint between a11 and a1 (for neck curve) - x aligned with a11, y centered
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
    'y' => $originY + ($apex * $scale) - (0.5 * $scale),
    'label' => 'b1',
    'code' => '$b1 = sMid.x, apex - 0.5"'
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
$a3_a4_distance = abs(frontNode('a4', 'x') - frontNode('a3', 'x'));
$waistCurveBulgeRatio = 0.30;
$waistCurveBulgeDepth = $a3_a4_distance * $waistCurveBulgeRatio;
$waistCurveControlPointPosition = 0.35;

$q_ctrl_left_x = frontNode('a3', 'x') + (frontNode('a4', 'x') - frontNode('a3', 'x')) * $waistCurveControlPointPosition;
$q_ctrl_left_y = frontNode('a3', 'y') + (frontNode('a4', 'y') - frontNode('a3', 'y')) * $waistCurveControlPointPosition + $waistCurveBulgeDepth;

// =============================================================================
// BOTTOM TUCK NODE (b3)
// =============================================================================
// Node b3: x = sMid.x (aligned with shoulder midpoint), y = apex + 1"
$frontNodes['b3'] = [
    'x' => $shoulderMid_x,
    'y' => $originY + (($apex + 1) * $scale),
    'label' => 'b3',
    'code' => '$b3 = sMid.x, apex + 1"'
];

// armholeTuck nodes (at a9 - armhole corner, pointing to b1)
// Width of e1-e3 = fixed 0.5" (since a7 is now at armhole end)
$e_tuck_width = 0.5 * $scale;  // 0.5" tuck width in pixels
$e_half_width = $e_tuck_width / 2;  // half the tuck width (from center a9)

// e1: 0.25" above a9 (along the armhole curve direction - toward a10)
$frontNodes['e1'] = [
    'x' => $pointB1_x - ($e_half_width * cos(deg2rad(45))),
    'y' => $pointB1_y - ($e_half_width * sin(deg2rad(45))),
    'label' => 'e1',
    'code' => '$e1 = a9 - 0.25" toward a10 (0.5" total tuck width)'
];

// e2: 1" from b1 toward a9 (along the b1-a9 line)
$e2_dx = $pointB1_x - frontNode('b1','x');
$e2_dy = $pointB1_y - frontNode('b1','y');
$e2_angle = atan2($e2_dy, $e2_dx);
$e2_dist = 1.0 * $scale;
$frontNodes['e2'] = [
    'x' => frontNode('b1','x') + ($e2_dist * cos($e2_angle)),
    'y' => frontNode('b1','y') + ($e2_dist * sin($e2_angle)),
    'label' => 'e2',
    'code' => '$e2 = 1" from b1 toward a9'
];

// e3: 0.25" below a9 (along the armhole curve direction - toward a7)
$frontNodes['e3'] = [
    'x' => $pointB1_x + ($e_half_width * cos(deg2rad(45))),
    'y' => $pointB1_y + ($e_half_width * sin(deg2rad(45))),
    'label' => 'e3',
    'code' => '$e3 = a9 + 0.25" toward a7 (0.5" total tuck width)'
];

// =============================================================================
// CUTTING LINE NODES (r = red line seam allowance)
// Uniform 0.5" offset from pattern, following the a-nodes
// =============================================================================
$seamOffset = 0.5;  // 0.5" uniform offset

// r1: aligned with a3.x, 0.25" below a3 (bottom-left corner)
$frontNodes['r1'] = [
    'x' => frontNode('a3','x'),
    'y' => frontNode('a3','y') + (0.25 * $scale),
    'label' => 'r1',
    'color' => 'red',
    'code' => '$r1 = a3.x, a3.y + 0.25"'
];

// r2: 0.5" below a4 (waist curve peak)
$frontNodes['r2'] = [
    'x' => frontNode('a4','x'),
    'y' => frontNode('a4','y') + (0.25 * $scale),
    'label' => 'r2',
    'color' => 'red',
    'code' => '$r2 = a4 + 0.25" down'
];

// r21: Between r2 and r3, at a41.x, r2.y
$frontNodes['r21'] = [
    'x' => frontNode('a41','x'),
    'y' => frontNode('r2','y'),
    'label' => 'r21',
    'color' => 'red',
    'code' => '$r21 = a41.x, r2.y'
];

// r3: a51.x + 0.25" (1.75" right of a5.x), 0.25" down from a51 (waist right)
$frontNodes['r3'] = [
    'x' => frontNode('a51','x'),
    'y' => frontNode('a51','y') + (0.25 * $scale),
    'label' => 'r3',
    'color' => 'red',
    'code' => '$r3 = a51.x, a51.y + 0.25"'
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
$cutShiftX = 0.25 * $scale;  // 0.25" to the right
$cutShiftY = 0.25 * $scale;  // 0.25" up (negative Y in SVG)

// r6: a7 (armhole end/C) + 0.25" right, 0.25" up
$frontNodes['r6'] = [
    'x' => frontNode('a7','x') + $cutShiftX,
    'y' => frontNode('a7','y') - $cutShiftY,
    'label' => 'r6',
    'color' => 'red',
    'code' => '$r6 = a7.x + 0.25", a7.y - 0.25"'
];

// r_a9: a9 (B1 corner at 45°) + 0.25" right, 0.25" up
$frontNodes['r_a9'] = [
    'x' => frontNode('a9','x') + $cutShiftX,
    'y' => frontNode('a9','y') - $cutShiftY,
    'label' => 'r_a9',
    'color' => 'red',
    'code' => '$r_a9 = a9.x + 0.25", a9.y - 0.25"'
];

// r_a91: a91 (AB2 curve midpoint) + 0.25" right, 0.25" up
$frontNodes['r_a91'] = [
    'x' => frontNode('a91','x') + $cutShiftX,
    'y' => frontNode('a91','y') - $cutShiftY,
    'label' => 'r_a91',
    'color' => 'red',
    'code' => '$r_a91 = a91.x + 0.25", a91.y - 0.25"'
];

// r7: a10 (shoulder/armhole top/A) + 0.25" right, 0.25" up
$frontNodes['r7'] = [
    'x' => frontNode('a10','x') + $cutShiftX,
    'y' => frontNode('a10','y') - $cutShiftY,
    'label' => 'r7',
    'color' => 'red',
    'code' => '$r7 = a10.x + 0.25", a10.y - 0.25"'
];

// r8: Offset perpendicular to shoulder line (a11→a10), 0.5" away from a11, minus 0.5" along shoulder toward neck
// Uses same perpendicular and along directions calculated for r7
$frontNodes['r8'] = [
    'x' => frontNode('a11','x') + ($perp_x * $r_offset) - ($along_x * $extend_offset) - ($along_x * 0.25 * $scale),
    'y' => frontNode('a11','y') + ($perp_y * $r_offset) - ($along_y * $extend_offset) - ($along_y * 0.25 * $scale),
    'label' => 'r8',
    'color' => 'red',
    'code' => '$r8 = a11 + 0.5" perp - 0.5" along shoulder'
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
$pattiOriginX = frontNode('a3', 'x');  // Follow a3.x position
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

// Get a3, a4, a5 offsets from front nodes for patti shape
$p_a4_x_offset = frontNode('a4', 'x') - frontNode('a3', 'x');
$p_a4_y_offset = frontNode('a4', 'y') - frontNode('a3', 'y');
$p_a5_x_offset = frontNode('a5', 'x') - frontNode('a3', 'x');
$p_a5_y_offset = frontNode('a5', 'y') - frontNode('a3', 'y');

// p1 = top-left (corresponds to a3 position)
$pattiNodes['p1'] = [
    'x' => $pattiOriginX,
    'y' => $pattiOriginY,
    'label' => 'p1',
    'code' => '$p1 = patti origin (a3 position)'
];

// p2 = top curve point (corresponds to a4 position)
$pattiNodes['p2'] = [
    'x' => $pattiOriginX + $p_a4_x_offset,
    'y' => $pattiOriginY + $p_a4_y_offset,
    'label' => 'p2',
    'code' => '$p2 = a4 position (offset from a3)'
];

// p21 = between p2 and p3, at a41.x (corresponds to a41 position)
$p_a41_x_offset = frontNode('a41', 'x') - frontNode('a3', 'x');
$p_a41_y_offset = frontNode('a41', 'y') - frontNode('a3', 'y');
$pattiNodes['p21'] = [
    'x' => $pattiOriginX + $p_a41_x_offset,
    'y' => $pattiOriginY + $p_a41_y_offset,
    'label' => 'p21',
    'code' => '$p21 = a41 position (offset from a3)'
];

// p3 = top-right (aligned with a51.x)
$pattiNodes['p3'] = [
    'x' => frontNode('a51', 'x'),
    'y' => $originY + (($flength + $pattiGap) * $scale) - (0.3 * $scale),
    'label' => 'p3',
    'code' => '$p3 = a51.x, originY + flength - 0.3"'
];

// p4 = bottom-left
$pattiNodes['p4'] = [
    'x' => $originX,
    'y' => $pattiNodes['p2']['y'] + ($pattiHeight * $scale),
    'label' => 'p4',
    'code' => '$p4 = originX, p2.y + pattiHeight'
];

// p41 = between p4 and p5, at a41.x, p4.y
$pattiNodes['p41'] = [
    'x' => frontNode('a41', 'x'),
    'y' => $pattiNodes['p4']['y'],
    'label' => 'p41',
    'code' => '$p41 = a41.x, p4.y'
];

// p5 = bottom-right (aligned with p3.x), 0.5" higher than p4
$pattiNodes['p5'] = [
    'x' => frontNode('a51', 'x'),
    'y' => $pattiNodes['p2']['y'] + ($pattiHeight * $scale) - (0.5 * $scale),
    'label' => 'p5',
    'code' => '$p5 = a51.x, p2.y + pattiHeight - 0.5"'
];

// Node b5: x = a4.x, y = p4.y (on the bottom line of patti)
$frontNodes['b5'] = [
    'x' => frontNode('a4', 'x'),
    'y' => $pattiNodes['p4']['y'],
    'label' => 'b5',
    'code' => '$b5 = a4.x, p4.y'
];

// Vertical guide line position for patti
$patti_a7_a3_distance = frontNode('a7', 'x') - frontNode('a3', 'x');
$pattiVerticalLineX = pattiNode('p1', 'x') + $patti_a7_a3_distance;

// Patti waist curve control points (matching a3→a4 curvature)
// Use same bulge ratio and control point position as front pattern
$patti_p1_p2_distance = abs(pattiNode('p2', 'x') - pattiNode('p1', 'x'));
$pattiWaistCurveBulgeDepth = $patti_p1_p2_distance * $waistCurveBulgeRatio;
$p_ctrl_x = pattiNode('p1', 'x') + (pattiNode('p2', 'x') - pattiNode('p1', 'x')) * $waistCurveControlPointPosition;
$p_ctrl_y = pattiNode('p1', 'y') + (pattiNode('p2', 'y') - pattiNode('p1', 'y')) * $waistCurveControlPointPosition + $pattiWaistCurveBulgeDepth;

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
// HELPER FUNCTIONS FOR RENDERING
// =============================================================================

// Snip Icon helper
if (!function_exists('snipIcon')) {
    function snipIcon($refNumber, $label, $nodeGetter, $nodeName, $angle, $size = 0.225, $offsetX = 0, $offsetY = 0) {
        global $scale, $isPrintMode, $isDevMode;
        $tipX = call_user_func($nodeGetter, $nodeName, 'x') + ($offsetX * $scale);
        $tipY = call_user_func($nodeGetter, $nodeName, 'y') + ($offsetY * $scale);
        if ($tipX === null || $tipY === null) return '';

        $sizeScaled = $size * $scale;
        switch($angle) {
            case 0: case 360:
                $x1 = $tipX; $y1 = $tipY;
                $x2 = $tipX - $sizeScaled; $y2 = $tipY - ($sizeScaled / 2);
                $x3 = $tipX - $sizeScaled; $y3 = $tipY + ($sizeScaled / 2);
                $labelX = $tipX - $sizeScaled - (0.15 * $scale); $labelY = $tipY;
                break;
            case 90:
                $x1 = $tipX; $y1 = $tipY;
                $x2 = $tipX - ($sizeScaled / 2); $y2 = $tipY - $sizeScaled;
                $x3 = $tipX + ($sizeScaled / 2); $y3 = $tipY - $sizeScaled;
                $labelX = $tipX; $labelY = $tipY - $sizeScaled - (0.15 * $scale);
                break;
            case 180:
                $x1 = $tipX; $y1 = $tipY;
                $x2 = $tipX + $sizeScaled; $y2 = $tipY - ($sizeScaled / 2);
                $x3 = $tipX + $sizeScaled; $y3 = $tipY + ($sizeScaled / 2);
                $labelX = $tipX + $sizeScaled + (0.15 * $scale); $labelY = $tipY;
                break;
            case 270:
                $x1 = $tipX; $y1 = $tipY;
                $x2 = $tipX - ($sizeScaled / 2); $y2 = $tipY + $sizeScaled;
                $x3 = $tipX + ($sizeScaled / 2); $y3 = $tipY + $sizeScaled;
                $labelX = $tipX; $labelY = $tipY + $sizeScaled + (0.15 * $scale);
                break;
            default: return '';
        }

        $svg = '<g class="snip-marker">';
        $svg .= sprintf('<line class="snip-triangle" x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333333" stroke-width="0.5"/>', $x1, $y1, $x2, $y2);
        $svg .= sprintf('<line class="snip-triangle" x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333333" stroke-width="0.5"/>', $x1, $y1, $x3, $y3);
        // Show label only in dev mode
        if ($isDevMode) {
            $svg .= sprintf('<text class="snip-label" x="%.2f" y="%.2f" text-anchor="middle" dominant-baseline="middle" font-size="8px" fill="black">%s</text>', $labelX, $labelY, htmlspecialchars($nodeName));
        }
        $svg .= '</g>';
        return $svg;
    }
}

// Scissors Icon helper
if (!function_exists('scissorsIcon')) {
    function scissorsIcon($x, $y, $rotation = 0, $size = 0.5, $color = '#333333', $label = '') {
        global $scale, $isDevMode;
        $sizeScaled = $size * $scale;
        $scissorsPath = 'M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3h-3z';
        $pathScale = $sizeScaled / 24;
        $svg = sprintf('<g transform="translate(%.2f, %.2f) rotate(%d) scale(%.4f) translate(-12, -12)">', $x, $y, $rotation, $pathScale);
        $svg .= sprintf('<path d="%s" fill="%s"/>', $scissorsPath, $color);
        $svg .= '</g>';
        // Add label if provided (8px font-size, black color) - only in dev mode
        if (!empty($label) && $isDevMode) {
            $labelOffsetY = $sizeScaled + 3; // Position label below scissors icon
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="8px" fill="#000000" text-anchor="middle" font-family="Arial, sans-serif">%s</text>', $x, $y + $labelOffsetY, htmlspecialchars($label));
        }
        return $svg;
    }
}

// Grainline helper
if (!function_exists('grainLine')) {
    function grainLine($x, $y, $length, $orientation = 'vertical') {
        global $scale;
        $lengthScaled = $length * $scale;
        $arrowSize = 0.15 * $scale;

        if ($orientation === 'vertical') {
            $x1 = $x; $y1 = $y - ($lengthScaled / 2);
            $x2 = $x; $y2 = $y + ($lengthScaled / 2);
            $svg = '<g class="grainline">';
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x2, $y2);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 - $arrowSize, $y1 + $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 + $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 - $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 + $arrowSize, $y2 - $arrowSize);
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="%.2f" font-family="Arial, sans-serif" fill="#000" text-anchor="middle" transform="rotate(-90 %.2f %.2f)">GRAINLINE</text>', $x + (0.25 * $scale), $y, 0.15 * $scale, $x + (0.25 * $scale), $y);
            $svg .= '</g>';
        } else {
            $x1 = $x - ($lengthScaled / 2); $y1 = $y;
            $x2 = $x + ($lengthScaled / 2); $y2 = $y;
            $svg = '<g class="grainline">';
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x2, $y2);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 - $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x1, $y1, $x1 + $arrowSize, $y1 + $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 - $arrowSize);
            $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#000" stroke-width="0.5"/>', $x2, $y2, $x2 - $arrowSize, $y2 + $arrowSize);
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="%.2f" font-family="Arial, sans-serif" fill="#000" text-anchor="middle">GRAINLINE</text>', $x, $y - (0.25 * $scale), 0.15 * $scale);
            $svg .= '</g>';
        }
        return $svg;
    }
}

// =============================================================================
// SECTION 3: SVG GENERATION
// =============================================================================

// Calculate cutting line control points
$seamOffsetPx = 0.5 * $scale;
// Use 0.25" offset for waist curve to match r1-r2 offset
$cut_waist_ctrl_x = $q_ctrl_left_x;
$cut_waist_ctrl_y = $q_ctrl_left_y + (0.25 * $scale);
$cut_neck_ctrl1_x = frontNode('a111','x') - $seamOffsetPx;
$cut_neck_ctrl1_y = frontNode('a1','y');
$cut_neck_ctrl2_x = frontNode('a111','x') - $seamOffsetPx;

// Armhole cutting line curve - exact copy shifted 0.25" right and 0.25" up
$cut_ctrl3_x = $ctrl3_x + $cutShiftX;
$cut_ctrl3_y = $ctrl3_y - $cutShiftY;
$cut_ctrl1_x = $ctrl1_x + $cutShiftX;
$cut_ctrl1_y = $ctrl1_y - $cutShiftY;
$cut_ctrl2a_x = $ctrl2a_x + $cutShiftX;
$cut_ctrl2a_y = $ctrl2a_y - $cutShiftY;
$cut_ctrl2b_x = $ctrl2b_x + $cutShiftX;
$cut_ctrl2b_y = $ctrl2b_y - $cutShiftY;
$cut_neck_ctrl2_y = frontNode('a11','y');

// Calculate tuck center points
$b_center_x = (frontNode('a4','x') + frontNode('a41','x')) / 2;
$b_center_y = frontNode('a4','y');

// Generate SVG content
ob_start();
?>
<svg id="frontPatternSvg" width="100%" height="100%"
     viewBox="<?php echo $frontBounds['minX']; ?> <?php echo $frontBounds['minY']; ?> <?php echo $frontBounds['width']; ?> <?php echo $frontBounds['height']; ?>"
     preserveAspectRatio="xMidYMid meet"
     xmlns="http://www.w3.org/2000/svg">
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

    <!-- Center front line: a1 to a3 -->
    <line x1="<?php echo frontNode('a1','x'); ?>" y1="<?php echo frontNode('a1','y'); ?>"
          x2="<?php echo frontNode('a3','x'); ?>" y2="<?php echo frontNode('a3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Waist line: curve a3 → a4, line a4 → a41, line a41 → a5 -->
    <path d="M <?php echo frontNode('a4','x'); ?>,<?php echo frontNode('a4','y'); ?>
             Q <?php echo $q_ctrl_left_x; ?>,<?php echo $q_ctrl_left_y; ?>
               <?php echo frontNode('a3','x'); ?>,<?php echo frontNode('a3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <line x1="<?php echo frontNode('a4','x'); ?>" y1="<?php echo frontNode('a4','y'); ?>"
          x2="<?php echo frontNode('a41','x'); ?>" y2="<?php echo frontNode('a41','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo frontNode('a41','x'); ?>" y1="<?php echo frontNode('a41','y'); ?>"
          x2="<?php echo frontNode('a5','x'); ?>" y2="<?php echo frontNode('a5','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- BOTTOM TUCK: b3 → a41 (black solid line) -->
    <line x1="<?php echo frontNode('b3','x'); ?>" y1="<?php echo frontNode('b3','y'); ?>"
          x2="<?php echo frontNode('a41','x'); ?>" y2="<?php echo frontNode('a41','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- PRINCESS CUT LINE: a4 → b1 → e1 (black solid line) -->
    <line x1="<?php echo frontNode('a4','x'); ?>" y1="<?php echo frontNode('a4','y'); ?>"
          x2="<?php echo frontNode('b1','x'); ?>" y2="<?php echo frontNode('b1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <?php
    // Curve from b1 to e1 with 0.5" bulge to the left
    $b1_e1_midX = (frontNode('b1','x') + frontNode('e1','x')) / 2;
    $b1_e1_midY = (frontNode('b1','y') + frontNode('e1','y')) / 2;
    $b1_e1_ctrlX = $b1_e1_midX - (0.5 * $scale);  // 0.5" to the left
    $b1_e1_ctrlY = $b1_e1_midY;
    ?>
    <path d="M <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>
             Q <?php echo $b1_e1_ctrlX; ?>,<?php echo $b1_e1_ctrlY; ?>
               <?php echo frontNode('e1','x'); ?>,<?php echo frontNode('e1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- CONNECTING LINE: b3 → b1 → e3 (black solid line) -->
    <?php
    // Curve from b3 to b1 with 0.25" bulge to the left
    $b3_b1_midX = (frontNode('b3','x') + frontNode('b1','x')) / 2;
    $b3_b1_midY = (frontNode('b3','y') + frontNode('b1','y')) / 2;
    $b3_b1_ctrlX = $b3_b1_midX - (0.25 * $scale);  // 0.25" to the left
    $b3_b1_ctrlY = $b3_b1_midY;
    ?>
    <path d="M <?php echo frontNode('b3','x'); ?>,<?php echo frontNode('b3','y'); ?>
             Q <?php echo $b3_b1_ctrlX; ?>,<?php echo $b3_b1_ctrlY; ?>
               <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <?php
    // Curve from b1 to e3 with 0.5" bulge to the left
    $b1_e3_midX = (frontNode('b1','x') + frontNode('e3','x')) / 2;
    $b1_e3_midY = (frontNode('b1','y') + frontNode('e3','y')) / 2;
    $b1_e3_ctrlX = $b1_e3_midX - (0.5 * $scale);  // 0.5" to the left
    $b1_e3_ctrlY = $b1_e3_midY;
    ?>
    <path d="M <?php echo frontNode('b1','x'); ?>,<?php echo frontNode('b1','y'); ?>
             Q <?php echo $b1_e3_ctrlX; ?>,<?php echo $b1_e3_ctrlY; ?>
               <?php echo frontNode('e3','x'); ?>,<?php echo frontNode('e3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Side seam: a5 to a7 (armhole end) -->
    <line x1="<?php echo frontNode('a5','x'); ?>" y1="<?php echo frontNode('a5','y'); ?>"
          x2="<?php echo frontNode('a7','x'); ?>" y2="<?php echo frontNode('a7','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Side box: a7 -> a71 -> a51 -> a5 (1" wide rectangle) -->
    <line x1="<?php echo frontNode('a7','x'); ?>" y1="<?php echo frontNode('a7','y'); ?>"
          x2="<?php echo frontNode('a71','x'); ?>" y2="<?php echo frontNode('a71','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo frontNode('a71','x'); ?>" y1="<?php echo frontNode('a71','y'); ?>"
          x2="<?php echo frontNode('a51','x'); ?>" y2="<?php echo frontNode('a51','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo frontNode('a51','x'); ?>" y1="<?php echo frontNode('a51','y'); ?>"
          x2="<?php echo frontNode('a5','x'); ?>" y2="<?php echo frontNode('a5','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Ease text (in side box - centered between a7-a5 and a71-a51) -->
    <?php
    // Horizontal center: midpoint between left edge (a7/a5) and right edge (a71/a51)
    $easeTextX = (frontNode('a7', 'x') + frontNode('a71', 'x')) / 2;
    // Vertical center: midpoint between top (a7/a71) and bottom (a5/a51)
    $easeTextY = (frontNode('a7', 'y') + frontNode('a5', 'y')) / 2;
    ?>
    <text x="<?php echo $easeTextX; ?>" y="<?php echo $easeTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(90, <?php echo $easeTextX; ?>, <?php echo $easeTextY; ?>)">---- ease -----</text>

    <!-- CUTTING LINE (RED) - armhole is exact copy of A→AB2→B1→C shifted 0.25" right and 0.25" up -->
    <path d="M <?php echo frontNode('r1','x'); ?>,<?php echo frontNode('r1','y'); ?>
             Q <?php echo $cut_waist_ctrl_x; ?>,<?php echo $cut_waist_ctrl_y; ?>
               <?php echo frontNode('r2','x'); ?>,<?php echo frontNode('r2','y'); ?>
             L <?php echo frontNode('r21','x'); ?>,<?php echo frontNode('r21','y'); ?>
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
    <?php
    // Position grainline between b1 and sMid points
    $grainX = (frontNode('b1', 'x') + frontNode('sMid', 'x')) / 2; // Midpoint between b1 and sMid horizontally
    $grainY = (frontNode('b1', 'y') + frontNode('sMid', 'y')) / 2; // Midpoint between b1 and sMid vertically
    $grainLength = 4; // 4 inches long
    echo grainLine($grainX, $grainY, $grainLength, 'vertical');
    ?>

    <?php if ($isPrintMode): ?>
    <!-- X marker at a7 (armhole end) - Print Mode Only -->
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
    <!-- Patti black pattern outline -->
    <!-- Top curve: p1 -> p2 with control point -->
    <path d="M <?php echo pattiNode('p2','x'); ?>,<?php echo pattiNode('p2','y'); ?>
             Q <?php echo $p_ctrl_x; ?>,<?php echo $p_ctrl_y; ?>
               <?php echo pattiNode('p1','x'); ?>,<?php echo pattiNode('p1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- p2 to p21 to p3 (straight lines) -->
    <line x1="<?php echo pattiNode('p2','x'); ?>" y1="<?php echo pattiNode('p2','y'); ?>"
          x2="<?php echo pattiNode('p21','x'); ?>" y2="<?php echo pattiNode('p21','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo pattiNode('p21','x'); ?>" y1="<?php echo pattiNode('p21','y'); ?>"
          x2="<?php echo pattiNode('p3','x'); ?>" y2="<?php echo pattiNode('p3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- p3 to p5 (right side) -->
    <line x1="<?php echo pattiNode('p3','x'); ?>" y1="<?php echo pattiNode('p3','y'); ?>"
          x2="<?php echo pattiNode('p5','x'); ?>" y2="<?php echo pattiNode('p5','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- p5 to p41 to p4 (bottom) -->
    <line x1="<?php echo pattiNode('p5','x'); ?>" y1="<?php echo pattiNode('p5','y'); ?>"
          x2="<?php echo pattiNode('p41','x'); ?>" y2="<?php echo pattiNode('p41','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo pattiNode('p41','x'); ?>" y1="<?php echo pattiNode('p41','y'); ?>"
          x2="<?php echo pattiNode('p4','x'); ?>" y2="<?php echo pattiNode('p4','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- p4 to p1 (left side) -->
    <line x1="<?php echo pattiNode('p4','x'); ?>" y1="<?php echo pattiNode('p4','y'); ?>"
          x2="<?php echo pattiNode('p1','x'); ?>" y2="<?php echo pattiNode('p1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Patti Grainline (centered between p4 and p5) -->
    <?php
    $pattiGrainX = (pattiNode('p4', 'x') + pattiNode('p5', 'x')) / 2;
    $pattiGrainY = (pattiNode('p2', 'y') + pattiNode('p4', 'y')) / 2;
    $pattiGrainLength = 3;
    echo grainLine($pattiGrainX, $pattiGrainY, $pattiGrainLength, 'horizontal');
    ?>

    <!-- Patti red cutting lines at a4.x and a41.x positions -->
    <!-- Vertical line at a4.x (from p2.y to b5.y) -->
    <line x1="<?php echo frontNode('a4','x'); ?>" y1="<?php echo pattiNode('p2','y'); ?>"
          x2="<?php echo frontNode('a4','x'); ?>" y2="<?php echo frontNode('b5','y'); ?>"
          stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>

    <!-- Vertical line at a41.x (from p2.y to b5.y) -->
    <line x1="<?php echo frontNode('a41','x'); ?>" y1="<?php echo pattiNode('p2','y'); ?>"
          x2="<?php echo frontNode('a41','x'); ?>" y2="<?php echo frontNode('b5','y'); ?>"
          stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>

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
    'type' => 'sariBlouseFront',
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
    <title>Blouse Front - <?php echo htmlspecialchars($customerName); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .pattern-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 10px; }
        .info { color: #666; margin-bottom: 20px; font-size: 14px; }
        .svg-container { border: 1px solid #ddd; background: white; overflow: auto; }

        /* Mode Switcher */
        .mode-switcher { display: flex; gap: 8px; margin-bottom: 15px; }
        .mode-btn { padding: 8px 16px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; border-radius: 4px; font-size: 14px; text-decoration: none; color: #333; }
        .mode-btn:hover { background: #e0e0e0; }
        .mode-btn.active { background: #333; color: white; border-color: #333; }
    </style>
</head>
<body>
    <div class="pattern-container">
        <h1>Blouse Front Pattern</h1>

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
