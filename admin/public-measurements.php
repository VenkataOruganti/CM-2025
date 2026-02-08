<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$adminUsername = $_SESSION['admin_username'];

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM public_measurements ORDER BY created_at DESC");
    $measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="public_measurements_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add headers
    if (!empty($measurements)) {
        fputcsv($output, array_keys($measurements[0]));
        foreach ($measurements as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

// Filtering
$category = $_GET['category'] ?? 'all';
$limit = intval($_GET['limit'] ?? 50);
$page = intval($_GET['page'] ?? 1);
$offset = ($page - 1) * $limit;

// Build query
global $pdo;
$whereClause = '';
$params = [];

if ($category !== 'all') {
    $whereClause = 'WHERE category = ?';
    $params[] = $category;
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM public_measurements $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get paginated data
$query = "SELECT * FROM public_measurements $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$measurements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category counts
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM public_measurements GROUP BY category");
$categoryCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Measurements - Admin - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h2 class="admin-logo">CuttingMaster</h2>
            <p class="admin-logo-subtitle">Admin Panel</p>
        </div>

        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-link">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="public-measurements.php" class="admin-nav-link active">
                <i data-lucide="ruler"></i>
                <span>Public Measurements</span>
            </a>
            <a href="../index.php" class="admin-nav-link" target="_blank">
                <i data-lucide="external-link"></i>
                <span>View Website</span>
            </a>
            <a href="logout.php" class="admin-nav-link admin-nav-logout">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <i data-lucide="user-circle"></i>
                <span><?php echo htmlspecialchars($adminUsername); ?></span>
            </div>
        </div>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1 class="admin-page-title">Public Measurements</h1>
        </header>

        <div class="admin-content">
            <!-- Filters and Export -->
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="admin-card-title" style="margin: 0;">Filter & Export</h2>
                    <a href="?export=csv&category=<?php echo urlencode($category); ?>" class="admin-action-button" style="display: inline-flex; padding: 0.625rem 1.25rem;">
                        <i data-lucide="download"></i>
                        <span>Export CSV</span>
                    </a>
                </div>

                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Category</label>
                        <select class="admin-filter-select" onchange="window.location.href='?category=' + this.value + '&limit=<?php echo $limit; ?>'">
                            <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories (<?php echo $totalRecords; ?>)</option>
                            <option value="women" <?php echo $category === 'women' ? 'selected' : ''; ?>>Women (<?php echo $categoryCounts['women'] ?? 0; ?>)</option>
                            <option value="men" <?php echo $category === 'men' ? 'selected' : ''; ?>>Men (<?php echo $categoryCounts['men'] ?? 0; ?>)</option>
                            <option value="boy" <?php echo $category === 'boy' ? 'selected' : ''; ?>>Boy (<?php echo $categoryCounts['boy'] ?? 0; ?>)</option>
                            <option value="girl" <?php echo $category === 'girl' ? 'selected' : ''; ?>>Girl (<?php echo $categoryCounts['girl'] ?? 0; ?>)</option>
                        </select>
                    </div>

                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Records per page</label>
                        <select class="admin-filter-select" onchange="window.location.href='?category=<?php echo $category; ?>&limit=' + this.value">
                            <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                            <option value="250" <?php echo $limit === 250 ? 'selected' : ''; ?>>250</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Measurements Table -->
            <div class="admin-table-container">
                <?php if (empty($measurements)): ?>
                    <div style="padding: 3rem; text-align: center; color: #718096;">
                        <i data-lucide="database" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>No measurements found.</p>
                    </div>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Bust</th>
                            <th>Waist</th>
                            <th>Hips</th>
                            <th>Height</th>
                            <th>Shoulder</th>
                            <th>Sleeve</th>
                            <th>Arm</th>
                            <th>Inseam</th>
                            <th>Thigh</th>
                            <th>Neck</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($measurements as $m): ?>
                        <tr>
                            <td><?php echo $m['id']; ?></td>
                            <td>
                                <span class="admin-table-badge admin-table-badge-<?php echo $m['category']; ?>">
                                    <?php echo ucfirst($m['category']); ?>
                                </span>
                            </td>
                            <td><?php echo $m['bust']; ?>"</td>
                            <td><?php echo $m['waist']; ?>"</td>
                            <td><?php echo $m['hips']; ?>"</td>
                            <td><?php echo $m['height']; ?>"</td>
                            <td><?php echo $m['shoulder_width'] ?? '-'; ?></td>
                            <td><?php echo $m['sleeve_length'] ?? '-'; ?></td>
                            <td><?php echo $m['arm_circumference'] ?? '-'; ?></td>
                            <td><?php echo $m['inseam'] ?? '-'; ?></td>
                            <td><?php echo $m['thigh_circumference'] ?? '-'; ?></td>
                            <td><?php echo $m['neck_circumference'] ?? '-'; ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="admin-card" style="margin-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: #718096; margin: 0;">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?> records
                    </p>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                        <a href="?category=<?php echo $category; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>"
                           class="admin-action-button" style="padding: 0.5rem 1rem;">
                            <i data-lucide="chevron-left"></i>
                            <span>Previous</span>
                        </a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="?category=<?php echo $category; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>"
                           class="admin-action-button" style="padding: 0.5rem 1rem;">
                            <span>Next</span>
                            <i data-lucide="chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
