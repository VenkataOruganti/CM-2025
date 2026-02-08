<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back

/*
// Front ArmHole Depth

    Small: 24-28: 1 Inch
    Medium: 28-36: 0.75 Inch
    Large: 36-44: .5 Inch
    XL - XXL: 24-28: 0.25 Inch

- If the Chest Size is less than 32 deduct .5 inch from shoulder measurement
- If the Chest Size is larger than 38 add 0.5 In to the shoulder measurement.

BackNeck Depth    Shoulder    Armhole

4" - 6.75" - 6.75"
4.5" - 6.5" - 6.5"
5" - 6.5 - 6.5
5.5" - 6.25" - 6.25
6" - 6" -6"
6.5" - 5.75" - 5.75"
7" -5.5" - 5.5"
7.5" - 5.25" - 5.25"

//If Back Neck Depth 8" or More, follow the below:

Chest Size        Shoulder       Armhole

28-30            4.5'      4.5"
32-34            5"         5"
34-36            5.25"       5.25"
36-38            5.25"       5.25"
38-40            5.5"        5.5"
42 above         5.75"       5.75"
*/

/* Validating the Chest number whether Odd / Even number 
Chest Vertical Line common formulas, Manual Processes:
1. Full Shoulder / 2
2. Chest or Bust / 4 - 2 + 0.5
3. Bust or Chest / 6 + 2 - We will follow this formula for the calculation

*/
        if ($chest == '36') {
            $chestVertical = ((($chest / 4) - 2) - 0.5); // Venkata's Calculation      
            
            switch ($armhole) {
                case "16":
                    $chestVertical = $chestVertical + 0;
                    break;        

                case "16.5":
                    $chestVertical = $chestVertical + 0;
                    break;        

                case "17":
                    $chestVertical = $chestVertical + 0;
                    break;        
                    
                default:
                    echo " ";
                }
        }


    $chestVertical = $chestVertical * $cIn; 
?>