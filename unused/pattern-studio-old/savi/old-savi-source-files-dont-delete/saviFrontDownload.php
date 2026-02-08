<?php
 error_reporting (E_ALL ^ E_NOTICE);
		session_start();
		$filename = $_SESSION["cust"]. "_front". "."."svg"; // blouse front file name
		$vApex = $_SESSION["vApex"];
        $hApex = $_SESSION["hApex"];
		$blouseFront = $_SESSION["blouseFront"];
		$blouseRed = $_SESSION["blouseFrontRed"];
		

		
$specVariable = '<?xml version="1.0" encoding="UTF-8" standalone="no"?> <svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"  xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" width="297mm" height="420mm" viewBox="-5 120 297 420" version="1.1" id="svg94" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)" sodipodi:docname="drawing-1.svg"> <defs id="defs88" /> <sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2"  inkscape:zoom="0.32" inkscape:cx="241.47996" inkscape:cy="765.19041" inkscape:document-units="mm" inkscape:current-layer="layer1" showgrid="true" inkscape:window-width="1366" inkscape:window-height="705" inkscape:window-x="-8" inkscape:window-y="-8" inkscape:window-maximized="1" />  <metadata id="metadata91"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title>  </cc:Work> </rdf:RDF> </metadata>';

// graydesign SVG Code
$dataVariable = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-width:0.3px;stroke-dasharray:2,2; stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviBlouseFront"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// green design SVG Code
$dataVariable1 = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-width:0.3px; stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviFrontBlouseGreen"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// Brown design SVG Code
$dataVariable2 = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-dasharray:5,2,4; stroke-width:0.3px; stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviFrontBlouseBrown"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// red design SVG Code
$dataVariable3 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.3px;stroke-linecap:butt;stroke-linejoin:miter;"
       d="' . $_SESSION["saviBlouseFrontRed"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// left tucks
$dataVariable4 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.3px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviFlTucks"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// bottom tucks
$dataVariable5 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.3px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviFbTucks"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// right tucks
$dataVariable6 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.3px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviFrTucks"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// right tucks
$dataVariable7 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.3px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["rightFrTucks"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// file name text - 
$dataVariable8 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">        
       <text style="fill: #000000; stroke: none;  font-size: 8px" x="' . $_SESSION["hApex"] . '"  y="' . $_SESSION["vApex"] . '" font-size="9"> ' . $_SESSION["cust"] . ' </text>
  </g>';


$dataVariable12 = '</svg>';

header("Content-Description: File Transfer"); 
header("Content-Type: application/octet-stream"); 
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
header('Content-type: svg/image');

echo $specVariable;
echo $dataVariable;
echo $dataVariable1;
echo $dataVariable2;
echo $dataVariable3;
echo $dataVariable4;
echo $dataVariable5;
echo $dataVariable6;
echo $dataVariable7;
echo $dataVariable8;
//
echo $dataVariable12;
//exit;
?>