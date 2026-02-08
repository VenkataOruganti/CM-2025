<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../config/recaptcha.php';
require_once __DIR__ . '/../includes/lang-init.php';

$message = '';
$messageType = '';

// Initialize form variables
$username = '';
$email = '';
$businessName = '';
$businessLocation = '';
$mobileNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $userType = trim($_POST['user_type'] ?? 'individual');
    $businessName = trim($_POST['business_name'] ?? '');
    $businessLocation = trim($_POST['business_location'] ?? '');
    $mobileNumber = trim($_POST['mobile_number'] ?? '');

    // reCAPTCHA v3 verification
    $recaptchaToken = $_POST['recaptcha_token'] ?? '';
    $recaptchaResult = verifyRecaptcha($recaptchaToken, 'register');

    if (!$recaptchaResult['success']) {
        $message = __('register.errors.captcha_failed') ?? 'Security verification failed. Please try again.';
        $messageType = 'error';
        error_log("Registration reCAPTCHA failed for email: $email - Score: {$recaptchaResult['score']}");
    }
    // Validation
    elseif (empty($username) || empty($email) || empty($password)) {
        $message = __('register.errors.fill_required');
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = __('register.errors.invalid_email');
        $messageType = 'error';
    } elseif (strlen($username) < 3) {
        $message = __('register.errors.username_short');
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = __('register.errors.password_short');
        $messageType = 'error';
    } elseif (in_array($userType, ['boutique', 'wholesaler', 'pattern_provider']) && (empty($businessName) || empty($businessLocation) || empty($mobileNumber))) {
        $message = __('register.errors.fill_business');
        $messageType = 'error';
    } else {
        // Attempt registration
        $result = registerUser(
            $username,
            $email,
            $password,
            $userType,
            !empty($businessName) ? $businessName : null,
            !empty($businessLocation) ? $businessLocation : null,
            !empty($mobileNumber) ? $mobileNumber : null
        );

        if ($result['success']) {
            // Send welcome email (non-blocking - don't fail registration if email fails)
            try {
                $emailResult = sendWelcomeEmail($email, $username, $userType);
                if (!$emailResult['success']) {
                    error_log("Welcome email failed for {$email}: " . $emailResult['message']);
                }
            } catch (Exception $e) {
                error_log("Welcome email error for {$email}: " . $e->getMessage());
            }

            $message = $result['message'] . ' ' . __('register.success.can_login');
            $messageType = 'success';
            // Clear form
            $username = $email = $businessName = $businessLocation = $mobileNumber = '';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

// Set header variables
$pageTitle = 'Register - Create Your Free Account';
$metaDescription = 'Create a free CuttingMaster account. Join as an individual tailor, boutique owner, wholesaler, or pattern designer. Save measurements, access patterns, and grow your fashion business.';
$metaKeywords = 'CuttingMaster register, tailor signup, boutique registration, pattern designer account, wholesale account India, free tailoring account';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';

$additionalStyles = '
.auth-container-register {
    max-width: 1280px;
}

.auth-container-register .auth-form {
    max-width: 60%; /* Reduced by 40% from 100% (additional 10% reduction) */
}

.auth-container-register .btn-submit {
    width: 100%; /* Full width of the form */
    margin-left: 0;
    margin-right: 0;
}

.auth-container-register .hero-description.auth-description {
    font-size: 0.875rem; /* Reduced font size */
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #E2E8F0; /* Add separator line */
}

.auth-container-register .radio-label {
    align-items: flex-start; /* Top-align radio button with multi-line labels */
}

.auth-container-register .auth-title {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.auth-container-register .form-input-wrapper {
    position: relative;
}

.auth-container-register .form-input-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #A0AEC0;
    pointer-events: none;
}
';

// reCAPTCHA site key for JavaScript
$recaptchaSiteKey = getRecaptchaSiteKey();

$additionalScripts = "
// Toggle business fields based on user type selection
const userTypeRadios = document.querySelectorAll('input[name=\"user_type\"]');
const businessFields = document.getElementById('business_fields');
const businessNameInput = document.getElementById('business_name');
const businessLocationInput = document.getElementById('business_location');
const mobileNumberInput = document.getElementById('mobile_number');
const businessNameLabel = document.getElementById('business_name_label');

// Translation strings for JavaScript
const businessNameLabelBoutique = '" . addslashes(__('register.business_name_boutique')) . "';
const businessNameLabelDefault = '" . addslashes(__('register.business_name_default')) . "';

userTypeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'boutique' || this.value === 'wholesaler' || this.value === 'pattern_provider') {
            businessFields.style.display = 'block';
            businessNameInput.setAttribute('required', 'required');
            businessLocationInput.setAttribute('required', 'required');
            mobileNumberInput.setAttribute('required', 'required');

            // Update label based on user type
            if (this.value === 'wholesaler') {
                businessNameLabel.textContent = businessNameLabelDefault;
            } else if (this.value === 'pattern_provider') {
                businessNameLabel.textContent = businessNameLabelDefault;
            } else {
                businessNameLabel.textContent = businessNameLabelBoutique;
            }
        } else {
            businessFields.style.display = 'none';
            businessNameInput.removeAttribute('required');
            businessLocationInput.removeAttribute('required');
            mobileNumberInput.removeAttribute('required');
        }
    });
});

