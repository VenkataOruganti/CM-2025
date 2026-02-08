<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        try {
            $pattern_name = trim($_POST['pattern_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? 'blouse');
            $price = floatval($_POST['price'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $sort_order = intval($_POST['sort_order'] ?? 0);

            if (empty($pattern_name)) {
                throw new Exception('Pattern name is required');
            }

            // Handle thumbnail upload
            $thumbnailPath = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['thumbnail'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception('Invalid thumbnail type. Allowed: JPG, PNG, WebP, GIF');
                }

                $maxSize = 5 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    throw new Exception('Thumbnail too large. Maximum: 5MB');
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'predesigned_thumb_' . time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/predesigned-patterns/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception('Failed to upload thumbnail');
                }

                $thumbnailPath = 'uploads/predesigned-patterns/' . $filename;
            }

            // Handle PDF upload
            $pdfPath = null;
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['pdf_file'];

                if ($file['type'] !== 'application/pdf') {
                    throw new Exception('Invalid file type. Only PDF files are allowed');
                }

                $maxSize = 50 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    throw new Exception('PDF too large. Maximum: 50MB');
                }

                $filename = 'predesigned_' . time() . '_' . uniqid() . '.pdf';
                $uploadDir = __DIR__ . '/../uploads/predesigned-patterns/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception('Failed to upload PDF');
                }

                $pdfPath = 'uploads/predesigned-patterns/' . $filename;
            }

            $stmt = $pdo->prepare("INSERT INTO predesigned_patterns (pattern_name, description, category, thumbnail_path, pdf_path, price, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$pattern_name, $description, $category, $thumbnailPath, $pdfPath, $price, $is_active, $sort_order]);

            header('Location: admin-predesigned-patterns.php?added=1');
            exit;
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        try {
            $id = intval($_POST['id']);

            // Get file paths to delete
            $stmt = $pdo->prepare("SELECT thumbnail_path, pdf_path FROM predesigned_patterns WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            if ($item) {
                if ($item['thumbnail_path'] && file_exists(__DIR__ . '/../' . $item['thumbnail_path'])) {
                    unlink(__DIR__ . '/../' . $item['thumbnail_path']);
                }
                if ($item['pdf_path'] && file_exists(__DIR__ . '/../' . $item['pdf_path'])) {
                    unlink(__DIR__ . '/../' . $item['pdf_path']);
                }
            }

            $stmt = $pdo->prepare("DELETE FROM predesigned_patterns WHERE id = ?");
            $stmt->execute([$id]);

            header('Location: admin-predesigned-patterns.php?deleted=1');
            exit;
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'toggle') {
        try {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("UPDATE predesigned_patterns SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);

            header('Location: admin-predesigned-patterns.php?updated=1');
            exit;
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Check for success messages
if (isset($_GET['deleted'])) {
    $message = 'Pre-designed pattern deleted successfully!';
    $messageType = 'success';
}
if (isset($_GET['added'])) {
    $message = 'Pre-designed pattern added successfully!';
    $messageType = 'success';
}
if (isset($_GET['updated'])) {
    $message = 'Pre-designed pattern updated successfully!';
    $messageType = 'success';
}

// Fetch all pre-designed patterns
$patterns = [];
try {
    $stmt = $pdo->query("SELECT * FROM predesigned_patterns ORDER BY sort_order ASC, created_at DESC");
    $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
}

$totalPatterns = count($patterns);
$activePatterns = count(array_filter($patterns, fn($p) => $p['is_active'] == 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-designed Patterns - Admin - CuttingMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Pre-designed Patterns</h1>
            <p>Manage ready-to-use patterns for customers</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalPatterns; ?></div>
                <div class="stat-card-info"><h3>Total Patterns</h3></div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-value"><?php echo $activePatterns; ?></div>
                <div class="stat-card-info"><h3>Active Patterns</h3></div>
            </div>
        </div>

        <div class="add-form-container">
            <h2>Add New Pre-designed Pattern</h2>
            <form class="add-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="pattern_name">Pattern Name *</label>
                    <input type="text" id="pattern_name" name="pattern_name" required placeholder="e.g., Classic Blouse Pattern">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" placeholder="Brief description">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="blouse">Blouse</option>
                        <option value="dress">Dress</option>
                        <option value="skirt">Skirt</option>
                        <option value="pants">Pants</option>
                        <option value="kurti">Kurti</option>
                        <option value="saree_blouse">Saree Blouse</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price (optional)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="thumbnail">Thumbnail (Max 5MB)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
                <div class="form-group">
                    <label for="pdf_file">PDF Pattern File (Max 50MB)</label>
                    <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
                </div>
                <div class="form-group">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group" style="display: flex; align-items: center; padding-top: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" checked>
                        Active
                    </label>
                </div>
                <button type="submit" class="btn-add">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Add Pattern
                </button>
            </form>
        </div>

        <div class="portfolio-grid-container">
            <div class="table-header">
                <h2>Pre-designed Patterns</h2>
            </div>

            <?php if (empty($patterns)): ?>
                <div class="no-data">
                    <i data-lucide="file-text" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No pre-designed patterns yet. Add your first pattern above!</p>
                </div>
            <?php else: ?>
                <div class="portfolio-grid">
                    <?php foreach ($patterns as $pattern): ?>
                        <div class="portfolio-card">
                            <?php if ($pattern['thumbnail_path']): ?>
                                <img src="../<?php echo htmlspecialchars($pattern['thumbnail_path']); ?>" alt="<?php echo htmlspecialchars($pattern['pattern_name']); ?>" class="portfolio-card-image">
                            <?php else: ?>
                                <div class="portfolio-card-image" style="background: #E2E8F0; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="file-text" style="width: 48px; height: 48px; color: #A0AEC0;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="portfolio-card-content">
                                <div class="portfolio-card-title"><?php echo htmlspecialchars($pattern['pattern_name']); ?></div>
                                <div class="portfolio-card-category">
                                    <span class="user-type-badge boutique"><?php echo ucfirst(str_replace('_', ' ', $pattern['category'])); ?></span>
                                    <?php if ($pattern['price'] > 0): ?>
                                        <span style="margin-left: 0.5rem; font-weight: 600; color: #065F46;">$<?php echo number_format($pattern['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span style="margin-left: 0.5rem; color: #718096;">Free</span>
                                    <?php endif; ?>
                                </div>
                                <div class="portfolio-card-desc">
                                    <?php echo htmlspecialchars($pattern['description'] ?? ''); ?>
                                    <?php if ($pattern['pdf_path']): ?>
                                        <br><a href="../<?php echo htmlspecialchars($pattern['pdf_path']); ?>" target="_blank" style="color: #3B82F6;">View PDF</a>
                                    <?php endif; ?>
                                </div>
                                <div class="portfolio-card-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $pattern['id']; ?>">
                                        <button type="submit" class="btn-status <?php echo $pattern['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $pattern['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                    <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $pattern['id']; ?>, '<?php echo htmlspecialchars($pattern['pattern_name'], ENT_QUOTES); ?>')">
                                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <footer class="admin-footer">
            <p>&copy; <?php echo date('Y'); ?> CuttingMaster. All rights reserved.</p>
        </footer>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Confirm Delete</h3></div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">This will also delete associated files. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteItemId">
                    <button type="submit" class="btn-modal-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        function confirmDelete(id, name) {
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
