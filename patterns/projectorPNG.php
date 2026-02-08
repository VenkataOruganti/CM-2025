<?php
/**
 * =============================================================================
 * PROJECTOR PNG GENERATOR - INDIVIDUAL FILES WITH METADATA
 * =============================================================================
 *
 * Generates individual PNG files optimized for projector-based pattern cutting.
 * Uses ImageMagick or GD library to convert SVG to PNG at high resolution.
 * Includes customer info, measurements, watermark, and logo.
 *
 * Modes:
 * 1. ?action=list - Returns JSON list of available patterns
 * 2. ?action=download&pattern=front - Downloads specific pattern PNG
 * 3. No action - Downloads scale calibration PNG by default
 *
 * @author CM-2025
 * @date January 2026
 */

// Suppress PHP 8.5 deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED);

// Start output buffering to prevent any output before image generation
ob_start();

// Start session to access pattern data
session_start();

// =============================================================================
// CONFIGURATION
// =============================================================================

$scale = 25.4; // Pixels per inch (standard conversion for SVG)
$pngDPI = 96; // Output PNG resolution (150 DPI for good quality projector display)
$pngScale = $pngDPI / 25.4; // Conversion factor for PNG output

// Scale box dimensions (for projector calibration)
$scaleBoxSize = 10.0; // 10 inches x 10 inches

// Page padding (small padding around patterns for clean edges)
$pagePadding = 0.5; // 0.5 inch padding

// Header height for metadata (in inches) - minimal since info goes on pattern background
$headerHeightInches = 0;

// Logo path
$logoPath = __DIR__ . '/../assets/images/cm-logo.png';

// =============================================================================
// GET PARAMETERS
// =============================================================================

$action = $_GET['action'] ?? 'download';
$patternKey = $_GET['pattern'] ?? 'scale';
$measurementId = isset($_GET['measurement_id']) ? intval($_GET['measurement_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

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
$contactNumber = $metadata['contact_number'] ?? $metadata['mobile_number'] ?? '';

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
 * Draw scaled text using GD's built-in fonts with scaling
 * This creates larger text by drawing at a higher resolution and scaling
 */
function drawScaledText($image, $fontSize, $x, $y, $text, $color, $scale = 2) {
    // For scale > 1, we create a temporary image and scale it down
    if ($scale > 1) {
        // Calculate text dimensions at original size
        $fontWidth = imagefontwidth($fontSize);
        $fontHeight = imagefontheight($fontSize);
        $textWidth = $fontWidth * strlen($text);
        $textHeight = $fontHeight;

        // Create temporary image at scaled size
        $tempWidth = $textWidth * $scale;
        $tempHeight = $textHeight * $scale;

        $temp = imagecreatetruecolor($tempWidth, $tempHeight);
        $white = imagecolorallocate($temp, 255, 255, 255);
        $textColor = imagecolorallocate($temp, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF);

        imagefill($temp, 0, 0, $white);
        imagecolortransparent($temp, $white);

        // Draw text multiple times for each scaled position (poor man's scaling)
        for ($sx = 0; $sx < $scale; $sx++) {
            for ($sy = 0; $sy < $scale; $sy++) {
                imagestring($temp, $fontSize, $sx, $sy, $text, $textColor);
            }
        }

        // Scale down and copy to main image (this gives slightly larger text effect)
        imagecopyresampled($image, $temp, $x, $y, 0, 0, $textWidth, $textHeight, $tempWidth / $scale, $tempHeight / $scale);
        imagedestroy($temp);
    } else {
        imagestring($image, $fontSize, $x, $y, $text, $color);
    }
}

/**
 * Add logo to top-right corner of image (like PDF generator)
 * Uses larger fonts (20px) for URL text
 */
function addLogo($image, $widthPx, $dpi, $logoPath) {
    if (!file_exists($logoPath)) {
        return $image;
    }

    $logo = @imagecreatefrompng($logoPath);
    if (!$logo) {
        return $image;
    }

    $origLogoW = imagesx($logo);
    $origLogoH = imagesy($logo);

    // Scale logo to 2.5" tall (matching PDF style)
    $maxLogoHeight = round(2.5 * $dpi);
    $logoScale = $maxLogoHeight / $origLogoH;
    $logoW = round($origLogoW * $logoScale);
    $logoH = round($origLogoH * $logoScale);

    // Position logo in top-right corner with padding
    $padding = round(0.4 * $dpi);
    $logoX = $widthPx - $logoW - $padding;
    $logoY = $padding;

    // Add semi-transparent white background behind logo for visibility
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, $logoX - 15, $logoY - 15, $logoX + $logoW + 15, $logoY + $logoH + 50, $white);

    imagecopyresampled($image, $logo, $logoX, $logoY, 0, 0, $logoW, $logoH, $origLogoW, $origLogoH);
    imagedestroy($logo);

    // Add website URL below logo using large text (20px)
    $brandColor = imagecolorallocate($image, 0, 102, 153);
    $urlText = "www.CuttingMaster.in";

    // Try TrueType font first
    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
        '/Library/Fonts/Arial.ttf',
        'C:/Windows/Fonts/arial.ttf',
        __DIR__ . '/../assets/fonts/DejaVuSans.ttf',
    ];

    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }

    $urlY = $logoY + $logoH + 5;

    if ($fontFile && function_exists('imagettftext')) {
        $fontSize = 16;
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $urlText);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $urlX = $logoX + $logoW - $textWidth;
        imagettftext($image, $fontSize, 0, $urlX, $urlY + $fontSize, $brandColor, $fontFile, $urlText);
        imagettftext($image, $fontSize, 0, $urlX + 1, $urlY + $fontSize, $brandColor, $fontFile, $urlText); // Bold
    } else {
        // Fallback with larger effect
        $urlWidth = imagefontwidth(5) * strlen($urlText);
        $urlX = $logoX + $logoW - $urlWidth - 20;
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 2; $j++) {
                imagestring($image, 5, $urlX + $i, $urlY + $j, $urlText, $brandColor);
            }
        }
    }

    return $image;
}

