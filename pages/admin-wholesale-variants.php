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

// Get product ID from URL
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Fetch product details
$product = null;
if ($productId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM wholesale_portfolio WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $product = null;
    }
}

// Handle variant deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_variant'])) {
    try {
        $deleteId = intval($_POST['variant_id']);

        $stmt = $pdo->prepare("SELECT image FROM wholesale_variants WHERE id = ?");
        $stmt->execute([$deleteId]);
        $item = $stmt->fetch();

        if ($item && $item['image']) {
            $imagePath = __DIR__ . '/../uploads/wholesale-variants/' . $item['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleteStmt = $pdo->prepare("DELETE FROM wholesale_variants WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        header('Location: admin-wholesale-variants.php?product_id=' . $productId . '&deleted=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error deleting variant: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle variant upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $color = trim($_POST['color']);
        $size = trim($_POST['size']);
        $displayOrder = intval($_POST['display_order']);
        $variantProductId = intval($_POST['product_id']);

        if (empty($color)) {
            throw new Exception('Color is required');
        }

        if (empty($size)) {
            throw new Exception('Size is required');
        }

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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
            $filename = 'variant_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/wholesale-variants/';
            $uploadPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload image');
            }

            $imagePath = $filename;
        }

        $stmt = $pdo->prepare("INSERT INTO wholesale_variants (product_id, name, description, price, color, size, image, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$variantProductId, $name, $description, $price, $color, $size, $imagePath, $displayOrder]);

        header('Location: admin-wholesale-variants.php?product_id=' . $variantProductId . '&added=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    try {
        $variantId = intval($_POST['variant_id']);
        $newStatus = $_POST['new_status'];

        $stmt = $pdo->prepare("UPDATE wholesale_variants SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $variantId]);

        header('Location: admin-wholesale-variants.php?product_id=' . $productId . '&updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch all products for dropdown
$products = [];
try {
    $stmt = $pdo->query("SELECT id, title, category FROM wholesale_portfolio ORDER BY title ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}

// Fetch variants for selected product
$variants = [];
if ($productId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM wholesale_variants WHERE product_id = ? ORDER BY display_order ASC, created_at DESC");
        $stmt->execute([$productId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $variants = [];
    }
}

// Handle success messages
if (isset($_GET['added'])) {
    $message = 'Variant added successfully!';
    $messageType = 'success';
} elseif (isset($_GET['deleted'])) {
    $message = 'Variant deleted successfully!';
    $messageType = 'success';
} elseif (isset($_GET['updated'])) {
    $message = 'Variant updated successfully!';
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Variants - CuttingMaster Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Page-specific styles for variants page */
        .admin-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 80px;
        }

        .page-header { margin-bottom: 2rem; }
        .page-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #2D3748; margin-bottom: 0.5rem; }
        .page-subtitle { color: #718096; font-size: 0.9rem; }

        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; }
        .breadcrumb a { color: #B19CD9; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb span { color: #718096; }

        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .product-selector { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; }
        .product-selector label { display: block; font-weight: 500; color: #2D3748; margin-bottom: 0.5rem; }
        .product-selector select { width: 100%; max-width: 400px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; color: #2D3748; }

        .product-info-card { background: linear-gradient(135deg, rgba(255, 182, 217, 0.1), rgba(177, 156, 217, 0.1)); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; gap: 1.5rem; align-items: center; }
        .product-info-image { width: 100px; height: 100px; border-radius: 8px; object-fit: cover; }
        .product-info-details h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #2D3748; margin-bottom: 0.25rem; }
        .product-info-details p { color: #718096; font-size: 0.9rem; }

        .grid-container { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }
        .add-form-container { height: fit-content; position: sticky; top: 5rem; }
        .add-form-container h2 { padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 1rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn-add { width: 100%; justify-content: center; background: linear-gradient(135deg, #FFB6D9, #B19CD9); }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(177, 156, 217, 0.4); }

        .variants-container { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        .variants-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .variants-header h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #2D3748; }
        .variants-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }

        .variant-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: box-shadow 0.3s; }
        .variant-card:hover { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .variant-card-image { width: 100%; height: 180px; object-fit: cover; }
        .variant-card-placeholder { width: 100%; height: 180px; background: linear-gradient(135deg, rgba(255, 182, 217, 0.1), rgba(177, 156, 217, 0.1)); display: flex; align-items: center; justify-content: center; color: rgba(177, 156, 217, 0.5); }
        .variant-card-content { padding: 1rem; }
        .variant-card-title { font-weight: 600; color: #2D3748; margin-bottom: 0.25rem; }
        .variant-card-meta { font-size: 0.8rem; color: #718096; margin-bottom: 0.5rem; }
        .variant-card-price { font-weight: 600; color: #B19CD9; margin-bottom: 0.75rem; }
        .variant-card-actions { display: flex; gap: 0.5rem; }

        .btn-status { padding: 0.4rem 0.75rem; border-radius: 4px; }
        .btn-status.active { background: #d4edda; color: #155724; }
        .btn-status.inactive { background: #f8d7da; color: #721c24; }
        .btn-delete { padding: 0.4rem 0.75rem; border-radius: 4px; }

        .modal.show { display: flex; }
        .modal-content { max-width: 400px; text-align: center; }
        .modal-content h3 { margin-bottom: 1rem; color: #2D3748; }
        .modal-content p { color: #718096; margin-bottom: 1.5rem; }
        .modal-buttons { display: flex; gap: 1rem; justify-content: center; }
        .modal-btn { padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .modal-btn-cancel { background: #e2e8f0; color: #4a5568; }
        .modal-btn-delete { background: #ef4444; color: white; }

        @media (max-width: 992px) {
            .grid-container { grid-template-columns: 1fr; }
            .add-form-container { position: static; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-content">
        <div class="breadcrumb">
            <a href="admin-wholesale-portfolio.php">Wholesale Portfolio</a>
            <span>/</span>
            <span>Manage Variants</span>
        </div>

        <div class="page-header">
            <h1 class="page-title">Manage Product Variants</h1>
            <p class="page-subtitle">Add and manage variants for wholesale products (colors, sizes, styles)</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Product Selector -->
        <div class="product-selector">
            <label for="product-select">Select a Product to Manage Variants:</label>
            <select id="product-select" onchange="window.location.href='admin-wholesale-variants.php?product_id=' + this.value">
                <option value="">-- Select a Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $productId === intval($p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['title']); ?> (<?php echo htmlspecialchars($p['category'] ?? 'Wholesale'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($product): ?>
            <!-- Product Info Card -->
            <div class="product-info-card">
                <?php if ($product['image']): ?>
                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-info-image">
                <?php else: ?>
                    <div class="product-info-image" style="background: linear-gradient(135deg, rgba(255, 182, 217, 0.2), rgba(177, 156, 217, 0.2)); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="package" style="width: 32px; height: 32px; color: rgba(177, 156, 217, 0.5);"></i>
                    </div>
                <?php endif; ?>
                <div class="product-info-details">
                    <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category'] ?? 'Wholesale'); ?> | <strong>Price:</strong> <?php echo htmlspecialchars($product['price'] ?? 'N/A'); ?></p>
                    <p><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                </div>
            </div>

            <div class="grid-container">
                <!-- Add Variant Form -->
                <div class="add-form-container">
                    <h2>Add New Variant</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">

                        <div class="form-group">
                            <label for="name">Variant Name</label>
                            <input type="text" id="name" name="name" placeholder="e.g., Red Silk Version">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="2" placeholder="Brief description"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="text" id="price" name="price" placeholder="e.g., ₹35/unit">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="color">Color *</label>
                                <input type="text" id="color" name="color" required placeholder="e.g., Red">
                            </div>
                            <div class="form-group">
                                <label for="size">Size *</label>
                                <input type="text" id="size" name="size" required placeholder="e.g., S, M, L">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label for="image">Image (Max 5MB)</label>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>

                        <button type="submit" name="add_variant" class="btn-add">
                            <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                            Add Variant
                        </button>
                    </form>
                </div>

                <!-- Variants List -->
                <div class="variants-container">
                    <div class="variants-header">
                        <h2>Variants (<?php echo count($variants); ?>)</h2>
                    </div>

                    <?php if (empty($variants)): ?>
                        <div class="no-data">
                            <i data-lucide="layers" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No variants yet. Add your first variant using the form.</p>
                        </div>
                    <?php else: ?>
                        <div class="variants-grid">
                            <?php foreach ($variants as $variant): ?>
                                <div class="variant-card">
                                    <?php if ($variant['image']): ?>
                                        <img src="../uploads/wholesale-variants/<?php echo htmlspecialchars($variant['image']); ?>" alt="<?php echo htmlspecialchars($variant['name']); ?>" class="variant-card-image">
                                    <?php else: ?>
                                        <div class="variant-card-placeholder">
                                            <i data-lucide="image" style="width: 32px; height: 32px;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="variant-card-content">
                                        <div class="variant-card-title"><?php echo htmlspecialchars($variant['name']); ?></div>
                                        <div class="variant-card-meta">
                                            <?php if ($variant['color']): ?>
                                                <span>Color: <?php echo htmlspecialchars($variant['color']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($variant['color'] && $variant['size']): ?> | <?php endif; ?>
                                            <?php if ($variant['size']): ?>
                                                <span>Size: <?php echo htmlspecialchars($variant['size']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($variant['price']): ?>
                                            <div class="variant-card-price"><?php echo htmlspecialchars($variant['price']); ?></div>
                                        <?php endif; ?>
                                        <div class="variant-card-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $variant['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" class="btn-status <?php echo $variant['status']; ?>">
                                                    <?php echo $variant['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                                </button>
                                            </form>
                                            <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $variant['id']; ?>, '<?php echo htmlspecialchars($variant['name'], ENT_QUOTES); ?>')">
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
            </div>
        <?php else: ?>
            <div class="no-data" style="background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
                <i data-lucide="package" style="width: 64px; height: 64px; margin-bottom: 1rem; opacity: 0.3;"></i>
                <p>Please select a product above to manage its variants.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="variant_id" id="deleteItemId">
                    <button type="submit" name="delete_variant" class="modal-btn modal-btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function confirmDelete(id, name) {
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
