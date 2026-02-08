<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="inc/frontDownload.php" style="color:#ffffff"> Download Blouse Front Design</a>
        </button>
    </div>
    <div>
        <p>
            <ul>
                <li>The print size is : A3 ( 11" x 17" - portrait)</li>
                <li>Do not cut the cloth, too close to the margins</li>
                <li>Left side is neck depth and fold.</li>
            </ul>
        </p>
    </div>
    <div>
        <?php include 'inc/chestVertical.php';?> 
    </div>

	<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
    
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back  
        $_SESSION["fbDart"] = $bDart;
    
    // Blouse Sleeve center to Apex
		$vApex = (($apex + 0.5) * $cIn);
		$_SESSION["vApex"] = $vApex;

		$hApex = ((($fshoulder/2) - $shoulder) * $cIn);        //((($fshoulder - ($shoulder * 2)) /2) * $cIn);
		$_SESSION["hApex"] = $hApex;
      
//		$frontDart = "L" . ($hApex - $fDart) . "," . ($blength * $cIn);

		$front_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
		$front_point2 = " L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$front_point3 = " L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
		$front_point4 = " L" . $fLeft . "," . $fndepth1;
		$front_point5 = " L" . $fLeft . "," . ($flength1 - (1 * $cIn));
        
        $front_point6 = "Q" .   (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (0.4 * $cIn)) .",". 
                                $hApex .",". 
                                $flength1 ; 
    
		$front_point8 = " L" . ((($chest / 4) + 1) * $cIn) . "," . ($flength1 - (0.5 * $cIn)); // low chest
		$front_point9 = " L" . (((($chest / 4) + 1) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    $front_point10 = " L" . (($fshoulder1 / 2) - (0.5 *$cIn)) . "," . $chestVertical;
	//	$front_point11 = " L" . ($fshoulder1 / 2) . "," . ($chestVertical - (1 * $cIn));
		$front_point12 = " L" . ($fshoulder1 / 2) . "," . $seam ;
        
// Black line graphic (squares)

		$frontBlouseGray = $front_point1 . $front_point2 . $front_point3 . $front_point4 . $front_point5 . $front_point6 . $front_point8 . $front_point9 . $front_point10 . $front_point11 . $front_point12 . "Z";

		$_SESSION["blouseFront"] = $frontBlouseGray;
        


// -------------- green dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 0.5) * $cIn) . "," . ($flength1 - (0.5 * $cIn)); // low chest
        $green_point9 = " L" . (((($chest / 4) + 0.5) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $frontBlouseGreen = $green_point8 . $green_point9;

		$_SESSION["frontBlouseGreen"] = $frontBlouseGreen;
    
// -------------- brown dotted line start ----------------------------

		$green_point8 = " M" . (($chest / 4)  * $cIn) . "," . ($flength1 - (0.5 * $cIn)); // low chest
        $green_point9 = " L" . ((($chest / 4) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $frontBlouseBrown = $green_point8 . $green_point9;

		$_SESSION["frontBlouseBrown"] = $frontBlouseBrown;
         
// -------------- red dotted line start ----------------------------
		
		$front_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . $topMargin;
		$front_point2 = "L" . ((($fshoulder1 / 2) - $shoulder1) + (0.2 * $cIn)) . "," . ($fndepth1 / 2);
		$front_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $fLeft .",". $fndepth1;
        $front_point5 = " L" . $fLeft . "," . ($flength1 - (1 * $cIn)+ $seam);
        $front_point6 = "Q" .   (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (.5 * $cIn)) .",". 
                                (($fshoulder1 - ($shoulder1 * 2)) /2) .",". 
                                ($flength1 + (0.5 * $cIn)); 
    
		$front_point8 = " L" . (((($chest / 4) + 1) + 0.5 )* $cIn) . "," . ($flength1); // low chest
		$front_point9 = " L" . ((((($chest / 4) + 1) + 0.5) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
    
        $front_point10 = " Q" . (($fshoulder1 / 2) - (0.8 *$cIn)) . "," . $chestVertical . "," . (($fshoulder1 / 2) - (0.5 *$cIn)) . "," . ($chestVertical - (1 * $cIn));

        $front_point11 = "M" . ((($fshoulder/ 2) -0.5) * $cIn) . "," . ($chestVertical - (1 * $cIn)) . " Q" . (($fshoulder1 /2) - (0.9 * $cIn)) .",". //cx
                                (($fshoulder1 /2) - (1.8 * $cIn)) .",". //cy
                                ($fshoulder1 /2)  . "," . // X - Co-ordinate
                                $topPadding; // Y- Co-ordinate

        $front_point12 = " L" . ((($fshoulder / 2) -  $shoulder) * $cIn) . "," . $topMargin;
             
		$frontBlouseRed = $front_point1 . $front_point2 . $front_point4 . $front_point5 . $front_point6 . $front_point7 . $front_point8 . $front_point9 . $front_point10 . $front_point11 . $front_point12;

		$_SESSION["blouseFrontRed"] = $frontBlouseRed;

// -------------- red dotted line ends ---------------------------- //

		
    
// -------------------     Patti Design Start ---------------------

        $patti_point1 = "M" . $fLeft . "," . ($flength1 -(0.5 * $cIn));
		$front_point2 = " L" . $fLeft . "," . ($flength1 + (0.5 * $cIn));

        $patti_point3 = "Q" .    (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (1 * $cIn)) .",". 
                                (($fshoulder1 - ($shoulder1 * 2)) /2) .",". 
                                ($flength1 + (0.5 * $cIn));     
    
        $patti_point4 = " L" . ($waist1 / 4 ) . "," . ($flength1+(0.2 * $cIn));
        $patti_point5 = " L" . (($waist1 / 4 )) . "," . ($flength1 + (($blength - $flength) * $cIn));
        $patti_point6 = " L" . $fLeft . "," . ($flength1 + (($blength - $flength) * $cIn));

        $patti = $patti_point1 . $patti_point2 . $patti_point3 . $patti_point4 . $patti_point5 . $patti_point6 . "Z" ;
    
        $_SESSION["patti"] = $patti;

    // -------------------     Patti Design End -----------------------

// Front Left Tuck
		$frontLeftTucks =   "M" . $fLeft . "," .  (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["flTucks"] = $frontLeftTucks;
    
//Front Bottom Tuck
		$frontBottomTucks = "M" . ($hApex - (($bDart / 2) * $cIn)) . "," . ($flength1 + $seam) . "L" . ($hApex + (0.1 * $cIn)) .",". (($apex + 1.2) * $cIn) . "L" . ($hApex + (($bDart / 2) * $cIn)) . "," . ($flength1 + $seam);
    
        $_SESSION["fbTucks"] = $frontBottomTucks;
            
// Front Right Tuck                            
        $frontRight1 =  "M" . (($fshoulder1 / 2) - (0.5 * $cIn))  . "," . ($chestVertical - (0.25 * $cIn));

        switch ($apex) {
                case "5":
                    $frontRight2 =  "L" . ($hApex + (1 * $cIn)) . "," . (($apex + 0.5) * $cIn);;
                    break;
                case "6":
                    $frontRight2 =  "L" . ($hApex +(1 * $cIn)) . "," . ($apex * $cIn);
                    break;
                case "7":
                    $frontRight2 =  "L" . ($hApex +(1.2 * $cIn)) . "," . ($vApex -(1 * $cIn));
                    break;
                case "8":
                    $frontRight2 =  "L" . ($hApex +(1 * $cIn)) . "," . ($vApex -(1 * $cIn));
                    break;
                case "9":
                    $frontRight2 =  "L" . ($hApex +(1 * $cIn)) . "," . ($vApex -(1 * $cIn));
                    break;
                case "10":
                    $frontRight2 =  "L" . ($hApex +(0.5 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
                case "11":
                    $frontRight2 =  "L" . ($hApex +(0.7 * $cIn)) . "," . ($vApex -(1 * $cIn));
                    break;
                case "12":
                    $frontRight2 =  "L" . ($hApex +(0.6 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
                case "13":
                    $frontRight2 =  "L" . ($hApex +(0.6 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
                case "14":
                    $frontRight2 =  "L" . ($hApex +(0.6 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
                case "15":
                    $frontRight2 =  "L" . ($hApex +(0.6 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
                case "16":
                    $frontRight2 =  "L" . ($hApex +(0.6 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                    break;
        
                default:
                    $frontRight2 =  "L" . ($hApex +(0.5 * $cIn)) . "," . ($vApex -(1.2 * $cIn));
                };
    
        $frontRight3 = "L" . (($fshoulder1 / 2) - (0.1 * $cIn)) . "," . $chestVertical;
    
        $frontRightTucks = $frontRight1 . $frontRight2 . $frontRight3; 

        $_SESSION["frTucks"] = $frontRightTucks;    
	?>
	
	<svg width="600" height="450" viewbox = "-50, 0, 600, 450">
      <g>
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontBlouseGray;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontBlouseGreen;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontBlouseBrown;?>" />
        <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontLeftTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontRightTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $frontBottomTucks;?>" />
    	<path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
      </g>    	
    	
	<g>
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $frontBlouseRed;?>" />
    </g>
 	</svg>	
</div>