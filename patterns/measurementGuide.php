<?php
/**
 * =============================================================================
 * PATTERN READING GUIDE — 3-Page PDF (A4 Size) with Dimension Annotations
 * =============================================================================
 *
 * Page 1: Front + Patti
 * Page 2: Back
 * Page 3: Sleeve
 *
 * Uses REAL pattern SVGs via COMPOSITE_MODE and overlays engineering-style
 * dimension arrows on the actual node positions.
 *
 * Usage:
 *   Browser: measurementGuide.php?id=X              (measurement_id)
 *            measurementGuide.php?customer_id=X     (legacy: customer_id)
 *   CLI:     php measurementGuide.php               (customer_id=11 default)
 *
 * Integrates with pattern-download.php flow for paid/free patterns.
 * Always generates A4 size PDF.
 *
 * @author CM-2025
 * @date February 2026
 */

error_reporting(E_ALL & ~E_DEPRECATED);

// =============================================================================
// SETUP ENVIRONMENT
// =============================================================================
$isCLI = (php_sapi_name() === 'cli');
if ($isCLI) {
    $_GET['customer_id'] = $_GET['customer_id'] ?? 11;
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

// Always use print mode for measurement guide (hides node labels and dev annotations)
$_GET['mode'] = 'print';

// Get pattern type for filename (e.g., "Saree Blouse - 3 Dart")
$patternType = isset($_GET['type']) ? $_GET['type'] : 'SariBlouse';

// Handle measurement_id parameter (new flow) or customer_id (legacy)
if (isset($_GET['id']) && !isset($_GET['customer_id'])) {
    // New flow: look up customer_id from measurement_id
    require_once __DIR__ . '/../config/database.php';
    $measurementId = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT customer_id, user_id FROM measurements WHERE id = ?");
    $stmt->execute([$measurementId]);
    $measurement = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($measurement) {
        if ($measurement['customer_id']) {
            $_GET['customer_id'] = $measurement['customer_id'];
        } else {
            // Self measurement - use user_id to get self measurement
            $_GET['measurement_id'] = $measurementId;
        }
    }
}

if (!defined('COMPOSITE_MODE')) {
    define('COMPOSITE_MODE', true);
}

// =============================================================================
// LOAD ALL THREE PATTERNS
// =============================================================================
require_once __DIR__ . '/sariBlouses/patternConfig.php';

// 1) Front + Patti
require_once __DIR__ . '/sariBlouses/sariBlouse3TFront.php';
$fn = $frontPatternData['nodes'];
$pn = $frontPatternData['pattiNodes'] ?? [];
$frontSvg = $frontPatternData['svg_content'];
$frontDims = $frontPatternData['dimensions'];
$frontPatti = $frontPatternData['patti'] ?? null;

// 2) Back
require_once __DIR__ . '/sariBlouses/sariBlouseBack.php';
$bn = $backPatternData['nodes'];
$backSvg = $backPatternData['svg_content'];
$backBnds = $backPatternData['bounds'];

// 3) Sleeve
require_once __DIR__ . '/sariBlouses/sariSleeve.php';
$slN = $sleevePatternData['nodes'];
$sleeveSvg = $sleevePatternData['svg_content'];
$sleeveBnds = $sleevePatternData['bounds'];

// TCPDF
require_once __DIR__ . '/../vendor/autoload.php';

$s = $scale;  // 25.4 px/inch
$_dimScale = $scale;
$_dimFontSize = 0.28 * $scale;  // Uniform measurement font size across all dimension types

// =============================================================================
// DIMENSION LINE HELPERS  (uniform font size for all measurements)
// =============================================================================

/**
 * Horizontal dimension arrow with extension lines
 */
function dimH($x1, $x2, $refY, $label, $offsetPx, $color = '#444') {
    global $_dimScale, $_dimFontSize;
    $s = $_dimScale;
    $a = 0.14 * $s;  $ah = $a * 0.45;  $fs = $_dimFontSize;
    $ly = $refY + $offsetPx;
    $gap = 0.04 * $s * ($offsetPx > 0 ? 1 : -1);

    $svg  = sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.4" stroke-dasharray="2,2"/>', $x1, $refY + $gap, $x1, $ly, $color);
    $svg .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.4" stroke-dasharray="2,2"/>', $x2, $refY + $gap, $x2, $ly, $color);
    $svg .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.6"/>', $x1, $ly, $x2, $ly, $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>', $x1, $ly, $x1+$a, $ly-$ah, $x1+$a, $ly+$ah, $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>', $x2, $ly, $x2-$a, $ly-$ah, $x2-$a, $ly+$ah, $color);
    $mx = ($x1 + $x2) / 2;
    $ty = ($offsetPx > 0) ? $ly + $fs + 1 : $ly - 2;
    $svg .= sprintf('<text x="%.1f" y="%.1f" fill="%s" font-size="%.1f" font-family="Arial, sans-serif" text-anchor="middle" font-weight="bold">%s</text>',
        $mx, $ty, $color, $fs, htmlspecialchars($label));
    return $svg;
}

/**
 * Vertical dimension arrow with extension lines
 */
function dimV($refX, $y1, $y2, $label, $offsetPx, $color = '#444') {
    global $_dimScale, $_dimFontSize;
    $s = $_dimScale;
    $a = 0.14 * $s;  $ah = $a * 0.45;  $fs = $_dimFontSize;
    $lx = $refX + $offsetPx;
    $gap = 0.04 * $s * ($offsetPx > 0 ? 1 : -1);

    $svg  = sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.4" stroke-dasharray="2,2"/>', $refX + $gap, $y1, $lx, $y1, $color);
    $svg .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.4" stroke-dasharray="2,2"/>', $refX + $gap, $y2, $lx, $y2, $color);
    $svg .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.6"/>', $lx, $y1, $lx, $y2, $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>', $lx, $y1, $lx-$ah, $y1+$a, $lx+$ah, $y1+$a, $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>', $lx, $y2, $lx-$ah, $y2-$a, $lx+$ah, $y2-$a, $color);
    $my = ($y1 + $y2) / 2;
    $tx = ($offsetPx > 0) ? $lx + $fs * 0.6 : $lx - $fs * 0.4;
    $svg .= sprintf('<text x="%.1f" y="%.1f" fill="%s" font-size="%.1f" font-family="Arial, sans-serif" text-anchor="middle" font-weight="bold" transform="rotate(-90 %.1f %.1f)">%s</text>',
        $tx, $my, $color, $fs, $tx, $my, htmlspecialchars($label));
    return $svg;
}

/**
 * Diagonal dimension line with arrowheads
 */
function dimDiag($x1, $y1, $x2, $y2, $label, $color = '#444') {
    global $_dimScale, $_dimFontSize;
    $s = $_dimScale;
    $fs = $_dimFontSize;  $a = 0.14 * $s;
    $angle = atan2($y2 - $y1, $x2 - $x1);

    $svg  = sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="0.6" stroke-dasharray="3,2"/>', $x1, $y1, $x2, $y2, $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>',
        $x1, $y1, $x1 + $a*cos($angle-0.4), $y1 + $a*sin($angle-0.4), $x1 + $a*cos($angle+0.4), $y1 + $a*sin($angle+0.4), $color);
    $svg .= sprintf('<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="%s"/>',
        $x2, $y2, $x2 - $a*cos($angle-0.4), $y2 - $a*sin($angle-0.4), $x2 - $a*cos($angle+0.4), $y2 - $a*sin($angle+0.4), $color);
    $mx = ($x1 + $x2) / 2;
    $my = ($y1 + $y2) / 2;
    $deg = rad2deg($angle);
    $svg .= sprintf('<text x="%.1f" y="%.1f" fill="%s" font-size="%.1f" font-family="Arial, sans-serif" text-anchor="middle" font-weight="bold" transform="rotate(%.1f %.1f %.1f)">%s</text>',
        $mx, $my - 3, $color, $fs, $deg, $mx, $my - 3, htmlspecialchars($label));
    return $svg;
}

