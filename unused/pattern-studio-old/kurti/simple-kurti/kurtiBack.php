<div>
    <div style="float: right;">
	 <span>Download front-design</span>
        <button class="btn btn-info" >
            <a href="kurtiBackDownload.php" style="color:#ffffff">SVG</a>
        </button>
		 
		 <button class="btn btn-info" >
        <a href="kurtiBackDownloadPdf.php" style="color:#ffffff">PDF</a>
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
        $seam = 0.3 * $cIn;
        $neckSeam = 0.2;
        $backDart = 1;
        $mLeft = 1;
		$topMargin = 0;

// Back Length

        $backTuckText = $_SESSION["cust"];
		$bbPoint1 = "M" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". $topPadding; 
		$bbPoint2 = "L" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". ($topMargin + ($bnDepth/2) * $cIn);
		$bbPoint3 = "L" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". ($topMargin + ($bnDepth * $cIn));	
		$bbPoint4 = "L" . $seam .",". (($topMargin + $bnDepth) * $cIn);
		$bbPoint5 = "L" . $seam .",". ($flength * $cIn);
        $bbPoint5a = "L" . (($bottom /4)  * $cIn) .",". ($flength * $cIn);
        $bbPoint5b = "L" . (($hip /4)  * $cIn) .",". (($hlength +0.5) * $cIn);

        $bbPoint6 = "L". ((($waist / 4) * $cIn) + $seam) .",". (($wlength +0.5)* $cIn);
		$bbPoint7 = "L" . ((($chest / 4) * $cIn) +$seam ) .",". $chestVertical;	
		$bbPoint8 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". $chestVertical;
		$bbPoint9 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". (($fshoulder / 4) * $cIn);
		$bbPoint10 = "L" . ((($fshoulder / 2) * $cIn) +$seam) .",". ($topPadding * 2);
		
		$saviBackBlack = $bbPoint1 . $bbPoint2 . $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint5a . $bbPoint5b . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
    
        $_SESSION["saviBackBlack"] = $saviBackBlack;

// -------------- Black line design ----------//

		$sb_point1 = "M" . ((((( $fshoulder /2) -$shoulder) ) *$cIn)+$seam) .",". (0.3 * $cIn); 
		$sb_point2 = "L" . ((((( $fshoulder / 2) -$shoulder) +0.3) *$cIn)+$seam) .",". ($topMargin + ($bnDepth / 2) * $cIn);
		$sb_point3 = "Q" . ((((($fshoulder/2) - $shoulder)+0.3) *$cIn)+$seam). "," .($bnDepth * $cIn).",". $seam .",".($bnDepth * $cIn);
        $sb_point4 = "L" . $seam .",".($bnDepth * $cIn);
		$sb_point5 = "M" . $seam .",". ($flength * $cIn);

        $sbPoint5a = "L" . (($bottom /4) * $cIn) .",". ($flength * $cIn);
        $sb_point5b=  "L" . ((($bottom /4) +0.3)  * $cIn) .",". ($flength *$cIn);
        $sb_point6 = "L" . ((($hip /4) +0.3)  * $cIn) .",". (($hlength + 0.5) * $cIn);
		$sb_point7 = "L" . ((($waist / 4) * $cIn) + $seam) .",". ($wlength * $cIn);

        $sb_point7a = "L" . ((($chest / 4)+ 0.3) * $cIn) .",". $chestVertical; // curve calculation

            if(($chest > '28')&&($chest <= '32')){
                $frontChestVertical = $chestVertical/2;
            } elseif(($chest > '32')&&($chest <= '38')){
                $frontChestVertical = ($chestVertical/2 + (0.5 * $cIn));
            } elseif($chest > '38'){
                $frontChestVertical = ($chestVertical/2 + (1 * $cIn));
            }

		$sb_point8 = "Q" . ((($fshoulder/2) * $cIn) +$seam). "," .($chestVertical - (0.2 * $cIn)) .",". ((($fshoulder/2) * $cIn) +$seam) .",". $frontChestVertical;

		$sb_point9 = "L" . ((($fshoulder / 2) * $cIn) +$seam) .",". (($fshoulder / 4) * $cIn);
		$sb_point10 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". (0.5 * $cIn);
        $sb_point11 = "L" . (((( $fshoulder /2) - $shoulder) * $cIn)  +$seam ) .",". (0.25 * $cIn);

        $saviBackGreen = $sb_point1 . $sb_point2 . $sb_point3 . $sb_point5 . $sb_point5a . $sb_point5b . $sb_point6 . $sb_point7 . $sb_point7a . $sb_point8 . $sb_point9 . $sb_point10 . $sb_point11;
    
        $_SESSION["saviBackGreen"] = $saviBackGreen;
    
// -------------- brown line - extra Bust design ----------//

        $brownPoint6 = "M" . (((($waist / 4) + 0.5) * $cIn)+ $seam) .",". ((($topMargin + $blength)+ 0.5) * $cIn);
		$brownPoint7 = "L" . (((($chest / 4) - 0.5) * $cIn) + $seam) .",". $chestVertical;	

        $saviBackBrown1 = $brownPoint6 . $brownPoint7;
    
        $_SESSION["saviBackBrown"] = $saviBackBrown;

