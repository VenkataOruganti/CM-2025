<?php
/**
 * =============================================================================
 * PATTERN CONFIGURATION - Common setup for all blouse patterns
 * =============================================================================
 *
 * This file handles:
 * - Database connection
 * - Measurement loading via deepNeck.php
 * - SVG setup (scale, margins, origin)
 * - Common helper functions
 *
 * USAGE:
 *   require_once 'patternConfig.php';
 *   // All measurements and config variables are now available
 *
 * COMPOSITE MODE:
 *   When used in composite files (like sariBlouseComplete.php), set:
 *   define('PATTERN_CONFIG_LOADED', true);
 *   before including this file to prevent double-loading.
 *
 * =============================================================================
 */

// Mark config as loaded (for composite mode detection)
if (!defined('PATTERN_CONFIG_LOADED')) {
    define('PATTERN_CONFIG_LOADED', true);
}

// =============================================================================
// DATABASE CONNECTION
// =============================================================================
if (!isset($pdo)) {
    // Path: /patterns/saree_blouses/ -> /config/database.php (2 levels up)
    $dbPath = __DIR__ . '/../../config/database.php';
    if (!file_exists($dbPath)) {
        $dbPath = $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
    }
    require_once $dbPath;
}

// =============================================================================
// MEASUREMENTS LOADER (deepNeck.php)
// =============================================================================
if (!function_exists('getMeasurements')) {
    // Path: /patterns/saree_blouses/ -> /includes/deepNeck.php (2 levels up)
    $deepNeckPath = __DIR__ . '/../../includes/deepNeck.php';
    if (!file_exists($deepNeckPath)) {
        $deepNeckPath = $_SERVER['DOCUMENT_ROOT'] . '/includes/deepNeck.php';
    }
    require_once $deepNeckPath;
}

// =============================================================================
// SESSION & PARAMETERS
// =============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get parameters from URL
$customerId = $_GET['customer_id'] ?? null;
$measurementId = $_GET['measurement_id'] ?? null;
$mode = $_GET['mode'] ?? 'dev';

// Set mode flags
$isDevMode = ($mode === 'dev');
$isPrintMode = ($mode === 'print');

// =============================================================================
// LOAD MEASUREMENTS
// =============================================================================
// Check if measurements are loaded in session by deepNeck.php
$measurements = null;

// PRIORITY 1: Check for preview_measurements in URL (from Pattern Editor)
if (isset($_GET['preview_measurements'])) {
    $previewMeasurements = json_decode($_GET['preview_measurements'], true);
    if ($previewMeasurements && !empty($previewMeasurements)) {
        $measurements = [
            'customer_name' => 'Preview',
            'customer_id' => $customerId,
            'measurement_id' => $measurementId,
            'bust' => floatval($previewMeasurements['bust'] ?? 0),
            'chest' => floatval($previewMeasurements['chest'] ?? 0),
            'waist' => floatval($previewMeasurements['waist'] ?? 0),
            'bnDepth' => floatval($previewMeasurements['bnDepth'] ?? 0),
            'armhole' => floatval($previewMeasurements['armhole'] ?? 0),
            'shoulder' => floatval($previewMeasurements['shoulder'] ?? 0),
            'fndepth' => floatval($previewMeasurements['fndepth'] ?? 0),
            'fshoulder' => floatval($previewMeasurements['fshoulder'] ?? 0),
            'blength' => floatval($previewMeasurements['blength'] ?? 0),
            'flength' => floatval($previewMeasurements['flength'] ?? 0),
            'slength' => floatval($previewMeasurements['slength'] ?? 0),
            'apex' => floatval($previewMeasurements['apex'] ?? 0),
            'saround' => floatval($previewMeasurements['saround'] ?? 0),
            'sopen' => floatval($previewMeasurements['sopen'] ?? 0),
            'scale' => 25.4
        ];
    }
}

// Second try: Session measurements
if (!$measurements && isset($_SESSION['measurements']) && !empty($_SESSION['measurements'])) {
    $measurements = $_SESSION['measurements'];
}

// Third try: Load from session helper
if (!$measurements) {
    $measurements = loadMeasurementsFromSession();
}