/**
 * Prepare SVG for PDF: expand viewBox, inject annotations, sanitize for TCPDF
 * Returns [$cleanSvg, $newViewBoxWidth, $newViewBoxHeight]
 */
function prepareSvgForPdf($svgRaw, $annotations, $expandL, $expandT, $expandR, $expandB) {
    // Parse existing viewBox
    if (preg_match('/viewBox="([^"]*)"/', $svgRaw, $m)) {
        $parts = preg_split('/\s+/', trim($m[1]));
        $vbX = (float)$parts[0];
        $vbY = (float)$parts[1];
        $vbW = (float)$parts[2];
        $vbH = (float)$parts[3];
    } else {
        preg_match('/width="([\d.]+)"/', $svgRaw, $wm);
        preg_match('/height="([\d.]+)"/', $svgRaw, $hm);
        $vbX = 0; $vbY = 0;
        $vbW = (float)($wm[1] ?? 500);
        $vbH = (float)($hm[1] ?? 500);
    }

    $newVbX = $vbX - $expandL;
    $newVbY = $vbY - $expandT;
    $newVbW = $vbW + $expandL + $expandR;
    $newVbH = $vbH + $expandT + $expandB;

    // Replace viewBox
    $svg = preg_replace(
        '/viewBox="[^"]*"/',
        sprintf('viewBox="%.1f %.1f %.1f %.1f"', $newVbX, $newVbY, $newVbW, $newVbH),
        $svgRaw
    );

    // Inject annotations before </svg>
    $svg = str_replace('</svg>', "\n<!-- MEASUREMENT ANNOTATIONS -->\n" . $annotations . "\n</svg>", $svg);

    // Sanitize for TCPDF: remove <pattern> elements, replace pattern fills
    $svg = preg_replace('/<pattern\b[^>]*>.*?<\/pattern>/s', '', $svg);
    $svg = preg_replace('/fill="url\(#[^)]+\)"/', 'fill="#ddd"', $svg);

    // Remove grainlines (groups with class="grainline")
    $svg = preg_replace('/<g\s+class="grainline"[^>]*>.*?<\/g>/s', '', $svg);

    return [$svg, $newVbW, $newVbH];
}

