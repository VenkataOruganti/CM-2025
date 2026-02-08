<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle user deletion
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        $deleteUserId = intval($_POST['user_id']);

        if ($deleteUserId <= 0) {
            throw new Exception('Invalid user ID');
        }

        // Start transaction for cascading delete
        $pdo->beginTransaction();

        // Step 1: Delete measurements created by this user (their own measurements)
        $deleteMeasurementsStmt = $pdo->prepare("DELETE FROM measurements WHERE user_id = ?");
        $deleteMeasurementsStmt->execute([$deleteUserId]);
        $deletedMeasurements = $deleteMeasurementsStmt->rowCount();

        // Step 2: Delete measurements linked to this boutique's customers
        // First get all customer IDs for this boutique
        $customerIdsStmt = $pdo->prepare("SELECT id FROM customers WHERE boutique_user_id = ?");
        $customerIdsStmt->execute([$deleteUserId]);
        $customerIds = $customerIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        $deletedCustomerMeasurements = 0;
        if (!empty($customerIds)) {
            $placeholders = str_repeat('?,', count($customerIds) - 1) . '?';
            $deleteCustomerMeasurementsStmt = $pdo->prepare("DELETE FROM measurements WHERE customer_id IN ($placeholders)");
            $deleteCustomerMeasurementsStmt->execute($customerIds);
            $deletedCustomerMeasurements = $deleteCustomerMeasurementsStmt->rowCount();
        }

        // Step 3: Delete customers created by this boutique user
        $deleteCustomersStmt = $pdo->prepare("DELETE FROM customers WHERE boutique_user_id = ?");
        $deleteCustomersStmt->execute([$deleteUserId]);
        $deletedCustomers = $deleteCustomersStmt->rowCount();

        // Step 4: Delete the user
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->execute([$deleteUserId]);

        // Commit transaction
        $pdo->commit();

        $message = 'User deleted successfully!';
        $messageType = 'success';

        // Redirect to prevent form resubmission
        header('Location: dashboard-admin.php?deleted=1');
        exit;

    } catch (Exception $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = 'Error deleting user: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for success messages from redirects
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $message = 'User deleted successfully!';
    $messageType = 'success';
}

// Check for mimic ended message (using session to prevent showing on refresh)
if (isset($_SESSION['mimic_ended_message']) && $_SESSION['mimic_ended_message'] === true) {
    $message = 'You have returned to your admin account.';
    $messageType = 'success';
    // Clear the session variable so it doesn't show on refresh
    unset($_SESSION['mimic_ended_message']);
}

// Check for mimic errors
if (isset($_GET['error'])) {
    $messageType = 'error';
    switch ($_GET['error']) {
        case 'invalid_user':
            $message = 'Invalid user ID provided.';
            break;
        case 'user_not_found':
            $message = 'User not found in the database.';
            break;
        case 'database_error':
            $message = 'A database error occurred. Please try again.';
            break;
        default:
            $message = 'An error occurred.';
    }
}

// Fetch all users from the database
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, user_type, business_name, business_location,
               mobile_number, status, created_at, last_login
        FROM users
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}

// Get total counts
try {
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $countStmt->fetch()['total'];

    $activeStmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $activeUsers = $activeStmt->fetch()['total'];

    $measurementsStmt = $pdo->query("SELECT COUNT(*) as total FROM measurements");
    $totalMeasurements = $measurementsStmt->fetch()['total'];

    $publicMeasurementsStmt = $pdo->query("SELECT COUNT(*) as total FROM public_measurements");
    $totalPublicMeasurements = $publicMeasurementsStmt->fetch()['total'];
} catch(PDOException $e) {
    error_log("Error fetching counts: " . $e->getMessage());
    $totalUsers = 0;
    $activeUsers = 0;
    $totalMeasurements = 0;
    $totalPublicMeasurements = 0;
}

