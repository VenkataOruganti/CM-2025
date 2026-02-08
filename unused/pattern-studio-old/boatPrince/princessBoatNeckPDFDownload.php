<?php
/**
 * =============================================================================
 * PRINCESS BOAT NECK BLOUSE PDF DOWNLOAD - Generates 3-page PDF with all pattern pieces
 * =============================================================================
 *
 * This file generates a PDF containing all 3 pattern pieces:
 * - Page 1: Front Pattern (Portrait A3)
 * - Page 2: Back Pattern (Portrait A3)
 * - Page 3: Sleeve Pattern (Landscape A3)
 *
 * Data Sources:
 * - Mode 1: customer_id parameter - loads measurements from database
 * - Mode 2: id parameter - loads measurements from database by measurement ID
 * - Mode 3: Session variables - uses existing session data
 *
 * Session Variables Used:
 * - Front: princeBlouseFront, princeFrontBlouseGreen, princeFrontBlouseBrown,
 *          princeBlouseFrontRed, princeFlTucks, princeCurveGray, rightFrTucks, vApex, hApex
 * - Back: princeBackBlack, princeBackGreen, princeBackBrown, princeBackRed,
 *         princeBackTucks, backVApex, backHApex
 * - Sleeve: saviBBlack, saviBGray, saviBRed, centerLine
 * =============================================================================
 */

// Suppress all errors and warnings to prevent output before PDF headers
// TCPDF has deprecation warnings in PHP 8.4+ that would corrupt the PDF
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Include princessBoatNeck.php to load measurements and generate SVG paths
// This sets all the session variables we need
$_GET['customer_id'] = $_GET['customer_id'] ?? null;
$_GET['id'] = $_GET['id'] ?? null;

// Capture any output from princessBoatNeck (we don't need it, just the session vars)
ob_start();
include __DIR__ . '/princessBoatNeck.php';
ob_end_clean();

// Check if we have the required session data
if (!isset($_SESSION["chest"]) || empty($_SESSION["chest"])) {
    die("Error: No pattern data found. Please generate a pattern first.");
}

// Get customer name for filename
$cust = $_SESSION["cust"] ?? 'pattern';
$safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $cust);

// Paper size from URL parameter
$paperSize = $_GET['paper'] ?? 'A3';

// =============================================================================
// TILING CONFIGURATION - Handle patterns larger than paper size
// =============================================================================

// Paper sizes in mm (width x height in Portrait orientation)
$paperSizes = [
    'A4' => ['width' => 210, 'height' => 297],
    'A3' => ['width' => 297, 'height' => 420],
    'A2' => ['width' => 420, 'height' => 594],
    'A1' => ['width' => 594, 'height' => 841],
];

// Get paper dimensions
$paperWidth = $paperSizes[$paperSize]['width'] ?? 297;
$paperHeight = $paperSizes[$paperSize]['height'] ?? 420;

// Conversion factor
$cIn = 25.4;  // 1 inch = 25.4mm

// Get measurements from session
$chest = floatval($_SESSION["chest"] ?? 34);
$fshoulder = floatval($_SESSION["fshoulder"] ?? 14);
$flength = floatval($_SESSION["flength"] ?? 15);
$blength = floatval($_SESSION["blength"] ?? 16);
$slength = floatval($_SESSION["slength"] ?? 6);
$saround = floatval($_SESSION["saround"] ?? 12);
$armhole = floatval($_SESSION["armhole"] ?? 16);

// Calculate pattern dimensions (in mm) with margins
$margin = 20;  // 20mm margin around pattern

// Front/Back pattern dimensions
$frontPatternWidth = (($fshoulder / 2) + 1) * $cIn + $margin;   // half shoulder + seam + margin
$frontPatternHeight = ($flength + 2) * $cIn + $margin;           // front length + seam + margin
$backPatternHeight = ($blength + 2) * $cIn + $margin;            // back length + seam + margin

// Sleeve pattern dimensions (wider, shorter)
$sleevePatternWidth = ($armhole + 4) * $cIn + $margin;           // armhole based width
$sleevePatternHeight = ($slength + 2) * $cIn + $margin;          // sleeve length + margin

// Overlap for tiling alignment marks (in mm)
$overlap = 15;

/**
 * Calculate tiling requirements for a pattern
 * Returns: ['needsTiling' => bool, 'orientation' => 'P'|'L', 'tilesX' => int, 'tilesY' => int]
 */
