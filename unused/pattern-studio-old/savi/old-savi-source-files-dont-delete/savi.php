<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Saree Blouse Pattern Designing - Cutting Master</title>
	<meta name="Description" content="Katori Blouse Pattern Design - Design your blouse and generate the pattern for any size, print on A3 and cut and stick your cloth">
	<meta name="Keywords" content="Blouse Design Pattern, Saree blouse pattern, Saree blouse tutorials, saree blouse cutting pattern">	

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
	<?php include '../inc/header2.php';?>
    <div><h1 style="font-weight:700; margin-left: 20px;">Saree Blouse</h1></div>
    <div class="row shadows" style="margin-top: -8px">
	<div class="col-md-5" style="background-color: #F8F9F9; border-right: 5px solid #D6DBDF">
        <?php include '../savi/saviMeasure.php'; ?></div>
    <!--  Right Column -->			
	<div class="col-md-7 grid" style="bottom: 1px gray solid;">
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
        <?php
        // Include the complete pattern file which contains all 4 patterns
        // and sets all session variables for download files
        include __DIR__ . '/saviComplete.php';
        ?>
  </div>

</div>



<!-- Tabs End -->



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

        var bust = document.getElementById("bust").value; 

        var flength = document.getElementById("flength").value;

        var fndepth = document.getElementById("fndepth").value;

        var apex = document.getElementById("apex").value;

        var slength = document.getElementById("slength").value;

        var saround = document.getElementById("saround").value;

        var sopen = document.getElementById("sopen").value;

        var armhole = document.getElementById("armhole").value;



(function() {        

    

    if (cust=="" || shoulder=="" || fshoulder=="" || bndepth=="" || waist=="" || bust=="" || chest=="" || flength=="" || fndepth==""  || apex=="" || slength=="" || saround=="" || armhole=="" || sopen==""){

            document.getElementById("design").style.display = "none";

            document.getElementById("diagram").style.display = "inline";

       }else{

           document.getElementById("design").style.display = "inline";

           document.getElementById("diagram").style.display = "none";    

       } 

    

    diff();

    })();



function diff() {

    var lenDif = blength - flength;

        

    if(blength !=0 && lenDif < 1.5){

        alert("Front Length is too small, please increase back length to get the good front setting."); 

    }else {

           // do nothing;    

    }

}    

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