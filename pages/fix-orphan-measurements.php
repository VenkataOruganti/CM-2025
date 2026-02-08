<?php
/**
 * Fix Orphan Measurements - Creates customer records for boutique measurements
 * that don't have linked customers
 *
 * This script:
 * 1. Finds all measurements from boutique users that have no customer_id
 * 2. Creates customer records for them (using customer_name or generating one)
 * 3. Links the measurements to the new customer records
 * 4. Converts "self" measurements to "customer" for boutique users
 *
 * Run this once as admin to fix existing data.
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Only allow admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die('Admin access required. Please login as admin first.');
}

$results = [];
$errors = [];
$action = $_GET['action'] ?? 'preview';

try {
    global $pdo;

    // Find ALL measurements from boutique users that don't have a customer_id
    $stmt = $pdo->prepare("
        SELECT m.id, m.user_id, m.customer_name, m.customer_reference, m.measurement_of,
               m.category, m.created_at, u.username, u.user_type
        FROM measurements m
        JOIN users u ON m.user_id = u.id
        WHERE u.user_type = 'boutique'
          AND (m.customer_id IS NULL OR m.customer_id = 0)
        ORDER BY m.user_id, m.created_at
    ");
    $stmt->execute();
    $orphanMeasurements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results['found'] = count($orphanMeasurements);
    $results['measurements'] = $orphanMeasurements;

    if ($action === 'fix' && count($orphanMeasurements) > 0) {
        $customersCreated = 0;
        $measurementsUpdated = 0;
        $customerCounter = [];

        foreach ($orphanMeasurements as $measurement) {
            $userId = $measurement['user_id'];
            $customerName = $measurement['customer_name'];

            // If no customer name, generate one based on category and count
            if (empty($customerName)) {
                if (!isset($customerCounter[$userId])) {
                    $customerCounter[$userId] = 1;
                }
                $customerName = 'Customer ' . $customerCounter[$userId];
                $customerCounter[$userId]++;
            }

            // Check if customer already exists for this boutique
            $checkStmt = $pdo->prepare("
                SELECT id FROM customers
                WHERE boutique_user_id = ? AND customer_name = ?
            ");
            $checkStmt->execute([$userId, $customerName]);
            $existingCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            } else {
                // Create new customer record
                $insertStmt = $pdo->prepare("
                    INSERT INTO customers (boutique_user_id, customer_name, customer_reference)
                    VALUES (?, ?, ?)
                ");
                $insertStmt->execute([
                    $userId,
                    $customerName,
                    $measurement['customer_reference']
                ]);
                $customerId = $pdo->lastInsertId();
                $customersCreated++;
            }

            // Update measurement with customer_id and change measurement_of to 'customer'
            $updateStmt = $pdo->prepare("
                UPDATE measurements
                SET customer_id = ?, measurement_of = 'customer', customer_name = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$customerId, $customerName, $measurement['id']]);
            $measurementsUpdated++;
        }

        $results['customers_created'] = $customersCreated;
        $results['measurements_updated'] = $measurementsUpdated;
        $results['fixed'] = true;
    }

} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Orphan Measurements</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        h1 { color: #2D3748; }
        .success { background: #D1FAE5; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #FEE2E2; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #DBEAFE; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #FEF3C7; padding: 15px; border-radius: 8px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #E2E8F0; padding: 10px; text-align: left; }
        th { background: #F7FAFC; }
        a { color: #3B82F6; }
        .btn-fix {
            display: inline-block;
            background: #10B981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn-fix:hover { background: #059669; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .badge-self { background: #E0E7FF; color: #3730A3; }
        .badge-customer { background: #D1FAE5; color: #065F46; }
    </style>
</head>
<body>
    <h1>Fix Orphan Measurements</h1>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($results['fixed']) && $results['fixed']): ?>
        <div class="success">
            <strong>Successfully Fixed!</strong><br><br>
            Customers created: <strong><?php echo $results['customers_created']; ?></strong><br>
            Measurements updated: <strong><?php echo $results['measurements_updated']; ?></strong>
        </div>
        <p>The boutique user's customer dropdown should now show the customers.</p>
        <p><a href="dashboard-admin.php">Back to Admin Dashboard</a></p>

    <?php elseif ($results['found'] > 0): ?>
        <div class="warning">
            <strong>Found <?php echo $results['found']; ?> boutique measurement(s) without customer links.</strong><br><br>
            These will be converted to customer measurements with auto-generated customer names.
        </div>

        <h3>Measurements to Fix:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Boutique User</th>
                <th>Current Type</th>
                <th>Customer Name</th>
                <th>Category</th>
                <th>Will Become</th>
            </tr>
            <?php foreach ($results['measurements'] as $m): ?>
            <tr>
                <td><?php echo $m['id']; ?></td>
                <td><?php echo htmlspecialchars($m['username']); ?></td>
                <td><span class="badge badge-<?php echo $m['measurement_of']; ?>"><?php echo $m['measurement_of']; ?></span></td>
                <td><?php echo $m['customer_name'] ? htmlspecialchars($m['customer_name']) : '<em>None (will generate)</em>'; ?></td>
                <td><?php echo ucfirst($m['category']); ?></td>
                <td><span class="badge badge-customer">customer</span></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <a href="?action=fix" class="btn-fix">Fix All Measurements</a>
        <p style="color: #718096; font-size: 14px;">This will create customer records and link them to the measurements.</p>

    <?php else: ?>
        <div class="success">
            <strong>All boutique measurements are properly linked!</strong><br>
            No orphan measurements found.
        </div>
    <?php endif; ?>

    <p><a href="dashboard-admin.php">Back to Admin Dashboard</a></p>
</body>
</html>
