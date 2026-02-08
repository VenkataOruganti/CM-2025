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

// Handle portfolio item deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    try {
        $deleteId = intval($_POST['item_id']);

        // Get the image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM tailoring_portfolio WHERE id = ?");
        $stmt->execute([$deleteId]);
        $item = $stmt->fetch();

        if ($item && $item['image']) {
            $imagePath = __DIR__ . '/../' . $item['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleteStmt = $pdo->prepare("DELETE FROM tailoring_portfolio WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        header('Location: admin-tailoring-portfolio.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error deleting item: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle portfolio item upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    try {
        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $displayOrder = intval($_POST['display_order']);

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        // Handle file upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select an image to upload');
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed: JPG, PNG, WebP, GIF');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum: 5MB');
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'portfolio_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/tailoring-portfolio/';
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image');
        }

        $imagePath = 'uploads/tailoring-portfolio/' . $filename;

        $stmt = $pdo->prepare("INSERT INTO tailoring_portfolio (title, category, image, display_order, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$title, $category, $imagePath, $displayOrder]);

        header('Location: admin-tailoring-portfolio.php?added=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    try {
        $itemId = intval($_POST['item_id']);
        $newStatus = $_POST['new_status'];

        $stmt = $pdo->prepare("UPDATE tailoring_portfolio SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $itemId]);

        header('Location: admin-tailoring-portfolio.php?updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error updating status: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for success messages
if (isset($_GET['deleted'])) {
    $message = 'Portfolio item deleted successfully!';
    $messageType = 'success';
}
if (isset($_GET['added'])) {
    $message = 'Portfolio item added successfully!';
    $messageType = 'success';
}
if (isset($_GET['updated'])) {
    $message = 'Portfolio item updated successfully!';
    $messageType = 'success';
}

// Fetch all portfolio items
try {
    $stmt = $pdo->prepare("SELECT * FROM tailoring_portfolio ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $portfolioItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $portfolioItems = [];
}

// Get counts
$totalItems = count($portfolioItems);
$activeItems = count(array_filter($portfolioItems, fn($item) => $item['status'] === 'active'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Portfolio - Admin - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="../css/admin-styles.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Tailor Portfolio</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalItems; ?></div>
                <div class="stat-card-info">
                    <h3>Total Items</h3>
                </div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-value"><?php echo $activeItems; ?></div>
                <div class="stat-card-info">
                    <h3>Active Items</h3>
                </div>
            </div>
        </div>

        <!-- Add New Item Form -->
        <div class="add-form-container">
            <h2>Add New Portfolio Item</h2>
            <form class="add-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Wedding Gown">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="bridal">Bridal Collection</option>
                        <option value="casual">Casual Elegance</option>
                        <option value="business">Business Attire</option>
                        <option value="custom">Custom Design</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="image">Image * (Max 5MB)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
                </div>
                <button type="submit" name="add_item" class="btn-add">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Add Item
                </button>
            </form>
        </div>

        <!-- Portfolio Items Grid -->
        <div class="portfolio-grid-container">
            <div class="table-header">
                <h2>Portfolio Items</h2>
            </div>

            <?php if (empty($portfolioItems)): ?>
                <div class="no-data">
                    <i data-lucide="image" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No portfolio items yet. Add your first item above!</p>
                </div>
            <?php else: ?>
                <div class="portfolio-grid">
                    <?php foreach ($portfolioItems as $item): ?>
                        <div class="portfolio-card">
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="portfolio-card-image">
                            <div class="portfolio-card-content">
                                <div class="portfolio-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="portfolio-card-category">
                                    <?php echo ucfirst(htmlspecialchars($item['category'])); ?> | Order: <?php echo $item['display_order']; ?>
                                </div>
                                <div class="portfolio-card-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $item['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" name="toggle_status" class="btn-status <?php echo $item['status']; ?>">
                                            <?php echo $item['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                    <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>')">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="item_id" id="deleteItemId">
                    <button type="submit" name="delete_item" class="btn-modal-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        function confirmDelete(id, name) {
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