// Third try: Load from database using customer_id/measurement_id
if (!$measurements && ($customerId || $measurementId)) {
    global $pdo;
    try {
        $query = "
            SELECT m.*,
                   COALESCE(c.customer_name, m.customer_name, 'Customer') as customer_name
            FROM measurements m
            LEFT JOIN customers c ON m.customer_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if ($measurementId) {
            $query .= " AND m.id = ?";
            $params[] = $measurementId;
        } elseif ($customerId) {
            $query .= " AND m.customer_id = ?";
            $params[] = $customerId;
            $query .= " ORDER BY m.created_at DESC LIMIT 1";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Build measurements array from database row
            // Column names match database: bnDepth, fndepth, blength, fshoulder, etc.
            $measurements = [
                'customer_name' => $row['customer_name'],
                'customer_id' => $row['customer_id'],
                'measurement_id' => $row['id'],
                'bust' => floatval($row['bust'] ?? 0),
                'chest' => floatval($row['chest'] ?? 0),
                'waist' => floatval($row['waist'] ?? 0),
                'bnDepth' => floatval($row['bnDepth'] ?? 0),
                'armhole' => floatval($row['armhole'] ?? 0),
                'shoulder' => floatval($row['shoulder'] ?? 0),
                'fndepth' => floatval($row['fndepth'] ?? 0),
                'fshoulder' => floatval($row['fshoulder'] ?? 0),
                'blength' => floatval($row['blength'] ?? 0),
                'flength' => floatval($row['flength'] ?? 0),
                'slength' => floatval($row['slength'] ?? 0),
                'apex' => floatval($row['apex'] ?? 0),
                'saround' => floatval($row['saround'] ?? 0),
                'sopen' => floatval($row['sopen'] ?? 0),
                'scale' => 25.4
            ];

            // Also set IDs from database if not already set
            if (!$customerId) $customerId = $row['customer_id'];
            if (!$measurementId) $measurementId = $row['id'];

            // Store in session for subsequent requests
            $_SESSION['measurements'] = $measurements;
        }
    } catch (Exception $e) {
        error_log("patternConfig.php - Database error: " . $e->getMessage());
    }
}

// Final check - error if still no measurements
if (!$measurements) {
    $errorMsg = "Error: Measurements not loaded.";
    if (!$customerId && !$measurementId) {
        $errorMsg .= " No customer_id or measurement_id provided.";
    } elseif ($customerId) {
        $errorMsg .= " No measurements found for customer_id: " . htmlspecialchars($customerId) . ". Please ensure the customer has measurements saved.";
    } elseif ($measurementId) {
        $errorMsg .= " No measurements found for measurement_id: " . htmlspecialchars($measurementId) . ".";
    }
    die($errorMsg);
}

$customerName = $measurements['customer_name'] ?? 'Unknown';

// =============================================================================
// EXTRACT MEASUREMENTS
// =============================================================================
// Extract all measurements from array to individual variables
// These are used by pattern calculation files (sariBlouseFront.php, etc.)

$bust     = $measurements['bust'];
$chest    = $measurements['chest'];
$waist    = $measurements['waist'];
$bnDepth  = $measurements['bnDepth'];
$armhole  = $measurements['armhole'];
$shoulder = $measurements['shoulder'];
$fndepth  = $measurements['fndepth'];        // Original name from DB
$frontNeckDepth = $measurements['fndepth'];  // Alias used by pattern files
$fshoulder = $measurements['fshoulder'];
$blength  = $measurements['blength'];
$flength  = $measurements['flength'];
$slength  = $measurements['slength'];
$apex     = $measurements['apex'];
$saround  = $measurements['saround'];
$sopen    = $measurements['sopen'];
$scale    = $measurements['scale'] ?? 25.4;

// =============================================================================
// SVG SETUP - Margins & Origin
// =============================================================================
$marginLeft = 0.5 * $scale;  // 0.5" left margin
$marginTop  = 1.0 * $scale;  // 1.0" top margin

// Origin point (where pattern starts)
$originX = $marginLeft;
$originY = $marginTop;

// =============================================================================
// COMMON CALCULATED VALUES
// =============================================================================
// $qWaist and $qBust are now defined in deepNeck.php (single source of truth)
// $qWaist = ($waist / 4) + 0.5;  // Quarter waist with 0.5" ease
// $qBust = ($bust / 4) + 0.5;    // Quarter bust with 0.5" ease
// bottomTuckWidth = qBust - qWaist = (bust - waist) / 4
$bottomTuckWidth = $qBust - $qWaist;

// =============================================================================
// COMMON HELPER FUNCTIONS
// =============================================================================

