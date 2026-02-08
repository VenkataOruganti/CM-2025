<?php
session_start();
?>

<form class="form-horizontal" method="post" name="blouse" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 

<!--  Right Column -->			
    <div>
        <h2>Measurement</h2>
           		
            <div class="form-group">
                <span style="text-align: center; padding-left: 50px; "><span class="red">*</span>All values in <span class="red">"inches" </span>Only.<br><br></span>
						
<!-- Download File -->
            <label class="control-label col-sm-5" for="cust">Download File Name <span class="red">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="cust" placeholder="File / Customer Name" name="cust" value="<?php echo $cust;?>">
                </div>   
                <div class="red"><?php echo $custErr;?></div>
            </div>

<!-- Full Shoulder -->                   
            <div class="form-group">
                <label class="control-label col-sm-5" for="fshoulder">Full Shoulder(1) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="fshoulder" name="fshoulder" step="0.5" min="10" max="17"value="<?php echo $fshoulder;?>">
                </div>
                <div class="red"><?php echo $fshoulderErr;?></div>
            </div>                                              
	  				
<!-- Shoulder Strap -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="shoulder">Shoulder Strap(2) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="shoulder" name="shoulder" step="0.5" min="1" max="5" value="<?php echo $shoulder;?>">
                </div>
                <div class="red"><?php echo $shoulderErr;?></div>
            </div>
                    
<!-- Front Neck -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="fnDepth">Front Neck Depth(3)<span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="fnDepth" name="fnDepth" step="0.5" value="<?php echo $fnDepth;?>">
                </div>
                <div class="red"><?php echo $fnDepthErr;?></div>
            </div>
            
<!-- Back Neck -->                
            <div class="form-group">
                <label class="control-label col-sm-5" for="bnDepth">Back Neck Depth(4) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="bnDepth" name="bnDepth" step="0.5" value="<?php echo $bnDepth;?>">
                </div>
                <div class="red"><?php echo $bnDepthErr;?></div>
            </div>
            <div class="col-md-12"><hr></div>

<!-- Apex -->                
            <div class="form-group">
                <label class="control-label col-sm-5" for="apex">Shoulder to Apex(5) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="apex" name="apex" step="0.5" value="<?php echo $apex;?>">
                </div>
                <div class="red"><?php echo $apexErr;?></div>
            </div>
                                                                       
<!-- Chest -->                
            <div class="form-group">
                <label class="control-label col-sm-5" for="chest">Upper Chest(6) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="chest" name="chest" step="0.5" min="26" max="44" value="<?php echo $chest;?>">
                </div>
                <div class="red"><?php echo $chestErr;?></div>
            </div>

<!-- Bust Round -->                                
            <div class="form-group">
                <label class="control-label col-sm-5" for="bust">Bust Round(7) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="bust" name="bust" step="0.5" value="<?php echo $bust;?>">
                </div>
                <div class="red"><?php echo $bustErr;?></div>
            </div>

<!-- Waist Round -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="waist">Waist Round(8) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="waist" name="waist" step="0.5" min="26" max="42" value="<?php echo $waist;?>">
                </div>
                <div class="red"><?php echo $waistErr;?></div>
            </div>

<!-- Hip Round -->
           <div class="form-group">
                <label class="control-label col-sm-5" for="hip">Hip Round(9) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="hip" name="hip" step="0.5" min="26" max="42" value="<?php echo $hip;?>">
                </div>
                <div class="red"><?php echo $hipErr;?></div>
            </div>
            
<!-- Bottom Round -->
           <div class="form-group">
                <label class="control-label col-sm-5" for="bottom">Bottom Round(9) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="bottom" name="bottom" step="0.5" min="26" value="<?php echo $bottom;?>">
                </div>
                <div class="red"><?php echo $bottomErr;?></div>
            </div>
            <div class="col-md-12"><hr></div>            

<!-- Full Length -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="flength">Full Length(10) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="flength" name="flength" step="0.5" min="10" value="<?php echo $flength;?>">
                </div>
                <div class="red"><?php echo $flengthErr;?></div>
            </div>

<!-- waist Length -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="wlength">Waist Length(11) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="wlength" name="wlength" step="0.5" value="<?php echo $wlength;?>">
                </div>
                <div class="red"><?php echo $wlengthErr;?></div>
            </div>
            
<!-- Hip Length -->            
            <div class="form-group">
                <label class="control-label col-sm-5" for="hlength">Hip Length(12) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="hlength" name="hlength" step="0.5" value="<?php echo $hlength;?>">
                </div>
                <div class="red"><?php echo $hlengthErr;?></div>
            </div>
            <div class="col-md-12"><hr></div>
            
<!-- Sleeve Length -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="slength">Sleeve Length(13) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="slength" name="slength" step="0.5" value="<?php echo $slength;?>">
                </div>
                <div class="red"><?php echo $slengthErr;?></div>
            </div>
            
<!-- Arm Round -->
            <div class="form-group">
                <label class="control-label col-sm-5" for="saround">Arm Round(14) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="saround" name="saround" step="0.5" value="<?php echo $saround;?>">
                </div>
                <div class="red"><?php echo $saroundErr;?></div>
            </div>
            
<!-- Sleeve End Round -->	
            <div class="form-group">
                <label class="control-label col-sm-5" for="sopen">Sleeve End Round(15)<span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="sopen" name="sopen" step="0.5" value="<?php echo $sopen;?>">
                </div>
                <div class="red"><?php echo $sopenErr;?></div>
            </div>
            
<!-- Arm Hole -->	
            <div class="form-group">
                <label class="control-label col-sm-5" for="armhole">Armhole(16) <span class="red">*</span></label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="armhole" name="armhole" step="0.5" value="<?php echo $armhole;?>">
                </div>
                <div class="red"><?php echo $armholeErr;?></div>
            </div>
            
<!-- Kurti Design -->
            <div class="form-group"> 
                <div class="col-sm-offset-4 col-sm-10">
                    <button type="submit" class="btn btn-default btn-primary" onclick="diff()">Generate Kurti Design</button>
                </div>
            </div>
        </div>
    </form>