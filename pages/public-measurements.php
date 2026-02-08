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
        $deleteStmt = $pdo->prepare("DELETE FROM public_measurements WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
        exit;
    } catch(PDOException $e) {
        error_log("Error deleting measurement: " . $e->getMessage());
    }
}

// Pagination settings
$recordsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
if (!in_array($recordsPerPage, [10, 25, 50, 100])) {
    $recordsPerPage = 20;
}
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'women';

// Define all saree blouse measurement fields (no upper/lower body concept)
$displayFields = [
    'blength' => 'Blouse Back Length (1)',
    'fshoulder' => 'Full Shoulder (2)',
    'shoulder' => 'Shoulder Strap (3)',
    'bnDepth' => 'Back Neck Depth (4)',
    'fndepth' => 'Front Neck Depth (5)',
    'apex' => 'Shoulder to Apex (6)',
    'flength' => 'Front Length (7)',
    'chest' => 'Upper Chest (8)',
    'bust' => 'Bust Round (9)',
    'waist' => 'Waist (10)',
    'slength' => 'Sleeve Length (11)',
    'saround' => 'Arm Round (12)',
    'sopen' => 'Sleeve End Round (13)',
    'armhole' => 'Armhole (14)'
];

// Build SELECT query with all saree blouse measurement fields + repetition counter
$selectFields = "id, category, " .
    "blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest, " .
    "bust, waist, slength, saround, sopen, armhole, " .
    "repetition, created_at, updated_at";

