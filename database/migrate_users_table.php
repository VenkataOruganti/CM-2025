<?php
/**
 * Migration Script - Update Users Table Structure
 * This script will migrate the existing users table to the new schema
 */

$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'Kris@1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to database\n";

    echo "\n📋 Migrating users table...\n";
    echo "==================\n\n";

    // Drop the old table (WARNING: This will delete all existing data)
    echo "⚠️  Dropping old users table...\n";
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "✅ Old table dropped\n\n";

    // Create new table with updated schema
    echo "📝 Creating new users table...\n";
    $sql = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('individual', 'boutique', 'pattern_provider', 'wholesaler') NOT NULL,
        business_name VARCHAR(255) DEFAULT NULL,
        business_location VARCHAR(255) DEFAULT NULL,
        mobile_number VARCHAR(20) DEFAULT NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_user_type (user_type),
        INDEX idx_email (email),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✅ New users table created successfully\n\n";

    // Verify table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "📋 New Table Structure:\n";
    echo "==================\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})";
        if ($column['Default'] !== null) {
            echo " [Default: {$column['Default']}]";
        }
        echo "\n";
    }

    echo "\n✅ Migration completed successfully!\n";
    echo "\nNew user types:\n";
    echo "- individual: Individual users\n";
    echo "- boutique: Tailors/Boutiques\n";
    echo "- pattern_provider: Pattern designers/providers\n";
    echo "- wholesaler: Garment wholesalers\n";

    echo "\nNew features:\n";
    echo "- status field: Track user account status (active, inactive, suspended)\n";
    echo "- last_login: Track when users last logged in\n";
    echo "- All original fields retained (username, business_name, business_location, mobile_number)\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
