<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'My Bills';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

$bills = getBillsByCustomerId($customer['id']);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">My Bills</h1>
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
        <h1>My Bills</h1>
    </div>

    <!-- Bills Table -->
    <div class="card">
        <div class="table-container">
            <table class="table" id="billsTable">
                <thead>
                    <tr>
                        <th>Bill ID</th>
                        <th>Month</th>
                        <th>Consumption</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <h3>No bills found</h3>
                                    <p>You don't have any bills yet</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td>#<?php echo $bill['id']; ?></td>
                                <td><?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?></td>
                                <td><?php echo $bill['consumption']; ?> m³</td>
                                <td><?php echo formatCurrency($bill['rate']); ?>/m³</td>
                                <td class="bill-amount"><?php echo formatCurrency($bill['amount']); ?></td>
                                <td><?php echo formatDate($bill['due_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $bill['status']; ?>">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($bill['status'] !== 'paid'): ?>
                                        <a href="pay-bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-primary">Pay Now</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Paid</span>
                                    <?php endif; ?>
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