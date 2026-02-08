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

        $stmt = $pdo->prepare("SELECT image FROM wholesale_portfolio WHERE id = ?");
        $stmt->execute([$deleteId]);
        $item = $stmt->fetch();

        if ($item && $item['image']) {
            $imagePath = __DIR__ . '/../' . $item['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleteStmt = $pdo->prepare("DELETE FROM wholesale_portfolio WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        header('Location: admin-wholesale-portfolio.php?deleted=1');
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
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $displayOrder = intval($_POST['display_order']);
        $vendorId = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        if (empty($category)) {
            throw new Exception('Category is required');
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select an image to upload');
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed: JPG, PNG, WebP, GIF');
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum: 5MB');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'wholesale_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/wholesale-portfolio/';
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image');
        }

        $imagePath = 'uploads/wholesale-portfolio/' . $filename;

        $stmt = $pdo->prepare("INSERT INTO wholesale_portfolio (vendor_id, title, description, category, price, image, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$vendorId, $title, $description, $category, $price, $imagePath, $displayOrder]);

        header('Location: admin-wholesale-portfolio.php?added=1');
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

        $stmt = $pdo->prepare("UPDATE wholesale_portfolio SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $itemId]);

        header('Location: admin-wholesale-portfolio.php?updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error updating status: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle portfolio item edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    try {
        $itemId = intval($_POST['edit_item_id']);
        $title = trim($_POST['edit_title']);
        $category = trim($_POST['edit_category']);
        $description = trim($_POST['edit_description']);
        $price = trim($_POST['edit_price']);
        $displayOrder = intval($_POST['edit_display_order']);
        $vendorId = !empty($_POST['edit_vendor_id']) ? intval($_POST['edit_vendor_id']) : null;

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        if (empty($category)) {
            throw new Exception('Category is required');
        }

        // Check if a new image was uploaded
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['edit_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: JPG, PNG, WebP, GIF');
            }

            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                throw new Exception('File size too large. Maximum: 5MB');
            }

            // Delete old image
            $stmt = $pdo->prepare("SELECT image FROM wholesale_portfolio WHERE id = ?");
            $stmt->execute([$itemId]);
            $oldItem = $stmt->fetch();
            if ($oldItem && $oldItem['image']) {
                $oldImagePath = __DIR__ . '/../' . $oldItem['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Upload new image
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'wholesale_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/wholesale-portfolio/';
            $uploadPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload image');
            }

            $imagePath = 'uploads/wholesale-portfolio/' . $filename;

            $stmt = $pdo->prepare("UPDATE wholesale_portfolio SET vendor_id = ?, title = ?, description = ?, category = ?, price = ?, image = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$vendorId, $title, $description, $category, $price, $imagePath, $displayOrder, $itemId]);
        } else {
            // Update without changing image
            $stmt = $pdo->prepare("UPDATE wholesale_portfolio SET vendor_id = ?, title = ?, description = ?, category = ?, price = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$vendorId, $title, $description, $category, $price, $displayOrder, $itemId]);
        }

        header('Location: admin-wholesale-portfolio.php?updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error updating item: ' . $e->getMessage();
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

// Fetch all wholesaler users for vendor dropdown
$wholesalers = [];
try {
    $stmt = $pdo->prepare("SELECT id, username, business_name FROM users WHERE user_type = 'wholesaler' AND status = 'active' ORDER BY business_name ASC, username ASC");
    $stmt->execute();
    $wholesalers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $wholesalers = [];
}

// Fetch all portfolio items with vendor info
try {
    $stmt = $pdo->prepare("
        SELECT wp.*, u.username as vendor_name, u.business_name as vendor_company
        FROM wholesale_portfolio wp
        LEFT JOIN users u ON wp.vendor_id = u.id
        ORDER BY wp.display_order ASC, wp.created_at DESC
    ");
    $stmt->execute();
    $portfolioItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $portfolioItems = [];
}

$totalItems = count($portfolioItems);
$activeItems = count(array_filter($portfolioItems, fn($item) => $item['status'] === 'active'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wholesale Portfolio - Admin - CuttingMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Page-specific styles */
        .btn-edit { background-color: #FEF3C7; color: #92400E; }
        .btn-edit:hover { background-color: #FDE68A; transform: none; box-shadow: none; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Wholesale Marketplace Portfolio</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalItems; ?></div>
                <div class="stat-card-info"><h3>Total Items</h3></div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-value"><?php echo $activeItems; ?></div>
                <div class="stat-card-info"><h3>Active Items</h3></div>
            </div>
        </div>

        <div class="add-form-container">
            <h2>Add New Portfolio Item</h2>
            <form class="add-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Classic Silk Blouse">
                </div>
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="Blouses">Blouses</option>
                        <option value="Dresses">Dresses</option>
                        <option value="Suits">Suits</option>
                        <option value="Collections">Collections</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" placeholder="Brief description">
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="text" id="price" name="price" placeholder="e.g., From ₹28/unit">
                </div>
                <div class="form-group">
                    <label for="vendor_id">Vendor</label>
                    <select id="vendor_id" name="vendor_id">
                        <option value="">-- Select Vendor --</option>
                        <?php foreach ($wholesalers as $wholesaler): ?>
                            <option value="<?php echo $wholesaler['id']; ?>">
                                <?php echo htmlspecialchars($wholesaler['business_name'] ?: $wholesaler['username']); ?>
                            </option>
                        <?php endforeach; ?>
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
                                <div class="portfolio-card-desc">
                                    <strong><?php echo htmlspecialchars($item['category'] ?? 'Wholesale'); ?></strong> | <?php echo htmlspecialchars($item['price'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                                    <?php if ($item['vendor_company'] || $item['vendor_name']): ?>
                                        <br><span style="color: #3B82F6;"><i data-lucide="store" style="width: 12px; height: 12px; display: inline;"></i> <?php echo htmlspecialchars($item['vendor_company'] ?: $item['vendor_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="portfolio-card-actions">
                                    <a href="admin-wholesale-variants.php?product_id=<?php echo $item['id']; ?>" class="btn-status" style="background-color: #E0E7FF; color: #3730A3; text-decoration: none;">
                                        <i data-lucide="layers" style="width: 12px; height: 12px;"></i>
                                        Variants
                                    </a>
                                    <button type="button" class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>)">
                                        <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                        Edit
                                    </button>
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

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Confirm Delete</h3></div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="item_id" id="deleteItemId">
                    <button type="submit" name="delete_item" class="btn-modal-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content edit-modal-content">
            <div class="modal-header"><h3>Edit Portfolio Item</h3></div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_item_id" id="edit_item_id">
                <div class="edit-form-grid">
                    <div class="form-group">
                        <label for="edit_title">Title *</label>
                        <input type="text" id="edit_title" name="edit_title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_category">Category *</label>
                        <select id="edit_category" name="edit_category" required>
                            <option value="Blouses">Blouses</option>
                            <option value="Dresses">Dresses</option>
                            <option value="Suits">Suits</option>
                            <option value="Collections">Collections</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_description">Description</label>
                        <input type="text" id="edit_description" name="edit_description">
                    </div>
                    <div class="form-group">
                        <label for="edit_price">Price</label>
                        <input type="text" id="edit_price" name="edit_price">
                    </div>
                    <div class="form-group">
                        <label for="edit_vendor_id">Vendor</label>
                        <select id="edit_vendor_id" name="edit_vendor_id">
                            <option value="">-- Select Vendor --</option>
                            <?php foreach ($wholesalers as $wholesaler): ?>
                                <option value="<?php echo $wholesaler['id']; ?>">
                                    <?php echo htmlspecialchars($wholesaler['business_name'] ?: $wholesaler['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_display_order">Display Order</label>
                        <input type="number" id="edit_display_order" name="edit_display_order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label for="edit_image">New Image (optional)</label>
                        <input type="file" id="edit_image" name="edit_image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <div class="current-image">
                            <p style="font-size: 0.75rem; color: #718096; margin-bottom: 0.25rem;">Current image:</p>
                            <img id="edit_current_image" src="" alt="Current image">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="margin-top: 1.5rem;">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_item" class="btn-modal-cancel btn-modal-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Delete Modal Functions
        function confirmDelete(id, name) {
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Edit Modal Functions
        function openEditModal(item) {
            document.getElementById('edit_item_id').value = item.id;
            document.getElementById('edit_title').value = item.title || '';
            document.getElementById('edit_category').value = item.category || 'Blouses';
            document.getElementById('edit_description').value = item.description || '';
            document.getElementById('edit_price').value = item.price || '';
            document.getElementById('edit_vendor_id').value = item.vendor_id || '';
            document.getElementById('edit_display_order').value = item.display_order || 0;
            document.getElementById('edit_current_image').src = '../' + (item.image || '');
            document.getElementById('editModal').classList.add('active');
            lucide.createIcons();
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</body>
</html>
