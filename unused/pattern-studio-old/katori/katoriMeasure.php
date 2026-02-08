<form class="form-horizontal" method="post" name="blouse" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 

<!--  Right Column -->			

				<div>
					<h2>Measurement</h2>
					<div class="form-group">
						<span style="text-align: center; padding-left: 50px; "><span class="red">*</span>Please input all values in <span class="red">"inches" </span>Only.<br><br></span>

	      				<label class="control-label col-sm-4" for="cust">Download File Name <span class="red">*</span> :</label>
	    				<div class="col-sm-6">
	        				<input type="text" class="form-control" id="cust" placeholder="File / Customer Name" name="cust" value="<?php echo $cust;?>">
	      				</div>   
	      				<div class="red"><?php echo $custErr;?></div>
	      			</div>

                	<div class="form-group">
	      				<label class="control-label col-sm-4" for="shoulder">Shoulder <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="shoulder" name="shoulder" step="0.01" value="<?php echo $shoulder;?>">
	      				</div>
	      				<div class="red"><?php echo $shoulderErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="fshoulder">Full Shoulder <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="fshoulder" name="fshoulder" step="0.01" value="<?php echo $fshoulder;?>">
	      				</div>
	      				<div class="red"><?php echo $fshoulderErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="bnDepth">Back Neck Depth <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="bnDepth" name="bnDepth" step="0.01" value="<?php echo $bnDepth;?>">
	      				</div>
	      				<div class="red"><?php echo $bnDepthErr;?></div>
	  				</div>

<!-- Back / Full Length -->
                
					<div class="form-group">
	      				<label class="control-label col-sm-4" for="blength">Blouse Back Length <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="blength" name="blength" step="0.01" value="<?php echo $blength;?>">
	      				</div>
	      				<div class="red"><?php echo $blengthErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="waist">Waist <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="waist" name="waist" step="0.01" value="<?php echo $waist;?>">
	      				</div>
	      				<div class="red"><?php echo $waistErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="chest">Chest (Around) <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="chest" name="chest" step="0.01" value="<?php echo $chest;?>">
	      				</div>
	      				<div class="red"><?php echo $chestErr;?></div>
	  				</div>

<!-- front Length -->
					<div class="form-group">
	      				<label class="control-label col-sm-4" for="flength">Front Length <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="flength" name="flength" step="0.01" value="<?php echo $flength;?>">
	      				</div>
	      				<div class="red"><?php echo $flengthErr;?></div>
	  				</div>


					<div class="form-group">
	      				<label class="control-label col-sm-4" for="fndepth">Front Neck Depth <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="fndepth" name="fndepth" step="0.01" value="<?php echo $fndepth;?>">
	      				</div>
	      				<div class="red"><?php echo $fndepthErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="apex">Shoulder to Apex <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="apex" name="apex" step="0.01" value="<?php echo $apex;?>">
	      				</div>
	      				<div class="red"><?php echo $apexErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="slength">Sleeve Length <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="slength" name="slength" step="0.01" value="<?php echo $slength;?>">
	      				</div>
	      				<div class="red"><?php echo $slengthErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="saround">Sleeve Around <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="saround" name="saround" step="0.01" value="<?php echo $saround;?>">
	      				</div>
	      				<div class="red"><?php echo $saroundErr;?></div>
	  				</div>

					<div class="form-group">
	      				<label class="control-label col-sm-4" for="armhole">Arm Hole <span class="red">*</span> :</label>
	      				<div class="col-sm-3">
	        				<input type="number" class="form-control" id="armhole" name="armhole" step="0.01" value="<?php echo $armhole;?>">
	      				</div>
	      				<div class="red"><?php echo $armholeErr;?></div>
	  				</div>

			      	<div class="form-group"> 
	      		  		<div class="col-sm-offset-4 col-sm-10">
		        			<button type="submit" class="btn btn-default btn-primary" onclick="katoridiff()">Generate Katori Blouse Design</button>
		      			</div>
	    			</div>
    			</div>
    </form>