function calculateTiling($patternWidth, $patternHeight, $paperWidth, $paperHeight, $overlap) {
    // First check Portrait orientation
    $portraitFits = ($patternWidth <= $paperWidth - 10) && ($patternHeight <= $paperHeight - 10);

    // Check Landscape orientation (swap paper dimensions)
    $landscapeFits = ($patternWidth <= $paperHeight - 10) && ($patternHeight <= $paperWidth - 10);

    // Determine best orientation
    if ($portraitFits) {
        return [
            'needsTiling' => false,
            'orientation' => 'P',
            'tilesX' => 1,
            'tilesY' => 1,
            'paperW' => $paperWidth,
            'paperH' => $paperHeight
        ];
    } elseif ($landscapeFits) {
        return [
            'needsTiling' => false,
            'orientation' => 'L',
            'tilesX' => 1,
            'tilesY' => 1,
            'paperW' => $paperHeight,  // swapped for landscape
            'paperH' => $paperWidth
        ];
    } else {
        // Need tiling - use landscape for more width
        $useWidth = $paperHeight;  // landscape width
        $useHeight = $paperWidth;  // landscape height

        // Calculate number of tiles needed
        $effectiveWidth = $useWidth - $overlap;
        $effectiveHeight = $useHeight - $overlap;

        $tilesX = max(1, ceil($patternWidth / $effectiveWidth));
        $tilesY = max(1, ceil($patternHeight / $effectiveHeight));

        return [
            'needsTiling' => true,
            'orientation' => 'L',
            'tilesX' => $tilesX,
            'tilesY' => $tilesY,
            'paperW' => $useWidth,
            'paperH' => $useHeight
        ];
    }
}

// Calculate tiling for each pattern piece
$frontTiling = calculateTiling($frontPatternWidth, $frontPatternHeight, $paperWidth, $paperHeight, $overlap);
$backTiling = calculateTiling($frontPatternWidth, $backPatternHeight, $paperWidth, $paperHeight, $overlap);
$sleeveTiling = calculateTiling($sleevePatternWidth, $sleevePatternHeight, $paperWidth, $paperHeight, $overlap);

// Store tiling info in array for later use
$tilingInfo = [
    'front' => $frontTiling,
    'back' => $backTiling,
    'sleeve' => $sleeveTiling,
    'paperSize' => $paperSize,
    'overlap' => $overlap
];

// =============================================================================
// SECTION 1: BUILD SVG STRINGS - Following exact style of savi*Download.php
// =============================================================================

// Common SVG header for Portrait A3 (297mm x 420mm) - Front, Back
$svgHeaderPortrait = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:cc="http://creativecommons.org/ns#"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:svg="http://www.w3.org/2000/svg"
     xmlns="http://www.w3.org/2000/svg"
     xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
     xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
     width="297mm"
     height="420mm"
     viewBox="-5 120 297 420"
     version="1.1"
     id="svg94">
  <defs id="defs88" />
  <metadata id="metadata91">
    <rdf:RDF>
      <cc:Work rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
      </cc:Work>
    </rdf:RDF>
  </metadata>';

// SVG header for Landscape A3 (420mm x 297mm) - Sleeve
$svgHeaderLandscape = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:cc="http://creativecommons.org/ns#"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:svg="http://www.w3.org/2000/svg"
     xmlns="http://www.w3.org/2000/svg"
     xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
     xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
     width="420mm"
     height="297mm"
     viewBox="5 100 420 297"
     version="1.1"
     id="svg64">
  <defs id="defs58" />
  <metadata id="metadata61">
    <rdf:RDF>
      <cc:Work rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
      </cc:Work>
    </rdf:RDF>
  </metadata>';

$svgFooter = '</svg>';

// -----------------------------------------------------------------------------
// PAGE 1: FRONT PATTERN - Princess Boat Neck specific paths
// -----------------------------------------------------------------------------
$frontSvg = $svgHeaderPortrait;

// Gray design (reference line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeBlouseFront"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Green design (main stitch line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeFrontBlouseGreen"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

