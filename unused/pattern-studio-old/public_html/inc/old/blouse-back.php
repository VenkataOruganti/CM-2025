<div>
    <div style="float: right;">
        <button class="btn btn-info" >
            <a href="inc/blouseBackDownload.php" style="color:#ffffff">Download Blouse Back Design</a>
        </button>
    </div>

    <p>
        <ul>
            <li>The print size is : A3 ( 11" x 17" - portrait)</li>
            <li>Do not cut the cloth, too close to the margins</li>
            <li>Left side is neck depth and fold.</li>
        </ul>
    </p>

    
	<?php
		if ($bnDepth =="") {
			$bnDepth = 0;
		}
    
        $backTuckText = $_SESSION["cust"];
    
        /* --- blouse back labels --*/
    
        $_SESSION["bChestLabel"] = $chestLabel = ((($chest /4) - 3.5) * $cIn); // chest width
        $_SESSION["bBackHeight"] = $backHeight = ($blength * $cIn);
    
        $_SESSION["bChestLabel0"] = $chestLabel05 = (($chest /4)   * $cIn); // chest width
        $_SESSION["bChestLabel05"] = $chestLabel10 = ((($chest /4) + 0.5) * $cIn); // chest width
        $_SESSION["bChestLabel10"] = $chestLabel15 = ((($chest /4) + 1.0) * $cIn); // chest width
        $_SESSION["bChestLabel15"] = $chestLabel20 = ((($chest /4) + 1.5) * $cIn); // chest width       
    
        /* --- blouse back labels --*/
    
		$back_bnDepth = $point1 = $point2 = $point3 = $point4 = $point5 = $point6 = $point7 = $point8 = $point9 = $point10 = $mLeft = $topMargin = $point6a = $point6b = $point7a = 0;

		$mLeft = 0;
		$topMargin = 0;

		$bbPoint1 = "M" . ((( $fshoulder / 2 ) - $shoulder) * $cIn ) .",". $topPadding; 
		$bbPoint2 = " L" . (((( $fshoulder/ 2) - $shoulder) + $mLeft) * $cIn) .",". ($topMargin + ($bnDepth/2) * $cIn);
		$bbPoint3 = " L" . (((( $fshoulder/ 2) - $shoulder) + $mLeft) * $cIn) .",". ($topMargin + ($bnDepth * $cIn));	
		$bbPoint4 = " L " . $mLeft .",". (($topMargin + $bnDepth) * $cIn);
		$bbPoint5 = " L " . $mLeft .",". ((($topMargin + $blength) +0.5) * $cIn);
		$bbPoint6 = " L " . ((($chest / 4) + 1) * $cIn) .",". ((($topMargin + $blength)+ 0.5) * $cIn);
		$bbPoint7 = " L " . ((($chest / 4) + 1.5) * $cIn) .",". $chestVertical;	
		$bbPoint8 = " L " . (($fshoulder / 2) * $cIn) .",". $chestVertical;
		$bbPoint9 = " L " . (($fshoulder / 2) * $cIn) .",". (($fshoulder / 4) * $cIn);
		$bbPoint10 = " L " . (($fshoulder / 2) * $cIn) .",". ($topPadding * 2);
		
		$blouseBackBlack = $bbPoint1 . $bbPoint2 .  $bbPoint3 . $bbPoint4 . $bbPoint5 . $bbPoint6 . $bbPoint7 . $bbPoint8 . $bbPoint9 . $bbPoint10 . "Z";
    
        $_SESSION["bBackBlack"] = $blouseBackBlack;

// -------------- green line design ----------//

        $greenPoint6 = "M" . ((($chest / 4) + 0.5) * $cIn) .",". ((($topMargin + $blength)+ 0.5) * $cIn);
		$greenPoint7 = "L" . ((($chest / 4) + 1.0) * $cIn) .",". $chestVertical;	

        $blouseBackGreen = $greenPoint6 . $greenPoint7;
    
        $_SESSION["bBackGreen"] = $blouseBackGreen;
    
// -------------- brown line design ----------//

        $brownPoint6 = "M" . ((($chest / 4) + 0.0) * $cIn) .",". ((($topMargin + $blength)+ 0.5) * $cIn);
		$brownPoint7 = "L" . ((($chest / 4) + 0.5) * $cIn) .",". $chestVertical;	

        $blouseBackBrown = $brownPoint6 . $brownPoint7;
    
        $_SESSION["bBackBrown"] = $blouseBackBrown;


