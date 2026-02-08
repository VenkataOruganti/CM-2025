<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="inc/blousePattiDownload.php" style="color:#ffffff"> Download Blouse Patti Design</a>
        </button>
    </div>
    
	<?php
/* blouse black border */
    
        $fLeft = (1 * $cIn);
        $topMargin = 0;
        $fbDart = $_SESSION["fbDart"];
    
        $bPatti1 = "M". ($fLeft + (0.5 * $cIn)) . "," . $topMargin;
        $bPatti2 = "L". ($fLeft + (0.5 * $cIn)) ."," . ((($blength - $flength) + 0.5) * $cIn);
        $bPatti3 = "L". (((($chest / 4) - $fbDart) + 0.5) * $cIn) ."," . ((($blength - $flength) + 0.5 ) * $cIn);
        $bPatti4 = "L". (((($chest / 4) - $fbDart) + 0.5) * $cIn) ."," . ((($blength - $flength) - 1) * $cIn);
        $bPatti5 = "L". (((($chest / 4) + 1.5) /2) * $cIn) ."," . ((($blength - $flength) - 1) * $cIn);
    
        $bPatti6 = "Q".  ($fLeft + (0.5 * $cIn)) . "," . (0.5 * $cIn) . ","  . ($fLeft + (0.5 * $cIn)) . "," . $topMargin;  
        
        $blousePatti = $bPatti1 . $bPatti2 . $bPatti3 . $bPatti4 . $bPatti5 . $bPatti6 . "Z" ;
            
        $_SESSION["pattiBlack"] = $blousePatti; 
    
    
/* blouse red border */    
    
        $bPattiRed1 = "M". $fLeft . "," . $topMargin;
        $bPattiRed2 = "L". $fLeft ."," . ((($blength - $flength) + 1.0) * $cIn);
        $bPattiRed3 = "L". (((($chest / 4) - $fbDart) + 0.5) * $cIn) ."," . ((($blength - $flength) + 1.0 ) * $cIn);
        
        $blousePattiRed = $bPattiRed1 . $bPattiRed2 . $bPattiRed3 ;
            
        $_SESSION["pattiRed"] = $blousePattiRed; 
		
	?>
	
	<svg width="500" height="400" viewbox = "-50, -50, 500, 400">
		<g>
    		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blousePatti;?>" />
    		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blousePattiRed;?>" />
    	</g>
	
	</svg>	
</div>