<?php
/**
 * =============================================================================
 * BLOUSE MEASUREMENT FORM - Saree Blouse Pattern Designer
 * =============================================================================
 *
 * This file handles:
 * 1. Displaying the measurement input form
 * 2. Validating user-submitted measurements
 * 3. Converting inches to millimeters for pattern calculations
 * 4. Storing measurements in session for pattern generation
 *
 * NOTE: Session must be started in parent file (savi.php) before including this file
 * =============================================================================
 */

// =============================================================================
// SECTION 1: CONSTANTS & INITIAL SETUP
// =============================================================================

// Conversion constant: 1 inch = 25.4 millimeters
// This is used to convert user inputs (in inches) to millimeters for pattern calculations
$cIn = 25.4;

// Display control variable (used in parent page for showing/hiding design section)
$displayRight = "none";


// =============================================================================
// SECTION 2: INITIALIZE VARIABLES
// =============================================================================

// Text input variables (customer name, order number)
// These are initialized as empty strings because they hold text values
$cust = $order = "";

// Numeric measurement variables (all measurements are in inches)
// These are initialized as 0 to prevent PHP 8+ type errors when doing math operations
$shoulder = $fshoulder = $fndepth = $chest = $waist = $apex = 0;
$blength = $flength = $slength = $saround = $sopen = $armhole = 0;
$bnDepth = $bust = 0;

// Error message variables (will hold validation error messages)
// These are initialized as empty strings - they'll be filled if validation fails
$shoulderErr = $fshoulderErr = $custErr = $orderErr = $fndepthErr = "";
$chestErr = $waistErr = $apexErr = $blengthErr = $flengthErr = "";
$slengthErr = $saroundErr = $sopenErr = $armholeErr = $bnDepthErr = $bustErr = "";


// =============================================================================
// SECTION 3: FORM SUBMISSION HANDLING & VALIDATION
// =============================================================================

