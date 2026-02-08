<?php
/**
 * =============================================================================
 * GENERIC PATTERN PDF GENERATOR
 * =============================================================================
 *
 * Universal PDF generator for all pattern types in CM-2025.
 * Based on sariBlouse_PDF.php specifications with full feature support.
 *
 * Features:
 * - Multiple paper sizes (A4, A3, A2, Letter, Legal, Tabloid)
 * - Landscape orientation for pattern layouts
 * - Automatic tiling for patterns larger than page size
 * - Scale verification box (2" x 2")
 * - Assembly markers for multi-page patterns
 * - Summary sheet with thumbnails and assembly guide
 * - CM logo and branding
 * - Watermark support
 *
 * Usage:
 * 1. Pattern file stores data in session with standardized format
 * 2. Access: pdfGenerator.php?measurement_id=123&paper=A3&type=sariBlouse
 *
 * Session Data Format Expected:
 * $_SESSION['pattern_data'] = [
 *     'metadata' => [
 *         'type' => 'sariBlouse',           // Pattern type identifier
 *         'name' => 'Saree Blouse',         // Display name
 *         'customer_id' => 123,
 *         'customer_name' => 'Customer Name',
 *         'contact_number' => '9876543210',
 *         'measurement_id' => 456,
 *     ],
 *     'measurements' => [
 *         'bust' => 36, 'chest' => 34, 'waist' => 32, ...
 *     ],
 *     'patterns' => [
 *         'front' => ['svg_content' => '...', 'name' => 'Front', 'order' => 1],
 *         'back' => ['svg_content' => '...', 'name' => 'Back', 'order' => 2],
 *         // ... more patterns as needed
 *     ]
 * ];
 *
 * Parameters:
 * - measurement_id: Measurement ID (required)
 * - paper: Paper size (A4, A3, A2, Letter, Legal, Tabloid - default: A3)
 * - type: Pattern type for filename (optional, uses metadata.type)
 *
 * @author CM-2025
 * @date January 2026
 */

// Suppress PHP 8.5 deprecation warnings for TCPDF compatibility
error_reporting(E_ALL & ~E_DEPRECATED);

// Custom error handler to show errors instead of white screen
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Start output buffering to prevent any output before PDF generation
ob_start();

// Start session to access pattern data
session_start();

// Load TCPDF library
require_once(__DIR__ . '/../vendor/autoload.php');

// =============================================================================
// CONFIGURATION
// =============================================================================

// Paper sizes (in inches) - PORTRAIT orientation (default)
// Landscape dimensions used only for tiling
$paperSizes = [
    'A2' => ['portrait' => ['width' => 16.54, 'height' => 23.39], 'landscape' => ['width' => 23.39, 'height' => 16.54]],
    'A3' => ['portrait' => ['width' => 11.69, 'height' => 16.54], 'landscape' => ['width' => 16.54, 'height' => 11.69]],
    'A4' => ['portrait' => ['width' => 8.27, 'height' => 11.69], 'landscape' => ['width' => 11.69, 'height' => 8.27]],
    'LETTER' => ['portrait' => ['width' => 8.5, 'height' => 11.0], 'landscape' => ['width' => 11.0, 'height' => 8.5]],
    'US_LETTER' => ['portrait' => ['width' => 8.5, 'height' => 11.0], 'landscape' => ['width' => 11.0, 'height' => 8.5]],
    'USLETTER' => ['portrait' => ['width' => 8.5, 'height' => 11.0], 'landscape' => ['width' => 11.0, 'height' => 8.5]],
    'LEGAL' => ['portrait' => ['width' => 8.5, 'height' => 14.0], 'landscape' => ['width' => 14.0, 'height' => 8.5]],
    'TABLOID' => ['portrait' => ['width' => 11.0, 'height' => 17.0], 'landscape' => ['width' => 17.0, 'height' => 11.0]]
];

// Get paper size from URL parameter, session, or default to A3
if (isset($_GET['paper'])) {
    $paperSize = strtoupper($_GET['paper']);
} elseif (isset($_SESSION['paper_size'])) {
    $paperSize = strtoupper($_SESSION['paper_size']);
} else {
    $paperSize = 'A3'; // Default
}

// Validate paper size
if (!isset($paperSizes[$paperSize])) {
    $paperSize = 'A3'; // Fallback to A3 if invalid
}

// Start with portrait orientation
$paper = $paperSizes[$paperSize]['portrait'];
$paperOrientation = 'P';

// Margins and layout
$margin = 0.5; // 0.5 inch margins on all sides
$scale = 25.4; // Pixels per inch

// =============================================================================
// GET PATTERN DATA FROM SESSION
// =============================================================================

