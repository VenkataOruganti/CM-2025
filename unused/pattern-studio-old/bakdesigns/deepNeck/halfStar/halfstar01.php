<div style="border-top: 1px solid lightgray">
    <div style="float: right;" >
	 <span>Download front-design</span>
        <button class="btn btn-info" >
            <a href="halfstarDownload.php" style="color:#ffffff">SVG</a>
        </button>
		<button class="btn btn-info" >
        <a href="halfstarDownloadPdf.php" style="color:#ffffff">PDF</a>
        </button>
    </div>

    <div>
        <ul><br>
            <li>This design for reference only, measure it before you use it. </li>
            <li>The print size is : A3 ( 11" x 17" - portrait)</li>
            <li>Do not cut the cloth, too close to the margins</li>
            <li>Left side is neck depth and fold.</li>
        </ul>
    </div>
    
    <div>
        <?php include '../../../inc/deepNeckCV.php';?> 
    </div>
    
	<?php

        $seam = 0.3 * $cIn;
        $neckSeam = 0.2;
        $backDart = 1;
        $mLeft = 1;
		$topMargin = 0;
    
        $cIn1 = $prnHeight;

// Back Length

        $backTuckText = $_SESSION["cust"];

		$bbPoint1 = "M" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". $topPadding; 
		$bbPoint2 = "L" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". ($topMargin + ($bnDepth/2) * $cIn1);
		$bbPoint3 = "L" . (((( $fshoulder/ 2) - $shoulder) *$cIn) +$seam) .",". ($topMargin + ($bnDepth * $cIn1));	
		$bbPoint4 = "L" . $seam .",". (($topMargin + $bnDepth) * $cIn1);
		$bbPoint5 = "L" . $seam .",". ((($topMargin + $blength) +0.5) * $cIn1);
		$bbPoint6 = "L". ((((($waist / 4) + $backDart) ) * $cIn) + $seam) .",". ((($topMargin+$blength)+0.5)* $cIn1);
		$bbPoint7 = "L" . ((($chest / 4) * $cIn) +$seam ) .",". $chestVertical;	
		$bbPoint8 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". $chestVertical;
		$bbPoint9 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". (($fshoulder / 4) * $cIn1);
		$bbPoint10 = "L" . ((($fshoulder / 2) * $cIn) +$seam) .",". ($topPadding * 2);
		
		$halfStarGray = $bbPoint1 . $bbPoint2 . $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
    
        $_SESSION["halfStarGray"] = $halfStarGray;

// -------------- Black line design ----------//

		$sb_point1 = "M" . (((( $fshoulder /2) -$shoulder) *$cIn)+$seam) .",". (0.3 * $cIn1);
    
        if ($angleWidth == ''){
            $sb_point2 = "L" . (((( $fshoulder / 2) -$shoulder) *$cIn)+$seam) .",". ($topMargin + ($bnDepth * ($angleDepth/100) * $cIn1));
        } else {
            $sb_point2 = "L" . ($seam + ($angleWidth * $cIn)) .",". ($topMargin + ($bnDepth * ($angleDepth/100) * $cIn1));
        }
    
//		$sb_point3="Q". (((($fshoulder/2)-$shoulder) *$cIn)+$seam).",".($bnDepth*$cIn).",". $seam.",".($bnDepth*$cIn);
        $sb_point3 = "L" . $seam .",".($bnDepth * $cIn1);
        $sb_point4 = "L" . $seam .",".($bnDepth * $cIn1);
		$sb_point5 = "M" . $seam .",". ((($topMargin + $blength) + 0.5) * $cIn1);
		$sb_point6 = "L" . ((((($waist / 4) + $backDart) ) *$cIn) +$seam) .",". ((($topMargin + $blength) +0.5) *$cIn1);
		$sb_point7 = "L" . ((($chest / 4) * $cIn)+$seam ).",". $chestVertical; // Seam Allowence

        $sb_point7a = "L" . (($chest / 4) * $cIn) .",". $chestVertical; // curve calculation

            if(($chest > '28')&&($chest <= '32')){
                $frontChestVertical = $chestVertical/2;
            } elseif(($chest > '32')&&($chest <= '38')){
                $frontChestVertical = ($chestVertical/2 + (0.5 * $cIn));
            } elseif($chest > '38'){
                $frontChestVertical = ($chestVertical/2 + (1 * $cIn));
            }

		$sb_point8 = "Q" . ((($fshoulder/2) * $cIn) +$seam). "," .($chestVertical - (0.2 * $cIn)) .",". ((($fshoulder/2) * $cIn) +$seam) .",". $frontChestVertical;

		$sb_point9 = "L" . ((($fshoulder / 2) * $cIn) +$seam) .",". (($fshoulder / 4) * $cIn1);
		$sb_point10 = "L" . ((($fshoulder / 2) *$cIn) +$seam) .",". (0.5 * $cIn1);
        $sb_point11 = "L" . (((( $fshoulder /2) - $shoulder) * $cIn)  +$seam ) .",". (0.25 * $cIn1);

        $halfStarBlack = $sb_point1 . $sb_point2 . $sb_point3 . $sb_point5 . $sb_point6 . $sb_point7 . $sb_point7a . $sb_point8 . $sb_point9 . $sb_point10 . $sb_point11;
    
        $_SESSION["halfStarBlack"] = $halfStarBlack;
    
