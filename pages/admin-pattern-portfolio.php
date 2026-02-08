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

// Get the category filter
$category = $_GET['category'] ?? 'all';
$validCategories = ['all', 'pattern', 'front_design', 'back_design'];
if (!in_array($category, $validCategories)) {
    $category = 'all';
}

$categoryLabels = [
    'all' => 'All Items',
    'pattern' => 'Blouse Patterns',
    'front_design' => 'Front Blouse Designs',
    'back_design' => 'Back Blouse Designs'
];

// Get the sort parameter
$sort = $_GET['sort'] ?? 'display_order';
$validSorts = ['display_order', 'category', 'title', 'created_at'];
if (!in_array($sort, $validSorts)) {
    $sort = 'display_order';
}

$sortLabels = [
    'display_order' => 'Display Order',
    'category' => 'Category',
    'title' => 'Title (A-Z)',
    'created_at' => 'Date Added'
];

// Build ORDER BY clause
$orderBy = match($sort) {
    'category' => 'category ASC, display_order ASC',
    'title' => 'title ASC',
    'created_at' => 'created_at DESC',
    default => 'display_order ASC, created_at DESC'
};

// Handle portfolio item deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    try {
        $deleteId = intval($_POST['item_id']);

        $stmt = $pdo->prepare("SELECT image FROM pattern_making_portfolio WHERE id = ?");
        $stmt->execute([$deleteId]);
        $item = $stmt->fetch();

        if ($item && $item['image']) {
            $imagePath = __DIR__ . '/../' . $item['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleteStmt = $pdo->prepare("DELETE FROM pattern_making_portfolio WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        header('Location: admin-pattern-portfolio.php?category=' . $category . '&deleted=1');
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
        $itemCategory = $_POST['category'] ?? 'pattern';
        $description = trim($_POST['description']);
        $price = floatval($_POST['price'] ?? 0);
        $displayOrder = intval($_POST['display_order']);
        $previewFile = trim($_POST['preview_file'] ?? '');
        $pdfDownloadFile = trim($_POST['pdf_download_file'] ?? '');
        $svgDownloadFile = trim($_POST['svg_download_file'] ?? '');
        $visibleOnHome = isset($_POST['visible_on_home']) ? 1 : 0;

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        if (!in_array($itemCategory, ['pattern', 'front_design', 'back_design'])) {
            $itemCategory = 'pattern';
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
        $filename = 'pattern_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/pattern-making-portfolio/';
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image');
        }

        $imagePath = 'uploads/pattern-making-portfolio/' . $filename;

        $stmt = $pdo->prepare("INSERT INTO pattern_making_portfolio (title, category, description, price, image, display_order, preview_file, pdf_download_file, svg_download_file, visible_on_home, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$title, $itemCategory, $description, $price, $imagePath, $displayOrder, $previewFile, $pdfDownloadFile, $svgDownloadFile, $visibleOnHome]);

        header('Location: admin-pattern-portfolio.php?category=' . $itemCategory . '&added=1');
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

        $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $itemId]);

        header('Location: admin-pattern-portfolio.php?category=' . $category . '&updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error updating status: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle portfolio item edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    try {
        $itemId = intval($_POST['item_id']);
        $title = trim($_POST['edit_title']);
        $editCategory = $_POST['edit_category'] ?? 'pattern';
        $description = trim($_POST['edit_description']);
        $price = floatval($_POST['edit_price'] ?? 0);
        $displayOrder = intval($_POST['edit_display_order']);
        $previewFile = trim($_POST['edit_preview_file'] ?? '');
        $pdfDownloadFile = trim($_POST['edit_pdf_download_file'] ?? '');
        $svgDownloadFile = trim($_POST['edit_svg_download_file'] ?? '');
        $visibleOnHome = isset($_POST['edit_visible_on_home']) ? 1 : 0;

        if (empty($title)) {
            throw new Exception('Title is required');
        }

        if (!in_array($editCategory, ['pattern', 'front_design', 'back_design'])) {
            $editCategory = 'pattern';
        }

        // Check if new image is uploaded
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

            // Get old image to delete
            $stmt = $pdo->prepare("SELECT image FROM pattern_making_portfolio WHERE id = ?");
            $stmt->execute([$itemId]);
            $oldItem = $stmt->fetch();

            // Upload new image
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'pattern_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/pattern-making-portfolio/';
            $uploadPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload image');
            }

            $imagePath = 'uploads/pattern-making-portfolio/' . $filename;

            // Delete old image
            if ($oldItem && $oldItem['image']) {
                $oldImagePath = __DIR__ . '/../' . $oldItem['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Update with new image
            $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET title = ?, category = ?, description = ?, price = ?, image = ?, display_order = ?, preview_file = ?, pdf_download_file = ?, svg_download_file = ?, visible_on_home = ? WHERE id = ?");
            $stmt->execute([$title, $editCategory, $description, $price, $imagePath, $displayOrder, $previewFile, $pdfDownloadFile, $svgDownloadFile, $visibleOnHome, $itemId]);
        } else {
            // Update without changing image
            $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET title = ?, category = ?, description = ?, price = ?, display_order = ?, preview_file = ?, pdf_download_file = ?, svg_download_file = ?, visible_on_home = ? WHERE id = ?");
            $stmt->execute([$title, $editCategory, $description, $price, $displayOrder, $previewFile, $pdfDownloadFile, $svgDownloadFile, $visibleOnHome, $itemId]);
        }

        header('Location: admin-pattern-portfolio.php?category=' . $editCategory . '&updated=1');
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

// Fetch portfolio items based on category filter and sort
try {
    if ($category === 'all') {
        $stmt = $pdo->prepare("SELECT * FROM pattern_making_portfolio ORDER BY $orderBy");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pattern_making_portfolio WHERE category = ? ORDER BY $orderBy");
        $stmt->execute([$category]);
    }
    $portfolioItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure pattern file path columns exist (set defaults if missing)
    foreach ($portfolioItems as &$item) {
        if (!isset($item['preview_file'])) $item['preview_file'] = '';
        if (!isset($item['pdf_download_file'])) $item['pdf_download_file'] = '';
        if (!isset($item['svg_download_file'])) $item['svg_download_file'] = '';
    }
    unset($item); // Break reference
} catch(PDOException $e) {
    $portfolioItems = [];
}

$totalItems = count($portfolioItems);
$activeItems = count(array_filter($portfolioItems, fn($item) => $item['status'] === 'active'));

// Get counts for each category
try {
    $countStmt = $pdo->query("SELECT category, COUNT(*) as cnt FROM pattern_making_portfolio GROUP BY category");
    $categoryCounts = [];
    while ($row = $countStmt->fetch()) {
        $categoryCounts[$row['category']] = $row['cnt'];
    }
    $totalAllItems = array_sum($categoryCounts);
} catch(PDOException $e) {
    $categoryCounts = [];
    $totalAllItems = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Catalog - Admin - CuttingMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">
        <div class="admin-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Pattern Catalog</h1>
            <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; color: #4A5568;">
                <span><strong><?php echo $totalItems; ?></strong> Showing</span>
                <span><strong><?php echo $activeItems; ?></strong> Active</span>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Portfolio Items - Full Width -->
        <div class="portfolio-grid-container">
            <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="margin: 0;"><?php echo $categoryLabels[$category]; ?></h2>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <label for="categoryFilter" style="font-size: 0.8rem; color: #64748B;">Category:</label>
                        <select id="categoryFilter" onchange="updateCategory(this.value)" style="padding: 0.35rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                            <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All (<?php echo $totalAllItems; ?>)</option>
                            <option value="pattern" <?php echo $category === 'pattern' ? 'selected' : ''; ?>>Patterns (<?php echo $categoryCounts['pattern'] ?? 0; ?>)</option>
                            <option value="front_design" <?php echo $category === 'front_design' ? 'selected' : ''; ?>>Front (<?php echo $categoryCounts['front_design'] ?? 0; ?>)</option>
                            <option value="back_design" <?php echo $category === 'back_design' ? 'selected' : ''; ?>>Back (<?php echo $categoryCounts['back_design'] ?? 0; ?>)</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <label for="sortBy" style="font-size: 0.8rem; color: #64748B;">Sort:</label>
                        <select id="sortBy" onchange="updateSort(this.value)" style="padding: 0.35rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                            <option value="display_order" <?php echo $sort === 'display_order' ? 'selected' : ''; ?>>Display Order</option>
                            <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
                            <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title (A-Z)</option>
                            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
                        </select>
                    </div>
                    <button type="button" class="btn-add" onclick="openAddModal()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Add Item
                    </button>
                </div>
            </div>

            <?php if (empty($portfolioItems)): ?>
                <div class="no-data">
                    <i data-lucide="image" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No portfolio items yet.</p>
                    <button type="button" class="btn-add" onclick="openAddModal()" style="margin-top: 1rem;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Add Your First Item
                    </button>
                </div>
            <?php else: ?>
                <div class="portfolio-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php foreach ($portfolioItems as $item): ?>
                        <div class="portfolio-card">
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="portfolio-card-image" style="height: 150px; width: 100%; object-fit: contain; background: #f8f9fa;">
                            <div class="portfolio-card-content">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                    <div class="portfolio-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <button type="button" class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>)" style="flex-shrink: 0;">
                                        <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                    </button>
                                </div>
                                <div class="portfolio-card-desc">
                                    <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                                </div>
                                <div style="font-size: 0.75rem; margin-bottom: 0.5rem;">
                                    <?php if (($item['price'] ?? 0) == 0): ?>
                                        <span style="color: #DC2626; font-weight: 600;">Free</span>
                                    <?php else: ?>
                                        <span style="color: #065F46; font-weight: 600;">$<?php echo number_format($item['price'], 2); ?></span>
                                    <?php endif; ?>
                                    <span style="color: #718096; margin-left: 0.5rem;">Order: <?php echo $item['display_order']; ?></span>
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

    <!-- Delete Modal -->
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
        <div class="modal-content" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3>Edit Portfolio Item</h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 1rem 1.5rem; max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" name="item_id" id="editItemId">

                    <!-- Two Column Layout -->
                    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem; align-items: start;">
                        <!-- Left Column: Image + Basic Info -->
                        <div>
                            <!-- Image Preview -->
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <img id="editImagePreview" src="" alt="Current image" style="width: 100%; max-height: 140px; object-fit: contain; border-radius: 8px; border: 1px solid #E2E8F0; background: #f8f9fa;">
                                <div style="margin-top: 0.5rem;">
                                    <label for="edit_image" style="font-size: 0.75rem; color: #3B82F6; cursor: pointer; text-decoration: underline;">Change Image</label>
                                    <input type="file" id="edit_image" name="edit_image" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                                </div>
                            </div>

                            <!-- Title -->
                            <div style="margin-bottom: 0.6rem;">
                                <label for="edit_title" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Title *</label>
                                <input type="text" id="edit_title" name="edit_title" required style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                            </div>

                            <!-- Category -->
                            <div style="margin-bottom: 0.6rem;">
                                <label for="edit_category" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Category</label>
                                <select id="edit_category" name="edit_category" required style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                    <option value="pattern">Blouse Pattern</option>
                                    <option value="front_design">Front Design</option>
                                    <option value="back_design">Back Design</option>
                                </select>
                            </div>

                            <!-- Price & Order Row -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.6rem;">
                                <div>
                                    <label for="edit_price" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Price</label>
                                    <input type="number" id="edit_price" name="edit_price" min="0" step="0.01" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                </div>
                                <div>
                                    <label for="edit_display_order" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Order</label>
                                    <input type="number" id="edit_display_order" name="edit_display_order" min="0" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                </div>
                            </div>

                            <!-- Show on Home Checkbox -->
                            <div style="margin-top: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.8rem; color: #4A5568;">
                                    <input type="checkbox" id="edit_visible_on_home" name="edit_visible_on_home" value="1" style="width: auto;">
                                    Show on Home Page
                                </label>
                            </div>
                        </div>

                        <!-- Right Column: Description + File Paths -->
                        <div>
                            <!-- Description -->
                            <div style="margin-bottom: 0.75rem;">
                                <label for="edit_description" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Description</label>
                                <textarea id="edit_description" name="edit_description" rows="3" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem; resize: vertical; min-height: 70px;"></textarea>
                            </div>

                            <!-- File Paths Section -->
                            <div style="font-size: 0.75rem; font-weight: 600; color: #64748B; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.5px;">Pattern File Path</div>
                            <div style="margin-bottom: 0.6rem;">
                                <label for="edit_preview_file" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Preview File</label>
                                <input type="text" id="edit_preview_file" name="edit_preview_file" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 0.75rem 1.5rem; border-top: 1px solid #E2E8F0;">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_item" class="btn-modal-confirm" style="background: #3B82F6;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3>Add New Item</h3>
                <button type="button" class="modal-close" onclick="closeAddModal()">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 1rem 1.5rem; max-height: 70vh; overflow-y: auto;">
                    <!-- Two Column Layout -->
                    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem; align-items: start;">
                        <!-- Left Column: Image + Basic Info -->
                        <div>
                            <!-- Image Upload -->
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <div id="addImagePreview" style="width: 100%; height: 140px; border-radius: 8px; border: 2px dashed #E2E8F0; display: flex; align-items: center; justify-content: center; background: #F8FAFC; cursor: pointer;" onclick="document.getElementById('image').click()">
                                    <div style="text-align: center; color: #94A3B8;">
                                        <i data-lucide="image-plus" style="width: 32px; height: 32px; margin-bottom: 0.5rem;"></i>
                                        <p style="font-size: 0.75rem; margin: 0;">Click to upload image</p>
                                    </div>
                                </div>
                                <img id="addImagePreviewImg" src="" alt="Preview" style="width: 100%; max-height: 140px; object-fit: contain; border-radius: 8px; border: 1px solid #E2E8F0; background: #f8f9fa; display: none;">
                                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required style="display: none;">
                                <p style="font-size: 0.7rem; color: #718096; margin-top: 0.5rem;">Max 5MB (JPG, PNG, WebP, GIF)</p>
                            </div>

                            <!-- Title -->
                            <div style="margin-bottom: 0.6rem;">
                                <label for="title" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Title *</label>
                                <input type="text" id="title" name="title" required placeholder="e.g., Bodice Pattern" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                            </div>

                            <!-- Category -->
                            <div style="margin-bottom: 0.6rem;">
                                <label for="category" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Category *</label>
                                <select id="category" name="category" required style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                    <option value="pattern" <?php echo $category === 'pattern' ? 'selected' : ''; ?>>Blouse Pattern</option>
                                    <option value="front_design" <?php echo $category === 'front_design' ? 'selected' : ''; ?>>Front Design</option>
                                    <option value="back_design" <?php echo $category === 'back_design' ? 'selected' : ''; ?>>Back Design</option>
                                </select>
                            </div>

                            <!-- Price & Order Row -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.6rem;">
                                <div>
                                    <label for="price" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Price</label>
                                    <input type="number" id="price" name="price" value="0" min="0" step="0.01" placeholder="0 = Free" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                </div>
                                <div>
                                    <label for="display_order" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Order</label>
                                    <input type="number" id="display_order" name="display_order" value="0" min="0" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem;">
                                </div>
                            </div>

                            <!-- Show on Home Checkbox -->
                            <div style="margin-top: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.8rem; color: #4A5568;">
                                    <input type="checkbox" id="visible_on_home" name="visible_on_home" value="1" checked style="width: auto;">
                                    Show on Home Page
                                </label>
                            </div>
                        </div>

                        <!-- Right Column: Description + File Paths -->
                        <div>
                            <!-- Description -->
                            <div style="margin-bottom: 0.75rem;">
                                <label for="description" style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 0.8rem; color: #4A5568;">Description</label>
                                <textarea id="description" name="description" rows="3" placeholder="Brief description of the pattern" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 5px; font-size: 0.85rem; resize: vertical; min-height: 70px;"></textarea>
                            </div>

                            <!-- File Paths Section -->
                            <div style="background: #F8FAFC; border-radius: 6px; padding: 0.75rem;">
                                <div style="font-size: 0.75rem; font-weight: 600; color: #64748B; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Pattern File Path</div>
                                <div>
                                    <label for="preview_file" style="display: block; margin-bottom: 0.15rem; font-size: 0.75rem; color: #64748B;">Preview File</label>
                                    <input type="text" id="preview_file" name="preview_file" placeholder="pattern-studio/savi/saviComplete.php" style="width: 100%; padding: 0.35rem 0.5rem; border: 1px solid #E2E8F0; border-radius: 4px; font-size: 0.8rem; font-family: monospace;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 0.75rem 1.5rem; border-top: 1px solid #E2E8F0;">
                    <button type="button" class="btn-modal-cancel" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="add_item" class="btn-modal-confirm" style="background: #10B981;">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .btn-edit {
            background: #EBF5FF;
            color: #3B82F6;
            border: none;
            padding: 0.35rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-edit:hover {
            background: #DBEAFE;
        }
        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #718096;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-close:hover {
            color: #2D3748;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>

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
            document.getElementById('editItemId').value = item.id;
            document.getElementById('edit_title').value = item.title || '';
            document.getElementById('edit_category').value = item.category || 'pattern';
            document.getElementById('edit_description').value = item.description || '';
            document.getElementById('edit_price').value = item.price || 0;
            document.getElementById('edit_display_order').value = item.display_order || 0;
            document.getElementById('edit_preview_file').value = item.preview_file || '';
            document.getElementById('edit_visible_on_home').checked = item.visible_on_home == 1;
            document.getElementById('editImagePreview').src = '../' + item.image;
            document.getElementById('edit_image').value = '';
            document.getElementById('editModal').classList.add('active');
            lucide.createIcons();
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // Preview new image before upload (Edit Modal)
        document.getElementById('edit_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editImagePreview').src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Add Modal Functions
        function openAddModal() {
            // Reset form
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('price').value = '0';
            document.getElementById('display_order').value = '0';
            document.getElementById('preview_file').value = '';
            document.getElementById('visible_on_home').checked = true;
            document.getElementById('image').value = '';
            // Reset image preview
            document.getElementById('addImagePreview').style.display = 'flex';
            document.getElementById('addImagePreviewImg').style.display = 'none';
            document.getElementById('addModal').classList.add('active');
            lucide.createIcons();
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddModal();
        });

        // Preview new image before upload (Add Modal)
        document.getElementById('image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('addImagePreview').style.display = 'none';
                    document.getElementById('addImagePreviewImg').style.display = 'block';
                    document.getElementById('addImagePreviewImg').src = ev.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeAddModal();
                closeEditModal();
            }
        });

        // Category filter
        function updateCategory(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('category', value);
            window.location.href = url.toString();
        }

        // Sort functionality
        function updateSort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
