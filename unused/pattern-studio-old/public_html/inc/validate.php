	<?php

		$displayRight = "none";
		$cIn = 25.4;

// variables
		$shoulder = $fshoulder = $cust = $order = $fndepth = $chest = $waist = $apex = $blength = $slength = $saround = $armhole = $bnDepth = "";

// variable errors
		$shoulderErr = $fshoulderErr = $custErr = $orderErr = $fndepthErr = $chestErr = $waistErr = $apexErr = $blengthErr = $slengthErr = $saroundErr = $armholeErr = $bnDepthErr = ""; 

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
// Customer / filename
            
			  if (empty($_POST["cust"])) {
			    $custErr = "Name required";
			  } else {
			    $cust = test_input($_POST["cust"]);                
                $_SESSION["cust"] = $cust;
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

// Full Shoulder
			  if (empty($_POST["fshoulder"])) {
			    $fshoulderErr = "Required";
			  } else {
			    $fshoulder = test_input($_POST["fshoulder"]);
                $fshoulder1 = $fshoulder * $cIn;
                  
                $_SESSION["fshoulder"] = $fshoulder;
                $_SESSION["fshoulder1"] = $fshoulder1;
              }

// Front Length
            
			  if (empty($_POST["flength"])) {
			    $flengthErr = "Required";
			  } else {
			    $flength = test_input($_POST["flength"]);
                $flength1 = $flength * $cIn;
                  
                  $_SESSION["flength"] = $flength;
                  $_SESSION["flength1"] = $flength1;
			  }
            
// Front Neck Depth
			  if (empty($_POST["fndepth"])) {
			    $fndepthErr = "Required";
			  } else {
			    $fndepth = test_input($_POST["fndepth"]); 
                $fndepth1 = $fndepth * $cIn;
                  
                  $_SESSION["fndepth"] = $fndepth;
                  $_SESSION["fndepth1"] = $fndepth1;
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

// Waist            
			  if (empty($_POST["waist"])) {
			    $waistErr = "Required";
			  } else {
			    $waist = test_input($_POST["waist"]);
                $waist1 = $waist * $cIn;
                  
                  $_SESSION["waist"] = $waist;
                  $_SESSION["waist1"] = $waist1;
			  }

// apex            
			  if (empty($_POST["apex"])) {
			    $apexErr = "Required";
			  } else {
			    $apex = test_input($_POST["apex"]);
                $apex1 = $apex * $cIn;
                  
                $_SESSION["apex"] = $apex;
                $_SESSION["apex1"] = $apex1;
			  }

//Back Length            
			  if (empty($_POST["blength"])) {
			    $blengthErr = "Required";
			  } else {
			    $blength = test_input($_POST["blength"]);
                $blength1 = $blength * $cIn;
                  
                   $_SESSION["blength"] = $blength;
                   $_SESSION["blength1"] = $blength1;
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

// shoulder around            
			  if (empty($_POST["saround"])) {
			    $saroundErr = "Required";
			  } else {
			    $saround = test_input($_POST["saround"]);
                $saround1 = $saround * $cIn;
                  
                  $_SESSION["saround"] = $saround;
                  $_SESSION["saround1"] = $saround1;
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

// Back Neck Depth            
			  if (empty($_POST["bnDepth"])) {
			    $bnDepthErr = "Required";			    
			  } elseif ($_POST["bnDepth"] < 1) {
			   	$bnDepthErr = "Not a enough Number";		
			  } else {
			  	$bnDepth = test_input($_POST["bnDepth"]);
                $bnDepth1 = $bnDepth * $cIn;
                  
                  $_SESSION["bnDepth"] = $bnDepth;
                  $_SESSION["bnDepth1"] = $bnDepth1;
			  }
		}

		$_SESSION["measure"] = "Shoulder :". $shoulder. ", Full Shoulder :" . $fshoulder . ", BackNeck Depth :" . $bnDepth;
        $_SESSION["measure1"] = "Back Length :" . $blength .", Waist :" . $waist . ", Chest :" . $chest;
        $_SESSION["measure2"] = "Front Length :" . $flength . ", Front Neck Depth :" . $fndepth . ", Shoulder to Apex :" . $apex;
        $_SESSION["measure3"] = "Sleeve Length :" . $slength . ", Sleeve Around :" . $saround . ", Arm Hole :" . $armhole;


		function test_input($data) {
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;
		}

		if (($bnDepth && $shoulder && $fshoulder && $cust && $order && $fndepth && $chest && $waist && $apex && $blength && $slength && $saround && $armhole && $bnDepth) > 0) {
			//$displayRight = "block";			
			//echo "<script type='text/javascript'>alert('$displayRight');</script>";
		} else {
			//$displayRight = "none";
			//echo "<script type='text/javascript'>alert('$displayRight');</script>";
		}

		$_SESSION["bnDepth"] = $bnDepth;
		$_SESSION["shoulder"] = $shoulder;
        
	?>	