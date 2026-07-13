<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

// Get payment ID from URL
$paymentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$paymentId) {
    setMessage('Invalid payment ID', 'error');
    redirect('payments.php');
}

// Get payment details
$payment = getPaymentById($paymentId);

if (!$payment) {
    setMessage('Payment not found', 'error');
    redirect('payments.php');
}

// Verify the payment belongs to the logged-in customer
$customer = getCustomerByUserId(getUserId());
if ($payment['customer_id'] != $customer['id']) {
    setMessage('Unauthorized access', 'error');
    redirect('payments.php');
}

$pageTitle = 'Payment Receipt';
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Payment Receipt</h1>
    </div>
    <div class="header-right">
        <div class="theme-toggle" id="themeToggle">
            <i class="fas fa-sun sun"></i>
            <i class="fas fa-moon moon"></i>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-logo">
                <i class="fas fa-water"></i>
            </div>
            <div class="receipt-title">AquaBill</div>
            <div>Payment Receipt</div>
        </div>

        <div class="receipt-body">
            <div class="receipt-section">
                <div class="receipt-section-title">Transaction Details</div>
                <div class="receipt-row">
                    <span>Transaction ID:</span>
                    <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Payment Date:</span>
                    <strong><?php echo formatDateTime($payment['payment_date']); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Payment Method:</span>
                    <strong><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Status:</span>
                    <span class="badge badge-success"><?php echo ucfirst(htmlspecialchars($payment['status'])); ?></span>
                </div>
            </div>

            <div class="receipt-section">
                <div class="receipt-section-title">Customer Information</div>
                <div class="receipt-row">
                    <span>Name:</span>
                    <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Meter Number:</span>
                    <strong><?php echo htmlspecialchars($payment['meter_number']); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Address:</span>
                    <strong><?php echo htmlspecialchars($payment['address'] ?: 'N/A'); ?></strong>
                </div>
            </div>

            <div class="receipt-section">
                <div class="receipt-section-title">Bill Details</div>
                <div class="receipt-row">
                    <span>Billing Month:</span>
                    <strong><?php echo date('F Y', strtotime($payment['billing_month'] . '-01')); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Consumption:</span>
                    <strong><?php echo $payment['consumption']; ?> m³</strong>
                </div>
                <div class="receipt-row">
                    <span>Bill Amount:</span>
                    <strong><?php echo formatCurrency($payment['bill_amount']); ?></strong>
                </div>
            </div>

            <div class="receipt-section">
                <div class="receipt-row total">
                    <span>Amount Paid:</span>
                    <span><?php echo formatCurrency($payment['amount']); ?></span>
                </div>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your payment!</p>
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <div class="receipt-actions">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="payments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<script>
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });

    // Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
    });

    // Close sidebar
    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
</script>