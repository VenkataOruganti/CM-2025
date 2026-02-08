<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back
        $chestVertical = 0;
     
/* Chest Vertical Calculation
    - 28” to 30” = ¼ chest-¾’ to 1”
    - 31” to 33” =¼ chest-1” to 1¼”
    - 34” to 36” = ¼ chest-1½” to 2”
    - 37 “ to 39” = ¼ chest- 2¼” to 2¾”
    - 40 to 42 = ¼ chest-3” to 3½”
        
*/         
// Vani Method : 
// Based on Chest Calculation, very close to the perfection.
// The only negative point in this method (based on my assumptions) range is too wide
// for women, who got different arm hole round, this might be difficult.


    switch ($chest) {
        case "21":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;
    
        case "22":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;

        case "23":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;

        case "24":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;

        case "25":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;
            
        case "26":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;

        case "27":
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;

        case "28":
            $chestVertical = ((($chest / 4) - 0.75) * $cIn);
            break;

        case "29":
            $chestVertical = ((($chest / 4) - 0.85) * $cIn);
            break;

        case "30":
            $chestVertical = ((($chest / 4) - 1) * $cIn);
            break;

        case "31":
            $chestVertical = ((($chest / 4) - 1) * $cIn);
            break;
    
        case "32":
            $chestVertical = ((($chest / 4) - 1.10) * $cIn);
            break;
                 
        case "33":
            $chestVertical = ((($chest / 4) - 1.25) * $cIn);
            break;
                 
        case "34":
            $chestVertical = ((($chest / 4) - 1.5) * $cIn);
            break;
                 
        case "35":
            $chestVertical = ((($chest / 4) - 1.75) * $cIn);
            break;
                 
        case "36":
            $chestVertical = ((($chest / 4) - 2) * $cIn);
            break;
                 
        case "37":
            $chestVertical = ((($chest / 4) - 2.25) * $cIn);
            break;
                 
        case "38":
            $chestVertical = ((($chest / 4) - 2.5) * $cIn);
            break;
                 
        case "39":
            $chestVertical = ((($chest / 4) - 2.75) * $cIn);
            break;

        case "40":
            $chestVertical = ((($chest / 4) - 3) * $cIn);
            break;
                 
        case "41":
            $chestVertical = ((($chest / 4) - 3.25) * $cIn);
            break;

        case "42":
            $chestVertical = ((($chest / 4) - 3.5) * $cIn);
            break;

        case "43":
            $chestVertical = ((($chest / 4) - 3.75) * $cIn);
            break;
                 
        case "44":
            $chestVertical = ((($chest / 4) - 4) * $cIn);
            break;
                 
        case "45":
            $chestVertical = ((($chest / 4) - 4.25) * $cIn);
            break;
                
        default:
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;
            
            }

/*

// Emode method: Based Arm hole perfect calculation.
// Do not change this code or delete any part of it.
// Read Actual Arm Hole measurement and deduct 1.5 from it.
// the below calculation is to adjust the full shoulder calculation only.



        $chestVertical = ((($armhole/2) - 1.5)* $cIn);

        if (($bnDepth > '4') && ($bnDepth < '7')) {
                $fshoulder = ($fshoulder - (($bnDepth * 0.25) - 0.25));
            
        } elseif (($bnDepth >= '7') && ($bnDepth < '9')) {
                $fshoulder = ($fshoulder - 1.5);

        } elseif (($bnDepth >= '9') && ($bnDepth <= '14')) {
                $fshoulder = ($fshoulder - 1.5);
    
        } else {
            $fshoulder = ($fshoulder - 1.5);    
        }

        $fshoulder1 = $fshoulder * $cIn;
*/

?>