<div>
    <div style="float: right;">
	 <span>Download front-design</span>
        <button class="btn btn-info" >
            <a href="../boatPrince/boatPrinceBackDownload.php" style="color:#ffffff">SVG</a>
        </button>
		<button class="btn btn-info" >
        <a href="../boatPrince/boatPrinceFrontDownloadPdf.php" style="color:#ffffff">PDF</a>
        </button>
    </div>

    <p>
        <ul>
            <li>This design for reference only, measure it before you use it. </li>
            <li>The print size is : A3 ( 11" x 17" - portrait)</li>
            <li>Do not cut the cloth, too close to the margins</li>
            <li>Left side is neck depth and fold.</li>
        </ul>
    </p>
    
	<?php

    if ($bnDepth =="") {
			$bnDepth = 0;
		}

        $seam = 0.3 * $cIn;
        $neckSeam = 0.2;
        $backDart = 1;
//        $fshoulder = $_SESSION[fshoulder];

        $backTuckText = $_SESSION["cust"];

        if (($bnDepth > '4') && ($bnDepth < '7')) {
            $fshoulder = ($fshoulder - (($bnDepth * 0.25) - 0.25));    
        } else {
            $fshoulder = ($fshoulder - 1.5);    
        }
    
/* --- blouse back labels --*/
    
        $_SESSION["bChestLabel"] = $chestLabel = ((($chest /4) - 3.5) * $cIn); // chest width
        $_SESSION["bBackHeight"] = $backHeight = ($blength * $cIn);
    
        $_SESSION["bChestLabel0"] = $chestLabel05 = (($chest /4)   * $cIn); // chest width
        $_SESSION["bChestLabel05"] = $chestLabel10 = ((($chest /4) + 0.5) * $cIn); // chest width
        $_SESSION["bChestLabel10"] = $chestLabel15 = ((($chest /4) + 1.0) * $cIn); // chest width
        $_SESSION["bChestLabel15"] = $chestLabel20 = ((($chest /4) + 1.5) * $cIn); // chest width       
    
/* --- blouse back labels --*/

// -------------- Gray line design ----------//
    
		$back_bnDepth = $point1 = $point2 = $point3 = $point4 = $point5 = $point6 = $point7 = $point8 = $point9 = $point10 = $mLeft = $topMargin = $point6a = $point6b = $point7a = 0;

		$mLeft = 1;
		$topMargin = 0;

		$bbPoint1 = "M" . (((( $fshoulder/ 2) - $shoulder) + $mLeft) * $cIn) .",". $topPadding; 
		$bbPoint2 = "L" . (((( $fshoulder/ 2) - $shoulder) + $mLeft) * $cIn) .",". ($topMargin + ($bnDepth/2) * $cIn);
		$bbPoint3 = "L" . (((( $fshoulder/ 2) - $shoulder) + $mLeft) * $cIn) .",". ($topMargin + ($bnDepth * $cIn));	
		$bbPoint4 = "L" . $seam .",". (($topMargin + $bnDepth) * $cIn);
		$bbPoint5 = "L" . $seam .",". ((($topMargin + $blength) +0.5) * $cIn);
		$bbPoint6 = "L". (((($waist / 4)+ $backDart) +$mLeft)* $cIn) .",". ((($topMargin+$blength)+0.5)* $cIn);
		$bbPoint7 = "L" . ((($chest / 4) + $mLeft ) * $cIn) .",". $chestVertical;	
		$bbPoint8 = "L" . ((($fshoulder / 2) + $mLeft) * $cIn) .",". $chestVertical;
		$bbPoint9 = "L" . ((($fshoulder / 2) + $mLeft) * $cIn) .",". (($fshoulder / 4) * $cIn);
		$bbPoint10 = "L" . ((($fshoulder / 2) + $mLeft) * $cIn) .",". ($topPadding * 2);
		
		$princeBackBlack = $bbPoint1 . $bbPoint2 . $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
    
        $_SESSION["princeBackBlack"] = $princeBackBlack;

