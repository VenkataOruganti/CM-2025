<?php
/**
 * Debug Boutique Data - Check the state of customers and measurements
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Only allow admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die('Admin access required. Please login as admin first.');
}

global $pdo;

// Get all boutique users
$boutiqueUsers = $pdo->query("
    SELECT id, username, email, user_type
    FROM users
    WHERE user_type = 'boutique'
")->fetchAll(PDO::FETCH_ASSOC);

// Get all customers
$allCustomers = $pdo->query("
    SELECT c.*, u.username as boutique_username
    FROM customers c
    LEFT JOIN users u ON c.boutique_user_id = u.id
    ORDER BY c.boutique_user_id
")->fetchAll(PDO::FETCH_ASSOC);

// Get all measurements from boutique users
$boutiqueMeasurements = $pdo->query("
    SELECT m.id, m.user_id, m.customer_id, m.measurement_of, m.customer_name as m_customer_name,
           m.category, m.created_at,
           u.username, u.user_type
    FROM measurements m
    JOIN users u ON m.user_id = u.id
    WHERE u.user_type = 'boutique'
    ORDER BY m.user_id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Boutique Data</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        h1, h2 { color: #2D3748; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #E2E8F0; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #F7FAFC; }
        .section { background: #F9FAFB; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .count { font-weight: bold; color: #3B82F6; }
        .null { color: #EF4444; font-style: italic; }
        a { color: #3B82F6; }
    </style>
</head>
<body>
    <h1>Debug Boutique Data</h1>

    <div class="section">
        <h2>1. Boutique Users (<span class="count"><?php echo count($boutiqueUsers); ?></span>)</h2>
        <?php if (empty($boutiqueUsers)): ?>
            <p class="null">No boutique users found!</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Username</th><th>Email</th><th>User Type</th></tr>
                <?php foreach ($boutiqueUsers as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo $u['user_type']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>2. Customers Table (<span class="count"><?php echo count($allCustomers); ?></span>)</h2>
        <?php if (empty($allCustomers)): ?>
            <p class="null">No customers found in the customers table!</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Boutique User ID</th><th>Boutique Username</th><th>Customer Name</th><th>Reference</th><th>Created</th></tr>
                <?php foreach ($allCustomers as $c): ?>
                <tr>
                    <td><?php echo $c['id']; ?></td>
                    <td><?php echo $c['boutique_user_id']; ?></td>
                    <td><?php echo $c['boutique_username'] ? htmlspecialchars($c['boutique_username']) : '<span class="null">NULL</span>'; ?></td>
                    <td><?php echo htmlspecialchars($c['customer_name']); ?></td>
                    <td><?php echo $c['customer_reference'] ? htmlspecialchars($c['customer_reference']) : '-'; ?></td>
                    <td><?php echo $c['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>3. Measurements from Boutique Users (<span class="count"><?php echo count($boutiqueMeasurements); ?></span>)</h2>
        <?php if (empty($boutiqueMeasurements)): ?>
            <p class="null">No measurements found from boutique users!</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>User ID</th><th>Username</th><th>Customer ID</th><th>Measurement Of</th><th>Customer Name (in measurement)</th><th>Category</th></tr>
                <?php foreach ($boutiqueMeasurements as $m): ?>
                <tr>
                    <td><?php echo $m['id']; ?></td>
                    <td><?php echo $m['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($m['username']); ?></td>
                    <td><?php echo $m['customer_id'] ? $m['customer_id'] : '<span class="null">NULL</span>'; ?></td>
                    <td><?php echo $m['measurement_of']; ?></td>
                    <td><?php echo $m['m_customer_name'] ? htmlspecialchars($m['m_customer_name']) : '<span class="null">NULL</span>'; ?></td>
                    <td><?php echo $m['category']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>4. Summary</h2>
        <ul>
            <li>Boutique users: <strong><?php echo count($boutiqueUsers); ?></strong></li>
            <li>Customers in customers table: <strong><?php echo count($allCustomers); ?></strong></li>
            <li>Measurements from boutique users: <strong><?php echo count($boutiqueMeasurements); ?></strong></li>
            <li>Measurements with customer_id set: <strong><?php echo count(array_filter($boutiqueMeasurements, fn($m) => !empty($m['customer_id']))); ?></strong></li>
            <li>Measurements with customer_id NULL: <strong><?php echo count(array_filter($boutiqueMeasurements, fn($m) => empty($m['customer_id']))); ?></strong></li>
        </ul>
    </div>

    <div class="section">
        <h2>5. ID Comparison Check</h2>
        <?php
        // Check if boutique_user_id in customers matches any boutique user IDs
        $boutiqueUserIds = array_column($boutiqueUsers, 'id');
        $customerBoutiqueIds = array_unique(array_column($allCustomers, 'boutique_user_id'));

        $matchingIds = array_intersect($boutiqueUserIds, $customerBoutiqueIds);
        $orphanCustomerIds = array_diff($customerBoutiqueIds, $boutiqueUserIds);
        ?>
        <p><strong>Boutique User IDs:</strong> <?php echo implode(', ', $boutiqueUserIds) ?: 'None'; ?></p>
        <p><strong>boutique_user_id values in customers table:</strong> <?php echo implode(', ', $customerBoutiqueIds) ?: 'None'; ?></p>
        <p><strong>Matching IDs:</strong> <?php echo implode(', ', $matchingIds) ?: '<span class="null">None - THIS IS THE PROBLEM!</span>'; ?></p>
        <?php if (!empty($orphanCustomerIds)): ?>
        <p class="null"><strong>Orphan boutique_user_ids (not matching any boutique user):</strong> <?php echo implode(', ', $orphanCustomerIds); ?></p>
        <?php endif; ?>
    </div>

    <p><a href="dashboard-admin.php">Back to Admin Dashboard</a></p>
</body>
</html>