// Fetch paginated public measurements
try {
    // Build WHERE clause
    $whereClause = "WHERE category = :category";

    // Get total count
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM public_measurements $whereClause");
    $totalStmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
    $totalStmt->execute();
    $totalRecords = $totalStmt->fetch()['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Fetch paginated measurements (sorted by repetition count, then by date)
    $stmt = $pdo->prepare("
        SELECT $selectFields
        FROM public_measurements
        $whereClause
        ORDER BY repetition DESC, created_at DESC
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
        FROM public_measurements
        GROUP BY category
    ");
    $stats = $statsStmt->fetchAll();

} catch(PDOException $e) {
    error_log("Error fetching public measurements: " . $e->getMessage());
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
    <title>Public Measurements - Admin Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="../css/admin-styles.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        /* Page-specific styles for public measurements */
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #2D3748; margin-bottom: 0.5rem; }
        .page-header p { color: #718096; font-size: 1rem; }

        .table-wrapper { overflow-x: auto; }

        .measurements-table th {
            padding: 0.5rem 0.35rem;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #E2E8F0;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.3;
            max-width: 80px;
        }
        .measurements-table td { padding: 0.5rem 0.35rem; font-size: 0.75rem; }
        .measurements-table tbody tr:nth-child(even) { background-color: #F7FAFC; }
        .measurements-table tbody tr:hover { background-color: #EDF2F7; }

        .category-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .category-badge.women { background-color: #FCE7F3; color: #9F1239; }
        .category-badge.men { background-color: #DBEAFE; color: #1E40AF; }

        .repetition-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: help;
            transition: transform 0.2s;
        }
        .repetition-badge:hover {
            transform: scale(1.1);
        }
        .category-badge.boy { background-color: #D1FAE5; color: #065F46; }
        .category-badge.girl { background-color: #FEF3C7; color: #92400E; }

        .category-filter { display: flex; gap: 1rem; align-items: center; }
        .category-filter label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #4A5568; font-weight: 500; }
        .category-filter input[type="radio"] { cursor: pointer; width: 18px; height: 18px; accent-color: #4299E1; }

        .btn-delete { background: none; border: none; color: #EF4444; cursor: pointer; padding: 0.25rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s; }
        .btn-delete:hover { background-color: #FEE2E2; color: #DC2626; }

        .pagination { display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem; padding: 2rem 1.5rem; flex-wrap: wrap; }
        .records-per-page { display: flex; align-items: center; gap: 0.5rem; margin-right: auto; font-size: 0.875rem; color: #4A5568; }
        .records-per-page select { padding: 0.5rem 2rem 0.5rem 0.75rem; border: 1px solid #E2E8F0; border-radius: 6px; background-color: white; color: #2D3748; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.5rem center; }
        .records-per-page select:hover { border-color: #CBD5E0; background-color: #F7FAFC; }
        .records-per-page select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

        .pagination a, .pagination span { padding: 0.5rem 0.75rem; border: 1px solid #E2E8F0; border-radius: 6px; text-decoration: none; color: #4A5568; font-size: 0.875rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
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
                <h1>Public Measurements Database</h1>
                <p>Anonymous measurement data collected for analysis and pattern optimization</p>
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
            </div>
        </div>

        <!-- All Measurements Table -->
        <div class="measurements-container">
            <div class="table-wrapper">
                <table class="measurements-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <?php foreach ($displayFields as $field => $label): ?>
                                <th><?php echo htmlspecialchars($label); ?></th>
                            <?php endforeach; ?>
                            <th>Count</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($measurements)): ?>
                            <tr>
                                <td colspan="<?php echo count($displayFields) + 6; ?>" style="text-align: center; padding: 30px; color: #888;">
                                    No public measurements found for this category.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($measurements as $meas): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meas['id']); ?></td>
                                    <td>
                                        <span class="category-badge <?php echo strtolower($meas['category']); ?>">
                                            <?php echo ucfirst($meas['category']); ?>
                                        </span>
                                    </td>
                                    <?php foreach ($displayFields as $field => $label): ?>
                                        <td><?php echo $meas[$field] ? htmlspecialchars($meas[$field]) . '"' : '-'; ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <span class="repetition-badge" title="<?php echo $meas['repetition']; ?> visitors submitted these measurements">
                                            <?php echo $meas['repetition'] ?? 1; ?>×
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($meas['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $updatedAt = $meas['updated_at'] ?? $meas['created_at'];
                                        if ($updatedAt !== $meas['created_at']) {
                                            echo '<span title="Last repetition: ' . date('M d, Y g:i A', strtotime($updatedAt)) . '">';
                                            echo date('M d, Y', strtotime($updatedAt));
                                            echo '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <!-- Records Per Page Dropdown -->
                <div class="records-per-page">
                    <label for="recordsPerPage">Records per page:</label>
                    <select id="recordsPerPage" onchange="changeRecordsPerPage(this.value)">
                        <option value="10" <?php echo $recordsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $recordsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $recordsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $recordsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>

                <?php if ($totalPages > 1): ?>
                    <!-- Previous Button -->
                    <?php if ($currentPage > 1): ?>
                        <a href="?category=<?php echo $categoryFilter; ?>&per_page=<?php echo $recordsPerPage; ?>&page=<?php echo $currentPage - 1; ?>">
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
                        <a href="?category=<?php echo $categoryFilter; ?>&per_page=<?php echo $recordsPerPage; ?>&page=1">1</a>
                        <?php if ($startPage > 2): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?category=<?php echo $categoryFilter; ?>&per_page=<?php echo $recordsPerPage; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <a href="?category=<?php echo $categoryFilter; ?>&per_page=<?php echo $recordsPerPage; ?>&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?category=<?php echo $categoryFilter; ?>&per_page=<?php echo $recordsPerPage; ?>&page=<?php echo $currentPage + 1; ?>">
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
                <?php endif; ?>
            </div>
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
            const perPage = new URLSearchParams(window.location.search).get('per_page') || '20';
            window.location.href = '?category=' + category + '&per_page=' + perPage + '&page=1';
        }

        function changeRecordsPerPage(perPage) {
            const category = new URLSearchParams(window.location.search).get('category') || 'women';
            window.location.href = '?category=' + category + '&per_page=' + perPage + '&page=1';
        }
    </script>
</body>
</html>
