<?php
require_once __DIR__ . '/../config/database.php';

$username = 'venkataoruganti@yahoo.com';
$password = 'Bhargav';

global $pdo;
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Admin found: " . ($admin ? "Yes" : "No") . "\n";
if ($admin) {
    echo "Username from DB: " . $admin['username'] . "\n";
    echo "Password hash: " . $admin['password'] . "\n";
    echo "Password verify result: " . (password_verify($password, $admin['password']) ? "TRUE" : "FALSE") . "\n";
}
?>
