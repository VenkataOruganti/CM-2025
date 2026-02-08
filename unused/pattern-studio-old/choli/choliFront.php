<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="../choli/choliFrontDownload.php" style="color:#ffffff">Download Front Design</a>
        </button>
    </div>
    <div>
        <p>
            <ul>
                <li>This design is for reference only. Measure twice before you cut the cloth.</li>
                <li>The print / paper size : A3 (11" x 17" - Portrait)</li>
                <li>Do not cut the cloth, too close to the margins</li>
                <li>Left side is neck and fold.</li>
            </ul>
        </p>
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

/*
// full shoulder calculation:

        if (($fshoulder >= '12.99') && ($fshoulder <= '13.99')){
            $fshoulder = 10.5;             
        }elseif(($fshoulder >= '13.99')&&($fshoulder <= '14.99')){
            $fshoulder = 11;
        }elseif(($fshoulder >= '14.99')&&($fshoulder <= '15.99')){
            $fshoulder = 11.5;
        } elseif(($fshoulder >= '15.99')&&($fshoulder <= '16.99')){
            $fshoulder = 12;
        } else {
            $fshoulder = $fshoulder;
        }
*/
// Back Length
/*
        if (($bnDepth > '2') && ($bnDepth < '7')) {
                $fshoulder = ($fshoulder - (($bnDepth * 0.25) - 0.25));
        } else {
            $fshoulder = ($fshoulder - 1.5);
        }

        $fshoulder1 = $fshoulder * $cIn;
*/    
    // Blouse Sleeve center to Apex
		$vApex = (($apex + 0.5) * $cIn);
		$_SESSION["vApex"] = $vApex;
 
//  Bust Point Calculation
//  $hApex = ((($fshoulder/2) - $shoulder) * $cIn);
//  ((($fshoulder - ($shoulder * 2)) /2) * $cIn);

        if(($chest >= '30')&&($chest <= '32')) {
            $hApex = 3.25 * $cIn;
        } elseif (($chest > '32') && ($chest <= '35')) {
            $hApex = 3.5 * $cIn;
        } elseif (($chest > '35') && ($chest <= '38')) {
            $hApex = 3.75 * $cIn;
        }  elseif (($chest >= '39') && ($chest <= '41')) {
            $hApex = 4 * $cIn;
        } elseif (($chest >= '41') && ($chest <= '44')) {
             $hApex = 4.25 * $cIn;
        } else {
            $hApex = $hApex;
        }

	$_SESSION["hApex"] = $hApex;

// Front - Length adjustment //
/*
XS - 34 - 36
S - 36 - 38
M - 38 - 40
L - 40 - 41
*/
        if(($chest >= '30')&&($chest <= '35')) {
            $xLength = 1.25;
        } elseif (($chest > '35') && ($chest <= '38')) {
            $xLength = 1.5;
        } elseif (($chest > '38 ') && ($chest <= '44')) {
            $xLength = 2;
        } else {
            $xLength = 1;
        }    

