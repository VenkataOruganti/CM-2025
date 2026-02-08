<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/lang-init.php';

$message = '';
$messageType = '';

// Check if there's a pending measurements message from URL (redirected from pattern-studio)
$fromPatternStudio = isset($_GET['action']) && $_GET['action'] === 'save_measurements';
if ($fromPatternStudio) {
    $message = __('login.login_to_save');
    $messageType = 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($email) || empty($password)) {
        $message = __('login.errors.fill_both');
        $messageType = 'error';
    } else {
        // First, try to login as admin
        $isAdmin = false;
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Admin login successful
                $isAdmin = true;

                // Update last login timestamp
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateStmt->execute([$admin['id']]);

                // Set admin session variables
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['email'] = $admin['username'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['is_admin'] = true;

                // Redirect to admin dashboard (or index page if dashboard doesn't exist)
                $adminDashboard = __DIR__ . '/dashboard-admin.php';
                if (file_exists($adminDashboard)) {
                    header('Location: dashboard-admin.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            }
        } catch(PDOException $e) {
            error_log("Admin login check error: " . $e->getMessage());
        }

        // If not admin, try regular user login
        if (!$isAdmin) {
            $result = loginUser($email, $password);

            if ($result['success']) {
                // Set session variables
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $result['user']['username'];
                $_SESSION['email'] = $result['user']['email'];
                $_SESSION['user_type'] = $result['user']['user_type'];
                $_SESSION['status'] = $result['user']['status'];
                $_SESSION['is_admin'] = false;

            // Check if there are pending measurements to save
            if (isset($_SESSION['pending_measurements'])) {
                try {
                    global $pdo;

                    $measurements = $_SESSION['pending_measurements'];
                    $userId = $_SESSION['user_id'];
                    $userType = $_SESSION['user_type'];

                    // Extract measurement data
                    $measurementOf = $measurements['measurement_of'];
                    $category = $measurements['category'];
                    $customerName = ($measurementOf === 'customer') ? $measurements['customer_name'] : null;
                    $customerReference = ($measurementOf === 'customer') ? $measurements['customer_reference'] : null;

                    // Initialize customer_id as null
                    $customerId = null;

                    // If boutique user with customer measurement, create customer record first
                    if ($userType === 'boutique' && $measurementOf === 'customer' && !empty($customerName)) {
                        // Check if customer already exists for this boutique
                        $checkStmt = $pdo->prepare("
                            SELECT id FROM customers
                            WHERE boutique_user_id = ? AND customer_name = ?
                        ");
                        $checkStmt->execute([$userId, $customerName]);
                        $existingCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);

                        if ($existingCustomer) {
                            // Customer already exists, use existing ID
                            $customerId = $existingCustomer['id'];
                        } else {
                            // Create new customer record
                            $customerStmt = $pdo->prepare("
                                INSERT INTO customers (boutique_user_id, customer_name, customer_reference)
                                VALUES (?, ?, ?)
                            ");
                            $customerStmt->execute([
                                $userId,
                                $customerName,
                                !empty($customerReference) ? $customerReference : null
                            ]);
                            $customerId = $pdo->lastInsertId();
                        }
                    }

                    // Women-specific measurements (from pattern-studio.php)
                    $blouseBackLength = !empty($measurements['blength']) ? floatval($measurements['blength']) : null;
                    $fullShoulder = !empty($measurements['fshoulder']) ? floatval($measurements['fshoulder']) : null;
                    $shoulderStrap = !empty($measurements['shoulder']) ? floatval($measurements['shoulder']) : null;
                    $backNeckDepth = !empty($measurements['bnDepth']) ? floatval($measurements['bnDepth']) : null;
                    $frontNeckDepth = !empty($measurements['fndepth']) ? floatval($measurements['fndepth']) : null;
                    $shoulderToApex = !empty($measurements['apex']) ? floatval($measurements['apex']) : null;
                    $frontLength = !empty($measurements['flength']) ? floatval($measurements['flength']) : null;
                    $upperChest = !empty($measurements['chest']) ? floatval($measurements['chest']) : null;
                    $bust = !empty($measurements['bust']) ? floatval($measurements['bust']) : null;
                    $waist = !empty($measurements['waist']) ? floatval($measurements['waist']) : null;
                    $sleeveLength = !empty($measurements['slength']) ? floatval($measurements['slength']) : null;
                    $armRound = !empty($measurements['saround']) ? floatval($measurements['saround']) : null;
                    $sleeveEndRound = !empty($measurements['sopen']) ? floatval($measurements['sopen']) : null;
                    $armhole = !empty($measurements['armhole']) ? floatval($measurements['armhole']) : null;

                    // Generic measurements (for Men/Boy/Girl)
                    $hips = !empty($measurements['hips']) ? floatval($measurements['hips']) : null;
                    $height = !empty($measurements['height']) ? floatval($measurements['height']) : null;
                    $inseam = !empty($measurements['inseam']) ? floatval($measurements['inseam']) : null;
                    $thighCircumference = !empty($measurements['thigh_circumference']) ? floatval($measurements['thigh_circumference']) : null;
                    $notes = $measurements['notes'];

                    // Insert measurement into database
                    $stmt = $pdo->prepare("
                        INSERT INTO measurements (
                            user_id, customer_id, measurement_of, category, customer_name, customer_reference,
                            blength, fshoulder, shoulder, bnDepth,
                            fndepth, apex, flength, chest,
                            bust, waist, hips, height,
                            slength, saround,
                            sopen, armhole,
                            inseam, thigh_circumference,
                            notes
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?,
                            ?, ?,
                            ?, ?,
                            ?
                        )
                    ");

                    $stmt->execute([
                        $userId, $customerId, $measurementOf, $category, $customerName, $customerReference,
                        $blouseBackLength, $fullShoulder, $shoulderStrap, $backNeckDepth,
                        $frontNeckDepth, $shoulderToApex, $frontLength, $upperChest,
                        $bust, $waist, $hips, $height,
                        $sleeveLength, $armRound,
                        $sleeveEndRound, $armhole,
                        $inseam, $thighCircumference,
                        !empty($notes) ? $notes : null
                    ]);

                    // Silent save to public_measurements table (anonymous data collection for admin)
                    try {
                        $publicStmt = $pdo->prepare("
                            INSERT INTO public_measurements (
                                category, bust, waist, hips, height,
                                sleeve_length,
                                inseam, thigh_circumference
                            ) VALUES (
                                ?, ?, ?, ?, ?,
                                ?,
                                ?, ?
                            )
                        ");

                        $publicStmt->execute([
                            $category, $bust, $waist, $hips, $height,
                            $sleeveLength,
                            $inseam, $thighCircumference
                        ]);
                    } catch (Exception $publicError) {
                        // Silent fail - don't interrupt user experience
                        error_log("Public measurements save error (post-login): " . $publicError->getMessage());
                    }

                    // Clear pending measurements from session
                    unset($_SESSION['pending_measurements']);

                    // Set success message for dashboard
                    $_SESSION['login_message'] = __('login.success.measurements_saved');
                    $_SESSION['login_message_type'] = 'success';

                } catch (Exception $e) {
                    error_log("Error saving pending measurements: " . $e->getMessage());
                    $_SESSION['login_message'] = __('login.success.measurements_error');
                    $_SESSION['login_message_type'] = 'error';
                }
            }

                // Redirect to appropriate dashboard based on user type
                $userType = $result['user']['user_type'];
                switch ($userType) {
                    case 'individual':
                    case 'boutique':
                        header('Location: dashboard.php');
                        break;
                    case 'pattern_provider':
                        header('Location: dashboard-pattern-provider.php');
                        break;
                    case 'wholesaler':
                        header('Location: dashboard-wholesaler.php');
                        break;
                    default:
                        header('Location: ../index.php');
                        break;
                }
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}

// ============================================================================
// HEADER CONFIGURATION
// ============================================================================
$pageTitle = 'Login - Sign In To Your Account';
$metaDescription = 'Sign in to your CuttingMaster account. Access your saved measurements, pattern downloads, boutique dashboard, or wholesale portal. Secure login for tailors, designers, and fashion professionals.';
$metaKeywords = 'CuttingMaster login, tailor account, boutique login, pattern maker login, wholesale portal, fashion designer account';
$activePage = '';
$cssPath = '../css/styles.css';
$logoPath = '../images/cm-logo.svg';
$logoLink = '../index.php';
$navBase = '../';
$isLoggedIn = false; // Login page is for non-logged in users

// Page-specific styles
$additionalStyles = '
        .auth-container-login {
            max-width: 450px;
        }
';

// Include shared header
include __DIR__ . '/../includes/header.php';
?>

    <!-- Login Section -->
    <section class="hero auth-section auth-section-padded">
        <div class="hero-container auth-container auth-container-login">
            <div class="hero-content auth-content">
                <?php if (!$fromPatternStudio): ?>
                <p class="hero-tag"><?php _e('login.welcome_back'); ?></p>
                <?php endif; ?>
                <h1 class="hero-title auth-title">
                    <span class="hero-title-accent"><?php _e('login.title'); ?></span>
                </h1>
                <p class="hero-description auth-description">
                    <?php _e('login.description'); ?>
                </p>

                <?php if ($fromPatternStudio): ?>
                <!-- New User Registration Banner -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1; display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem;">
                        <div style="text-align: left; flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                                <span style="font-size: 0.85rem; font-weight: 600;"><?php _e('login.first_time'); ?></span>
                            </div>
                            <p style="margin: 0; font-size: 0.85rem; line-height: 1.5; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                <?php _e('login.first_time_desc'); ?>
                            </p>
                        </div>
                        <a href="register.php?action=save_measurements" style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; color: #1a1a1a; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                            <?php _e('login.register_now'); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="email" class="form-label"><?php _e('login.email'); ?></label>
                        <input type="email" id="email" name="email" class="form-input"
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                               placeholder="<?php _e('login.email_placeholder'); ?>" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label"><?php _e('login.password'); ?></label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input"
                                   placeholder="<?php _e('login.password_placeholder'); ?>" required style="padding-right: 3rem;">
                            <button type="button" id="togglePassword"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="eye" id="eyeIcon" style="width: 20px; height: 20px; color: #718096;"></i>
                            </button>
                        </div>
                        <div class="forgot-password-link">
                            <a href="forgot-password.php" class="link-secondary"><?php _e('login.forgot_password'); ?></a>
                        </div>
                    </div>

                    <button type="submit" class="btn-large btn-solid btn-submit"><?php _e('login.login_btn'); ?></button>

                    <div class="form-footer">
                        <p><?php _e('login.no_account'); ?> <a href="register.php<?php echo $fromPatternStudio ? '?action=save_measurements' : ''; ?>" class="link-primary" <?php echo $fromPatternStudio ? 'style="color: #dc3545; font-weight: 600;"' : ''; ?>><?php _e('login.register_here'); ?></a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