// -------------- Red line Design ----------- //

        $point1 = "M" . ((((($fshoulder / 2) - $shoulder)) *$cIn) - $seam) .",". $topMargin; 
		$point2 = "L" . ((((($fshoulder / 2) - $shoulder)) *$cIn) - $seam) .",". ($topMargin + ($bnDepth / 2) * $cIn);
		$point3 = "Q". ((((($fshoulder/2)-$shoulder)) *$cIn) - $seam)."," .($bnDepth * $cIn).",".$seam .",".(($bnDepth * $cIn) - $seam);
        $point4 = "L" . $seam .",". ($bnDepth * $cIn);
        $point5 = "M" . $seam .",". (($flength +0.5) * $cIn);
        $point6 = "L" . ((($bottom /4) + 0.5)  * $cIn) .",". (($flength +0.5) * $cIn);
        $point7 = "L" . ((($hip /4) + 0.5)  * $cIn) .",". (($hlength +1.0) * $cIn);
		$point8 = "L" . ((($waist / 4) + 0.5) * $cIn) .",". ($wlength * $cIn);
		$point9 = "L" . ((($chest / 4) + 0.5) * $cIn) .",". (($chestVertical) - (0.3 * $cIn)); // Seam Allowence
		$point10 = "Q" . ((($fshoulder / 2) + 0.75) * $cIn) . "," . (($chestVertical) - (0.25 * $cIn)) .",". (((($fshoulder / 2) + 0.5) * $cIn) +$seam) .",". ($chestVertical / 2);
		$point11 = "L" . (((($fshoulder / 2)+ 0.5) *$cIn)+$seam) .",". (($fshoulder / 4) * $cIn);
		$point12 = "L" . (((($fshoulder / 2) + 0.5) * $cIn) +$seam) .",". $topPadding;
        $point13 = "L" . ((( $fshoulder / 2 ) - $shoulder) * $cIn) .",". $topMargin;

	$saviBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 . $point9 . $point10 .  $point11 . $point12. $point13;
    
    $_SESSION["saviBackRed"] = $saviBackRed;
    
// fleet Specifications

		$backTuckHeight = 3.5 * $cIn;
    
        $bvApex = (($apex + 1) * $cIn);
        $_SESSION["backVApex"] = $bvApex; 

        $bust = $_SESSION["chest"];

        if(($bust >= '28')&&($bust <= '32')) {
            $bhApex = 3.25 * $cIn;
        } elseif (($bust >= '32') && ($bust <= '35')) {
            $bhApex = 3.5 * $cIn;
        } elseif (($bust >= '35') && ($bust <= '38')) {
            $bhApex = 3.75 * $cIn;
        }  elseif (($bust >= '38') && ($bust <= '41')) {
            $bhApex = 4 * $cIn;
        } elseif (($bust >= '41') && ($bust <= '44')) {
             $bhApex = 4.25 * $cIn;
        } else {
            $bhApex = $bhApex;
        }

		$_SESSION["hApex"] = $hApex;
      
  //      $bhApex = ((($fshoulder / 2) - ($shoulder /2)) * $cIn); //((($fshoulder / 2) - ($shoulder /2)) * $cIn);
        $_SESSION["backHApex"] = $bhApex;
    
        $chestText = $bhApex;
		$blengthText = (($wlength + 0.5) * $cIn);
		
        $blengthTextLeft = $chestText - (0.5 * $cIn);
		$blengthTextRight = $chestText + (0.5 * $cIn);		
		$tuckHeight = ($wlength - $backTuckHeight) * $cIn;
		$blengthTextTuck = ($wlength - $tuckHeight);

		$saviBackTucks1 = "M" . $blengthTextLeft . "," . ($blengthText + (0.5 * $cIn)) . "L" . $chestText . "," . (($apex + 1) * $cIn) . "L" . $blengthTextRight . "," . ($blengthText + (0.5 * $cIn));
    
        $_SESSION["saviBackTucks"] = $saviBackTucks;  

/* --- blouse back labels --*/
    
        $_SESSION["bChestLabel"] = $chestLabel = ((($chest /4) - 3.5) * $cIn); // chest width
        $_SESSION["bBackHeight"] = $backHeight = ($wlength * $cIn);
    
        $_SESSION["bChestLabel0"] = $chestLabel05 = (($chest /4)   * $cIn); // chest width
        $_SESSION["bChestLabel05"] = $chestLabel10 = ((($chest /4) + 0.5) * $cIn); // chest width
        $_SESSION["bChestLabel10"] = $chestLabel15 = ((($chest /4) + 1.0) * $cIn); // chest width
        $_SESSION["bChestLabel15"] = $chestLabel20 = ((($chest /4) + 1.5) * $cIn); // chest width       
    
/* --- blouse back labels --*/
	
?>
	
<svg width="500" height="1250" viewbox = "-50, -20, 500, 1250 ">
	<g>
		<path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $saviBackBlack;?>" />
		
		<path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $saviBackGreen;?>" />
		
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-dasharray="10, 5" stroke-miterlimit="10" d="<?php echo $saviBackBrown;?>" />
				
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>"><?php echo $saviBackTuckText;?></text>
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>" transform="rotate(-90, 10, <?php echo $bvApex;?>)">'< ---- Fold --- >' </text>
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $_SESSION["saviBackTucks"] ?>" /> 
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10"  d="<?php echo $saviBackRed;?>" />
		
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