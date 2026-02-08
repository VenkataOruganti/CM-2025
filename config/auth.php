<?php
// Authentication helper functions with MySQL

// Include session configuration (must be before any session_start())
require_once __DIR__ . '/session.php';

// Include database connection
require_once __DIR__ . '/database.php';

function registerUser($username, $email, $password, $userType = 'individual', $businessName = null, $businessLocation = null, $mobileNumber = null) {
    global $pdo;

    try {
        // Validate user type
        $validUserTypes = ['individual', 'boutique', 'pattern_provider', 'wholesaler'];
        if (!in_array($userType, $validUserTypes)) {
            return ['success' => false, 'message' => 'Invalid user type'];
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, user_type, business_name, business_location, mobile_number, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        $stmt->execute([
            $username,
            $email,
            $hashedPassword,
            $userType,
            $businessName,
            $businessLocation,
            $mobileNumber
        ]);

        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $pdo->lastInsertId()];

    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function loginUser($email, $password) {
    global $pdo;

    try {
        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Your account is ' . $user['status']];
            }

            // Update last login timestamp
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type'],
                    'business_name' => $user['business_name'],
                    'business_location' => $user['business_location'],
                    'mobile_number' => $user['mobile_number'],
                    'status' => $user['status']
                ]
            ];
        }

        return ['success' => false, 'message' => 'Invalid email or password'];

    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    global $pdo;

    if (isLoggedIn()) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, user_type, business_name, business_location, mobile_number, status, created_at, last_login FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    return null;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}


