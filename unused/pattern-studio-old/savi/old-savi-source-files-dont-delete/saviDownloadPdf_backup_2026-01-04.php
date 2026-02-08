<?php
/**
 * =============================================================================
 * SAVI BLOUSE PDF DOWNLOAD - Generates 4-page PDF with all pattern pieces
 * =============================================================================
 *
 * This file generates a PDF containing all 4 pattern pieces:
 * - Page 1: Front Pattern (Portrait A3)
 * - Page 2: Back Pattern (Portrait A3)
 * - Page 3: Sleeve Pattern (Landscape A3)
 * - Page 4: Patti Pattern (Portrait A3)
 *
 * Data Sources:
 * - Mode 1: customer_id parameter - loads measurements from database
 * - Mode 2: id parameter - loads measurements from database by measurement ID
 * - Mode 3: Session variables - uses existing session data
 *
 * SVG Structure follows exact style of:
 * - saviFrontDownload.php
 * - saviBackDownload.php
 * - saviSleeveDownload.php
 * - saviPattiDownload.php
 * =============================================================================
 */

// Suppress all errors and warnings to prevent output before PDF headers
// TCPDF has deprecation warnings in PHP 8.4+ that would corrupt the PDF
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Include saviComplete.php to load measurements and generate SVG paths
// This sets all the session variables we need
$_GET['customer_id'] = $_GET['customer_id'] ?? null;
$_GET['id'] = $_GET['id'] ?? null;

// Capture any output from saviComplete (we don't need it, just the session vars)
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

// Paper size
$paperSize = $_GET['paper'] ?? 'A3';

// =============================================================================
// SECTION 1: BUILD SVG STRINGS - Following exact style of savi*Download.php
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
// PAGE 1: FRONT PATTERN - Exact style from saviFrontDownload.php
// -----------------------------------------------------------------------------
$frontSvg = $svgHeaderPortrait;

// Gray design (reference line)
$frontSvg .= '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-width:0.5;stroke-dasharray:2,2;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
        d="' . ($_SESSION["saviBlouseFront_hide"] ?? '') . '" id="path96" inkscape:connector-curvature="0" />
</g>'; // Gray design (reference line) main structure line, hidden in pdf

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

// Customer name text
$frontSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer9" transform="translate(0,123)">
  <text style="fill:#000000;stroke:none;font-size:8px" x="' . ($_SESSION["hApex"] ?? 80) . '" y="' . ($_SESSION["vApex"] ?? 200) . '">' . htmlspecialchars('+') . '</text>
</g>';

$frontSvg .= $svgFooter;

// -----------------------------------------------------------------------------
// PAGE 2: BACK PATTERN - Exact style from saviBackDownload.php
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

// Customer name text
$backSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">
  <text x="' . ($_SESSION["backHApex"] ?? 80) . '" y="' . ($_SESSION["backVApex"] ?? 200) . '" font-size="9">' . htmlspecialchars('') . '</text>
</g>';

// Cut mark
$backSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer7" transform="translate(0,123)">
  <text x="' . ($_SESSION["bbPoint11"] ?? 10) . '" y="' . ($_SESSION["bbPoint12"] ?? 130) . '" font-size="2">X</text>
</g>';

$backSvg .= $svgFooter;

// -----------------------------------------------------------------------------
// PAGE 3: SLEEVE PATTERN - Exact style from saviSleeveDownload.php
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

// Customer name text
$sleeveSvg .= '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">
  <text x="' . ($_SESSION["backHApex"] ?? 150) . '" y="' . ($_SESSION["backVApex"] ?? 200) . '">' . htmlspecialchars('') . '</text>
</g>';

// Center line
$sleeveSvg .= '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">
  <path style="fill:none;stroke:#000000;stroke-dasharray:2,5,2;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter"
        d="' . ($_SESSION["centerLine"] ?? '') . '" id="path99" inkscape:connector-curvature="0" />
</g>';

$sleeveSvg .= $svgFooter;

// -----------------------------------------------------------------------------
// PAGE 4: PATTI PATTERN - Exact style from saviPattiDownload.php
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

// =============================================================================
// SECTION 2: GENERATE PDF USING TCPDF
// =============================================================================

require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A3', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Cutting Master');
$pdf->SetAuthor('Cutting Master');
$pdf->SetTitle('Savi Blouse Pattern - ' . $cust);
$pdf->SetSubject('Blouse Pattern');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);

// -----------------------------------------------------------------------------
// PAGE 1: FRONT PATTERN (Portrait A3)
// -----------------------------------------------------------------------------
$pdf->AddPage('P', 'A3');
$pdf->ImageSVG('@' . $frontSvg, 0, 0, 297, 420, '', '', '', 0, true);

// -----------------------------------------------------------------------------
// PAGE 2: BACK PATTERN (Portrait A3)
// -----------------------------------------------------------------------------
$pdf->AddPage('P', 'A3');
$pdf->ImageSVG('@' . $backSvg, 0, 0, 297, 420, '', '', '', 0, true);

// -----------------------------------------------------------------------------
// PAGE 3: SLEEVE PATTERN (Landscape A3)
// -----------------------------------------------------------------------------
$pdf->AddPage('L', 'A3');
$pdf->ImageSVG('@' . $sleeveSvg, 0, 0, 420, 297, '', '', '', 0, true);

// -----------------------------------------------------------------------------
// PAGE 4: PATTI PATTERN (Portrait A3)
// -----------------------------------------------------------------------------
$pdf->AddPage('P', 'A3');
$pdf->ImageSVG('@' . $pattiSvg, 0, 0, 297, 420, '', '', '', 0, true);

// =============================================================================
// SECTION 3: OUTPUT PDF
// =============================================================================

$filename = $safeFilename . '_blouse_pattern.pdf';

// Output the PDF
$pdf->Output($filename, 'D');
exit;
?>
