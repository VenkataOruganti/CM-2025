<div>
    <div style="float: right;">
	<span>Download front-design</span>
        <button class="btn btn-info" >
        <a href="kurtiFrontDownload.php" style="color:#ffffff">SVG</a>
        </button>
		<button class="btn btn-info" >
        <a href="kurtiFrontDownloadPdf.php" style="color:#ffffff">PDF</a>
        </button>
    </div>
    <div>
            <ul>
                <li>paper size : A3 (11" x 17" - Portrait)</li>
                <li>Do not cut the cloth, too close to the margins</li>
                <li>Left side is neck and fold.</li>
            </ul>
    </div>

    <div>
        <?php include '../../inc/deepNeckCV.php';?> 
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
      
		$kPoint1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
		$kPoint2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fnDepth1 / 2);
		$kPoint3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fnDepth1;
		$kPoint4 = "L" . $fLeft . "," . $fnDepth1;
		$kPoint5 = "L" . $fLeft . "," . ($flength * $cIn);
		$kPoint6 = "L" . (($bottom/4) * $cIn) . "," . ($flength * $cIn);
        $kPoint7 = "L" . (($hip/4) * $cIn) . "," . ($hlength * $cIn);
        $kPoint8 = "L" . (($waist / 4) * $cIn) . "," . ($wlength * $cIn);    
		$kPoint9 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn)); //chest and Bust variation
		$kPoint10 = "L" . ((($chest / 4)+0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    $kPoint11 = "L" . ($fshoulder1 / 2) . "," . $chestVertical;
		$kPoint12 = "L" . ($fshoulder1 / 2) . "," . $seam ;

		$saviFrontBlouseGray = $kPoint1 . $kPoint2 . $kPoint3 . $kPoint4 . $kPoint5 . $kPoint6 . $kPoint7 . $kPoint8 . $kPoint9 . $kPoint10 . $kPoint11 . $kPoint12 . "Z";

		$_SESSION["saviBlouseFront"] = $saviFrontBlouseGray;
        
// -------------- Black line start ----------------------------

		$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);
    
        if ($bnDepth <= '4.5') {
  //      $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		  $sg_point3 = "Q" . ((($fshoulder1 / 2) - $shoulder1)) .",". $fnDepth1 ."," . $seam .",". $fnDepth1;      
        } else {
    	   $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fnDepth1 / 2);
		  $sg_point3 = "Q" . ((($fshoulder1 / 2) - $shoulder1)) .",". $fnDepth1 ."," . $seam .",". $fnDepth1;
        }
	
        $sg_point4 = "L" . $fLeft . "," . ($apex * $cIn);
        $sg_point5 = "L" . $fLeft . "," . ($flength * $cIn);    
        $sg_point6 = "L" . (($bottom / 4) * $cIn) . "," . ($flength * $cIn);
        $sg_point7 = "L" . (($hip / 4) * $cIn) . "," . ($hlength * $cIn);
        $sg_point8 = "Q" . ((($waist / 4) - 0.5) * $cIn) .",". ($wlength * $cIn) ."," . (($waist / 4) * $cIn) . "," . ($wlength * $cIn);
