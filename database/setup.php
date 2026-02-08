<?php
/**
 * Database Setup Script
 * Run this file once to create the users table
 */

$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'Kris@1234';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to MySQL server\n";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$dbname' created or already exists\n";

    // Select the database
    $pdo->exec("USE $dbname");
    echo "✅ Using database '$dbname'\n";

    // Create users table
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('individual', 'business', 'wholesaler', 'pattern_designer') NOT NULL,
        business_name VARCHAR(255) DEFAULT NULL,
        business_location VARCHAR(255) DEFAULT NULL,
        mobile_number VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username),
        INDEX idx_user_type (user_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✅ Table 'users' created successfully\n";

    // Verify table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n📋 Table Structure:\n";
    echo "==================\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }

    echo "\n✅ Database setup completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Register new users at: http://localhost/CM-2025/pages/register.php\n";
    echo "2. Login at: http://localhost/CM-2025/pages/login.php\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