// =============================================================================
// PAGE 1: FRONT + PATTI ANNOTATIONS
// =============================================================================
$ann1 = '';

// --- Horizontal dimensions ---
$ann1 .= dimH($fn['a0']['x'], $fn['a7']['x'], $fn['a7']['y'],
    'Bust/4: ' . number_format($qBust, 2) . '"', 0.8 * $s);

// Neck Width: a0 → a11 (neck center to shoulder start)
$neckW_in = abs($fn['a11']['x'] - $fn['a0']['x']) / $s;
$ann1 .= dimH($fn['a0']['x'], $fn['a11']['x'], $fn['a10']['y'],
    'Neck W: ' . number_format($neckW_in, 2) . '"', -0.8 * $s);

// Shoulder: a11 → a10 (shoulder width, same row)
$ann1 .= dimH($fn['a11']['x'], $fn['a10']['x'], $fn['a10']['y'],
    'Shoulder: ' . number_format($shoulder, 1) . '"', -0.8 * $s);

$ann1 .= dimH($fn['a7']['x'], $fn['a71']['x'], $fn['a71']['y'],
    'Ease: 1.5"', -0.5 * $s, '#E67E22');

// --- Waist line breakup (staggered into two rows to prevent text overlap) ---
$waistDimY = $fn['a5']['y'];
$wRow1 = 1.4 * $s;   // Row 1: segments 1 & 3 (left waist, right waist)
$wRow2 = 2.2 * $s;   // Row 2: segments 2 & 4 (tuck, ease)
$wRow3 = 3.0 * $s;   // Row 3: total

$a3_a4_in = abs($fn['a4']['x'] - $fn['a3']['x']) / $s;
$ann1 .= dimH($fn['a3']['x'], $fn['a4']['x'], $waistDimY,
    number_format($a3_a4_in, 2) . '"', $wRow1);

$a4_a41_in = abs($fn['a41']['x'] - $fn['a4']['x']) / $s;
$ann1 .= dimH($fn['a4']['x'], $fn['a41']['x'], $waistDimY,
    'Tuck ' . number_format($a4_a41_in, 2) . '"', $wRow2, '#9333EA');

$a41_a5_in = abs($fn['a5']['x'] - $fn['a41']['x']) / $s;
$ann1 .= dimH($fn['a41']['x'], $fn['a5']['x'], $waistDimY,
    number_format($a41_a5_in, 2) . '"', $wRow1);

$ann1 .= dimH($fn['a5']['x'], $fn['a51']['x'], $waistDimY,
    'Ease 1.5"', $wRow2, '#E67E22');

$waistEndX = $fn['a3']['x'] + $qWaist * $s;
$ann1 .= dimH($fn['a3']['x'], $waistEndX, $waistDimY,
    'Waist/4: ' . number_format($qWaist, 2) . '"', $wRow3);

// --- Vertical dimensions (spread across different x positions to avoid overlap) ---

// LEFT EDGE: Full front length (a0 → a3) - outermost left
$ann1 .= dimV($fn['a0']['x'], $fn['a0']['y'], $fn['a3']['y'],
    'F.Length: ' . number_format($flength, 1) . '"', -1.4 * $s);

// LEFT EDGE: Front neck depth (a0 → a1) - inner left, red
$fnDepthInches = abs($fn['a1']['y'] - $fn['a0']['y']) / $s;
$ann1 .= dimV($fn['a0']['x'], $fn['a0']['y'], $fn['a1']['y'],
    'F.Neck: ' . number_format($fnDepthInches, 2) . '"', -0.7 * $s, '#DC2626');

