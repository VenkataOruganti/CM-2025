<?php
	session_start();
	$filename = $_SESSION["cust"]. "_bsleeve". "."."svg"; // blouse front file name
	$vApex = $_SESSION["vApex"];
    $hApex = $_SESSION["hApex"];

    $slRed = $_SESSION["saviBSlRed"];
    $slBlack = $_SESSION["saviBSlBlack"];
		
$specVariable = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!-- Created with Inkscape (http://www.inkscape.org/) -->

<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
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
   id="svg64"
   inkscape:version="0.92.2 (5c3e80d, 2017-08-06)"
   sodipodi:docname="drawing.svg">
  <defs
     id="defs58" />
  <sodipodi:namedview
     id="base"
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1.0"
     inkscape:pageopacity="0.0"
     inkscape:pageshadow="2"
     inkscape:zoom="0.35"
     inkscape:cx="400"
     inkscape:cy="560"
     inkscape:document-units="mm"
     inkscape:current-layer="layer1"
     showgrid="false"
     inkscape:window-width="1366"
     inkscape:window-height="705"
     inkscape:window-x="-8"
     inkscape:window-y="-8"
     inkscape:window-maximized="1" />
  <metadata
     id="metadata61">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>';

// BlouseBack Black Design SVG Code
$dataVariable = '<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000;stroke-width:0.8px; stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       d="' . $_SESSION["saviBBlack"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// BlouseBack Red Design SVG Code
$dataVariable1 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,5,2; stroke-width:0.4px;stroke-linecap:butt;stroke-linejoin:miter;"
       d="'. $_SESSION["saviBRed"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';

// Blouse Brown Design SVG Code
$dataVariable2 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,2; stroke-width:0.4px;stroke-linecap:butt;stroke-linejoin:miter;"
       d="'. $_SESSION["saviBGray"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';


// file name text - 
$dataVariable3 = '<g inkscape:label="Layer 3" inkscape:groupmode="layer" id="layer3" transform="translate(0,123)">        
       <text x="' . $_SESSION["backHApex"] . '"  y="' . $_SESSION["backVApex"] . '"> ' . $_SESSION["cust"] . ' </text>
  </g>';

// BlouseBack Red Design SVG Code
$dataVariable4 = '<g inkscape:label="Layer 2" inkscape:groupmode="layer" id="layer1" transform="translate(0,123)"> <path
       style="fill:none;stroke:#000000; stroke-dasharray:2,5,2; stroke-width:0.4px;stroke-linecap:butt;stroke-linejoin:miter;"
       d="'. $_SESSION["centerLine"] . '" />"; id="path96" inkscape:connector-curvature="0" /> </g>';


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
echo $dataVariable12;
exit;
?>