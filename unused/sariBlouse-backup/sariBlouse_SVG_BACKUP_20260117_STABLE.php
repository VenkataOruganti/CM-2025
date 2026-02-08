<?php
/**
 * Saree Blouse Pattern - SVG Export Generator
 *
 * This script generates individual SVG files for each pattern piece
 * and immediately downloads them as a ZIP archive.
 *
 * Pattern pieces included:
 * - Front pattern
 * - Back pattern
 * - Patti pattern (border)
 * - Sleeve pattern
 *
 * Usage:
 * 1. First, generate pattern in browser using sariBlouse.php
 * 2. Then access: sariBlouse_SVG.php?measurement_id=123
 *    (or just sariBlouse_SVG.php to use the latest pattern from session)
 * 3. ZIP file with all 4 SVG files will download automatically
 *
 * Parameters:
 * - measurement_id: Measurement ID (optional - uses latest pattern if not specified)
 *
 * Output:
 * - ZIP file containing 4 SVG files + README.txt
 * - Filename format: CustomerName_123_patterns_2026-01-16_12-30-45.zip
 *
 * @author CM-2025
 * @date January 16, 2026
 */

// Start session to access pattern data
session_start();

// =============================================================================
// CONFIGURATION
// =============================================================================

$exportDir = __DIR__ . '/svg_exports';

// Create export directory if it doesn't exist
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

// =============================================================================
// GET PATTERN DATA FROM SESSION
// =============================================================================

$patternData = null;
$cacheKey = null;

// Try to get pattern from URL parameters
if (isset($_GET['measurement_id'])) {
    $measurementId = intval($_GET['measurement_id']);
    $cacheKey = "pattern_" . $measurementId;

    if (isset($_SESSION[$cacheKey])) {
        $patternData = $_SESSION[$cacheKey]['data'];
    }
}

// Fallback: try to get latest pattern from session
if ($patternData === null && isset($_SESSION['latest_pattern'])) {
    $cacheKey = $_SESSION['latest_pattern'];
    if (isset($_SESSION[$cacheKey])) {
        $patternData = $_SESSION[$cacheKey]['data'];
    }
}

// Error if no pattern data found
if ($patternData === null) {
    http_response_code(400);
    die("ERROR: No pattern data found in session. Please generate a pattern first using sariBlouse.php");
}

// =============================================================================
// EXTRACT PATTERN INFORMATION
// =============================================================================

$customerName = $patternData['metadata']['customer_name'] ?? 'Customer';
$customerId = $patternData['metadata']['customer_id'] ?? 'unknown';
$measurementId = $patternData['metadata']['measurement_id'] ?? 'unknown';
$timestamp = date('Y-m-d_H-i-s');

// Sanitize customer name for filename
$safeCustomerName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $customerName);

// =============================================================================
// SVG GENERATION FUNCTIONS
// =============================================================================

/**
 * Clean and prepare SVG content for standalone file
 */
function prepareSVGForExport($svgContent, $title, $description = '') {
    // Add XML declaration if not present
    if (strpos($svgContent, '<?xml') === false) {
        $svgContent = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n" . $svgContent;
    }

    // Add title and description metadata
    $svgContent = preg_replace(
        '/<svg([^>]*)>/',
        '<svg$1>' . "\n" .
        '  <title>' . htmlspecialchars($title) . '</title>' . "\n" .
        '  <desc>' . htmlspecialchars($description) . '</desc>',
        $svgContent,
        1
    );

    return $svgContent;
}

/**
 * Save SVG content to file
 */
function saveSVGFile($filename, $svgContent) {
    global $exportDir;
    $filepath = $exportDir . '/' . $filename;

    $result = file_put_contents($filepath, $svgContent);

    if ($result === false) {
        return ['success' => false, 'error' => "Failed to write file: $filename"];
    }

    return [
        'success' => true,
        'filename' => $filename,
        'filepath' => $filepath,
        'size' => filesize($filepath)
    ];
}

// =============================================================================
// GENERATE INDIVIDUAL SVG FILES
// =============================================================================

$generatedFiles = [];
$errors = [];

// 1. FRONT PATTERN
if (isset($patternData['front']['svg_content'])) {
    $frontSVG = prepareSVGForExport(
        $patternData['front']['svg_content'],
        "Blouse Front Pattern - $customerName",
        "Saree blouse front pattern piece for $customerName (Measurement ID: $measurementId)"
    );

    $filename = sprintf('%s_%s_front_%s.svg', $safeCustomerName, $measurementId, $timestamp);
    $result = saveSVGFile($filename, $frontSVG);

    if ($result['success']) {
        $generatedFiles['front'] = $result;
    } else {
        $errors[] = $result['error'];
    }
}