// CENTER-LEFT: Apex depth (a0.y → b1) at b1.x
$ann1 .= dimV($fn['b1']['x'], $fn['a0']['y'], $fn['b1']['y'],
    'Apex: ' . number_format($apex, 1) . '"', -0.6 * $s, '#E67E22');

// CENTER-RIGHT: Tuck start (a0.y → b3) at b1.x + offset
$tuckStartIn = abs($fn['b3']['y'] - $fn['a0']['y']) / $s;
$ann1 .= dimV($fn['b1']['x'], $fn['a0']['y'], $fn['b3']['y'],
    'Tuck@: ' . number_format($tuckStartIn, 2) . '"', 0.6 * $s, '#9333EA');

// RIGHT SIDE: Armhole height (a10 → a7)
$armHtInches = abs($fn['a7']['y'] - $fn['a10']['y']) / $s;
$ann1 .= dimV($fn['a10']['x'], $fn['a10']['y'], $fn['a7']['y'],
    'ArmHt: ' . number_format($armHtInches, 2) . '"', 0.6 * $s, '#DC2626');

// BOTTOM TUCK: height (b3 → a4) at a4.x
$tuckHtInches = abs($fn['a4']['y'] - $fn['b3']['y']) / $s;
$ann1 .= dimV($fn['a4']['x'], $fn['b3']['y'], $fn['a4']['y'],
    'Tuck Ht: ' . number_format($tuckHtInches, 2) . '"', -0.6 * $s, '#9333EA');

// BOTTOM TUCK: width (a4 → a41) - horizontal span of the tuck
$tuckWdInches = abs($fn['a41']['x'] - $fn['a4']['x']) / $s;
$ann1 .= dimH($fn['a4']['x'], $fn['a41']['x'], $fn['b3']['y'],
    'Tuck W: ' . number_format($tuckWdInches, 2) . '"', -0.5 * $s, '#9333EA');

// --- Side tuck (c1 → c2 → c3) - positioned to avoid overlap ---
$sideTuckW = abs($fn['c3']['y'] - $fn['c1']['y']) / $s;
$ann1 .= dimV($fn['c1']['x'], $fn['c1']['y'], $fn['c3']['y'],
    'Side: ' . number_format($sideTuckW, 2) . '"', -2.0 * $s, '#0891B2');

$sideTuckD = abs($fn['c2']['x'] - $fn['c1']['x']) / $s;
$ann1 .= dimH($fn['c1']['x'], $fn['c2']['x'], $fn['c2']['y'],
    'Depth: ' . number_format($sideTuckD, 2) . '"', 0.6 * $s, '#0891B2');

// --- Armhole tuck (e1 → e2 → e3) - diagonal dimensions ---
$armTuckW = sqrt(pow($fn['e3']['x'] - $fn['e1']['x'], 2) + pow($fn['e3']['y'] - $fn['e1']['y'], 2)) / $s;
$ann1 .= dimDiag($fn['e1']['x'], $fn['e1']['y'], $fn['e3']['x'], $fn['e3']['y'],
    'ArmTuck: ' . number_format($armTuckW, 2) . '"', '#7C3AED');

// --- Armhole curve label ---
$armLblX = ($fn['a10']['x'] + $fn['a7']['x']) / 2 + 0.5 * $s;
$armLblY = ($fn['a10']['y'] + $fn['a7']['y']) / 2;
$ann1 .= sprintf('<text x="%.1f" y="%.1f" fill="#DC2626" font-size="%.1f" font-family="Arial" font-weight="bold">Armhole: %s"</text>',
    $armLblX, $armLblY, $_dimFontSize, number_format($armhole / 2, 2));

