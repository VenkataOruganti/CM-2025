<?php
/**
 * =============================================================================
 * SAREE BLOUSE BACK PATTERN
 * =============================================================================
 *
 * Generates the back panel pattern for a saree blouse.
 *
 * MODES:
 * - Standalone: Full HTML preview (default) - for development/testing
 * - Composite:  Returns SVG + data only (when COMPOSITE_MODE defined)
 *
 * USAGE:
 *   Standalone: sariBlouseBack.php?customer_id=123&measurement_id=456&mode=dev
 *   Composite:  define('COMPOSITE_MODE', true); include 'sariBlouseBack.php';
 *
 * EXPORTS (when included):
 *   $backPatternData - Array with nodes, svg_content, dimensions
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
// B-B1 distance: 1.5" for bust > 30, 1.25" for bust ≤ 30
$backBottomCurve = ($bust > 30) ? 1.5 : 1.25;

// Calculate armhole - this also calculates $backArmHoleHeight in deepNeck.php
// The back A-B height is calculated centrally and exported as a global variable
calculateArmhole($backBottomCurve, $originX, $originY);

// Back armhole path: z8 → z71 (line) → B1 → z6 (smooth curves passing through B1)
// z8 = pointA (shoulder), z71 = midpoint, B1 = 45° point, z6 = pointC (armhole end)
// $backArmHoleHeight is now set by calculateArmhole() in deepNeck.php

// Target armhole length (half of customer-provided armhole) - for display only
$backTargetArmhole = ($armhole / 2) * $scale;

// Calculate final points with adjusted height
// Point z8 (A): top of armhole, 0.25" below origin
// If back neck depth > 8", shoulder aligns with origin (no 0.25" drop)
$back_z8_x = $originX + ($halfShoulder * $scale);
$back_z8_y = ($bnDepth > 8) ? $originY : $originY + (0.25 * $scale);

// Point B: at backArmHoleHeight from origin (B-C position adjusted by curve fitting)
$back_B_x = $back_z8_x;
$back_B_y = $originY + ($backArmHoleHeight * $scale);

// Point z71 (midpoint between z8 and B)
$z71_x = $back_z8_x;
$z71_y = ($back_z8_y + $back_B_y) / 2;

// Point B1 (45° from B) - curve passes THROUGH this point
$b1_offset_x = $backBottomCurve * cos(deg2rad(45)) * $scale;
$b1_offset_y = $backBottomCurve * sin(deg2rad(45)) * $scale;
$back_B1_x = $back_B_x + $b1_offset_x;
$back_B1_y = $back_B_y - $b1_offset_y;

// Point z6 (end of armhole)
$back_z6_x = $originX + (($halfShoulder + $armHoleDepth) * $scale);
$back_z6_y = $back_B_y;

// Calculate control points for cubic bezier that passes THROUGH B1
// Using parametric constraint: at t=0.5, the curve passes through B1
// Constraints: C1_x = z71_x (starts vertical), C2_y = z6_y (ends horizontal)
//
// At t=0.5: B1 = 0.125*z71 + 0.375*C1 + 0.375*C2 + 0.125*z6
// Solving for C1_y and C2_x:
$t = 0.5;
$t1 = 1 - $t;
$coef_start = $t1 * $t1 * $t1;           // 0.125
$coef_c1 = 3 * $t1 * $t1 * $t;           // 0.375
$coef_c2 = 3 * $t1 * $t * $t;            // 0.375
$coef_end = $t * $t * $t;                // 0.125

// C1_x = z71_x (vertical tangent at start)
$ctrl1_x = $z71_x;
// C2_y = z6_y (horizontal tangent at end)
$ctrl2_y = $back_z6_y;

// Solve for C2_x from B1_x constraint
// B1_x = coef_start*z71_x + coef_c1*C1_x + coef_c2*C2_x + coef_end*z6_x
// C2_x = (B1_x - coef_start*z71_x - coef_c1*C1_x - coef_end*z6_x) / coef_c2
$ctrl2_x = ($back_B1_x - $coef_start*$z71_x - $coef_c1*$ctrl1_x - $coef_end*$back_z6_x) / $coef_c2;

// Solve for C1_y from B1_y constraint
// B1_y = coef_start*z71_y + coef_c1*C1_y + coef_c2*C2_y + coef_end*z6_y
// C1_y = (B1_y - coef_start*z71_y - coef_c2*C2_y - coef_end*z6_y) / coef_c1
$ctrl1_y = ($back_B1_y - $coef_start*$z71_y - $coef_c2*$ctrl2_y - $coef_end*$back_z6_y) / $coef_c1;

// Build armhole path: z8 → z71 (line) → z6 (smooth cubic curve through B1)
$armholeSvgPath = sprintf(
    'M %.2f,%.2f L %.2f,%.2f C %.2f,%.2f %.2f,%.2f %.2f,%.2f',
    $back_z8_x, $back_z8_y,              // z8 (start)
    $z71_x, $z71_y,                       // z71 (line to)
    $ctrl1_x, $ctrl1_y,                   // control point 1 (below z71)
    $ctrl2_x, $ctrl2_y,                   // control point 2 (left of z6)
    $back_z6_x, $back_z6_y                // z6 (end)
);

// Update global point variables for node display
$pointB1_x = $back_B1_x;
$pointB1_y = $back_B1_y;
$pointC_x = $back_z6_x;
$pointC_y = $back_z6_y;
$pointAB_y = $z71_y;

// =============================================================================
// SECTION 2: BACK PATTERN CALCULATIONS
// =============================================================================

// Pre-calculate values needed for nodes
$shoulderLine_x = $pointA_x - ($shoulder * $scale);

// Back neck depth calculation (diagonal from z9 to z1)
$z1_horizontal = $shoulderLine_x - $originX;
$z1_diagonal = $bnDepth * $scale;
$z1_vertical = sqrt(pow($z1_diagonal, 2) - pow($z1_horizontal, 2));
$z1_y = $originY + $z1_vertical;

// Shoulder Mid x-coordinate
$shoulderMid_x = (($pointA_x - ($shoulder * $scale)) + $pointA_x) / 2;
$shoulderMid_y = ($originY + $pointA_y) / 2;

// Bottom tuck center (horizontally centered between z2 and z3)
// z2.x = originX, z3.x = originX + (bust/4) * scale
$tuckCenter_x = ($originX + ($originX + (($bust / 4) * $scale))) / 2;

// Bottom tuck calculations (centered between z2 and z3)
$b2_x = $tuckCenter_x - (($bottomTuckWidth / 2) * $scale);
$b4_x = $tuckCenter_x + (($bottomTuckWidth / 2) * $scale);

// =============================================================================
// BACK PATTERN NODES (z prefix)
// =============================================================================
$backNodes = [];

// Calculate z9.y early so other nodes can reference it
// If back neck depth > 8", add 0.10" drop (shared formula in deepNeck.php)
$back_z9_y = $originY + (getShoulderLineYOffset($bnDepth) * $scale);

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
    'y' => $z1_y,
    'label' => 'Back Neck',
    'code' => '$z1 = originY + bnDepth (back neck length from z9 to z1)'
];

// Back Node 2: Back Length point (bottom left corner)
$backNodes['z2'] = [
    'x' => $originX,
    'y' => $back_z9_y + ($blength * $scale),
    'label' => 'Back Length',
    'code' => '$z2 = z0.x, z9.y + blength'
];

// Back Node 3: Waist point (bust/4 from origin), 0.5" above z2
$backNodes['z3'] = [
    'x' => $originX + (($bust / 4) * $scale),
    'y' => $backNodes['z2']['y'] - (0.5 * $scale),
    'label' => 'Waist',
    'code' => '$z3 = originX + (bust/4), z2.y - 0.5"'
];

// Back Node 5: Intersection of armhole level and bust line - using curve-fitted y
$backNodes['z5'] = [
    'x' => $originX + (($bust / 4) * $scale),
    'y' => $back_z6_y,
    'label' => 'z5',
    'code' => '$z5 = originX + (bust/4), back_z6_y (curve-fitted)'
];

// Back Node 31: 1.5" right of z3 (bottom of side box), same y as z3
$backNodes['z31'] = [
    'x' => $backNodes['z3']['x'] + (1.5 * $scale),
    'y' => $backNodes['z3']['y'],
    'label' => 'z31',
    'code' => '$z31 = z3.x + 1.5", z3.y (0.5" above z2)'
];

// Back Node 51: 1.5" right of z5 (top of side box)
$backNodes['z51'] = [
    'x' => $backNodes['z5']['x'] + (1.5 * $scale),
    'y' => $backNodes['z5']['y'],
    'label' => 'z51',
    'code' => '$z51 = z5.x + 1.5", z5.y'
];

// Back Node 6: Armhole End (Point C) - using curve-fitted values
$backNodes['z6'] = [
    'x' => $back_z6_x,
    'y' => $back_z6_y,
    'label' => 'Armhole End',
    'code' => '$z6 = back_z6_x, back_z6_y (curve-fitted)'
];

// Back Node 8: Shoulder (top of armhole - Point A) - anchored to origin, not z9
$z8_yOffset = round(($back_z8_y - $originY) / $scale, 2);
$backNodes['z8'] = [
    'x' => $back_z8_x,
    'y' => $back_z8_y,
    'label' => 'Shoulder',
    'code' => '$z8 = z0.x + shoulder/2, z0.y + ' . number_format($z8_yOffset, 2) . '"'
];

// Back Node 71: Midpoint between z8 and B (vertically aligned with z8)
$backNodes['z71'] = [
    'x' => $z71_x,
    'y' => $z71_y,
    'label' => 'z71',
    'code' => '$z71 = z8.x, midpoint(z8.y, B.y)'
];

// Back Node B1: 45° point from corner B (transition point in armhole curve) - using curve-fitted values
$backNodes['zB1'] = [
    'x' => $back_B1_x,
    'y' => $back_B1_y,
    'label' => 'B1',
    'code' => '$zB1 = back_B1_x, back_B1_y (45° from B, curve-fitted)'
];

// Back Node 9: Shoulder Line (top, end of shoulder near neck)
$backNodes['z9'] = [
    'x' => $pointA_x - ($shoulder * $scale),
    'y' => $back_z9_y,
    'label' => 'Shoulder Line',
    'code' => '$z9 = z8.x - shoulder, y = originY + 0.10" if bnDepth > 8"'
];

// Back Node 91: Midpoint between z9 and z1 (for neck curve)
$backNodes['z91'] = [
    'x' => $shoulderLine_x + (0.25 * $scale),
    'y' => ($backNodes['z1']['y'] + $backNodes['z9']['y']) / 2,
    'label' => 'z91',
    'code' => '$z91 = z9.x + 0.25", (z1.y + z9.y) / 2'
];

// Back Shoulder Mid: Reference point (gray)
$backNodes['zMid'] = [
    'x' => $shoulderMid_x,
    'y' => $shoulderMid_y,
    'label' => 'Shoulder Mid',
    'color' => 'gray',
    'code' => '$zMid = midpoint(z9, z8)'
];

// Back tuck nodes (horizontally centered between z2 and z3)
// Y values interpolated along the z2-z3 angled line (z2.y to z3.y)
// Line slope: from z2 (originX, z2.y) to z3 (originX + bust/4, z2.y - 0.5")
$z2_x = $backNodes['z2']['x'];
$z2_y = $backNodes['z2']['y'];
$z3_x = $backNodes['z3']['x'];
$z3_y = $backNodes['z3']['y'];

// Helper function to interpolate y along z2-z3 line based on x position
$interpolateY = function($x) use ($z2_x, $z2_y, $z3_x, $z3_y) {
    $t = ($x - $z2_x) / ($z3_x - $z2_x);  // ratio along line (0 at z2, 1 at z3)
    return $z2_y + $t * ($z3_y - $z2_y);
};

$backNodes['zb1'] = [
    'x' => $tuckCenter_x,
    'y' => $interpolateY($tuckCenter_x),
    'label' => 'zb1',
    'code' => '$zb1 = midpoint(z2.x, z3.x), interpolated on z2-z3 line'
];

$backNodes['zb2'] = [
    'x' => $b2_x,
    'y' => $interpolateY($b2_x),
    'label' => 'zb2',
    'code' => '$zb2 = tuckCenter - (bottomTuckWidth/2), interpolated on z2-z3 line'
];

$backNodes['zb3'] = [
    'x' => $tuckCenter_x,
    'y' => $backNodes['zb1']['y'] - (4.5 * $scale),
    'label' => 'zb3',
    'code' => '$zb3 = midpoint(z2.x, z3.x), zb1.y - 4.5"'
];

$backNodes['zb4'] = [
    'x' => $b4_x,
    'y' => $interpolateY($b4_x),
    'label' => 'zb4',
    'code' => '$zb4 = tuckCenter + (bottomTuckWidth/2), interpolated on z2-z3 line'
];

// Back cutting line nodes (0.25" seam allowance - matches front pattern)
$backSeamOffset = 0.25;

$backNodes['zr1'] = [
    'x' => $backNodes['z0']['x'],
    'y' => $backNodes['z2']['y'] + ($backSeamOffset * $scale),
    'label' => 'zr1',
    'color' => 'red',
    'code' => '$zr1 = z0.x, z2.y + 0.25"'
];

// zr2: Between zr1 and zr3 at zb4.x, zb4.y + 0.25"
$backNodes['zr2'] = [
    'x' => $backNodes['zb4']['x'],
    'y' => $backNodes['zb4']['y'] + ($backSeamOffset * $scale),
    'label' => 'zr2',
    'color' => 'red',
    'code' => '$zr2 = zb4.x, zb4.y + 0.25"'
];

$backNodes['zr3'] = [
    'x' => $backNodes['z31']['x'],
    'y' => $backNodes['z31']['y'] + ($backSeamOffset * $scale),
    'label' => 'zr3',
    'color' => 'red',
    'code' => '$zr3 = z31.x, z31.y + 0.25"'
];

$backNodes['zr5'] = [
    'x' => $backNodes['z51']['x'],
    'y' => $backNodes['z51']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr5',
    'color' => 'red',
    'code' => '$zr5 = z51.x, z51.y - 0.25"'
];

$backNodes['zr6'] = [
    'x' => $backNodes['z6']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z6']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr6',
    'color' => 'red',
    'code' => '$zr6 = z6.x + 0.25", z6.y - 0.25"'
];

// zr71: between zr6 and zr7 at z71.y level (armhole mid) - shifted like front pattern
$backNodes['zr71'] = [
    'x' => $backNodes['z71']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z71']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr71',
    'color' => 'red',
    'code' => '$zr71 = z71.x + 0.25", z71.y - 0.25"'
];

// zrB1: offset from zB1 (45° point on armhole curve) - shifted like front pattern
$backNodes['zrB1'] = [
    'x' => $backNodes['zB1']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['zB1']['y'] - ($backSeamOffset * $scale),
    'label' => 'zrB1',
    'color' => 'red',
    'code' => '$zrB1 = zB1.x + 0.25", zB1.y - 0.25"'
];

$backNodes['zr7'] = [
    'x' => $backNodes['z8']['x'] + ($backSeamOffset * $scale),
    'y' => $backNodes['z8']['y'] - (0.5 * $scale),
    'label' => 'zr7',
    'color' => 'red',
    'code' => '$zr7 = z8.x + 0.25", z8.y - 0.5"'
];

$backNodes['zr91'] = [
    'x' => $backNodes['z91']['x'] - ($backSeamOffset * $scale),
    'y' => $backNodes['z91']['y'],
    'label' => 'zr91',
    'color' => 'red',
    'code' => '$zr91 = z91.x - 0.25", z91.y'
];

$backNodes['zr8'] = [
    'x' => $backNodes['z9']['x'] - ($backSeamOffset * $scale),
    'y' => $backNodes['z9']['y'] - (0.5 * $scale),
    'label' => 'zr8',
    'color' => 'red',
    'code' => '$zr8 = z9.x - 0.25", z9.y - 0.5"'
];

$backNodes['zr9'] = [
    'x' => $backNodes['z0']['x'],
    'y' => $backNodes['z1']['y'] - ($backSeamOffset * $scale),
    'label' => 'zr9',
    'color' => 'red',
    'code' => '$zr9 = z0.x, z1.y - 0.25"'
];

// Helper function to get back node coordinates
if (!function_exists('bn')) {
    function bn($name, $coord = null) {
        global $backNodes;
        if (!isset($backNodes[$name])) return null;
        if ($coord === 'x') return $backNodes[$name]['x'];
        if ($coord === 'y') return $backNodes[$name]['y'];
        return $backNodes[$name];
    }
}

// =============================================================================
// BACK ARMHOLE PATH LENGTH CALCULATION
// =============================================================================

// Calculate actual path length: z8 → z71 (line) → z6 (cubic curve)
$seg1_len = sqrt(pow($z71_x - $back_z8_x, 2) + pow($z71_y - $back_z8_y, 2));
$seg2_len = calcCubicLength($z71_x, $z71_y, $ctrl1_x, $ctrl1_y, $ctrl2_x, $ctrl2_y, $back_z6_x, $back_z6_y);
$actualBackArmholeLength = $seg1_len + $seg2_len;

// Back armhole path already built above as $armholeSvgPath
$backArmholePath = $armholeSvgPath;

// Red cutting line armhole path (offset version): single cubic Bezier matching black curve
// Control points offset like front pattern: +0.25" x, -0.25" y (replica shifted diagonally)
$cut_ctrl1_x = $ctrl1_x + ($backSeamOffset * $scale);
$cut_ctrl1_y = $ctrl1_y - ($backSeamOffset * $scale);
$cut_ctrl2_x = $ctrl2_x + ($backSeamOffset * $scale);
$cut_ctrl2_y = $ctrl2_y - ($backSeamOffset * $scale);

$backRedCuttingPath = sprintf(
    "M %.2f,%.2f L %.2f,%.2f C %.2f,%.2f %.2f,%.2f %.2f,%.2f",
    $backNodes['zr7']['x'], $backNodes['zr7']['y'],
    $backNodes['zr71']['x'], $backNodes['zr71']['y'],
    $cut_ctrl1_x, $cut_ctrl1_y,
    $cut_ctrl2_x, $cut_ctrl2_y,
    $backNodes['zr6']['x'], $backNodes['zr6']['y']
);

// =============================================================================
// SVG DIMENSIONS
// =============================================================================
$backSvgWidthInches = ($qBust + 2.5);
$backSvgHeightInches = ($blength + 2.5);
$backSvgWidth = $backSvgWidthInches * $scale;
$backSvgHeight = $backSvgHeightInches * $scale;

$backBounds = [
    'minX' => 0,
    'minY' => 0,
    'width' => $backSvgWidth,
    'height' => $backSvgHeight
];

// =============================================================================
// HELPER FUNCTIONS FOR RENDERING
// =============================================================================

// Scissors Icon helper
if (!function_exists('scissorsIcon')) {
    function scissorsIcon($x, $y, $rotation = 0, $size = 0.5, $color = '#333333') {
        global $scale;
        $sizeScaled = $size * $scale;
        $scissorsPath = 'M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3h-3z';
        $pathScale = $sizeScaled / 24;
        $svg = sprintf('<g transform="translate(%.2f, %.2f) rotate(%d) scale(%.4f) translate(-12, -12)">', $x, $y, $rotation, $pathScale);
        $svg .= sprintf('<path d="%s" fill="%s"/>', $scissorsPath, $color);
        $svg .= '</g>';
        return $svg;
    }
}

// Grainline helper
if (!function_exists('grainLine')) {
    function grainLine($x, $y, $length, $orientation = 'vertical') {
        global $scale;
        $lengthScaled = $length * $scale;
        $arrowSize = 0.15 * $scale;
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
        return $svg;
    }
}

// Snip Icon helper - small V-shaped notch mark
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
        if ($isDevMode) {
            $svg .= sprintf('<text class="snip-label" x="%.2f" y="%.2f" text-anchor="middle" dominant-baseline="middle" font-size="10px" font-weight="bold" fill="black">%s</text>', $labelX, $labelY, htmlspecialchars($label));
        }
        $svg .= '</g>';
        return $svg;
    }
}

// =============================================================================
// SECTION 3: SVG GENERATION
// =============================================================================

// Calculate cutting line control points
$seamOffsetPx = 0.5 * $scale;
$cut_neck_ctrl1_x = bn('z91','x') - $seamOffsetPx;
$cut_neck_ctrl1_y = bn('z1','y');
$cut_neck_ctrl2_x = bn('z91','x') - $seamOffsetPx;
$cut_neck_ctrl2_y = bn('z9','y');

// Calculate back tuck center
$zb_center_x = (bn('zb2','x') + bn('zb4','x')) / 2;
$zb_center_y = bn('zb2','y');

// Generate SVG content
ob_start();
?>
<svg id="backPatternSvg" width="<?php echo $backSvgWidth; ?>" height="<?php echo $backSvgHeight; ?>"
     viewBox="<?php echo $backBounds['minX']; ?> <?php echo $backBounds['minY']; ?> <?php echo $backBounds['width']; ?> <?php echo $backBounds['height']; ?>"
     xmlns="http://www.w3.org/2000/svg">
    <defs>
        <pattern id="hatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <line x1="0" y1="0" x2="0" y2="4" stroke="#444" stroke-width="0.8"/>
        </pattern>
        <!-- Clip path for back ease box: z3 -> z31 -> z51 -> z5 -->
        <clipPath id="backEaseBoxClip">
            <polygon points="<?php echo bn('z3','x'); ?>,<?php echo bn('z3','y'); ?>
                             <?php echo bn('z31','x'); ?>,<?php echo bn('z31','y'); ?>
                             <?php echo bn('z51','x'); ?>,<?php echo bn('z51','y'); ?>
                             <?php echo bn('z5','x'); ?>,<?php echo bn('z5','y'); ?>"/>
        </clipPath>
    </defs>
    <rect width="100%" height="100%" fill="#fff"/>

    <?php if ($isDevMode): ?>
    <!-- Margin guides -->
    <line x1="<?php echo $originX; ?>" y1="0" x2="<?php echo $originX; ?>" y2="<?php echo $backSvgHeight; ?>" stroke="#eee" stroke-dasharray="4,4"/>
    <line x1="0" y1="<?php echo $originY; ?>" x2="<?php echo $backSvgWidth; ?>" y2="<?php echo $originY; ?>" stroke="#eee" stroke-dasharray="4,4"/>
    <?php endif; ?>

    <!-- Back Armhole curve -->
    <path id="backArmholePath" d="<?php echo $armholeSvgPath; ?>" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Shoulder line: z9 to z8 -->
    <line x1="<?php echo bn('z9','x'); ?>" y1="<?php echo bn('z9','y'); ?>"
          x2="<?php echo bn('z8','x'); ?>" y2="<?php echo bn('z8','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Back neck: z9 to z91 (straight line), z91 to z1 (curve) -->
    <path d="M <?php echo bn('z9','x'); ?>,<?php echo bn('z9','y'); ?> L <?php echo bn('z91','x'); ?>,<?php echo bn('z91','y'); ?> Q <?php echo bn('z91','x'); ?>,<?php echo bn('z1','y'); ?> <?php echo bn('z1','x'); ?>,<?php echo bn('z1','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Fold line text (center back - between z1 and z2) -->
    <?php
    $foldTextX = (bn('z1', 'x') + bn('z2', 'x')) / 2;
    $foldTextY = (bn('z1', 'y') + bn('z2', 'y')) / 2;
    ?>
    <text x="<?php echo $foldTextX; ?>" y="<?php echo $foldTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(90, <?php echo $foldTextX; ?>, <?php echo $foldTextY; ?>)">------ Fold ------</text>

    <!-- Bottom line: z2 to z3 -->
    <line x1="<?php echo bn('z2','x'); ?>" y1="<?php echo bn('z2','y'); ?>"
          x2="<?php echo bn('z3','x'); ?>" y2="<?php echo bn('z3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Side seam: z3 to z5 to z6 -->
    <line x1="<?php echo bn('z3','x'); ?>" y1="<?php echo bn('z3','y'); ?>"
          x2="<?php echo bn('z5','x'); ?>" y2="<?php echo bn('z5','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo bn('z5','x'); ?>" y1="<?php echo bn('z5','y'); ?>"
          x2="<?php echo bn('z6','x'); ?>" y2="<?php echo bn('z6','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Side box: z5 -> z51 -> z31 -> z3 (1.5" wide rectangle) -->
    <line x1="<?php echo bn('z5','x'); ?>" y1="<?php echo bn('z5','y'); ?>"
          x2="<?php echo bn('z51','x'); ?>" y2="<?php echo bn('z51','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo bn('z51','x'); ?>" y1="<?php echo bn('z51','y'); ?>"
          x2="<?php echo bn('z31','x'); ?>" y2="<?php echo bn('z31','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <line x1="<?php echo bn('z31','x'); ?>" y1="<?php echo bn('z31','y'); ?>"
          x2="<?php echo bn('z3','x'); ?>" y2="<?php echo bn('z3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Ease box hatch fill: z5 -> z51 -> z31 -> z3 -->
    <polygon points="<?php echo bn('z5','x'); ?>,<?php echo bn('z5','y'); ?>
                     <?php echo bn('z51','x'); ?>,<?php echo bn('z51','y'); ?>
                     <?php echo bn('z31','x'); ?>,<?php echo bn('z31','y'); ?>
                     <?php echo bn('z3','x'); ?>,<?php echo bn('z3','y'); ?>"
             fill="url(#hatch)" stroke="none"/>

    <!-- Ease text (in side box - centered between z5-z3 and z51-z31) -->
    <?php
    // Horizontal center: midpoint between left edge (z5/z3) and right edge (z51/z31)
    $backEaseTextX = (bn('z5', 'x') + bn('z51', 'x')) / 2;
    // Vertical center: midpoint between top (z5/z51) and bottom (z3/z31)
    $backEaseTextY = (bn('z5', 'y') + bn('z3', 'y')) / 2;
    ?>
    <text x="<?php echo $backEaseTextX; ?>" y="<?php echo $backEaseTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(90, <?php echo $backEaseTextX; ?>, <?php echo $backEaseTextY; ?>)">---- ease -----</text>

    <!-- BACK TUCK: zb2 -> zb3 -> zb4 -->
    <line x1="<?php echo bn('zb2','x'); ?>" y1="<?php echo bn('zb2','y'); ?>"
          x2="<?php echo bn('zb3','x'); ?>" y2="<?php echo bn('zb3','y'); ?>"
          stroke="#808080" stroke-width="0.5"/>
    <line x1="<?php echo bn('zb3','x'); ?>" y1="<?php echo bn('zb3','y'); ?>"
          x2="<?php echo bn('zb4','x'); ?>" y2="<?php echo bn('zb4','y'); ?>"
          stroke="#808080" stroke-width="0.5"/>
    <!-- Center fold line -->
    <line x1="<?php echo bn('zb3','x'); ?>" y1="<?php echo bn('zb3','y'); ?>"
          x2="<?php echo $zb_center_x; ?>" y2="<?php echo $zb_center_y; ?>"
          stroke="#000" stroke-width="0.5" stroke-dasharray="2,2"/>

    <!-- CUTTING LINE (RED) - Complete outline with 0.25" seam allowance -->
    <?php
    // Offset control points for cutting line (0.25" seam allowance)
    // ctrl1 is near zr71, offset to the right
    $cut_ctrl1_x = $ctrl1_x + ($backSeamOffset * $scale);
    $cut_ctrl1_y = $ctrl1_y;
    // ctrl2 is near zr6/zrB1, offset to the right and up (perpendicular to curve)
    $cut_ctrl2_x = $ctrl2_x + ($backSeamOffset * $scale);
    $cut_ctrl2_y = $ctrl2_y - ($backSeamOffset * $scale);

    // Neck curve offset control points (matching black neck curve)
    $cut_neck_ctrl1_x = bn('zr91','x');
    $cut_neck_ctrl1_y = bn('zr9','y');
    $cut_neck_ctrl2_x = bn('zr91','x');
    $cut_neck_ctrl2_y = bn('zr8','y') + (0.5 * $scale);
    ?>
    <!-- Part 1: Bottom edge (zr1 → zr2 → zr3) + Side box outer (zr3 → zr5) -->
    <path d="M <?php echo bn('zr1','x'); ?>,<?php echo bn('zr1','y'); ?>
             L <?php echo bn('zr2','x'); ?>,<?php echo bn('zr2','y'); ?>
             L <?php echo bn('zr3','x'); ?>,<?php echo bn('zr3','y'); ?>
             L <?php echo bn('zr5','x'); ?>,<?php echo bn('zr5','y'); ?>"
          stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

    <!-- Part 2: Side seam + Armhole + Shoulder + Neck (zr5 → zr6 → zr71 → zr7 → zr8 → neck → zr9) -->
    <!-- Armhole uses single cubic Bezier (C) like black curve: zr6 → zr71 with ctrl2,ctrl1 (reversed for opposite direction) -->
    <path d="M <?php echo bn('zr5','x'); ?>,<?php echo bn('zr5','y'); ?>
             L <?php echo bn('zr6','x'); ?>,<?php echo bn('zr6','y'); ?>
             C <?php echo $cut_ctrl2_x; ?>,<?php echo $cut_ctrl2_y; ?>
               <?php echo $cut_ctrl1_x; ?>,<?php echo $cut_ctrl1_y; ?>
               <?php echo bn('zr71','x'); ?>,<?php echo bn('zr71','y'); ?>
             L <?php echo bn('zr7','x'); ?>,<?php echo bn('zr7','y'); ?>
             L <?php echo bn('zr8','x'); ?>,<?php echo bn('zr8','y'); ?>
             Q <?php echo $cut_neck_ctrl2_x; ?>,<?php echo $cut_neck_ctrl2_y; ?>
               <?php echo bn('zr91','x'); ?>,<?php echo bn('zr91','y'); ?>
             Q <?php echo $cut_neck_ctrl1_x; ?>,<?php echo $cut_neck_ctrl1_y; ?>
               <?php echo bn('zr9','x'); ?>,<?php echo bn('zr9','y'); ?>"
          stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3" fill="none"/>

    <!-- Scissors Icon on cutting line at zr5 -->
    <?php echo scissorsIcon(bn('zr5','x'), bn('zr5','y'), 90, 0.4); ?>

    <!-- Snip Marks (notches on cutting line - zr series) - HIDDEN FOR NOW
    <?php /* echo snipIcon(1, 'S1', 'bn', 'zr3', 90); */ ?>
    <?php /* echo snipIcon(2, 'S2', 'bn', 'zr5', 90); */ ?>
    -->

    <!-- Grainline -->
    <?php
    // Position grainline: X = midpoint between zb4 and z3, Y = zb3 (center)
    $grainX = (bn('zb4', 'x') + bn('z3', 'x')) / 2;  // Horizontal center between zb4 and z3
    $grainY = bn('zb3', 'y');  // zb3 is the vertical center
    $grainLength = 4;  // 4 inches long, centered at zb3.y
    echo grainLine($grainX, $grainY, $grainLength, 'vertical');
    ?>

    <?php if ($isPrintMode): ?>
    <!-- X markers at node positions (Print Mode Only) -->
    <!-- z6 node X marker -->
    <text x="<?php echo bn('z6', 'x'); ?>" y="<?php echo bn('z6', 'y'); ?>"
          font-size="6" text-anchor="middle" dominant-baseline="middle" fill="#000">X</text>

    <!-- z5 node X marker -->
    <text x="<?php echo bn('z5', 'x'); ?>" y="<?php echo bn('z5', 'y'); ?>"
          font-size="6" text-anchor="middle" dominant-baseline="middle" fill="#000">X</text>
    <?php endif; ?>

    <?php if ($isDevMode): ?>
    <!-- ARMHOLE CONSTRUCTION POINTS (Gray - Dev Mode Only) -->
    <!-- These show the L-shape calculation points from deepNeck.php -->
    <?php
    // Point A: Top of armhole (shoulder point) - using curve-fitted z8
    $armhole_A_x = $back_z8_x;
    $armhole_A_y = $back_z8_y;

    // Point B: Corner of L-shape (curve-fitted armHoleHeight)
    $armhole_B_x = $back_B_x;
    $armhole_B_y = $back_B_y;

    // Point AB: Midpoint between A and B (z71) - curve-fitted
    $armhole_AB_x = $z71_x;
    $armhole_AB_y = $z71_y;

    // Point B1: from B at 45° angle (curve-fitted)
    $armhole_B1_x = $back_B1_x;
    $armhole_B1_y = $back_B1_y;

    // Point C: End of armhole (curve-fitted z6)
    $armhole_C_x = $back_z6_x;
    $armhole_C_y = $back_z6_y;
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
    <text x="<?php echo $armhole_A_x - 10; ?>" y="<?php echo $armhole_A_y - 5; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">A</text>

    <!-- Point B -->
    <circle cx="<?php echo $armhole_B_x; ?>" cy="<?php echo $armhole_B_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_B_x - 10; ?>" y="<?php echo $armhole_B_y + 12; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">B</text>

    <!-- Point z71 (midpoint between z8 and B) -->
    <circle cx="<?php echo $armhole_AB_x; ?>" cy="<?php echo $armhole_AB_y; ?>" r="2"
            fill="#9CA3AF" stroke="none"/>
    <text x="<?php echo $armhole_AB_x - 15; ?>" y="<?php echo $armhole_AB_y + 3; ?>"
          font-size="8" fill="#9CA3AF">z71</text>

    <!-- Point B1 (45° from B) -->
    <circle cx="<?php echo $armhole_B1_x; ?>" cy="<?php echo $armhole_B1_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_B1_x + 5; ?>" y="<?php echo $armhole_B1_y - 5; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">B1</text>
    <!-- Armhole curve length at B1 (total of z8→z71→B1→z6) -->
    <text x="<?php echo $armhole_B1_x; ?>" y="<?php echo $armhole_B1_y + 18; ?>"
          font-size="8" fill="#6366F1" text-anchor="middle" font-weight="bold">
        Curve: <?php echo number_format($actualBackArmholeLength / $scale, 2); ?>"
    </text>

    <!-- Point C -->
    <circle cx="<?php echo $armhole_C_x; ?>" cy="<?php echo $armhole_C_y; ?>" r="3"
            fill="none" stroke="#9CA3AF" stroke-width="1"/>
    <text x="<?php echo $armhole_C_x + 5; ?>" y="<?php echo $armhole_C_y + 12; ?>"
          font-size="9" fill="#9CA3AF" font-weight="bold">C</text>

    <!-- Dimension labels -->
    <!-- armHoleHeight label (A to B distance) - using curve-fitted value -->
    <text x="<?php echo $armhole_A_x - 25; ?>" y="<?php echo ($armhole_A_y + $armhole_B_y) / 2; ?>"
          font-size="7" fill="#9CA3AF" text-anchor="end"
          transform="rotate(-90, <?php echo $armhole_A_x - 25; ?>, <?php echo ($armhole_A_y + $armhole_B_y) / 2; ?>)">
        <?php echo number_format($backArmHoleHeight, 2); ?>"
    </text>

    <!-- armHoleDepth label (B to C distance) -->
    <text x="<?php echo ($armhole_B_x + $armhole_C_x) / 2; ?>" y="<?php echo $armhole_B_y + 15; ?>"
          font-size="7" fill="#9CA3AF" text-anchor="middle">
        <?php echo number_format($armHoleDepth, 2); ?>"
    </text>

    <!-- Node Labels (Dev Mode Only) -->
    <?php foreach ($backNodes as $name => $node): ?>
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
    <?php foreach ($backNodes as $name => $node): ?>
        <?php if (isset($node['color']) && $node['color'] === 'red'): ?>
        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                fill="#DC2626" stroke="#fff" stroke-width="1"/>
        <?php if ($name === 'zr91'): ?>
        <!-- zr91 label on left side for readability -->
        <text x="<?php echo $node['x'] - 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="#DC2626" font-weight="300" opacity="0.7" text-anchor="end"><?php echo $name; ?></text>
        <?php else: ?>
        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="#DC2626" font-weight="300" opacity="0.7"><?php echo $name; ?></text>
        <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php endif; ?>
