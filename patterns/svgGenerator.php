<?php
/**
 * =============================================================================
 * GENERIC PATTERN SVG GENERATOR
 * =============================================================================
 *
 * Universal SVG generator for all pattern types in CM-2025.
 * Outputs all patterns as a ZIP bundle containing separate SVG files.
 *
 * Features:
 * - ZIP bundle containing all patterns as separate SVG files
 * - Clean SVG output with metadata
 * - Customer info and branding embedded
 * - Scalable vector graphics at 1:1 actual size
 * - README manifest with pattern details and measurements
 *
 * Usage:
 * 1. Pattern file stores data in session with standardized format
 * 2. Access: svgGenerator.php?measurement_id=123
 *
 * Parameters:
 * - measurement_id: Measurement ID (required)
 * - type: Pattern type for filename (optional, uses metadata.type)
 *
 * Output: ZIP file containing all pattern SVGs + README.md
 *
 * @author CM-2025
 * @date January 2026
 */

// Start session to access pattern data
session_start();

// =============================================================================
// CONFIGURATION
// =============================================================================

$scale = 25.4; // Pixels per inch (same as PDF generator)

// =============================================================================
// GET PATTERN DATA FROM SESSION
// =============================================================================

$patternData = null;
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
$contactNumber = $metadata['contact_number'] ?? $metadata['mobile_number'] ?? '';

// Sanitize customer name for filename
$safeCustomerName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customerName);
$safeCustomerName = preg_replace('/_+/', '_', $safeCustomerName);
$safeCustomerName = trim($safeCustomerName, '_');

// Date/time for filename
$dateForFilename = date('M_d');
$timeForFilename = date('g_iA');

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Clean and enhance SVG content with metadata
 * Also normalizes dimensions for 1:1 actual size printing
 */
function enhanceSVG($svgContent, $patternName, $customerName, $measurementId, $measurements) {
    global $scale;

    // Normalize SVG dimensions for 1:1 printing
    // Extract viewBox dimensions (these are the true pixel dimensions)
    if (preg_match('/viewBox="([0-9.]+) ([0-9.]+) ([0-9.]+) ([0-9.]+)"/', $svgContent, $vbMatch)) {
        $vbWidth = floatval($vbMatch[3]);
        $vbHeight = floatval($vbMatch[4]);

        // Calculate actual dimensions in inches
        $widthInches = $vbWidth / $scale;
        $heightInches = $vbHeight / $scale;

        // Only replace width/height on the ROOT <svg> element (not inner elements)
        // Handle both numeric values (500) and percentage values (100%)
        // First, try to match consecutive width/height attributes
        $replaced = preg_replace_callback(
            '/(<svg[^>]*)\s+width="[0-9.%]+"\s+height="[0-9.%]+"/',
            function($matches) use ($widthInches, $heightInches) {
                return $matches[1] . ' width="' . number_format($widthInches, 2) . 'in" height="' . number_format($heightInches, 2) . 'in"';
            },
            $svgContent,
            1  // Only replace first match (the root SVG element)
        );

        // If no replacement happened (attributes not consecutive), replace individually
        if ($replaced === $svgContent) {
            // Replace width in root svg only (match <svg...width="X"... up to first >)
            $svgContent = preg_replace_callback(
                '/(<svg\s[^>]*?)width="[0-9.%]+"/',
                function($matches) use ($widthInches) {
                    return $matches[1] . 'width="' . number_format($widthInches, 2) . 'in"';
                },
                $svgContent,
                1
            );
            // Replace height in root svg only
            $svgContent = preg_replace_callback(
                '/(<svg\s[^>]*?)height="[0-9.%]+"/',
                function($matches) use ($heightInches) {
                    return $matches[1] . 'height="' . number_format($heightInches, 2) . 'in"';
                },
                $svgContent,
                1
            );
        } else {
            $svgContent = $replaced;
        }
    }

    // Add XML declaration if not present
    if (strpos($svgContent, '<?xml') === false) {
        $svgContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $svgContent;
    }

    // Add metadata comment at the beginning of SVG
    $metadataComment = sprintf(
        "\n<!-- \n  Pattern: %s\n  Customer: %s\n  Measurement ID: %s\n  Generated: %s\n  Generator: CuttingMaster.in\n-->\n",
        htmlspecialchars($patternName),
        htmlspecialchars($customerName),
        $measurementId,
        date('Y-m-d H:i:s')
    );

    // Insert metadata after XML declaration or at start
    if (preg_match('/(<\?xml[^?]*\?>)/i', $svgContent, $matches)) {
        $svgContent = str_replace($matches[1], $matches[1] . $metadataComment, $svgContent);
    } else {
        $svgContent = $metadataComment . $svgContent;
    }

    // Add title element inside SVG if not present
    if (strpos($svgContent, '<title>') === false) {
        $titleElement = sprintf('<title>%s - %s</title>', htmlspecialchars($patternName), htmlspecialchars($customerName));
        $svgContent = preg_replace('/(<svg[^>]*>)/i', '$1' . "\n  " . $titleElement, $svgContent, 1);
    }

    // Add desc element with measurements
    if (strpos($svgContent, '<desc>') === false && !empty($measurements)) {
        $measurementStr = [];
        foreach ($measurements as $key => $value) {
            if ($value !== null && $value !== '') {
                $measurementStr[] = ucfirst($key) . ': ' . $value . '"';
            }
        }
        $descElement = '<desc>' . htmlspecialchars(implode(', ', $measurementStr)) . '</desc>';
        $svgContent = preg_replace('/(<title>[^<]*<\/title>)/i', '$1' . "\n  " . $descElement, $svgContent, 1);
    }

    return $svgContent;
}

