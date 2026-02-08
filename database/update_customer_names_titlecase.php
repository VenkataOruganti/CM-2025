<?php
/**
 * Script to update existing customer names to Title Case
 * Run this script once to normalize all existing customer names in the database
 *
 * Usage: php update_customer_names_titlecase.php
 */

require_once __DIR__ . '/../config/database.php';

echo "Starting customer name normalization...\n\n";

try {
    global $pdo;

    // Update customers table
    echo "Updating 'customers' table...\n";
    $stmt = $pdo->query("SELECT id, customer_name FROM customers WHERE customer_name IS NOT NULL");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $customersUpdated = 0;
    foreach ($customers as $customer) {
        $originalName = $customer['customer_name'];
        $titleCaseName = ucwords(strtolower(trim($originalName)));

        if ($originalName !== $titleCaseName) {
            $updateStmt = $pdo->prepare("UPDATE customers SET customer_name = ? WHERE id = ?");
            $updateStmt->execute([$titleCaseName, $customer['id']]);
            $customersUpdated++;
            echo "  Updated: '{$originalName}' -> '{$titleCaseName}'\n";
        }
    }
    echo "Customers table: {$customersUpdated} records updated out of " . count($customers) . " total.\n\n";

    // Update measurements table
    echo "Updating 'measurements' table...\n";
    $stmt = $pdo->query("SELECT id, customer_name FROM measurements WHERE customer_name IS NOT NULL");
    $measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $measurementsUpdated = 0;
    foreach ($measurements as $measurement) {
        $originalName = $measurement['customer_name'];
        $titleCaseName = ucwords(strtolower(trim($originalName)));

        if ($originalName !== $titleCaseName) {
            $updateStmt = $pdo->prepare("UPDATE measurements SET customer_name = ? WHERE id = ?");
            $updateStmt->execute([$titleCaseName, $measurement['id']]);
            $measurementsUpdated++;
            echo "  Updated: '{$originalName}' -> '{$titleCaseName}'\n";
        }
    }
    echo "Measurements table: {$measurementsUpdated} records updated out of " . count($measurements) . " total.\n\n";

    echo "Done! Total updates:\n";
    echo "  - Customers: {$customersUpdated}\n";
    echo "  - Measurements: {$measurementsUpdated}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