/**
 * Draw large text using TrueType font or fallback to scaled bitmap
 * @param resource $image GD image resource
 * @param int $fontSize Font size in points (e.g., 20, 16, 14)
 * @param int $x X position
 * @param int $y Y position
 * @param string $text Text to draw
 * @param int $color Color allocated with imagecolorallocate
 * @param bool $bold Whether to simulate bold by drawing twice
 */
function drawLargeText($image, $fontSize, $x, $y, $text, $color, $bold = false) {
    // Try to find a TrueType font
    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/TTF/DejaVuSans.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
        '/System/Library/Fonts/SFNSText.ttf',
        '/Library/Fonts/Arial.ttf',
        'C:/Windows/Fonts/arial.ttf',
        __DIR__ . '/../assets/fonts/DejaVuSans.ttf',
    ];

    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }

    if ($fontFile && function_exists('imagettftext')) {
        // Use TrueType font for crisp large text
        // Note: imagettftext y is baseline, not top
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textHeight = abs($bbox[5] - $bbox[1]);
        $baselineY = $y + $textHeight;

        imagettftext($image, $fontSize, 0, $x, $baselineY, $color, $fontFile, $text);
        if ($bold) {
            imagettftext($image, $fontSize, 0, $x + 1, $baselineY, $color, $fontFile, $text);
        }
    } else {
        // Fallback: Use largest built-in font (5) and draw multiple times for "larger" effect
        // Built-in font 5 is about 9x15 pixels per character
        // To simulate larger text, we draw it multiple times with offsets

        $scale = max(1, round($fontSize / 10)); // How much to "embiggen"

        for ($dx = 0; $dx < $scale; $dx++) {
            for ($dy = 0; $dy < $scale; $dy++) {
                imagestring($image, 5, $x + $dx, $y + $dy, $text, $color);
            }
        }
        if ($bold) {
            imagestring($image, 5, $x + $scale, $y, $text, $color);
            imagestring($image, 5, $x, $y + $scale, $text, $color);
        }
    }
}

/**
 * Add pattern info overlay on image (like PDF generator style)
 * Info appears on the pattern background, not in a separate header
 * Uses 20px font size for better readability
 */