// -------------- Red line Design
		$point1 = "M" . ((( $fshoulder / 2 ) - $shoulder) * $cIn ) .",". $topMargin; 
		$point2 = " L" . (((( $fshoulder / 2 ) - $shoulder ) + $mLeft ) * $cIn ) .",". ($topMargin + ($bnDepth / 2) * $cIn);
		$point3 = " Q" . ((($fshoulder/2) - $shoulder) * $cIn ). "," .($bnDepth * $cIn).",".$mLeft .",".($bnDepth * $cIn);
        $point4 = "L" . $mLeft .",".($bnDepth * $cIn);
//        $point4 = " L " . $mLeft .",". (($topMargin + $bnDepth) * $cIn);
		$point5 = " M " . $mLeft .",". ((($topMargin + $blength) + 1) * $cIn);
		$point6 = " L " . ((($chest / 4) + 1.5) * $cIn) .",". (((($topMargin + $blength) + 1) ) * $cIn);
		$point7 = " L " . ((($chest / 4) + 2) * $cIn) .",". $chestVertical; // Seam Allowence
		$point8 = " Q" . (($fshoulder / 2 ) * $cIn ) . "," . (($fshoulder / 2 ) * $cIn ) .",". (($fshoulder / 2 ) * $cIn ) .",". (($bnDepth / 2) * $cIn);
		$point9 = " L " . (($fshoulder / 2) * $cIn) .",". (($fshoulder / 4) * $cIn);
		$point10 = " L " . ((($fshoulder / 2) + 0.25) * $cIn) .",". $topPadding;
        $point11 = "L" . ((( $fshoulder / 2 ) - $shoulder) * $cIn ) .",". $topMargin;

	$blouseBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 . $point9 . $point10 . $point11 ;
//	$blouseBackRed = $point1 . $point2 . $point3 . $point4 . $point5 . $point6 . $point7 . $point8 . $point9 . $point10 .  "Z";
    
    $_SESSION["bBackRed"] = $blouseBackRed;
    
// fleet Specifications

		$backTuckHeight = ($apex + 1);
    
        $bvApex = (($apex + 1) * $cIn);
        $_SESSION["backVApex"] = $bvApex;
    
        $bhApex = ((($fshoulder / 2) - ($shoulder /2)) * $cIn); //((($fshoulder / 2) - ($shoulder /2)) * $cIn);
        $_SESSION["backHApex"] = $bhApex;
    
        $chestText = $bhApex;
		$blengthText = (($blength + 0.5) * $cIn);
		
        $blengthTextLeft = $chestText - (0.5 * $cIn);
		$blengthTextRight = $chestText + (0.5 * $cIn);		
		$tuckHeight = ($blength - $backTuckHeight) * $cIn;
		$blengthTextTuck = ($blength - $tuckHeight);

		$backTucks = "M" . $blengthTextLeft . "," . $blengthText . "L" . $chestText . "," . (($apex + 1) * $cIn) . "L" . $blengthTextRight . "," . $blengthText ;
    
        $_SESSION["blouseBackTucks"] = $backTucks;    
?>
	
<svg width="500" height="550" viewbox = "-50, -20, 500, 550 ">
	<g>
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blouseBackBlack;?>" />
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blouseBackGreen;?>" />
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $blouseBackBrown;?>" />
				
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>"><?php echo $backTuckText;?></text>
		<text x="<?php echo $bhApex;?>" y="<?php echo $bvApex;?>" transform="rotate(-90, 10, <?php echo $bvApex;?>)">'< ---- Fold --- >' </text>
		<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $_SESSION["blouseBackTucks"] ?>" /> 
		<path fill="none" stroke="#ff0000" stroke-dasharray="5, 5" stroke-width="0.5" stroke-miterlimit="10"  d="<?php echo $blouseBackRed;?>" />
		
		<text x="<?php echo $chestLabel;?>" y="<?php echo $backHeight;?>" font-size="8">1/4 of Chest-></text>
        <text x="<?php echo $chestLabel05;?>" y="<?php echo $backHeight;?>" font-size="8">0.5"</text>
        <text x="<?php echo $chestLabel10;?>" y="<?php echo $backHeight;?>" font-size="8">1.0"</text>
        <text x="<?php echo $chestLabel15;?>" y="<?php echo $backHeight;?>"font-size="8">1.5"</text>
        <text x="<?php echo $chestLabel20;?>" y="<?php echo $backHeight;?>"font-size="8">2.0"</text>
	</g>
</svg>

</div>
<!--
<div> Brown: <?php echo $blouseBackBrown; ?></div>
<div> Green: <?php echo $blouseBackGreen; ?></div> -->