// --- Patti annotations (width + 3 heights: left, center, right) ---
if (!empty($pn) && isset($pn['p4']) && isset($pn['p5']) && isset($pn['p1'])) {
    // Patti width (bottom edge p4 → p5)
    $pattiWIn = abs($pn['p5']['x'] - $pn['p4']['x']) / $s;
    $ann1 .= dimH($pn['p4']['x'], $pn['p5']['x'], $pn['p4']['y'],
        'Patti W: ' . number_format($pattiWIn, 2) . '"', 0.8 * $s, '#059669');

    // Left height: p1 → p4 (left edge) - offset left
    $pattiLHt = abs($pn['p4']['y'] - $pn['p1']['y']) / $s;
    $ann1 .= dimV($pn['p1']['x'], $pn['p1']['y'], $pn['p4']['y'],
        'L: ' . number_format($pattiLHt, 2) . '"', -0.6 * $s, '#059669');

    // Center height: p2 → p41 (neck curve to bottom center)
    if (isset($pn['p2']) && isset($pn['p41'])) {
        $pattiCHt = abs($pn['p41']['y'] - $pn['p2']['y']) / $s;
        $ann1 .= dimV($pn['p2']['x'], $pn['p2']['y'], $pn['p41']['y'],
            'C: ' . number_format($pattiCHt, 2) . '"', -0.6 * $s, '#059669');
    }

    // Right height: p3 → p5 (right edge) - offset right
    if (isset($pn['p3'])) {
        $pattiRHt = abs($pn['p5']['y'] - $pn['p3']['y']) / $s;
        $ann1 .= dimV($pn['p5']['x'], $pn['p3']['y'], $pn['p5']['y'],
            'R: ' . number_format($pattiRHt, 2) . '"', 0.6 * $s, '#059669');
    }
}

// Prepare front page SVG (3.5" bottom for staggered waist breakup + patti)
list($frontClean, $frontVbW, $frontVbH) = prepareSvgForPdf(
    $frontSvg, $ann1, 1.5 * $s, 0.5 * $s, 0.5 * $s, 3.5 * $s
);

// =============================================================================
// PAGE 2: BACK ANNOTATIONS
// =============================================================================
$ann2 = '';

// --- Horizontal dimensions ---
// Bust/4 width (z0 → z5 at bust line)
$backBustW = abs($bn['z5']['x'] - $bn['z0']['x']) / $s;
$ann2 .= dimH($bn['z0']['x'], $bn['z5']['x'], $bn['z5']['y'],
    'Bust/4: ' . number_format($backBustW, 2) . '"', 0.8 * $s);

// Shoulder (z9 → z8)
$backShoulderW = abs($bn['z8']['x'] - $bn['z9']['x']) / $s;
$ann2 .= dimH($bn['z9']['x'], $bn['z8']['x'], min($bn['z8']['y'], $bn['z9']['y']),
    'Shoulder: ' . number_format($backShoulderW, 2) . '"', -0.8 * $s);

// Ease box (z5 → z51)
if (isset($bn['z51'])) {
    $ann2 .= dimH($bn['z5']['x'], $bn['z51']['x'], $bn['z51']['y'],
        'Ease: 1.5"', -0.5 * $s, '#E67E22');
}

// Bottom width (z2 → z3 = bust/4, same as bust line)
$backWaistW = abs($bn['z3']['x'] - $bn['z2']['x']) / $s;
$ann2 .= dimH($bn['z2']['x'], $bn['z3']['x'], $bn['z2']['y'],
    'Bust/4: ' . number_format($backWaistW, 2) . '"', 1.5 * $s);

// --- Vertical dimensions ---
// Back length (z0 → z2)
$ann2 .= dimV($bn['z0']['x'], $bn['z0']['y'], $bn['z2']['y'],
    'B.Length: ' . number_format($blength, 1) . '"', -1.2 * $s);

// Back neck depth (z0 → z1)
$backNeckIn = abs($bn['z1']['y'] - $bn['z0']['y']) / $s;
$ann2 .= dimV($bn['z0']['x'], $bn['z0']['y'], $bn['z1']['y'],
    'B.Neck: ' . number_format($backNeckIn, 2) . '"', -0.6 * $s, '#DC2626');

// Armhole height (z8 → z6)
$backArmHt = abs($bn['z6']['y'] - $bn['z8']['y']) / $s;
$ann2 .= dimV($bn['z8']['x'], $bn['z8']['y'], $bn['z6']['y'],
    'ArmHt: ' . number_format($backArmHt, 2) . '"', 0.5 * $s, '#DC2626');

// --- Back tuck (zb2 → zb3 → zb4) ---
if (isset($bn['zb2']) && isset($bn['zb4'])) {
    $backTuckW = abs($bn['zb4']['x'] - $bn['zb2']['x']) / $s;
    $ann2 .= dimH($bn['zb2']['x'], $bn['zb4']['x'], $bn['zb2']['y'],
        'Tuck W: ' . number_format($backTuckW, 2) . '"', 0.5 * $s, '#9333EA');

    if (isset($bn['zb3']) && isset($bn['zb1'])) {
        $backTuckH = abs($bn['zb1']['y'] - $bn['zb3']['y']) / $s;
        $ann2 .= dimV($bn['zb2']['x'], $bn['zb3']['y'], $bn['zb1']['y'],
            'Tuck Ht: ' . number_format($backTuckH, 2) . '"', -0.5 * $s, '#9333EA');
    }
}