function addPatternInfo($image, $widthPx, $heightPx, $dpi, $patternName, $customerName, $measurementId, $dims) {
    // Colors
    $black = imagecolorallocate($image, 0, 0, 0);
    $darkGray = imagecolorallocate($image, 80, 80, 80);
    $gray = imagecolorallocate($image, 120, 120, 120);

    $padding = round(0.4 * $dpi);
    $lineHeight = round(0.35 * $dpi); // Increased for larger fonts

    // Top-left info block (pattern name, customer, etc.)
    $y = $padding;

    // Pattern name (large, bold - 24px)
    $patternTitle = strtoupper($patternName);
    drawLargeText($image, 24, $padding, $y, $patternTitle, $black, true);
    $y += $lineHeight + 10;

    // Customer name (20px)
    $customerText = "Customer: " . $customerName;
    drawLargeText($image, 20, $padding, $y, $customerText, $darkGray, false);
    $y += $lineHeight;

    // Pattern ID (20px)
    $idText = "Pattern ID: " . $measurementId;
    drawLargeText($image, 20, $padding, $y, $idText, $darkGray, false);
    $y += $lineHeight;

    // Date (18px)
    $dateText = "Generated: " . date('d-M-Y');
    drawLargeText($image, 18, $padding, $y, $dateText, $gray, false);
    $y += $lineHeight + 15;

    // Pattern includes section header (20px bold)
    drawLargeText($image, 20, $padding, $y, "PATTERN INCLUDES:", $black, true);
    $y += $lineHeight;

    // Ease info (18px)
    drawLargeText($image, 18, $padding, $y, "Ease: 1.5\" (Standard fitting)", $darkGray, false);
    $y += $lineHeight - 5;

    // Seam allowance (18px)
    drawLargeText($image, 18, $padding, $y, "Seam Allowance: 0.5\" (All edges)", $darkGray, false);
    $y += $lineHeight - 5;

    // Scale (18px)
    drawLargeText($image, 18, $padding, $y, "Scale: 1:1 (Actual Size)", $gray, false);

    // Bottom-left: Dimensions (18px)
    $dimsText = sprintf("Pattern Size: %.1f\" x %.1f\"", $dims['width'], $dims['height']);
    $bottomY = $heightPx - $padding - 30;
    drawLargeText($image, 18, $padding, $bottomY, $dimsText, $gray, false);

    return $image;
}

/**
 * Add watermark to image (PDF-style: with pattern name and customer)
 * Uses larger fonts for better visibility
 */
function addWatermark($image, $widthPx, $heightPx, $patternName = '', $customerName = '') {
    // Semi-transparent gray for watermark
    $watermarkColor = imagecolorallocatealpha($image, 180, 180, 180, 90);

    $centerX = $widthPx / 2;
    $centerY = $heightPx / 2;

    // Try to find a TrueType font for large watermark text
    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
        '/Library/Fonts/Arial.ttf',
        'C:/Windows/Fonts/arial.ttf',
        __DIR__ . '/../assets/fonts/DejaVuSans.ttf',
    ];

    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }

    // Primary watermark: Pattern name (large, centered) - 48pt font
    if (!empty($patternName)) {
        $text1 = strtoupper($patternName);

        if ($fontFile && function_exists('imagettftext')) {
            $fontSize = 48;
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $text1);
            $textWidth = abs($bbox[2] - $bbox[0]);
            $x = $centerX - $textWidth / 2 - 50;
            $y = $centerY - 20;
            imagettftext($image, $fontSize, 0, $x, $y, $watermarkColor, $fontFile, $text1);
        } else {
            // Fallback: draw multiple times for larger effect
            $textWidth = imagefontwidth(5) * strlen($text1);
            for ($i = 0; $i < 3; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    imagestring($image, 5, $centerX - $textWidth / 2 - 50 + $i, $centerY - 30 + $j, $text1, $watermarkColor);
                }
            }
        }
    }

    // Secondary watermark: Customer name - 36pt font
    if (!empty($customerName)) {
        $text2 = $customerName;

        if ($fontFile && function_exists('imagettftext')) {
            $fontSize = 36;
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $text2);
            $textWidth = abs($bbox[2] - $bbox[0]);
            $x = $centerX - $textWidth / 2;
            $y = $centerY + 40;
            imagettftext($image, $fontSize, 0, $x, $y, $watermarkColor, $fontFile, $text2);
        } else {
            $textWidth = imagefontwidth(5) * strlen($text2);
            for ($i = 0; $i < 2; $i++) {
                for ($j = 0; $j < 2; $j++) {
                    imagestring($image, 5, $centerX - $textWidth / 2 + $i, $centerY + 10 + $j, $text2, $watermarkColor);
                }
            }
        }
    }

    // Repeating "CuttingMaster.in" watermarks across image - 20pt font
    $brandText = "CuttingMaster.in";
    $brandColor = imagecolorallocatealpha($image, 210, 210, 210, 100);
    $spacing = 600; // Pixels between watermarks

    for ($wy = 250; $wy < $heightPx - 150; $wy += $spacing) {
        for ($wx = -150; $wx < $widthPx; $wx += $spacing) {
            $offsetX = ($wy % 400) / 2;

            if ($fontFile && function_exists('imagettftext')) {
                imagettftext($image, 20, 0, $wx + $offsetX, $wy, $brandColor, $fontFile, $brandText);
            } else {
                imagestring($image, 5, $wx + $offsetX, $wy, $brandText, $brandColor);
            }
        }
    }

    return $image;
}