// 2. BACK PATTERN
if (isset($patternData['back']['svg_content'])) {
    $backSVG = prepareSVGForExport(
        $patternData['back']['svg_content'],
        "Blouse Back Pattern - $customerName",
        "Saree blouse back pattern piece for $customerName (Measurement ID: $measurementId)"
    );

    $filename = sprintf('%s_%s_back_%s.svg', $safeCustomerName, $measurementId, $timestamp);
    $result = saveSVGFile($filename, $backSVG);

    if ($result['success']) {
        $generatedFiles['back'] = $result;
    } else {
        $errors[] = $result['error'];
    }
}

// 3. PATTI PATTERN (Border)
if (isset($patternData['patti']['svg_content'])) {
    $pattiSVG = prepareSVGForExport(
        $patternData['patti']['svg_content'],
        "Patti Border Pattern - $customerName",
        "Saree blouse patti (border) pattern piece for $customerName (Measurement ID: $measurementId)"
    );

    $filename = sprintf('%s_%s_patti_%s.svg', $safeCustomerName, $measurementId, $timestamp);
    $result = saveSVGFile($filename, $pattiSVG);

    if ($result['success']) {
        $generatedFiles['patti'] = $result;
    } else {
        $errors[] = $result['error'];
    }
}

// 4. SLEEVE PATTERN
if (isset($patternData['sleeve']['svg_content'])) {
    $sleeveSVG = prepareSVGForExport(
        $patternData['sleeve']['svg_content'],
        "Sleeve Pattern - $customerName",
        "Saree blouse sleeve pattern piece for $customerName (Measurement ID: $measurementId)"
    );

    $filename = sprintf('%s_%s_sleeve_%s.svg', $safeCustomerName, $measurementId, $timestamp);
    $result = saveSVGFile($filename, $sleeveSVG);

    if ($result['success']) {
        $generatedFiles['sleeve'] = $result;
    } else {
        $errors[] = $result['error'];
    }
}

// =============================================================================
// CREATE ZIP FILE AND DOWNLOAD (ALWAYS)
// =============================================================================

if (count($generatedFiles) > 0) {
    $zipFilename = sprintf('%s_%s_patterns_%s.zip', $safeCustomerName, $measurementId, $timestamp);
    $zipPath = $exportDir . '/' . $zipFilename;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Add all generated SVG files to ZIP
        foreach ($generatedFiles as $pattern => $fileInfo) {
            $zip->addFile($fileInfo['filepath'], $fileInfo['filename']);
        }

        // Add a README.txt file
        $readme = "Saree Blouse Pattern Files\n";
        $readme .= "=========================\n\n";
        $readme .= "Customer: $customerName\n";
        $readme .= "Customer ID: $customerId\n";
        $readme .= "Measurement ID: $measurementId\n";
        $readme .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $readme .= "Pattern Pieces:\n";
        foreach ($generatedFiles as $pattern => $fileInfo) {
            $readme .= "- " . ucfirst($pattern) . ": " . $fileInfo['filename'] . " (" . number_format($fileInfo['size']) . " bytes)\n";
        }
        $readme .= "\nUsage:\n";
        $readme .= "1. Open SVG files in a vector graphics editor (Adobe Illustrator, Inkscape, etc.)\n";
        $readme .= "2. Print at 100% scale (do not scale to fit)\n";
        $readme .= "3. Verify scale using the 2\"×2\" scale box on the front pattern\n";
        $readme .= "4. Cut along red dashed lines (cutting lines with seam allowance)\n";
        $readme .= "5. Fold along blue dashed lines (fold lines)\n";

        $zip->addFromString('README.txt', $readme);
        $zip->close();

        // Send ZIP file for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);

        // Clean up: delete individual SVG files and ZIP after download
        foreach ($generatedFiles as $fileInfo) {
            @unlink($fileInfo['filepath']);
        }
        @unlink($zipPath);

        exit;
    } else {
        // If ZIP creation fails, show error and exit
        http_response_code(500);
        die("ERROR: Failed to create ZIP file. Please check directory permissions.");
    }
} else {
    // No files generated
    http_response_code(400);
    die("ERROR: No SVG files were generated. Pattern data may be incomplete.");
}

// If we reach here, something went wrong (should have exited above)
http_response_code(500);
die("ERROR: Unexpected error during SVG export.");
