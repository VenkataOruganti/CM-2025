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

/* Validating the Chest number whether Odd / Even number */

// Adjusting the Upper Chest Measurment with 0.5 inches increase

if (($chest == 30.5) || ($chest == 31.5) || ($chest == 32.5) || ($chest == 33.5) || ($chest == 34.5) || ($chest == 35.5) ||($chest == 36.5) || ($chest == 37.5) || ($chest == 38.5) || ($chest == 39.5) || ($chest == 40.5) || ($chest == 41.5) || ($chest == 42.5) || ($chest == 43.5)) {
    $chest = $chest + 0.5;
}

// deepNeck Calculation

switch ($bnDepth) {
        
     case "4":
        $fshoulder = 6.75 * 2;
        $chestVertical = 6.75;
        break;
        
    case "4.5":
        $fshoulder = 6.5 * 2;
        $chestVertical = 6.5;
        break;
        
    case "5":
        $fshoulder = 6.5 * 2;
        $chestVertical = 6.5;
        break;
        
    case "5.5":
        $fshoulder = 6.25 * 2;
        $chestVertical = 6.25;
        break;
        
    case "6":
        $fshoulder = 6 * 2;
        $chestVertical = 6;
        break;
                
    case "6.5":
        $fshoulder = 5.75 * 2;
        $chestVertical = 5.75;
        break;
        
    case "7":
        $fshoulder = 5.5 * 2;
        $chestVertical = 5.5;
        break;
        
    case "7.5":
        $fshoulder = 5.25 * 2;
        $chestVertical = 5.25;
        break;
        
    case ($bnDepth >= '8'):

        if ($chest == '28') {
            $fshoulder = 4.5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
        } elseif ($chest == '29') {
            $fshoulder = 5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
        } elseif ($chest == '30') {
            $fshoulder = 5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
        } elseif ($chest == '31') {
            $fshoulder = 5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
        } elseif ($chest == '32') {
            $fshoulder = 5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "14.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "15":
                    $chestVertical = $chestVertical + 0.32;
                    break;         

                case "15.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "16":
                    $chestVertical = $chestVertical + 0.5;
                    break;         

                case "16.5":
                    $chestVertical = $chestVertical + 0.5;
                    break; 

                case "17":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "17.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "18":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "18.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;        

                default:
                    echo " ";

            }
            
        } elseif ($chest == '33') {
            $fshoulder = 5 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "14.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "15":
                    $chestVertical = $chestVertical + 0.32;
                    break;         

                case "15.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "16":
                    $chestVertical = $chestVertical + 0.5;
                    break;         

                case "16.5":
                    $chestVertical = $chestVertical + 0.5;
                    break; 

                case "17":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "17.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "18":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "18.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;        

                default:
                    echo " ";

            }
            
        } elseif ($chest == '34') {
            $fshoulder = 5.25 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
                switch ($armhole) {
                    case "14":
                        $chestVertical = $chestVertical + 0.17;
                        break;         

                    case "14.5":
                        $chestVertical = $chestVertical + 0.17;
                        break;

                    case "15":
                        $chestVertical = $chestVertical + 0.25;
                        break;         

                    case "15.5":
                        $chestVertical = $chestVertical + 0.25;
                        break;

                    case "16":
                        $chestVertical = $chestVertical - 0.10;
                        break;         

                    case "16.5":
                        $chestVertical = $chestVertical + 0.25;
                        break; 

                    case "17":
                        $chestVertical = $chestVertical + 0.1;
                        break;         

                    case "17.5":
                        $chestVertical = $chestVertical + 0.1;
                        break;

                    case "18":
                        $chestVertical = $chestVertical + 0.1;
                        break;         

                    case "18.5":
                        $chestVertical = $chestVertical + 0.27;
                        break;        

                    default:
                        echo " ";
                }              
            
        } elseif ($chest == '35') {
            $fshoulder = 5.25 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
                switch ($armhole) {
                    case "14":
                        $chestVertical = $chestVertical + 0.17;
                        break;         

                    case "14.5":
                        $chestVertical = $chestVertical + 0.17;
                        break;

                    case "15":
                        $chestVertical = $chestVertical + 0.25;
                        break;         

                    case "15.5":
                        $chestVertical = $chestVertical + 0.25;
                        break;

                    case "16":
                        $chestVertical = $chestVertical + 0.17;
                        break;         

                    case "16.5":
                        $chestVertical = $chestVertical + 0.5;
                        break; 

                    case "17":
                        $chestVertical = $chestVertical + 0.39;
                        break;         

                    case "17.5":
                        $chestVertical = $chestVertical + 0.39;
                        break;

                    case "18":
                        $chestVertical = $chestVertical + 0.36;
                        break;         

                    case "18.5":
                        $chestVertical = $chestVertical + 0.4;
                        break;        

                    default:
                        echo " ";
                }             
            
            
        } elseif ($chest == '36') {
            $fshoulder = 5.25 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
                switch ($armhole) {
                    case "14":
                        $chestVertical = $chestVertical + 0.17;
                        break;         

                    case "14.5":
                        $chestVertical = $chestVertical + 0.17;
                        break;

                    case "15":
                        $chestVertical = $chestVertical + 0.25;
                        break;         

                    case "15.5":
                        $chestVertical = $chestVertical + 0.25;
                        break;

                    case "16":
                        $chestVertical = $chestVertical + 0.10;
                        break;         

                    case "16.5":
                        $chestVertical = $chestVertical + 0.25;
                        break; 

                    case "17":
                        $chestVertical = $chestVertical + 0.1;
                        break;         

                    case "17.5":
                        $chestVertical = $chestVertical + 0.1;
                        break;

                    case "18":
                        $chestVertical = $chestVertical + 0.1;
                        break;         

                    case "18.5":
                        $chestVertical = $chestVertical + 0.27;
                        break;        

                    default:
                        echo " ";

                }              
            
        } elseif ($chest == '37') {
            $fshoulder = 5.25 * 2;
            $chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "14.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "15":
                    $chestVertical = $chestVertical + 0.32;
                    break;         

                case "15.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "16":
                    $chestVertical = $chestVertical + 0.5;
                    break;         

                case "16.5":
                    $chestVertical = $chestVertical + 0.5;
                    break; 

                case "17":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "17.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "18":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "18.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;        

                default:
                    echo " ";

            }
            
        } elseif ($chest == '38') {
            $fshoulder = 5.5 * 2;
            //$chestVertical = 5.5;        Creative Fashions Calculation
            $chestVertical = ((($armhole / 2) - 1) - 1.9); // Venkata's Calculation
                
                switch ($armhole) {
                    case "14":
                        $chestVertical = $chestVertical + 0.27;
                        break;         

                    case "14.5":
                        $chestVertical = $chestVertical + 0.27;
                        break;

                    case "15":
                        $chestVertical = $chestVertical + 0.32;
                        break;         

                    case "15.5":
                        $chestVertical = $chestVertical + 0.27;
                        break;

                    case "16":
                        //$chestVertical = $chestVertical + 0.5;
                        break;         

                    case "16.5":
                        //$chestVertical = $chestVertical + 0.5;
                        break; 

                    case "17":
                        $chestVertical = $chestVertical + 0.3;
                        break;         

                    case "17.5":
                        $chestVertical = $chestVertical + 0.35;
                        break;

                    case "18":
                        $chestVertical = $chestVertical + 0.36;
                        break;         

                    case "18.5":
                        $chestVertical = $chestVertical + 0.35;
                        break;        

                    default:
                        echo " ";

                }
        
            
        } elseif ($chest == '39') {
            $fshoulder = 5.5 * 2;
            //$chestVertical = 5.5;        Creative Fashions Calculation
            $chestVertical = ((($armhole / 2) - 1) - 2.25); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "14.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "15":
                    $chestVertical = $chestVertical + 0.32;
                    break;         

                case "15.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "16":
                    $chestVertical = $chestVertical + 0.5;
                    break;         

                case "16.5":
                    $chestVertical = $chestVertical + 0.5;
                    break; 

                case "17":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "17.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "18":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "18.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;        

                default:
                    echo " ";

            }
            
        } elseif ($chest == '40') {
            $fshoulder = 5.5 * 2;
            //$chestVertical = 5.5;        Creative Fashions Calculation
            $chestVertical = ((($armhole / 2) - 1) - 3); // Venkata's Calculation
            
                switch ($armhole) {
                    case "14":
                        $chestVertical = $chestVertical + 0.27;
                        break;         

                    case "14.5":
                        $chestVertical = $chestVertical + 0.27;
                        break;

                    case "15":
                        $chestVertical = $chestVertical + 0.6;
                        break;         

                    case "15.5":
                        $chestVertical = $chestVertical + 0.65;
                        break;

                    case "16":
                        $chestVertical = $chestVertical + 0.5;
                        break;         

                    case "16.5":
                        $chestVertical = $chestVertical + 0.5;
                        break; 

                    case "17":
                        $chestVertical = $chestVertical + 0.8;
                        break;         

                    case "17.5":
                        $chestVertical = $chestVertical + 0.9;
                        break;

                    case "18":
                        $chestVertical = $chestVertical + 0.95;
                        break;         

                    case "18.5":
                        $chestVertical = $chestVertical + 0.95;
                        break;        

                    default:
                        echo " ";
                }            
            
        } elseif ($chest == '41') {
            $fshoulder = 5.5 * 2;
            //$chestVertical = 5.5;      Creative Fashions Calculation
            $chestVertical = ((($armhole / 2) - 1) - 2.25); // Venkata's Calculation
            
        } elseif ($chest >= '42') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "14.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "15":
                    $chestVertical = $chestVertical + 0.32;
                    break;         

                case "15.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "16":
                    $chestVertical = $chestVertical + 0.5;
                    break;         

                case "16.5":
                    $chestVertical = $chestVertical + 0.5;
                    break; 

                case "17":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "17.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;

                case "18":
                    $chestVertical = $chestVertical + 0.27;
                    break;         

                case "18.5":
                    $chestVertical = $chestVertical + 0.27;
                    break;        

                default:
                    echo " ";

            }
            
        } else {
            $fshoulder= $fshoulder;
        }
                
        break;
    
    default:
        echo " ";
}

/* Half-inch Armhole Calculation

*/
    $fshoulder1 = $fshoulder * $cIn;
    $chestVertical = $chestVertical * $cIn;
 
?>