// --- Armhole curve label ---
$backArmLblX = ($bn['z8']['x'] + $bn['z6']['x']) / 2 + 0.5 * $s;
$backArmLblY = ($bn['z8']['y'] + $bn['z6']['y']) / 2;
$ann2 .= sprintf('<text x="%.1f" y="%.1f" fill="#DC2626" font-size="%.1f" font-family="Arial" font-weight="bold">Armhole: %s"</text>',
    $backArmLblX, $backArmLblY, $_dimFontSize, number_format($armhole / 2, 2));

// Prepare back page SVG
list($backClean, $backVbW, $backVbH) = prepareSvgForPdf(
    $backSvg, $ann2, 1.5 * $s, 0.5 * $s, 0.5 * $s, 2.0 * $s
);

// =============================================================================
// PAGE 3: SLEEVE ANNOTATIONS (rotated -90° for horizontal layout)
// =============================================================================

// --- Rotate sleeve -90° around its center ---
$slvVbW = $sleeveBnds['width'];
$slvVbH = $sleeveBnds['height'];
$slvCx = $sleeveBnds['minX'] + $slvVbW / 2;
$slvCy = $sleeveBnds['minY'] + $slvVbH / 2;

// Rotate point -90° around center: (x,y) → (cx+(y-cy), cy-(x-cx))
$rot = function($node) use ($slvCx, $slvCy) {
    return [
        'x' => $slvCx + ($node['y'] - $slvCy),
        'y' => $slvCy - ($node['x'] - $slvCx)
    ];
};
$rs = [];
foreach (['s1','s2','s3','s4','s5'] as $k) $rs[$k] = $rot($slN[$k]);
if (isset($slN['s31'])) $rs['s31'] = $rot($slN['s31']);

// New viewBox after rotation (width ↔ height swap)
$newSlvVbX = $slvCx - $slvVbH / 2;
$newSlvVbY = $slvCy - $slvVbW / 2;
$newSlvVbW = $slvVbH;
$newSlvVbH = $slvVbW;

// Wrap sleeve SVG in rotation group with updated viewBox
$rotSvg = preg_replace('/viewBox="[^"]*"/',
    sprintf('viewBox="%.1f %.1f %.1f %.1f"', $newSlvVbX, $newSlvVbY, $newSlvVbW, $newSlvVbH),
    $sleeveSvg);
$tagEnd = strpos($rotSvg, '>', strpos($rotSvg, '<svg'));
$bgRect = sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fff"/>',
    $newSlvVbX - 3*$s, $newSlvVbY - 3*$s, $newSlvVbW + 6*$s, $newSlvVbH + 6*$s);
$rotSvg = substr($rotSvg, 0, $tagEnd + 1)
    . "\n" . $bgRect
    . sprintf("\n<g transform=\"rotate(-90, %.1f, %.1f)\">", $slvCx, $slvCy)
    . substr($rotSvg, $tagEnd + 1);
$rotSvg = str_replace('</svg>', "</g>\n</svg>", $rotSvg);

// --- Build annotations in rotated coordinate space ---
$ann3 = '';

// Bicep width (was horizontal s2→s3, now vertical after rotation)
$bicepW = abs($slN['s3']['x'] - $slN['s2']['x']) / $s;
$ann3 .= dimV($rs['s2']['x'], min($rs['s2']['y'], $rs['s3']['y']), max($rs['s2']['y'], $rs['s3']['y']),
    'Bicep: ' . number_format($bicepW, 2) . '"', 0.8 * $s);

// Wrist opening (was horizontal s4→s5, now vertical)
$wristW = abs($slN['s5']['x'] - $slN['s4']['x']) / $s;
$ann3 .= dimV($rs['s4']['x'], min($rs['s4']['y'], $rs['s5']['y']), max($rs['s4']['y'], $rs['s5']['y']),
    'Wrist: ' . number_format($wristW, 2) . '"', 0.8 * $s);

// Ease box (was horizontal s3→s31, now vertical)
if (isset($rs['s31'])) {
    $ann3 .= dimV($rs['s3']['x'], min($rs['s3']['y'], $rs['s31']['y']), max($rs['s3']['y'], $rs['s31']['y']),
        'Ease: 1.5"', -0.5 * $s, '#E67E22');
}

