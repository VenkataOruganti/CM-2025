<?php
/**
 * Database Setup Script for Boutique Feature
 *
 * This script executes the SQL commands to create the customers table
 * and add customer_id column to the measurements table.
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

try {
    // Read the SQL file
    $sqlFile = __DIR__ . '/setup_boutique_tables.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }

    echo "Starting database setup...\n";
    echo str_repeat("=", 50) . "\n";

    // Execute the SQL commands
    // Note: PDO exec() can only execute one statement at a time
    // So we'll use multi_query approach by creating a new mysqli connection

    // Database configuration (from config/database.php)
    $db_host = 'localhost';
    $db_name = 'cm';
    $db_username = 'root';
    $db_password = 'Kris@1234';

    // Create mysqli connection for multi-query support
    $mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    echo "Connected to database: $db_name\n";
    echo str_repeat("-", 50) . "\n";

    // Execute multi-query
    if ($mysqli->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    foreach ($row as $key => $value) {
                        echo "$key: $value\n";
                    }
                }
                $result->free();
            }

            // Check for errors
            if ($mysqli->errno) {
                echo "Error: " . $mysqli->error . "\n";
            }

        } while ($mysqli->more_results() && $mysqli->next_result());
    } else {
        throw new Exception("Error executing SQL: " . $mysqli->error);
    }

    echo str_repeat("=", 50) . "\n";
    echo "✓ Database setup completed successfully!\n";
    echo "\nTables created/updated:\n";
    echo "  - customers\n";
    echo "  - measurements (added customer_id column)\n";
    echo "\nYou can now use the Boutique feature.\n";

    $mysqli->close();

} catch (Exception $e) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo str_repeat("=", 50) . "\n";
    exit(1);
}
