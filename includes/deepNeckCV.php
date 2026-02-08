<?php
		$topMargin = 0;
        $topPadding = (0.25 * $cIn); 
		$fLeft = 0;
		$seam = (0.5 * $cIn);
		$bDart = (($chest - $waist) / 4) ; //bottom Dart - front and back

/*

Front ArmHole Depth

    Small: 24-28: 1 Inch
    Medium: 28-36: 0.75 Inch
    Large: 36-44: .5 Inch
    XL - XXL: 24-28: 0.25 Inch

- If the Chest Size is less than 32 deduct .5 inch from shoulder measurement, its only for back depth less than 8'
- If the Chest Size is larger than 38 add 0.5 In to the shoulder measurement,  its only for back depth less than 8'

BackNeck Depth    chestVertical    Armhole

4" -                6.75" -     6.75"
4.5" -              6.5" -      6.5"
5" -                6.5 -       6.5
5.5" -              6.25" -     6.25
6" -                6" -        6"
6.5" -              5.75" -     5.75"
7" -                5.5" -      5.5"
7.5" -              5.25" -     5.25"

//If Back Neck Depth 8" or More, follow the below:

Chest Size        Shoulder       Armhole

28-30            4.5'           4.5"
32-34            5"             5"
34-38            5.25"          5.25"
39-40            5.5"           5.5"
42 above         5.75"          5.75"

*/

/* Validating the Chest number whether Odd / Even number */
// Adjusting the Chest Measurment with 0.5 inches increase

if (($chest == 30.5) || ($chest == 31.5) || ($chest == 32.5) || ($chest == 33.5) || ($chest == 34.5) || ($chest == 35.5) ||($chest == 36.5) || ($chest == 37.5) || ($chest == 38.5) || ($chest == 39.5) || ($chest == 40.5) || ($chest == 41.5) || ($chest == 42.5) || ($chest == 43.5)) {
    $chest = $chest + 0.5;
}

// deepNeck Calculation

