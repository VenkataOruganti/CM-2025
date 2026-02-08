<div>
    <div style="float: right;">
        <span>Download front-design</span>
        <button class="btn btn-info" >
        <a href="../savi/saviFrontDownload.php" style="color:#ffffff">SVG</a>
        </button>
		<button class="btn btn-info" >
        <a href="../savi/saviFrontDownloadPdf.php" style="color:#ffffff">PDF</a>
        </button>
    </div>
    <div style="margin-top: 15px">
            <ul>
                <li>Paper size : A3 (11" x 17" - Portrait)</li>
                <li>The dark line is  stitch line, please add margin as required</li>
                <li>Do not cut the cloth, too close to the margins</li>
                <li>Left side is neck and fold.</li>
            </ul>
    </div>

    <div>
        <?php include '../inc/deepNeckCV.php';?> 
    </div>

<?php        
    $topMargin = 0;
    $topPadding = (0.25 * $cIn); 
    $fLeft = 0;
    $seam = (0.3 * $cIn);

    $bDart = (($chest - $waist) / 2) ; //bottom Dart - front and back  
    $_SESSION["fbDart"] = $bDart;
    
    // Blouse Sleeve center to Apex
		$vApex = (($apex + 0.5) * $cIn);
		$_SESSION["vApex"] = $vApex;

    $bustVar = ($bust - ($chest /2))/2;
    
// center tuck calculation : Bust Variance - waist / 4 = (result / 2) + 0.5 (additional margin);  
    
    $legWidth = ($bustVar - ($waist/4));
    $legWidth = $legWidth / 2;

// Bust Point Calculation

        if(($bust >= '30')&&($bust <= '32')) {
            $hApex = 3.25 * $cIn;
        } elseif (($bust > '32') && ($bust <= '35')) {
            $hApex = 3.5 * $cIn;
        } elseif (($bust > '35') && ($bust <= '38')) {
            $hApex = 3.75 * $cIn;
        }  elseif (($bust >= '39') && ($bust <= '41')) {
            $hApex = 4 * $cIn;
        } elseif (($bust >= '41') && ($bust <= '44')) {
             $hApex = 4.25 * $cIn;
        } else {
            $hApex = $hApex;
        }

		$_SESSION["hApex"] = $hApex;


// -------------- gray dotted line start ----------------------------
      
		$fnt_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
		$fnt_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$fnt_point3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
		$fnt_point4 = "L" . $fLeft . "," . $fndepth1;
		$fnt_point5 = "L" . $fLeft . "," . (($flength - 1.0) * $cIn);
        
        $fnt_point6 = "Q" .   (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (0.5 * $cIn)) .",". 
                                $hApex .",". 
                                ($flength1 + (0.5 * $cIn));
  
		$fnt_point7 = "L" . ((($waist/4) + ($legWidth * 2)) * $cIn) . "," . (($flength - 0.5) * $cIn); // low chest
		$fnt_point8 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn)); //chest and Bust variation
		$fnt_point9 = "L" . (($chest / 4) * $cIn) . "," . $chestVertical; //arm hole D-E + K

	    $fnt_point10 = "L" . ($fshoulder1 / 2) . "," . $chestVertical;
		$fnt_point11 = "L" . ($fshoulder1 / 2) . "," . $seam ;

		$saviFrontBlouseGray = $fnt_point1 . $fnt_point2 . $fnt_point3 . $fnt_point4 . $fnt_point5 . $fnt_point6 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . $fnt_point11 . "Z";

		$_SESSION["saviBlouseFront"] = $saviFrontBlouseGray;
        
