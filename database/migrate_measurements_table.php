<?php
/**
 * Migration script for measurements table
 * This script creates the measurements table in the database
 */

$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'Kris@1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to database\n\n";

    // Read the measurements schema file
    $schemaFile = __DIR__ . '/measurements_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $sql = file_get_contents($schemaFile);

    echo "📋 Executing measurements table migration...\n";
    echo "==========================================\n\n";

    // Execute the schema
    $pdo->exec($sql);

    echo "✅ Measurements table created successfully!\n\n";

    // Show table structure
    echo "📊 Table Structure:\n";
    echo "==================\n";
    $stmt = $pdo->query("DESCRIBE measurements");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo sprintf(
            "%-25s %-20s %-10s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key']
        );
    }

    echo "\n✅ Migration completed successfully!\n";

} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
