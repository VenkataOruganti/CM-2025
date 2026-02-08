<?php
		session_start();
		$filename = $_SESSION["cust"]. "_patti". "."."svg"; // blouse front file name
		$vApex = $_SESSION["vApex"];
        $hApex = $_SESSION["hApex"];
		$blBackBlack = $_SESSION["bBackBlack"];
        $blBackRed = $_SESSION["bBackRed"];

        $blBackVApex = $_SESSION["backVApex"];
        $blBackVApex = $_SESSION["backHApex"];

		
$specVariable = '<?xml version="1.0" encoding="UTF-8" standalone="no"?> <svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"  xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" width="297mm" height="420mm" viewBox="-20 150 297 420" version="1.1" id="svg94" inkscape:version="0.92.2 (5c3e80d, 2017-08-06)" sodipodi:docname="drawing-1.svg"> <defs id="defs88" /> <sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2"  inkscape:zoom="0.32" inkscape:cx="241.47996" inkscape:cy="765.19041" inkscape:document-units="mm" inkscape:current-layer="layer1" showgrid="true" inkscape:window-width="1366" inkscape:window-height="705" inkscape:window-x="-8" inkscape:window-y="-8" inkscape:window-maximized="1" />  <metadata id="metadata91"> <rdf:RDF> <cc:Work rdf:about=""> <dc:format>image/svg+xml</dc:format> <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" /> <dc:title></dc:title>  </cc:Work> </rdf:RDF> </metadata>';

// BlouseBack Black Design SVG Code
$dataVariable = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-width:0.26458332px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["patti"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// file name text
$dataVariable1 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">        
       <text x="20"  y="120" font-size="7"> ' . $_SESSION["measure"] . ' </text>
  </g>';

$dataVariable2 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer4" transform="translate(0,123)">        
       <text x="20"  y="130" font-size="7"> ' . $_SESSION["measure1"] . ' </text>
  </g>';

$dataVariable3 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer5" transform="translate(0,123)">        
       <text x="20"  y="140" font-size="7"> ' . $_SESSION["measure2"] . ' </text>
  </g>';

$dataVariable4 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer6" transform="translate(0,123)">        
       <text x="20"  y="150" font-size="7"> ' . $_SESSION["measure3"] . ' </text>
  </g>';

// file name text
$dataVariable5 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">        
       <text x="' . $_SESSION["backHApex"] . '"  y="' . $_SESSION["backVApex"] . '"> ' . $_SESSION["cust"] . ' </text>
  </g>';

// BlouseBack Black Design SVG Code
$dataVariable = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-width:0.26458332px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["patti"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';


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
echo $dataVariable12;
exit;
?>