/**
 * Get SVG dimensions from content
 */
function getSVGDimensions($svgContent, $scale) {
    $width = 0;
    $height = 0;

    // Extract from viewBox
    if (preg_match('/viewBox="[0-9.]+ [0-9.]+ ([0-9.]+) ([0-9.]+)"/', $svgContent, $viewBoxMatch)) {
        $width = floatval($viewBoxMatch[1]) / $scale;
        $height = floatval($viewBoxMatch[2]) / $scale;
    }
    // Fallback: width/height attributes
    elseif (preg_match('/width="([0-9.]+)"/', $svgContent, $widthMatch) &&
            preg_match('/height="([0-9.]+)"/', $svgContent, $heightMatch)) {
        $viewScale = 2.0;
        $width = floatval($widthMatch[1]) / ($scale * $viewScale);
        $height = floatval($heightMatch[1]) / ($scale * $viewScale);
    }

    return [
        'width' => $width,
        'height' => $height,
        'widthInches' => number_format($width, 1),
        'heightInches' => number_format($height, 1)
    ];
}

// =============================================================================
// GENERATE ZIP BUNDLE WITH ALL PATTERNS
// =============================================================================

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    http_response_code(500);
    die("ERROR: ZIP extension not available on server.");
}

// Create temporary file for ZIP
$zipFilename = sprintf('%s-%s-Patterns-%s-%s.zip', $safeCustomerName, $measurementId, $dateForFilename, $timeForFilename);
$tempZipPath = sys_get_temp_dir() . '/' . uniqid('pattern_svg_') . '.zip';

$zip = new ZipArchive();
if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    die("ERROR: Cannot create ZIP file.");
}

// Define standard pattern order
$standardOrder = ['front', 'back', 'patti', 'sleeve', 'side_panel', 'center_panel'];

// Sort patterns by order
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

// Add each pattern to ZIP
$patternIndex = 1;
$manifestContent = "# Pattern SVG Bundle\n";
$manifestContent .= "# Generated: " . date('Y-m-d H:i:s') . "\n";
$manifestContent .= "# Customer: $customerName\n";
$manifestContent .= "# Measurement ID: $measurementId\n";
$manifestContent .= "# Generator: CuttingMaster.in\n\n";
$manifestContent .= "## Patterns Included:\n\n";

foreach ($sortedPatterns as $key => $pattern) {
    if (isset($pattern['svg_content'])) {
        $patternName = $pattern['name'] ?? ucfirst($key);
        $svgContent = enhanceSVG($pattern['svg_content'], $patternName, $customerName, $measurementId, $measurements);

        // Generate individual filename (numbered for ordering)
        $svgFilename = sprintf('%02d-%s.svg', $patternIndex, ucfirst($key));

        // Add to ZIP
        $zip->addFromString($svgFilename, $svgContent);

        // Get dimensions for manifest
        $dims = getSVGDimensions($pattern['svg_content'], $scale);

        // Add to manifest
        $manifestContent .= sprintf("%d. **%s** (%s)\n", $patternIndex, $patternName, $svgFilename);
        $manifestContent .= sprintf("   - Dimensions: %s\" x %s\"\n", $dims['widthInches'], $dims['heightInches']);
        $manifestContent .= "\n";

        $patternIndex++;
    }
}

// Add measurements to manifest
$manifestContent .= "## Measurements:\n\n";
foreach ($measurements as $key => $value) {
    if ($value !== null && $value !== '') {
        $label = ucfirst(preg_replace('/([A-Z])/', ' $1', $key));
        $manifestContent .= sprintf("- %s: %s\"\n", trim($label), number_format((float)$value, 2));
    }
}

$manifestContent .= "\n---\n";
$manifestContent .= "Generated by CuttingMaster.in Pattern Generator\n";

// Add manifest to ZIP
$zip->addFromString('README.md', $manifestContent);

// Close ZIP
$zip->close();

// Output ZIP
if (!file_exists($tempZipPath)) {
    http_response_code(500);
    die("ERROR: Failed to create ZIP file.");
}

$zipSize = filesize($tempZipPath);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . $zipSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($tempZipPath);

// Clean up temp file
unlink($tempZipPath);

exit;
