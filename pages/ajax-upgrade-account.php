<?php
/**
 * AJAX Handler: Upgrade Individual Account to Boutique
 *
 * This endpoint upgrades an individual user account to a boutique account.
 * It updates the user_type field in the users table from 'individual' to 'boutique'.
 */

session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    global $pdo;
    $userId = $_SESSION['user_id'];

    // Get current user
    $stmt = $pdo->prepare("SELECT id, user_type, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    // Check if already boutique
    if ($user['user_type'] === 'boutique') {
        echo json_encode(['success' => false, 'error' => 'Account is already a Boutique account']);
        exit;
    }

    // Check if individual (only individual can upgrade)
    if ($user['user_type'] !== 'individual') {
        echo json_encode(['success' => false, 'error' => 'Only Individual accounts can be upgraded']);
        exit;
    }

    // Update user type to boutique
    $updateStmt = $pdo->prepare("UPDATE users SET user_type = 'boutique', updated_at = NOW() WHERE id = ?");
    $result = $updateStmt->execute([$userId]);

    if ($result) {
        // Update session
        $_SESSION['user_type'] = 'boutique';

        // Log the upgrade
        error_log("User {$user['username']} (ID: $userId) upgraded from individual to boutique");

        echo json_encode([
            'success' => true,
            'message' => 'Your account has been upgraded to Boutique! You now have access to customer management and more features.',
            'new_type' => 'boutique'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upgrade account. Please try again.']);
    }

} catch (PDOException $e) {
    error_log("Account upgrade error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