// -------------- Black line design ----------//

		$sb_point1 = "M" . ((((( $fshoulder / 2) - $shoulder) + $mLeft) * $cIn)) .",". (0.3 * $cIn);
		$sb_point2 = "";  // Initialize to empty (PHP 8+ compatibility)
		$sb_point3 = "Q" . (((($fshoulder/2) -$shoulder) + $mLeft) * $cIn ). "," . ($bnDepth * $cIn).",". $seam .",". ($bnDepth * $cIn);
        $sb_point4 = "L" . $seam .",".($bnDepth * $cIn);
		$sb_point5 = "M" . $seam .",". ((($topMargin + $blength) + 0.5) * $cIn);
		$sb_point6 = "L" . ((((($waist / 4) + $backDart) ) * $cIn) + $seam) .",". (((($topMargin + $blength) + 0.5) ) * $cIn);
		$sb_point7 = "L" . (((($chest / 4) ) * $cIn)+$seam ).",". $chestVertical; // Seam Allowence
     
        $sb_point7a = "L" . (($chest / 4) * $cIn) .",". $chestVertical; // curve calculation

		$sb_point8 = "Q" . ((($fshoulder / 2)+ $mLeft) * $cIn) . "," . $chestVertical .",". ((($fshoulder / 2) + $mLeft) * $cIn) .",". (($chestVertical/2) + (0.2*$cIn)) ;

		$sb_point9 = "L" . ((($fshoulder / 2) + $mLeft) * $cIn) .",". (($fshoulder / 4) * $cIn);
		$sb_point10 = "L" . ((($fshoulder / 2) + $mLeft ) * $cIn) .",". (0.5 * $cIn);
        $sb_point11 = "L" . (((( $fshoulder / 2 ) - $shoulder) + $mLeft) * $cIn ) .",". (0.25 * $cIn);

        $princeBackGreen = $sb_point1 . $sb_point2 . $sb_point3 . $sb_point5 . $sb_point6 . $sb_point7 . $sb_point7a . $sb_point8 . $sb_point9 . $sb_point10 . $sb_point11;
    
        $_SESSION["princeBackGreen"] = $princeBackGreen;
    
// -------------- brown line - extra Bust design ----------//

        $brownPoint6 = "M" . (((($waist / 4) + 0.5) * $cIn)+ $seam) .",". ((($topMargin + $blength)+ 0.5) * $cIn);
		$brownPoint7 = "L" . (((($chest / 4) - 0.5) * $cIn) + $seam) .",". $chestVertical;	

        $princeBackBrown = $brownPoint6 . $brownPoint7;
    
        $_SESSION["princeBackBrown"] = $princeBackBrown;

// -------------- Red line Design ----------- //

        $point1 = "M" . ((((($fshoulder / 2) - $shoulder) +$mLeft) * $cIn) - $seam) .",". $topMargin; 
//		$point2 = "L" . ((((($fshoulder / 2) - $shoulder) + $mLeft) * $cIn) - $seam) .",". ($topMargin + ($bnDepth / 2) * $cIn);
		$point3 = "Q". ((((($fshoulder/2)-$shoulder) +$mLeft) * $cIn) - $seam)."," .($bnDepth * $cIn).",".$seam .",".(($bnDepth * $cIn) - $seam);
        $point4 = "L" . $seam .",".($bnDepth * $cIn);

        $point5 = "M" . $seam .",". ((($topMargin + $blength) + 1) * $cIn);
		$point6 = "L" . (((($waist / 4) + $backDart) + 1.0) * $cIn) .",". (((($topMargin + $blength) + 1) ) * $cIn);
		$point7 = "L" . ((($chest / 4) + 1.0) * $cIn) .",". (($chestVertical) - (0.5 * $cIn)); // Seam Allowence
		$point8 = "Q" . ((($fshoulder / 2) + 1) * $cIn) . "," . $chestVertical .",". (((($fshoulder / 2) + 0.5) + $mLeft) * $cIn) .",". ($chestVertical / 2);
		$point9 = "L" . (((($fshoulder / 2)+ 0.5) + $mLeft) * $cIn) .",". (($fshoulder / 4) * $cIn);
		$point10 = "L" . (((($fshoulder / 2) + 0.5)+$mLeft) * $cIn) .",". $topPadding;
        $point11 = "L" . (((( $fshoulder / 2 ) - $shoulder) + $mLeft) * $cIn ) .",". $topMargin;

	$princeBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 .  $point10 . $point11;
    
    $_SESSION["princeBackRed"] = $princeBackRed;
    
