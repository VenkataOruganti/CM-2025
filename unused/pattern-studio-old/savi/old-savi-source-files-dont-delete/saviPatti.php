<div>
    <div style="float: right;">
        <span>Download Blouse Patti</span>
        <button class="btn btn-info" >
        <a href="../savi/saviPattiDownload.php" style="color:#ffffff"> SVG </a>
        </button>
		 <button class="btn btn-info" >
        <a href="../savi/saviPattiDownloadPdf.php"  style="color:#ffffff"> PDF </a>
        </button>
    </div>
    
	<?php
/* blouse black border */
        // Retrieve variables from session
        $cIn = 25.4;  // Conversion: 1 inch = 25.4mm
        $blength = $_SESSION["blength"] ?? 0;
        $flength = $_SESSION["flength"] ?? 0;
        $chest = $_SESSION["chest"] ?? 0;
        $fnDepth = $_SESSION["fndepth"] ?? 0;
        $fbDart = $_SESSION["fbDart"] ?? 0;

        $fLeft = (1 * $cIn);
        $topMargin = 0.5;
    
        $bPatti1 = "M". ($fLeft + (0.25 * $cIn)) . "," . ($topMargin * $cIn);
        $bPatti2 = "L". ($fLeft + (0.25 * $cIn)) ."," . ((($blength - $flength) + 2) * $cIn);
        $bPatti3 = "Q". ((($blength - $flength) + 0.5)* $cIn) ."," . ((($blength - $flength) + 0.5)* $cIn) . "," . (($chest / 8) * $cIn) ."," . ((($blength - $flength) + 0.5)* $cIn);
        $bPatti4 = "L". (($chest / 4) * $cIn) ."," . ((($blength - $flength) + 1.5) * $cIn);
        $bPatti5 = "L". (($chest / 4) * $cIn) . "," . ($topMargin * $cIn);

        $saviBlousePatti = $bPatti1 . $bPatti2 . $bPatti3 . $bPatti4 . $bPatti5 . "Z" ;
        $_SESSION["saviPattiBlack"] = $saviBlousePatti;     
    
/* blouse red border */    
    
        $bPattiRed1 = "M". $fLeft . "," . (0.2 * $cIn);
        $bPattiRed2 = "L". $fLeft ."," . ((($blength - $flength) + 2.5) * $cIn);
        $bPattiRed3 = "Q". ((($blength - $flength) + 1)* $cIn) ."," . ((($blength - $flength) + 0.8)* $cIn) . "," . ((($chest / 8) +1) * $cIn) ."," . ((($blength - $flength) + 1.0)* $cIn);
    
        $bPattiRed4 = "L". ((($chest / 4) +0.25) * $cIn) ."," . ((($blength - $flength) + 2) * $cIn);
        $bPattiRed5 = "L". ((($chest / 4) +0.25) * $cIn) . "," . (0.2 * $cIn);
        
        $saviBlousePattiRed = $bPattiRed1 . $bPattiRed2 . $bPattiRed3 . $bPattiRed4 . $bPattiRed5 ."Z" ;
        
     $_SESSION["saviPattiRed"] = $saviBlousePattiRed; 
            
// hook patti
        $pattiTop = 5 * $cIn;

        $hPoint1 = "M" . 0 . "," . $pattiTop;
        $hPoint2 = "L" . ((($blength - $fnDepth) + $fLeft) * $cIn) . "," . $pattiTop;
        $hPoint3 = "L" . ((($blength - $fnDepth)+ $fLeft) * $cIn) . "," . ($pattiTop + (3 * $cIn));  
        $hPoint4 = "L" . 0  . "," . ($pattiTop + (3 * $cIn));
  
        $hookPatti = $hPoint1 . $hPoint2 . $hPoint3 . $hPoint4 . "Z";
        $_SESSION["saviPattiRed"] = $saviBlousePattiRed . $hookPatti; 
	?>
	
	<svg width="500" height="600" viewbox = "-50, -50, 500, 600">
		<g>
    		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBlousePatti;?>" />
    		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBlousePattiRed;?>" />
    		
    	</g>
    	<g>    	    
    		<path fill="none" stroke="#ff0000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $hookPatti;?>" />
    	</g>
	</svg>
</div>
<!--
<div>FN Depth : <?php echo $fnDepth;?></div>
<div>Back Length : <?php echo $blength;?></div>
-->