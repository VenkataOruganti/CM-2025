<div>
    <div style="float: right;">
        <button class="btn btn-info" >
        <a href="../katori/katoriFrontDownload.php" style="color:#ffffff"> Download Blouse Front Design</a>
        </button>
    </div>
    <div>
        <?php include '../inc/chestVertical.php';?> 
    </div>

	<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
    
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back  
        $_SESSION["fbDart"] = $bDart;
    
// Z point - front bottom piece
    
        if ($chest < "33") {
            $z = 0.75;
        } else if ($chest < "38") {
            $z = 0.5;
        } else {
            $z = 0.25;
        }

// Blouse Sleeve center to Apex
		$vApex = (($apex + 0.5) * $cIn);
		$_SESSION["vApex"] = $vApex;

		$hApex = ((($fshoulder/2) - $shoulder) * $cIn);        //((($fshoulder - ($shoulder * 2)) /2) * $cIn);
        $hApex = ((((($fshoulder / 2) - $z) / 2) + 1) * $cIn); // + 1 is left margin
            
		$_SESSION["hApex"] = $hApex;
    
//		$frontDart = "L" . ($hApex - $fDart) . "," . ($blength * $cIn);

		$front_point1 = "M" . ((($fshoulder1 / 2) - $shoulder1) + 1 * $cIn) . "," . ($topMargin + $topPadding);
		$front_point2 = " L" . ((($fshoulder1 / 2) - $shoulder1) + 1 * $cIn) . "," . ($fndepth1 / 2);
		$front_point3 = " L" . ((($fshoulder1 / 2) - $shoulder1) + 1 * $cIn) . "," . $fndepth1;
		$front_point4 = " L" . ($fLeft + 1 * $cIn) . "," . $fndepth1;
		$front_point5 = " L" . ($fLeft + 1 * $cIn) . "," . (($apex - 0.5) * $cIn);
// T - Point
        $front_point6 = " L" . (($fLeft * $cIn) + $hApex) . "," . ($apex * $cIn);
		$front_point7 = " L" . (($fLeft + 0.5) * $cIn) . "," . (($apex + 0.5) * $cIn);
//X - Point    
        $front_point8 = " L" . (($fLeft + 1) * $cIn) . "," . (($flength + 1.5) * $cIn);
        $front_point8a = " L" . $hApex . "," . (($flength + 2.5) * $cIn);

// 5-point    
        $front_point9 = " L" . (((($fshoulder / 2) + 1) - $z) * $cIn) . "," . (($flength + 2.5) * $cIn); // low chest