// -------------- Blackline line start ----------------------------
      
		$fntPoint1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
		$fntPoint2 = " L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$fntPoint3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
		$fntPoint4 = "L" . $fLeft . "," . $fndepth1;
		$fntPoint5 = "L" . $fLeft . "," . ($apex * $cIn);
        $fntPoint6 = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);

        
 //     $fntPoinb6 = "Q". (($waist1 /4) /4) .",". ($flength1 + (0.5 * $cIn)) .",". $hApex .",". ($flength1 + (0.5 * $cIn));
        $fntPoint7 = " L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn); // low chest
		$fntPoint8 = " L" . ((($waist / 4) + 2) * $cIn) . "," . ((($blength + $xLength) - 0.5) * $cIn); // low chest
        $fntPoint8a = " L" . ((($chest / 4) + 1) * $cIn) . "," . (($apex+ 0.5) * $cIn); //arm hole D-E + K
        $fntPoint9 = " L" . ((($chest / 4) + 1) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    $fntPoint10 = " L" . ($fshoulder1 / 2) . "," . $chestVertical;
	//	$fntPoint11 = " L" . ($fshoulder1 / 2) . "," . ($chestVertical - (1 * $cIn));
		$fntPoint12 = " L" . ($fshoulder1 / 2) . "," . $seam ;
        
// Black line graphic (squares)

		$choliFrontBlouseGray = $fntPoint1 . $fntPoint2 . $fntPoint3 . $fntPoint4 . $fntPoint5 . $fntPoint6 . $fntPoint7 . $fntPoint8 . $fntPoint8a . $fntPoint9 . $fntPoint10 . $fntPoint12;

		$_SESSION["choliBlouseFront"] = $choliFrontBlouseGray;
        
// -------------- green dotted line start ----------------------------

		$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);
		$sg_point2 = "L" . ((($fshoulder1 / 2) - $shoulder1) + (0 * $cIn)) . "," . ($fndepth1 / 2);
		$sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
        $sg_point5 = "L" . $fLeft . "," . ($apex * $cIn);

        $sg_point5a = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);

/*        $sg_point6 = "Q" .   (($waist1 / 4) / 4 ) .",". 
                                ($flength1 + (0.5 * $cIn)) .",". 
                                $hApex .",". 
                                ($flength1 + (0.5 * $cIn)); 
*/    
        $sg_point7 = "L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn);
		$sg_point8 = "L" . ((($waist / 4) +2)* $cIn) . "," . ((($blength + $xLength) - 0.5) * $cIn); // low chest
		$sg_point8a = "L" . ((($chest / 4) +1)* $cIn) . "," . ($apex * $cIn); // low chest
		$sg_point9 = "L" . ((($chest / 4) +1) * $cIn) . "," . $chestVertical; //arm hole D-E + K
    
        $sg_point10 = "Q" . (($fshoulder1 / 2) - (0.8 *$cIn)) .",". $chestVertical .",". (($fshoulder1 / 2) - (0.3 *$cIn)) . "," . ($chestVertical/2);

        $sg_point11 = "L" . ($fshoulder1 /2) . "," . ($topPadding + (0.25 * $cIn)); // Y- Co-ordinate
        $sg_point12 = "L" . ((($fshoulder / 2) -  $shoulder) * $cIn) . "," . $topPadding;
	    
        $choliFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point4 . $sg_point5 . $sg_point5a . $sg_point6. $sg_point7 . $sg_point8 . $sg_point8a . $sg_point9 . $sg_point10 . $sg_point11 . $sg_point12 . "Z" ;

		$_SESSION["choliFrontBlouseGreen"] = $choliFrontBlouseGreen;

// -------------- green dotted line End ----------------------------

// -------------- brown dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 0.5)  * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = " L" . ((($chest / 4) + 1)  * $cIn) . "," . $chestVertical ; //arm hole D-E + K
	    
 //       $choliFrontBlouseBrown = $green_point8 . $green_point9;

		$_SESSION["choliFrontBlouseBrown"] = $choliFrontBlouseBrown;
         
// -------------- red dotted line start ----------------------------
		
		$fnt_point1 = "M". ((($fshoulder1 /2) -$shoulder1) -($seam)) . "," . $topMargin;
		$fnt_point2 = "L". ((($fshoulder1 /2) -$shoulder1) -($seam)) . "," . ($fndepth1 / 2);
		$fnt_point4 = "Q". ((($fshoulder1 /2) -$shoulder1) -$seam) .",". ($fndepth1 - $seam) .",". $fLeft .",". ($fndepth1 - $seam);
        $fnt_point5 = " L" . $fLeft . "," . ((($blength + $xLength) + 0.5) * $cIn);
