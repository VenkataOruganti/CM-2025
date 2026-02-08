<?php
/**
 * Session Configuration
 * This file configures secure session settings and can be safely included multiple times
 * Automatically detects local vs production environment
 */

// Only configure and start if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {

    // Detect if running on HTTPS (production) or HTTP (local development)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    // Security settings for session cookies
    ini_set('session.cookie_secure', $isHttps ? 1 : 0);  // Only require secure on HTTPS
    ini_set('session.cookie_httponly', 1);     // Prevent JavaScript access
    ini_set('session.cookie_samesite', 'Lax'); // Prevent CSRF attacks
    ini_set('session.use_strict_mode', 1);     // Reject uninitialized session IDs

    // Session lifetime (24 hours)
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 0);     // Session cookie (expires on browser close)

    // Use only cookies (no URL-based sessions)
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
}
