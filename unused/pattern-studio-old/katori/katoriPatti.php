<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="../katori/katoriPatti.php" style="color:#ffffff"> Download Blouse Patti Design</a>
        </button>
    </div>
    
	<?php
    
        $fLeft = (1 * $cIn);
        $topMargin = 0;
        $fbDart = $_SESSION["fbDart"];
    
        $bPatti1 = "M". $fLeft . "," . $topMargin;
        $bPatti2 = "L". $fLeft ."," . ((($blength - $flength) + 0.5) * $cIn);
        $bPatti3 = "L". ((($chest / 4) - $fbDart) * $cIn) ."," . ((($blength - $flength) + 0.5 ) * $cIn);
        $bPatti4 = "L". ((($chest / 4) - $fbDart) * $cIn) ."," . ((($blength - $flength) - 1) * $cIn);
        $bPatti5 = "L". (((($chest / 4) + 1) /2) * $cIn) ."," . ((($blength - $flength) - 1) * $cIn);
    
        $bPatti6 = "Q".  $fLeft . "," . (0.5 * $cIn) . ","  . $fLeft . "," . $topMargin;  
        
        $blousePatti = $bPatti1 . $bPatti2 . $bPatti3 . $bPatti4 . $bPatti5 . $bPatti6 . "Z" ;
            
        $_SESSION["patti1"] = $blousePatti;        
		
	?>
	
	<svg width="500" height="400" viewbox = "-50, -50, 500, 400">
		<g>
    		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blousePatti;?>" />
    	</g>
	
	</svg>	
</div>