/*        $fnt_point6 = "Q" .   (($waist1 / 4) / 4) .",". 
                                ($flength1 + (1 * $cIn)) .",". 
                                $hApex .",". 
                                (($flength + 1) * $cIn); 
*/    
		$fnt_point7 = "L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)+0.5) * $cIn); // low chest
		$fnt_point8 = "L" . (((($chest /4) +1))* $cIn) .",". ((($blength + $xLength)) * $cIn); // low chest

        $fnt_point9 = "L" . (((($chest /4) +0.5) +1.0)* $cIn) .",". ($chestVertical -(0.5 * $cIn)); //arm hole D-E + K

        $fnt_point10 = "Q" .((($fshoulder /2)- 1) *$cIn) .",". $chestVertical .",". ((($fshoulder/2) + 0.5) * $cIn) . "," . $topMargin;
             
		$choliFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point4 . $fnt_point5 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . "Z";

		$_SESSION["choliBlouseFrontRed"] = $choliFrontBlouseRed;

// -------------- red dotted line ends ---------------------------- //
		

// Front Left Tuck
		$choliFrontLeftTucks =   "M" . $fLeft . "," .  (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["choliFlTucks"] = $choliFrontLeftTucks;
    
//Front Bottom Tuck
        $cfBottomTuks01 = "M" . ($hApex - (1 * $cIn)) . "," . (($blength + $xLength) * $cIn); 
		$cfBottomTuks02 = "L" . ($hApex - (1 * $cIn)) . "," . ($flength1 + $seam+ (0.7 * $cIn)); 
        $cfBottomTuks03 = "L" . $hApex .",". (($apex + 1.2) * $cIn);
        $cfBottomTuks04 = "L" . ($hApex + (1 * $cIn)) . "," . ($flength1 + $seam + (0.7 * $cIn));
        $cfBottomTuks05 = "L" . ($hApex + (1 * $cIn)) . "," . (($blength + $xLength) * $cIn);

        $choliFrontBottomTucks = $cfBottomTuks01 . $cfBottomTuks02 . $cfBottomTuks03 . $cfBottomTuks04 . $cfBottomTuks05; 
        $_SESSION["choliFbTucks"] = $choliFrontBottomTucks;
            
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

        $frontRight3 = "L" . (($fshoulder1 / 2) + (1 * $cIn)) . "," . ($chestVertical - (0.75 * $cIn));
    
        $choliFrontRightTucks = $frontRight1 . $frontRight2 . $frontRight3; 
        $_SESSION["choliFrTucks"] = $choliFrontRightTucks;    

// Right Center Tuck

        $rCenter1 = "M". ($hApex + (2 * $cIn)). "," . ($vApex +(0 * $cIn));
        $rCenter2 = "L". ((($chest/4) +1) * $cIn). "," .  ($vApex + (0 * $cIn));
        $rCenter3 = "L". ((($chest/4) + 1) * $cIn). "," .  ($vApex + (0.5 * $cIn));
        $rCenter4 = "L" . ($hApex + (2 * $cIn)). "," . ($vApex +(0 * $cIn));

    $rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
    $_SESSION["rightFrTucks"] = $rightCenter;

	?>
	
	<svg width="600" height="450" viewbox = "-50, 0, 600, 450">
      <g>
        <path fill="none" stroke="#000000" stroke-width="0.3"  stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $choliFrontBlouseGray;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $choliFrontBlouseBrown;?>" />
        <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $choliFrontLeftTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $choliFrontRightTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $choliFrontBottomTucks;?>" />
     	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter;?>" />    	
    	<path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
      </g>    	
    	
	<g>
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $choliFrontBlouseRed;?>" />
    </g>
    <g>
        <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $choliFrontBlouseGreen;?>" />
    </g>
 	</svg>	
</div>
<!--
<div> front bottom dart width (chest - waist / 2): <?php echo $bDart;?>test</div>
<div>Full Shoulder <?php echo $fshoulder;?></div> -->