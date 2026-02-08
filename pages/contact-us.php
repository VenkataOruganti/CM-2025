<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/recaptcha.php';
require_once __DIR__ . '/../includes/lang-init.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

$message = '';
$messageType = '';

// Generate form token for spam protection
if (!isset($_SESSION['contact_form_token'])) {
    $_SESSION['contact_form_token'] = bin2hex(random_bytes(32));
    $_SESSION['contact_form_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');

    // Spam protection checks
    $honeypot = trim($_POST['website'] ?? ''); // Honeypot field - should be empty
    $formToken = $_POST['form_token'] ?? '';
    $formLoadTime = intval($_POST['form_load_time'] ?? 0);

    $isSpam = false;

    // Check 0: reCAPTCHA v3 verification
    $recaptchaToken = $_POST['recaptcha_token'] ?? '';
    $recaptchaResult = verifyRecaptcha($recaptchaToken, 'contact');
    if (!$recaptchaResult['success']) {
        $isSpam = true;
        error_log("Contact form reCAPTCHA failed for email: $email - Score: {$recaptchaResult['score']}");
    }

    // Check 1: Honeypot field must be empty (bots often fill all fields)
    if (!empty($honeypot)) {
        $isSpam = true;
    }

    // Check 2: Form token must match session token
    if ($formToken !== ($_SESSION['contact_form_token'] ?? '')) {
        $isSpam = true;
    }

    // Check 3: Form must be submitted at least 3 seconds after loading (bots submit instantly)
    $currentTime = time();
    if ($formLoadTime > 0 && ($currentTime - $formLoadTime) < 3) {
        $isSpam = true;
    }

    // Check 4: Form must be submitted within 1 hour of loading
    if ($formLoadTime > 0 && ($currentTime - $formLoadTime) > 3600) {
        $isSpam = true;
    }

    if ($isSpam) {
        // Silently reject spam - don't give feedback to bots
        $message = 'Thank you for contacting us! We have received your message and will respond as soon as possible.';
        $messageType = 'success';
        $name = $email = $mobile = $subject = $messageText = '';
    } elseif (empty($name) || empty($email) || empty($subject) || empty($messageText)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO enquiries (name, email, mobile, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $mobile, $subject, $messageText]);

            $message = 'Thank you for contacting us! We have received your message and will respond as soon as possible.';
            $messageType = 'success';
            // Clear form
            $name = $email = $mobile = $subject = $messageText = '';

            // Regenerate token after successful submission
            $_SESSION['contact_form_token'] = bin2hex(random_bytes(32));
            $_SESSION['contact_form_time'] = time();
        } catch (PDOException $e) {
            $message = 'Sorry, there was an error submitting your message. Please try again later.';
            $messageType = 'error';
        }
    }
}

$formToken = $_SESSION['contact_form_token'];
$formLoadTime = time();

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = 'Contact Us - Get In Touch';
$metaDescription = 'Contact CuttingMaster for customized tailoring patterns, custom clothing inquiries, wholesale partnerships, or any questions. We respond within 2-3 business days. Reach out today!';
$metaKeywords = 'contact CuttingMaster, tailoring support, pattern making help, wholesale inquiry, custom tailoring questions, fashion design contact India';
$activePage = 'contact-us';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';

// Get current user info for header
if ($isLoggedIn) {
    require_once __DIR__ . '/../config/auth.php';
    $currentUser = getCurrentUser();
}

