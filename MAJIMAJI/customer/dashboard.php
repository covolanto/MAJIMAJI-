<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Dashboard';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

$bills = getBillsByCustomerId($customer['id']);
$payments = getPaymentsByCustomerId($customer['id']);

// Calculate stats
$totalBills = count($bills);
$pendingBills = count(array_filter($bills, function ($b) {
    return $b['status'] === 'pending';
}));
$overdueBills = count(array_filter($bills, function ($b) {
    return $b['status'] === 'overdue';
}));
$totalPaid = 0;
$totalPending = 0;
foreach ($bills as $bill) {
    if ($bill['status'] === 'paid') {
        $totalPaid += $bill['amount'];
    } elseif ($bill['status'] === 'pending' || $bill['status'] === 'overdue') {
        $totalPending += $bill['amount'];
    }
}

// Get recent payments
$recentPayments = array_slice($payments, 0, 3);

// Get upcoming due bills
$upcomingBills = array_filter($bills, function ($b) {
    return ($b['status'] === 'pending' || $b['status'] === 'overdue') &&
        strtotime($b['due_date']) >= strtotime('today');
});
$upcomingBills = array_slice($upcomingBills, 0, 3);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Dashboard</h1>
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
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo $customer['first_name']; ?>! 👋</h1>
            <p>Here's your water usage overview and billing status.</p>
        </div>
        <div class="welcome-meta">
            <div class="meta-item">
                <i class="fas fa-id-card"></i>
                <span>Meter No: <strong><?php echo $customer['meter_number']; ?></strong></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo $customer['address'] ?: 'Address not set'; ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Bills</div>
                <div class="stat-value"><?php echo $totalBills; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Pending Bills</div>
                <div class="stat-value"><?php echo $pendingBills; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Overdue Bills</div>
                <div class="stat-value"><?php echo $overdueBills; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Paid</div>
                <div class="stat-value"><?php echo formatCurrency($totalPaid); ?></div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Billing Summary</h3>
            </div>
            <div class="summary-stats">
                <div class="summary-item">
                    <div class="summary-label">Total Amount Paid</div>
                    <div class="summary-value success"><?php echo formatCurrency($totalPaid); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Outstanding Amount</div>
                    <div class="summary-value warning"><?php echo formatCurrency($totalPending); ?></div>
                </div>
                <?php if ($totalBills > 0): ?>
                    <div class="summary-item">
                        <div class="summary-label">Payment Rate</div>
                        <div class="summary-value primary"><?php echo round(($totalPaid / ($totalPaid + $totalPending)) * 100, 1); ?>%</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="quick-actions">
                <?php if ($pendingBills > 0): ?>
                    <a href="pay-bill.php" class="quick-action-btn primary">
                        <i class="fas fa-credit-card"></i>
                        <span>Pay Bills</span>
                        <span class="badge"><?php echo $pendingBills; ?> pending</span>
                    </a>
                <?php endif; ?>
                <a href="bills.php" class="quick-action-btn">
                    <i class="fas fa-file-invoice"></i>
                    <span>View Bills</span>
                </a>
                <a href="payments.php" class="quick-action-btn">
                    <i class="fas fa-history"></i>
                    <span>Payment History</span>
                </a>
                <a href="profile.php" class="quick-action-btn">
                    <i class="fas fa-user-cog"></i>
                    <span>Update Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Bills & Payments -->
    <div class="dashboard-grid">
        <!-- Upcoming Due Bills -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Upcoming Due Bills</h3>
                <a href="bills.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if (empty($upcomingBills)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No upcoming bills</h3>
                    <p>You're all caught up!</p>
                </div>
            <?php else: ?>
                <div class="bill-list">
                    <?php foreach ($upcomingBills as $bill): ?>
                        <div class="bill-item">
                            <div class="bill-info">
                                <div class="bill-month"><?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?></div>
                                <div class="bill-consumption"><?php echo $bill['consumption']; ?> m³</div>
                            </div>
                            <div class="bill-details">
                                <div class="bill-amount"><?php echo formatCurrency($bill['amount']); ?></div>
                                <span class="badge badge-<?php echo $bill['status']; ?>"><?php echo ucfirst($bill['status']); ?></span>
                            </div>
                            <?php if ($bill['status'] !== 'paid'): ?>
                                <a href="pay-bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-primary">Pay Now</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Payments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Recent Payments</h3>
                <a href="payments.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if (empty($recentPayments)): ?>
                <div class="empty-state">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>No payments yet</h3>
                    <p>Your payment history will appear here</p>
                </div>
            <?php else: ?>
                <div class="payment-list">
                    <?php foreach ($recentPayments as $payment): ?>
                        <div class="payment-item">
                            <div class="payment-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="payment-info">
                                <div class="payment-amount"><?php echo formatCurrency($payment['amount']); ?></div>
                                <div class="payment-meta">
                                    <?php echo date('F Y', strtotime($payment['billing_month'] . '-01')); ?> •
                                    <?php echo ucfirst($payment['payment_method']); ?>
                                </div>
                            </div>
                            <div class="payment-date"><?php echo formatDate($payment['payment_date']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="card tips-card">
        <div class="tips-header">
            <i class="fas fa-lightbulb"></i>
            <h3>Water Conservation Tips</h3>
        </div>
        <div class="tips-grid">
            <div class="tip-item">
                <i class="fas fa-shower"></i>
                <span>Take shorter showers</span>
            </div>
            <div class="tip-item">
                <i class="fas fa-faucet"></i>
                <span>Fix leaky faucets promptly</span>
            </div>
            <div class="tip-item">
                <i class="fas fa-tint"></i>
                <span>Run full loads only</span>
            </div>
            <div class="tip-item">
                <i class="fas fa-seedling"></i>
                <span>Water plants in early morning</span>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<style>
    .welcome-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .welcome-section::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .welcome-content {
        position: relative;
        z-index: 1;
    }

    .welcome-content h1 {
        color: white;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .welcome-content p {
        opacity: 0.9;
    }

    .welcome-meta {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .meta-item i {
        font-size: 1rem;
    }

    .meta-item strong {
        color: white;
    }

    .summary-stats {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: var(--bg-input);
        border-radius: var(--radius);
    }

    .summary-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .summary-value {
        font-size: 1.25rem;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
    }

    .summary-value.success {
        color: var(--success);
    }

    .summary-value.warning {
        color: var(--warning);
    }

    .summary-value.primary {
        color: var(--primary);
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1.25rem 1rem;
        background: var(--bg-input);
        border-radius: var(--radius-md);
        text-align: center;
        transition: var(--transition);
    }

    .quick-action-btn:hover {
        background: var(--primary-light);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .quick-action-btn i {
        font-size: 1.5rem;
    }

    .quick-action-btn span {
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .quick-action-btn .badge {
        font-size: 0.6875rem;
        padding: 0.125rem 0.5rem;
    }

    .quick-action-btn.primary {
        background: var(--primary);
        color: white;
    }

    .quick-action-btn.primary:hover {
        background: var(--primary-hover);
    }

    .bill-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .bill-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-input);
        border-radius: var(--radius);
    }

    .bill-info {
        flex: 1;
    }

    .bill-month {
        font-weight: 600;
        color: var(--text-primary);
    }

    .bill-consumption {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .bill-details {
        text-align: right;
    }

    .bill-amount {
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        color: var(--text-primary);
    }

    .payment-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .payment-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-input);
        border-radius: var(--radius);
    }

    .payment-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .payment-icon.success {
        background: var(--success-light);
        color: var(--success);
    }

    .payment-info {
        flex: 1;
    }

    .payment-amount {
        font-weight: 600;
        color: var(--text-primary);
    }

    .payment-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .payment-date {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .tips-card {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--info-light) 100%);
        border: 1px solid var(--primary);
    }

    [data-theme="dark"] .tips-card {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--bg-card) 100%);
    }

    .tips-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .tips-header i {
        font-size: 1.5rem;
        color: var(--warning);
    }

    .tips-header h3 {
        margin: 0;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .tip-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--bg-card);
        border-radius: var(--radius);
    }

    .tip-item i {
        color: var(--primary);
    }

    .tip-item span {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .welcome-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }

        .bill-item {
            flex-wrap: wrap;
        }

        .bill-details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
        }
    }
</style>

<script>
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