$patternData = null;
// Accept both 'id' and 'measurement_id' parameters for compatibility
$measurementId = isset($_GET['measurement_id']) ? intval($_GET['measurement_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

// Try to get pattern from session - support multiple key formats
$sessionKeys = [
    'pattern_data',                              // New standardized format
    'patternData',                               // blousePatterns format
    'pattern_' . $measurementId,                 // sariBlouse format with ID
];

// Also check for latest_pattern reference
if (isset($_SESSION['latest_pattern'])) {
    $sessionKeys[] = $_SESSION['latest_pattern'];
}

foreach ($sessionKeys as $key) {
    if (isset($_SESSION[$key])) {
        $sessionData = $_SESSION[$key];
        // Handle both direct data and wrapped data formats
        if (isset($sessionData['data'])) {
            $patternData = $sessionData['data'];
        } else {
            $patternData = $sessionData;
        }
        break;
    }
}

// Error if no pattern data found
if ($patternData === null) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Pattern Data Not Found</title>";
    echo "<style>body{font-family:Arial,sans-serif;padding:40px;background:#f5f5f5}";
    echo ".error-box{background:white;border-radius:8px;padding:30px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
    echo "h1{color:#dc2626;margin:0 0 20px}ul{margin:10px 0;padding-left:20px}";
    echo ".btn{display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;margin:10px 5px 0 0}</style></head><body>";
    echo "<div class='error-box'>";
    echo "<h1>Pattern Data Not Found</h1>";
    echo "<p>No pattern data was found in your session. This usually means you haven't viewed the pattern yet.</p>";
    echo "<p><strong>Session keys checked:</strong></p><ul>";
    foreach ($sessionKeys as $key) {
        echo "<li>" . htmlspecialchars($key) . " - " . (isset($_SESSION[$key]) ? "exists" : "not found") . "</li>";
    }
    echo "</ul>";
    echo "<p><strong>To fix this:</strong></p><ol>";
    echo "<li>Go back to your dashboard</li>";
    echo "<li>Click on the pattern to view it first</li>";
    echo "<li>Then click the Download PDF button from the pattern preview page</li>";
    echo "</ol>";
    echo "<a href='../pages/dashboard.php' class='btn'>Go to Dashboard</a>";
    echo "<a href='javascript:history.back()' class='btn' style='background:#6b7280'>← Go Back</a>";
    echo "</div></body></html>";
    exit;
}

// =============================================================================
// NORMALIZE PATTERN DATA STRUCTURE
// =============================================================================

// Handle different data structures from various pattern generators
$metadata = $patternData['metadata'] ?? [];
$measurements = $patternData['measurements'] ?? [];

// Patterns can be at root level or under 'patterns' key
$patterns = [];
if (isset($patternData['patterns'])) {
    $patterns = $patternData['patterns'];
} else {
    // Check for patterns at root level (sariBlouse format)
    $patternKeys = ['front', 'back', 'patti', 'sleeve', 'side_panel', 'center_panel'];
    foreach ($patternKeys as $key) {
        if (isset($patternData[$key]) && isset($patternData[$key]['svg_content'])) {
            $patterns[$key] = $patternData[$key];
        }
    }
}

// Extract metadata
$customerName = $metadata['customer_name'] ?? 'Customer';
$customerId = $metadata['customer_id'] ?? 'unknown';
$measurementId = $metadata['measurement_id'] ?? $measurementId ?? 'unknown';
$patternType = $metadata['type'] ?? $_GET['type'] ?? 'pattern';
$patternDisplayName = $metadata['name'] ?? ucfirst(str_replace('_', ' ', $patternType));
$contactNumber = $metadata['contact_number'] ?? $metadata['mobile_number'] ?? '';
// Format: CustomerName-PatternID-Mon_DD-H_MMAM.pdf (e.g., Siva-97-Jan_19-7_52PM.pdf)
$dateForFilename = date('M_d');  // e.g., Jan_19
$timeForFilename = date('g_iA'); // e.g., 7_52PM (12-hour format with AM/PM)

// Sanitize customer name for filename (replace spaces with underscores, remove special chars)
$safeCustomerName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customerName);
$safeCustomerName = preg_replace('/_+/', '_', $safeCustomerName); // Remove multiple underscores
$safeCustomerName = trim($safeCustomerName, '_'); // Remove leading/trailing underscores
$pdfFilename = sprintf('%s-%s-%s-%s.pdf', $safeCustomerName, $measurementId, $dateForFilename, $timeForFilename);

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Add CM logo to PDF page with website URL below it
 */
function addCMLogo($pdf, $pageWidth, $pageHeight) {
    $logoPath = __DIR__ . '/../images/cm-logo.svg';
    if (file_exists($logoPath)) {
        $logoWidth = 2.7;  // Reduced by 20% (3.375 * 0.8 = 2.7)
        $logoHeight = 2.7; // Reduced by 20% (3.375 * 0.8 = 2.7)
        $logoX = $pageWidth - $logoWidth - 0.5;
        $logoY = -0.5;

        $pdf->ImageSVG($logoPath, $logoX, $logoY, $logoWidth, $logoHeight, '', '', '', 0, false);

        // Add website URL below the logo
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 102, 153);
        $urlText = 'www.CuttingMaster.in';
        $urlWidth = $pdf->GetStringWidth($urlText);
        $urlX = $logoX + $logoWidth - $urlWidth - 0.2;
        $urlY = $logoY + $logoHeight - 1.08; // Adjusted for smaller logo (1.35 * 0.8)
        $pdf->Text($urlX, $urlY, $urlText);

        $pdf->SetTextColor(0, 0, 0);
    }
}

/**
 * Add watermark text in center of page
 */