// -------------- Black line start ----------------------------

		$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);
    
    if ($bnDepth <= '4.5') {
    //  $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
    } else {
    	$sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
    }
	
        $sg_point5 = "L" .$fLeft . "," . ($apex * $cIn);
        $sg_point5a = "L" . $fLeft . "," . ((($flength - 1.0) * $cIn));

        $sg_point6 = "Q" .   (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (0.5 * $cIn)) .",". 
                                $hApex .",". 
                                ($flength1 + (0.5 * $cIn)); 
    
        $sg_point7 = "L" . ((($waist/4) + ($legWidth * 2)) * $cIn)  . "," . (($flength - 0.5 )* $cIn); // waist
        $sg_point8 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn)); // Bust
		$sg_point9 = "L" . ((($chest / 4)+0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K

// Front Shoulder bottom curve

            if(($chest > '28')&&($chest <= '32')){
                $frontChestVertical = $chestVertical/2;
            } elseif(($chest > '32')&&($chest <= '38')){
                $frontChestVertical = ($chestVertical/2 + (0.5 * $cIn));
            } elseif($chest > '38'){
                $frontChestVertical = ($chestVertical/2 + (1 * $cIn));
            }

        $sg_point10 = "Q" . (($fshoulder1 / 2) - (0.4 *$cIn)) .",". ($chestVertical+ (0.2 * $cIn)) .",". (($fshoulder1 / 2) - (0.4 *$cIn)) . "," . $frontChestVertical;
        $sg_point11 = "L" . ($fshoulder1 /2) . "," . ($topPadding + (0.25 * $cIn)); // Y- Co-ordinate
        $sg_point12 = "L" . ((($fshoulder / 2) -  $shoulder) * $cIn) . "," . $topPadding;
	    
        $saviFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point4 . $sg_point5 . $sg_point5a . $sg_point6 . $sg_point7 .$sg_point8 . $sg_point9 . $sg_point10 . $sg_point11 . $sg_point12 . "Z" ;

		$_SESSION["saviFrontBlouseGreen"] = $saviFrontBlouseGreen;

// -------------- Black line End ----------------------------

// -------------- brown dotted line start ----------------------------

		$green_point8 = "M" . ((($chest / 4) + 0.5)  * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = "L" . ((($chest / 4) + 1)  * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $saviFrontBlouseBrown1 = $green_point8 . $green_point9;

		$_SESSION["saviFrontBlouseBrown"] = $saviFrontBlouseBrown1;
         
// -------------- red dotted line start ----------------------------
		
		$fnt_point1 = "M" . ((($fshoulder1 / 2) - $shoulder1) - ($seam)) . "," . $topMargin;    
    
        if ($bnDepth <= '4.5') {
            $fnt_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
            $fnt_point3 = "Q" . (($fshoulder1 / 2) - ($shoulder1 + (1.5 * $cIn))) .",". $fndepth1 ."," . $seam .",". $fndepth1; 
        } else {
            $fnt_point2 = "L" . (($fshoulder1 / 2) - ($shoulder1 +(0.3 * $cIn))) . "," . ($fndepth1 / 2);
            $fnt_point3 = "Q" . ((($fshoulder1 / 2) - $shoulder1)-(0.5 * $cIn)) .",". $fndepth1 ."," . ($seam -(0.3 * $cIn)).",". ($fndepth1 - (0.3 * $cIn));
        }        
        $fnt_point4 = "L" . $fLeft . "," . ($flength1 - (0.5 * $cIn));
        $fnt_point5 = "Q" .   (($waist1 / 4) / 4) .",". 
                                ($flength1 + (1 * $cIn)) .",". 
                                $hApex .",". 
                                (($flength + 1) * $cIn); 
    
		$fnt_point6 = "L" . ((($waist/4) + ($legWidth * 2) + 0.5) * $cIn) . "," . ($flength * $cIn); // low chest
		$fnt_point7 = "L" . (($bustVar + 0.5) * $cIn) . "," . $vApex; // low chest
		$fnt_point8 = "L" . ((($chest /4) + 1)* $cIn) .",". ($chestVertical - (0.5 * $cIn)); //arm hole D-E + K
        $fnt_point9 = "Q" .((($fshoulder /2) - 1.5) * $cIn) .",". ($chestVertical + (0.5 * $cIn)) .",". ((($fshoulder/2) + 0.5) * $cIn) . "," . $topMargin;
             
		$saviFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point3 . $fnt_point4 . $fnt_point5 . $fnt_point6 . $fnt_point7 . $fnt_point8 . $fnt_point9 . "Z";

		$_SESSION["saviBlouseFrontRed"] = $saviFrontBlouseRed;

// -------------- red dotted line ends ---------------------------- //
		
// Front Left Tuck
		$saviFrontLeftTucks = "M" . $fLeft . "," . (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["saviFlTucks"] = $saviFrontLeftTucks;
    
//Front Bottom Tuck
// center tuck calculation : Bust Variance - waist / 4 = (result / 2) + 0.5 (additional margin);  
//        $legWidth = ($bustVar - ($waist/4)) + 0.5;
    
        $legWidth = $legWidth + 0.25;
        $saviFBT01 = "M" . ($hApex - ($legWidth * $cIn)) . "," . ($flength1 + $seam);
        $saviFBT02 = "L" . ($hApex + (0.1 * $cIn)) .",". (($apex + 1.0) * $cIn);
        $saviFBT03 = "L" . ($hApex + ($legWidth * $cIn)) . "," . ($flength1 + $seam);

        $saviFrontBottomTucks = $saviFBT01 . $saviFBT02 . $saviFBT03;    
        $_SESSION["saviFbTucks"] = $saviFrontBottomTucks;
            
// Front Right Tuck                            
        $frontRight1 =  "M" . (($fshoulder1 / 2) + (0.5 * $cIn))  . "," . ($chestVertical - (1 * $cIn));

        switch ($apex) {
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
                    $frontRight2 =  "L" . ($hApex +(1 * $cIn)) . "," . ($vApex - (1 * $cIn));
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
        
                default:
                    $frontRight2 =  "L" . ($hApex + (0.5 * $cIn)) . "," . (($apex - 1) * $cIn);    
                };

        $frontRight3 = "L" . (($fshoulder1 / 2) + (1 * $cIn)) . "," . ($chestVertical - (0.6 * $cIn));
    
        $saviFrontRightTucks = $frontRight1 . $frontRight2 . $frontRight3; 
        $_SESSION["saviFrTucks"] = $saviFrontRightTucks;    

// Right Center Tuck

        $rCenter1 = "M". ($hApex + (2 * $cIn)). "," . ($vApex - (0.25 * $cIn));
        $rCenter2 = "L". ($bustVar * $cIn). "," . ($vApex - (0.25 * $cIn));
        $rCenter3 = "M". ($bustVar * $cIn). "," . ($vApex + (0.25 * $cIn));
        $rCenter4 = "L" . ($hApex + (2 * $cIn)). "," . ($vApex - (0.25 * $cIn));

    $rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
    $_SESSION["rightFrTucks"] = $rightCenter;

?>
	<svg width="600" height="450" viewbox = "-50, 0, 600, 450">
  <g>
    <path fill="none" stroke="#000" stroke-width="0.3"  stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGray;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseBrown1;?>" />
    <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontLeftTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontRightTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontBottomTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter;?>" />    	
    <path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
  </g>    	
    	
	<g>
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseRed;?>" />
    </g>
    <g>
        <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGreen;?>" />
    </g>
 	</svg>	
</div>

<!--
    <div>Leg Width: <?php echo $legWidth;?></div>
    <div> Leg Width: <?php echo $legWidth;?></div>

    <div>After BackNeck Depth check <?php echo $cShoulder;?></div>
    <div>Full Shoulder <?php echo $fshoulder;?></div>
    <div>chest vertical <?php echo $chestVertical;?></div>
  -->