//        $sg_point8a = "L" . (($waist / 4) * $cIn) . "," . (($wlength -0.5) * $cIn);
        $sg_point9 = "L" . ($bustVar * $cIn) . "," . ($vApex - (0.25 * $cIn)); // low chest

		$sg_point10 = "L" . ((($chest / 4)+0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K

// Front Shoulder bottom curve

            if(($chest > '28')&&($chest <= '32')){
                $frontChestVertical = $chestVertical/2;
            } elseif(($chest > '32')&&($chest <= '38')){
                $frontChestVertical = ($chestVertical/2 + (0.5 * $cIn));
            } elseif($chest > '38'){
                $frontChestVertical = ($chestVertical/2 + (1 * $cIn));
            }

        $sg_point11 = " Q" . (($fshoulder1 / 2) - (0.4 *$cIn)) .",". ($chestVertical+ (0.2 * $cIn)) .",". (($fshoulder1 / 2) - (0.4 *$cIn)) . "," . $frontChestVertical;

        $sg_point12 = " L" . ($fshoulder1 /2) . "," . ($topPadding + (0.25 * $cIn)); // Y- Co-ordinate
        $sg_point13 = " L" . ((($fshoulder / 2) -  $shoulder) * $cIn) . "," . $topPadding;
	    
        $saviFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point3 . $sg_point4 . $sg_point5 . $sg_point6 . $sg_point7 . $sg_point8 . $sg_point8a . $sg_point9 .$sg_point10 . $sg_point11 . $sg_point12 . $sg_point13 . "Z" ;

		$_SESSION["saviFrontBlouseGreen"] = $saviFrontBlouseGreen;

// -------------- Black line End ----------------------------

// -------------- brown dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 0.5)  * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = " L" . ((($chest / 4) + 1)  * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $saviFrontBlouseBrown1 = $green_point8 . $green_point9;

		$_SESSION["saviFrontBlouseBrown"] = $saviFrontBlouseBrown;
         
// -------------- red dotted line start ----------------------------
		
		$fnt_point1 = "M" . ((($fshoulder1 / 2) - $shoulder1) - ($seam)) . "," . $topMargin;
		$fnt_point2 = "L" . ((($fshoulder1 / 2) - $shoulder1) - ($seam)) . "," . ($fnDepth1 / 2);
		$fnt_point3 = "Q" . ((($fshoulder1 / 2) - $shoulder1) - $seam) .",". ($fnDepth1 - $seam) ."," . $fLeft .",". ($fnDepth1 - $seam);
        $fnt_point4 = " L" . $fLeft . "," . (($flength +0.5) * $cIn);
        $fnt_point5 = "L" . ((($bottom / 4) + 0.5) * $cIn) . "," . (($flength +0.5) * $cIn);
        $fnt_point6 = "L" . ((($hip / 4) + 0.5) * $cIn) . "," . ($hlength * $cIn);    
		$fnt_point7 = "L" . ((($waist /4) + 0.5) * $cIn) . "," . ($wlength * $cIn); // low chest
		$fnt_point8 = "L" . (($bustVar + 0.5) * $cIn) . "," . $vApex; // low chest

		$fnt_point9 = "L" . ((($chest /4) + 1)* $cIn) .",". ($chestVertical - (0.5 * $cIn)); //arm hole D-E + K

        $fnt_point10 = "Q" .((($fshoulder /2) - 1.5) * $cIn) .",". ($chestVertical + (0.5 * $cIn)) .",". ((($fshoulder/2) + 0.5) * $cIn) . "," . $topMargin;
             
		$saviFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point3 . $fnt_point4 . $fnt_point5 . $fnt_point6 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . "Z";

		$_SESSION["saviBlouseFrontRed"] = $saviFrontBlouseRed;

// -------------- red dotted line ends ---------------------------- //

/* Front Left Tuck
		$saviFntLtTucks =   "M" . $fLeft . "," .  (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["saviFlTucks"] = $saviFntLtTucks;
*/    
//Front Bottom Tuck
		$saviFrontBottomTucks1 = "M" . ($hApex - (($bDart / 2) * $cIn)) . "," . ($flength1 + $seam+ (0.7 * $cIn)) . "L" . ($hApex + (0.1 * $cIn)) .",". (($apex + 1.2) * $cIn) . "L" . ($hApex + (($bDart / 2) * $cIn)) . "," . ($flength1 + $seam + (0.7 * $cIn));
    
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
        $rCenter2 = "L". ($bustVar * $cIn). "," .  ($vApex - (0.25 * $cIn));
        $rCenter3 = "M". (($bustVar -0.2) * $cIn). "," .  ($vApex + (0.25 * $cIn));
        $rCenter4 = "L". ($hApex + (2 * $cIn)). "," . ($vApex - (0.25 * $cIn));

    $rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
    $_SESSION["rightFrTucks"] = $rightCenter;

?>

	<svg width="600" height="1250" viewbox = "-50, 0, 600, 1250">
  <g>
    <path fill="none" stroke="#000" stroke-width="0.3"  stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGray;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseBrown;?>" />
    <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontLeftTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontRightTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviFrontBottomTucks;?>" />
    <path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter;?>" />    	
    <path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
  </g>    	
    	
	<g>
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseRed;?>" />
           <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $saviFrontBlouseGreen;?>" />
    </g>
    <g>

    </g>
 	</svg>	
</div>

<!--
<div>Bust Variation : <?php echo $bustVar;?></div>
<div> Quarter Chest Value: <?php echo $qChest;?></div>

<div>After BackNeck Depth check <?php echo $cShoulder;?></div>
<div>Full Shoulder <?php echo $fshoulder;?></div>
<div>chest vertical <?php echo $chestVertical;?></div>
  -->