/**
 * Add footer to image (minimal, PDF-style)
 * Uses larger fonts (16px) for better readability
 */
function addFooter($image, $widthPx, $heightPx, $dpi, $patternName, $dims) {
    $footerHeight = round(0.5 * $dpi); // Increased for larger text
    $footerY = $heightPx - $footerHeight;

    // Colors
    $lightGray = imagecolorallocate($image, 248, 248, 248);
    $darkGray = imagecolorallocate($image, 100, 100, 100);
    $borderGray = imagecolorallocate($image, 200, 200, 200);

    // Fill footer background
    imagefilledrectangle($image, 0, $footerY, $widthPx - 1, $heightPx - 1, $lightGray);

    // Draw top border
    imagesetthickness($image, 2);
    imageline($image, 0, $footerY, $widthPx - 1, $footerY, $borderGray);
    imagesetthickness($image, 1);

    $padding = round(0.3 * $dpi);

    // Footer text using large text helper
    $footerText = sprintf("%s  |  %.1f\" x %.1f\"  |  Scale 1:1", $patternName, $dims['width'], $dims['height']);
    $rightText = sprintf("%d DPI  |  CuttingMaster.in", $dpi);

    // Try TrueType font first
    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
        '/Library/Fonts/Arial.ttf',
        'C:/Windows/Fonts/arial.ttf',
        __DIR__ . '/../assets/fonts/DejaVuSans.ttf',
    ];

    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }

    $textY = $footerY + $footerHeight / 2;

    if ($fontFile && function_exists('imagettftext')) {
        $fontSize = 14;

        // Left text
        imagettftext($image, $fontSize, 0, $padding, $textY + $fontSize / 2, $darkGray, $fontFile, $footerText);

        // Right text
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $rightText);
        $rightWidth = abs($bbox[2] - $bbox[0]);
        imagettftext($image, $fontSize, 0, $widthPx - $rightWidth - $padding, $textY + $fontSize / 2, $darkGray, $fontFile, $rightText);
    } else {
        // Fallback with larger effect
        $textY = $footerY + ($footerHeight - imagefontheight(5)) / 2;

        // Draw text multiple times for "larger" effect
        for ($i = 0; $i < 2; $i++) {
            imagestring($image, 5, $padding + $i, $textY, $footerText, $darkGray);
        }

        $rightWidth = imagefontwidth(5) * strlen($rightText);
        for ($i = 0; $i < 2; $i++) {
            imagestring($image, 5, $widthPx - $rightWidth - $padding + $i, $textY, $rightText, $darkGray);
        }
    }

    return $image;
}

/**
 * Convert SVG to PNG with metadata using Imagick or GD
 * Uses PDF-style layout: info on pattern background, logo in corner, watermark
 */
