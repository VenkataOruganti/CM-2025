<?php
/**
 * =============================================================================
 * PROJECTOR PDF GENERATOR - INDIVIDUAL FILES
 * =============================================================================
 *
 * Generates individual PDF files optimized for projector-based pattern cutting.
 *
 * Modes:
 * 1. ?action=list - Returns JSON list of available patterns
 * 2. ?action=download&pattern=front - Downloads specific pattern PDF
 * 3. No action - Downloads scale calibration PDF by default
 *
 * @author CM-2025
 * @date January 2026
 */

// Suppress PHP 8.5 deprecation warnings for TCPDF compatibility
error_reporting(E_ALL & ~E_DEPRECATED);

// Start output buffering to prevent any output before PDF generation
ob_start();

// Start session to access pattern data
session_start();

// Load TCPDF library
require_once(__DIR__ . '/../vendor/autoload.php');

// =============================================================================
// CONFIGURATION
// =============================================================================

$scale = 25.4; // Pixels per inch (standard conversion)

// Scale box dimensions (for projector calibration)
$scaleBoxSize = 10.0; // 10 inches x 10 inches

// Page padding (small padding around patterns for clean edges)
$pagePadding = 0.5; // 0.5 inch padding

// =============================================================================
// GET PARAMETERS
// =============================================================================

$action = $_GET['action'] ?? 'download';
$patternKey = $_GET['pattern'] ?? 'scale';
$measurementId = isset($_GET['measurement_id']) ? intval($_GET['measurement_id']) : null;

// =============================================================================
// GET PATTERN DATA FROM SESSION
// =============================================================================

$patternData = null;

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
    if ($action === 'list') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No pattern data found in session']);
        exit;
    }
    http_response_code(400);
    die("ERROR: No pattern data found in session. Please generate a pattern first.");
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

// Safe customer name for filenames
$safeCustomerName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customerName);
$safeCustomerName = preg_replace('/_+/', '_', $safeCustomerName);
$safeCustomerName = trim($safeCustomerName, '_');

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

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
            'height' => $svgHeight
        ];
    }

    return ['width' => 10.0, 'height' => 10.0];
}

/**
 * Draw the 10" x 10" scale calibration box
 */
function drawScaleCalibrationBox($pdf, $boxSize, $pageWidth, $pageHeight) {
    // Center the box on the page
    $boxX = ($pageWidth - $boxSize) / 2;
    $boxY = ($pageHeight - $boxSize) / 2;

    // Draw filled black box
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Rect($boxX, $boxY, $boxSize, $boxSize, 'F');

    // Add white measurement labels around the box
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);

    // Top label
    $topLabel = '10 inches';
    $topLabelWidth = $pdf->GetStringWidth($topLabel);
    $pdf->Text($boxX + ($boxSize - $topLabelWidth) / 2, $boxY - 0.3, $topLabel);

    // Right side label (rotated)
    $pdf->StartTransform();
    $pdf->Rotate(-90, $boxX + $boxSize + 0.5, $boxY + $boxSize / 2);
    $pdf->Text($boxX + $boxSize + 0.5 - $topLabelWidth / 2, $boxY + $boxSize / 2, $topLabel);
    $pdf->StopTransform();

    // Draw white border inside the black box for visibility
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0.02);
    $innerMargin = 0.1;
    $pdf->Rect($boxX + $innerMargin, $boxY + $innerMargin,
               $boxSize - 2 * $innerMargin, $boxSize - 2 * $innerMargin, 'D');

    // Draw 1-inch grid lines inside (white, dashed)
    $pdf->SetLineStyle(['dash' => '2,2']);
    for ($i = 1; $i < $boxSize; $i++) {
        // Vertical lines
        $pdf->Line($boxX + $i, $boxY + $innerMargin, $boxX + $i, $boxY + $boxSize - $innerMargin);
        // Horizontal lines
        $pdf->Line($boxX + $innerMargin, $boxY + $i, $boxX + $boxSize - $innerMargin, $boxY + $i);
    }
    $pdf->SetLineStyle(['dash' => 0]);

    // Add corner measurements (1" marks)
    $pdf->SetFont('helvetica', '', 10);
    for ($i = 0; $i <= 10; $i += 2) {
        // Bottom edge labels
        $label = $i . '"';
        $labelWidth = $pdf->GetStringWidth($label);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($boxX + $i - $labelWidth / 2, $boxY + $boxSize + 0.25, $label);
    }

    // Reset colors
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
}

/**
 * Add pattern name label to page
 */
function addPatternLabel($pdf, $patternName, $pageWidth, $pageHeight, $padding) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(100, 100, 100);

    // Position at top-left with padding
    $pdf->Text($padding, $padding + 0.15, strtoupper($patternName));

    // Add "PROJECTOR MODE" indicator at top-right
    $projectorLabel = 'PROJECTOR MODE';
    $labelWidth = $pdf->GetStringWidth($projectorLabel);
    $pdf->Text($pageWidth - $padding - $labelWidth, $padding + 0.15, $projectorLabel);

    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Add CM branding (minimal for projector mode)
 */
function addMinimalBranding($pdf, $pageWidth, $pageHeight, $padding) {
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(150, 150, 150);

    $brandText = 'CuttingMaster.in';
    $brandWidth = $pdf->GetStringWidth($brandText);

    // Bottom-right corner
    $pdf->Text($pageWidth - $padding - $brandWidth, $pageHeight - $padding, $brandText);

    $pdf->SetTextColor(0, 0, 0);
}