// fleet Specifications

		$backTuckHeight = 3.5 * $cIn;
    
        $bvApex = (($apex + 1) * $cIn);
        $_SESSION["backVApex"] = $bvApex;

        if(($chest >= '30')&&($chest <= '32')) {
            $bhApex = 3.25 * $cIn;
        } elseif (($chest >= '32') && ($chest <= '35')) {
            $bhApex = 3.5 * $cIn;
        } elseif (($chest >= '35') && ($chest <= '38')) {
            $bhApex = 3.75 * $cIn;
        }  elseif (($chest >= '38') && ($chest <= '41')) {
            $bhApex = 4 * $cIn;
        } elseif (($chest >= '41') && ($chest <= '44')) {
             $bhApex = 4.25 * $cIn;
        } else {
            $bhApex = $bhApex;
        }

		$_SESSION["hApex"] = $hApex;
      
  //      $bhApex = ((($fshoulder / 2) - ($shoulder /2)) * $cIn); //((($fshoulder / 2) - ($shoulder /2)) * $cIn);
        $_SESSION["backHApex"] = $bhApex;
    
        $chestText = $bhApex;
		$blengthText = (($blength + 0.5) * $cIn);
		
        $blengthTextLeft = $chestText - (0.5 * $cIn);
		$blengthTextRight = $chestText + (0.5 * $cIn);		
		$tuckHeight = ($blength - $backTuckHeight) * $cIn;
		$blengthTextTuck = ($blength - $tuckHeight);

		$princeBackTucks = "M". $blengthTextLeft .",". ($blengthText + (0.5 * $cIn)) ."L". $chestText .",". (($apex + 1) * $cIn) ."L". $blengthTextRight .",". ($blengthText + (0.5 * $cIn));
    
        $_SESSION["princeBackTucks"] = $princeBackTucks;  
?>
	
<svg width="500" height="550" viewbox = "-50, -20, 500, 550 ">
	<g>
		<path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $princeBackBlack;?>" />
		
		<path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $princeBackGreen;?>" />
		
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $princeBackBrown;?>" />
				
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>"><?php echo $backTuckText;?></text>
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>" transform="rotate(-90, 10, <?php echo $bvApex;?>)">'< ---- Fold --- >' </text>
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $_SESSION["princeBackTucks"] ?>" /> 
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10"  d="<?php echo $princeBackRed;?>" />
		
		<text x="<?php echo $chestLabel;?>" y="<?php echo $backHeight;?>" font-size="9">1/4 of Chest-></text>
        <text x="<?php echo $chestLabel05;?>" y="<?php echo $backHeight;?>" font-size="9">Seam</text>
<!--        <text x="<?php echo $chestLabel10;?>" y="<?php echo $backHeight;?>" font-size="9">1.0"</text>
        <text x="<?php echo $chestLabel15;?>" y="<?php echo $backHeight;?>"font-size="9">1.5"</text>
        <text x="<?php echo $chestLabel20;?>" y="<?php echo $backHeight;?>"font-size="9">2.0"</text> -->
	</g>
</svg>
</div>
<!--
<div> Brown: <?php echo $fshoulderTemp; ?></div>
<div> Brown: <?php echo $fshoulder; ?></div>
<div> Green: <?php echo $_SESSION["fshoulder"]?></div> -->