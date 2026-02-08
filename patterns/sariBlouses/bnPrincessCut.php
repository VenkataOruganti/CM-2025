<?php
/**
 * =============================================================================
 * BOAT NECK PRINCESS CUT BLOUSE - Composite Pattern File
 * =============================================================================
 *
 * Combines all pattern pieces for a complete boat neck princess cut blouse:
 * - Front panel with Patti (bnPrincessCutFront.php)
 * - Back panel (sariBlouseBack.php)
 * - Sleeve (sariSleeve.php)
 *
 * MODES:
 * - Standalone: Full HTML preview with 2x2 grid (default)
 * - PDF: Outputs session data for PDF generation
 *
 * USAGE:
 *   bnPrincessCut.php?customer_id=123&measurement_id=456&mode=dev
 *
 * =============================================================================
 */

// =============================================================================
// SECTION 1: ENABLE COMPOSITE MODE
// =============================================================================
define('COMPOSITE_MODE', true);
define('PATTERN_CONFIG_LOADED', true);

// Load shared configuration (measurements, session, etc.)
require_once __DIR__ . '/patternConfig.php';

// =============================================================================
// SECTION 2: INCLUDE ALL PATTERN FILES
// =============================================================================
// Each file will return after setting its pattern data variable

include __DIR__ . '/bnPrincessCutFront.php';   // Sets $frontPatternData (includes patti)
include __DIR__ . '/sariBlouseBack.php';    // Sets $backPatternData
include __DIR__ . '/sariSleeve.php';        // Sets $sleevePatternData

// =============================================================================
// SECTION 3: BUILD COMPLETE PATTERN DATA
// =============================================================================
// Get contact number from measurements if available
$contactNumber = $measurements['contact_number'] ?? $measurements['mobile_number'] ?? '';

// Derive mode string from flags set by patternConfig.php
$modeString = $isDevMode ? 'dev' : ($isPrintMode ? 'print' : 'default');

$patternData = [
    'metadata' => [
        'type' => 'bnPrincessCut',
        'name' => 'Boat Neck Princess Cut Blouse',
        'customer_id' => $customerId,
        'customer_name' => $customerName,
        'contact_number' => $contactNumber,
        'measurement_id' => $measurementId,
        'generated_at' => time(),
        'mode' => $modeString
    ],
    'measurements' => [
        'bust' => $bust,
        'chest' => $chest,
        'waist' => $waist,
        'bnDepth' => $bnDepth,
        'armhole' => $armhole,
        'shoulder' => $shoulder,
        'frontNeckDepth' => $frontNeckDepth,
        'fshoulder' => $fshoulder,
        'blength' => $blength,
        'flength' => $flength,
        'slength' => $slength,
        'apex' => $apex,
        'saround' => $saround,
        'sopen' => $sopen,
        'scale' => $scale
    ],
    'patterns' => [
        'front' => $frontPatternData,
        'back' => $backPatternData,
        'sleeve' => $sleevePatternData
    ]
];

// Store in session for PDF generation (multiple formats for compatibility)
$_SESSION['pattern_data'] = $patternData;                    // New standardized format
$_SESSION['patternData'] = $patternData;                     // Legacy format
$cacheKey = "pattern_" . $measurementId;
$_SESSION[$cacheKey] = ['data' => $patternData, 'timestamp' => time()];  // sariBlouse format
$_SESSION['latest_pattern'] = $cacheKey;

// =============================================================================
// SECTION 4: RENDER PREVIEW (2x2 Grid)
// =============================================================================

// Measurement labels for the dev panel
$measurementLabels = [
    'Bust' => $bust,
    'Chest' => $chest,
    'Waist' => $waist,
    'Armhole' => $armhole,
    'Shoulder' => $shoulder,
    'Back Length' => $blength,
    'Front Length' => $flength,
    'Sleeve Length' => $slength,
    'Sleeve Round' => $saround,
    'Sleeve Opening' => $sopen,
    'Apex' => $apex,
    'Back Neck Depth' => $bnDepth,
    'Front Neck Depth' => $frontNeckDepth,
    'A-B Height (adjusted)' => isset($armHoleHeight) ? number_format($armHoleHeight, 2) : 'N/A',
];

// Detect if we're being embedded inside another page (e.g., pattern-preview.php)
// If headers are already sent, a parent page has started HTML output
$isEmbedded = headers_sent();

