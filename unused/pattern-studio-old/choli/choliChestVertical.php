<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back
         
/* Chest Vertical Calculation
    - 28” to 30” = ¼ chest-¾’ to 1”
    - 31” to 33” =¼ chest-1” to 1¼”
    - 34” to 36” = ¼ chest-1½” to 2”
    - 37 “ to 39” = ¼ chest- 2¼” to 2¾”
    - 40 to 42 = ¼ chest-3” to 3½”
*/         

    switch ($chest) {
      
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
                
        default:
            $chestVertical = ((($chest / 4) - 0.5) * $cIn);
            break;    
            }

        $chestVertical = (($saround / 2) * $cIn);
?>