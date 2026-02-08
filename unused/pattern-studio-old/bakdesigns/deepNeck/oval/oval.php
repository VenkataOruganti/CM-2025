<?php
    session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Saree Blouse Back Oval Designing - Cutting Master</title>
	<meta name="Description" content="Saree Blouse Back Designs">
	
	<meta name="Keywords" content="Blouse Back Design Pattern, Saree blouse Back pattern, Saree blouse Back Design tutorials, saree blouse Back cutting pattern">	
		
	<meta charset="utf-8">     
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">  	
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 	<link rel="stylesheet" href="../../../css/style.css">	
    <link rel="stylesheet" href="../../../css/menustyle.css">	

</head>

<body>
	<?php include '../../../inc/header2.php'; ?>
	<?php include 'ovalValidate.php';?>
	
<div><h1 style="font-weight:700; margin-left: 20px;">Deep Neck Blouse Back Designs(Oval Shape)</h1></div>

<div class="row shadows" style="margin-top: -8px">
   
    <div class="col-md-2" style="background-color: #fafafa; border-right: 5px solid #D6DBDF"> <?php include '../../../inc/bkdesign-menu.php';?> </div>
	<div class="col-md-4" style="background-color: #F8F9F9; border-right: 5px solid #D6DBDF"><?php include 'ovalMeasure.php'; ?></div>

<!--  Right Column -->			
	<div class="col-md-6 grid" style="bottom: 1px gray solid;">

<div>
     <div id="diagram">
          <h2>Diagram</h2>
          <p>The below diagram will help you understand the measurement inputs on the left. </p>
        <span style="margin: auto;">
            <img src="../../../img/ovalpattern.PNG" alt="blouse diagram">
        </span>
	</div>
	
<!-- design download files -->     
     <div id="design">
           <h2>Back Design Pattern files (for Download)</h2> 
          <p>The below pattern design will help you cut the cloth. This is for reference only, please measure twice, before you cut the cloth. </p>

      <div> <?php include 'oval01.php';?> </div>

  </div>
</div>

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
        var armhole = document.getElementById("armhole").value;
        var apex = document.getElementById("apex").value;

(function() {        
    
    if (cust=="" || shoulder=="" || fshoulder=="" || bndepth=="" || waist=="" || chest=="" || armhole==""){
            document.getElementById("design").style.display = "none";
            document.getElementById("diagram").style.display = "inline";
       }else{
           document.getElementById("design").style.display = "inline";
           document.getElementById("diagram").style.display = "none";    
       } 
    
    diff();
    })();

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