function svgToPngWithMetadata($svgContent, $widthInches, $heightInches, $dpi, $headerHeightInches, $customerName, $patternName, $measurementId, $measurements, $logoPath, $contactNumber, $dims) {
    // No separate header - info goes directly on pattern background (PDF style)
    $footerHeightPx = round(0.4 * $dpi);
    $patternWidthPx = round($widthInches * $dpi);
    $patternHeightPx = round($heightInches * $dpi);

    $totalWidthPx = $patternWidthPx;
    $totalHeightPx = $patternHeightPx + $footerHeightPx;

    // Create main image with white background
    $image = imagecreatetruecolor($totalWidthPx, $totalHeightPx);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);

    // Try Imagick for SVG rendering
    $patternImage = null;
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick();
            $imagick->setResolution($dpi, $dpi);
            $imagick->readImageBlob($svgContent);
            $imagick->setImageFormat('png');
            $imagick->resizeImage($patternWidthPx, $patternHeightPx, Imagick::FILTER_LANCZOS, 1);
            $imagick->setImageBackgroundColor('white');
            $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

            // Convert Imagick to GD
            $pngBlob = $imagick->getImageBlob();
            $patternImage = imagecreatefromstring($pngBlob);

            $imagick->clear();
            $imagick->destroy();
        } catch (Exception $e) {
            $patternImage = null;
        }
    }

    // If Imagick failed or not available, create placeholder
    if (!$patternImage) {
        $patternImage = imagecreatetruecolor($patternWidthPx, $patternHeightPx);
        $white = imagecolorallocate($patternImage, 255, 255, 255);
        $black = imagecolorallocate($patternImage, 0, 0, 0);
        $gray = imagecolorallocate($patternImage, 128, 128, 128);

        imagefill($patternImage, 0, 0, $white);
        imagerectangle($patternImage, 0, 0, $patternWidthPx - 1, $patternHeightPx - 1, $black);

        // Placeholder text
        $text1 = "SVG Pattern Area";
        $text2 = "Imagick extension required for full SVG rendering";
        $text3 = sprintf("Pattern: %s (%.1f\" x %.1f\")", $patternName, $dims['width'], $dims['height']);

        $fontSize = 5;
        $centerY = $patternHeightPx / 2;

        $text1Width = imagefontwidth($fontSize) * strlen($text1);
        imagestring($patternImage, $fontSize, ($patternWidthPx - $text1Width) / 2, $centerY - 30, $text1, $black);

        $text2Width = imagefontwidth(3) * strlen($text2);
        imagestring($patternImage, 3, ($patternWidthPx - $text2Width) / 2, $centerY, $text2, $gray);

        $text3Width = imagefontwidth(3) * strlen($text3);
        imagestring($patternImage, 3, ($patternWidthPx - $text3Width) / 2, $centerY + 20, $text3, $gray);
    }

    // Copy pattern image to main image (at top, no header offset)
    imagecopy($image, $patternImage, 0, 0, 0, 0, $patternWidthPx, $patternHeightPx);
    imagedestroy($patternImage);

    // Add watermark (PDF style - with pattern name and customer name)
    $image = addWatermark($image, $totalWidthPx, $totalHeightPx, $patternName, $customerName);

    // Add logo in top-right corner (like PDF)
    $image = addLogo($image, $totalWidthPx, $dpi, $logoPath);

    // Add pattern info overlay (top-left, like PDF style)
    $image = addPatternInfo($image, $totalWidthPx, $totalHeightPx, $dpi, $patternName, $customerName, $measurementId, $dims);

    // Add footer
    $image = addFooter($image, $totalWidthPx, $totalHeightPx, $dpi, $patternName, $dims);

    // Capture PNG output
    ob_start();
    imagepng($image, null, 9); // Max compression
    $pngData = ob_get_clean();
    imagedestroy($image);

    return $pngData;
}

/**
 * Generate scale calibration PNG with metadata and larger fonts
 */