// reCAPTCHA v3 form submission
const recaptchaSiteKey = '" . $recaptchaSiteKey . "';
if (recaptchaSiteKey) {
    const registerForm = document.querySelector('.auth-form');
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('button[type=\"submit\"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Verifying...';

        grecaptcha.ready(function() {
            grecaptcha.execute(recaptchaSiteKey, {action: 'register'}).then(function(token) {
                // Add token to form
                let tokenInput = form.querySelector('input[name=\"recaptcha_token\"]');
                if (!tokenInput) {
                    tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'recaptcha_token';
                    form.appendChild(tokenInput);
                }
                tokenInput.value = token;
                form.submit();
            }).catch(function(error) {
                console.error('reCAPTCHA error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                // Submit anyway if reCAPTCHA fails (graceful degradation)
                form.submit();
            });
        });
    });
}
";

// Add reCAPTCHA script to head
$additionalHeadScripts = getRecaptchaScript();

// Include header
include __DIR__ . '/../includes/header.php';
?>

    <!-- Register Section -->
    <section class="hero auth-section register-section auth-section-padded">
        <div class="hero-container auth-container auth-container-register">
            <div class="hero-content auth-content">
                <h1 class="hero-title auth-title">
                    <?php _e('register.title'); ?> <span class="hero-title-accent"><?php _e('register.title_accent'); ?></span>
                </h1>
                <p class="hero-description auth-description">
                    <?php _e('register.description'); ?>
                </p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group radio-inline-group">
                        <label class="form-label"><?php _e('register.are_you_a'); ?></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="individual" class="radio-input" required checked id="user_type_individual">
                                <span><?php _e('register.individual'); ?><br><span style="font-size: 0.75rem; color: #718096;"><?php _e('register.individual_desc'); ?></span></span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="boutique" class="radio-input" required id="user_type_boutique">
                                <span><?php _e('register.boutique'); ?><br><span style="font-size: 0.75rem; color: #718096;"><?php _e('register.boutique_desc'); ?></span></span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="pattern_provider" class="radio-input" required id="user_type_pattern_provider">
                                <span><?php _e('register.pattern_provider'); ?><br><span style="font-size: 0.75rem; color: #718096;"><?php _e('register.pattern_provider_desc'); ?></span></span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="user_type" value="wholesaler" class="radio-input" required id="user_type_wholesaler">
                                <span><?php _e('register.wholesaler'); ?><br><span style="font-size: 0.75rem; color: #718096;"><?php _e('register.wholesaler_desc'); ?></span></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label"><?php _e('register.your_name'); ?></label>
                        <div class="form-input-wrapper">
                            <input type="text" id="username" name="username" class="form-input"
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                   placeholder="<?php _e('register.your_name_placeholder'); ?>" required>
                            <i data-lucide="user" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <!-- Business Fields (Hidden by default) -->
                    <div id="business_fields" class="business-fields" style="display: none;">
                        <div class="form-group">
                            <label for="business_name" class="form-label" id="business_name_label"><?php _e('register.business_name_boutique'); ?></label>
                            <div class="form-input-wrapper">
                                <input type="text" id="business_name" name="business_name" class="form-input"
                                       value="<?php echo isset($businessName) ? htmlspecialchars($businessName) : ''; ?>"
                                       placeholder="<?php _e('register.business_name_placeholder'); ?>">
                                <i data-lucide="building-2" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="business_location" class="form-label"><?php _e('register.location'); ?></label>
                            <div class="form-input-wrapper">
                                <input type="text" id="business_location" name="business_location" class="form-input"
                                       value="<?php echo isset($businessLocation) ? htmlspecialchars($businessLocation) : ''; ?>"
                                       placeholder="<?php _e('register.location_placeholder'); ?>">
                                <i data-lucide="map-pin" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="mobile_number" class="form-label"><?php _e('register.mobile_number'); ?></label>
                            <div class="form-input-wrapper">
                                <input type="tel" id="mobile_number" name="mobile_number" class="form-input"
                                       value="<?php echo isset($mobileNumber) ? htmlspecialchars($mobileNumber) : ''; ?>"
                                       placeholder="<?php _e('register.mobile_placeholder'); ?>">
                                <i data-lucide="phone" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label"><?php _e('register.email'); ?></label>
                        <div class="form-input-wrapper">
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                   placeholder="<?php _e('register.email_placeholder'); ?>" required>
                            <i data-lucide="mail" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label"><?php _e('register.password'); ?></label>
                        <div class="form-input-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                   placeholder="<?php _e('register.password_placeholder'); ?>" required>
                            <i data-lucide="lock" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-large btn-solid btn-submit"><?php _e('register.register_btn'); ?></button>

                    <div class="form-footer">
                        <p><?php _e('register.already_have_account'); ?> <a href="login.php" class="link-primary"><?php _e('register.login_here'); ?></a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