// Cap height (was vertical s1→s2, now horizontal)
$capHt = abs($slN['s2']['y'] - $slN['s1']['y']) / $s;
$botRefY = max($rs['s1']['y'], $rs['s2']['y'], $rs['s4']['y'], $rs['s5']['y']);
$ann3 .= dimH(min($rs['s1']['x'], $rs['s2']['x']), max($rs['s1']['x'], $rs['s2']['x']),
    $botRefY, 'Cap Ht: ' . number_format($capHt, 2) . '"', 1.0 * $s, '#E67E22');

// Sleeve length (was vertical s1→s4, now horizontal)
$slLenIn = abs($slN['s4']['y'] - $slN['s1']['y']) / $s;
$ann3 .= dimH(min($rs['s1']['x'], $rs['s4']['x']), max($rs['s1']['x'], $rs['s4']['x']),
    $botRefY, 'S.Length: ' . number_format($slLenIn, 2) . '"', 2.0 * $s);

// --- Armhole diagonals ---
$armDiagL = sqrt(pow($slN['s1']['x'] - $slN['s2']['x'], 2) + pow($slN['s1']['y'] - $slN['s2']['y'], 2)) / $s;
$ann3 .= dimDiag($rs['s2']['x'], $rs['s2']['y'], $rs['s1']['x'], $rs['s1']['y'],
    'Armhole: ' . number_format($armDiagL, 2) . '"', '#DC2626');

$armDiagR = sqrt(pow($slN['s3']['x'] - $slN['s1']['x'], 2) + pow($slN['s3']['y'] - $slN['s1']['y'], 2)) / $s;
$ann3 .= dimDiag($rs['s1']['x'], $rs['s1']['y'], $rs['s3']['x'], $rs['s3']['y'],
    'Armhole: ' . number_format($armDiagR, 2) . '"', '#DC2626');

// Prepare sleeve page SVG (wider margins for rotated layout)
list($sleeveClean, $sleeveVbW, $sleeveVbH) = prepareSvgForPdf(
    $rotSvg, $ann3, 2.0 * $s, 1.0 * $s, 2.0 * $s, 2.5 * $s
);

// =============================================================================
// PDF GENERATION — 3 Pages, A4 Size
// =============================================================================
$pdf = new TCPDF('P', 'in', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('CM-2025 Pattern Reading Guide');
$pdf->SetAuthor($customerName);
$pdf->SetTitle('Pattern Reading Guide - ' . $customerName);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0.3, 0.3, 0.3);
$pdf->SetAutoPageBreak(false, 0.3);

$pw = 8.27 - 0.6;   // 7.67" printable width (A4)
$ph = 11.69 - 0.6;  // 11.09" printable height (A4)

// --- Preprocess logo SVG for TCPDF (inline CSS classes) ---
$logoSvg = file_get_contents(__DIR__ . '/../images/cm-logo.svg');
$logoSvg = preg_replace('/<\?xml[^>]*\?>/', '', $logoSvg);
$logoSvg = preg_replace('/<style>.*?<\/style>/s', '', $logoSvg);
$logoSvg = str_replace('class="st0"', 'fill="#dd2a2a"', $logoSvg);
$logoSvg = str_replace('class="st1"', 'fill="none" stroke="#e83434" stroke-miterlimit="10"', $logoSvg);
$logoSvg = str_replace('class="st2"', 'fill="none" stroke="#e83434" stroke-miterlimit="10" stroke-width="0.3"', $logoSvg);

/**
 * Render one page: vertical title (left), header (customer, measurements, logo) + SVG content + footer
 */