// =============================================================================
// ACTION: LIST - Return JSON list of available patterns
// =============================================================================

if ($action === 'list') {
    ob_end_clean();
    header('Content-Type: application/json');

    // Define standard pattern order
    $standardOrder = ['front', 'back', 'patti', 'sleeve', 'side_panel', 'center_panel'];

    $availablePatterns = [];

    // Add scale calibration first
    $availablePatterns[] = [
        'key' => 'scale',
        'name' => 'Scale Calibration',
        'description' => '10" x 10" calibration box',
        'icon' => 'ruler'
    ];

    // Add patterns in order
    $patternNumber = 1;
    foreach ($standardOrder as $key) {
        if (isset($patterns[$key]) && isset($patterns[$key]['svg_content'])) {
            $patternName = $patterns[$key]['name'] ?? ucfirst($key);
            $availablePatterns[] = [
                'key' => $key,
                'name' => $patternName,
                'description' => 'Pattern piece ' . $patternNumber,
                'icon' => 'file'
            ];
            $patternNumber++;
        }
    }

    // Add any remaining patterns not in standard order
    foreach ($patterns as $key => $pattern) {
        if (!in_array($key, $standardOrder) && isset($pattern['svg_content'])) {
            $patternName = $pattern['name'] ?? ucfirst($key);
            $availablePatterns[] = [
                'key' => $key,
                'name' => $patternName,
                'description' => 'Pattern piece ' . $patternNumber,
                'icon' => 'file'
            ];
            $patternNumber++;
        }
    }

    echo json_encode([
        'success' => true,
        'customer' => $customerName,
        'measurementId' => $measurementId,
        'patterns' => $availablePatterns
    ]);
    exit;
}

// =============================================================================
// ACTION: DOWNLOAD - Generate and download specific pattern PDF
// =============================================================================

if ($patternKey === 'scale') {
    // Generate Scale Calibration PDF
    $scalePageWidth = $scaleBoxSize + 2;
    $scalePageHeight = $scaleBoxSize + 2;

    $pdf = new TCPDF('P', 'in', [$scalePageWidth, $scalePageHeight], true, 'UTF-8', false);
    $pdf->SetCreator('CM-2025 Projector PDF Generator');
    $pdf->SetAuthor($customerName);
    $pdf->SetTitle('Scale Calibration - ' . $customerName);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->AddPage('P', [$scalePageWidth, $scalePageHeight]);

    // Add title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $titleText = 'PROJECTOR SCALE CALIBRATION';
    $titleWidth = $pdf->GetStringWidth($titleText);
    $pdf->Text(($scalePageWidth - $titleWidth) / 2, 0.4, $titleText);

    // Add instructions
    $pdf->SetFont('helvetica', '', 10);
    $instructions = 'Adjust your projector until this box measures exactly 10" x 10"';
    $instrWidth = $pdf->GetStringWidth($instructions);
    $pdf->Text(($scalePageWidth - $instrWidth) / 2, 0.7, $instructions);

    // Draw the scale box
    drawScaleCalibrationBox($pdf, $scaleBoxSize, $scalePageWidth, $scalePageHeight);

    // Add branding
    addMinimalBranding($pdf, $scalePageWidth, $scalePageHeight, 0.3);

    // Output
    $filename = $safeCustomerName . '-Scale_Calibration.pdf';
    ob_end_clean();
    $pdf->Output($filename, 'D');
    exit;
}

// Generate specific pattern PDF
if (!isset($patterns[$patternKey]) || !isset($patterns[$patternKey]['svg_content'])) {
    ob_end_clean();
    http_response_code(404);
    die("ERROR: Pattern '$patternKey' not found.");
}

$pattern = $patterns[$patternKey];
$patternName = $pattern['name'] ?? ucfirst($patternKey);
$svgContent = $pattern['svg_content'];

// Get pattern dimensions
$dims = getSVGDimensions($svgContent, $scale);

// Calculate page size (pattern size + padding)
$pageWidth = $dims['width'] + (2 * $pagePadding);
$pageHeight = $dims['height'] + (2 * $pagePadding) + 0.5;

// Determine orientation based on dimensions
$orientation = ($pageWidth > $pageHeight) ? 'L' : 'P';

// Create PDF
$pdf = new TCPDF($orientation, 'in', [$pageWidth, $pageHeight], true, 'UTF-8', false);
$pdf->SetCreator('CM-2025 Projector PDF Generator');
$pdf->SetAuthor($customerName);
$pdf->SetTitle($patternName . ' Pattern - ' . $customerName);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Add page with custom dimensions
$pdf->AddPage($orientation, [$pageWidth, $pageHeight]);

// Add pattern label
addPatternLabel($pdf, $patternName, $pageWidth, $pageHeight, $pagePadding);

// Render the SVG pattern
$renderX = $pagePadding;
$renderY = $pagePadding + 0.4;
$renderWidth = $dims['width'];
$renderHeight = $dims['height'];

$pdf->ImageSVG('@' . $svgContent, $renderX, $renderY, $renderWidth, $renderHeight, '', '', '', 0, false);

// Add minimal branding
addMinimalBranding($pdf, $pageWidth, $pageHeight, $pagePadding);

// Output
$safePatternName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $patternName);
$filename = $safeCustomerName . '-' . $safePatternName . '.pdf';
ob_end_clean();
$pdf->Output($filename, 'D');
exit;
