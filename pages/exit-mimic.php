<?php
/**
 * Exit Mimic - Returns admin to their original session
 */
session_start();

// Check if user is currently mimicking
if (!isset($_SESSION['is_mimicking']) || $_SESSION['is_mimicking'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if original admin data exists
if (!isset($_SESSION['original_admin'])) {
    // Something went wrong, clear session and redirect to login
    session_destroy();
    header('Location: login.php');
    exit;
}

// Restore original admin session
$originalAdmin = $_SESSION['original_admin'];

// Clear mimic-related session data
unset($_SESSION['is_mimicking']);
unset($_SESSION['mimicked_user_id']);
unset($_SESSION['mimicked_username']);
unset($_SESSION['original_admin']);

// Restore admin session
$_SESSION['admin_id'] = $originalAdmin['admin_id'];
$_SESSION['is_admin'] = $originalAdmin['is_admin'];
$_SESSION['username'] = $originalAdmin['username'];
$_SESSION['user_id'] = $originalAdmin['user_id'];

// Set flash message for mimic ended
$_SESSION['mimic_ended_message'] = true;

// Redirect back to admin dashboard
header('Location: dashboard-admin.php');
exit;
