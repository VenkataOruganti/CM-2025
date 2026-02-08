<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require login and check user type
requireLogin();
$currentUser = getCurrentUser();

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Check if user is individual or boutique (both can access payment)
$isIndividual = $currentUser['user_type'] === 'individual';
$isBoutique = $currentUser['user_type'] === 'boutique';

if (!$isIndividual && !$isBoutique) {
    header('Location: dashboard.php');
    exit;
}

// Get parameters from URL
$itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
$customerId = isset($_GET['customer_id']) ? htmlspecialchars($_GET['customer_id']) : '';
$paperSize = isset($_GET['paper']) ? htmlspecialchars($_GET['paper']) : 'A3';

if (!$itemId || !$customerId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch the portfolio item
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pattern_making_portfolio WHERE id = ? AND status = 'active'");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: dashboard.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: dashboard.php');
    exit;
}

// Fetch customer info if not self
$customerName = 'Self Measurements';
if ($customerId !== 'self') {
    try {
        $stmt = $pdo->prepare("SELECT customer_name FROM customers WHERE id = ? AND boutique_user_id = ?");
        $stmt->execute([$customerId, $currentUser['id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer) {
            $customerName = $customer['customer_name'];
        }
    } catch (Exception $e) {
        // Use default
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo htmlspecialchars($item['title']); ?> - CuttingMaster</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Libre+Franklin:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .payment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4A5568;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: #F7FAFC;
            color: #2D3748;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .payment-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .payment-card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 500;
            color: #2D3748;
            margin: 0;
        }

        .payment-item-info {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            background: #F7FAFC;
        }

        .payment-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .payment-item-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            color: #2D3748;
        }

        .payment-item-details p {
            margin: 0;
            font-size: 0.875rem;
            color: #718096;
        }

        .payment-summary {
            padding: 1.5rem;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-size: 0.875rem;
        }

        .payment-row-label {
            color: #718096;
        }

        .payment-row-value {
            color: #2D3748;
            font-weight: 500;
        }

        .payment-total {
            border-top: 2px solid #E2E8F0;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        .payment-total .payment-row-label {
            font-size: 1rem;
            font-weight: 600;
            color: #2D3748;
        }

        .payment-total .payment-row-value {
            font-size: 1.25rem;
            color: #065F46;
        }

        .payment-methods {
            padding: 0 1.5rem 1.5rem;
        }

        .payment-methods h4 {
            margin: 0 0 1rem 0;
            font-size: 0.875rem;
            color: #4A5568;
        }

        .payment-method-btn {
            width: 100%;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
        }

        .payment-method-btn:hover {
            border-color: #4FD1C5;
            background: #F0FDFA;
        }

        .payment-method-btn.active {
            border-color: #4FD1C5;
            background: #F0FDFA;
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F7FAFC;
            border-radius: 8px;
        }

        .payment-method-info h5 {
            margin: 0;
            font-size: 0.875rem;
            color: #2D3748;
        }

        .payment-method-info p {
            margin: 0;
            font-size: 0.75rem;
            color: #718096;
        }

        .payment-action {
            padding: 1.5rem;
            border-top: 1px solid #E2E8F0;
        }

        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4FD1C5, #38B2AC);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 209, 197, 0.3);
        }

        .btn-pay:disabled {
            background: #CBD5E0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.75rem;
            color: #718096;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/mimic-banner.php'; ?>

    <!-- Background Glow Effects -->
    <div class="bg-glow">
        <div class="bg-glow-circle-1"></div>
        <div class="bg-glow-circle-2"></div>
    </div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo logo-clickable" onclick="window.location.href='../index.php'">
                <img src="../images/cm-logo.svg" alt="CuttingMaster" style="height: 40px; width: auto;">
            </div>
            <div class="nav-links">
                <a href="pattern-studio.php" class="nav-link">PATTERN STUDIO</a>
                <a href="wholesale-catalog.php" class="nav-link">WHOLESALE MARKETPLACE</a>
                <a href="contact-us.php" class="nav-link">CONTACT US</a>
                <a href="dashboard.php" class="nav-link active-nav-link">YOUR ACCOUNT</a>
                <a href="logout.php" class="btn-secondary btn-link btn-no-border">LOGOUT</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="hero auth-section auth-section-padded" style="align-items: flex-start; padding-top: calc(4.5rem + 40px);">
        <div class="payment-container">
            <div class="payment-header">
                <a href="dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    Back to Dashboard
                </a>
            </div>

            <div class="payment-card">
                <div class="payment-card-header">
                    <h2 class="payment-card-title">Complete Your Purchase</h2>
                </div>

                <div class="payment-item-info">
                    <?php if ($item['image']): ?>
                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="payment-item-image">
                    <?php endif; ?>
                    <div class="payment-item-details">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p>Customer: <?php echo htmlspecialchars($customerName); ?></p>
                        <p>Paper Size: <?php echo htmlspecialchars($paperSize); ?></p>
                    </div>
                </div>

                <div class="payment-summary">
                    <div class="payment-row">
                        <span class="payment-row-label">Pattern Price</span>
                        <span class="payment-row-value">₹<?php echo number_format($item['price'], 0); ?></span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-row-label">Platform Fee</span>
                        <span class="payment-row-value">₹0</span>
                    </div>
                    <div class="payment-row payment-total">
                        <span class="payment-row-label">Total Amount</span>
                        <span class="payment-row-value">₹<?php echo number_format($item['price'], 0); ?></span>
                    </div>
                </div>

                <div class="payment-methods">
                    <h4>Select Payment Method</h4>
                    <button class="payment-method-btn active" data-method="upi">
                        <div class="payment-method-icon">
                            <i data-lucide="smartphone" style="width: 20px; height: 20px; color: #4FD1C5;"></i>
                        </div>
                        <div class="payment-method-info">
                            <h5>UPI</h5>
                            <p>GPay, PhonePe, Paytm, etc.</p>
                        </div>
                    </button>
                    <button class="payment-method-btn" data-method="card">
                        <div class="payment-method-icon">
                            <i data-lucide="credit-card" style="width: 20px; height: 20px; color: #4FD1C5;"></i>
                        </div>
                        <div class="payment-method-info">
                            <h5>Credit / Debit Card</h5>
                            <p>Visa, Mastercard, Rupay</p>
                        </div>
                    </button>
                    <button class="payment-method-btn" data-method="netbanking">
                        <div class="payment-method-icon">
                            <i data-lucide="landmark" style="width: 20px; height: 20px; color: #4FD1C5;"></i>
                        </div>
                        <div class="payment-method-info">
                            <h5>Net Banking</h5>
                            <p>All major banks supported</p>
                        </div>
                    </button>
                </div>

                <div class="payment-action">
                    <button class="btn-pay" onclick="processPayment()">
                        Pay ₹<?php echo number_format($item['price'], 0); ?>
                    </button>
                    <div class="secure-badge">
                        <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                        Secure Payment - 256-bit SSL Encryption
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        lucide.createIcons();

        // Payment method selection
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Process payment (placeholder - integrate with actual payment gateway)
        function processPayment() {
            const selectedMethod = document.querySelector('.payment-method-btn.active').dataset.method;

            // TODO: Integrate with Razorpay, PayU, or other payment gateway
            // For now, simulate successful payment

            alert('Payment processing... In production, this will redirect to the payment gateway.');

            // After successful payment, redirect to the download page
            const customerId = '<?php echo addslashes($customerId); ?>';
            const paperSize = '<?php echo addslashes($paperSize); ?>';
            const itemId = '<?php echo $itemId; ?>';

            // Redirect to download page with paid flag
            window.location.href = 'pattern-download.php?item_id=' + itemId + '&customer_id=' + customerId + '&paper=' + paperSize + '&paid=1';
        }
    </script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