function generateScaleCalibrationPng($boxSizeInches, $dpi, $customerName, $measurementId, $logoPath) {
    $paddingInches = 1.0;
    $headerHeightInches = 2.0; // Increased for larger fonts
    $footerHeightInches = 0.5; // Increased for larger fonts

    $totalWidthInches = $boxSizeInches + (2 * $paddingInches);
    $boxAreaHeightInches = $boxSizeInches + (2 * $paddingInches);
    $totalHeightInches = $headerHeightInches + $boxAreaHeightInches + $footerHeightInches;

    $widthPx = round($totalWidthInches * $dpi);
    $heightPx = round($totalHeightInches * $dpi);
    $headerHeightPx = round($headerHeightInches * $dpi);
    $boxSizePx = round($boxSizeInches * $dpi);
    $paddingPx = round($paddingInches * $dpi);

    // Create image
    $image = imagecreatetruecolor($widthPx, $heightPx);

    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $darkGray = imagecolorallocate($image, 60, 60, 60);
    $gray = imagecolorallocate($image, 100, 100, 100);
    $lightGray = imagecolorallocate($image, 200, 200, 200);
    $brandColor = imagecolorallocate($image, 102, 126, 234);

    // Fill white background
    imagefill($image, 0, 0, $white);

    // Header section with larger fonts
    $headerPadding = round(0.25 * $dpi);
    $lineHeight = round(0.35 * $dpi);
    $y = $headerPadding;

    // Brand name (font 5, bold effect)
    imagestring($image, 5, $headerPadding, $y, "CuttingMaster.in", $brandColor);
    imagestring($image, 5, $headerPadding + 1, $y, "CuttingMaster.in", $brandColor);
    $y += $lineHeight;

    // Title (font 5, bold effect)
    imagestring($image, 5, $headerPadding, $y, "PROJECTOR SCALE CALIBRATION", $black);
    imagestring($image, 5, $headerPadding + 1, $y, "PROJECTOR SCALE CALIBRATION", $black);
    imagestring($image, 5, $headerPadding, $y + 1, "PROJECTOR SCALE CALIBRATION", $black);
    $y += $lineHeight;

    // Customer & meta info (font 5)
    $metaText = "Customer: " . $customerName . "  |  ID: " . $measurementId . "  |  " . date('d-M-Y');
    imagestring($image, 5, $headerPadding, $y, $metaText, $darkGray);
    $y += $lineHeight - 5;

    // Instructions (font 4)
    imagestring($image, 4, $headerPadding, $y, "Adjust your projector until the box measures exactly 10\" x 10\"", $gray);

    // Draw header border (thicker)
    imagesetthickness($image, 3);
    imageline($image, 0, $headerHeightPx - 2, $widthPx - 1, $headerHeightPx - 2, $lightGray);
    imagesetthickness($image, 1);

    // Try to load logo
    if (file_exists($logoPath)) {
        $logo = @imagecreatefrompng($logoPath);
        if ($logo) {
            $origLogoW = imagesx($logo);
            $origLogoH = imagesy($logo);
            $maxLogoHeight = round(1.5 * $dpi); // Larger logo
            $logoScale = $maxLogoHeight / $origLogoH;
            $logoW = round($origLogoW * $logoScale);
            $logoH = round($origLogoH * $logoScale);
            $logoX = $widthPx - $logoW - $headerPadding;
            $logoY = $headerPadding;
            imagecopyresampled($image, $logo, $logoX, $logoY, 0, 0, $logoW, $logoH, $origLogoW, $origLogoH);
            imagedestroy($logo);
        }
    }

    // Calculate box position (centered in the area below header)
    $boxAreaStartY = $headerHeightPx;
    $boxX = ($widthPx - $boxSizePx) / 2;
    $boxY = $boxAreaStartY + ($boxAreaHeightInches * $dpi - $boxSizePx) / 2;

    // Draw black filled box
    imagefilledrectangle($image, $boxX, $boxY, $boxX + $boxSizePx, $boxY + $boxSizePx, $black);

    // Draw white grid lines inside the box (1" intervals)
    $gridSpacingPx = round($dpi); // 1 inch
    imagesetthickness($image, 1);
    for ($i = 1; $i < $boxSizeInches; $i++) {
        $offset = $i * $gridSpacingPx;
        // Vertical lines
        imageline($image, $boxX + $offset, $boxY + 5, $boxX + $offset, $boxY + $boxSizePx - 5, $white);
        // Horizontal lines
        imageline($image, $boxX + 5, $boxY + $offset, $boxX + $boxSizePx - 5, $boxY + $offset, $white);
    }

    // Draw white border inside
    imagerectangle($image, $boxX + 5, $boxY + 5, $boxX + $boxSizePx - 5, $boxY + $boxSizePx - 5, $white);

    // Add measurement labels below the box (font 4 for readability)
    for ($i = 0; $i <= 10; $i += 2) {
        $label = $i . '"';
        $labelX = $boxX + ($i * $gridSpacingPx) - (imagefontwidth(4) * strlen($label) / 2);
        imagestring($image, 4, $labelX, $boxY + $boxSizePx + 15, $label, $black);
    }

    // Add "10 inches" labels (font 5 for visibility)
    $sizeLabel = "10 inches";
    $sizeLabelWidth = imagefontwidth(5) * strlen($sizeLabel);
    imagestring($image, 5, $boxX + ($boxSizePx - $sizeLabelWidth) / 2, $boxY - 30, $sizeLabel, $black);

    // Footer with larger fonts
    $footerY = $heightPx - round($footerHeightInches * $dpi);
    $footerBg = imagecolorallocate($image, 245, 245, 245);
    imagefilledrectangle($image, 0, $footerY, $widthPx - 1, $heightPx - 1, $footerBg);

    // Footer border (thicker)
    imagesetthickness($image, 2);
    imageline($image, 0, $footerY, $widthPx - 1, $footerY, $lightGray);
    imagesetthickness($image, 1);

    // Footer text (font 5 for readability)
    $footerTextY = $footerY + (round($footerHeightInches * $dpi) - imagefontheight(5)) / 2;
    $footerText = "Scale Calibration  |  10\" x 10\" Reference Box  |  CuttingMaster.in";
    imagestring($image, 5, $headerPadding, $footerTextY, $footerText, $darkGray);

    $dpiText = sprintf("%d DPI", $dpi);
    $dpiWidth = imagefontwidth(4) * strlen($dpiText);
    imagestring($image, 4, $widthPx - $dpiWidth - $headerPadding, $footerTextY + 2, $dpiText, $darkGray);

    // Capture PNG output
    ob_start();
    imagepng($image, null, 9);
    $pngData = ob_get_clean();
    imagedestroy($image);

    return $pngData;
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
        'icon' => 'ruler',
        'format' => 'png'
    ];

    // Add patterns in order
    $patternNumber = 1;
    foreach ($standardOrder as $key) {
        if (isset($patterns[$key]) && isset($patterns[$key]['svg_content'])) {
            $patternName = $patterns[$key]['name'] ?? ucfirst($key);
            $dims = getSVGDimensions($patterns[$key]['svg_content'], $scale);
            $availablePatterns[] = [
                'key' => $key,
                'name' => $patternName,
                'description' => sprintf('%.1f" x %.1f"', $dims['width'], $dims['height']),
                'icon' => 'image',
                'format' => 'png'
            ];
            $patternNumber++;
        }
    }

    // Add any remaining patterns not in standard order
    foreach ($patterns as $key => $pattern) {
        if (!in_array($key, $standardOrder) && isset($pattern['svg_content'])) {
            $patternName = $pattern['name'] ?? ucfirst($key);
            $dims = getSVGDimensions($pattern['svg_content'], $scale);
            $availablePatterns[] = [
                'key' => $key,
                'name' => $patternName,
                'description' => sprintf('%.1f" x %.1f"', $dims['width'], $dims['height']),
                'icon' => 'image',
                'format' => 'png'
            ];
            $patternNumber++;
        }
    }

    echo json_encode([
        'success' => true,
        'customer' => $customerName,
        'measurementId' => $measurementId,
        'format' => 'png',
        'dpi' => $pngDPI,
        'patterns' => $availablePatterns
    ]);
    exit;
}

