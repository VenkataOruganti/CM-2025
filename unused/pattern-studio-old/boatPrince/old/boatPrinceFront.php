<div>
    <div style="float: right;">
	 <span>Download front-design</span>
        <button class="btn btn-info" >
        <a href="../boatPrince/boatPrinceFrontDownload.php" style="color:#ffffff">SVG</a>
        </button>
		<button class="btn btn-info" >
        <a href="../boatPrince/boatPrinceFrontDownloadPdf.php" style="color:#ffffff">PDF</a>
        </button>
    </div>
    <div>
        <p>
            <ul>
                <li>Paper size : A3 (11" x 17" - Portrait)</li>
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

        $bustVar = ($bust - ($chest /2))/2;
    
// center tuck calculation : Bust Variance - waist / 4 = (result / 2) + 0.5 (additional margin);  
    
        $legWidth = ($bustVar - ($waist/4));
        $legWidth = $legWidth / 2;

        $_SESSION["fbDart"] = $bDart;

// Blouse Sleeve center to Apex
		$vApex = (($apex + 0.5) * $cIn);
		$_SESSION["vApex"] = $vApex;
 
//  Bust Point Calculation

        $bust = $_SESSION["chest"];
        $bust = $chest;

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

// Front - Length adjustment //

        if(($chest >= '30')&&($chest <= '35')) {
            $xLength = 1.25;
        } elseif (($chest > '35') && ($chest <= '38')) {
            $xLength = 1.5;
        } elseif (($chest > '38 ') && ($chest <= '44')) {
            $xLength = 2;
        } else {
            $xLength = 1;
        }    