// Page-specific styles
$additionalStyles = <<<'CSS'
        .form-input-wrapper {
            position: relative;
        }

        .form-input-wrapper .form-input {
            padding-right: 2.5rem;
        }

        .form-input-wrapper .form-input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #B19CD9;
            pointer-events: none;
        }

        .form-input-wrapper.textarea-wrapper .form-input-icon {
            top: 1.25rem;
            transform: none;
        }

        /* Success Modal Styles */
        .success-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .success-modal.active {
            display: flex;
        }

        .success-modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 1px solid rgba(177, 156, 217, 0.3);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .success-modal-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(177, 156, 217, 0.2), rgba(177, 156, 217, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #B19CD9;
        }

        .success-modal-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.75rem;
            color: #fff;
            margin-bottom: 1rem;
        }

        .success-modal-message {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .success-modal-notice {
            background: rgba(177, 156, 217, 0.1);
            border: 1px solid rgba(177, 156, 217, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .success-modal-notice p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            text-align: left;
        }

        .success-modal-notice i {
            color: #B19CD9;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .success-modal-btn {
            background: linear-gradient(135deg, #B19CD9, #9370DB);
            color: #fff;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .success-modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(177, 156, 217, 0.4);
        }
CSS;

// reCAPTCHA site key for JavaScript
$recaptchaSiteKey = getRecaptchaSiteKey();

// Add reCAPTCHA script to head
$additionalHeadScripts = getRecaptchaScript();

// Include shared header
include __DIR__ . '/../includes/header.php';
?>

    <!-- Contact Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container">
            <div class="hero-content auth-content">
                <p class="hero-tag">Get In Touch</p>
                <h1 class="hero-title auth-title">
                    <span class="hero-title-accent">Contact</span> Us
                </h1>
                <p class="hero-description auth-description" style="font-size: 0.95rem;">
                    Have a question or inquiry? We'd love to hear from you. Fill out the form below and we'll get back to you as soon as possible.
                </p>
                <div style="width: 80px; height: 1px; background: linear-gradient(90deg, transparent, #B19CD9, transparent); margin: 1.5rem auto;"></div>

                <?php if ($message && $messageType === 'error'): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form" autocomplete="off">
                    <!-- Spam Protection: Hidden fields -->
                    <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($formToken); ?>">
                    <input type="hidden" name="form_load_time" value="<?php echo $formLoadTime; ?>">

                    <!-- Honeypot field - hidden from users, bots will fill it -->
                    <div style="position: absolute; left: -9999px; opacity: 0; height: 0; overflow: hidden;" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="form-input-wrapper">
                            <input type="text" id="name" name="name" class="form-input"
                                   value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                   placeholder="Enter your full name" required autofocus autocomplete="off">
                            <i data-lucide="user" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="form-input-wrapper">
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                   placeholder="Enter your email" required autocomplete="off">
                            <i data-lucide="mail" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile Number <span style="font-weight: 400; color: rgba(255,255,255,0.5);">(Optional)</span></label>
                        <div class="form-input-wrapper">
                            <input type="tel" id="mobile" name="mobile" class="form-input"
                                   value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>"
                                   placeholder="Enter your mobile number" autocomplete="off">
                            <i data-lucide="phone" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <div class="form-input-wrapper">
                            <input type="text" id="subject" name="subject" class="form-input"
                                   value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"
                                   placeholder="What is this regarding?" required autocomplete="off">
                            <i data-lucide="tag" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <div class="form-input-wrapper textarea-wrapper">
                            <textarea id="message" name="message" class="form-input form-textarea" rows="6"
                                      placeholder="Tell us more about your inquiry..." required autocomplete="off"><?php echo isset($messageText) ? htmlspecialchars($messageText) : ''; ?></textarea>
                            <i data-lucide="message-square" class="form-input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-large btn-solid btn-submit">SEND MESSAGE</button>

                    <div class="form-footer">
                    
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Success Modal -->
    <div id="successModal" class="success-modal">
        <div class="success-modal-content">
            <div class="success-modal-icon">
                <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
            </div>
            <h2 class="success-modal-title">Message Sent!</h2>
            <p class="success-modal-message">
                Thank you for contacting us! We have received your message and will respond as soon as possible.
            </p>
            <div class="success-modal-notice">
                <p>
                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                    <span>Please note: Due to high demand, there may be a delay in our response. We appreciate your patience and will get back to you within 2-3 business days.</span>
                </p>
            </div>
            <button type="button" class="success-modal-btn" onclick="closeSuccessModal()">Got It</button>
        </div>
    </div>

    <script>
        // Success Modal Functions
        function openSuccessModal() {
            document.getElementById('successModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on outside click
        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSuccessModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSuccessModal();
            }
        });

        // Show modal on successful form submission
        <?php if ($messageType === 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openSuccessModal();
        });
        <?php endif; ?>

        // reCAPTCHA v3 form submission
        <?php if ($recaptchaSiteKey): ?>
        (function() {
            const recaptchaSiteKey = '<?php echo $recaptchaSiteKey; ?>';
            const contactForm = document.querySelector('.auth-form');

            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Verifying...';

                grecaptcha.ready(function() {
                    grecaptcha.execute(recaptchaSiteKey, {action: 'contact'}).then(function(token) {
                        // Add token to form
                        let tokenInput = form.querySelector('input[name="recaptcha_token"]');
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
        })();
        <?php endif; ?>
    </script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
