<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $deleteId = intval($_POST['delete_id']);
        $deleteStmt = $pdo->prepare("DELETE FROM measurements WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
        exit;
    } catch(PDOException $e) {
        error_log("Error deleting measurement: " . $e->getMessage());
    }
}

// Pagination settings
$recordsPerPage = 20;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'women';

// Body section filter
$bodySectionFilter = isset($_GET['body_section']) ? $_GET['body_section'] : 'upper_body';

// Define which columns belong to upper body and lower body
$upperBodyColumns = ['blength', 'fshoulder', 'shoulder', 'bnDepth', 'fndepth', 'apex', 'flength', 'chest', 'bust', 'waist', 'slength', 'saround', 'sopen', 'armhole'];
$lowerBodyColumns = ['hips', 'height', 'inseam', 'thigh_circumference'];

// Determine which columns to show
$showColumn = function($column) use ($bodySectionFilter, $upperBodyColumns, $lowerBodyColumns) {
    if ($bodySectionFilter === 'all') return true;
    if ($bodySectionFilter === 'upper_body') return in_array($column, $upperBodyColumns);
    if ($bodySectionFilter === 'lower_body') return in_array($column, $lowerBodyColumns);
    return true;
};

// Fetch all saved measurements with user information and pagination
try {
    // Build WHERE clause for category filter (always filter by category)
    $whereClause = "WHERE m.category = :category";

    // Get total count
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM measurements m $whereClause");
    $totalStmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
    $totalStmt->execute();
    $totalRecords = $totalStmt->fetch()['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Fetch paginated measurements
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.email, c.customer_name, c.customer_reference
        FROM measurements m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN customers c ON m.customer_id = c.id
        $whereClause
        ORDER BY m.category, m.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $measurements = $stmt->fetchAll();

    // Get statistics
    $statsStmt = $pdo->query("
        SELECT
            category,
            COUNT(*) as count,
            AVG(bust) as avg_bust,
            AVG(waist) as avg_waist,
            AVG(hips) as avg_hips,
            AVG(height) as avg_height
        FROM measurements
        WHERE bust IS NOT NULL OR waist IS NOT NULL OR hips IS NOT NULL
        GROUP BY category
    ");
    $stats = $statsStmt->fetchAll();

} catch(PDOException $e) {
    error_log("Error fetching saved measurements: " . $e->getMessage());
    $measurements = [];
    $stats = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Measurements - Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="../css/admin-styles.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        /* Page-specific styles for measurements */
        .admin-container { max-width: 1600px; }

        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #2D3748; margin-bottom: 0.5rem; }
        .page-header p { color: #718096; font-size: 1rem; }

        .table-wrapper { overflow-x: auto; }

        .measurements-table { table-layout: fixed; }
        .measurements-table th {
            padding: 0.5rem 0.35rem;
            font-size: 0.7rem;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #E2E8F0;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.3;
            max-width: 80px;
        }
        .measurements-table th:first-child { max-width: 35px; width: 35px; }
        .measurements-table th:nth-child(2) { max-width: 70px; }
        .measurements-table td:first-child { max-width: 35px; width: 35px; font-size: 0.75rem; }
        .measurements-table td {
            padding: 0.5rem 0.35rem;
            font-size: 0.8rem;
            white-space: normal;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .category-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .category-badge.women { background-color: #FCE7F3; color: #9F1239; }
        .category-badge.men { background-color: #DBEAFE; color: #1E40AF; }
        .category-badge.boy { background-color: #D1FAE5; color: #065F46; }
        .category-badge.girl { background-color: #FEF3C7; color: #92400E; }

        .category-filter { display: flex; gap: 1rem; align-items: center; }
        .category-filter label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #4A5568; font-weight: 500; }
        .category-filter input[type="radio"] { cursor: pointer; width: 18px; height: 18px; accent-color: #4299E1; }

        .measurements-table .variable-name { font-size: 0.65rem; color: #718096; font-weight: 400; text-transform: none; letter-spacing: normal; display: block; margin-top: 0.15rem; word-break: break-all; }

        .btn-delete { background: none; border: none; color: #EF4444; cursor: pointer; padding: 0.25rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s; }
        .btn-delete:hover { background-color: #FEE2E2; color: #DC2626; }

        .pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 2rem 1.5rem; background: white; border-top: 1px solid #E2E8F0; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 0.75rem; border: 1px solid #E2E8F0; border-radius: 6px; text-decoration: none; color: #4A5568; font-weight: 500; font-size: 0.875rem; transition: all 0.2s; }
        .pagination a:hover { background-color: #F7FAFC; border-color: #CBD5E0; }
        .pagination .active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-color: #667eea; }
        .pagination .disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .pagination-info { color: #718096; font-size: 0.875rem; margin: 0 1rem; }

        .admin-footer { background-color: #F7FAFC; }
        .admin-footer-content { max-width: 1400px; margin: 0 auto; padding: 0 2rem; text-align: center; }
        .admin-footer-content p { margin: 0; font-size: 0.875rem; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">

        <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1>Saved Measurements</h1>
                <p>All user measurements with complete details</p>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem; align-items: flex-end;">
                <div class="category-filter">
                    <label>
                        <input type="radio" name="category" value="women" <?php echo $categoryFilter === 'women' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Women
                    </label>
                    <label>
                        <input type="radio" name="category" value="men" <?php echo $categoryFilter === 'men' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Men
                    </label>
                    <label>
                        <input type="radio" name="category" value="boy" <?php echo $categoryFilter === 'boy' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Boy
                    </label>
                    <label>
                        <input type="radio" name="category" value="girl" <?php echo $categoryFilter === 'girl' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Girl
                    </label>
                </div>
                <div class="category-filter">
                    <label>
                        <input type="radio" name="body_section" value="upper_body" <?php echo $bodySectionFilter === 'upper_body' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Upper Body
                    </label>
                    <label>
                        <input type="radio" name="body_section" value="lower_body" <?php echo $bodySectionFilter === 'lower_body' ? 'checked' : ''; ?> onchange="applyFilters()">
                        Lower Body
                    </label>
                </div>
            </div>
        </div>

        <!-- All Measurements Table -->
        <div class="measurements-container">

            <?php if (empty($measurements)): ?>
                <div class="no-data">
                    <p>No saved measurements found.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="measurements-table">
                        <thead>
                            <tr>
                                <th>ID<br><span class="variable-name">(id)</span></th>
                                <th>User<br><span class="variable-name">(username)</span></th>
                                <th>Customer<br><span class="variable-name">(customer_name)</span></th>
                                <th>Category<br><span class="variable-name">(category)</span></th>
                                <?php if ($showColumn('blength')): ?><th>Blouse Back Length (1)<br><span class="variable-name">(blength)</span></th><?php endif; ?>
                                <?php if ($showColumn('fshoulder')): ?><th>Full Shoulder (2)<br><span class="variable-name">(fshoulder)</span></th><?php endif; ?>
                                <?php if ($showColumn('shoulder')): ?><th>Shoulder Strap (3)<br><span class="variable-name">(shoulder)</span></th><?php endif; ?>
                                <?php if ($showColumn('bnDepth')): ?><th>Back Neck Depth (4)<br><span class="variable-name">(bnDepth)</span></th><?php endif; ?>
                                <?php if ($showColumn('fndepth')): ?><th>Front Neck Depth (5)<br><span class="variable-name">(fndepth)</span></th><?php endif; ?>
                                <?php if ($showColumn('apex')): ?><th>Shoulder to Apex (6)<br><span class="variable-name">(apex)</span></th><?php endif; ?>
                                <?php if ($showColumn('flength')): ?><th>Front Length (7)<br><span class="variable-name">(flength)</span></th><?php endif; ?>
                                <?php if ($showColumn('chest')): ?><th>Upper Chest (8)<br><span class="variable-name">(chest)</span></th><?php endif; ?>
                                <?php if ($showColumn('bust')): ?><th>Bust (9)<br><span class="variable-name">(bust)</span></th><?php endif; ?>
                                <?php if ($showColumn('waist')): ?><th>Waist (10)<br><span class="variable-name">(waist)</span></th><?php endif; ?>
                                <?php if ($showColumn('hips')): ?><th>Hips<br><span class="variable-name">(hips)</span></th><?php endif; ?>
                                <?php if ($showColumn('height')): ?><th>Height<br><span class="variable-name">(height)</span></th><?php endif; ?>
                                <?php if ($showColumn('slength')): ?><th>Sleeve Length (11)<br><span class="variable-name">(slength)</span></th><?php endif; ?>
                                <?php if ($showColumn('saround')): ?><th>Arm Round (12)<br><span class="variable-name">(saround)</span></th><?php endif; ?>
                                <?php if ($showColumn('sopen')): ?><th>Sleeve End Round (13)<br><span class="variable-name">(sopen)</span></th><?php endif; ?>
                                <?php if ($showColumn('armhole')): ?><th>Armhole (14)<br><span class="variable-name">(armhole)</span></th><?php endif; ?>
                                <?php if ($showColumn('inseam')): ?><th>Inseam<br><span class="variable-name">(inseam)</span></th><?php endif; ?>
                                <?php if ($showColumn('thigh_circumference')): ?><th>Thigh Circ.<br><span class="variable-name">(thigh_circumference)</span></th><?php endif; ?>
                                <th>Notes<br><span class="variable-name">(notes)</span></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($measurements as $meas): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meas['id']); ?></td>
                                    <td><?php echo $meas['username'] ? htmlspecialchars($meas['username']) : '<em>Unknown</em>'; ?></td>
                                    <td><?php echo $meas['customer_name'] ? htmlspecialchars($meas['customer_name']) : '<em>Self/None</em>'; ?></td>
                                    <td>
                                        <span class="category-badge <?php echo strtolower($meas['category']); ?>">
                                            <?php echo ucfirst($meas['category']); ?>
                                        </span>
                                    </td>
                                    <?php if ($showColumn('blength')): ?><td><?php echo $meas['blength'] ? $meas['blength'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('fshoulder')): ?><td><?php echo $meas['fshoulder'] ? $meas['fshoulder'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('shoulder')): ?><td><?php echo $meas['shoulder'] ? $meas['shoulder'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('bnDepth')): ?><td><?php echo $meas['bnDepth'] ? $meas['bnDepth'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('fndepth')): ?><td><?php echo $meas['fndepth'] ? $meas['fndepth'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('apex')): ?><td><?php echo $meas['apex'] ? $meas['apex'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('flength')): ?><td><?php echo $meas['flength'] ? $meas['flength'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('chest')): ?><td><?php echo $meas['chest'] ? $meas['chest'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('bust')): ?><td><?php echo $meas['bust'] ? $meas['bust'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('waist')): ?><td><?php echo $meas['waist'] ? $meas['waist'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('hips')): ?><td><?php echo $meas['hips'] ? $meas['hips'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('height')): ?><td><?php echo $meas['height'] ? $meas['height'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('slength')): ?><td><?php echo $meas['slength'] ? $meas['slength'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('saround')): ?><td><?php echo $meas['saround'] ? $meas['saround'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('sopen')): ?><td><?php echo $meas['sopen'] ? $meas['sopen'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('armhole')): ?><td><?php echo $meas['armhole'] ? $meas['armhole'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('inseam')): ?><td><?php echo $meas['inseam'] ? $meas['inseam'] . '"' : '-'; ?></td><?php endif; ?>
                                    <?php if ($showColumn('thigh_circumference')): ?><td><?php echo $meas['thigh_circumference'] ? $meas['thigh_circumference'] . '"' : '-'; ?></td><?php endif; ?>
                                    <td><?php echo $meas['notes'] ? htmlspecialchars($meas['notes']) : '-'; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this measurement?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $meas['id']; ?>">
                                            <button type="submit" class="btn-delete" title="Delete">
                                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <!-- Previous Button -->
                        <?php if ($currentPage > 1): ?>
                            <a href="?category=<?php echo $categoryFilter; ?>&body_section=<?php echo $bodySectionFilter; ?>&page=<?php echo $currentPage - 1; ?>">
                                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
                                Previous
                            </span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1): ?>
                            <a href="?category=<?php echo $categoryFilter; ?>&body_section=<?php echo $bodySectionFilter; ?>&page=1">1</a>
                            <?php if ($startPage > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?category=<?php echo $categoryFilter; ?>&body_section=<?php echo $bodySectionFilter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?category=<?php echo $categoryFilter; ?>&body_section=<?php echo $bodySectionFilter; ?>&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?category=<?php echo $categoryFilter; ?>&body_section=<?php echo $bodySectionFilter; ?>&page=<?php echo $currentPage + 1; ?>">
                                Next
                                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                Next
                                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                            </span>
                        <?php endif; ?>

                        <!-- Pagination Info -->
                        <span class="pagination-info">
                            Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                        </span>
                    </div>
                <?php endif; ?>
        </div>
    </div>

    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div class="admin-footer-content">
            <p>&copy; 2026 CuttingMaster Admin Panel. All rights reserved.</p>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });

        function applyFilters() {
            const category = document.querySelector('input[name="category"]:checked').value;
            const bodySection = document.querySelector('input[name="body_section"]:checked').value;
            window.location.href = '?category=' + category + '&body_section=' + bodySection + '&page=1';
        }

        function toggleDetails(id) {
            const row = document.getElementById('details-' + id);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }

            // Reinitialize icons after DOM update
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>