// Check if form was submitted (user clicked "Generate Blouse Design" button)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // -------------------------------------------------------------------------
    // VALIDATE: Customer/File Name (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["cust"])) {
        // If field is empty, set error message
        $custErr = "Name required";
    } else {
        // If field has value, clean it and store in variable
        $cust = test_input($_POST["cust"]);
        // Save to session so it's available on other pages
        $_SESSION["cust"] = $cust;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Shoulder Strap (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["shoulder"])) {
        $shoulderErr = "Required";
    } else {
        // Get the value entered by user (in inches)
        $shoulder = test_input($_POST["shoulder"]);
        // Convert inches to millimeters for pattern calculations
        $shoulder1 = $shoulder * $cIn;

        // Store both values in session
        $_SESSION["shoulder"] = $shoulder;      // Original in inches
        $_SESSION["shoulder1"] = $shoulder1;    // Converted to mm
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Full Shoulder (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["fshoulder"])) {
        $fshoulderErr = "Required";
    } else {
        $fshoulder = test_input($_POST["fshoulder"]);
        $_SESSION["fshoulder"] = $fshoulder;

        $fshoulder1 = $fshoulder * $cIn;
        $_SESSION["fshoulder1"] = $fshoulder1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Front Length (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["flength"])) {
        $flengthErr = "Required";
    } else {
        $flength = test_input($_POST["flength"]);
        $flength1 = $flength * $cIn;

        $_SESSION["flength"] = $flength;
        $_SESSION["flength1"] = $flength1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Front Neck Depth (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["fndepth"])) {
        $fndepthErr = "Required";
    } else {
        $fndepth = test_input($_POST["fndepth"]);
        $fndepth1 = $fndepth * $cIn;

        $_SESSION["fndepth"] = $fndepth;
        $_SESSION["fndepth1"] = $fndepth1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Upper Chest (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["chest"])) {
        $chestErr = "Required";
    } else {
        $chest = test_input($_POST["chest"]);
        $chest1 = $chest * $cIn;

        $_SESSION["chest"] = $chest;
        $_SESSION["chest1"] = $chest1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Waist (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["waist"])) {
        $waistErr = "Required";
    } else {
        $waist = test_input($_POST["waist"]);
        $waist1 = $waist * $cIn;

        $_SESSION["waist"] = $waist;
        $_SESSION["waist1"] = $waist1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Shoulder to Apex (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["apex"])) {
        $apexErr = "Required";
    } else {
        $apex = test_input($_POST["apex"]);
        $apex1 = $apex * $cIn;

        $_SESSION["apex"] = $apex;
        $_SESSION["apex1"] = $apex1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Back Length (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["blength"])) {
        $blengthErr = "Required";
    } else {
        $blength = test_input($_POST["blength"]);
        $blength1 = $blength * $cIn;

        $_SESSION["blength"] = $blength;
        $_SESSION["blength1"] = $blength1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Sleeve Length (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["slength"])) {
        $slengthErr = "Required";
    } else {
        $slength = test_input($_POST["slength"]);
        $slength1 = $slength * $cIn;

        $_SESSION["slength"] = $slength;
        $_SESSION["slength1"] = $slength1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Arm Round (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["saround"])) {
        $saroundErr = "Required";
    } else {
        $saround = test_input($_POST["saround"]);
        $saround1 = $saround * $cIn;

        $_SESSION["saround"] = $saround;
        $_SESSION["saround1"] = $saround1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Sleeve End Round (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["sopen"])) {
        $sopenErr = "Required";
    } else {
        $sopen = test_input($_POST["sopen"]);
        $sopen1 = $sopen * $cIn;

        $_SESSION["sopen"] = $sopen;
        $_SESSION["sopen1"] = $sopen1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Bust Round (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["bust"])) {
        $bustErr = "Required";
    } else {
        $bust = test_input($_POST["bust"]);
        $bust1 = $bust * $cIn;

        $_SESSION["bust"] = $bust;
        $_SESSION["bust1"] = $bust1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Armhole (Required field)
    // -------------------------------------------------------------------------
    if (empty($_POST["armhole"])) {
        $armholeErr = "Required";
    } else {
        $armhole = test_input($_POST["armhole"]);
        $armhole1 = $armhole * $cIn;

        $_SESSION["armhole"] = $armhole;
        $_SESSION["armhole1"] = $armhole1;
    }

    // -------------------------------------------------------------------------
    // VALIDATE: Back Neck Depth (Required field with minimum value check)
    // -------------------------------------------------------------------------
    if (empty($_POST["bnDepth"])) {
        $bnDepthErr = "Required";
    } elseif ($_POST["bnDepth"] < 1) {
        // Additional validation: value must be at least 1 inch
        $bnDepthErr = "Not a enough Number";
    } else {
        $bnDepth = test_input($_POST["bnDepth"]);
        $bnDepth1 = $bnDepth * $cIn;

        $_SESSION["bnDepth"] = $bnDepth;
        $_SESSION["bnDepth1"] = $bnDepth1;
    }

    // -------------------------------------------------------------------------
    // SAVE TO DATABASE: If all validations pass, save measurements
    // -------------------------------------------------------------------------
    $hasErrors = !empty($custErr) || !empty($shoulderErr) || !empty($fshoulderErr) ||
                 !empty($flengthErr) || !empty($fndepthErr) || !empty($chestErr) ||
                 !empty($waistErr) || !empty($apexErr) || !empty($blengthErr) ||
                 !empty($slengthErr) || !empty($saroundErr) || !empty($sopenErr) ||
                 !empty($bustErr) || !empty($armholeErr) || !empty($bnDepthErr);

    if (!$hasErrors && !empty($cust)) {
        // Include database connection
        require_once __DIR__ . '/../../../config/database.php';
        global $pdo;

        try {
            // Check if user is logged in
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            // Insert measurement into database
            $stmt = $pdo->prepare("
                INSERT INTO measurements (
                    user_id, category, measurement_of, customer_name,
                    blength, fshoulder, shoulder, bnDepth, fndepth,
                    apex, flength, chest, waist, slength, saround,
                    sopen, bust, armhole, created_at
                ) VALUES (
                    ?, 'women', 'customer', ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $userId,
                $cust,
                $blength, $fshoulder, $shoulder, $bnDepth, $fndepth,
                $apex, $flength, $chest, $waist, $slength, $saround,
                $sopen, $bust, $armhole
            ]);

            // Get the inserted measurement ID and store in session
            $measurementId = $pdo->lastInsertId();
            $_SESSION['measurement_id'] = $measurementId;

        } catch (PDOException $e) {
            // Log error but don't break the page - pattern can still display from session
            error_log("Failed to save measurement to database: " . $e->getMessage());
        }
    }
}

// =============================================================================
// SECTION 4: STORE MEASUREMENT SUMMARY IN SESSION
// =============================================================================

// Create human-readable measurement summaries for display/printing
// These strings are stored in session and can be shown to the user
$_SESSION["measure"] = "Shoulder: " . $shoulder . ", Full Shoulder: " . $fshoulder . ", BackNeck Depth: " . $bnDepth;
$_SESSION["measure1"] = "Back Length: " . $blength . ", Waist: " . $waist . ", Chest: " . $chest;
$_SESSION["measure2"] = "Front Length: " . $flength . ", Front Neck Depth: " . $fndepth . ", Shoulder to Apex: " . $apex;
$_SESSION["measure3"] = "Sleeve Length: " . $slength . ", Sleeve Open: " . $sopen . ", Sleeve Round: " . $saround . ", Arm Hole: " . $armhole;