//W-point
        $front_point10 = " L" . (((($fshoulder / 2) +1) - $z) * $cIn) . "," . (($flength + 0.5) * $cIn); // low chest
        $front_point11 = " L" . (((($chest / 4) + 2) + 0) * $cIn) . "," . (($flength + 0.5) * $cIn); // low chest

		$front_point12 = " L" . (((($chest / 4) + 2) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    $front_point13 = " L" . (((($fshoulder / 2) + 1) - 0.0) * $cIn) . "," . $chestVertical;
		$front_point14 = " L" . ((($fshoulder / 2) + 1) * $cIn) . "," . $seam ;
        $front_point15 = "L" . (((($fshoulder / 2) - $shoulder ) + 1) * $cIn) . "," . ($topMargin + $topPadding);
        
// Black line graphic (squares)

		$ktFrontBlouseGray = $front_point1 . $front_point2 . $front_point3 . $front_point4 . $front_point5 .$front_point6 .  $front_point7 . $front_point8. $front_point8a . $front_point9 . $front_point10 .$front_point11 . $front_point12 . $front_point13 . $front_point14 . $front_point15;

		$_SESSION["ktBlouseFrontBlack"] = $ktFrontBlouseGray;
        
// -------------- green dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 1.0) * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = " L" . (((($chest / 4) + 1.0) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $ktFrontBlouseGreen = $green_point8 . $green_point9;

		$_SESSION["ktFrontBlouseGreen"] = $ktFrontBlouseGreen;
    
// -------------- brown dotted line start ----------------------------

		$green_point8 = " M" . ((($chest / 4) + 1.5) * $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
        $green_point9 = " L" . ((($chest / 4) + 2.0) * $cIn) . "," . $chestVertical; //arm hole D-E + K
	    
        $ktFrontBlouseBrown = $green_point8 . $green_point9;

		$_SESSION["ktFrontBlouseBrown"] = $ktFrontBlouseBrown;
         
// -------------- red dotted line start ----------------------------
		
		$ktRedpoint1 = "M" . (((($fshoulder / 2) - $shoulder) + 1) * $cIn) . "," . $topMargin;
		$ktRedpoint2 = "L" . (((($fshoulder / 2) - $shoulder) + 1) * $cIn) . "," . ($fndepth1 / 2);
		$ktRedpoint4 = "Q" . (($fshoulder1 / 2) - $shoulder1) .",". $fndepth1 ."," . ($fLeft + (1 * $cIn)) .",". $fndepth1;
        $ktRedpoint5 = " L" . ($fLeft + (1 * $cIn)) . "," . (($apex - 0.5) * $cIn);
        $ktRedpoint6 = " L" . $hApex . "," . ($apex * $cIn);
        $ktRedpoint7 = " L" . (($fLeft + 0.5) * $cIn) . "," . (($apex + 0.5) * $cIn);
      
		$ktRedpoint8 = " L" . (($fLeft + 1) * $cIn) . "," . (($flength + 1.5) * $cIn); 
		$ktRedpoint8a = " L" . $hApex . "," . (($flength + 2.5) * $cIn); 
    
        $ktRedpoint9 = " L" . ((($fshoulder/2) + 0.5) * $cIn) . "," . (($flength + 2.5) * $cIn); 
        $ktRedpoint10 = " L" . ((($fshoulder/2) + 0.5) * $cIn) . "," . (($flength + 0.5) * $cIn); 
        
		$ktRedpoint11 = " L" . (((($chest / 4) + 1.5) + 0.5 )* $cIn) . "," . ($flength1 + (0.5 * $cIn)); // low chest
		$ktRedpoint12 = " L" . ((((($chest / 4) + 1.5) + 0.5) + 0.5) * $cIn) . "," . $chestVertical; //arm hole D-E + K
    
        $ktRedpoint13 = " Q" . (((($fshoulder/2) + 2) - 0.5) * $cIn) . "," . $chestVertical . "," . (((($fshoulder/2)+1.5) - 0.5) *$cIn) . "," . ($chestVertical - (1 * $cIn));

        $ktRedpoint14 = "M" . ((($fshoulder/ 2) + 1) * $cIn) . "," . ($chestVertical - (1 * $cIn)) . 
                        " Q" . (($fshoulder1 /2) + (0.1 * $cIn)) .",". //cx
                                (($fshoulder1 /2) - (2.5 * $cIn)) .",". //cy
                                ((($fshoulder /2) + 1.25) * $cIn)  . "," . // X - Co-ordinate
                                $topPadding; // Y- Co-ordinate

        $ktRedpoint15 = " L" . (((($fshoulder / 2) - $shoulder) +1) * $cIn) . "," . $topMargin;
             
		$ktFrontBlouseRed = $ktRedpoint1 . $ktRedpoint2 . $ktRedpoint4 . $ktRedpoint5 . $ktRedpoint6 .$ktRedpoint7 . $ktRedpoint8 . $ktRedpoint8a . $ktRedpoint9 . $ktRedpoint10 . $ktRedpoint11 . $ktRedpoint12 . $ktRedpoint13 .  $ktRedpoint14 . $ktRedpoint15;

		$_SESSION["ktBlouseFrontRed"] = $ktFrontBlouseRed;

// -------------- red dotted line ends ---------------------------- //

// Front Left Tuck
		$ktFrontLeftTucks1 =   "M" . $fLeft . "," .  (($apex - 0.3)* $cIn) . 
                            "L" . ($hApex - (1*$cIn)) . "," . ($vApex - (0.25 * $cIn)) . 
                            "L" . $fLeft . "," . (($apex + 0.3)* $cIn);
    
		$_SESSION["ktFlTucks"] = $ktFrontLeftTucks;
    
//Front Bottom Tuck
		$ktFrontBottomTucks = "M" . ($hApex - (($bDart / 2) * $cIn)) . "," . ((($flength ) + 2.5)* $cIn) . 
                            "L" . ($hApex + (0.1 * $cIn)) .",". ($apex * $cIn) . 
                            "L" . ($hApex + (($bDart / 2) * $cIn)) . "," . ((($flength ) + 2.5)* $cIn);
    
        $_SESSION["ktFbTucks"] = $ktFrontBottomTucks;
            
// Front Right Tuck                            

        $ktRightTuck1 =  "M" . (((($fshoulder / 2) + 1) - $z) * $cIn)  . "," . (($flength + 0.5) * $cIn);
        $ktRightTuck2 =  "L" . (((($fshoulder / 2) + 1) - $z) * $cIn)  . "," . (($apex + 0.5) * $cIn);
        $ktRightTuck3 =  "L" . $hApex . "," . ($apex * $cIn);
        $ktRightTuck4 =  "L" . (((($fshoulder / 2) + 1) - $z) * $cIn)  . "," . (($apex - 0.5) * $cIn);
        $ktRightTuck5 = "L" . (((($fshoulder / 2) + 1 ) - $z) * $cIn) . "," . $chestVertical;
        $ktRightTuck6 = "Q" . (($fshoulder /2) * $cIn) ."," . (($fndepth - 1) * $cIn) ."," . (((($fshoulder /2 ) - $shoulder) + 1) * $cIn) . "," . (($fndepth /2) * $cIn);
    
        $ktFrontRightTucks = $ktRightTuck1 . $ktRightTuck2 . $ktRightTuck3 . $ktRightTuck4 . $ktRightTuck5 . $ktRightTuck6; 
        $_SESSION["ktFrTucks"] = $ktFrontRightTucks; 
	?>
	
	<svg width="600" height="450" viewbox = "-50, 0, 600, 450">
      <g>
        <path fill="none" stroke="#d3d3d3" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontBlouseGray;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontBlouseGreen;?>" />
        <path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontBlouseBrown;?>" />
        <text x="<?php echo $hApex;?>" y="<?php echo $vApex;?>"> <?php echo $cust;?> </text>
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontLeftTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontRightTucks;?>" />
    	<path fill="none" stroke="#000000" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $ktFrontBottomTucks;?>" />
    	<path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $centerApex;?>" />
    <!--	<path fill="none" stroke="#B2BABB" stroke-width="0.5" stroke-miterlimit="10" d="<?php echo $patti;?>" /> -->
     </g>    	
    	
	<g>
		<path fill="none" stroke="#000000" stroke-width="1" stroke-miterlimit="10" d="<?php echo $ktFrontBlouseRed;?>" />
    </g>
 	</svg>	
</div>