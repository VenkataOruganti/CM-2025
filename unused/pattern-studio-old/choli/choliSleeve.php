<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="../choli/choliSleeveDownload.php" style="color:#ffffff">Download Blouse Sleeve</a>
        </button>
    </div>
    <div>    
        <p>
            <ul>
                <li>The print size is : A3 ( 11" x 17" - Landscape)</li>
                <li>Do not cut the cloth, too close to the margins</li>
                <li>Cut 2 pieces (flip the design as required).</li>
            </ul>
        </p>
    </div>
    
<?php

    $p6 = $topMargin = $saroundCenter = 0;
	$fLeft = 0.2;
	$seam = 0.4;
	$topMargin = 0.6;
    $mLeft = 1 * $cIn;

    $chestVertical = ((($armhole / 2) -1.25) * $cIn);
    
    $sAngle = 0;

    if ($chest < "33") {
        $sAngle = ((($chest / 8) - 0.5) * $cIn);
    } else {
        $sAngle = (3.5 * $cIn);
    };

// Sleeve Design - Red line Start - Outerline //
    
    $nPoint1 = "M" . ($chestVertical + $mLeft) . "," . $topMargin;
    $nPoint2 = "Q" . (($chestVertical / 1.5) + $mLeft) . "," . ($topMargin + (0.5 * $cIn)) . "," . (($chestVertical /2) + (1 * $cIn)) . "," . ($sAngle / 2);  

    $nPoint3 = "Q" . (($chestVertical /2) - (0.5 * $cIn)) . "," . $sAngle . "," . $mLeft . "," . $sAngle;
    
    $nPoint4 = "L" . $mLeft . "," . $sAngle;
    $nPoint5 = "L" . $mLeft . "," . $sAngle;
    
        $slCenter = (($chestVertical * 2) - ($saround * $cIn)) /2;
        $slCenter = $slCenter + (1.5 * $cIn);

    $nPoint6 = "L" . $slCenter . "," . ($slength  * $cIn);
    $nPoint7 = "L" . ($slCenter + (($saround + 0) * $cIn)) . "," . ($slength * $cIn);
    
    $nPoint8 = "L" . (($chestVertical * 2) + (2 * $cIn)) . "," . $sAngle;
    $nPoint9 = "Q" . ($chestVertical * 2 - (0.5 * $cIn)) . "," . (($sAngle/2) - ($topMargin * $cIn)) . "," . ($chestVertical + (2 * $cIn)) . "," . $topMargin;
   
    $sleeveRed = $nPoint1 . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . $nPoint6 . $nPoint7 . $nPoint8 . $nPoint9 . "Z" ;

    $_SESSION["choliBRed"] = $sleeveRed;

// Black Line - Inner dotted line ----

    $sAngle = 0;
    $nPoint1 = " M" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . $topMargin;
    
     if ($chest < "33") {
        $sAngle = ((($chest / 8) - 0.5) * $cIn);
    } else {
        $sAngle = (3.5 * $cIn);
    };
    
    $nPoint2 = "L" . (1 * $cIn) . "," . $sAngle;
    
        $slCenter = (($chestVertical * 2) - ($saround * $cIn)) /2;
        $slCenter = $slCenter + (1.5 * $cIn);
              
    $nPoint4 = "L" . $slCenter . "," . ($slength * $cIn);
    $nPoint5 = "L" . ($slCenter-(1* $cIn)) . "," . (($slength + 1.5) * $cIn);

    $nPoint6 = "L" . ($slCenter + (($saround + 1) * $cIn)) . "," . (($slength + 1.5) * $cIn);
    $nPoint7 = "L" . ($slCenter + ($saround * $cIn)) . "," . ($slength * $cIn);
    
    $nPoint8 = "L" . (($chestVertical * 2) + (3 * $cIn)) . "," . $sAngle;
    $nPoint9 = "L" . (($chestVertical * 2) + (2 * $cIn)) . "," . $sAngle;
    $nPoint10 = "Q" . (($chestVertical * 2) -(1 * $cIn)) . "," . ($sAngle/2) . "," . ($chestVertical + (1.5 * $cIn)) . "," . $topMargin;
   
    $sleeveBlack = $nPoint1 .  $nPoint2 . $nPoint4 .  $nPoint7 .  $nPoint9 .  "Z" ;
    
    $_SESSION["choliBBlack"] = $sleeveBlack;

// Brown Line - Inner dotted line -- Start here 

    $nPoint1 = "M" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . (-0.5 * $cIn);
    $nPoint1a = "Q" . (($chestVertical / 1.5) + (1 * $cIn)) . "," . $topMargin  . "," . (($chestVertical /2) + (1 * $cIn)) . "," . (($sAngle / 2) - (0.5 * $cIn));    

    $nPoint1b = "Q" . (($chestVertical /2) - (0.5*$cIn)) . "," . $sAngle . "," . (0*$cIn) . "," . ($sAngle -(0.5* $cIn));
    
    $nPoint2 = "L" . (0 * $cIn) . "," . $sAngle;
    $nPoint3 = "L" . (0 * $cIn) . "," . $sAngle;
    
    $slCenter = (($chestVertical * 2) - ($saround * $cIn)) /2;
    $slCenter = $slCenter + (1.5 * $cIn);

    $nPoint4 = "L" . ($slCenter - (1 * $cIn)) . "," . ($slength * $cIn);
    $nPoint5 = "L" . ($slCenter-(1* $cIn)) . "," . (($slength + 1) * $cIn);

    $nPoint6 = "L" . ($slCenter + (($saround + 1) * $cIn)) . "," . (($slength + 1) * $cIn);
    $nPoint7 = "L" . ($slCenter + (($saround + 1) * $cIn)) . "," . ($slength * $cIn);
    
    $nPoint8 = "L" . (($chestVertical * 2) + (3 * $cIn)) . "," . ($sAngle - (0.5 * $cIn));
    $nPoint9 = "L" . (($chestVertical * 2) + (2 * $cIn)) . "," . ($sAngle - (0.5 * $cIn));
    $nPoint10 = "Q" . ($chestVertical * 2 + (1 * $cIn)) . "," . (($sAngle/2) - ($topMargin * $cIn)) . "," . ($chestVertical + (2 * $cIn)) . "," . (-0.5 * $cIn);
   
    $sleeveBrown = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . $nPoint6 . $nPoint7 . $nPoint8 . $nPoint9 . $nPoint10 . "Z" ;

    $_SESSION["choliBBrown"] = $sleeveBrown;

// rectangle
    
/*    $rectLength = (($slength + 0.5) * $cIn);
    $rectWidth = (($chestVertical * 2) + (3 * $cIn));
  */  
?>
    <div style=" margin: 0 auto;">
        <svg width="500" height="500" viewbox = "-50, -50, 500, 500 ">
            
        <g>
            <path fill="none" stroke="#ff0000" stroke-width="0.2" stroke-dasharray="5,2,3" stroke-miterlimit="10"  d="<?php echo $sleeveBlack;?>" />
            <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $sleeveRed;?>"/>    
        <!--    
            <rect width="<?php echo $rectWidth;?>" height="<?php echo $rectLength;?>" style="fill:none;stroke-width:0.3;stroke:rgb(0,0,0)" /> -->
        </g> 
        <g>
            <path fill="none" stroke="#a9a9a9" stroke-width="0.5px" stroke-dasharray="5,5" stroke-miterlimit="10"  d="<?php echo $sleeveBrown;?>"/>
        </g>               
        </svg>
    </div>	
</div>