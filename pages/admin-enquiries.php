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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $enquiryId = intval($_POST['enquiry_id']);
        $newStatus = $_POST['new_status'];

        $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $enquiryId]);

        header('Location: admin-enquiries.php?updated=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error updating status: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle enquiry deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enquiry'])) {
    try {
        $deleteId = intval($_POST['enquiry_id']);

        $deleteStmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
        $deleteStmt->execute([$deleteId]);

        header('Location: admin-enquiries.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $message = 'Error deleting enquiry: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for success messages
if (isset($_GET['deleted'])) {
    $message = 'Enquiry deleted successfully!';
    $messageType = 'success';
}
if (isset($_GET['updated'])) {
    $message = 'Enquiry status updated successfully!';
    $messageType = 'success';
}

// Fetch all enquiries
try {
    $stmt = $pdo->prepare("SELECT * FROM enquiries ORDER BY created_at DESC");
    $stmt->execute();
    $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $enquiries = [];
}

$totalEnquiries = count($enquiries);
$newEnquiries = count(array_filter($enquiries, fn($e) => $e['status'] === 'new'));
$repliedEnquiries = count(array_filter($enquiries, fn($e) => $e['status'] === 'replied'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries - Admin - CuttingMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Page-specific overrides */
        .enquiries-table th, .enquiries-table td { padding: 1rem 1.5rem; }
        .enquiries-table th { letter-spacing: 0.05em; }
        .enquiries-table td { font-size: 0.875rem; }
        .modal-content { max-width: 600px; max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-header.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Customer Enquiries</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width: 20px; height: 20px;"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalEnquiries; ?></div>
                <div class="stat-card-info"><h3>Total Enquiries</h3></div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-value"><?php echo $newEnquiries; ?></div>
                <div class="stat-card-info"><h3>New Enquiries</h3></div>
            </div>
            <div class="stat-card tertiary">
                <div class="stat-value"><?php echo $repliedEnquiries; ?></div>
                <div class="stat-card-info"><h3>Replied</h3></div>
            </div>
        </div>

        <div class="enquiries-container">
            <div class="table-header">
                <h2>All Enquiries</h2>
            </div>

            <?php if (empty($enquiries)): ?>
                <div class="no-data">
                    <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No enquiries yet.</p>
                </div>
            <?php else: ?>
                <table class="enquiries-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($enquiry['created_at'])); ?><br><span style="font-size: 0.75rem; color: #718096;"><?php echo date('h:i A', strtotime($enquiry['created_at'])); ?></span></td>
                                <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($enquiry['email']); ?></td>
                                <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                                <td><span class="status-badge <?php echo $enquiry['status']; ?>"><?php echo ucfirst($enquiry['status']); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-view" onclick="viewEnquiry(<?php echo htmlspecialchars(json_encode($enquiry), ENT_QUOTES); ?>)">
                                            <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                            View
                                        </button>
                                        <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $enquiry['id']; ?>, '<?php echo htmlspecialchars($enquiry['name'], ENT_QUOTES); ?>')">
                                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
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

        <footer class="admin-footer">
            <p>&copy; <?php echo date('Y'); ?> CuttingMaster. All rights reserved.</p>
        </footer>
    </div>

    <!-- View Enquiry Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Enquiry Details</h3>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="enquiry-detail">
                    <label>Name</label>
                    <p id="view_name"></p>
                </div>
                <div class="enquiry-detail">
                    <label>Email</label>
                    <p id="view_email"></p>
                </div>
                <div class="enquiry-detail">
                    <label>Mobile</label>
                    <p id="view_mobile"></p>
                </div>
                <div class="enquiry-detail">
                    <label>Subject</label>
                    <p id="view_subject"></p>
                </div>
                <div class="enquiry-detail message">
                    <label>Message</label>
                    <p id="view_message"></p>
                </div>
                <div class="enquiry-detail">
                    <label>Received On</label>
                    <p id="view_date"></p>
                </div>
            </div>
            <div class="modal-footer">
                <form method="POST" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                    <input type="hidden" name="enquiry_id" id="view_enquiry_id">
                    <select name="new_status" class="status-select">
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-modal btn-status-change">Update Status</button>
                </form>
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the enquiry from "<span id="deleteEnquiryName"></span>"?</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #718096;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="enquiry_id" id="deleteEnquiryId">
                    <button type="submit" name="delete_enquiry" class="btn-modal btn-modal-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // View Modal Functions
        function viewEnquiry(enquiry) {
            document.getElementById('view_name').textContent = enquiry.name || '';
            document.getElementById('view_email').textContent = enquiry.email || '';
            document.getElementById('view_mobile').textContent = enquiry.mobile || 'Not provided';
            document.getElementById('view_subject').textContent = enquiry.subject || '';
            document.getElementById('view_message').textContent = enquiry.message || '';
            document.getElementById('view_date').textContent = new Date(enquiry.created_at).toLocaleString();
            document.getElementById('view_enquiry_id').value = enquiry.id;

            // Set current status in dropdown
            const statusSelect = document.querySelector('select[name="new_status"]');
            statusSelect.value = enquiry.status;

            document.getElementById('viewModal').classList.add('active');
            lucide.createIcons();
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        document.getElementById('viewModal').addEventListener('click', function(e) {
            if (e.target === this) closeViewModal();
        });

        // Delete Modal Functions
        function confirmDelete(id, name) {
            document.getElementById('deleteEnquiryId').value = id;
            document.getElementById('deleteEnquiryName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>
</body>
</html>
