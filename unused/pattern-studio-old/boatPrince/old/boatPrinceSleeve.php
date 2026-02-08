<div>
    <div style="float: right;">
        <span>Download Sleeve Design</span>
        <button class="btn btn-info" >
        <a href="../inc/sleeveDownload.php" style="color:#ffffff"> SVG </a>
        </button>
		<button class="btn btn-info" >
        <a href="../savi/saviSleeveDownloadPdf.php" target="_blank" style="color:#ffffff"> PDF </a>
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
    $mLeft = 1;
    $armhole = $armhole + 0.5;
    $saround = $saround + 1.5;

    $sleeveCapHeight = ($armhole - $saround);
    

    $chestVertical = ((($armhole/2) - 1.5)* $cIn);  // actual armhole value - 1.5 Inches
    $chestVertical = ($chestVertical+(0.04 * $cIn)); // adding some margin to the Chest Vertical.

// Sleeve Cap Height calculation, generally for small 3.5, 
// medium 3.75 and large 4.0, but, I am using 3.5 for medium and large.

    $sAngle = 0;
    /*
    if ($chest < "33") {
           $sAngle = (3.0 * $cIn);
    } else {
        $sAngle = (3.5 * $cIn);
    };
*/
    switch($sleeveCapHeight) {
            
    case "1.0":
        $sAngle = (3.25 * $cIn);
        break;
            
    case "1.5":
        $sAngle = (3.25 * $cIn);
        break;
            
    case "2":
        $sAngle = (3.5 * $cIn);
        break;
                        
    case "2.5":
        $sAngle = (3.85 * $cIn);
        break;
                        
    case "3":
        $sAngle = (4 * $cIn);
        break;
            
    case "3.5":
        $sAngle = (4.25 * $cIn);
        break;  
            
    case "4":
        $sAngle = (4.7 * $cIn);
        break;  
            
    default:
        echo "Don't Print the sleeve, it is incorrect";
}


// Sleeve Design - Black Stitchline //

// Sleeve Center Calculation:

        $slCenter = (($chestVertical * 2) - ($saround * $cIn)) /2;
        $slCenter = $slCenter + (1.5 * $cIn);

        $slOpenCtr = (($saround - $sopen)/2) * $cIn;

    $nPoint1 = "M" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . $topMargin;
    $nPoint1a = "Q" . (($chestVertical / 1.5) + (1 * $cIn)) . "," . ($topMargin + (0.2 * $cIn)) . "," . ($chestVertical * 0.5) . "," . ($sAngle * 0.7);    

    $nPoint1b = "Q" . ($chestVertical * 0.3) . "," . $sAngle . "," . $slCenter . "," . $sAngle;

    $nPoint2 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength  * $cIn);
    $nPoint3 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
    $nPoint4 = "L" . ($slCenter + ($saround * $cIn)) . "," . $sAngle;

    $nPoint5 = "Q" . ($chestVertical * 2 - (0.5 * $cIn)) . "," . ($sAngle * 0.3) . "," . ($chestVertical + (2 * $cIn)) . "," . $topMargin;
   
    $sleeveBlack = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . "Z" ;

    $_SESSION["saviBBlack"] = $sleeveBlack;

// Gray Line - inner line --

    $nPoint1 = "M" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . $topMargin;    
    $nPoint2 = "L" . $slCenter . "," . $sAngle;
                  
    $nPoint3 = "L" . ($slCenter + $slOpenCtr) . "," . ($slength * $cIn);
    $nPoint4 = "L" . ($slCenter + $slOpenCtr + ($sopen * $cIn)) . "," . ($slength * $cIn);
    $nPoint5 = "L" . ($slCenter + ($saround * $cIn)) . "," . $sAngle;

    $sleeveGray = $nPoint1 .  $nPoint2 . $nPoint3 .  $nPoint4 .  $nPoint5 .  "Z" ;
    
    $_SESSION["saviBGray"] = $sleeveGray;

// Red Line - Outer dotted line -- Start here 

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
   
    $sleeveRed1 = $nPoint1 . $nPoint1a . $nPoint1b . $nPoint2 . $nPoint3 . $nPoint4 . $nPoint5 . $nPoint6 . $nPoint7 . $nPoint8 . $nPoint9 . $nPoint10 . "Z" ;

    $_SESSION["saviBRed"] = $sleeveRed1;

/* Center line */

    $nPoint1 = "M" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . $topMargin;
    $nPoint2 = "L" . ($chestVertical + ((1 + 0.5) * $cIn)) . "," . ($slength * $cIn);

    $centerLine = $nPoint1 . $nPoint2;
    $_SESSION["centerLine"] = $centerLine;
    
    
?>
    <div style=" margin: 0 auto;">
        <svg width="500" height="500" viewbox = "-50, -50, 500, 500 ">
            
        <g>
            <path fill="none" stroke="#ff0000" stroke-width="0.2" stroke-dasharray="5,2,3" stroke-miterlimit="10"  d="<?php echo $sleeveRed1;?>" />
            
            <path fill="none" stroke="#ff0000" stroke-width="0.2" stroke-dasharray="5,2,3" stroke-miterlimit="10"  d="<?php echo $centerLine;?>" />
            
            <path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $sleeveBlack;?>"/>    
        <!--    
            <rect width="<?php echo $rectWidth;?>" height="<?php echo $rectLength;?>" style="fill:none;stroke-width:0.3;stroke:rgb(0,0,0)" /> -->
        </g> 
        <g>
            <path fill="none" stroke="#a9a9a9" stroke-width="0.5px" stroke-dasharray="5,5" stroke-miterlimit="10"  d="<?php echo $sleeveGray;?>"/>
        </g>               
        </svg>
    </div>	
</div>
<!--
<div> ChestVertical: <?php echo $sAngle; ?></div>
<div> Sleeve Open: <?php echo $sopen; ?></div>
<div> Sleeve Round: <?php echo $saround; ?></div>
<div> arm hole: <?php echo $armhole; ?></div>

<div> Sleeve Cap Height: <?php echo $sleeveCapHeight; ?></div>
 -->