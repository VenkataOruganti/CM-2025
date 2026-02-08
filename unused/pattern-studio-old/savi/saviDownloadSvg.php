<?php
/**
 * =============================================================================
 * SAVI BLOUSE SVG DOWNLOAD - Generates ZIP with all 4 pattern SVG files
 * =============================================================================
 *
 * This file generates a ZIP archive containing all 4 pattern pieces as SVG files:
 * - {customer}_front.svg - Front Pattern
 * - {customer}_back.svg - Back Pattern
 * - {customer}_sleeve.svg - Sleeve Pattern
 * - {customer}_patti.svg - Patti Pattern
 *
 * Each SVG includes a centered watermark with customer name and pattern name.
 *
 * Data Sources:
 * - Mode 1: customer_id parameter - loads measurements from database
 * - Mode 2: id parameter - loads measurements from database by measurement ID
 * - Mode 3: Session variables - uses existing session data
 *
 * =============================================================================
 */

// Suppress all errors and warnings to prevent output before ZIP headers
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Include saviComplete.php to load measurements and generate SVG paths
$_GET['customer_id'] = $_GET['customer_id'] ?? null;
$_GET['id'] = $_GET['id'] ?? null;

// Capture any output from saviComplete
ob_start();
include __DIR__ . '/saviComplete.php';
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
// SECTION 2: BUILD SVG STRINGS - Following exact style of saviDownloadPdf.php
// =============================================================================

// Common SVG header for Portrait A3 (297mm x 420mm) - Front, Back, Patti
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

// SVG header for Patti (different viewBox)
$svgHeaderPatti = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:cc="http://creativecommons.org/ns#"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:svg="http://www.w3.org/2000/svg"
     xmlns="http://www.w3.org/2000/svg"
     xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
     xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
     width="297mm"
     height="420mm"
     viewBox="-20 100 297 420"
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

$svgFooter = '</svg>';

// -----------------------------------------------------------------------------
// SVG 1: FRONT PATTERN
// -----------------------------------------------------------------------------
$frontSvg = $svgHeaderPortrait;

// Gray design (reference line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviBlouseFront_hide"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Green design (main stitch line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviFrontBlouseGreen"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

// Brown design (extra bust line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:5,2,4;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviFrontBlouseBrown"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Red design (seam allowance)
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBlouseFrontRed"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

// Left tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviFlTucks"] ?? '') . '" id="path100" inkscape:connector-curvature="0" />
</g>';

// Bottom tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviFbTucks"] ?? '') . '" id="path101" inkscape:connector-curvature="0" />
</g>';

// Right tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer7" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviFrTucks"] ?? '') . '" id="path102" inkscape:connector-curvature="0" />
</g>';

// Right center tucks
$frontSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer8" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["rightFrTucks"] ?? '') . '" id="path103" inkscape:connector-curvature="0" />
</g>';

// Apex mark
$frontSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer9" transform="translate(0,123)">
  <text style="fill:#000000;stroke:none;font-size:8px" x="' . ($_SESSION["hApex"] ?? 80) . '" y="' . ($_SESSION["vApex"] ?? 200) . '">+</text>
</g>';

$frontSvg .= $svgFooter;

// Add watermark to front SVG
$frontSvg = addWatermarkToSvg($frontSvg, $cust, 'Front', 297, 420, 120);

// -----------------------------------------------------------------------------
// SVG 2: BACK PATTERN
// -----------------------------------------------------------------------------
$backSvg = $svgHeaderPortrait;

// Black design (reference)
$backSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviBackBlack_hide"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Red design (seam allowance)
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBackRed"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

// Green design (main stitch line)
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBackGreen"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Brown design
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,2;stroke-width:0.3;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBackBrown"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

// Back tucks
$backSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["saviBackTucks"] ?? '') . '" id="path100" inkscape:connector-curvature="0" />
</g>';

// Cut mark
$backSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer7" transform="translate(0,123)">
  <text x="' . ($_SESSION["bbPoint11"] ?? 10) . '" y="' . ($_SESSION["bbPoint12"] ?? 130) . '" font-size="2">X</text>
</g>';

$backSvg .= $svgFooter;

// Add watermark to back SVG
$backSvg = addWatermarkToSvg($backSvg, $cust, 'Back', 297, 420, 120);

// -----------------------------------------------------------------------------
// SVG 3: SLEEVE PATTERN
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
        d="' . ($_SESSION["saviBGray_hide"] ?? '') . '" id="path98" inkscape:connector-curvature="0" />
</g>';

// Center line
$sleeveSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,5,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["centerLine"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

$sleeveSvg .= $svgFooter;

// Add watermark to sleeve SVG (landscape orientation)
$sleeveSvg = addWatermarkToSvg($sleeveSvg, $cust, 'Sleeve', 420, 297, 100);

// -----------------------------------------------------------------------------
// SVG 4: PATTI PATTERN
// -----------------------------------------------------------------------------
$pattiSvg = $svgHeaderPatti;

// Black design (main stitch line)
$pattiSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviPattiBlack"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>';

// Measurement text line 1
$pattiSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer2" transform="translate(0,123)">
  <text x="20" y="105" font-size="4">' . htmlspecialchars($_SESSION["measure"] ?? '') . '</text>
</g>';

// Measurement text line 2
$pattiSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">
  <text x="20" y="110" font-size="4">' . htmlspecialchars($_SESSION["measure1"] ?? '') . '</text>
</g>';

// Measurement text line 3
$pattiSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <text x="20" y="115" font-size="4">' . htmlspecialchars($_SESSION["measure2"] ?? '') . '</text>
</g>';

// Measurement text line 4
$pattiSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <text x="20" y="120" font-size="4">' . htmlspecialchars($_SESSION["measure3"] ?? '') . '</text>
</g>';

// Customer name
$pattiSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">
  <text x="100" y="240">' . htmlspecialchars($cust) . '</text>
</g>';

// Red design (seam allowance + hook patti)
$pattiSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer7" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviPattiRed"] ?? '') . '" id="path97" inkscape:connector-curvature="0" />
</g>';

$pattiSvg .= $svgFooter;

// Add watermark to patti SVG
$pattiSvg = addWatermarkToSvg($pattiSvg, $cust, 'Patti', 297, 420, 100);

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
$zip->addFromString($safeFilename . '_patti.svg', $pattiSvg);

// Close the ZIP
$zip->close();

// =============================================================================
// SECTION 4: OUTPUT ZIP FILE FOR DOWNLOAD
// =============================================================================

$zipFilename = $safeFilename . '_blouse_patterns.zip';

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