function addWatermark($pdf, $pageWidth, $pageHeight, $patternName, $customerName) {
    $pdf->SetAlpha(0.1);
    $pdf->SetFont('helvetica', 'B', 60);
    $pdf->SetTextColor(128, 128, 128);

    $centerX = $pageWidth / 2;
    $centerY = $pageHeight / 2;

    $pdf->StartTransform();
    $pdf->Rotate(45, $centerX, $centerY);
    $pdf->Text($centerX - 3, $centerY - 0.5, $patternName);
    $pdf->Text($centerX - 2, $centerY + 0.5, $customerName);
    $pdf->StopTransform();

    $pdf->SetAlpha(1);
    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Draw scale verification box (2" × 2") with ruler markings
 */
function drawScaleBox($pdf, $x, $y) {
    $boxSize = 2.0;

    // Draw main box border
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(1.5 / 72);
    $pdf->Rect($x, $y, $boxSize, $boxSize, 'D');

    // Draw internal gridlines (0.5" increments) - dashed gray
    $pdf->SetDrawColor(180, 180, 180);
    $pdf->SetLineWidth(0.5 / 72);
    $pdf->SetLineStyle(['dash' => '2,2']);
    for ($i = 0.5; $i < $boxSize; $i += 0.5) {
        $pdf->Line($x, $y + $i, $x + $boxSize, $y + $i); // Horizontal
        $pdf->Line($x + $i, $y, $x + $i, $y + $boxSize); // Vertical
    }
    $pdf->SetLineStyle(['dash' => 0]);
    $pdf->SetDrawColor(0, 0, 0);

    // Draw tick marks on bottom edge (ruler style)
    $pdf->SetLineWidth(1 / 72);
    for ($i = 0; $i <= 4; $i++) {
        $tickX = $x + ($i * 0.5);
        $tickLength = ($i % 2 == 0) ? 0.12 : 0.08; // Longer ticks at 0", 1", 2"
        $pdf->Line($tickX, $y + $boxSize, $tickX, $y + $boxSize + $tickLength);
    }

    // Draw tick marks on right edge (ruler style)
    for ($i = 0; $i <= 4; $i++) {
        $tickY = $y + ($i * 0.5);
        $tickLength = ($i % 2 == 0) ? 0.12 : 0.08;
        $pdf->Line($x + $boxSize, $tickY, $x + $boxSize + $tickLength, $tickY);
    }

    // Bottom edge labels (0.0", 0.5", 1.0", 1.5", 2.0") - 50% larger fonts
    $pdf->SetTextColor(0, 0, 0);
    for ($i = 0; $i <= 4; $i++) {
        $labelValue = number_format($i * 0.5, 1) . '"';
        $labelX = $x + ($i * 0.5);
        $labelY = $y + $boxSize + 0.22;
        $fontSize = ($i % 2 == 0) ? 11 : 9;
        $pdf->SetFont('helvetica', '', $fontSize);
        // Center the label under the tick mark
        $labelWidth = $pdf->GetStringWidth($labelValue);
        $pdf->Text($labelX - ($labelWidth / 2), $labelY, $labelValue);
    }

    // Right edge labels (0.0", 0.5", 1.0", 1.5", 2.0") - 50% larger fonts
    for ($i = 0; $i <= 4; $i++) {
        $labelValue = number_format($i * 0.5, 1) . '"';
        $labelX = $x + $boxSize + 0.18;
        $labelY = $y + ($i * 0.5) + 0.05; // Slight offset to center vertically
        $fontSize = ($i % 2 == 0) ? 11 : 9;
        $pdf->SetFont('helvetica', '', $fontSize);
        $pdf->Text($labelX, $labelY, $labelValue);
    }

    // Center labels inside the box - 50% larger fonts
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $centerX = $x + ($boxSize / 2);
    $centerY = $y + ($boxSize / 2);

    $pdf->Text($centerX - 0.38, $centerY - 0.15, 'Printer');
    $pdf->Text($centerX - 0.25, $centerY + 0.08, 'Test');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Text($centerX - 0.48, $centerY + 0.30, '2" x 2" Scale');
}

/**
 * Add tile reference text to page
 */
function addTileReference($pdf, $pageWidth, $pageHeight, $tileNumber, $totalTiles, $patternName, $margin) {
    $logoWidth = 2.7;  // Reduced by 20% (3.375 * 0.8 = 2.7)
    $logoHeight = 2.7; // Reduced by 20% (3.375 * 0.8 = 2.7)
    $logoX = $pageWidth - $logoWidth - 0.5;
    $logoY = -0.5;

    $tileInfoY = $logoY + $logoHeight - 0.42; // Moved down 0.5" to avoid overlapping URL
    $centerX = $logoX + ($logoWidth / 2);

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);

    if ($totalTiles == 1) {
        $tileText = $patternName;
    } else {
        $tileText = sprintf('Tile %d of %d', $tileNumber, $totalTiles);
    }

    $textWidth = $pdf->GetStringWidth($tileText);
    $x = $centerX - ($textWidth / 2);

    $pdf->SetAlpha(0.85);
    $pdf->SetFillColor(255, 255, 255);
    $boxWidth = $textWidth + 0.2;
    $boxHeight = 0.3;
    $pdf->Rect($x - 0.1, $tileInfoY - 0.05, $boxWidth, $boxHeight, 'F');
    $pdf->SetAlpha(1);

    $pdf->Text($x, $tileInfoY + 0.12, $tileText);
    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Add corner registration marks for tile alignment
 */
function addRegistrationMarks($pdf, $pageWidth, $pageHeight, $margin, $tileX, $tileY, $tilesX, $tilesY) {
    $markSize = 0.25;
    $circleRadius = 0.08;

    $pdf->SetLineStyle([
        'width' => 0.012,
        'cap' => 'round',
        'join' => 'round',
        'dash' => 0,
        'color' => [0, 0, 0]
    ]);

    $corners = [
        ['x' => $margin, 'y' => $margin, 'label' => 'TL'],
        ['x' => $pageWidth - $margin, 'y' => $margin, 'label' => 'TR'],
        ['x' => $margin, 'y' => $pageHeight - $margin, 'label' => 'BL'],
        ['x' => $pageWidth - $margin, 'y' => $pageHeight - $margin, 'label' => 'BR']
    ];

    foreach ($corners as $corner) {
        $x = $corner['x'];
        $y = $corner['y'];

        $pdf->Circle($x, $y, $circleRadius, 0, 360, 'D');
        $pdf->Line($x - $markSize, $y, $x + $markSize, $y);
        $pdf->Line($x, $y - $markSize, $x, $y + $markSize);
    }
}

/**
 * Add diagonal alignment lines for tile overlap zones
 */
function addDiagonalAlignmentLines($pdf, $pageWidth, $pageHeight, $margin, $tileX, $tileY, $tilesX, $tilesY, $overlapZone = 0.5) {
    $pdf->SetLineStyle([
        'width' => 0.008,
        'cap' => 'round',
        'join' => 'round',
        'dash' => '1,2',
        'color' => [128, 128, 128]
    ]);

    $contentLeft = $margin;
    $contentRight = $pageWidth - $margin;
    $contentTop = $margin;
    $contentBottom = $pageHeight - $margin;

    $numLines = 3;

    // Right edge diagonal lines
    if ($tileX < $tilesX - 1) {
        $overlapStart = $contentRight - $overlapZone;
        for ($i = 0; $i < $numLines; $i++) {
            $spacing = $overlapZone / ($numLines + 1);
            $xPos = $overlapStart + ($i + 1) * $spacing;
            $lineLength = min($contentBottom - $contentTop, $overlapZone);
            $pdf->Line($xPos, $contentTop, $xPos + $lineLength * 0.3, $contentTop + $lineLength);
        }
    }

    // Bottom edge diagonal lines
    if ($tileY < $tilesY - 1) {
        $overlapStart = $contentBottom - $overlapZone;
        for ($i = 0; $i < $numLines; $i++) {
            $spacing = $overlapZone / ($numLines + 1);
            $yPos = $overlapStart + ($i + 1) * $spacing;
            $lineLength = min($contentRight - $contentLeft, $overlapZone);
            $pdf->Line($contentLeft, $yPos, $contentLeft + $lineLength, $yPos + $lineLength * 0.3);
        }
    }

    $pdf->SetLineStyle([
        'width' => 0.012,
        'cap' => 'round',
        'join' => 'round',
        'dash' => 0,
        'color' => [0, 0, 0]
    ]);
}

/**
 * Sanitize SVG content for TCPDF compatibility
 * Removes unsupported elements like <pattern> and replaces pattern fills with solid gray
 */
function sanitizeSvgForPdf($svgContent) {
    // Remove <pattern ...>...</pattern> blocks from <defs>
    $svgContent = preg_replace('/<pattern\b[^>]*>.*?<\/pattern>/s', '', $svgContent);
    // Replace fill="url(#hatch)" (or any pattern reference) with a light gray fill
    $svgContent = preg_replace('/fill="url\(#[^)]+\)"/', 'fill="#ddd"', $svgContent);
    return $svgContent;
}

/**
 * Calculate tile grid for pattern tiling
 */
function calculateTileGrid($patternWidth, $patternHeight, $paperPortrait, $paperLandscape, $margin) {
    $usableWidthP = $paperPortrait['width'] - (2 * $margin);
    $usableHeightP = $paperPortrait['height'] - (2 * $margin);

    // Check if pattern fits in portrait single page
    if ($patternWidth <= $usableWidthP && $patternHeight <= $usableHeightP) {
        return [
            'orientation' => 'P',
            'paper' => $paperPortrait,
            'tilesX' => 1,
            'tilesY' => 1,
            'totalTiles' => 1,
            'usableWidth' => $usableWidthP,
            'usableHeight' => $usableHeightP
        ];
    }

    // Try portrait tiling if width fits
    if ($patternWidth <= $usableWidthP) {
        $tilesX = 1;
        $tilesY = (int)ceil($patternHeight / $usableHeightP);

        return [
            'orientation' => 'P',
            'paper' => $paperPortrait,
            'tilesX' => $tilesX,
            'tilesY' => $tilesY,
            'totalTiles' => $tilesX * $tilesY,
            'usableWidth' => $usableWidthP,
            'usableHeight' => $usableHeightP
        ];
    }

    // Switch to landscape for tiling
    $usableWidthL = $paperLandscape['width'] - (2 * $margin);
    $usableHeightL = $paperLandscape['height'] - (2 * $margin);

    $tilesX = (int)ceil($patternWidth / $usableWidthL);
    $tilesY = (int)ceil($patternHeight / $usableHeightL);

    return [
        'orientation' => 'L',
        'paper' => $paperLandscape,
        'tilesX' => $tilesX,
        'tilesY' => $tilesY,
        'totalTiles' => $tilesX * $tilesY,
        'usableWidth' => $usableWidthL,
        'usableHeight' => $usableHeightL
    ];
}

/**
 * Get pattern dimensions from SVG content
 */
function getSVGDimensions($svgContent, $scale) {
    $svgWidth = null;
    $svgHeight = null;

    // Extract from viewBox
    if (preg_match('/viewBox="[0-9.]+ [0-9.]+ ([0-9.]+) ([0-9.]+)"/', $svgContent, $viewBoxMatch)) {
        $svgWidth = floatval($viewBoxMatch[1]) / $scale;
        $svgHeight = floatval($viewBoxMatch[2]) / $scale;
    }
    // Fallback: width/height attributes
    elseif (preg_match('/width="([0-9.]+)"/', $svgContent, $widthMatch) &&
            preg_match('/height="([0-9.]+)"/', $svgContent, $heightMatch)) {
        $viewScale = 2.0;
        $svgWidth = floatval($widthMatch[1]) / ($scale * $viewScale);
        $svgHeight = floatval($heightMatch[1]) / ($scale * $viewScale);
    }

    if ($svgWidth && $svgHeight) {
        return [
            'width' => $svgWidth,
            'height' => $svgHeight,
            'svgWidth' => $svgWidth,
            'svgHeight' => $svgHeight
        ];
    }

    return ['width' => 10.0, 'height' => 10.0];
}

/**
 * Modify SVG viewBox for tiling
 */
function modifySVGForTile($svgContent, $tileOffsetX, $tileOffsetY, $tileWidth, $tileHeight, $scale) {
    $viewBoxX = $tileOffsetX * $scale;
    $viewBoxY = $tileOffsetY * $scale;
    $viewBoxWidth = $tileWidth * $scale;
    $viewBoxHeight = $tileHeight * $scale;

    $newViewBox = sprintf('viewBox="%.2f %.2f %.2f %.2f"', $viewBoxX, $viewBoxY, $viewBoxWidth, $viewBoxHeight);

    if (preg_match('/viewBox="[^"]*"/', $svgContent)) {
        $modifiedSVG = preg_replace('/viewBox="[^"]*"/', $newViewBox, $svgContent);
    } else {
        $modifiedSVG = preg_replace('/<svg/', '<svg ' . $newViewBox, $svgContent, 1);
    }

    return $modifiedSVG;
}

/**
 * Generate Summary Sheet as the first page
 */
function addSummarySheet($pdf, $pageWidth, $pageHeight, $margin, $patternData, $tileInfo, $paperSize, $patternDisplayName, $patterns) {
    global $scale;

    // Add logo
    addCMLogo($pdf, $pageWidth, $pageHeight);

    $contentStartY = 1.2; // Moved down 1.2" from top
    $contentWidth = $pageWidth - (2 * $margin);

    // Title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(0, 51, 102);
    $titleText = strtoupper($patternDisplayName) . ' PATTERN';
    $titleX = $margin;
    $titleY = $contentStartY;
    $pdf->Text($titleX, $titleY, $titleText);

    // Decorative line
    $pdf->SetDrawColor(0, 102, 153);
    $pdf->SetLineWidth(0.02);
    $lineY = $titleY + 0.35;
    $pdf->Line($margin, $lineY, $pageWidth - $margin - 4, $lineY);

    // Get customer details for metadata
    $customerName = $patternData['metadata']['customer_name'] ?? 'Unknown';
    $contactNumber = $patternData['metadata']['contact_number'] ?? $patternData['metadata']['mobile_number'] ?? '';

    // Pattern metadata (left column) and Printing instructions (right column)
    $instructionsY = $lineY + 0.4; // Close to title

    // Left column: Pattern metadata (including customer info)
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $metaY = $instructionsY;
    $metaLineHeight = 0.18;

    $pdf->Text($margin, $metaY, 'Customer: ' . $customerName);
    $metaY += $metaLineHeight;

    if (!empty($contactNumber)) {
        $pdf->Text($margin, $metaY, 'Contact: ' . $contactNumber);
        $metaY += $metaLineHeight;
    }

    $generatedDate = date('d-M-Y h:i A');
    $pdf->Text($margin, $metaY, 'Generated: ' . $generatedDate);
    $metaY += $metaLineHeight;

    $patternId = $patternData['metadata']['measurement_id'] ?? 'N/A';
    $pdf->Text($margin, $metaY, 'Pattern ID: ' . $patternId);
    $metaY += $metaLineHeight;

    $pdf->Text($margin, $metaY, 'Paper Size: ' . $paperSize);
    $metaY += $metaLineHeight;

    $pdf->Text($margin, $metaY, 'Scale: 1:1 (Actual Size)');

    // Right column: Printing instructions
    $instrColX = $margin + 2.0; // Start after metadata column
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text($instrColX, $instructionsY, 'PRINTING & ASSEMBLY INSTRUCTIONS');

    $instrBoxY = $instructionsY + 0.12;
    $instrBoxHeight = 0.95;

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(50, 50, 50);

    $instructions = [
        '1. Print at 100% scale (no scaling/fit-to-page). Verify using the 2-inch square reference on first tile.',
        '2. Match tiles using registration marks (+) at corners. Align diagonal lines for precise positioning.',
        '3. Overlap tiles at matching marks, then tape securely before cutting the pattern.'
    ];

    $instrTextY = $instrBoxY + 0.15;
    $lineHeight = 0.18;
    foreach ($instructions as $instruction) {
        $pdf->Text($instrColX + 0.15, $instrTextY, $instruction);
        $instrTextY += $lineHeight;
    }

    $pdf->SetTextColor(0, 0, 0);

    // Two-column layout
    $twoColY = $instrBoxY + $instrBoxHeight + 0.15;
    $leftColWidth = 2.0; // Fixed 2" width for measurements column
    $colGap = 0.5; // Fixed 0.5" gutter between columns
    $rightColWidth = $contentWidth - $leftColWidth - $colGap;

    // Left column: Measurements
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text($margin, $twoColY, 'MEASUREMENTS');

    $measBoxY = $twoColY + 0.12;
    $measBoxHeight = 5.5;

    $measurements = $patternData['measurements'] ?? [];

    // Define measurement labels (generic list that works for most patterns)
    // Note: fndepth and frontNeckDepth are aliases - use whichever is available
    $fndepthValue = $measurements['fndepth'] ?? $measurements['frontNeckDepth'] ?? '';
    $measList = [
        ['label' => 'Bust', 'key' => 'bust'],
        ['label' => 'Chest', 'key' => 'chest'],
        ['label' => 'Waist', 'key' => 'waist'],
        ['label' => 'Shoulder', 'key' => 'shoulder'],
        ['label' => 'Front Shoulder', 'key' => 'fshoulder'],
        ['label' => 'Armhole', 'key' => 'armhole'],
        ['label' => 'F.Neck Depth', 'key' => '_fndepth_combined', 'value' => $fndepthValue],
        ['label' => 'B.Neck Depth', 'key' => 'bnDepth'],
        ['label' => 'Front Length', 'key' => 'flength'],
        ['label' => 'Back Length', 'key' => 'blength'],
        ['label' => 'Apex', 'key' => 'apex'],
        ['label' => 'Sleeve Length', 'key' => 'slength'],
        ['label' => 'Sleeve Round', 'key' => 'saround'],
        ['label' => 'Sleeve Open', 'key' => 'sopen'],
    ];

    $pdf->SetFont('helvetica', '', 9);
    $measY = $measBoxY + 0.2;
    $lineHeight = 0.25; // Reduced from 0.35 for tighter spacing

    foreach ($measList as $meas) {
        // Use pre-computed value if available, otherwise look up from measurements
        $value = isset($meas['value']) ? $meas['value'] : ($measurements[$meas['key']] ?? '');
        if ($value !== '' && $value !== null) {
            $pdf->Text($margin + 0.1, $measY, $meas['label'] . ':');
            $pdf->Text($margin + 1.1, $measY, number_format((float)$value, 2) . '"');
            $measY += $lineHeight;
        }
    }

    // ----- SCALE BOX (below measurements in left column) -----
    $scaleBoxY = $measY + 0.5; // 0.5" padding above scale box
    drawScaleBox($pdf, $margin, $scaleBoxY);

    // Right column: Pattern tiles assembly guide
    $rightColX = $margin + $leftColWidth + $colGap;
    // Title will be rendered after we calculate totalPages

    $tilesBoxY = $twoColY + 0.12;
    $tilesBoxHeight = $measBoxHeight;

    $thumbMaxWidth = 2.5;
    $thumbMaxHeight = 2.2;
    $thumbGap = 0.35;
    $thumbStartY = $tilesBoxY + 0.35;
    $thumbStartX = $rightColX + 0.15;

    $thumbX = $thumbStartX;
    $thumbY = $thumbStartY;
    $thumbsPerRow = 2;
    $thumbCount = 0;
    $totalPages = 0;
    $patternNumber = 1;

    $svgScale = 25.4;

    foreach ($patterns as $key => $pattern) {
        if (isset($pattern['svg_content'])) {
            $svgWidth = $thumbMaxWidth;
            $svgHeight = $thumbMaxHeight;
            $patternWidth = 0;
            $patternHeight = 0;

            if (preg_match('/viewBox="[0-9.]+ [0-9.]+ ([0-9.]+) ([0-9.]+)"/', $pattern['svg_content'], $vbMatch)) {
                $patternWidth = floatval($vbMatch[1]) / $svgScale;
                $patternHeight = floatval($vbMatch[2]) / $svgScale;

                $aspectRatio = $patternWidth / $patternHeight;

                if ($aspectRatio > ($thumbMaxWidth / $thumbMaxHeight)) {
                    $svgWidth = $thumbMaxWidth;
                    $svgHeight = $thumbMaxWidth / $aspectRatio;
                } else {
                    $svgHeight = $thumbMaxHeight;
                    $svgWidth = $thumbMaxHeight * $aspectRatio;
                }
            }

            // Pattern label - use standardized names for sariBlouse
            $patternLabel = $pattern['name'] ?? ucfirst($key);

            // For sariBlouse type, use consistent naming like old sariBlouse_PDF.php
            if (strpos($patternDisplayName, 'Blouse') !== false || strpos($patternDisplayName, 'blouse') !== false) {
                switch ($key) {
                    case 'front':
                        $patternLabel = 'FRONT';
                        break;
                    case 'back':
                        $patternLabel = 'BACK';
                        break;
                    case 'patti':
                        $patternLabel = 'PATTI';
                        break;
                    case 'sleeve':
                        $patternLabel = 'SLEEVE';
                        break;
                }
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Text($thumbX, $thumbY, $patternNumber . '. ' . strtoupper($patternLabel));

            // Thumbnail border
            $pdf->SetDrawColor(150, 150, 150);
            $pdf->SetLineWidth(0.01);
            $thumbBoxY = $thumbY + 0.18;
            $pdf->Rect($thumbX, $thumbBoxY, $svgWidth, $svgHeight, 'D');

            // Render SVG (sanitized for TCPDF)
            $pdf->ImageSVG('@' . sanitizeSvgForPdf($pattern['svg_content']), $thumbX, $thumbBoxY, $svgWidth, $svgHeight, '', '', '', 0, false);

            // Tile grid overlay
            $tiles = $tileInfo[$key] ?? ['tilesX' => 1, 'tilesY' => 1, 'totalTiles' => 1];
            if ($tiles['totalTiles'] > 1 && $patternWidth > 0 && $patternHeight > 0) {
                $pdf->SetDrawColor(255, 0, 0);
                $pdf->SetLineStyle(['width' => 0.02, 'dash' => '3,2']);

                if ($tiles['tilesX'] > 1) {
                    for ($i = 1; $i < $tiles['tilesX']; $i++) {
                        $lineX = $thumbX + ($svgWidth * $i / $tiles['tilesX']);
                        $pdf->Line($lineX, $thumbBoxY, $lineX, $thumbBoxY + $svgHeight);
                    }
                }

                if ($tiles['tilesY'] > 1) {
                    for ($i = 1; $i < $tiles['tilesY']; $i++) {
                        $tileLineY = $thumbBoxY + ($svgHeight * $i / $tiles['tilesY']);
                        $pdf->Line($thumbX, $tileLineY, $thumbX + $svgWidth, $tileLineY);
                    }
                }

                // Add tile numbers as watermarks
                $tileWidth = $svgWidth / $tiles['tilesX'];
                $tileHeight = $svgHeight / $tiles['tilesY'];
                $tileNum = 1;

                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->SetTextColor(255, 0, 0);  // Red color for visibility
                $pdf->SetAlpha(0.5);  // Semi-transparent watermark effect

                for ($row = 0; $row < $tiles['tilesY']; $row++) {
                    for ($col = 0; $col < $tiles['tilesX']; $col++) {
                        $tileCenterX = $thumbX + ($col * $tileWidth) + ($tileWidth / 2);
                        $tileCenterY = $thumbBoxY + ($row * $tileHeight) + ($tileHeight / 2);

                        // Center the number in the tile
                        $numWidth = $pdf->GetStringWidth((string)$tileNum);
                        $pdf->Text($tileCenterX - ($numWidth / 2), $tileCenterY + 0.05, (string)$tileNum);
                        $tileNum++;
                    }
                }

                $pdf->SetAlpha(1);  // Reset transparency
                $pdf->SetTextColor(0, 0, 0);  // Reset text color

                $pdf->SetLineStyle(['width' => 0.01, 'dash' => 0]);
                $pdf->SetDrawColor(0, 0, 0);
            }

            // Tile count label
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(80, 80, 80);
            $tileLabel = $tiles['totalTiles'] == 1 ? '(1 page)' : '(' . $tiles['totalTiles'] . ' tiles: ' . $tiles['tilesX'] . 'x' . $tiles['tilesY'] . ')';
            $pdf->Text($thumbX, $thumbBoxY + $svgHeight + 0.15, $tileLabel);
            $pdf->SetTextColor(0, 0, 0);

            $totalPages += $tiles['totalTiles'];
            $patternNumber++;

            $thumbCount++;
            if ($thumbCount % $thumbsPerRow == 0) {
                $thumbX = $thumbStartX;
                $thumbY += $thumbMaxHeight + 0.55;
            } else {
                $thumbX += $thumbMaxWidth + $thumbGap;
            }
        }
    }

    // Calculate the ending Y position of tiles (after last row)
    $tilesEndY = $thumbStartY + (ceil($thumbCount / $thumbsPerRow) * ($thumbMaxHeight + 0.55));

    // Render title with page count now that we know totalPages
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text($rightColX, $twoColY, 'PATTERN TILES (' . $totalPages . '), ASSEMBLY GUIDE');

    // =========================================================================
    // DISCLAIMER SECTION (Below tiles in right column)
    // =========================================================================
    $disclaimerY = $tilesEndY + 0.1;

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text($rightColX, $disclaimerY, 'DISCLAIMER');

    // Disclaimer text
    $pdf->SetFont('helvetica', '', 8);
    $discTextY = $disclaimerY + 0.2;
    $discLineHeight = 0.15;

    $disclaimerLines = [
        '• Pattern generated based on measurements provided.',
        '• Verify all measurements before cutting fabric.',
        '• Seam allowance NOT included - add as required.',
        '• Print at 100% scale - verify with 2"x2" scale box.',
        '• CuttingMaster.in not responsible for errors.',
    ];

    foreach ($disclaimerLines as $line) {
        $pdf->Text($rightColX + 0.1, $discTextY, $line);
        $discTextY += $discLineHeight;
    }

    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Render a single pattern (with tiling support)
 */
function renderPattern($pdf, $svgContent, $patternName, $patternNumber, $customerName, $paperSizes, $paperSize, $margin, $scale, $startX, $startY) {
    $svgContent = sanitizeSvgForPdf($svgContent);
    $dims = getSVGDimensions($svgContent, $scale);
    $tileGrid = calculateTileGrid($dims['width'], $dims['height'],
                                  $paperSizes[$paperSize]['portrait'],
                                  $paperSizes[$paperSize]['landscape'],
                                  $margin);

    $patternPaper = $tileGrid['paper'];
    $patternOrientation = $tileGrid['orientation'];
    $displayName = $patternNumber . '. ' . strtoupper($patternName);

    if ($tileGrid['totalTiles'] == 1) {
        // Single page
        $pdf->AddPage($patternOrientation, [$patternPaper['width'], $patternPaper['height']]);
        addCMLogo($pdf, $patternPaper['width'], $patternPaper['height']);
        addWatermark($pdf, $patternPaper['width'], $patternPaper['height'], $displayName, $customerName);
        addTileReference($pdf, $patternPaper['width'], $patternPaper['height'], 1, 1, $patternName, $margin);

        $renderWidth = $dims['width'];
        $renderHeight = $dims['height'];
        $pdf->ImageSVG('@' . $svgContent, $startX, $startY, $renderWidth, $renderHeight, '', '', '', 0, false);
    } else {
        // Multi-page tiling
        $tileNumber = 1;

        for ($ty = 0; $ty < $tileGrid['tilesY']; $ty++) {
            for ($tx = 0; $tx < $tileGrid['tilesX']; $tx++) {
                $tileOffsetX = $tx * $tileGrid['usableWidth'];
                $tileOffsetY = $ty * $tileGrid['usableHeight'];

                $actualTileWidth = min($tileGrid['usableWidth'], $dims['width'] - $tileOffsetX);
                $actualTileHeight = min($tileGrid['usableHeight'], $dims['height'] - $tileOffsetY);

                if ($tileOffsetX >= $dims['width'] || $tileOffsetY >= $dims['height']) {
                    continue;
                }

                if ($actualTileWidth < 0.1 || $actualTileHeight < 0.1) {
                    continue;
                }

                $pdf->AddPage($patternOrientation, [$patternPaper['width'], $patternPaper['height']]);
                addCMLogo($pdf, $patternPaper['width'], $patternPaper['height']);
                addWatermark($pdf, $patternPaper['width'], $patternPaper['height'],
                           sprintf('%s (Tile %d of %d)', $displayName, $tileNumber, $tileGrid['totalTiles']),
                           $customerName);
                addTileReference($pdf, $patternPaper['width'], $patternPaper['height'], $tileNumber, $tileGrid['totalTiles'], $patternName, $margin);

                // Add registration marks and diagonal alignment lines BEFORE pattern (behind)
                addRegistrationMarks($pdf, $patternPaper['width'], $patternPaper['height'], $margin, $tx, $ty, $tileGrid['tilesX'], $tileGrid['tilesY']);
                addDiagonalAlignmentLines($pdf, $patternPaper['width'], $patternPaper['height'], $margin, $tx, $ty, $tileGrid['tilesX'], $tileGrid['tilesY']);

                $tileSVG = modifySVGForTile($svgContent, $tileOffsetX, $tileOffsetY, $actualTileWidth, $actualTileHeight, $scale);

                $tileRenderWidth = $actualTileWidth;
                $tileRenderHeight = $actualTileHeight;
                $pdf->ImageSVG('@' . $tileSVG, $startX, $startY, $tileRenderWidth, $tileRenderHeight, '', '', '', 0, false);

                $tileNumber++;
            }
        }
    }

    return $tileGrid;
}

// =============================================================================
// CREATE PDF
// =============================================================================

try {
    // Validate patterns have SVG content
    if (empty($patterns)) {
        ob_end_clean();
        http_response_code(400);
        die("ERROR: No patterns found in session data. Patterns array is empty. Please regenerate the pattern.");
    }

    // Check if any pattern has SVG content
    $hasValidPattern = false;
    foreach ($patterns as $key => $pattern) {
        if (!empty($pattern['svg_content'])) {
            $hasValidPattern = true;
            break;
        }
    }

    if (!$hasValidPattern) {
        ob_end_clean();
        http_response_code(400);
        die("ERROR: No SVG content found in patterns. Pattern keys found: " . implode(', ', array_keys($patterns)) . ". Please regenerate the pattern.");
    }

    $pdf = new TCPDF('P', 'in', [$paper['width'], $paper['height']], true, 'UTF-8', false);

    $pdf->SetCreator('CM-2025 Pattern Generator');
    $pdf->SetAuthor($customerName);
    $pdf->SetTitle($patternDisplayName . ' Pattern - ' . $customerName);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($margin, $margin, $margin);
    $pdf->SetAutoPageBreak(false, $margin);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);

    $startX = $margin;
    $startY = $margin;

    // =============================================================================
    // PRE-CALCULATE TILE INFO FOR ALL PATTERNS
    // =============================================================================
    $tileInfo = [];

    foreach ($patterns as $key => $pattern) {
        if (isset($pattern['svg_content'])) {
            $dims = getSVGDimensions($pattern['svg_content'], $scale);
            $tileGrid = calculateTileGrid($dims['width'], $dims['height'],
                                          $paperSizes[$paperSize]['portrait'],
                                          $paperSizes[$paperSize]['landscape'], $margin);
            $tileInfo[$key] = [
                'tilesX' => $tileGrid['tilesX'],
                'tilesY' => $tileGrid['tilesY'],
                'totalTiles' => $tileGrid['totalTiles']
            ];
        }
    }

    // =============================================================================
    // SUMMARY SHEET (Page 1)
    // =============================================================================
    $summaryMargin = 0.5; // Summary sheet uses 0.5" margins
    $pdf->AddPage('P', [$paper['width'], $paper['height']]);
    addSummarySheet($pdf, $paper['width'], $paper['height'], $summaryMargin, $patternData, $tileInfo, $paperSize, $patternDisplayName, $patterns);

    // =============================================================================
    // RENDER ALL PATTERNS
    // =============================================================================
    $patternNumber = 1;

    // Define standard pattern order (can be overridden by 'order' key in pattern data)
    $standardOrder = ['front', 'back', 'patti', 'sleeve', 'side_panel', 'center_panel'];

    // Sort patterns by order if specified, otherwise use standard order
    $sortedPatterns = [];
    foreach ($standardOrder as $key) {
        if (isset($patterns[$key])) {
            $sortedPatterns[$key] = $patterns[$key];
        }
    }
    // Add any remaining patterns not in standard order
    foreach ($patterns as $key => $pattern) {
        if (!isset($sortedPatterns[$key])) {
            $sortedPatterns[$key] = $pattern;
        }
    }

    foreach ($sortedPatterns as $key => $pattern) {
        if (isset($pattern['svg_content'])) {
            // Use standardized pattern names for sariBlouse patterns
            $patternName = $pattern['name'] ?? ucfirst($key);

            // For sariBlouse type, use "BLOUSE" prefix like old sariBlouse_PDF.php
            if ($patternType === 'sariBlouse') {
                switch ($key) {
                    case 'front':
                        $patternName = 'BLOUSE FRONT';
                        break;
                    case 'back':
                        $patternName = 'BLOUSE BACK';
                        break;
                    case 'patti':
                        $patternName = 'PATTI';
                        break;
                    case 'sleeve':
                        $patternName = 'SLEEVE';
                        break;
                }
            }

            renderPattern($pdf, $pattern['svg_content'], $patternName, $patternNumber, $customerName, $paperSizes, $paperSize, $margin, $scale, $startX, $startY);
            $patternNumber++;
        }
    }

    // =============================================================================
    // OUTPUT PDF
    // =============================================================================
    ob_end_clean();
    $pdf->Output($pdfFilename, 'D');
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>PDF Generation Error</title>";
    echo "<style>body{font-family:Arial,sans-serif;padding:40px;background:#f5f5f5}";
    echo ".error-box{background:white;border-radius:8px;padding:30px;max-width:800px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
    echo "h1{color:#dc2626;margin:0 0 20px}pre{background:#f1f5f9;padding:15px;border-radius:4px;overflow:auto;font-size:13px}";
    echo ".btn{display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;margin-top:20px}</style></head><body>";
    echo "<div class='error-box'>";
    echo "<h1>PDF Generation Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
    echo "<details><summary style='cursor:pointer;color:#3b82f6'>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    echo "<p style='margin-top:20px'><strong>Possible solutions:</strong></p><ul>";
    echo "<li>Go back to the pattern preview page and view the pattern first</li>";
    echo "<li>Try a different paper size</li>";
    echo "<li>Check if the pattern SVG content is valid</li>";
    echo "</ul>";
    echo "<a href='javascript:history.back()' class='btn'>← Go Back</a>";
    echo "</div></body></html>";
    exit;
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>PDF Generation Error</title>";
    echo "<style>body{font-family:Arial,sans-serif;padding:40px;background:#f5f5f5}";
    echo ".error-box{background:white;border-radius:8px;padding:30px;max-width:800px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
    echo "h1{color:#dc2626;margin:0 0 20px}pre{background:#f1f5f9;padding:15px;border-radius:4px;overflow:auto;font-size:13px}";
    echo ".btn{display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;margin-top:20px}</style></head><body>";
    echo "<div class='error-box'>";
    echo "<h1>PDF Generation Fatal Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
    echo "<details><summary style='cursor:pointer;color:#3b82f6'>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    echo "<a href='javascript:history.back()' class='btn'>← Go Back</a>";
    echo "</div></body></html>";
    exit;
}