if ($isEmbedded):
// =============================================================================
// EMBEDDED MODE: Output only pattern grid with scoped styles (no HTML document)
// =============================================================================
?>
<style>
    .pc-pattern-grid {
        max-width: 1800px;
        width: 100%;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .pc-pattern-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .pc-pattern-card-header {
        padding: 8px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    .pc-pattern-card-header h2 {
        margin: 0;
        font-size: 16px;
        color: #333;
    }
    .pc-pattern-card-header .dimensions {
        color: #666;
        font-size: 12px;
    }
    .pc-pattern-card-body {
        padding: 10px;
        background: #fff;
        flex: 1;
        min-height: 0;
    }
    .pc-pattern-card-body svg {
        display: block;
        width: 100%;
        height: auto;
    }
    @media (max-width: 1200px) {
        .pc-pattern-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pc-pattern-grid">
    <!-- FRONT -->
    <div class="pc-pattern-card">
        <div class="pc-pattern-card-header">
            <h2>Front</h2>
            <span class="dimensions"><?php echo number_format($frontPatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($frontPatternData['dimensions']['heightInches'], 1); ?>"</span>
        </div>
        <div class="pc-pattern-card-body">
            <?php echo $frontPatternData['svg_content']; ?>
        </div>
    </div>

    <!-- BACK -->
    <div class="pc-pattern-card">
        <div class="pc-pattern-card-header">
            <h2>Back</h2>
            <span class="dimensions"><?php echo number_format($backPatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($backPatternData['dimensions']['heightInches'], 1); ?>"</span>
        </div>
        <div class="pc-pattern-card-body">
            <?php echo $backPatternData['svg_content']; ?>
        </div>
    </div>

    <!-- SLEEVE -->
    <div class="pc-pattern-card">
        <div class="pc-pattern-card-header">
            <h2>Sleeve</h2>
            <span class="dimensions"><?php echo number_format($sleevePatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($sleevePatternData['dimensions']['heightInches'], 1); ?>"</span>
        </div>
        <div class="pc-pattern-card-body">
            <?php echo $sleevePatternData['svg_content']; ?>
        </div>
    </div>
</div>
<?php else:
// =============================================================================
// STANDALONE MODE: Full HTML document for direct access
// =============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Boat Neck Princess Cut Blouse Pattern - <?php echo htmlspecialchars($customerName); ?></title>
    <style>
        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            overflow: auto;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 10px;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
        }
        .header {
            max-width: 1800px;
            width: 100%;
            margin: 0 auto 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 20px;
        }
        .header-info {
            color: #666;
            font-size: 13px;
        }
        .mode-switch {
            display: flex;
            gap: 8px;
        }
        .mode-switch a {
            padding: 6px 14px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        .mode-switch a.active { background: #0056b3; }
        .mode-switch a:hover { background: #0069d9; }

        .pattern-grid {
            max-width: 1800px;
            width: 100%;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .pattern-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .pattern-card-header {
            padding: 8px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .pattern-card-header h2 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .pattern-card-header .dimensions {
            color: #666;
            font-size: 12px;
        }
        .pattern-card-body {
            padding: 10px;
            background: #fff;
            flex: 1;
            min-height: 0;
        }
        .pattern-card-body svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .measurements-panel {
            max-width: 1800px;
            width: 100%;
            margin: 10px auto 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 15px;
            flex-shrink: 0;
        }
        .measurements-panel h3 {
            margin: 0 0 8px;
            color: #333;
            font-size: 14px;
        }
        .measurements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 6px;
        }
        .measurement-item {
            padding: 6px 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .measurement-item label {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 2px;
        }
        .measurement-item span {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        @media (max-width: 1200px) {
            .pattern-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            html, body { height: auto; overflow: visible; }
            body { background: white; padding: 0; }
            .header, .mode-switch { display: none; }
            .pattern-grid { gap: 10px; }
            .pattern-card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
    <?php
    // Show header on localhost (development) - support various local ports
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocalhost = (strpos($host, 'localhost') !== false) || (strpos($host, '127.0.0.1') !== false);
    if ($isLocalhost):
    ?>
    <div class="header">
        <div>
            <h1>Boat Neck Princess Cut Blouse Pattern</h1>
            <div class="header-info">
                Customer: <?php echo htmlspecialchars($customerName); ?> |
                Bust: <?php echo $bust; ?>" |
                Waist: <?php echo $waist; ?>"
            </div>
        </div>

        <div class="mode-switch">
            <a href="?customer_id=<?php echo $customerId; ?>&measurement_id=<?php echo $measurementId; ?>&mode=dev" <?php echo $isDevMode ? 'class="active"' : ''; ?>>Dev Mode</a>
            <a href="?customer_id=<?php echo $customerId; ?>&measurement_id=<?php echo $measurementId; ?>&mode=print" <?php echo $isPrintMode ? 'class="active"' : ''; ?>>Print Mode</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="pattern-grid">
        <!-- FRONT -->
        <div class="pattern-card">
            <div class="pattern-card-header">
                <h2>Front</h2>
                <span class="dimensions"><?php echo number_format($frontPatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($frontPatternData['dimensions']['heightInches'], 1); ?>"</span>
            </div>
            <div class="pattern-card-body">
                <?php echo $frontPatternData['svg_content']; ?>
            </div>
        </div>

        <!-- BACK -->
        <div class="pattern-card">
            <div class="pattern-card-header">
                <h2>Back</h2>
                <span class="dimensions"><?php echo number_format($backPatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($backPatternData['dimensions']['heightInches'], 1); ?>"</span>
            </div>
            <div class="pattern-card-body">
                <?php echo $backPatternData['svg_content']; ?>
            </div>
        </div>

        <!-- SLEEVE -->
        <div class="pattern-card">
            <div class="pattern-card-header">
                <h2>Sleeve</h2>
                <span class="dimensions"><?php echo number_format($sleevePatternData['dimensions']['widthInches'], 1); ?>" x <?php echo number_format($sleevePatternData['dimensions']['heightInches'], 1); ?>"</span>
            </div>
            <div class="pattern-card-body">
                <?php echo $sleevePatternData['svg_content']; ?>
            </div>
        </div>
    </div>

    <?php if ($isLocalhost && $isDevMode): ?>
    <div class="measurements-panel">
        <h3>Measurements</h3>
        <div class="measurements-grid">
            <?php foreach ($measurementLabels as $label => $value): ?>
            <div class="measurement-item">
                <label><?php echo $label; ?></label>
                <span><?php echo $value; ?>"</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
<?php endif; ?>