// Get posting counts per user
$userPostingCounts = [];
try {
    // Boutique users - count of customers
    $boutiqueCountsStmt = $pdo->query("SELECT boutique_user_id, COUNT(*) as count FROM customers GROUP BY boutique_user_id");
    while ($row = $boutiqueCountsStmt->fetch(PDO::FETCH_ASSOC)) {
        $userPostingCounts[$row['boutique_user_id']] = $row['count'];
    }

    // Pattern providers - count of patterns uploaded
    $patternCountsStmt = $pdo->query("SELECT provider_id, COUNT(*) as count FROM pattern_making_portfolio WHERE provider_id IS NOT NULL GROUP BY provider_id");
    while ($row = $patternCountsStmt->fetch(PDO::FETCH_ASSOC)) {
        $userPostingCounts[$row['provider_id']] = $row['count'];
    }

    // Wholesalers - count of catalog items
    $wholesaleCountsStmt = $pdo->query("SELECT vendor_id, COUNT(*) as count FROM wholesale_portfolio WHERE vendor_id IS NOT NULL GROUP BY vendor_id");
    while ($row = $wholesaleCountsStmt->fetch(PDO::FETCH_ASSOC)) {
        $userPostingCounts[$row['vendor_id']] = $row['count'];
    }
} catch(PDOException $e) {
    error_log("Error fetching posting counts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="../css/admin-styles.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        /* Page-specific overrides */
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .stat-card {
            gap: 0rem;
        }

        .stat-card .stat-value {
            font-size: 3rem;
            min-width: 80px;
        }

        /* Dashboard-specific button styles */
        .btn-edit,
        .btn-delete {
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            font-size: 0.8125rem;
        }

        .btn-delete {
            background-color: #EF4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #DC2626;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        /* Ensure buttons are clickable */
        .users-table a,
        .users-table button {
            pointer-events: auto !important;
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <!-- Main Container -->
    <div class="admin-container">
        <div class="admin-header">
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <h1>Admin Dashboard</h1>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div id="alertMessage" class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <script>
                setTimeout(function() {
                    var alert = document.getElementById('alertMessage');
                    if (alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() { alert.remove(); }, 500);
                    }
                }, 3000);
            </script>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-card-info">
                    <h3>Total Users</h3>
                    <p>Registered accounts</p>
                </div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-value"><?php echo $activeUsers; ?></div>
                <div class="stat-card-info">
                    <h3>Active Users</h3>
                    <p>Currently active</p>
                </div>
            </div>
            <div class="stat-card tertiary">
                <div class="stat-value"><?php echo $totalMeasurements; ?></div>
                <div class="stat-card-info">
                    <h3>Saved Measurements</h3>
                    <p>From measurements table</p>
                    <a href="saved-measurements.php" style="display: inline-block; margin-top: 0.5rem; text-decoration: underline;">View Details</a>
                </div>
            </div>
            <div class="stat-card quaternary">
                <div class="stat-value"><?php echo $totalPublicMeasurements; ?></div>
                <div class="stat-card-info">
                    <h3>Public Measurements</h3>
                    <p>Anonymous data collection</p>
                    <a href="public-measurements.php" style="display: inline-block; margin-top: 0.5rem; text-decoration: underline;">View Details</a>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="users-table-container">
            <div class="table-header">
                <h2>All Users</h2>
                <div class="table-filter">
                    <label for="userTypeFilter">Filter by:</label>
                    <select id="userTypeFilter" onchange="filterUsers(this.value)">
                        <option value="all">All Users</option>
                        <option value="individual">Individual</option>
                        <option value="boutique">Boutique</option>
                        <option value="pattern_provider">Pattern Provider</option>
                        <option value="wholesaler">Wholesaler</option>
                    </select>
                </div>
            </div>
            <?php if (empty($users)): ?>
                <div class="no-data">No users found in the database.</div>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Business</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php foreach ($users as $user): ?>
                            <tr data-user-type="<?php echo $user['user_type']; ?>">
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                                    <a href="mimic-user.php?id=<?php echo $user['id']; ?>"
                                                       class="mimic-user-link"
                                                       title="Login as <?php echo htmlspecialchars($user['username']); ?>">
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                    </a><?php
                                                    $postingCount = $userPostingCounts[$user['id']] ?? 0;
                                                    if ($postingCount > 0 && in_array($user['user_type'], ['boutique', 'pattern_provider', 'wholesaler'])):
                                                    ?><span class="posting-count">(<?php echo $postingCount; ?>)</span><?php endif; ?>
                                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="user-type-badge <?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['business_name'] ? htmlspecialchars($user['business_name']) : '-'; ?></td>
                                <td><?php echo $user['mobile_number'] ? htmlspecialchars($user['mobile_number']) : '-'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" onclick="location.href='user-details.php?id=<?php echo $user['id']; ?>&edit=1'" class="btn-edit">
                                            <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                                            Edit
                                        </button>
                                        <button type="button" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>')" class="btn-delete">
                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <footer class="admin-footer">
            <p>&copy; <?php echo date('Y'); ?> CuttingMaster Admin Panel. All rights reserved.</p>
        </footer>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i data-lucide="alert-triangle" style="width: 24px; height: 24px; color: #EF4444;"></i>
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p style="color: #EF4444; font-weight: 500; margin-top: 1rem;">⚠️ This action cannot be undone!</p>
                <p style="margin-top: 0.75rem; font-size: 0.875rem; color: #4A5568;">The following data will be permanently deleted:</p>
                <ul style="margin-top: 0.5rem; margin-left: 1.5rem; font-size: 0.875rem; color: #4A5568; line-height: 1.6;">
                    <li>User account and profile information</li>
                    <li>All saved measurements created by this user</li>
                    <li>All customer records (for boutique users)</li>
                    <li>All measurements linked to their customers</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" name="delete_user" class="btn-modal-confirm">Delete User</button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Initialize Lucide icons
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });

        // Filter users by type
        function filterUsers(userType) {
            var rows = document.querySelectorAll('#usersTableBody tr');
            rows.forEach(function(row) {
                if (userType === 'all' || row.dataset.userType === userType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Delete confirmation modal functions
        function confirmDelete(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = username;
            document.getElementById('deleteModal').classList.add('active');

            // Reinitialize icons in modal
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