// -------------- gray line start ----------------------------
      
		$fntPoint1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . ($topMargin + $topPadding);
		$fntPoint2 = " L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$fntPoint3 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . $fndepth1;
		$fntPoint4 = "L" . $fLeft . "," . $fndepth1;
		$fntPoint5 = "L" . $fLeft . "," . ($apex * $cIn);
        $fntPoint6 = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);

        $fntPoint7 = "L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn); // low chest
		$fntPoint8 = "L" . ((($waist/4) + ($legWidth * 2)) * $cIn) .",". ((($blength + $xLength) - 0.5) * $cIn); //low chest
        $fntPoint8a = "L" . ($bustVar * $cIn) . "," . $apex1; //bust point
        
        $fntPoint9 = "L" . ((($chest / 4) + 0) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    $fntPoint10 = "L" . ($fshoulder1 / 2) . "," . $chestVertical;
		$fntPoint12 = "L" . ($fshoulder1 / 2) . "," . $seam ;
        
// Black line graphic (squares)

		$princeFrontBlouseGray = $fntPoint1 . $fntPoint2 . $fntPoint3 . $fntPoint4 . $fntPoint5 . $fntPoint6 . $fntPoint7 . $fntPoint8 . $fntPoint8a . $fntPoint9 . $fntPoint10 . $fntPoint12;

		$_SESSION["princeBlouseFront"] = $princeFrontBlouseGray;
        
// -------------- Black stitch line start ----------------------------

		$sg_point1 = "M" . (($fshoulder1 / 2) - $shoulder1) . "," . (0.5 * $cIn);
		$sg_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
		$sg_point6 = "";  // Initialize to empty (PHP 8+ compatibility)

// princess-cut & Deepneck logic
        if ($bnDepth <='4.5') {
            //	$sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
                $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
        } else {
                $sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
                $sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
        }

//		$sg_point2 = "L" . (($fshoulder1 / 2) - $shoulder1) . "," . ($fndepth1 / 2);
		$sg_point4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . $seam .",". $fndepth1;
        $sg_point5 = "L" . $fLeft . "," . ($apex * $cIn);

        $sg_point5a = "L" . $fLeft . "," . (($blength + $xLength) * $cIn);
        $sg_point7 = " L" . (($hApex) + (1 * $cIn)) . "," . ((($blength + $xLength)) * $cIn); // low chest
   
		$sg_point8 = "L" . ((($waist/4) + ($legWidth * 2)) * $cIn) .",". ((($blength + $xLength) - 0.5) * $cIn); //low waist
		$sg_point8a = "L" . ($bustVar * $cIn) . "," . ($vApex +(0 * $cIn)); // low chest
        $sg_point9 = " L" . ((((($chest / 4))) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K

// Front Shoulder bottom curve

            if(($chest > '28')&&($chest <= '32')){
                $frontChestVertical = $chestVertical/2;
            } elseif(($chest > '32')&&($chest <= '38')){
                $frontChestVertical = ($chestVertical/2 + (1 * $cIn));
            } elseif($chest > '38'){
                $frontChestVertical = ($chestVertical/2 + (1.5 * $cIn));
            }

        $sg_point10 = "Q" . (($fshoulder1 / 2) - (0.5 *$cIn)) .",". $chestVertical .",". (($fshoulder1 / 2) - (0.4 *$cIn)) . "," . $frontChestVertical;

        $sg_point11 = "L" . ($fshoulder1 /2) . "," . ($topPadding + (0.25 * $cIn)); // Y- Co-ordinate
        $sg_point12 = "L" . ((($fshoulder / 2) - $shoulder) * $cIn) . "," . $topPadding;
	    
        $princeFrontBlouseGreen = $sg_point1 . $sg_point2 . $sg_point4 . $sg_point5 . $sg_point5a . $sg_point6. $sg_point7 . $sg_point8 . $sg_point8a . $sg_point9 . $sg_point10 . $sg_point11 . $sg_point12 . "Z" ;

		$_SESSION["princeFrontBlouseGreen"] = $princeFrontBlouseGreen;

// -------------- brown dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 0.5)  * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = " L" . ((($chest / 4) + 1)  * $cIn) . "," . $chestVertical ; //arm hole D-E + K

        $princeFrontBlouseBrown = $green_point8 . $green_point9;

		$_SESSION["princeFrontBlouseBrown"] = $princeFrontBlouseBrown;
         
// -------------- red dotted line start ----------------------------

		$fnt_point1 = "M". ((($fshoulder1 /2) -$shoulder1) -($seam)) . "," . $topMargin;
		$fnt_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
		$fnt_point7 = "";  // Initialize to empty (PHP 8+ compatibility)
		$fnt_point4 = "Q". ((($fshoulder1 /2) -$shoulder1) -$seam) .",". ($fndepth1 - $seam) .",". $fLeft .",". ($fndepth1 - $seam);
        $fnt_point5 = " L" . $fLeft . "," . ((($blength + $xLength) + 0.5) * $cIn);

		$fnt_point8 = "L" . (((($bust /4) +1)+0.5)* $cIn) .",". ((($blength + $xLength)) * $cIn); // low chest
        $fnt_point9 = "L" . (((($chest /4) +0.5) +1.0)* $cIn) .",". ($chestVertical -(0.5 * $cIn)); //arm hole D-E + K

        $fnt_point10 = "Q" .((($fshoulder /2)- 1) *$cIn) .",". $chestVertical .",". ((($fshoulder/2) + 0.5) * $cIn) . "," . $topMargin;

		$princeFrontBlouseRed = $fnt_point1 . $fnt_point2 . $fnt_point4 . $fnt_point5 . $fnt_point7 . $fnt_point8 . $fnt_point9 . $fnt_point10 . "Z";

		$_SESSION["princeBlouseFrontRed"] = $princeFrontBlouseRed;

// -------------- red dotted line ends ---------------------------- //
		

// Front Left Tuck
		$princeFrontLeftTucks =   "M" . $fLeft . "," .  (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["princeFlTucks"] = $princeFrontLeftTucks;
    
/* Front Bottom Tuck
        $cfBottomTuks01 = "M" . ($hApex - (1 * $cIn)) . "," . (($blength + $xLength) * $cIn); 
		$cfBottomTuks02 = "L" . ($hApex - (1 * $cIn)) . "," . ($flength1 + $seam+ (0.7 * $cIn)); 
        $cfBottomTuks03 = "L" . $hApex .",". (($apex + 1.2) * $cIn);
        $cfBottomTuks04 = "L" . ($hApex + (1 * $cIn)) . "," . ($flength1 + $seam + (0.7 * $cIn));
        $cfBottomTuks05 = "L" . ($hApex + (1 * $cIn)) . "," . (($blength + $xLength) * $cIn);

//      $princeFrontBottomTucks = $cfBottomTuks01 . $cfBottomTuks02 . $cfBottomTuks03 . $cfBottomTuks04 . $cfBottomTuks05; 
        $_SESSION["princeFbTucks"] = $princeFrontBottomTucks; 
*/
// Right Center Tuck

        $rCenter1 = "M". ($hApex + (2 * $cIn)). "," . ($vApex +(0 * $cIn));
        $rCenter2 = "L". ($bustVar * $cIn). "," .  ($vApex + (0 * $cIn));
        $rCenter3 = "M". ($bustVar  * $cIn). "," .  ($vApex + (0.5 * $cIn));
        $rCenter4 = "L" . ($hApex + (2 * $cIn)). "," . ($vApex +(0 * $cIn));

        $rightCenter = $rCenter1 . $rCenter2 . $rCenter3 . $rCenter4;
        $_SESSION["rightFrTucks"] = $rightCenter;

// Front - GrayCurves

        $legWidth = $legWidth + 0.25;

        $pcGray1 = "M". ($hApex - ($legWidth * $cIn)) . "," . (($blength + $xLength) * $cIn); 
        $pcGray2 = "Q". ($hApex - ($legWidth * $cIn)) .",". (($apex + 4) * $cIn) . "," . $hApex .",". ($apex * $cIn);
        $pcGray3 = "Q". ($hApex + ($legWidth * $cIn)) . "," . (($apex - 2.5)*$cIn). "," .(($fshoulder1 / 2) + (0.5 * $cIn)) .",". ($chestVertical - (1 * $cIn));

        $pcGray4 = "M". ($hApex + ($legWidth * $cIn)) .",". (($blength + $xLength) * $cIn); 
        $pcGray5 = "L". ($hApex + ($legWidth * $cIn)) .",". ($flength1 + $seam+ (0.7 * $cIn));    
        $pcGray6 = "Q". (($hApex) -0.5*$cIn) .",". (($apex +1) * $cIn) . "," . $hApex .",". ($apex * $cIn);
        $pcGray7 = "Q". (($hApex)+ 1 *$cIn) .",". (($apex - 2)*$cIn).",".(($fshoulder1 /2)+(1.0 * $cIn)).",". ($chestVertical - (0.5 * $cIn));

        $princeCurveGray = $pcGray1 . $pcGray2 . $pcGray3 . $pcGray4 . $pcGray5 . $pcGray6 . $pcGray7;
        $_SESSION["princeCurveGray"] = $princeCurveGray;
	?>
	
	<svg width="600" height="450" viewbox = "-50, 0, 600, 450">
      <g>
        <path fill="none" stroke="#000000" stroke-width="0.3"  stroke-dasharray="3, 5, 3" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseGray;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseBrown;?>" />
        <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeFrontLeftTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeFrontRightTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeFrontBottomTucks;?>" />
     	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $rightCenter;?>" />    	
    	<path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
      </g>    	
    	
	<g>
		<path fill="none" stroke="#ff0000" stroke-dasharray="5,5" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseRed;?>" />
    </g>
    <g>       
        <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeCurveGray;?>" />
    </g>
    <g>
        <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeFrontBlouseGreen;?>" />
    </g>
 	</svg>	
</div>
<!--
    <div> front bottom dart width (chest - waist / 2): <?php echo $bDart;?>test</div>
    <div>Full Shoulder <?php echo $fshoulder;?></div>
    <div>chest <?php echo $chest;?></div>
    <div>bust <?php echo $bust;?></div>
  -->