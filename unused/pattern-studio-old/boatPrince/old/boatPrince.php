<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Prince Blouse Pattern Design - Cutting Master</title>
	
	<meta name="Description" content="Princess-Cut Boat Neck Blouse Pattern Design - Design your blouse and generate the pattern for any size, print on A3 and cut and stick your cloth">
	<meta name="Keywords" content="Prince Boat Neck Blouse Design Pattern, prince blouse tutorials, saree blouse cutting pattern">
		
	<meta charset="utf-8">     
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">  	
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	
  	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 	<link rel="stylesheet" href="../css/style.css">	
    <link rel="stylesheet" href="../css/menustyle.css">	

</head>

<body>
	<?php include '../inc/header2.php'; ?>
	<?php include '../boatPrince/boatPrinceValidate.php'; ?>
	
<div><h1 style="font-weight:700; margin-left: 20px;">Blouse Design (Princess Boat Neck)</h1></div>
<div class="row shadows" style="margin-top: -8px">

	<div class="col-md-5" style="background-color: #F8F9F9; border-right: 5px solid #D6DBDF"><?php include '../boatPrince/boatPrinceMeasure.php';?></div>

<!--  Right Column -->			
	<div class="col-md-7 grid" style="bottom: 1px solid gray;">

<!-- Tabs Start -->
 <div>
     <div id="diagram">
          <h2>Diagram</h2>
          <p>The below diagram will help you understand the measurement inputs on the left. </p>
        <span style="margin: auto;">
            <img src="../img/blouse_diagram.PNG" alt="blouse diagram">
        </span>
	</div>
	
<!-- design download files -->
     <div id="design">
           <h2>Design Pattern files (for Download)</h2>
          <p>The below pattern design will help you cut the cloth. This is for reference only, please measure twice you cut the cloth. </p>

      <?php include '../boatPrince/princessBoatNeck.php';?>
  </div>
</div>

<!-- Tabs End  -->

    </div> 
</div>

<script>
        var cust = document.getElementById("cust").value;
        var shoulder = document.getElementById("shoulder").value;
        var fshoulder = document.getElementById("fshoulder").value;
        var bndepth = document.getElementById("bnDepth").value;
        var blength = document.getElementById("blength").value; 
        var waist = document.getElementById("waist").value;
        var chest = document.getElementById("chest").value;
  /*      var bust = document.getElementById("bust").value; */
        var flength = document.getElementById("flength").value;
        var fndepth = document.getElementById("fndepth").value;
        var apex = document.getElementById("apex").value;
        var slength = document.getElementById("slength").value;
        var saround = document.getElementById("saround").value;
        var sopen = document.getElementById("sopen").value;
        var armhole = document.getElementById("armhole").value;

(function() {        
    
    if (cust=="" || shoulder=="" || fshoulder=="" || bndepth=="" || waist=="" || chest=="" || flength=="" || fndepth==""  || apex=="" || slength=="" || saround=="" || armhole=="" || sopen==""){
            document.getElementById("design").style.display = "none";
            document.getElementById("diagram").style.display = "inline";
       }else{
           document.getElementById("design").style.display = "inline";
           document.getElementById("diagram").style.display = "none";    
       }        
    })();

<!--
    XS - 34 - 36
    S - 36 - 38
    M - 38 - 40
    L - 40 - 41
-->
    
function diff() {
    var lenDif = blength - flength;
/*        
   if ((chest >= 32) && (chest <= 36)) {
        if(lenDif<"1"){
            alert("Front Length is less than required, increase the size");
        }
    } else if ((chest >= 36) && (chest <= 38)) {
        if(lenDif<"1"){
            alert("Front Length is less than required, increase the size");
        }
    } else {
         if(lenDif<"1"){
            alert("Front Length is less than required, increase the size");
        }
*/    }
    
</script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-113897701-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-113897701-1');
</script>

</body>
</html>