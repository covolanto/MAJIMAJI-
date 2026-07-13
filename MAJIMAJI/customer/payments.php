<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Payment History';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

$payments = getPaymentsByCustomerId($customer['id']);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Payment History</h1>
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
        <h1>My Payment History</h1>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="table-container">
            <table class="table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bill Month</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <h3>No payments found</h3>
                                    <p>Your payment history will appear here</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>#<?php echo $payment['id']; ?></td>
                                <td><?php echo date('F Y', strtotime($payment['billing_month'] . '-01')); ?></td>
                                <td class="bill-amount"><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td><?php echo $payment['transaction_id']; ?></td>
                                <td><?php echo formatDateTime($payment['payment_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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