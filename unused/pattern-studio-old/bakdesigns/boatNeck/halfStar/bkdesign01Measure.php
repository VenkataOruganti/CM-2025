<form class="form-horizontal" method="post" name="blouse" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 

<!--  Right Column -->			
				<div>
					<h2>Measurement</h2>
					
					<div class="form-group">
						<span style="text-align: center; padding-left: 50px; "><span class="red">*</span>Please input all values in <span class="red">"inches" </span>Only.<br><br></span>

	      				<label class="control-label col-sm-5" for="cust">Download File Name <span class="red">*</span></label>
	    				<div class="col-sm-6">
	        				<input type="text" class="form-control" id="cust" placeholder="File / Customer Name" name="cust" value="<?php echo $cust;?>">
	      				</div>   
	      				<div class="red"><?php echo $custErr;?></div>
	      			</div>

                
					<div class="form-group">
	      				<label class="control-label col-sm-5" for="blength">Blouse Back Length(1) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="blength" name="blength" step="0.5" min="10" max="18" value="<?php echo $blength;?>">
	      				</div>
	      				<div class="red"><?php echo $blengthErr;?></div>
	  				</div>

                   
                    <div class="form-group">
	      				<label class="control-label col-sm-5" for="fshoulder">Full Shoulder(2) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="fshoulder" name="fshoulder" step="0.5" min="12" max="17"value="<?php echo $fshoulder;?>">
	      				</div>
	      				<div class="red"><?php echo $fshoulderErr;?></div>
	  				</div>
                   
                	<div class="form-group">
	      				<label class="control-label col-sm-5" for="shoulder">Shoulder Strap(3) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="shoulder" name="shoulder" step="0.5" min="1" max="5" value="<?php echo $shoulder;?>">
	      				</div>
	      				<div class="red"><?php echo $shoulderErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-5" for="bnDepth">Back Neck Depth(4) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="bnDepth" name="bnDepth" step="1" value="<?php echo $bnDepth;?>">
	      				</div>
	      				<div class="red"><?php echo $bnDepthErr;?></div>
	  				</div>
	  				
	  				<div class="col-md-12"><hr></div>

<!-- Back Part End -->

					<div class="form-group">
	      				<label class="control-label col-sm-5" for="apex">Shoulder to Apex(6) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="apex" name="apex" step="0.5" value="<?php echo $apex;?>">
	      				</div>
	      				<div class="red"><?php echo $apexErr;?></div>
	  				</div>

<!-- Upper Chest Round -->                
					<div class="form-group">
	      				<label class="control-label col-sm-5" for="chest">Chest Round(8) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="chest" name="chest" step="1" min="28" max="44" value="<?php echo $chest;?>">
	      				</div>
	      				<div class="red"><?php echo $chestErr;?></div>
	  				</div>

<!-- Bust Around -->
                
					<div class="form-group">
	      				<label class="control-label col-sm-5" for="waist">Waist Round (9) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="waist" name="waist" step="0.5" min="28" max="44" value="<?php echo $waist;?>">
	      				</div>
	      				<div class="red"><?php echo $waistErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-5" for="armhole">Armhole(13) <span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="armhole" name="armhole" step="0.5" min="14" max="20" value="<?php echo $armhole;?>">
	      				</div>
	      				<div class="red"><?php echo $armholeErr;?></div>
	  				</div>
                   
                    <div class="form-group">
	      				<label class="control-label col-sm-5" for="angleDepth">Angle Height<span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="angleDepth" name="angleDepth" step="10" min="0" max="90" value="<?php echo $angleDepth;?>">
	      				</div>
	      				<div class="red"><?php echo $angleDepthErr;?></div>
	  				</div>
                                   
                    <div class="form-group">
	      				<label class="control-label col-sm-5" for="angleWidth">Angle Wdth<span class="red">*</span></label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="angleWidth" name="angleWidth" step="10" min="0" max="90" value="<?php echo $angleWidth;?>">
	      				</div>
	      				<div class="red"><?php echo $angleWidthErr;?></div>
	  				</div>      

			      	<div class="form-group"> 
	      		  		<div class="col-sm-offset-4 col-sm-10">
		        			<button type="submit" class="btn btn-default btn-primary" onclick="diff()">Generate Blouse Design</button>
		      			</div>
	    			</div>
    			</div>
    </form>