/**
 * Generate 2x2" scale verification box SVG
 * @param float $x X position
 * @param float $y Y position
 * @param float $scale Pixels per inch
 * @return string SVG markup for scale box
 */
function generateScaleBox($x, $y, $scale) {
    $boxSize = 2 * $scale; // 2 inches

    $svg = '';
    // Outer box
    $svg .= sprintf('<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" fill="none" stroke="#333" stroke-width="1"/>',
        $x, $y, $boxSize, $boxSize);
    // Cross lines
    $midX = $x + ($boxSize / 2);
    $midY = $y + ($boxSize / 2);
    $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333" stroke-width="0.5"/>',
        $x, $midY, $x + $boxSize, $midY);
    $svg .= sprintf('<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="#333" stroke-width="0.5"/>',
        $midX, $y, $midX, $y + $boxSize);
    // Label
    $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="10" text-anchor="middle" fill="#333">2" × 2"</text>',
        $midX, $y + $boxSize + 12);

    return $svg;
}

/**
 * Generate scissors icon SVG at specified position
 * @param float $x X position
 * @param float $y Y position
 * @param float $rotation Rotation angle in degrees
 * @return string SVG markup
 */
function generateScissorsIcon($x, $y, $rotation = 0) {
    return sprintf(
        '<g transform="translate(%.2f, %.2f) rotate(%.1f)">
            <image href="../../../images/scissors-icon.svg" width="24" height="24" x="-12" y="-12"/>
        </g>',
        $x, $y, $rotation
    );
}

/**
 * Generate snip/notch marker icon on pattern
 * @param int $refNumber Reference number
 * @param string $label Label text
 * @param callable $nodeGetter Function to get node coordinates (e.g., 'frontNode')
 * @param string $nodeName Node name to position at
 * @param int $angle Angle: 0, 90, 180, or 270
 * @param float $size Size in inches (default 0.225")
 * @param float $offsetX X offset in inches
 * @param float $offsetY Y offset in inches
 * @return string SVG markup
 */
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
            $svg .= sprintf('<text class="snip-label" x="%.2f" y="%.2f" text-anchor="middle" dominant-baseline="middle" font-size="8px" fill="black">%s</text>', $labelX, $labelY, htmlspecialchars($nodeName));
        }
        $svg .= '</g>';
        return $svg;
    }
}

/**
 * Generate scissors icon SVG using inline path
 * @param float $x X position
 * @param float $y Y position
 * @param float $rotation Rotation angle
 * @param float $size Size in inches (default 0.5")
 * @param string $color Fill color
 * @param string $label Optional label text
 * @return string SVG markup
 */
if (!function_exists('scissorsIcon')) {
    function scissorsIcon($x, $y, $rotation = 0, $size = 0.5, $color = '#333333', $label = '') {
        global $scale, $isDevMode;
        $sizeScaled = $size * $scale;
        $scissorsPath = 'M9.64 7.64c.23-.5.36-1.05.36-1.64 0-2.21-1.79-4-4-4S2 3.79 2 6s1.79 4 4 4c.59 0 1.14-.13 1.64-.36L10 12l-2.36 2.36C7.14 14.13 6.59 14 6 14c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4c0-.59-.13-1.14-.36-1.64L12 14l7 7h3v-1L9.64 7.64zM6 8c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm0 12c-1.1 0-2-.89-2-2s.9-2 2-2 2 .89 2 2-.9 2-2 2zm6-7.5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5.5.22.5.5-.22.5-.5.5zM19 3l-6 6 2 2 7-7V3h-3z';
        $pathScale = $sizeScaled / 24;
        $svg = sprintf('<g transform="translate(%.2f, %.2f) rotate(%d) scale(%.4f) translate(-12, -12)">', $x, $y, $rotation, $pathScale);
        $svg .= sprintf('<path d="%s" fill="%s"/>', $scissorsPath, $color);
        $svg .= '</g>';
        if (!empty($label) && $isDevMode) {
            $labelOffsetY = $sizeScaled + 3;
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="8px" fill="#000000" text-anchor="middle" font-family="Arial, sans-serif">%s</text>', $x, $y + $labelOffsetY, htmlspecialchars($label));
        }
        return $svg;
    }
}

/**
 * Generate grainline arrow with label
 * @param float $x Center X position
 * @param float $y Center Y position
 * @param float $length Length in inches
 * @param string $orientation 'vertical' or 'horizontal'
 * @return string SVG markup
 */
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