// -------------- brown line - extra Bust design ----------//

        $brownPoint6 = "M" . (((($waist / 4) + 0.5) * $cIn)+ $seam) .",". ((($topMargin + $blength)+ 0.5) * $cIn1);
		$brownPoint7 = "L" . (((($chest / 4) - 0.5) * $cIn) + $seam) .",". $chestVertical;	

        $saviBackBrown1 = $brownPoint6 . $brownPoint7;
    
        $_SESSION["saviBackBrown"] = $saviBackBrown;

// -------------- Red line Design ----------- //

        $point1 = "M" . ((((($fshoulder / 2) - $shoulder)) *$cIn) - $seam) .",". $topMargin; 
		$point2 = "L" . ((((($fshoulder / 2) - $shoulder)) *$cIn) - $seam) .",". ($topMargin + ($bnDepth / 2) * $cIn1);
		$point3 = "Q". (((($fshoulder/2)-$shoulder) *$cIn) - $seam)."," .($bnDepth * $cIn1).",".$seam .",".(($bnDepth * $cIn1) - $seam);
        $point4 = "L" . $seam .",". ($bnDepth * $cIn1);

        $point5 = "M" . $seam .",". ((($topMargin + $blength) + 1) * $cIn1);
		$point6 = "L" . (((($waist / 4) + $backDart) + 1.0) * $cIn) .",". (((($topMargin + $blength) + 1) ) * $cIn1);
		$point7 = "L" . ((($chest / 4) + 1.0) * $cIn) .",". (($chestVertical) - (0.3 * $cIn1)); // Seam Allowence
		$point8 = "Q" . ((($fshoulder / 2) + 0.75) * $cIn) . "," . (($chestVertical) - (0.25 * $cIn1)) .",". (((($fshoulder / 2) + 0.5) * $cIn) +$seam) .",". ($chestVertical / 2);
		$point9 = "L" . (((($fshoulder / 2)+ 0.5) *$cIn)+$seam) .",". (($fshoulder / 4) * $cIn1);
		$point10 = "L" . (((($fshoulder / 2) + 0.5) * $cIn) +$seam) .",". $topPadding;
        $point11 = "L" . ((( $fshoulder / 2 ) - $shoulder) * $cIn) .",". $topMargin;

	$halfStarRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 .  $point10 . $point11;
    
    $_SESSION["halfStarRed"] = $halfStarRed;
    
// fleet Specifications

		$backTuckHeight = 3.5 * $cIn1;
    
        $bvApex = (($apex + 1) * $cIn1);
        $_SESSION["backVApex"] = $bvApex; 

        $bust = $_SESSION["chest"];

        if(($bust >= '30') && ($bust <= '32')) {
            $bhApex = 3.25 * $cIn1;
        } elseif (($bust >= '32') && ($bust <= '35')) {
            $bhApex = 3.5 * $cIn1;
        } elseif (($bust >= '35') && ($bust <= '38')) {
            $bhApex = 3.75 * $cIn1;
        }  elseif (($bust >= '38') && ($bust <= '41')) {
            $bhApex = 4 * $cIn1;
        } elseif (($bust >= '41') && ($bust <= '44')) {
             $bhApex = 4.25 * $cIn1;
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
		$tuckHeight = ($blength - $backTuckHeight) * $cIn1;
		$blengthTextTuck = ($blength - $tuckHeight);

		$saviBackTucks = "M" . $blengthTextLeft . "," . ($blengthText + (0.5 * $cIn)) . "L" . $chestText . "," . (($apex + 1) * $cIn) . "L" . $blengthTextRight . "," . ($blengthText + (0.5 * $cIn));
    
        $_SESSION["saviBackTucks"] = $saviBackTucks;  

/* --- blouse back labels --*/
    
        $_SESSION["bChestLabel"] = $chestLabel = ((($chest /4) - 3.5) * $cIn); // chest width
        $_SESSION["bBackHeight"] = $backHeight = ($blength * $cIn);
    
        $_SESSION["bChestLabel0"] = $chestLabel05 = (($chest /4)   * $cIn); // chest width
        $_SESSION["bChestLabel05"] = $chestLabel10 = ((($chest /4) + 0.5) * $cIn); // chest width
        $_SESSION["bChestLabel10"] = $chestLabel15 = ((($chest /4) + 1.0) * $cIn); // chest width
        $_SESSION["bChestLabel15"] = $chestLabel20 = ((($chest /4) + 1.5) * $cIn); // chest width       
    
/* --- blouse back labels --*/
?>

<svg width="500" height="450" viewbox = "-50, -20, 500, 450 ">
	<g>
		<path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $halfStarGray;?>" />
		
		<path fill="none" stroke="#000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $halfStarBlack;?>" />
		<path fill="none" stroke="#ff0000" stroke-width="0.5" stroke-dasharray="5,5" stroke-miterlimit="10" d="<?php echo $halfStarRed;?>" />
				
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>"><?php echo $saviBackTuckText;?></text>
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>" transform="rotate(-90, 10, <?php echo $bvApex;?>)">'< ---- Fold --- >' </text>
		<path fill="none" stroke="#000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $_SESSION["saviBackTucks"] ?>" /> 
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10"  d="<?php echo $saviBackRed;?>" />
		
		<text x="<?php echo $chestLabel;?>" y="<?php echo $backHeight;?>" font-size="9">1/4 of Chest-></text>
        <text x="<?php echo $chestLabel05;?>" y="<?php echo $backHeight;?>" font-size="9">Seam</text>
	</g>
</svg>
</div>

<!--
<div> Angle Depth: <?php echo $angleDepth; ?></div>
<div> Brown: <?php echo $fshoulder; ?></div>
<div> Green: <?php echo $_SESSION["fshoulder"]?></div> -->