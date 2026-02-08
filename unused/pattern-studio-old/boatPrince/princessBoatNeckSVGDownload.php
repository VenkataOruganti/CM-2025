<?php
/**
 * =============================================================================
 * PRINCESS BOAT NECK BLOUSE SVG DOWNLOAD - Generates ZIP with all 3 pattern SVG files
 * =============================================================================
 *
 * This file generates a ZIP archive containing all 3 pattern pieces as SVG files:
 * - {customer}_front.svg - Front Pattern
 * - {customer}_back.svg - Back Pattern
 * - {customer}_sleeve.svg - Sleeve Pattern
 *
 * Each SVG includes a centered watermark with customer name and pattern name.
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

// Suppress all errors and warnings to prevent output before ZIP headers
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Include princessBoatNeck.php to load measurements and generate SVG paths
$_GET['customer_id'] = $_GET['customer_id'] ?? null;
$_GET['id'] = $_GET['id'] ?? null;

// Capture any output from princessBoatNeck
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

// =============================================================================
// SECTION 1: HELPER FUNCTION TO ADD WATERMARK TO SVG
// =============================================================================

/**
 * Add watermark text to SVG content (centered on page)
 * @param string $svgContent The SVG XML content
 * @param string $customerName Customer name (18px bold)
 * @param string $patternName Pattern name like "Front", "Back" (16px)
 * @param float $pageWidth Page width in mm
 * @param float $pageHeight Page height in mm
 * @param float $viewBoxOffsetY The Y offset from the viewBox
 * @return string Modified SVG with watermark
 */
function addWatermarkToSvg($svgContent, $customerName, $patternName, $pageWidth, $pageHeight, $viewBoxOffsetY = 120) {
    // Calculate center position
    $centerX = $pageWidth / 2;
    $centerY = ($pageHeight / 2) + $viewBoxOffsetY;

    // Create watermark group with light gray color
    $watermarkGroup = '
  <g id="watermark" style="opacity: 0.3;">
    <text x="' . $centerX . '" y="' . ($centerY - 5) . '"
          text-anchor="middle"
          font-family="Helvetica, Arial, sans-serif"
          font-size="18"
          font-weight="bold"
          fill="#888888">' . htmlspecialchars($customerName) . '</text>
    <text x="' . $centerX . '" y="' . ($centerY + 12) . '"
          text-anchor="middle"
          font-family="Helvetica, Arial, sans-serif"
          font-size="16"
          fill="#888888">' . htmlspecialchars($patternName . ' Pattern') . '</text>
  </g>';

    // Insert watermark before closing </svg> tag
    $svgContent = str_replace('</svg>', $watermarkGroup . "\n</svg>", $svgContent);

    return $svgContent;
}

// =============================================================================
// SECTION 2: BUILD SVG STRINGS - Following exact style of saviDownloadSvg.php
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
// SVG 1: FRONT PATTERN - Princess Boat Neck specific paths
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

// Apex mark
$frontSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer8" transform="translate(0,123)">
  <text style="fill:#000000;stroke:none;font-size:8px" x="' . ($_SESSION["hApex"] ?? 80) . '" y="' . ($_SESSION["vApex"] ?? 200) . '">+</text>
</g>';

$frontSvg .= $svgFooter;

// Add watermark to front SVG
$frontSvg = addWatermarkToSvg($frontSvg, $cust, 'Front', 297, 420, 120);

// -----------------------------------------------------------------------------
// SVG 2: BACK PATTERN - Princess Boat Neck specific paths
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

$backSvg .= $svgFooter;

// Add watermark to back SVG
$backSvg = addWatermarkToSvg($backSvg, $cust, 'Back', 297, 420, 120);

// -----------------------------------------------------------------------------
// SVG 3: SLEEVE PATTERN - Same as savi (shared sleeve logic)
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

// Add watermark to sleeve SVG (landscape orientation)
$sleeveSvg = addWatermarkToSvg($sleeveSvg, $cust, 'Sleeve', 420, 297, 100);

// =============================================================================
// SECTION 3: CREATE ZIP ARCHIVE WITH ALL SVG FILES
// =============================================================================

// Create a temporary file for the ZIP
$tempZipFile = tempnam(sys_get_temp_dir(), 'svg_patterns_');

// Create ZIP archive
$zip = new ZipArchive();
if ($zip->open($tempZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Error: Could not create ZIP archive.");
}

// Add SVG files to ZIP
$zip->addFromString($safeFilename . '_front.svg', $frontSvg);
$zip->addFromString($safeFilename . '_back.svg', $backSvg);
$zip->addFromString($safeFilename . '_sleeve.svg', $sleeveSvg);

// Close the ZIP
$zip->close();

// =============================================================================
// SECTION 4: OUTPUT ZIP FILE FOR DOWNLOAD
// =============================================================================

$zipFilename = $safeFilename . '_princess_boat_neck_patterns.zip';

// Set headers for ZIP download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . filesize($tempZipFile));
header('Pragma: no-cache');
header('Expires: 0');

// Output the ZIP file
readfile($tempZipFile);

// Clean up temporary file
unlink($tempZipFile);

exit;
?>
