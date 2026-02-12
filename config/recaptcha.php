<?php
/**
 * Google reCAPTCHA v3 Configuration
 *
 * To set up:
 * 1. Go to https://www.google.com/recaptcha/admin
 * 2. Register a new site with reCAPTCHA v3
 * 3. Add your domain(s): cuttingmaster.in, localhost (for testing)
 * 4. Copy the Site Key and Secret Key below
 *
 * reCAPTCHA v3 returns a score (0.0 - 1.0):
 * - 1.0 is very likely a good interaction
 * - 0.0 is very likely a bot
 * - Recommended threshold: 0.5
 */

// reCAPTCHA v3 Keys
define('RECAPTCHA_SITE_KEY', '6LdCK1csAAAAALo782BHJ5lDOVu51SwREhoBMy88');
define('RECAPTCHA_SECRET_KEY', '6LdCK1csAAAAAEVO2A7kkUU6oAPBbC0HuL5KTSw1');

// Score threshold (0.0 to 1.0) - submissions below this score are rejected
define('RECAPTCHA_THRESHOLD', 0.5);

// Enable/disable reCAPTCHA (useful for local development)
// TODO: Get new reCAPTCHA v3 keys for cuttingmaster.in from https://www.google.com/recaptcha/admin
define('RECAPTCHA_ENABLED', false);

/**
 * Verify reCAPTCHA v3 token
 *
 * @param string $token The reCAPTCHA token from the frontend
 * @param string $expectedAction The expected action name (e.g., 'register', 'contact')
 * @return array ['success' => bool, 'score' => float, 'error' => string|null]
 */
function verifyRecaptcha($token, $expectedAction = null) {
    // If reCAPTCHA is disabled, always return success
    if (!RECAPTCHA_ENABLED) {
        return ['success' => true, 'score' => 1.0, 'error' => null];
    }

    // Check if keys are configured
    if (RECAPTCHA_SECRET_KEY === 'YOUR_RECAPTCHA_SECRET_KEY_HERE') {
        error_log('reCAPTCHA: Secret key not configured');
        // Return success to not block users if not configured
        return ['success' => true, 'score' => 1.0, 'error' => 'Not configured'];
    }

    // Token is required
    if (empty($token)) {
        return ['success' => false, 'score' => 0, 'error' => 'No token provided'];
    }

    // Verify with Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        error_log('reCAPTCHA: Failed to connect to Google API');
        // Return success on network failure to not block legitimate users
        return ['success' => true, 'score' => 0.5, 'error' => 'Network error'];
    }

    $result = json_decode($response, true);

    if (!$result) {
        error_log('reCAPTCHA: Invalid JSON response');
        return ['success' => true, 'score' => 0.5, 'error' => 'Invalid response'];
    }

    // Check if verification was successful
    if (!isset($result['success']) || !$result['success']) {
        $errors = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : 'Unknown error';
        error_log("reCAPTCHA: Verification failed - $errors");
        return ['success' => false, 'score' => 0, 'error' => $errors];
    }

    // Get the score
    $score = isset($result['score']) ? floatval($result['score']) : 0;

    // Verify action matches (optional but recommended)
    if ($expectedAction !== null && isset($result['action']) && $result['action'] !== $expectedAction) {
        error_log("reCAPTCHA: Action mismatch - expected '$expectedAction', got '{$result['action']}'");
        return ['success' => false, 'score' => $score, 'error' => 'Action mismatch'];
    }

    // Check if score meets threshold
    if ($score < RECAPTCHA_THRESHOLD) {
        error_log("reCAPTCHA: Low score ($score) - likely bot");
        return ['success' => false, 'score' => $score, 'error' => 'Low score'];
    }

    return ['success' => true, 'score' => $score, 'error' => null];
}

/**
 * Get reCAPTCHA script tag for frontend
 *
 * @return string HTML script tag
 */
function getRecaptchaScript() {
    if (!RECAPTCHA_ENABLED || RECAPTCHA_SITE_KEY === 'YOUR_RECAPTCHA_SITE_KEY_HERE') {
        return '';
    }
    return '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars(RECAPTCHA_SITE_KEY) . '"></script>';
}

/**
 * Get reCAPTCHA site key for frontend JavaScript
 *
 * @return string The site key or empty string if not configured
 */
function getRecaptchaSiteKey() {
    if (!RECAPTCHA_ENABLED || RECAPTCHA_SITE_KEY === 'YOUR_RECAPTCHA_SITE_KEY_HERE') {
        return '';
    }
    return RECAPTCHA_SITE_KEY;
}