function renderPage($pdf, $svgContent, $vbW, $vbH, $pw, $ph, $pageNum, $totalPages,
                    $customerName, $title, $measurements, $logoSvg) {
    $pdf->AddPage();
    $lm = 0.3;  // left margin
    $titleW = 0.6;  // width reserved for vertical title

    // --- Vertical Title (rotated elegant script text on left edge) ---
    $titleText = $title;  // Sentence case as passed in
    // Create tall narrow SVG with script font - viewBox sized for 140px font: 180 wide x 1000 tall
    $titleSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 1000">'
        . '<text x="90" y="500" font-size="140" font-family="Brush Script MT, cursive" font-style="italic" '
        . 'fill="#333333" text-anchor="middle" dominant-baseline="middle" '
        . 'transform="rotate(-90, 90, 500)">' . htmlspecialchars($titleText) . '</text>'
        . '</svg>';
    // Render: 0.6" wide x 5" tall, positioned with 0.5" left margin
    $pdf->ImageSVG('@' . $titleSvg, 0.5, 3.0, 0.6, 5.0, '', '', '', 0, true);

    // Adjust content area to account for vertical title
    $contentLm = $lm + $titleW;
    $contentPw = $pw - $titleW;

    // --- Header: customer + measurements (left), logo (right) ---
    // Line 1: Customer name
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(51, 51, 51);  // #333
    $pdf->SetXY($contentLm, $lm);
    $pdf->Cell($contentPw * 0.7, 0.2, $customerName, 0, 1, 'L');

    // Line 2-3: Measurements (semicolon-separated, auto-wrapping)
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(113, 128, 150);  // #718096
    $pdf->SetXY($contentLm, $lm + 0.24);
    $pdf->MultiCell($contentPw - 1.6, 0.14, $measurements, 0, 'L', false, 1);
    $measBottom = $pdf->GetY();

    // Logo (top right, ~1.5" wide)
    $logoW = 1.5;
    $logoH = $logoW * (156.9 / 974.8);  // aspect ratio from viewBox
    $logoX = $contentLm + $contentPw - $logoW;
    $logoY = $lm;
    $pdf->ImageSVG('@' . $logoSvg, $logoX, $logoY, $logoW, $logoH, '', '', '', 0, true);

    // --- SVG pattern content (below header) ---
    $headerH = max(0.9, $measBottom - $lm + 0.05);
    $contentTop = $lm + $headerH;
    $contentH = $ph - $headerH;

    $aspect = $vbW / $vbH;
    $rw = min($contentPw, $contentH * $aspect);
    $rh = $rw / $aspect;
    if ($rh > $contentH) { $rh = $contentH; $rw = $rh * $aspect; }

    $x = $contentLm + ($contentPw - $rw) / 2;
    $y = $contentTop + ($contentH - $rh) / 2;
    $pdf->ImageSVG('@' . $svgContent, $x, $y, $rw, $rh, '', '', '', 0, true);

    // --- Footer (with 0.3" bottom margin to avoid printer clipping) ---
    // Footer line (edge to edge, 80% black)
    $pdf->SetDrawColor(51, 51, 51);  // 80% black
    $pdf->SetLineWidth(0.01);
    $pdf->Line(0, 11.39, 8.27, 11.39);  // Full page width (A4 = 8.27")

    // File info on the left
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY(0.3, 11.44);
    $pdf->Cell(4, 0.2,
        'CM-2025 Pattern Reading Guide  |  ' . htmlspecialchars($customerName) . '  |  Page ' . $pageNum . ' of ' . $totalPages,
        0, 0, 'L');

    // Website address on the right
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(4.3, 11.44);
    $pdf->Cell(3.67, 0.2, 'www.cuttingmaster.in', 0, 0, 'R');
}

$allMeasurements = 'Bust: ' . number_format($bust, 1) . '";  Chest: ' . number_format($chest, 1) . '";  Waist: ' . number_format($waist, 1) . '";  Apex: ' . number_format($apex, 1) . '"' . "\n"
    . 'Armhole: ' . number_format($armhole, 1) . '";  Shoulder: ' . number_format($shoulder, 1) . '";  F.Shoulder: ' . number_format($fshoulder, 1) . '"' . "\n"
    . 'F.Length: ' . number_format($flength, 1) . '";  B.Length: ' . number_format($blength, 1) . '";  S.Length: ' . number_format($slength, 1) . '"' . "\n"
    . 'F.Neck: ' . number_format($fndepth, 1) . '";  B.Neck: ' . number_format($bnDepth, 1) . '";  S.Around: ' . number_format($saround, 1) . '";  S.Open: ' . number_format($sopen, 1) . '"';

renderPage($pdf, $frontClean, $frontVbW, $frontVbH, $pw, $ph, 1, 3, $customerName,
    'Front Pattern', $allMeasurements, $logoSvg);
renderPage($pdf, $backClean, $backVbW, $backVbH, $pw, $ph, 2, 3, $customerName,
    'Back Pattern', $allMeasurements, $logoSvg);
renderPage($pdf, $sleeveClean, $sleeveVbW, $sleeveVbH, $pw, $ph, 3, 3, $customerName,
    'Sleeve Pattern', $allMeasurements, $logoSvg);

// =============================================================================
// OUTPUT
// =============================================================================
$patternNameClean = preg_replace('/[^a-zA-Z0-9]/', '', $patternType);
$customerNameClean = preg_replace('/[^a-zA-Z0-9]/', '_', $customerName);
$filename = $patternNameClean . '_Guide_' . $customerNameClean . '.pdf';
if ($isCLI) {
    $outputPath = getenv('HOME') . '/Desktop/' . $filename;
    $pdf->Output($outputPath, 'F');
    echo "PDF saved to: $outputPath\n";
} else {
    $pdf->Output($filename, 'D');
}
