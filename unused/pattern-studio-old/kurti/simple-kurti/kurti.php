<?php
session_start();
error_reporting (E_ALL ^ E_NOTICE);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Kurti Pattern Designing - Cutting Master</title>
	
	<meta name="Description" content="Katori Blouse Pattern Design - Design your blouse and generate the pattern for any size, print on A3 and cut and stick your cloth">
	
	<meta name="Keywords" content="Blouse Design Pattern, Saree blouse pattern, Saree blouse tutorials, saree blouse cutting pattern">	
		
	<meta charset="utf-8">     
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">  	
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 	<link rel="stylesheet" href="../../css/style.css">	
    <link rel="stylesheet" href="../../css/menustyle.css">	

</head>

<body>
	<?php include '../../inc/header2.php'; ?>
	<?php include 'kurtiValidate.php';?>
	
    <div><h1 style="font-weight:700; margin-left: 20px;">Kurti</h1></div>
    <div class="row shadows" style="margin-top: -8px">
    
	<div class="col-md-5" style="background-color: #F8F9F9; border-right: 5px solid #D6DBDF"><?php include 'kurtiMeasure.php'; ?></div>

    <!--  Right Column -->			
	<div class="col-md-7 grid" style="bottom: 1px gray solid;">

    <!-- Tabs Start -->
 <div>
     <div id="diagram">
          <h2>Kurti Diagram</h2>
          <p>The below diagram will help you understand the measurement inputs on the left. </p>
        <span style="margin: auto;">
            <img src="../../img/kurti.png" alt="kurti diagram">
        </span>
	</div>
	
<!-- design download files -->     
     <div id="design">
           <h2>Kurti Design Pattern</h2> 
          <p>The below pattern design will help you cut the cloth. This is for reference only, please measure twice you cut the cloth. </p><br>
          
      <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#home">Front</a></li>
        <li><a data-toggle="tab" href="#menu1">Back</a></li>
        <li><a data-toggle="tab" href="#menu2">Sleeve</a></li>
<!--        <li><a data-toggle="tab" href="#menu3">WaistBand</a></li>
        <li><a data-toggle="tab" href="#menu4">01</a></li>
        <li><a data-toggle="tab" href="#menu5">02</a></li> -->
      </ul>

       <div class="tab-content">
       
        <div id="home" class="tab-pane fade in active">
          <?php include 'kurtiFront.php';?>
        </div>
        
         <div id="menu1" class="tab-pane fade">
          <?php include 'kurtiBack.php';?>            
        </div>      
            
        <div id="menu2" class="tab-pane fade">
          <?php include 'sleeve.php';?>
        </div>                     
<!--                
        <div id="menu3" class="tab-pane fade">
           <?php include '../savi/saviPatti.php';?>
        </div>        
-->        
      </div>
  </div>
</div>

<!-- Tabs End -->

    </div> 
</div>

<script>
        var cust = document.getElementById("cust").value;
        var fshoulder = document.getElementById("fshoulder").value;
        var shoulder = document.getElementById("shoulder").value;
        var fndepth = document.getElementById("fnDepth").value;
        var bndepth = document.getElementById("bnDepth").value;
    
        var apex = document.getElementById("apex").value;
        var chest = document.getElementById("chest").value;
        var bust = document.getElementById("bust").value; 
        var waist = document.getElementById("waist").value;
        var hip = document.getElementById("hip").value;
        var bottom = document.getElementById("bottom").value;
    
        var flength = document.getElementById("flength").value;     
        var wlength = document.getElementById("wlength").value; 
        var hlength = document.getElementById("hlength").value;         
               
        var slength = document.getElementById("slength").value;
        var saround = document.getElementById("saround").value;
        var sopen = document.getElementById("sopen").value;
        var armhole = document.getElementById("armhole").value;

(function() {        
    
    if (cust=="" || fshoulder=="" || shoulder=="" || fndepth==""  || bndepth=="" || apex=="" || chest=="" || bust=="" || waist=="" || hip=="" || flength=="" || wlength=="" || wlength=="" || hlength=="" || saround=="" || armhole=="" || sopen==""){
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
        
    if(lenDif == '0'){
        
    }else if(lenDif <= '1.5'){
        alert("Front Length is too small, please increase back length to get the good front setting.");
    } else {
        
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