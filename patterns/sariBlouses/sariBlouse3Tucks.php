<?php
/**
 * =============================================================================
 * SAREE BLOUSE 3 TUCKS - Composite Pattern File
 * =============================================================================
 *
 * Combines all pattern pieces for a complete saree blouse with 3 tucks:
 * - Front panel with Patti (sariBlouse3TFront.php)
 * - Back panel (sariBlouseBack.php)
 * - Sleeve (sariSleeve.php)
 *
 * MODES:
 * - Standalone: Full HTML preview with 2x2 grid (default)
 * - PDF: Outputs session data for PDF generation
 *
 * USAGE:
 *   sariBlouse3Tucks.php?customer_id=123&measurement_id=456&mode=dev
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

include __DIR__ . '/sariBlouse3TFront.php';   // Sets $frontPatternData (includes patti)
include __DIR__ . '/sariBlouseBack.php';    // Sets $backPatternData
include __DIR__ . '/sariSleeve.php';        // Sets $sleevePatternData

// =============================================================================
// SECTION 3: BUILD COMPLETE PATTERN DATA
// =============================================================================
// Get contact number from measurements if available
$contactNumber = $measurements['contact_number'] ?? $measurements['mobile_number'] ?? '';

$patternData = [
    'metadata' => [
        'type' => 'sariBlouse',
        'name' => 'Saree Blouse',
        'customer_id' => $customerId,
        'customer_name' => $customerName,
        'contact_number' => $contactNumber,
        'measurement_id' => $measurementId,
        'generated_at' => time(),
        'mode' => $mode
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Blouse Pattern - <?php echo htmlspecialchars($customerName); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
        }
        .header {
            max-width: 1800px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .header-info {
            color: #666;
            font-size: 14px;
        }
        .mode-switch {
            display: flex;
            gap: 10px;
        }
        .mode-switch a {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .mode-switch a.active { background: #0056b3; }
        .mode-switch a:hover { background: #0069d9; }

        .pattern-grid {
            max-width: 1800px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .pattern-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .pattern-card-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pattern-card-header h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .pattern-card-header .dimensions {
            color: #666;
            font-size: 13px;
        }
        .pattern-card-body {
            padding: 20px;
            overflow: auto;
            max-height: 1200px;
            background: #fff;
        }
        .pattern-card-body svg {
            display: block;
            width: 100%;
            height: auto;
            min-height: 600px;
        }

        .measurements-panel {
            max-width: 1800px;
            margin: 20px auto 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .measurements-panel h3 {
            margin: 0 0 15px;
            color: #333;
            font-size: 16px;
        }
        .measurements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .measurement-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .measurement-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        .measurement-item span {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        @media (max-width: 1200px) {
            .pattern-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body { background: white; padding: 0; }
            .header, .mode-switch { display: none; }
            .pattern-grid { gap: 10px; }
            .pattern-card { box-shadow: none; border: 1px solid #ddd; }
            .pattern-card-body { max-height: none; }
        }
    </style>
</head>
<body>
    <?php
    // Show header only in dev mode
    if ($isDevMode):
    ?>
    <div class="header">
        <div>
            <h1>Saree Blouse Pattern</h1>
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

    <?php if ($isDevMode): ?>
    <div class="measurements-panel">
        <h3>Measurements</h3>
        <div class="measurements-grid">
            <div class="measurement-item">
                <label>Bust</label>
                <span><?php echo $bust; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Chest</label>
                <span><?php echo $chest; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Waist</label>
                <span><?php echo $waist; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Armhole</label>
                <span><?php echo $armhole; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Shoulder</label>
                <span><?php echo $shoulder; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Back Length</label>
                <span><?php echo $blength; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Front Length</label>
                <span><?php echo $flength; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Sleeve Length</label>
                <span><?php echo $slength; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Sleeve Round</label>
                <span><?php echo $saround; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Sleeve Opening</label>
                <span><?php echo $sopen; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Apex</label>
                <span><?php echo $apex; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Back Neck Depth</label>
                <span><?php echo $bnDepth; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>Front Neck Depth</label>
                <span><?php echo $frontNeckDepth; ?>"</span>
            </div>
            <div class="measurement-item">
                <label>A-B Height (adjusted)</label>
                <span><?php echo isset($armHoleHeight) ? number_format($armHoleHeight, 2) : 'N/A'; ?>"</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
