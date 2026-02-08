<?php
/**
 * Mimic User - Allows admin to impersonate a user
 * Stores original admin session so they can return
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Only allow access if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Get user ID to mimic
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    header('Location: dashboard-admin.php?error=invalid_user');
    exit;
}

try {
    // Fetch the user to mimic
    $stmt = $pdo->prepare("
        SELECT id, username, email, user_type, business_name, business_location,
               mobile_number, status, created_at, last_login
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $userToMimic = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userToMimic) {
        header('Location: dashboard-admin.php?error=user_not_found');
        exit;
    }

    // Store original admin session data for later restoration
    $_SESSION['original_admin'] = [
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'is_admin' => $_SESSION['is_admin'],
        'username' => $_SESSION['username'],
        'user_id' => $_SESSION['user_id'] ?? null
    ];

    // Set mimic flag
    $_SESSION['is_mimicking'] = true;
    $_SESSION['mimicked_user_id'] = $userToMimic['id'];
    $_SESSION['mimicked_username'] = $userToMimic['username'];

    // Switch session to the mimicked user
    $_SESSION['user_id'] = $userToMimic['id'];
    $_SESSION['username'] = $userToMimic['username'];
    $_SESSION['email'] = $userToMimic['email'];
    $_SESSION['user_type'] = $userToMimic['user_type'];
    $_SESSION['is_admin'] = false;

    // Redirect to appropriate dashboard based on user type
    $dashboards = [
        'individual' => 'dashboard.php',
        'boutique' => 'dashboard.php',
        'pattern_provider' => 'dashboard-pattern-provider.php',
        'wholesaler' => 'dashboard-wholesaler.php'
    ];

    $redirectTo = $dashboards[$userToMimic['user_type']] ?? 'dashboard.php';

    header('Location: ' . $redirectTo);
    exit;

} catch (PDOException $e) {
    error_log("Error mimicking user: " . $e->getMessage());
    header('Location: dashboard-admin.php?error=database_error');
    exit;
}