// Brown design (extra bust line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:5,2,4;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeFrontBlouseBrown"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Red design (seam allowance)
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["princeBlouseFrontRed"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

// Left tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeFlTucks"] ?? '') . '" id="path100" inkscape:connector-curvature="0" />
</g>';

// Princess curve gray (unique to princess cut)
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeCurveGray"] ?? '') . '" id="path101" inkscape:connector-curvature="0" />
</g>';

// Right center tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer7" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["rightFrTucks"] ?? '') . '" id="path102" inkscape:connector-curvature="0" />
</g>';

// Customer name text
$frontSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer8" transform="translate(0,123)">
  <text style="fill:#000000;stroke:none;font-size:8px" x="' . ($_SESSION["hApex"] ?? 80) . '" y="' . ($_SESSION["vApex"] ?? 200) . '">' . htmlspecialchars('+') . '</text>
</g>';

$frontSvg .= $svgFooter;

// -----------------------------------------------------------------------------
// PAGE 2: BACK PATTERN - Princess Boat Neck specific paths
// -----------------------------------------------------------------------------
$backSvg = $svgHeaderPortrait;

// Black design (reference)
$backSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["princeBackBlack"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Green design (main stitch line)
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["princeBackGreen"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

// Brown design
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.3;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["princeBackBrown"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Red design (seam allowance)
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["princeBackRed"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

// Back tucks
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["princeBackTucks"] ?? '') . '" id="path100" inkscape:connector-curvature="0" />
</g>';

// Customer name text
$backSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">
  <text x="' . ($_SESSION["backHApex"] ?? 80) . '" y="' . ($_SESSION["backVApex"] ?? 200) . '" font-size="9">' . htmlspecialchars('') . '</text>
</g>';

$backSvg .= $svgFooter;

// -----------------------------------------------------------------------------
// PAGE 3: SLEEVE PATTERN - Same as savi (shared sleeve logic)
// -----------------------------------------------------------------------------
$sleeveSvg = $svgHeaderLandscape;

// Black design (main stitch line)
$sleeveSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviBBlack"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Red design (seam allowance)
$sleeveSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,5,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBRed"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

// Gray design (inner reference)
$sleeveSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBGray"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Center line
$sleeveSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,5,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["centerLine"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

$sleeveSvg .= $svgFooter;

// =============================================================================
// SECTION 2: GENERATE PDF USING TCPDF WITH TILING SUPPORT
// =============================================================================

require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Create new PDF document
$pdf = new TCPDF('P', 'mm', $paperSize, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Cutting Master');
$pdf->SetAuthor('Cutting Master');
$pdf->SetTitle('Princess Boat Neck Blouse Pattern - ' . $cust);
$pdf->SetSubject('Blouse Pattern');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);

/**
 * Add alignment marks to a page for tiling
 */
function addAlignmentMarks($pdf, $row, $col, $tilesX, $tilesY, $paperW, $paperH, $patternName) {
    $markSize = 8;  // 8mm alignment marks
    $markOffset = 5; // 5mm from edge

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);

    // Top-left corner mark (if not first row/col)
    if ($row > 0 || $col > 0) {
        $pdf->Line($markOffset, $markOffset, $markOffset + $markSize, $markOffset);
        $pdf->Line($markOffset, $markOffset, $markOffset, $markOffset + $markSize);
        // Cross mark for alignment
        $pdf->Line($markOffset, $markOffset + $markSize/2, $markOffset + $markSize, $markOffset + $markSize/2);
        $pdf->Line($markOffset + $markSize/2, $markOffset, $markOffset + $markSize/2, $markOffset + $markSize);
    }

    // Top-right corner mark (if not last column)
    if ($col < $tilesX - 1) {
        $x = $paperW - $markOffset - $markSize;
        $pdf->Line($x, $markOffset, $x + $markSize, $markOffset);
        $pdf->Line($x + $markSize, $markOffset, $x + $markSize, $markOffset + $markSize);
    }

    // Bottom-left corner mark (if not last row)
    if ($row < $tilesY - 1) {
        $y = $paperH - $markOffset - $markSize;
        $pdf->Line($markOffset, $y, $markOffset, $y + $markSize);
        $pdf->Line($markOffset, $y + $markSize, $markOffset + $markSize, $y + $markSize);
    }

    // Bottom-right corner mark (if not last row/col)
    if ($row < $tilesY - 1 || $col < $tilesX - 1) {
        $x = $paperW - $markOffset - $markSize;
        $y = $paperH - $markOffset - $markSize;
        $pdf->Line($x + $markSize, $y, $x + $markSize, $y + $markSize);
        $pdf->Line($x, $y + $markSize, $x + $markSize, $y + $markSize);
    }

    // Add tile label
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(100, 100, 100);
    $tileLabel = $patternName . " - Tile " . ($row * $tilesX + $col + 1) . " of " . ($tilesX * $tilesY);
    $pdf->Text($markOffset + $markSize + 5, $markOffset + 2, $tileLabel);

    // Add grid position
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Text($markOffset + $markSize + 5, $markOffset + 8, "Row " . ($row + 1) . ", Col " . ($col + 1));

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Add watermark to a page (customer name and pattern name)
 */
function addWatermark($pdf, $customerName, $patternName, $paperW, $paperH) {
    // Set semi-transparent light gray color for watermark
    $pdf->SetTextColor(200, 200, 200);

    // Customer name - 18px, centered horizontally
    $pdf->SetFont('helvetica', 'B', 18);
    $customerNameWidth = $pdf->GetStringWidth($customerName);
    $xPos = ($paperW - $customerNameWidth) / 2;
    $yPos = ($paperH / 2) - 5; // 5mm above center
    $pdf->Text($xPos, $yPos, $customerName);

    // Pattern name - 16px, centered horizontally
    $pdf->SetFont('helvetica', '', 16);
    $patternLabel = $patternName . ' Pattern';
    $patternNameWidth = $pdf->GetStringWidth($patternLabel);
    $xPos = ($paperW - $patternNameWidth) / 2;
    $yPos = ($paperH / 2) + 3; // 3mm below center
    $pdf->Text($xPos, $yPos, $patternLabel);

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Generate tiled pages for a pattern
 */
function generateTiledPages($pdf, $svgContent, $tiling, $patternName, $paperSize, $overlap, $customerName = '') {
    if (!$tiling['needsTiling']) {
        // No tiling needed - single page
        $pdf->AddPage($tiling['orientation'], $paperSize);
        $pdf->ImageSVG('@' . $svgContent, 0, 0, $tiling['paperW'], $tiling['paperH'], '', '', '', 0, true);

        // Add watermark
        if (!empty($customerName)) {
            addWatermark($pdf, $customerName, $patternName, $tiling['paperW'], $tiling['paperH']);
        }
    } else {
        // Tiling needed - generate multiple pages
        $tilesX = $tiling['tilesX'];
        $tilesY = $tiling['tilesY'];
        $paperW = $tiling['paperW'];
        $paperH = $tiling['paperH'];

        for ($row = 0; $row < $tilesY; $row++) {
            for ($col = 0; $col < $tilesX; $col++) {
                // Calculate viewport offset for this tile
                $offsetX = $col * ($paperW - $overlap);
                $offsetY = $row * ($paperH - $overlap);

                // Create new page in landscape orientation
                $pdf->AddPage('L', $paperSize);

                // Modify the SVG viewBox for this tile
                $tiledSvg = preg_replace(
                    '/viewBox="[^"]*"/',
                    'viewBox="' . ($offsetX - 5) . ' ' . ($offsetY + 100) . ' ' . $paperW . ' ' . $paperH . '"',
                    $svgContent
                );

                // Render the tiled portion
                $pdf->ImageSVG('@' . $tiledSvg, 0, 0, $paperW, $paperH, '', '', '', 0, true);

                // Add alignment marks
                addAlignmentMarks($pdf, $row, $col, $tilesX, $tilesY, $paperW, $paperH, $patternName);

                // Add watermark on each tile
                if (!empty($customerName)) {
                    addWatermark($pdf, $customerName, $patternName, $paperW, $paperH);
                }
            }
        }
    }
}

// -----------------------------------------------------------------------------
// PAGE 1: FRONT PATTERN
// -----------------------------------------------------------------------------
generateTiledPages($pdf, $frontSvg, $frontTiling, 'Front', $paperSize, $overlap, $cust);

// -----------------------------------------------------------------------------
// PAGE 2: BACK PATTERN
// -----------------------------------------------------------------------------
generateTiledPages($pdf, $backSvg, $backTiling, 'Back', $paperSize, $overlap, $cust);

// -----------------------------------------------------------------------------
// PAGE 3: SLEEVE PATTERN
// -----------------------------------------------------------------------------
generateTiledPages($pdf, $sleeveSvg, $sleeveTiling, 'Sleeve', $paperSize, $overlap, $cust);

// =============================================================================
// SECTION 3: OUTPUT PDF
// =============================================================================

$filename = $safeFilename . '_princess_boat_neck_pattern.pdf';

// Output the PDF
$pdf->Output($filename, 'D');
exit;
?>
