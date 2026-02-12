<?php
/**
 * AJAX Session Check Endpoint
 * Returns JSON indicating if user session is still valid
 * Used by frontend JavaScript to detect session expiration
 */

header('Content-Type: application/json');

// Include session config
require_once __DIR__ . '/../config/session.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'valid' => false,
        'reason' => 'not_logged_in'
    ]);
    exit;
}

// Check for session timeout based on last activity
if (isset($_SESSION['last_activity'])) {
    $inactive_time = time() - $_SESSION['last_activity'];

    if ($inactive_time > SESSION_TIMEOUT) {
        // Session has timed out - destroy it
        session_unset();
        session_destroy();

        echo json_encode([
            'valid' => false,
            'reason' => 'timeout',
            'inactive_seconds' => $inactive_time
        ]);
        exit;
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Session is valid
echo json_encode([
    'valid' => true,
    'user_id' => $_SESSION['user_id'],
    'remaining_seconds' => SESSION_TIMEOUT - (isset($_SESSION['last_activity']) ? (time() - $_SESSION['last_activity']) : 0)
]);
