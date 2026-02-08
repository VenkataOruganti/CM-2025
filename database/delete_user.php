<?php
/**
 * Delete specific user from database
 */

$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'Kris@1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to database\n\n";

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['o_sivakumari@yahoo.com']);
    $user = $stmt->fetch();

    if ($user) {
        echo "📋 User found:\n";
        echo "==================\n";
        echo "ID: {$user['id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "User Type: {$user['user_type']}\n";
        echo "Created: {$user['created_at']}\n\n";

        // Delete the user
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $deleteStmt->execute(['o_sivakumari@yahoo.com']);

        echo "✅ User 'o_sivakumari@yahoo.com' has been deleted successfully!\n";
    } else {
        echo "⚠️  User 'o_sivakumari@yahoo.com' not found in the database.\n";
    }

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
