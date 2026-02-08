<?php
/**
 * =============================================================================
 * SAREE BLOUSE SLEEVE PATTERN
 * =============================================================================
 *
 * Generates the sleeve pattern for a saree blouse.
 * Uses iterative calculation to match armhole length.
 *
 * MODES:
 * - Standalone: Full HTML preview (default) - for development/testing
 * - Composite:  Returns SVG + data only (when COMPOSITE_MODE defined)
 *
 * USAGE:
 *   Standalone: sariSleeve.php?customer_id=123&measurement_id=456&mode=dev
 *   Composite:  define('COMPOSITE_MODE', true); include 'sariSleeve.php';
 *
 * EXPORTS (when included):
 *   $sleevePatternData - Array with nodes, svg_content, dimensions
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

// Calculate armhole (for reference values)
calculateArmhole(1.0, $originX, $originY);

// =============================================================================
// SECTION 2: SLEEVE CALCULATIONS
// =============================================================================

// SVG view scale
$sleeveViewScale = 2.0;

// Coordinate system setup
// Note: Left margin needs to be 2.5" to accommodate the 1.5" ease box on the left side
$sleeveTopMargin = 1 * $scale;
$sleeveLeftMargin = 2.5 * $scale;

// =============================================================================
// HELPER FUNCTIONS
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
// CAP HEIGHT CALCULATION - Using centralized function from deepNeck.php
// =============================================================================
// Cap height based on waist: 3.0" if waist ≤ 30", else 3.5"
// Diagonal (s2→s1, s1→s3) = armhole/2
// Half width = sqrt(diagonal² - capHeight²) using Pythagorean theorem
// If saround > calculated sleeveWidth, use saround and reduce cap height

$sleeveCapDims = getSleeveCapDimensions($waist, $armhole, $saround);
$capHeight = $sleeveCapDims['capHeight'] * $scale;
$halfWidth = $sleeveCapDims['halfWidth'] * $scale;
$sleeveWidth = $sleeveCapDims['sleeveWidth'] * $scale;

// Cap point (fixed) - centered over sleeve width
$capX = $sleeveLeftMargin + $halfWidth;
$capY = $sleeveTopMargin;

// Shoulder ease (0.5" on each side = 1" total added to s2-s3 width)
// NOTE: This is experimental ease added to s2 and s3 — may be removed later
$shoulderEaseInches = 0.5;
$shoulderEase = $shoulderEaseInches * $scale;

// Shoulder points - positioned at capHeight below cap, with ease
$leftShoulderX = $sleeveLeftMargin - $shoulderEase;
$leftShoulderY = $sleeveTopMargin + $capHeight;
$rightShoulderX = $sleeveLeftMargin + $sleeveWidth + $shoulderEase;
$rightShoulderY = $sleeveTopMargin + $capHeight;

// Verify diagonal distances
$s2_s1_diagonal = sqrt(pow($capX - $leftShoulderX, 2) + pow($capY - $leftShoulderY, 2));
$s1_s3_diagonal = sqrt(pow($rightShoulderX - $capX, 2) + pow($rightShoulderY - $capY, 2));

// Wrist points
$wristCenter = $capX;
$wristHalfWidth = ($sopen * $scale) / 2;
$leftWristX = $wristCenter - $wristHalfWidth;
$leftWristY = $capY + ($slength * $scale);
$rightWristX = $wristCenter + $wristHalfWidth;
$rightWristY = $capY + ($slength * $scale);

// Calculate angles for curves to s1 (cap point)
$angle_s2_s1 = atan2($capY - $leftShoulderY, $capX - $leftShoulderX);
$perpendicular_s2_s1 = $angle_s2_s1 + (M_PI / 2);
$angle_s1_s3 = atan2($rightShoulderY - $capY, $rightShoulderX - $capX);

// S-CURVE parameters
$sCurveDepth = 1.3 * $scale;

// Control points for s2→s1
$ctrl1_s2s1_t = 0.35;
$ctrl1_s2s1_base_x = $leftShoulderX + ($capX - $leftShoulderX) * $ctrl1_s2s1_t;
$ctrl1_s2s1_base_y = $leftShoulderY + ($capY - $leftShoulderY) * $ctrl1_s2s1_t;
$ctrl1_s2s1_x = $ctrl1_s2s1_base_x + cos($perpendicular_s2_s1) * $sCurveDepth;
$ctrl1_s2s1_y = $ctrl1_s2s1_base_y + sin($perpendicular_s2_s1) * $sCurveDepth;

$ctrl2_s2s1_t = 0.65;
$ctrl2_s2s1_base_x = $leftShoulderX + ($capX - $leftShoulderX) * $ctrl2_s2s1_t;
$ctrl2_s2s1_base_y = $leftShoulderY + ($capY - $leftShoulderY) * $ctrl2_s2s1_t;
$ctrl2_s2s1_x = $ctrl2_s2s1_base_x - cos($perpendicular_s2_s1) * $sCurveDepth + (0.3 * $scale);
$ctrl2_s2s1_y = $ctrl2_s2s1_base_y - sin($perpendicular_s2_s1) * $sCurveDepth - (0.2 * $scale);

// Control points for s1→s3
$perpendicular_s1_s3 = $angle_s1_s3 + (M_PI / 2);

$ctrl1_s1s3_t = 0.25;
$ctrl1_s1s3_base_x = $capX + ($rightShoulderX - $capX) * $ctrl1_s1s3_t;
$ctrl1_s1s3_base_y = $capY + ($rightShoulderY - $capY) * $ctrl1_s1s3_t;
$ctrl1_s1s3_x = $ctrl1_s1s3_base_x - cos($perpendicular_s1_s3) * (1.0 * $scale);
$ctrl1_s1s3_y = $ctrl1_s1s3_base_y - sin($perpendicular_s1_s3) * (1.0 * $scale);

$ctrl2_s1s3_t = 0.75;
$ctrl2_s1s3_base_x = $capX + ($rightShoulderX - $capX) * $ctrl2_s1s3_t;
$ctrl2_s1s3_base_y = $capY + ($rightShoulderY - $capY) * $ctrl2_s1s3_t;
$ctrl2_s1s3_x = $ctrl2_s1s3_base_x + cos($perpendicular_s1_s3) * (0.2 * $scale);
$ctrl2_s1s3_y = $ctrl2_s1s3_base_y + sin($perpendicular_s1_s3) * (0.2 * $scale);

// Calculate actual curved path lengths for reference
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

$totalPathLength = $s2_s1_length + $s1_s3_length;

// Store calculated values for display
$actualArmholePathInches = $totalPathLength / $scale;
$s2_s1_diagonal_inches = $s2_s1_diagonal / $scale;
$s1_s3_diagonal_inches = $s1_s3_diagonal / $scale;

// =============================================================================
// SLEEVE NODES - Store all key points for reference and rendering
// =============================================================================
$sleeveNodes = [
    's1' => [
        'x' => $capX,
        'y' => $capY,
        'label' => 'Cap',
        'color' => '#10B981',
        'code' => '$s1 = cap point (x = margin + halfWidth, y = topMargin)'
    ],
    's2' => [
        'x' => $leftShoulderX,
        'y' => $leftShoulderY,
        'label' => 'Left Shoulder',
        'color' => '#10B981',
        'code' => '$s2 = margin - ' . number_format($shoulderEaseInches, 1) . '" ease, diagonal to s1 = armhole/2 (' . number_format($armhole/2, 2) . '")'
    ],
    's3' => [
        'x' => $rightShoulderX,
        'y' => $rightShoulderY,
        'label' => 'Right Shoulder',
        'color' => '#10B981',
        'code' => '$s3 = margin + sleeveWidth + ' . number_format($shoulderEaseInches, 1) . '" ease, diagonal to s1 = armhole/2 (' . number_format($armhole/2, 2) . '")'
    ],
    's4' => [
        'x' => $leftWristX,
        'y' => $leftWristY,
        'label' => 'Left Wrist',
        'color' => '#10B981',
        'code' => '$s4 = left wrist (center - sopen/2, s1.y + slength)'
    ],
    's5' => [
        'x' => $rightWristX,
        'y' => $rightWristY,
        'label' => 'Right Wrist',
        'color' => '#10B981',
        'code' => '$s5 = right wrist (center + sopen/2, s1.y + slength)'
    ],
    'center' => [
        'x' => $capX,
        'y' => ($sleeveTopMargin + $leftWristY) / 2,
        'label' => 'Center',
        'color' => '#6366F1',
        'code' => '$center = center point for grainline'
    ],
];

// Side box nodes (1.5" wide box from s5 to s3, following the s5-s3 angle)
// Calculate angle and perpendicular offset for 1.5" box width
$s5_s3_dx = $rightShoulderX - $rightWristX;
$s5_s3_dy = $rightShoulderY - $rightWristY;
$s5_s3_length = sqrt($s5_s3_dx * $s5_s3_dx + $s5_s3_dy * $s5_s3_dy);
$s5_s3_perpX = -$s5_s3_dy / $s5_s3_length;  // Perpendicular unit vector X (pointing outward/right)
$s5_s3_perpY = $s5_s3_dx / $s5_s3_length;   // Perpendicular unit vector Y
$sideBoxWidth = 1.5 * $scale;  // 1.5" box width (matches back/front patterns)

// Node s51: 1.5" right of s5, same y as s5
$sleeveNodes['s51'] = [
    'x' => $rightWristX + $sideBoxWidth,
    'y' => $rightWristY,
    'label' => 's51',
    'color' => '#10B981',
    'code' => '$s51 = s5.x + 1.5", s5.y'
];

// Node s31: 1.5" right of s3, same y as s3
$sleeveNodes['s31'] = [
    'x' => $rightShoulderX + $sideBoxWidth,
    'y' => $rightShoulderY,
    'label' => 's31',
    'color' => '#10B981',
    'code' => '$s31 = s3.x + 1.5", s3.y'
];

// Node s21: 1.5" left of s2, same y as s2
$sleeveNodes['s21'] = [
    'x' => $leftShoulderX - $sideBoxWidth,
    'y' => $leftShoulderY,
    'label' => 's21',
    'color' => '#10B981',
    'code' => '$s21 = s2.x - 1.5", s2.y'
];

// Node s41: 1.5" left of s4, same y as s4
$sleeveNodes['s41'] = [
    'x' => $leftWristX - $sideBoxWidth,
    'y' => $leftWristY,
    'label' => 's41',
    'color' => '#10B981',
    'code' => '$s41 = s4.x - 1.5", s4.y'
];

// Helper function for sleeve nodes
if (!function_exists('sn')) {
    function sn($nodeName, $coord) {
        global $sleeveNodes;
        return $sleeveNodes[$nodeName][$coord] ?? 0;
    }
}

// =============================================================================
// BUILD SLEEVE PATHS
// =============================================================================

// Black pattern line
$sleeveBlack = "M" . $leftShoulderX . "," . $leftShoulderY;
$sleeveBlack .= " C" . $ctrl1_s2s1_x . "," . $ctrl1_s2s1_y . " " . $ctrl2_s2s1_x . "," . $ctrl2_s2s1_y . " " . $capX . "," . $capY;
$sleeveBlack .= " C" . $ctrl1_s1s3_x . "," . $ctrl1_s1s3_y . " " . $ctrl2_s1s3_x . "," . $ctrl2_s1s3_y . " " . $rightShoulderX . "," . $rightShoulderY;

// Curve from s3 to s5 (0.25" bulge inward from straight line)
// Midpoint of straight s3-s5 line, then move 0.25" inward (left)
$s3_s5_midX = ($rightShoulderX + $rightWristX) / 2;
$s3_s5_midY = ($rightShoulderY + $rightWristY) / 2;
$s3_s5_ctrl_x = $s3_s5_midX - (0.25 * $scale);  // 0.25" left of midpoint
$s3_s5_ctrl_y = $s3_s5_midY;
$sleeveBlack .= " Q" . $s3_s5_ctrl_x . "," . $s3_s5_ctrl_y . " " . $rightWristX . "," . $rightWristY;

$sleeveBlack .= " L" . $leftWristX . "," . $leftWristY;

// Curve from s4 to s2 (0.25" bulge inward from straight line)
// Midpoint of straight s4-s2 line, then move 0.25" inward (right)
$s4_s2_midX = ($leftWristX + $leftShoulderX) / 2;
$s4_s2_midY = ($leftWristY + $leftShoulderY) / 2;
$s4_s2_ctrl_x = $s4_s2_midX + (0.25 * $scale);  // 0.25" right of midpoint (inward for left side)
$s4_s2_ctrl_y = $s4_s2_midY;
$sleeveBlack .= " Q" . $s4_s2_ctrl_x . "," . $s4_s2_ctrl_y . " " . $leftShoulderX . "," . $leftShoulderY;

// Gray reference line
$sleeveGray = "M" . $leftShoulderX . "," . $leftShoulderY;
$s2_s1_ctrl_x = ($leftShoulderX + $capX) / 2;
$s2_s1_ctrl_y = ($leftShoulderY + $capY) / 2 - (0.3 * $scale);
$sleeveGray .= " Q" . $s2_s1_ctrl_x . "," . $s2_s1_ctrl_y . " " . $capX . "," . $capY;
$s1_s3_ctrl_x = ($capX + $rightShoulderX) / 2;
$s1_s3_ctrl_y = ($capY + $rightShoulderY) / 2 - (0.3 * $scale);
$sleeveGray .= " Q" . $s1_s3_ctrl_x . "," . $s1_s3_ctrl_y . " " . $rightShoulderX . "," . $rightShoulderY;
$sleeveGray .= " L" . $rightWristX . "," . $rightWristY;
$sleeveGray .= " L" . $leftWristX . "," . $leftWristY;
$sleeveGray .= " Z";

// =============================================================================
// RED CUTTING LINE (follows s21 → s2 → s1 → s3 → s31, moved up 0.25")
// =============================================================================
$redVerticalOffset = 0.25 * $scale;  // Move up by 0.25"

// Red cutting line points (same x as pattern, y moved up by 0.25")
$redS21_x = $sleeveNodes['s21']['x'];
$redS21_y = $sleeveNodes['s21']['y'] - $redVerticalOffset;
$redS2_x = $leftShoulderX;
$redS2_y = $leftShoulderY - $redVerticalOffset;
$redS1_x = $capX;
$redS1_y = $capY - $redVerticalOffset;
$redS3_x = $rightShoulderX;
$redS3_y = $rightShoulderY - $redVerticalOffset;
$redS31_x = $sleeveNodes['s31']['x'];
$redS31_y = $sleeveNodes['s31']['y'] - $redVerticalOffset;

// Control points for red line (same as black pattern, moved up by 0.25")
$red_ctrl1_s2s1_x = $ctrl1_s2s1_x;
$red_ctrl1_s2s1_y = $ctrl1_s2s1_y - $redVerticalOffset;
$red_ctrl2_s2s1_x = $ctrl2_s2s1_x;
$red_ctrl2_s2s1_y = $ctrl2_s2s1_y - $redVerticalOffset;
$red_ctrl1_s1s3_x = $ctrl1_s1s3_x;
$red_ctrl1_s1s3_y = $ctrl1_s1s3_y - $redVerticalOffset;
$red_ctrl2_s1s3_x = $ctrl2_s1s3_x;
$red_ctrl2_s1s3_y = $ctrl2_s1s3_y - $redVerticalOffset;

// Build red cutting path (TOP): s21 → s2 → s1 → s3 → s31
$sleeveRedTop = "M" . $redS21_x . "," . $redS21_y;
$sleeveRedTop .= " L" . $redS2_x . "," . $redS2_y;
$sleeveRedTop .= " C" . $red_ctrl1_s2s1_x . "," . $red_ctrl1_s2s1_y . " " . $red_ctrl2_s2s1_x . "," . $red_ctrl2_s2s1_y . " " . $redS1_x . "," . $redS1_y;
$sleeveRedTop .= " C" . $red_ctrl1_s1s3_x . "," . $red_ctrl1_s1s3_y . " " . $red_ctrl2_s1s3_x . "," . $red_ctrl2_s1s3_y . " " . $redS3_x . "," . $redS3_y;
$sleeveRedTop .= " L" . $redS31_x . "," . $redS31_y;

// Red cutting line (BOTTOM): s41 → s4 → s5 → s51, moved down by 0.25"
$redS41_x = $sleeveNodes['s41']['x'];
$redS41_y = $sleeveNodes['s41']['y'] + $redVerticalOffset;
$redS4_x = $leftWristX;
$redS4_y = $leftWristY + $redVerticalOffset;
$redS5_x = $rightWristX;
$redS5_y = $rightWristY + $redVerticalOffset;
$redS51_x = $sleeveNodes['s51']['x'];
$redS51_y = $sleeveNodes['s51']['y'] + $redVerticalOffset;

// Build red cutting path (BOTTOM): s41 → s4 → s5 → s51 (straight line)
$sleeveRedBottom = "M" . $redS41_x . "," . $redS41_y;
$sleeveRedBottom .= " L" . $redS4_x . "," . $redS4_y;
$sleeveRedBottom .= " L" . $redS5_x . "," . $redS5_y;
$sleeveRedBottom .= " L" . $redS51_x . "," . $redS51_y;

// Center fold line
$centerFoldLine = "M" . $capX . "," . $capY . " L" . $capX . "," . $leftWristY;

// =============================================================================
// SVG DIMENSIONS
// =============================================================================
// Width: sleeveWidth + 2.5" left margin + 1.5" ease box left + 1.5" ease box right + 1" right margin + shoulder ease both sides
$sleeveSvgWidthInches = ($sleeveCapDims['sleeveWidth'] + 6.5 + $shoulderEaseInches * 2);
$sleeveSvgHeightInches = ($slength + 6);
$sleeveSvgWidth = $sleeveSvgWidthInches * $scale * $sleeveViewScale;
$sleeveSvgHeight = $sleeveSvgHeightInches * $scale * $sleeveViewScale;

$sleeveBounds = [
    'minX' => 0,
    'minY' => 0,
    'width' => $sleeveSvgWidth / $sleeveViewScale,
    'height' => $sleeveSvgHeight / $sleeveViewScale
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

// =============================================================================
// CURVE GAP MEASUREMENTS (distance between black S-curve and gray reference)
// =============================================================================
// Quadratic bezier helper for gray reference line
if (!function_exists('quadBezier_sleeve')) {
    function quadBezier_sleeve($t, $p0, $p1, $p2) {
        $mt = 1 - $t;
        return $mt * $mt * $p0 + 2 * $mt * $t * $p1 + $t * $t * $p2;
    }
}

// Sample gap at t values along s2→s1 segment
$gapPoints_s2s1 = [];
$idx = 1;
foreach ([0.25, 0.5, 0.75] as $t) {
    $blackX = cubicBezier_sleeve($t, $leftShoulderX, $ctrl1_s2s1_x, $ctrl2_s2s1_x, $capX);
    $blackY = cubicBezier_sleeve($t, $leftShoulderY, $ctrl1_s2s1_y, $ctrl2_s2s1_y, $capY);
    $grayX = quadBezier_sleeve($t, $leftShoulderX, $s2_s1_ctrl_x, $capX);
    $grayY = quadBezier_sleeve($t, $leftShoulderY, $s2_s1_ctrl_y, $capY);
    $gapDist = sqrt(pow($blackX - $grayX, 2) + pow($blackY - $grayY, 2));
    $gapPoints_s2s1[] = [
        'blackX' => $blackX, 'blackY' => $blackY,
        'grayX' => $grayX, 'grayY' => $grayY,
        'gap' => $gapDist / $scale, 't' => $t,
        'blackLabel' => 'g' . $idx, 'grayLabel' => 'r' . $idx
    ];
    $idx++;
}

// Sample gap at t values along s1→s3 segment
$gapPoints_s1s3 = [];
foreach ([0.25, 0.5, 0.75] as $t) {
    $blackX = cubicBezier_sleeve($t, $capX, $ctrl1_s1s3_x, $ctrl2_s1s3_x, $rightShoulderX);
    $blackY = cubicBezier_sleeve($t, $capY, $ctrl1_s1s3_y, $ctrl2_s1s3_y, $rightShoulderY);
    $grayX = quadBezier_sleeve($t, $capX, $s1_s3_ctrl_x, $rightShoulderX);
    $grayY = quadBezier_sleeve($t, $capY, $s1_s3_ctrl_y, $rightShoulderY);
    $gapDist = sqrt(pow($blackX - $grayX, 2) + pow($blackY - $grayY, 2));
    $gapPoints_s1s3[] = [
        'blackX' => $blackX, 'blackY' => $blackY,
        'grayX' => $grayX, 'grayY' => $grayY,
        'gap' => $gapDist / $scale, 't' => $t,
        'blackLabel' => 'g' . $idx, 'grayLabel' => 'r' . $idx
    ];
    $idx++;
}

// =============================================================================
// SECTION 3: SVG GENERATION
// =============================================================================

// Pre-calculate curve control points for side box edges
$s51_s31_midX = (sn('s51', 'x') + sn('s31', 'x')) / 2;
$s51_s31_midY = (sn('s51', 'y') + sn('s31', 'y')) / 2;
$s51_s31_ctrl_x = $s51_s31_midX - (0.25 * $scale);
$s51_s31_ctrl_y = $s51_s31_midY;

$s41_s21_midX = (sn('s41', 'x') + sn('s21', 'x')) / 2;
$s41_s21_midY = (sn('s41', 'y') + sn('s21', 'y')) / 2;
$s41_s21_ctrl_x = $s41_s21_midX + (0.25 * $scale);
$s41_s21_ctrl_y = $s41_s21_midY;

// Generate SVG content
ob_start();
?>
<svg id="sleevePatternSvg" width="<?php echo $sleeveSvgWidth; ?>" height="<?php echo $sleeveSvgHeight; ?>"
     viewBox="<?php echo $sleeveBounds['minX']; ?> <?php echo $sleeveBounds['minY']; ?> <?php echo $sleeveBounds['width']; ?> <?php echo $sleeveBounds['height']; ?>"
     xmlns="http://www.w3.org/2000/svg">
    <defs>
        <pattern id="hatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <line x1="0" y1="0" x2="0" y2="4" stroke="#444" stroke-width="0.8"/>
        </pattern>
    </defs>
    <rect width="100%" height="100%" fill="#fff"/>

    <?php if ($isDevMode): ?>
    <!-- Gray reference line -->
    <path d="<?php echo $sleeveGray; ?>" fill="none" stroke="#999" stroke-width="1" stroke-dasharray="6,3"/>

    <!-- Gap measurements: s2→s1 segment -->
    <?php foreach ($gapPoints_s2s1 as $gp): ?>
    <circle cx="<?php echo $gp['blackX']; ?>" cy="<?php echo $gp['blackY']; ?>" r="2" fill="#E67E22" stroke="#fff" stroke-width="0.5"/>
    <circle cx="<?php echo $gp['grayX']; ?>" cy="<?php echo $gp['grayY']; ?>" r="2" fill="#999" stroke="#fff" stroke-width="0.5"/>
    <line x1="<?php echo $gp['blackX']; ?>" y1="<?php echo $gp['blackY']; ?>"
          x2="<?php echo $gp['grayX']; ?>" y2="<?php echo $gp['grayY']; ?>"
          stroke="#E67E22" stroke-width="0.5" stroke-dasharray="2,2"/>
    <text x="<?php echo ($gp['blackX'] + $gp['grayX']) / 2 + 3; ?>" y="<?php echo ($gp['blackY'] + $gp['grayY']) / 2 - 2; ?>"
          font-size="7" fill="#E67E22" font-weight="bold"><?php echo $gp['blackLabel']; ?>-<?php echo number_format($gp['gap'], 2); ?>"</text>
    <?php endforeach; ?>

    <!-- Gap measurements: s1→s3 segment -->
    <?php foreach ($gapPoints_s1s3 as $gp): ?>
    <circle cx="<?php echo $gp['blackX']; ?>" cy="<?php echo $gp['blackY']; ?>" r="2" fill="#E67E22" stroke="#fff" stroke-width="0.5"/>
    <circle cx="<?php echo $gp['grayX']; ?>" cy="<?php echo $gp['grayY']; ?>" r="2" fill="#999" stroke="#fff" stroke-width="0.5"/>
    <line x1="<?php echo $gp['blackX']; ?>" y1="<?php echo $gp['blackY']; ?>"
          x2="<?php echo $gp['grayX']; ?>" y2="<?php echo $gp['grayY']; ?>"
          stroke="#E67E22" stroke-width="0.5" stroke-dasharray="2,2"/>
    <text x="<?php echo ($gp['blackX'] + $gp['grayX']) / 2 + 3; ?>" y="<?php echo ($gp['blackY'] + $gp['grayY']) / 2 - 2; ?>"
          font-size="7" fill="#E67E22" font-weight="bold"><?php echo $gp['blackLabel']; ?>-<?php echo number_format($gp['gap'], 2); ?>"</text>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Black pattern outline -->
    <path d="<?php echo $sleeveBlack; ?>" fill="none" stroke="#000" stroke-width="1.5"/>

    <!-- Right side box hatch fill -->
    <polygon points="<?php echo sn('s5','x'); ?>,<?php echo sn('s5','y'); ?> <?php echo sn('s51','x'); ?>,<?php echo sn('s51','y'); ?> <?php echo sn('s31','x'); ?>,<?php echo sn('s31','y'); ?> <?php echo sn('s3','x'); ?>,<?php echo sn('s3','y'); ?>"
             fill="url(#hatch)" stroke="none"/>

    <!-- Right side box: s5 -> s51 -> s31 -> s3 -->
    <line x1="<?php echo sn('s5','x'); ?>" y1="<?php echo sn('s5','y'); ?>"
          x2="<?php echo sn('s51','x'); ?>" y2="<?php echo sn('s51','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M <?php echo sn('s51','x'); ?>,<?php echo sn('s51','y'); ?> Q <?php echo $s51_s31_ctrl_x; ?>,<?php echo $s51_s31_ctrl_y; ?> <?php echo sn('s31','x'); ?>,<?php echo sn('s31','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <line x1="<?php echo sn('s31','x'); ?>" y1="<?php echo sn('s31','y'); ?>"
          x2="<?php echo sn('s3','x'); ?>" y2="<?php echo sn('s3','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Ease text (right side box - centered between s5, s51, s3, s31) -->
    <?php
    // Horizontally centered between inner edge (s5,s3) and outer edge (s51,s31)
    // Adjust 0.25" left to account for inward curve bulge
    $rightEaseTextX = (sn('s5', 'x') + sn('s51', 'x') + sn('s3', 'x') + sn('s31', 'x')) / 4 - (0.25 * $scale);
    // Vertically centered between all 4 corner points
    $rightEaseTextY = (sn('s5', 'y') + sn('s51', 'y') + sn('s3', 'y') + sn('s31', 'y')) / 4;
    // Calculate angle of s5-s3 line
    $rightAngle = rad2deg(atan2(sn('s3', 'y') - sn('s5', 'y'), sn('s3', 'x') - sn('s5', 'x')));
    ?>
    <text x="<?php echo $rightEaseTextX; ?>" y="<?php echo $rightEaseTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(<?php echo $rightAngle; ?>, <?php echo $rightEaseTextX; ?>, <?php echo $rightEaseTextY; ?>)">---- ease -----</text>

    <!-- Left side box hatch fill -->
    <polygon points="<?php echo sn('s4','x'); ?>,<?php echo sn('s4','y'); ?> <?php echo sn('s41','x'); ?>,<?php echo sn('s41','y'); ?> <?php echo sn('s21','x'); ?>,<?php echo sn('s21','y'); ?> <?php echo sn('s2','x'); ?>,<?php echo sn('s2','y'); ?>"
             fill="url(#hatch)" stroke="none"/>

    <!-- Left side box: s4 -> s41 -> s21 -> s2 (1" wide rectangle) -->
    <line x1="<?php echo sn('s4','x'); ?>" y1="<?php echo sn('s4','y'); ?>"
          x2="<?php echo sn('s41','x'); ?>" y2="<?php echo sn('s41','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M <?php echo sn('s41','x'); ?>,<?php echo sn('s41','y'); ?> Q <?php echo $s41_s21_ctrl_x; ?>,<?php echo $s41_s21_ctrl_y; ?> <?php echo sn('s21','x'); ?>,<?php echo sn('s21','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <line x1="<?php echo sn('s21','x'); ?>" y1="<?php echo sn('s21','y'); ?>"
          x2="<?php echo sn('s2','x'); ?>" y2="<?php echo sn('s2','y'); ?>"
          stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Ease text (left side box - centered between s4, s41, s2, s21) -->
    <?php
    // Horizontally centered between inner edge (s4,s2) and outer edge (s41,s21)
    // Adjust 0.25" right to account for inward curve bulge
    $leftEaseTextX = (sn('s4', 'x') + sn('s41', 'x') + sn('s2', 'x') + sn('s21', 'x')) / 4 + (0.25 * $scale);
    // Vertically centered between all 4 corner points
    $leftEaseTextY = (sn('s4', 'y') + sn('s41', 'y') + sn('s2', 'y') + sn('s21', 'y')) / 4;
    // Calculate angle of s4-s2 line (same as s41-s21)
    $leftAngle = rad2deg(atan2(sn('s2', 'y') - sn('s4', 'y'), sn('s2', 'x') - sn('s4', 'x')));
    ?>
    <text x="<?php echo $leftEaseTextX; ?>" y="<?php echo $leftEaseTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(<?php echo $leftAngle; ?>, <?php echo $leftEaseTextX; ?>, <?php echo $leftEaseTextY; ?>)">---- ease -----</text>

    <!-- "right" text (horizontally centered between s2 and s1) -->
    <?php
    // Horizontally centered between s2.x and s1.x (fold line midpoint)
    $rightTextX = (sn('s2', 'x') + sn('s1', 'x')) / 2;
    // Vertically centered between shoulder and wrist
    $rightTextY = (sn('s2', 'y') + sn('s4', 'y')) / 2;
    ?>
    <text x="<?php echo $rightTextX; ?>" y="<?php echo $rightTextY; ?>"
          font-size="10" fill="#666" text-anchor="middle"
          transform="rotate(-90, <?php echo $rightTextX; ?>, <?php echo $rightTextY; ?>)">-- right --</text>

    <!-- Red cutting line (TOP): s21 → s2 → s1 → s3 → s31 -->
    <path d="<?php echo $sleeveRedTop; ?>" fill="none" stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>

    <!-- Red cutting line (BOTTOM): s41 → s4 → s5 → s51 -->
    <path d="<?php echo $sleeveRedBottom; ?>" fill="none" stroke="#DC2626" stroke-width="0.5" stroke-dasharray="6,3"/>

    <!-- Center fold line -->
    <path d="<?php echo $centerFoldLine; ?>" fill="none" stroke="#808080" stroke-width="0.5" stroke-dasharray="6,4"/>

    <!-- Fold label (upper half of fold line) -->
    <?php
    $foldLabelX = $capX + (0.3 * $scale);
    $foldLabelY = $capY + ($leftWristY - $capY) * 0.3; // 30% from top
    ?>
    <text x="<?php echo $foldLabelX; ?>" y="<?php echo $foldLabelY; ?>"
          font-size="10" fill="#666" transform="rotate(-90 <?php echo $foldLabelX; ?> <?php echo $foldLabelY; ?>)">FOLD LINE</text>

    <!-- Scissors Icon on sleeve cutting line (at s21 on red cutting line) -->
    <?php
    $scissorsX = $redS21_x;
    $scissorsY = $redS21_y;
    echo scissorsIcon($scissorsX, $scissorsY, 0, 0.4);
    ?>

    <!-- Grainline (lower half, below fold label) -->
    <?php
    $grainX = sn('s1', 'x'); // Center of sleeve
    $grainY = sn('s1', 'y') + (sn('s4', 'y') - sn('s1', 'y')) * 0.65; // 65% from top
    $grainLength = 3; // 3 inches long
    echo grainLine($grainX, $grainY, $grainLength, 'vertical');
    ?>

    <?php if ($isDevMode): ?>
    <!-- Node Labels (Dev Mode Only) -->
    <?php foreach ($sleeveNodes as $name => $node): ?>
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
    <?php foreach ($sleeveNodes as $name => $node): ?>
        <?php if (isset($node['color']) && $node['color'] === 'red'): ?>
        <circle cx="<?php echo $node['x']; ?>" cy="<?php echo $node['y']; ?>" r="2"
                fill="#DC2626" stroke="#fff" stroke-width="1"/>
        <text x="<?php echo $node['x'] + 3; ?>" y="<?php echo $node['y'] - 3; ?>"
              font-size="8" fill="#DC2626" font-weight="300" opacity="0.7"><?php echo $name; ?></text>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
</svg>
<?php
$sleeveSvgContent = ob_get_clean();

// =============================================================================
// SECTION 4: EXPORT DATA (For composites & PDF)
// =============================================================================
$sleevePatternData = [
    'name' => 'SLEEVE',
    'type' => 'sariSleeve',
    'nodes' => $sleeveNodes,
    'svg_content' => $sleeveSvgContent,
    'paths' => [
        'black' => $sleeveBlack,
        'gray' => $sleeveGray,
        'redTop' => $sleeveRedTop,
        'redBottom' => $sleeveRedBottom,
        'centerFold' => $centerFoldLine
    ],
    'dimensions' => [
        'width' => $sleeveSvgWidth,
        'height' => $sleeveSvgHeight,
        'widthInches' => $sleeveSvgWidthInches,
        'heightInches' => $sleeveSvgHeightInches,
        'viewScale' => $sleeveViewScale
    ],
    'bounds' => $sleeveBounds,
    'measurements' => [
        'capHeight' => $sleeveCapDims['capHeight'],
        'halfWidth' => $sleeveCapDims['halfWidth'],
        'sleeveWidth' => $sleeveCapDims['sleeveWidth'],
        'diagonal' => $sleeveCapDims['diagonal'],
        'adjusted' => $sleeveCapDims['adjusted'],
        'defaultCapHeight' => $sleeveCapDims['defaultCapHeight'],
        'saround' => $saround,
        'actualArmholePathInches' => $actualArmholePathInches,
        'targetArmhole' => $armhole
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
    <title>Blouse Sleeve - <?php echo htmlspecialchars($customerName); ?></title>
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
        <h1>Blouse Sleeve Pattern</h1>
        <div class="info">
            Customer: <?php echo htmlspecialchars($customerName); ?> |
            Sleeve Length: <?php echo $slength; ?>" |
            Saround (bicep): <?php echo $saround; ?>" |
            Cap Height: <?php echo number_format($sleeveCapDims['capHeight'], 2); ?>"<?php if ($sleeveCapDims['adjusted']): ?> <span style="color:#DC2626">(adjusted from <?php echo number_format($sleeveCapDims['defaultCapHeight'], 2); ?>")</span><?php endif; ?> |
            Sleeve Width: <?php echo number_format($sleeveCapDims['sleeveWidth'], 2); ?>" |
            Diagonal (armhole/2): <?php echo number_format($sleeveCapDims['diagonal'], 2); ?>" |
            Actual s2→s1: <?php echo number_format($s2_s1_diagonal_inches, 2); ?>" |
            Actual s1→s3: <?php echo number_format($s1_s3_diagonal_inches, 2); ?>"
        </div>

        <div class="svg-container">
            <?php echo $sleeveSvgContent; ?>
        </div>
    </div>
</body>
</html>