// =============================================================================
// ACTION: DOWNLOAD - Generate and download specific pattern PNG
// =============================================================================

if ($patternKey === 'scale') {
    // Generate Scale Calibration PNG with metadata
    $pngData = generateScaleCalibrationPng($scaleBoxSize, $pngDPI, $customerName, $measurementId, $logoPath);

    $filename = $safeCustomerName . '-Scale_Calibration.png';

    ob_end_clean();
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pngData));
    header('Cache-Control: no-cache, must-revalidate');
    echo $pngData;
    exit;
}

// Generate specific pattern PNG
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

// Add padding to dimensions
$totalWidthInches = $dims['width'] + (2 * $pagePadding);
$totalHeightInches = $dims['height'] + (2 * $pagePadding);

// Convert SVG to PNG with metadata
$pngData = svgToPngWithMetadata(
    $svgContent,
    $totalWidthInches,
    $totalHeightInches,
    $pngDPI,
    $headerHeightInches,
    $customerName,
    $patternName,
    $measurementId,
    $measurements,
    $logoPath,
    $contactNumber,
    $dims
);

// Output
$safePatternName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $patternName);
$filename = $safeCustomerName . '-' . $safePatternName . '.png';

ob_end_clean();
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pngData));
header('Cache-Control: no-cache, must-revalidate');
echo $pngData;
exit;
