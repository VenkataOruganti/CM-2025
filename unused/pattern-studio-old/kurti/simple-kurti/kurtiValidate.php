<?php
session_start();
?>   
<?php
		$displayRight = "none";
		$cIn = 25.4;

// variables
		$cust = $fshoulder = $shoulder = $fndepth = $bnDepth = $apex = $chest = $bust = $waist = $hip = $flength = $wlength = $hlength = $slength = $saround = $sopen = $armhole = "";

// variable errors

		$custErr = $fshoulderErr = $shoulderErr = $fndepthErr = $bnDepthErr = $apexErr = $chestErr = $bustErr = $waistErr = $hipErr = $flengthErr = $wlengthErr = $hlengthErr = $slengthErr = $saroundErr = $sopenErr = $armholeErr = "";

//		$shoulderErr = $fshoulderErr = $custErr = $orderErr = $fndepthErr = $chestErr = $waistErr = $apexErr = $blengthErr = $slengthErr = $saroundErr = $armholeErr = $bnDepthErr = ""; 

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
// Customer / filename            
        if (empty($_POST["cust"])) {
            $custErr = "Name required";
        } else {
            $cust = test_input($_POST["cust"]);                
            $_SESSION["cust"] = $cust;
        }

// Full Shoulder
        if (empty($_POST["fshoulder"])) {
            $fshoulderErr = "Required";
        } else {                 
            $fshoulder = test_input($_POST["fshoulder"]);
            $_SESSION["fshoulder"] = $fshoulder;

            $fshoulder1 = $fshoulder * $cIn;                  
            $_SESSION["fshoulder1"] = $fshoulder1;
        }
            
// Shoulder / Strap    
        if (empty($_POST["shoulder"])) {
            $shoulderErr = "Required";
        } else {
            $shoulder = test_input($_POST["shoulder"]);
            $shoulder1 = $shoulder * $cIn;

            $_SESSION["shoulder"] = $shoulder;
            $_SESSION["shoulder1"] = $shoulder1;
        }

// Front Neck Depth
        if (empty($_POST["fnDepth"])) {
            $fnDepthErr = "Required";
        } else {
            $fnDepth = test_input($_POST["fnDepth"]); 
            $fnDepth1 = $fnDepth * $cIn;

            $_SESSION["fnDepth"] = $fnDepth;
            $_SESSION["fnDepth1"] = $fnDepth1;
        }
            
// Back Neck Depth
        if (empty($_POST["bnDepth"])) {
            $bnDepthErr = "Required";			    
        } else {
            $bnDepth = test_input($_POST["bnDepth"]);
            $bnDepth1 = $bnDepth * $cIn;
                  
            $_SESSION["bnDepth"] = $bnDepth;
            $_SESSION["bnDepth1"] = $bnDepth1;
        }
            
// Apex            
		if (empty($_POST["apex"])) {
		   $apexErr = "Required";
		} else {
		   $apex = test_input($_POST["apex"]);
           $apex1 = $apex * $cIn;
                  
           $_SESSION["apex"] = $apex;
           $_SESSION["apex1"] = $apex1;
        }            
            
// Chest
        if (empty($_POST["chest"])) {
            $chestErr = "Required";
        } else {
            $chest = test_input($_POST["chest"]);
            $chest1 = $chest * $cIn;

            $_SESSION["chest"] = $chest;
            $_SESSION["chest1"] = $chest1;
        }
            
// Bust
        if (empty($_POST["bust"])) {
            $bustErr = "Required";
        } else {
            $bust = test_input($_POST["bust"]);
            $bust1 = $bust * $cIn;

            $_SESSION["bust"] = $bust;
            $_SESSION["bust1"] = $bust1;
        }

// Waist            
        if (empty($_POST["waist"])) {
            $waistErr = "Required";
        } else {
            $waist = test_input($_POST["waist"]);
            $waist1 = $waist * $cIn;

            $_SESSION["waist"] = $waist;
            $_SESSION["waist1"] = $waist1;
        }

// Hip            
        if (empty($_POST["hip"])) {
            $hipErr = "Required";
        } else {
            $hip = test_input($_POST["hip"]);
            $hip1 = $hip * $cIn;

            $_SESSION["hip"] = $hip;
            $_SESSION["hip1"] = $hip1;
        }

// Bottom Round            
        if (empty($_POST["bottom"])) {
            $bottomErr = "Required";
        } else {
            $bottom = test_input($_POST["bottom"]);
            $bottom1 = $bottom * $cIn;

            $_SESSION["bottom"] = $bottom;
            $_SESSION["bottom1"] = $bottom1;
        }
            
// Full Length
        if (empty($_POST["flength"])) {
            $flengthErr = "Required";
        } else {
            $flength = test_input($_POST["flength"]);
            $flength1 = $flength * $cIn;

            $_SESSION["flength"] = $flength;
            $_SESSION["flength1"] = $flength1;
        }

//Waist Length            
        if (empty($_POST["wlength"])) {
            $wlengthErr = "Required";
        } else {
            $wlength = test_input($_POST["wlength"]);
            $wlength1 = $wlength * $cIn;
                  
            $_SESSION["wlength"] = $wlength;
            $_SESSION["wlength1"] = $wlength1;
        }
            
//Hip Length            
        if (empty($_POST["hlength"])) {
            $hlengthErr = "Required";
        } else {
            $hlength = test_input($_POST["hlength"]);
            $hlength1 = $hlength * $cIn;
                  
            $_SESSION["hlength"] = $hlength;
            $_SESSION["hlength1"] = $hlength1;
        }
            
// Sleeve Length    
        if (empty($_POST["slength"])) {
            $slengthErr = "Required";
        } else {
            $slength = test_input($_POST["slength"]);
            $slength1 = $slength * $cIn;
                  
            $_SESSION["slength"] = $slength;
            $_SESSION["slength1"] = $slength1;
        }

// Sleeve Round            
        if (empty($_POST["saround"])) {
            $saroundErr = "Required";
        } else {
            $saround = test_input($_POST["saround"]);
            $saround1 = $saround * $cIn;
                  
            $_SESSION["saround"] = $saround;
            $_SESSION["saround1"] = $saround1;
        }

// Sleeve Open
        if (empty($_POST["sopen"])) {
            $sopenErr = "Required";
        } else {
            $sopen = test_input($_POST["sopen"]);
            $sopen1 = $sopen * $cIn;
                  
            $_SESSION["sopen"] = $sopen;
            $_SESSION["sopen1"] = $sopen1;
        }


// armhole round
        if (empty($_POST["armhole"])) {
            $armholeErr = "Required";
        } else {
            $armhole = test_input($_POST["armhole"]);
            $armhole1 = $armhole * $cIn;
                  
            $_SESSION["armhole"] = $armhole;
            $_SESSION["armhole1"] = $armhole1;
        }
}

    $_SESSION["measure"] = "Shoulder :". $shoulder. ", Full Shoulder :" . $fshoulder . ", BackNeck Depth :" . $bnDepth;

    $_SESSION["measure1"] = "Back Length :" . $blength .", Waist :" . $waist . ", Chest :" . $chest;

    $_SESSION["measure2"] = "Front Length :" . $flength . ", Front Neck Depth :" . $fndepth . ", Shoulder to Apex :" . $apex;

    $_SESSION["measure3"] = "Sleeve Length :" . $slength . ", Sleeve Open :" . $sopen . ", Sleeve Round :" . $saround . ", Arm Hole :" . $armhole;

    function test_input($data) {
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;
    }

		$_SESSION["bnDepth"] = $bnDepth;
		$_SESSION["shoulder"] = $shoulder;
        
?>	