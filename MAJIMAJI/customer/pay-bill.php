<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Pay Bill';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

// Get bill ID
$billId = sanitize($_GET['id'] ?? '');

if (empty($billId)) {
    setMessage('Invalid bill ID', 'error');
    redirect('bills.php');
}

// Get bill details
$sql = "SELECT * FROM bills WHERE id = '$billId' AND customer_id = '" . $customer['id'] . "'";
$bill = getRow($sql);

if (!$bill) {
    setMessage('Bill not found', 'error');
    redirect('bills.php');
}

if ($bill['status'] === 'paid') {
    setMessage('This bill is already paid', 'warning');
    redirect('bills.php');
}

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'online');

    // Generate transaction ID
    $transactionId = generateTransactionId();

    // Insert payment
    $sql = "INSERT INTO payments (bill_id, amount, payment_method, transaction_id, status) 
            VALUES ('$billId', '" . $bill['amount'] . "', '$paymentMethod', '$transactionId', 'completed')";

    if (query($sql)) {
        // Update bill status
        $sql = "UPDATE bills SET status = 'paid' WHERE id = '$billId'";
        query($sql);

        setMessage('Payment successful! Transaction ID: ' . $transactionId, 'success');
        redirect('payments.php');
    } else {
        $errors[] = 'Payment failed. Please try again.';
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Pay Bill</h1>
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
    <!-- Page Header -->
    <div class="page-header">
        <h1>Pay Your Bill</h1>
        <div class="page-actions">
            <a href="bills.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment Form -->
    <div class="card" style="max-width: 600px;">
        <!-- Bill Summary -->
        <div style="background: var(--bg-input); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Bill Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Bill ID:</span>
                <span>#<?php echo $bill['id']; ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Billing Month:</span>
                <span><?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Consumption:</span>
                <span><?php echo $bill['consumption']; ?> m³</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Due Date:</span>
                <span><?php echo formatDate($bill['due_date']); ?></span>
            </div>
            <hr style="border-color: var(--border-color); margin: 1rem 0;">
            <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.5rem;">
                <span>Total Amount:</span>
                <span style="color: var(--primary);"><?php echo formatCurrency($bill['amount']); ?></span>
            </div>
        </div>

        <!-- Payment Method -->
        <form method="POST">
            <h3 style="margin-bottom: 1.5rem;">Payment Method</h3>

            <div class="form-group">
                <label class="form-label required">Select Payment Method</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="online" checked style="display: none;">
                        <div class="card" style="text-align: center; padding: 1.5rem; border: 2px solid var(--primary); background: var(--primary-light);">
                            <i class="fas fa-credit-card" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: 500;">Online Payment</div>
                        </div>
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="bank_transfer" style="display: none;">
                        <div class="card" style="text-align: center; padding: 1.5rem; border: 1px solid var(--border-color);">
                            <i class="fas fa-university" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: 500;">Bank Transfer</div>
                        </div>
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="cash" style="display: none;">
                        <div class="card" style="text-align: center; padding: 1.5rem; border: 1px solid var(--border-color);">
                            <i class="fas fa-money-bill" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: 500;">Cash</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-lock"></i> Pay <?php echo formatCurrency($bill['amount']); ?>
                </button>
            </div>

            <p style="text-align: center; margin-top: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                <i class="fas fa-shield-alt"></i> Your payment is secure and encrypted
            </p>
        </form>
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