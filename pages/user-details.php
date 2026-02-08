<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    header('Location: dashboard-admin.php');
    exit;
}

// Handle form submission for user updates
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    try {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $userType = $_POST['user_type'] ?? '';
        $status = $_POST['status'] ?? '';
        $businessName = trim($_POST['business_name'] ?? '');
        $businessLocation = trim($_POST['business_location'] ?? '');
        $mobileNumber = trim($_POST['mobile_number'] ?? '');

        // Validation
        if (empty($username) || empty($email)) {
            throw new Exception('Username and email are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check if email is already used by another user
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->execute([$email, $userId]);
        if ($checkStmt->fetch()) {
            throw new Exception('Email is already in use by another user');
        }

        // Update user
        $updateStmt = $pdo->prepare("
            UPDATE users
            SET username = ?, email = ?, user_type = ?, status = ?,
                business_name = ?, business_location = ?, mobile_number = ?
            WHERE id = ?
        ");

        $updateStmt->execute([
            $username,
            $email,
            $userType,
            $status,
            !empty($businessName) ? $businessName : null,
            !empty($businessLocation) ? $businessLocation : null,
            !empty($mobileNumber) ? $mobileNumber : null,
            $userId
        ]);

        $message = 'User details updated successfully!';
        $messageType = 'success';

    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch user details including password
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, user_type, business_name,
               business_location, mobile_number, status, created_at, last_login, updated_at
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: dashboard-admin.php');
        exit;
    }

    // Fetch user's measurements
    $measStmt = $pdo->prepare("
        SELECT id, measurement_of, category, customer_name, customer_reference,
               blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest,
               bust, waist, hips, height, shoulder_width, slength, arm_circumference,
               saround, sopen, armhole, inseam, thigh_circumference, neck_circumference,
               notes, created_at, updated_at
        FROM measurements
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $measStmt->execute([$userId]);
    $measurements = $measStmt->fetchAll();

} catch(PDOException $e) {
    error_log("Error fetching user details: " . $e->getMessage());
    header('Location: dashboard-admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4299E1;
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .back-link:hover {
            color: #3182CE;
        }

        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E2E8F0;
        }

        .page-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: #2D3748;
            margin-bottom: 0.5rem;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .info-card h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            color: #2D3748;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            color: #2D3748;
            word-break: break-all;
        }

        .password-field {
            background-color: #F7FAFC;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        .user-type-badge {
            display: inline-block;
            padding: 0.375rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-type-badge.individual {
            background-color: #E6FFFA;
            color: #047857;
        }

        .user-type-badge.boutique {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .user-type-badge.pattern_provider {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .user-type-badge.wholesaler {
            background-color: #FCE7F3;
            color: #9F1239;
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge.active {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-badge.inactive {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-badge.suspended {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .measurements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .measurements-table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #4A5568;
            background-color: #F7FAFC;
            border-bottom: 2px solid #E2E8F0;
        }

        .measurements-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            color: #2D3748;
            font-size: 0.875rem;
        }

        .measurements-table tbody tr:hover {
            background-color: #F7FAFC;
        }

        .no-data {
            padding: 2rem;
            text-align: center;
            color: #718096;
        }

        .measurement-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #F7FAFC;
            border-radius: 8px;
        }

        .measurement-item {
            font-size: 0.875rem;
        }

        .measurement-item strong {
            color: #4A5568;
            display: block;
            margin-bottom: 0.25rem;
        }

        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #4299E1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .edit-btn:hover {
            background-color: #3182CE;
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #718096;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-left: 0.5rem;
        }

        .cancel-btn:hover {
            background-color: #4A5568;
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #48BB78;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .save-btn:hover {
            background-color: #38A169;
        }

        .form-input-edit {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            font-size: 1rem;
            color: #2D3748;
        }

        .form-input-edit:focus {
            outline: none;
            border-color: #4299E1;
        }

        .form-select-edit {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            font-size: 1rem;
            color: #2D3748;
            background-color: white;
        }

        .form-select-edit:focus {
            outline: none;
            border-color: #4299E1;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert.success {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .alert.error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        #viewMode, #editMode {
            transition: opacity 0.2s;
        }
    </style>
</head>
<body>
    <!-- Background Glow Effects -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <div class="admin-container">
        <a href="dashboard-admin.php" class="back-link">
            <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
            Back to Dashboard
        </a>

        <div class="page-header">
            <h1>User Details</h1>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- User Information Card -->
        <div class="info-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #E2E8F0;">
                <h2 style="margin: 0;">Account Information</h2>
                <button id="editBtn" class="edit-btn" onclick="toggleEditMode()" <?php echo (isset($_GET['edit']) && $_GET['edit'] == 1) ? 'style="display: none;"' : ''; ?>>
                    <i data-lucide="edit-2" style="width: 16px; height: 16px;"></i>
                    Edit User
                </button>
            </div>

            <!-- View Mode -->
            <div id="viewMode" <?php echo (isset($_GET['edit']) && $_GET['edit'] == 1) ? 'style="display: none;"' : ''; ?>>
                <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">User ID</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['id']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">User Type</div>
                    <div class="info-value">
                        <span class="user-type-badge <?php echo $user['user_type']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge <?php echo $user['status']; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mobile Number</div>
                    <div class="info-value"><?php echo $user['mobile_number'] ? htmlspecialchars($user['mobile_number']) : 'Not provided'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Business Name</div>
                    <div class="info-value"><?php echo $user['business_name'] ? htmlspecialchars($user['business_name']) : 'Not provided'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Business Location</div>
                    <div class="info-value"><?php echo $user['business_location'] ? htmlspecialchars($user['business_location']) : 'Not provided'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Registered</div>
                    <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Login</div>
                    <div class="info-value"><?php echo $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'Never logged in'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['updated_at'])); ?></div>
                </div>
                </div>
            </div>

            <!-- Edit Mode -->
            <div id="editMode" <?php echo (isset($_GET['edit']) && $_GET['edit'] == 1) ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                <form method="POST" action="">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">User ID</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['id']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Username *</div>
                            <input type="text" name="username" class="form-input-edit" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email *</div>
                            <input type="email" name="email" class="form-input-edit" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="info-item">
                            <div class="info-label">User Type *</div>
                            <select name="user_type" class="form-select-edit" required>
                                <option value="individual" <?php echo $user['user_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
                                <option value="boutique" <?php echo $user['user_type'] === 'boutique' ? 'selected' : ''; ?>>Boutique</option>
                                <option value="pattern_provider" <?php echo $user['user_type'] === 'pattern_provider' ? 'selected' : ''; ?>>Pattern Provider</option>
                                <option value="wholesaler" <?php echo $user['user_type'] === 'wholesaler' ? 'selected' : ''; ?>>Wholesaler</option>
                            </select>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status *</div>
                            <select name="status" class="form-select-edit" required>
                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Mobile Number</div>
                            <input type="text" name="mobile_number" class="form-input-edit" value="<?php echo htmlspecialchars($user['mobile_number'] ?? ''); ?>">
                        </div>
                        <div class="info-item">
                            <div class="info-label">Business Name</div>
                            <input type="text" name="business_name" class="form-input-edit" value="<?php echo htmlspecialchars($user['business_name'] ?? ''); ?>">
                        </div>
                        <div class="info-item">
                            <div class="info-label">Business Location</div>
                            <input type="text" name="business_location" class="form-input-edit" value="<?php echo htmlspecialchars($user['business_location'] ?? ''); ?>">
                        </div>
                        <div class="info-item">
                            <div class="info-label">Registered</div>
                            <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Login</div>
                            <div class="info-value"><?php echo $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'Never logged in'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['updated_at'])); ?></div>
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #E2E8F0;">
                        <button type="submit" name="update_user" class="save-btn">
                            <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                            Save Changes
                        </button>
                        <button type="button" class="cancel-btn" onclick="toggleEditMode()">
                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Information Card -->
        <div class="info-card">
            <h2>Security Information</h2>
            <div class="info-item">
                <div class="info-label">Password Hash (Bcrypt)</div>
                <div class="password-field"><?php echo htmlspecialchars($user['password']); ?></div>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #718096;">
                    <strong>Note:</strong> This is the bcrypt hashed password. The original password cannot be retrieved from this hash.
                </p>
            </div>
        </div>

        <!-- Measurements Card -->
        <div class="info-card">
            <h2>Saved Measurements (<?php echo count($measurements); ?>)</h2>

            <?php if (empty($measurements)): ?>
                <div class="no-data">
                    <p>This user has not saved any measurements yet.</p>
                </div>
            <?php else: ?>
                <table class="measurements-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Customer Name</th>
                            <th>Reference</th>
                            <th>Created</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($measurements as $meas): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($meas['id']); ?></td>
                                <td><?php echo ucfirst($meas['measurement_of']); ?></td>
                                <td><?php echo ucfirst($meas['category']); ?></td>
                                <td><?php echo $meas['customer_name'] ? htmlspecialchars($meas['customer_name']) : '-'; ?></td>
                                <td><?php echo $meas['customer_reference'] ? htmlspecialchars($meas['customer_reference']) : '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($meas['created_at'])); ?></td>
                                <td>
                                    <button onclick="toggleDetails(<?php echo $meas['id']; ?>)" class="btn-view" style="border: none; cursor: pointer;">
                                        <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
                                        Show Details
                                    </button>
                                </td>
                            </tr>
                            <tr id="details-<?php echo $meas['id']; ?>" style="display: none;">
                                <td colspan="7">
                                    <div class="measurement-details">
                                        <?php if ($meas['category'] === 'women'): ?>
                                            <?php if ($meas['blength']): ?><div class="measurement-item"><strong>Blouse Back Length:</strong> <?php echo $meas['blength']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['fshoulder']): ?><div class="measurement-item"><strong>Full Shoulder:</strong> <?php echo $meas['fshoulder']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['shoulder']): ?><div class="measurement-item"><strong>Shoulder Strap:</strong> <?php echo $meas['shoulder']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['bnDepth']): ?><div class="measurement-item"><strong>Back Neck Depth:</strong> <?php echo $meas['bnDepth']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['fndepth']): ?><div class="measurement-item"><strong>Front Neck Depth:</strong> <?php echo $meas['fndepth']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['apex']): ?><div class="measurement-item"><strong>Shoulder to Apex:</strong> <?php echo $meas['apex']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['flength']): ?><div class="measurement-item"><strong>Front Length:</strong> <?php echo $meas['flength']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['chest']): ?><div class="measurement-item"><strong>Upper Chest:</strong> <?php echo $meas['chest']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['bust']): ?><div class="measurement-item"><strong>Bust:</strong> <?php echo $meas['bust']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['waist']): ?><div class="measurement-item"><strong>Waist:</strong> <?php echo $meas['waist']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['slength']): ?><div class="measurement-item"><strong>Sleeve Length:</strong> <?php echo $meas['slength']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['saround']): ?><div class="measurement-item"><strong>Arm Round:</strong> <?php echo $meas['saround']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['sopen']): ?><div class="measurement-item"><strong>Sleeve End Round:</strong> <?php echo $meas['sopen']; ?>"</div><?php endif; ?>
                                            <?php if ($meas['armhole']): ?><div class="measurement-item"><strong>Armhole:</strong> <?php echo $meas['armhole']; ?>"</div><?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($meas['hips']): ?><div class="measurement-item"><strong>Hips:</strong> <?php echo $meas['hips']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['height']): ?><div class="measurement-item"><strong>Height:</strong> <?php echo $meas['height']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['shoulder_width']): ?><div class="measurement-item"><strong>Shoulder Width:</strong> <?php echo $meas['shoulder_width']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['arm_circumference']): ?><div class="measurement-item"><strong>Arm Circumference:</strong> <?php echo $meas['arm_circumference']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['inseam']): ?><div class="measurement-item"><strong>Inseam:</strong> <?php echo $meas['inseam']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['thigh_circumference']): ?><div class="measurement-item"><strong>Thigh Circumference:</strong> <?php echo $meas['thigh_circumference']; ?>"</div><?php endif; ?>
                                        <?php if ($meas['neck_circumference']): ?><div class="measurement-item"><strong>Neck Circumference:</strong> <?php echo $meas['neck_circumference']; ?>"</div><?php endif; ?>

                                        <?php if ($meas['notes']): ?>
                                            <div class="measurement-item" style="grid-column: 1 / -1;">
                                                <strong>Notes:</strong> <?php echo htmlspecialchars($meas['notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });

        function toggleDetails(id) {
            const row = document.getElementById('details-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }

        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            const editBtn = document.getElementById('editBtn');

            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                editBtn.style.display = 'inline-flex';
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                editBtn.style.display = 'none';
            }

            // Reinitialize Lucide icons
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>
