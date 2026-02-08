<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Blouse Pattern Design Application</title>
	
	<meta name="Description" content="Katori Blouse Pattern Design - Design your blouse and generate the pattern for any size, print on A3 and cut and stick your cloth">
	
	<meta name="Keywords" content="Blouse Design Pattern, Saree blouse pattern, Saree blouse tutorials, saree blouse cutting pattern">
	
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">  	
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  	<link rel="stylesheet" href="css/style.css">
  	<link rel="stylesheet" href="css/menustyle.css">
</head>

<body>
	<?php include 'inc/header2.php'; ?>
	<?php include 'inc/validate.php'; ?>
	
<div><h1 style="font-weight:700; margin-left: 20px;">Saree Blouse Design</h1></div>
<div class="row shadows" style="margin-top: -8px">
	<div class="col-md-5" style="background-color: #F8F9F9; border-right: 5px solid #D6DBDF"><?php include 'inc/measure.php'; ?></div>

<!--  Right Column -->			
	<div class="col-md-7 grid" style="bottom: 1px gray solid;">

<!-- Tabs Start -->
 <div>
     <div id="diagram">
          <h2>Diagram</h2>
          <p>The below diagram will help you understand the form on the left. Please refer the diagram. </p>
        <span style="margin: auto;">
            <img src="img/blouse_diagram.PNG" alt="blouse diagram">
        </span>
	</div>
	
<!-- design download files -->     
     <div id="design">
           <h2>Design Pattern files (for Download)</h2> 
          <p>The below pattern design will help you cut the cloth. This is for reference only, please measure twice you cut the cloth. </p>
          
      <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#home">Blouse Front</a></li>
        <li><a data-toggle="tab" href="#menu1">Blouse Back</a></li>
        <li><a data-toggle="tab" href="#menu2">Sleeve</a></li>
        <li><a data-toggle="tab" href="#menu3">Patti</a></li>
      </ul>

      <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
          <?php include 'inc/blouse-front.php';?>
        </div>
        <div id="menu1" class="tab-pane fade">
          <?php include 'inc/blouse-back.php';?>
        </div>
        <div id="menu2" class="tab-pane fade">
          <?php include 'inc/blouse-sleeve.php';?>
        </div>
        <div id="menu3" class="tab-pane fade">
          <?php include 'inc/blouse-patti.php';?>
        </div>
      </div>
  </div>
</div>

<!-- Tabs End  -->

    </div> 
</div>

<script>
    
    (function() {
        
        var cust = document.getElementById("cust").value;
        var shoulder = document.getElementById("shoulder").value;
        var fshoulder = document.getElementById("fshoulder").value;
        var bndepth = document.getElementById("bnDepth").value;
        var blength = document.getElementById("blength").value; 
        var waist = document.getElementById("waist").value;
        var chest = document.getElementById("chest").value;
        var flength = document.getElementById("flength").value;
        var fndepth = document.getElementById("fndepth").value;
        var apex = document.getElementById("apex").value;
        var slength = document.getElementById("slength").value;
        var saround = document.getElementById("saround").value;
        var armhole = document.getElementById("armhole").value;

        
    if (cust=="" || shoulder=="" || fshoulder=="" || bndepth=="" || waist=="" || chest=="" || flength=="" || fndepth==""  || apex=="" || slength=="" || saround=="" || armhole==""){
            document.getElementById("design").style.display = "none";
            document.getElementById("diagram").style.display = "inline";
       }else{
           document.getElementById("design").style.display = "inline";
           document.getElementById("diagram").style.display = "none";    
       }        
    })();
    
    function diff() {
        var lenDif = blength - flength;
        
        if(lenDif<"2.5"){
            alert("Front Length is too small, please increase back length to get the good front setting.");
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