switch ($bnDepth) {
        
     case $bnDepth <= '4':

        if ($chest == '28') {
            $fshoulder = 6.25 * 2;
            $chestVertical = 6.75;
            
            switch ($armhole) {
                case "14":        $chestVertical = $chestVertical + 0.5;  break;
                case "14.5":      $chestVertical = $chestVertical + 0.5;  break;
                case "15":        $chestVertical = $chestVertical + 0.5;  break;
                case "15.5":      $chestVertical = $chestVertical + 0.5;  break;
                case "16":        $chestVertical = $chestVertical + 0.5;  break;
                case "16.5":      $chestVertical = $chestVertical + 0.5;  break;
                case "17":        $chestVertical = $chestVertical + 0.5;  break;
                case "17.5":      $chestVertical = $chestVertical + 0.5;  break;
                case "18":        $chestVertical = $chestVertical + 0.5;  break;
                case "18.5":      $chestVertical = $chestVertical + 0.5;  break;
                case "19":        $chestVertical = $chestVertical + 0.5;  break;
                default:          echo " ";
            }
            
        } elseif ( $chest == '30' ) {
            $fshoulder = 6.5 * 2;
            $chestVertical = 7;
            
           switch ($armhole) {
               case "14":        $chestVertical = $chestVertical + 0;       break;
               case "14.5":      $chestVertical = $chestVertical + 0.25;    break;
               case "15":        $chestVertical = $chestVertical + 0.5;     break;
               case "15.5":      $chestVertical = $chestVertical + 0.75;    break;
               case "16":        $chestVertical = $chestVertical + 1.0;     break;
               case "16.5":      $chestVertical = $chestVertical + 1.25;    break;
               case "17":        $chestVertical = $chestVertical + 1.50;    break;
               case "17.5":      $chestVertical = $chestVertical + 1.75;    break;
               case "18":        $chestVertical = $chestVertical + 2.0;     break;               
               default:           echo " ";
            }
            
        } elseif ($chest == '32') {
            $fshoulder = 6.75 * 2;
            $chestVertical = 7;

            switch ($armhole) {
                case "14.5":        $chestVertical = $chestVertical + 0.1;      break;
                case "15":          $chestVertical = $chestVertical + 0.35;     break;
                case "15.5":        $chestVertical = $chestVertical + 0.6;      break;
                case "16":          $chestVertical = $chestVertical + 0.85;     break;
                case "16.5":        $chestVertical = $chestVertical + 1.05;     break;
                case "17":          $chestVertical = $chestVertical + 1.30;     break;
                case "17.5":        $chestVertical = $chestVertical + 1.60;     break;
                case "18":          $chestVertical = $chestVertical + 1.85;     break;
                default:            echo " ";
            }
           
        } elseif ($chest == '34') {
            $fshoulder = 7 * 2;
            $chestVertical = 7.5;
            
          switch ($armhole) {                    
                case "14":      $chestVertical = $chestVertical - 0.82;     break;         
                case "14.5":    $chestVertical = $chestVertical - 0.57;     break;
                case "15":      $chestVertical = $chestVertical - 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical - 0.07;     break;
                case "16":      $chestVertical = $chestVertical + 0.18;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.43;     break;
                case "17":      $chestVertical = $chestVertical + 0.68;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.93;     break;
                case "18":      $chestVertical = $chestVertical + 1.18;     break;
                default:        echo " ";
            }

            
        } elseif ($chest == '35') {
            $fshoulder = 7.25 * 2;
            $chestVertical = 7.5;

            switch ($armhole) {                    
                case "14":      $chestVertical = $chestVertical - 1.0;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.75;     break;
                case "15":      $chestVertical = $chestVertical - 0.5;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "16":      $chestVertical = $chestVertical + 0.05;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.28;     break;
                case "17":      $chestVertical = $chestVertical + 0.55;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.8;      break;
                case "18":      $chestVertical = $chestVertical + 1.1;      break;
                default:        echo " ";
            }
            
            
        } elseif ($chest == '36') {
            $fshoulder = 7.25 * 2;
            $chestVertical = 7.5;

            switch ($armhole) {                    
                case "14":      $chestVertical = $chestVertical - 1.0;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.75;     break;
                case "15":      $chestVertical = $chestVertical - 0.5;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "16":      $chestVertical = $chestVertical + 0.05;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.28;     break;
                case "17":      $chestVertical = $chestVertical + 0.55;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.8;      break;
                case "18":      $chestVertical = $chestVertical + 1.1;      break;
                default:        echo " ";
            }
            
        } elseif ($chest == '38') {
            $fshoulder = 7.5 * 2;
            $chestVertical = 8;

            switch ($armhole) {                    
                case "14":      $chestVertical = $chestVertical - 1.55;     break;         
                case "14.5":    $chestVertical = $chestVertical - 1.3;      break;
                case "15":      $chestVertical = $chestVertical - 1.05;     break;
                case "15.5":    $chestVertical = $chestVertical - 0.85;     break;
                case "16":      $chestVertical = $chestVertical - 0.6;      break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.08;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.2;      break;
                case "18":      $chestVertical = $chestVertical + 0.45;     break;                    
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 0.90;     break;    
                default:        echo " ";
            }

        } elseif ($chest == '40') {
            $fshoulder = 7.75 * 2;
            $chestVertical = 7.75;
                           
            switch ($armhole) {
                case "15":      $chestVertical = $chestVertical - 1.05;   break; 
                case "15.5":    $chestVertical = $chestVertical - 0.8;    break;     
                case "16":      $chestVertical = $chestVertical - 0.5;    break;
                case "16.5":    $chestVertical = $chestVertical - 0.25;   break;
                case "17":      $chestVertical = $chestVertical + 0;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.25;   break;
                case "18":      $chestVertical = $chestVertical + 0.5;    break;
                case "18.5":    $chestVertical = $chestVertical + 0.75;   break;
                case "19":      $chestVertical = $chestVertical + 1.0;    break;
                case "19.5":    $chestVertical = $chestVertical + 1.25;   break;
                case "20":      $chestVertical = $chestVertical + 1.5;    break;
                default:        echo " ";
            }            
        }        
        break;

    case "4.5":
        $fshoulder = 6.5 * 2;
        $chestVertical = 6.5;
        break;
        
    case "5":
        if ($chest == '32') {
            $fshoulder = 6.5 * 2;
            $chestVertical = 7.0;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.25;  break;
                    case "14.5":    $chestVertical = $chestVertical + 0;     break;
                    case "15":      $chestVertical = $chestVertical + 0.25;  break;
                    case "15.5":    $chestVertical = $chestVertical + 0.5;   break;
                    case "16":      $chestVertical = $chestVertical + 0.75;  break;
                    case "16.5":    $chestVertical = $chestVertical + 1.05;  break;
                    case "17":      $chestVertical = $chestVertical + 1.3;   break;
                    case "17.5":    $chestVertical = $chestVertical + 1.55;  break;                         
                    case "18":      $chestVertical = $chestVertical + 1.85;  break;

                    default:        echo " ";
                } 
          } elseif ($chest == '34') {
            $fshoulder = 6.5 * 2;
            $chestVertical = 7.0;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.5;      break;
                    case "14.5":    $chestVertical = $chestVertical - 0.25;     break;
                    case "15":      $chestVertical = $chestVertical + 0;        break;
                    case "15.5":    $chestVertical = $chestVertical + 0.25;     break;
                    case "16":      $chestVertical = $chestVertical + 0.5;      break;
                    case "16.5":    $chestVertical = $chestVertical + 0.75;     break;
                    case "17":      $chestVertical = $chestVertical + 1.05;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.3;      break;                         
                    case "18":      $chestVertical = $chestVertical + 1.55;     break;

                    default:        echo " ";
                } 
           
        } elseif ($chest == '36') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.98;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.60;     break;
                    case "15":      $chestVertical = $chestVertical - 0.28;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.06;     break;
                    case "16":      $chestVertical = $chestVertical + 0.30;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.55;     break;
                    case "17":      $chestVertical = $chestVertical + 0.80;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.10;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.35;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.60;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.90;     break;                   
                        
                    default:        echo " ";
                } 
           
        } elseif ($chest == '38') {
            $fshoulder = 6.25 * 2;
            $chestVertical = 6.75;  // Creative Fashions Calculation
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.3;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.05;     break;
                    case "15":      $chestVertical = $chestVertical - 0.75;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.50;     break;
                    case "16":      $chestVertical = $chestVertical - 0.25;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.58;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.85;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.16;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.40;     break;
                    default:        echo " ";
                }
        }
        break;
            
    case "6":
        
        if ($chest == '32') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.1;     break;
                    case "14.5":    $chestVertical = $chestVertical + 0;     break;
                    case "15":      $chestVertical = $chestVertical + 0.5;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.75;     break;
                    case "16":      $chestVertical = $chestVertical + 1.0;     break;
                    case "16.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "17":      $chestVertical = $chestVertical + 1.5;     break;
                    case "17.5":    $chestVertical = $chestVertical + 2.05;     break;                         
                    case "18":      $chestVertical = $chestVertical + 2.65;     break;
                    case "18.5":    $chestVertical = $chestVertical + 3.02;     break;
                    default:        echo " ";
                } 
           
        }elseif ($chest == '34') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.15;     break;
                    case "15":      $chestVertical = $chestVertical + 0.1;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.38;     break;
                    case "16":      $chestVertical = $chestVertical + 0.68;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.95;     break;
                    case "17":      $chestVertical = $chestVertical + 1.2;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.35;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.77;     break;
                    case "18.5":    $chestVertical = $chestVertical + 2.02;     break;                        
                    default:        echo " ";
                } 
           
        } elseif ($chest == '35') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.74;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.45;     break;
                    case "15":      $chestVertical = $chestVertical - 0.16;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.12;     break;
                    case "16":      $chestVertical = $chestVertical + 0.43;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.7;     break;
                    case "17":      $chestVertical = $chestVertical + 0.96;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.22;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.5;     break;                         
                    default:        echo " ";
                } 
           
        } elseif ($chest == '36') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.98;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.6;     break;
                    case "15":      $chestVertical = $chestVertical - 0.28;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.03;     break;
                    case "16":      $chestVertical = $chestVertical + 0.25;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.5;     break;
                    case "17":      $chestVertical = $chestVertical + 0.75;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.0;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.3;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.55;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;                   
                        
                    default:        echo " ";
                } 
           
        } elseif ($chest == '38') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;  // Creative Fashions Calculation
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.3;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.05;     break;
                    case "15":      $chestVertical = $chestVertical - 0.75;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.50;     break;
                    case "16":      $chestVertical = $chestVertical - 0.25;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.58;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.85;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.16;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.40;     break;                   
                        
                    default:
                        echo " ";
                }       
        }
        break;
        
    case "7":
        
        if ($chest == '28') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;      break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "14":      $chestVertical = $chestVertical + 0.75;      break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;     break;
                case "15":      $chestVertical = $chestVertical + 1.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;     break;
                case "16":      $chestVertical = $chestVertical + 1.75;      break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;        break;
                case "18":      $chestVertical = $chestVertical + 2.85;     break;
             
                default:        echo " ";
                }
  
        } elseif ($chest == '28.5') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;   break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;   break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;    break;
                case "14":      $chestVertical = $chestVertical + 0.75;   break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;    break;
                case "15":      $chestVertical = $chestVertical + 1.25;   break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;    break;
                case "16":      $chestVertical = $chestVertical + 1.75;   break;
                case "16.5":    $chestVertical = $chestVertical + 2;      break;
                case "17":      $chestVertical = $chestVertical + 2.25;   break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;    break;
                case "18":      $chestVertical = $chestVertical + 2.85;   break;
                default:        echo " ";
                }

            
        } elseif ($chest == '29') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {
                case "12":      $chestVertical = $chestVertical - 0.25;      break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "14":      $chestVertical = $chestVertical + 0.75;      break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;     break;
                case "15":      $chestVertical = $chestVertical + 1.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;     break;
                case "16":      $chestVertical = $chestVertical + 1.75;      break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;        break;
                case "18":      $chestVertical = $chestVertical + 2.85;     break;             
                default:        echo " ";
            }

            
        } elseif ($chest == '29.5') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            //$chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {
                case "12":      $chestVertical = $chestVertical - 0.25;      break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "14":      $chestVertical = $chestVertical + 0.75;      break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;     break;
                case "15":      $chestVertical = $chestVertical + 1.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;     break;
                case "16":      $chestVertical = $chestVertical + 1.75;      break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;        break;
                case "18":      $chestVertical = $chestVertical + 2.85;     break;             
                default:        echo " ";
            }

        } elseif ($chest == '30') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            //$chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {
                case "12":      $chestVertical = $chestVertical - 1.4;      break;
                case "12.5":    $chestVertical = $chestVertical - 1.15;      break;
                case "13":      $chestVertical = $chestVertical + 0.1;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.35;      break;
                case "14":      $chestVertical = $chestVertical + 0.6;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.85;     break;
                case "15":      $chestVertical = $chestVertical + 1.1;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.37;     break;
                case "16":      $chestVertical = $chestVertical + 1.68;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.95;     break;
                case "17":      $chestVertical = $chestVertical + 2.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.45;        break;
                case "18":      $chestVertical = $chestVertical + 2.73;     break;             
                default:        echo " ";
            }

        } elseif ($chest == '30.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "12":      $chestVertical = $chestVertical - 0.8;      break;
                case "12.5":    $chestVertical = $chestVertical - 0.55;      break;
                case "13":      $chestVertical = $chestVertical - 0.2;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.15;      break;
                case "14":      $chestVertical = $chestVertical + 0.35;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.6;     break;
                case "15":      $chestVertical = $chestVertical + 0.85;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.10;     break;
                case "16":      $chestVertical = $chestVertical + 1.35;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.6;     break;
                case "17":      $chestVertical = $chestVertical + 1.85;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.1;        break;
                case "18":      $chestVertical = $chestVertical + 2.35;     break;             
                default:        echo " ";
            }
            
        } elseif ($chest == '31') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.8;      break;
                case "12.5":    $chestVertical = $chestVertical - 0.55;      break;
                case "13":      $chestVertical = $chestVertical - 0.2;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.15;      break;
                case "14":      $chestVertical = $chestVertical + 0.35;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.6;     break;
                case "15":      $chestVertical = $chestVertical + 0.85;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.10;     break;
                case "16":      $chestVertical = $chestVertical + 1.35;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.6;     break;
                case "17":      $chestVertical = $chestVertical + 1.85;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.1;        break;
                case "18":      $chestVertical = $chestVertical + 2.35;     break;             
                default:        echo " ";
            }
            
        } elseif ($chest == '31.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.35;     break;
                case "15":      $chestVertical = $chestVertical + 0.65;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.90;     break;
                case "16":      $chestVertical = $chestVertical + 1.2;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.45;     break;
                case "17":      $chestVertical = $chestVertical + 1.7;      break;
                case "17.5":    $chestVertical = $chestVertical + 2;        break;
                case "18":      $chestVertical = $chestVertical + 2.25;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '32') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.35;     break;
                case "15":      $chestVertical = $chestVertical + 0.65;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.90;     break;
                case "16":      $chestVertical = $chestVertical + 1.2;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.45;     break;
                case "17":      $chestVertical = $chestVertical + 1.7;      break;
                case "17.5":    $chestVertical = $chestVertical + 2;        break;
                case "18":      $chestVertical = $chestVertical + 2.25;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '32.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.24;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.05;     break;
                case "15":      $chestVertical = $chestVertical + 0.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.6;      break;
                case "16":      $chestVertical = $chestVertical + 0.88;     break;
                case "16.5":    $chestVertical = $chestVertical + 1.2;      break;
                case "17":      $chestVertical = $chestVertical + 1.44;     break;
                case "17.5":    $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
           
        } elseif ($chest == '33') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.24;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.05;     break;
                case "15":      $chestVertical = $chestVertical + 0.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.6;      break;
                case "16":      $chestVertical = $chestVertical + 0.88;     break;
                case "16.5":    $chestVertical = $chestVertical + 1.2;      break;
                case "17":      $chestVertical = $chestVertical + 1.44;     break;
                case "17.5":    $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
           
        } elseif ($chest == '33.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
           
            switch ($armhole) {

                case "14":      $chestVertical = $chestVertical - 0.5;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "15":      $chestVertical = $chestVertical + 0.10;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.40;     break;
                case "16":      $chestVertical = $chestVertical + 0.65;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;      break; 
                case "17":      $chestVertical = $chestVertical + 1.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;      break;
                case "18":      $chestVertical = $chestVertical + 1.75;     break;             
                default:        echo " ";
            }
            
        } elseif ($chest == '34') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
           
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.5;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "15":      $chestVertical = $chestVertical + 0.10;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.40;     break;
                case "16":      $chestVertical = $chestVertical + 0.65;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;      break; 
                case "17":      $chestVertical = $chestVertical + 1.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;      break;
                case "18":      $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '34.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.55;     break;
                case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                case "15":      $chestVertical = $chestVertical - 0.15;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                case "16":      $chestVertical = $chestVertical + 0.40;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                case "17":      $chestVertical = $chestVertical + 0.9;      break;
                case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                case "18":      $chestVertical = $chestVertical + 1.5;      break;
                case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                default:        echo " ";
            } 
            
        } elseif ($chest == '35') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.55;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "15":      $chestVertical = $chestVertical - 0.15;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.40;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                    case "17":      $chestVertical = $chestVertical + 0.9;      break;
                    case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "18":      $chestVertical = $chestVertical + 1.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '35.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical + 0.1;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.4;     break;
                    case "17":      $chestVertical = $chestVertical + 0.65;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.95;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.3;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.55;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;                        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '36') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical + 0.1;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.4;     break;
                    case "17":      $chestVertical = $chestVertical + 0.65;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.95;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.3;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.55;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;                        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '36.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '37') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6 ;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '37.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical - 0.3;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.05;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.50;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.8;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.05;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.30;     break;                         
                    default:        echo " ";
                } 
       
        } elseif ($chest == '38') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical - 0.3;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.05;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.50;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.8;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.05;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.30;     break;                         
                    default:        echo " ";
                } 
       
        } elseif ($chest == '38.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25; //Creative
            
            switch ($armhole) {

                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '39') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;        //Creative
            
            switch ($armhole) {

                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '39.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;  //Creative
            
                switch ($armhole) {

                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break;                         
                    default:        echo " ";
                }            

        } elseif ($chest == '40') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;  //Creative
            
                switch ($armhole) {

                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break; 
                    default:        echo " ";
                }            

        } elseif ($chest == '41') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;      //Creative 
            //$chestVertical = ((($armhole / 2) - 1) - 2.25); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;       
                default:        echo " ";
            }    
            
        } elseif ($chest >= '41.5') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;   
                default:        echo " ";
            }
            
        } elseif ($chest >= '42') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;        
                default:        echo " ";
            }
            
        } else {
            $fshoulder= $fshoulder;
        }
        break;  
        
        case "7.5":
        
        if ($chest == '28') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;   break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;   break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;    break;
                case "14":      $chestVertical = $chestVertical + 0.75;   break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;    break;
                case "15":      $chestVertical = $chestVertical + 1.25;   break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;    break;
                case "16":      $chestVertical = $chestVertical + 1.75;   break;
                case "16.5":    $chestVertical = $chestVertical + 2;      break;
                case "17":      $chestVertical = $chestVertical + 2.25;   break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;    break;
                case "18":      $chestVertical = $chestVertical + 2.85;   break;
             
                default:        echo " ";
                }
  
        } elseif ($chest == '28.5') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;   break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;   break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;    break;
                case "14":      $chestVertical = $chestVertical + 0.75;   break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;    break;
                case "15":      $chestVertical = $chestVertical + 1.25;   break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;    break;
                case "16":      $chestVertical = $chestVertical + 1.75;   break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;   break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;    break;
                case "18":      $chestVertical = $chestVertical + 2.85;   break;
                default:        echo " ";
                }

            
        } elseif ($chest == '29') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;      break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "14":      $chestVertical = $chestVertical + 0.75;      break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;     break;
                case "15":      $chestVertical = $chestVertical + 1.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;     break;
                case "16":      $chestVertical = $chestVertical + 1.75;      break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;        break;
                case "18":      $chestVertical = $chestVertical + 2.85;     break;             
                default:        echo " ";
                }

            
        } elseif ($chest == '29.5') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            //$chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.25;      break;
                case "12.5":    $chestVertical = $chestVertical - 0;      break;
                case "13":      $chestVertical = $chestVertical + 0.25;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "14":      $chestVertical = $chestVertical + 0.75;      break;
                case "14.5":    $chestVertical = $chestVertical + 1.0;     break;
                case "15":      $chestVertical = $chestVertical + 1.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.5;     break;
                case "16":      $chestVertical = $chestVertical + 1.75;      break;
                case "16.5":    $chestVertical = $chestVertical + 2;     break;
                case "17":      $chestVertical = $chestVertical + 2.25;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.6;        break;
                case "18":      $chestVertical = $chestVertical + 2.85;     break;             
                default:        echo " ";
                }

        } elseif ($chest == '30') {
            $fshoulder = 4.75 * 2;
            $chestVertical = 5.25;
            //$chestVertical = ((($armhole / 2) - 1) - 1.5); // Venkata's Calculation
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 1.4;      break;
                case "12.5":    $chestVertical = $chestVertical - 1.15;      break;
                case "13":      $chestVertical = $chestVertical + 0.1;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.35;      break;
                case "14":      $chestVertical = $chestVertical + 0.6;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.85;     break;
                case "15":      $chestVertical = $chestVertical + 1.1;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.37;     break;
                case "16":      $chestVertical = $chestVertical + 1.68;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.95;     break;
                case "17":      $chestVertical = $chestVertical + 2.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.45;        break;
                case "18":      $chestVertical = $chestVertical + 2.73;     break;             
                default:        echo " ";
                }

        } elseif ($chest == '30.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.8;      break;
                case "12.5":    $chestVertical = $chestVertical - 0.55;      break;
                case "13":      $chestVertical = $chestVertical - 0.2;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.15;      break;
                case "14":      $chestVertical = $chestVertical + 0.35;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.6;     break;
                case "15":      $chestVertical = $chestVertical + 0.85;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.10;     break;
                case "16":      $chestVertical = $chestVertical + 1.35;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.6;     break;
                case "17":      $chestVertical = $chestVertical + 1.85;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.1;        break;
                case "18":      $chestVertical = $chestVertical + 2.35;     break;             
                default:        echo " ";
                }
            
        } elseif ($chest == '31') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {

                case "12":      $chestVertical = $chestVertical - 0.8;      break;
                case "12.5":    $chestVertical = $chestVertical - 0.55;      break;
                case "13":      $chestVertical = $chestVertical - 0.2;      break;
                case "13.5":    $chestVertical = $chestVertical + 0.15;      break;
                case "14":      $chestVertical = $chestVertical + 0.35;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.6;     break;
                case "15":      $chestVertical = $chestVertical + 0.85;     break;
                case "15.5":    $chestVertical = $chestVertical + 1.10;     break;
                case "16":      $chestVertical = $chestVertical + 1.35;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.6;     break;
                case "17":      $chestVertical = $chestVertical + 1.85;      break;
                case "17.5":    $chestVertical = $chestVertical + 2.1;        break;
                case "18":      $chestVertical = $chestVertical + 2.35;     break;             
                default:        echo " ";
                }
            
        } elseif ($chest == '31.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.35;     break;
                case "15":      $chestVertical = $chestVertical + 0.65;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.90;     break;
                case "16":      $chestVertical = $chestVertical + 1.2;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.45;     break;
                case "17":      $chestVertical = $chestVertical + 1.7;      break;
                case "17.5":    $chestVertical = $chestVertical + 2;        break;
                case "18":      $chestVertical = $chestVertical + 2.25;     break;
                default:        echo " ";
                }
            
        } elseif ($chest == '32') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;      break;
                case "14.5":    $chestVertical = $chestVertical + 0.35;     break;
                case "15":      $chestVertical = $chestVertical + 0.65;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.90;     break;
                case "16":      $chestVertical = $chestVertical + 1.2;      break;
                case "16.5":    $chestVertical = $chestVertical + 1.45;     break;
                case "17":      $chestVertical = $chestVertical + 1.7;      break;
                case "17.5":    $chestVertical = $chestVertical + 2;        break;
                case "18":      $chestVertical = $chestVertical + 2.25;     break;
                default:        echo " ";
                }
            
        } elseif ($chest == '32.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.24;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.05;     break;
                case "15":      $chestVertical = $chestVertical + 0.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.6;      break;
                case "16":      $chestVertical = $chestVertical + 0.88;     break;
                case "16.5":    $chestVertical = $chestVertical + 1.2;      break;
                case "17":      $chestVertical = $chestVertical + 1.44;     break;
                case "17.5":    $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
           
        } elseif ($chest == '33') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.24;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.05;     break;
                case "15":      $chestVertical = $chestVertical + 0.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.6;      break;
                case "16":      $chestVertical = $chestVertical + 0.88;     break;
                case "16.5":    $chestVertical = $chestVertical + 1.2;      break;
                case "17":      $chestVertical = $chestVertical + 1.44;     break;
                case "17.5":    $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
           
        } elseif ($chest == '33.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
           
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical - 0.5;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "15":      $chestVertical = $chestVertical + 0.10;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.40;     break;
                case "16":      $chestVertical = $chestVertical + 0.65;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;      break; 
                case "17":      $chestVertical = $chestVertical + 1.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;      break;
                case "18":      $chestVertical = $chestVertical + 1.75;     break;             
                default:        echo " ";
                }
            
        } elseif ($chest == '34') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
           
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical - 0.5;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.25;     break;
                case "15":      $chestVertical = $chestVertical + 0.10;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.40;     break;
                case "16":      $chestVertical = $chestVertical + 0.65;     break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;      break; 
                case "17":      $chestVertical = $chestVertical + 1.2;      break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;      break;
                case "18":      $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
                }
            
        } elseif ($chest == '34.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.55;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "15":      $chestVertical = $chestVertical - 0.15;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.40;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                    case "17":      $chestVertical = $chestVertical + 0.9;      break;
                    case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "18":      $chestVertical = $chestVertical + 1.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '35') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.55;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "15":      $chestVertical = $chestVertical - 0.15;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.40;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                    case "17":      $chestVertical = $chestVertical + 0.9;      break;
                    case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "18":      $chestVertical = $chestVertical + 1.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '35.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical + 0.1;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.4;     break;
                    case "17":      $chestVertical = $chestVertical + 0.65;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.95;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.3;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.55;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;                        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '36') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical + 0.1;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.4;     break;
                    case "17":      $chestVertical = $chestVertical + 0.65;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.95;     break;                         
                    case "18":      $chestVertical = $chestVertical + 1.3;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.55;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;                        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '36.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '37') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6 ;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '37.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical - 0.3;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.05;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.50;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.8;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.05;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.30;     break;                         
                    default:        echo " ";
                } 
       
        } elseif ($chest == '38') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
              
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 1.4;     break;
                    case "14.5":    $chestVertical = $chestVertical - 1.15;     break;
                    case "15":      $chestVertical = $chestVertical - 0.85;     break;
                    case "15.5":    $chestVertical = $chestVertical - 0.55;     break;
                    case "16":      $chestVertical = $chestVertical - 0.3;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.05;     break;
                    case "17":      $chestVertical = $chestVertical + 0.25;     break;
                    case "17.5":    $chestVertical = $chestVertical + 0.50;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.8;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.05;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.30;     break;                         
                    default:        echo " ";
                } 
       
        } elseif ($chest == '38.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25; //Creative
            
            switch ($armhole) {

                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '39') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;        //Creative
            
            switch ($armhole) {

                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;     
                default:        echo " ";
            }
            
        } elseif ($chest == '39.5') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;  //Creative
            
                switch ($armhole) {

                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break;                         
                    default:        echo " ";
                }            

        } elseif ($chest == '40') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;  //Creative
            
                switch ($armhole) {

                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break; 
                    default:        echo " ";
                }            

        } elseif ($chest == '41') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;      //Creative             
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;       
                default:        echo " ";
            }    
            
        } elseif ($chest >= '41.5') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;   
                default:        echo " ";
            }
            
        } elseif ($chest >= '42') {
            $fshoulder = 6 * 2;
            $chestVertical = 6.5;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;
                default:        echo " ";
            }
            
        } else {
            $fshoulder= $fshoulder;
        }
        break;           
        
    case ($bnDepth >= '8'):

        if ($chest == '27') {
            $fshoulder = 4.5 * 2;
            $chestVertical = 5;
            
        } elseif ($chest == '28.5') {
            $fshoulder = 4.5 * 2;
            $chestVertical = 5;
         
            switch ($armhole) {
                case "13":      $chestVertical = $chestVertical + 0.39;  break;  
                case "13.5":    $chestVertical = $chestVertical + 0.66; break;
                case "14":      $chestVertical = $chestVertical + 0.93;  break;  
                case "14.5":    $chestVertical = $chestVertical + 1.20; break;
                case "15":      $chestVertical = $chestVertical + 1.47; break;
                case "15.5":    $chestVertical = $chestVertical + 1.73; break;
                case "16":      $chestVertical = $chestVertical + 2;  break;
                case "16.5":    $chestVertical = $chestVertical + 2.27; break; 
                case "17":      $chestVertical = $chestVertical + 2.53;  break;
                case "17.5":    $chestVertical = $chestVertical + 2.8;   break;
                case "18":      $chestVertical = $chestVertical + 3.06; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '29') {
            $fshoulder = 4.5 * 2;
            $chestVertical = 5;
         
            switch ($armhole) {
                case "13":      $chestVertical = $chestVertical + 0.28;  break;  
                case "13.5":    $chestVertical = $chestVertical + 0.55; break;
                case "14":      $chestVertical = $chestVertical + 0.82;  break;  
                case "14.5":    $chestVertical = $chestVertical + 1.10; break;
                case "15":      $chestVertical = $chestVertical + 1.37; break;
                case "15.5":    $chestVertical = $chestVertical + 1.63; break;
                case "16":      $chestVertical = $chestVertical + 1.9;  break;
                case "16.5":    $chestVertical = $chestVertical + 2.17; break; 
                case "17":      $chestVertical = $chestVertical + 2.44;  break;
                case "17.5":    $chestVertical = $chestVertical + 2.7;   break;
                case "18":      $chestVertical = $chestVertical + 2.97; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '29.5') {
            $fshoulder = 4.5 * 2;
            $chestVertical = 5;
         
            switch ($armhole) {
                case "13":      $chestVertical = $chestVertical + 0.06;  break;  
                case "13.5":    $chestVertical = $chestVertical + 0.35; break;
                case "14":      $chestVertical = $chestVertical + 0.6;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.88; break;
                case "15":      $chestVertical = $chestVertical + 1.15; break;
                case "15.5":    $chestVertical = $chestVertical + 1.43; break;
                case "16":      $chestVertical = $chestVertical + 1.8;  break;
                case "16.5":    $chestVertical = $chestVertical + 2.07; break; 
                case "17":      $chestVertical = $chestVertical + 2.34;  break;
                case "17.5":    $chestVertical = $chestVertical + 2.61;   break;
                case "18":      $chestVertical = $chestVertical + 2.87; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '30') {
            $fshoulder = 4.5 * 2;
            $chestVertical = 5;
            
              switch ($armhole) {
                case "13":      $chestVertical = $chestVertical + 0.06;  break;  
                case "13.5":    $chestVertical = $chestVertical + 0.35; break;
                case "14":      $chestVertical = $chestVertical + 0.6;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.88; break;
                case "15":      $chestVertical = $chestVertical + 1.15; break;
                case "15.5":    $chestVertical = $chestVertical + 1.43; break;
                case "16":      $chestVertical = $chestVertical + 1.75;  break;
                case "16.5":    $chestVertical = $chestVertical + 1.97; break; 
                case "17":      $chestVertical = $chestVertical + 2.25;  break;
                case "17.5":    $chestVertical = $chestVertical + 2.51;   break;
                case "18":      $chestVertical = $chestVertical + 2.77; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '31') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
            
              switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.35; break;
                case "15":      $chestVertical = $chestVertical + 0.65; break;
                case "15.5":    $chestVertical = $chestVertical + 0.90; break;
                case "16":      $chestVertical = $chestVertical + 1.2;  break;
                case "16.5":    $chestVertical = $chestVertical + 1.45; break; 
                case "17":      $chestVertical = $chestVertical + 1.7;  break;
                case "17.5":    $chestVertical = $chestVertical + 2;    break;
                case "18":      $chestVertical = $chestVertical + 2.25; break;
                default:        echo " ";
                }

            
        } elseif ($chest == '31.5') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
            
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.35; break;
                case "15":      $chestVertical = $chestVertical + 0.65; break;
                case "15.5":    $chestVertical = $chestVertical + 0.90; break;
                case "16":      $chestVertical = $chestVertical + 1.2;  break;
                case "16.5":    $chestVertical = $chestVertical + 1.45; break; 
                case "17":      $chestVertical = $chestVertical + 1.7;  break;
                case "17.5":    $chestVertical = $chestVertical + 2;    break;
                case "18":      $chestVertical = $chestVertical + 2.25; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '32') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
            
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.35; break;
                case "15":      $chestVertical = $chestVertical + 0.65; break;
                case "15.5":    $chestVertical = $chestVertical + 0.90; break;
                case "16":      $chestVertical = $chestVertical + 1.2;  break;
                case "16.5":    $chestVertical = $chestVertical + 1.45; break; 
                case "17":      $chestVertical = $chestVertical + 1.7;  break;
                case "17.5":    $chestVertical = $chestVertical + 2;    break;
                case "18":      $chestVertical = $chestVertical + 2.25; break;        
                default:        echo " ";
                }
            
        } elseif ($chest == '32.5') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
            
          switch ($armhole) {

                case "14":      $chestVertical = $chestVertical + 0.1;  break;  
                case "14.5":    $chestVertical = $chestVertical + 0.35; break;
                case "15":      $chestVertical = $chestVertical + 0.65; break;
                case "15.5":    $chestVertical = $chestVertical + 0.90; break;
                case "16":      $chestVertical = $chestVertical + 1.2;  break;
                case "16.5":    $chestVertical = $chestVertical + 1.45; break; 
                case "17":      $chestVertical = $chestVertical + 1.7;  break;
                case "17.5":    $chestVertical = $chestVertical + 2.0;  break;
                case "18":      $chestVertical = $chestVertical + 2.25; break;
                default:        echo " ";
                }
            
        } elseif ($chest == '33') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.24;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.05;     break;
                case "15":      $chestVertical = $chestVertical + 0.25;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.6;      break;
                case "16":      $chestVertical = $chestVertical + 0.88;     break;
                case "16.5":    $chestVertical = $chestVertical + 1.2;      break;
                case "17":      $chestVertical = $chestVertical + 1.44;     break;
                case "17.5":    $chestVertical = $chestVertical + 1.75;     break;
                default:        echo " ";
            }
           
        } elseif ($chest == '33.5') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
           
          switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.5;  break;
                case "14.5":    $chestVertical = $chestVertical - 0.25; break;
                case "15":      $chestVertical = $chestVertical + 0.10; break;
                case "15.5":    $chestVertical = $chestVertical + 0.40; break;
                case "16":      $chestVertical = $chestVertical + 0.65; break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;  break; 
                case "17":      $chestVertical = $chestVertical + 1.2;  break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;  break;
                case "18":      $chestVertical = $chestVertical + 1.75; break;         
                default:        echo " ";
                }
            
        } elseif ($chest == '34') {
            $fshoulder = 5 * 2;
            $chestVertical = 5.5;
           
          switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 0.5;  break;
                case "14.5":    $chestVertical = $chestVertical - 0.25; break;
                case "15":      $chestVertical = $chestVertical + 0.10; break;
                case "15.5":    $chestVertical = $chestVertical + 0.40; break;
                case "16":      $chestVertical = $chestVertical + 0.65; break;
                case "16.5":    $chestVertical = $chestVertical + 0.9;  break; 
                case "17":      $chestVertical = $chestVertical + 1.2;  break;
                case "17.5":    $chestVertical = $chestVertical + 1.5;  break;
                case "18":      $chestVertical = $chestVertical + 1.75; break;         
                default:        echo " ";
                }
            
        } elseif ($chest == '34.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.55;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "15":      $chestVertical = $chestVertical - 0.15;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.40;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                    case "17":      $chestVertical = $chestVertical + 0.9;      break;
                    case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "18":      $chestVertical = $chestVertical + 1.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '35') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 0.55;     break;
                    case "14.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "15":      $chestVertical = $chestVertical - 0.15;     break;
                    case "15.5":    $chestVertical = $chestVertical + 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.40;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.65;     break;
                    case "17":      $chestVertical = $chestVertical + 0.9;      break;
                    case "17.5":    $chestVertical = $chestVertical + 1.25;     break;
                    case "18":      $chestVertical = $chestVertical + 1.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 1.8;      break;        
                    default:        echo " ";
                } 
            
        } elseif ($chest == '35.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
                 switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical + 0.2;      break;
                    case "14.5":    $chestVertical = $chestVertical - 0.65;     break;
                    case "15":      $chestVertical = $chestVertical - 0.4;      break;
                    case "15.5":    $chestVertical = $chestVertical - 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.15;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.45;     break;
                    case "17":      $chestVertical = $chestVertical + 0.75;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.0;      break;                         
                    case "18":      $chestVertical = $chestVertical + 1.25;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.65;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;         
                    default:        echo " ";
                }
            
        } elseif ($chest == '36') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
                 switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical + 0.2;      break;
                    case "14.5":    $chestVertical = $chestVertical - 0.65;     break;
                    case "15":      $chestVertical = $chestVertical - 0.4;      break;
                    case "15.5":    $chestVertical = $chestVertical - 0.1;      break;
                    case "16":      $chestVertical = $chestVertical + 0.15;     break;
                    case "16.5":    $chestVertical = $chestVertical + 0.45;     break;
                    case "17":      $chestVertical = $chestVertical + 0.75;     break;
                    case "17.5":    $chestVertical = $chestVertical + 1.0;      break;                         
                    case "18":      $chestVertical = $chestVertical + 1.25;     break;
                    case "18.5":    $chestVertical = $chestVertical + 1.65;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1.85;     break;         
                    default:        echo " ";
                }
            
        } elseif ($chest == '36.5') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;        
                default:        echo " ";
            }
            
        } elseif ($chest == '37') {
            $fshoulder = 5.25 * 2;
            $chestVertical = 5.75;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.2;      break;
                case "14.5":    $chestVertical = $chestVertical - 0.95;     break;
                case "15":      $chestVertical = $chestVertical - 0.6;      break;
                case "15.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "16":      $chestVertical = $chestVertical;            break;
                case "16.5":    $chestVertical = $chestVertical + 0.25;     break;
                case "17":      $chestVertical = $chestVertical + 0.5;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.75;     break;
                case "18":      $chestVertical = $chestVertical + 1.05;     break;
                case "18.5":    $chestVertical = $chestVertical + 1.35;     break;
                default:        echo " ";
            }
            
        } elseif ($chest == '37.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;  // Creative
                
                switch ($armhole) {
                    case "15":      $chestVertical = $chestVertical - 1.0;      break;
                    case "15.5":    $chestVertical = $chestVertical - 0.75;     break;
                    case "16":      $chestVertical = $chestVertical - 0.5;      break;
                    case "16.5":    $chestVertical = $chestVertical - 0.25;     break;
                    case "17":      $chestVertical = $chestVertical + 0;        break;
                    case "17.5":    $chestVertical = $chestVertical + 0.25;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 0.90;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1;        break;
                    case "19.5":    $chestVertical = $chestVertical + 1.25;     break;
                    default:        echo " ";
                }
        
        } elseif ($chest == '38') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;  // Creative
                
                switch ($armhole) {
                    case "15":      $chestVertical = $chestVertical - 1.0;      break;
                    case "15.5":    $chestVertical = $chestVertical - 0.75;     break;
                    case "16":      $chestVertical = $chestVertical - 0.5;      break;
                    case "16.5":    $chestVertical = $chestVertical - 0.25;     break;
                    case "17":      $chestVertical = $chestVertical + 0;        break;
                    case "17.5":    $chestVertical = $chestVertical + 0.25;     break;                         
                    case "18":      $chestVertical = $chestVertical + 0.5;      break;
                    case "18.5":    $chestVertical = $chestVertical + 0.90;     break;                             
                    case "19":      $chestVertical = $chestVertical + 1;        break;
                    case "19.5":    $chestVertical = $chestVertical + 1.25;     break;                        
                    default:        echo " ";
                } 
        
        } elseif ($chest == '38.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;        //Creative
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.9;     break;
                case "14.5":    $chestVertical = $chestVertical - 1.6;      break;
                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;
                default:        echo " ";
            }            
            
        } elseif ($chest == '39') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;        //Creative
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical - 1.9;     break;
                case "14.5":    $chestVertical = $chestVertical - 1.6;      break;
                case "15":      $chestVertical = $chestVertical - 1.35;     break;
                case "15.5":    $chestVertical = $chestVertical - 1.05;     break;
                case "16":      $chestVertical = $chestVertical - 0.75;     break;
                case "16.5":    $chestVertical = $chestVertical - 0.35;     break;
                case "17":      $chestVertical = $chestVertical - 0.1;      break;
                case "17.5":    $chestVertical = $chestVertical + 0.15;     break;
                case "18":      $chestVertical = $chestVertical + 0.30;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.7;      break;
                case "19":      $chestVertical = $chestVertical + 1.0;      break;
                case "19.5":    $chestVertical = $chestVertical + 1.30;     break;
                default:        echo " ";
            }            
            
        } elseif ($chest == '39.5') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;  //Creative
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 2.25;     break;
                    case "14.5":    $chestVertical = $chestVertical - 2.0;      break;
                    case "15":      $chestVertical = $chestVertical - 1.65;     break;
                    case "15.5":    $chestVertical = $chestVertical - 1.35;     break;
                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break;                         
                    default:        echo " ";
                }
            
        } elseif ($chest == '40') {
            $fshoulder = 5.5 * 2;
            $chestVertical = 6;  //Creative
            
                switch ($armhole) {
                    case "14":      $chestVertical = $chestVertical - 2.25;     break;
                    case "14.5":    $chestVertical = $chestVertical - 2.0;      break;
                    case "15":      $chestVertical = $chestVertical - 1.65;     break;
                    case "15.5":    $chestVertical = $chestVertical - 1.35;     break;
                    case "16":      $chestVertical = $chestVertical - 1.05;     break;
                    case "16.5":    $chestVertical = $chestVertical - 0.7;      break;
                    case "17":      $chestVertical = $chestVertical - 0.45;     break;
                    case "17.5":    $chestVertical = $chestVertical - 0.12;     break;
                    case "18":      $chestVertical = $chestVertical + 0.14;     break;
                    case "18.5":    $chestVertical = $chestVertical + 0.4;      break;
                    case "19":      $chestVertical = $chestVertical + 0.7;      break;
                    case "19.5":    $chestVertical = $chestVertical + 1.05;     break;
                    case "20":      $chestVertical = $chestVertical + 1.35;     break; 
                    default:        echo " ";
                }

        } elseif ($chest == '41') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;      //Creative
            //$chestVertical = ((($armhole / 2) - 1) - 2.25); // Venkata's Calculation
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;
                default:        echo " ";
            }
            
        } elseif ($chest >= '42') {
            $fshoulder = 5.75 * 2;
            $chestVertical = 6.25;
            
            switch ($armhole) {
                case "14":      $chestVertical = $chestVertical + 0.27;     break;
                case "14.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "15":      $chestVertical = $chestVertical + 0.32;     break;
                case "15.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "16":      $chestVertical = $chestVertical + 0.5;      break;
                case "16.5":    $chestVertical = $chestVertical + 0.5;      break;
                case "17":      $chestVertical = $chestVertical + 0.27;     break;
                case "17.5":    $chestVertical = $chestVertical + 0.27;     break;
                case "18":      $chestVertical = $chestVertical + 0.27;     break;
                case "18.5":    $chestVertical = $chestVertical + 0.27;     break;

                default:        echo " ";
            }
            
        } else {
            $fshoulder= $fshoulder;
        }
        
/* end of first Switch Statement , don't delete */                
        break;
    
    default:        echo " ";
}
    $fshoulder1 = $fshoulder * $cIn;
    $chestVertical = $chestVertical * $cIn;
?>