// Redundant session storage (these are already set in validation section above)
// Keeping for backward compatibility with existing code
$_SESSION["bnDepth"] = $bnDepth;
$_SESSION["shoulder"] = $shoulder;


// =============================================================================
// HELPER FUNCTION: Clean and Sanitize User Input
// =============================================================================

/**
 * Clean user input to prevent security issues and data corruption
 *
 * @param string $data The raw input from user
 * @return string The cleaned/sanitized input
 */
function test_input($data) {
    // Step 1: Remove whitespace from beginning and end
    $data = trim($data);

    // Step 2: Remove backslashes (prevents injection attacks)
    $data = stripslashes($data);

    // Step 3: Convert special characters to HTML entities (prevents XSS attacks)
    // For example: converts "<script>" to "&lt;script&gt;"
    $data = htmlspecialchars($data);

    return $data;
}

?>

<!-- ============================================================================= -->
<!-- SECTION 5: HTML FORM - User Interface for Measurement Input                  -->
<!-- ============================================================================= -->

<form class="form-horizontal" method="post" name="blouse" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

    <!-- Main Form Container -->
    <div>
        <h2>Measurement</h2>

        <!-- Instructions for User -->
        <div class="form-group">
            <span style="text-align: center; padding-left: 50px;">
                <span class="red">*</span>Enter values in <span class="red">"inches"</span> Only.<br><br>
            </span>

            <!-- ============================================== -->
            <!-- FIELD: Customer/File Name                      -->
            <!-- ============================================== -->
            <label class="control-label col-sm-5" for="cust">Download File Name <span class="red">*</span></label>
            <div class="col-sm-6">
                <!-- Pre-fill with previously entered value (if form was submitted with errors) -->
                <input type="text" class="form-control" id="cust" placeholder="File / Customer Name"
                       name="cust" value="<?php echo htmlspecialchars($cust ?? '');?>">
            </div>
            <!-- Display error message if validation failed -->
            <div class="red"><?php echo htmlspecialchars($custErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Blouse Back Length (1)                  -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="blength">Blouse Back Length (1) <span class="red">*</span></label>
            <div class="col-sm-3">
                <!-- Number input with step of 0.5 (allows half inches), min=10, max=18 -->
                <input type="number" class="form-control" id="blength" name="blength" required
                       step="0.5" min="10" max="18" value="<?php echo $blength;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($blengthErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Full Shoulder (2)                       -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="fshoulder">Full Shoulder (2) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="fshoulder" name="fshoulder" required
                       step="0.5" min="10" max="17" value="<?php echo $fshoulder;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($fshoulderErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Shoulder Strap (3)                      -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="shoulder">Shoulder Strap (3) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="shoulder" name="shoulder" required
                       step="0.5" min="1" max="5" value="<?php echo $shoulder;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($shoulderErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Back Neck Depth (4)                     -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="bnDepth">Back Neck Depth (4) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="bnDepth" name="bnDepth" required
                       step="0.5" value="<?php echo $bnDepth;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($bnDepthErr ?? "");?></div>
        </div>

        <!-- Visual separator between back and front measurements -->
        <div class="col-md-12"><hr></div>

        <!-- ================================================ -->
        <!-- FRONT MEASUREMENTS SECTION                       -->
        <!-- ================================================ -->

        <!-- ============================================== -->
        <!-- FIELD: Front Neck Depth (5)                    -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="fndepth">Front Neck Depth (5) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="fndepth" name="fndepth" required
                       step="0.5" value="<?php echo $fndepth;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($fndepthErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Shoulder to Apex (6)                    -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="apex">Shoulder to Apex (6) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="apex" name="apex" required
                       step="0.5" value="<?php echo $apex;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($apexErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Front Length (7)                        -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="flength">Front Length (7) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="flength" name="flength" required
                       step="0.5" value="<?php echo $flength;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($flengthErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Upper Chest (8)                         -->
        <!-- Measured at bottom of armhole                  -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="chest">Upper Chest (8) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" placeholder="bottom of arm hole" class="form-control"
                       id="chest" name="chest" required step="0.5" min="26" max="44" value="<?php echo $chest;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($chestErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Bust Round (9)                          -->
        <!-- Measured at highest point of bust              -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="bust">Bust Round (9) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" placeholder="high point bust" class="form-control"
                       id="bust" name="bust" required step="0.5" value="<?php echo $bust;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($bustErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Waist Round (10)                        -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="waist">Waist Round (10) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="waist" name="waist" required
                       step="0.5" min="26" max="42" value="<?php echo $waist;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($waistErr ?? "");?></div>
        </div>

        <!-- Visual separator before sleeve measurements -->
        <div class="col-md-12"><hr></div>

        <!-- ================================================ -->
        <!-- SLEEVE MEASUREMENTS SECTION                      -->
        <!-- ================================================ -->

        <!-- ============================================== -->
        <!-- FIELD: Sleeve Length (11)                      -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="slength">Sleeve Length (11) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="slength" name="slength" required
                       step="0.5" value="<?php echo $slength;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($slengthErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Arm Round (12)                          -->
        <!-- Circumference of upper arm                     -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="saround">Arm Round (12) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="saround" name="saround" required
                       step="0.5" value="<?php echo $saround;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($saroundErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Sleeve End Round (13)                   -->
        <!-- Circumference at end of sleeve (wrist)         -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="sopen">Sleeve End Round (13) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="sopen" name="sopen" required
                       step="0.5" value="<?php echo $sopen;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($sopenErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- FIELD: Armhole (14)                            -->
        <!-- Circumference of armhole opening               -->
        <!-- ============================================== -->
        <div class="form-group">
            <label class="control-label col-sm-5" for="armhole">Armhole (14) <span class="red">*</span></label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="armhole" name="armhole" required
                       step="0.5" value="<?php echo $armhole;?>">
            </div>
            <div class="red"><?php echo htmlspecialchars($armholeErr ?? "");?></div>
        </div>

        <!-- ============================================== -->
        <!-- SUBMIT BUTTON                                  -->
        <!-- onclick="diff()" calls JavaScript validation   -->
        <!-- defined in parent page (savi.php)              -->
        <!-- ============================================== -->
        <div class="form-group">
            <div class="col-sm-offset-5 col-sm-10">
                <button type="submit" class="btn btn-default btn-primary" onclick="diff()">
                    Generate Blouse Design
                </button>
            </div>
        </div>

    </div><!-- End main form container -->
</form>

<!-- ============================================================================= -->
<!-- CLIENT-SIDE VALIDATION SCRIPT                                                 -->
<!-- This shows ALL validation errors at once (instead of one at a time)          -->
<!-- ============================================================================= -->
<script>
// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
    // Get the form element
    const form = document.querySelector('form[name="blouse"]');

    if (form) {
        // Prevent HTML5 default validation popups (we'll show our own)
        form.setAttribute('novalidate', 'novalidate');

        // Add submit event listener
        form.addEventListener('submit', function(e) {
            // Clear any previous error highlighting
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.style.borderColor = '';
            });

            let hasErrors = false;
            let firstError = null;

            // Check each required field
            inputs.forEach(input => {
                // For number inputs, check if value is empty or truly invalid
                // For text inputs, just check if empty
                const isEmpty = input.type === 'number'
                    ? (!input.value || input.value.trim() === '')
                    : (!input.value || input.value.trim() === '');

                if (isEmpty) {
                    // Mark field as invalid
                    input.style.borderColor = '#dc3545';
                    input.style.borderWidth = '2px';
                    hasErrors = true;

                    // Store first error to scroll to it
                    if (!firstError) {
                        firstError = input;
                    }
                }

                // Check min/max validation for number fields
                if (input.type === 'number' && input.value) {
                    const value = parseFloat(input.value);
                    const min = input.getAttribute('min');
                    const max = input.getAttribute('max');

                    if (min && value < parseFloat(min)) {
                        input.style.borderColor = '#dc3545';
                        input.style.borderWidth = '2px';
                        hasErrors = true;
                        if (!firstError) firstError = input;
                    }

                    if (max && value > parseFloat(max)) {
                        input.style.borderColor = '#dc3545';
                        input.style.borderWidth = '2px';
                        hasErrors = true;
                        if (!firstError) firstError = input;
                    }
                }
            });

            // If there are errors, prevent submission and show message
            if (hasErrors) {
                e.preventDefault();

                // Show alert with helpful message
                alert('Please fill in all required fields (marked with red border) before submitting.');

                // Scroll to first error
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                return false;
            }

            // If no errors, allow form to submit
            // The diff() function will run due to onclick attribute
            return true;
        });

        // Add blur event to remove error highlighting when field is filled
        form.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                // Remove error highlight if field has a value
                if (this.value && this.value.trim() !== '') {
                    this.style.borderColor = '';
                    this.style.borderWidth = '';
                }
            });
        });
    }
});
</script>