</svg>
<?php
$backSvgContent = ob_get_clean();

// =============================================================================
// SECTION 4: EXPORT DATA (For composites & PDF)
// =============================================================================
$backPatternData = [
    'name' => 'BACK',
    'type' => 'sariBlouseBack',
    'nodes' => $backNodes,
    'svg_content' => $backSvgContent,
    'paths' => [
        'armhole' => $armholeSvgPath,
        'cutting' => $backRedCuttingPath
    ],
    'dimensions' => [
        'width' => $backSvgWidth,
        'height' => $backSvgHeight,
        'widthInches' => $backSvgWidthInches,
        'heightInches' => $backSvgHeightInches
    ],
    'bounds' => $backBounds,
    'measurements' => [
        'actualArmholeLength' => $actualBackArmholeLength / $scale,
        'targetArmholeLength' => $armhole / 2
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
    <title>Blouse Back - <?php echo htmlspecialchars($customerName); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .pattern-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 10px; }
        .info { color: #666; margin-bottom: 20px; font-size: 14px; }
        .svg-container { border: 1px solid #ddd; background: white; overflow: auto; }
    </style>
</head>
<body>
    <div class="pattern-container">
        <h1>Blouse Back Pattern</h1>
        <div class="info">
            Customer: <?php echo htmlspecialchars($customerName); ?> |
            Bust: <?php echo $bust; ?>" |
            Back Length: <?php echo $blength; ?>" |
            Armhole: <?php echo number_format($actualBackArmholeLength / $scale, 2); ?>" (target: <?php echo number_format($armhole / 2, 2); ?>")
        </div>

        <div class="svg-container">
            <?php echo $backSvgContent; ?>
        </div>

        <?php if ($isDevMode): ?>
        <div id="svgMeasurements" style="margin-top: 15px; padding: 10px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 4px; font-family: monospace;">
            <strong>SVG Path Measurements (Dynamic):</strong>
            <div id="armholeMeasurement">Measuring...</div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scale = <?php echo $scale; ?>; // pixels per inch
            const armholePath = document.getElementById('backArmholePath');
            const measureDiv = document.getElementById('armholeMeasurement');

            if (armholePath) {
                const pathLength = armholePath.getTotalLength();
                const lengthInches = pathLength / scale;
                const target = <?php echo $armhole / 2; ?>;
                const diff = lengthInches - target;
                const diffSign = diff >= 0 ? '+' : '';

                measureDiv.innerHTML =
                    '<span style="color: #059669;">✓ Back Armhole (SVG measured): <strong>' + lengthInches.toFixed(2) + '"</strong></span>' +
                    ' | Target: ' + target.toFixed(2) + '"' +
                    ' | Diff: <span style="color: ' + (Math.abs(diff) < 0.1 ? '#059669' : '#dc2626') + ';">' + diffSign + diff.toFixed(2) + '"</span>' +
                    ' | Path pixels: ' + pathLength.toFixed(1) + 'px';
            } else {
                measureDiv.innerHTML = '<span style="color: #dc2626;">Error: Armhole path not found</span>';
            }
        });
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
