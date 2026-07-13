<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Dashboard';
$stats = getDashboardStats();
$recentBills = getRecentBills(5);
$recentPayments = getRecentPayments(5);
$recentCustomers = getRecentCustomers(5);
$monthlyRevenue = getMonthlyRevenue(6);
$monthlyConsumption = getMonthlyConsumption(6);

// Calculate additional stats
$overdueBills = $stats['pending_bills'];
$avgConsumption = getAverageConsumption();
$collectionRate = getCollectionRate();
$topCustomers = getTopCustomersByRevenue(5);
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
        <div class="header-date">
            <i class="fas fa-calendar-alt"></i>
            <span id="currentDate"></span>
        </div>
        <div class="theme-toggle" id="themeToggle">
            <i class="fas fa-sun sun"></i>
            <i class="fas fa-moon moon"></i>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <!-- Welcome Section -->
    <div class="admin-welcome">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo getUsername(); ?>! 👋</h1>
            <p>Here's what's happening with your water billing system today.</p>
        </div>
        <div class="quick-actions-header">
            <a href="bill-add.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> New Bill
            </a>
            <a href="customer-add.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Add Customer
            </a>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value"><?php echo $stats['total_customers']; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Active
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Bills</div>
                <div class="stat-value"><?php echo $stats['total_bills']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Pending Bills</div>
                <div class="stat-value"><?php echo $stats['pending_bills']; ?></div>
                <div class="stat-change negative">
                    <i class="fas fa-exclamation-circle"></i> Needs attention
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value"><?php echo formatCurrency($stats['total_revenue']); ?></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="stats-grid">
        <div class="stat-card mini">
            <div class="stat-icon info">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Collection Rate</div>
                <div class="stat-value"><?php echo $collectionRate; ?>%</div>
            </div>
        </div>

        <div class="stat-card mini">
            <div class="stat-icon primary">
                <i class="fas fa-tint"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Avg. Consumption</div>
                <div class="stat-value"><?php echo $avgConsumption; ?> m³</div>
            </div>
        </div>

        <div class="stat-card mini">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">This Month Revenue</div>
                <div class="stat-value"><?php echo formatCurrency($stats['this_month_revenue']); ?></div>
            </div>
        </div>

        <div class="stat-card mini">
            <div class="stat-icon success">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Paid Bills</div>
                <div class="stat-value"><?php echo $stats['paid_bills']; ?></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="dashboard-grid">
        <!-- Revenue Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> Revenue Trend</h3>
                <div class="chart-legend">
                    <span><i class="fas fa-circle"></i> Last 6 months</span>
                </div>
            </div>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Consumption Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-bar"></i> Consumption Trend</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="consumptionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="dashboard-grid">
        <!-- Recent Bills -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice"></i> Recent Bills</h3>
                <a href="bills.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="table-container" style="box-shadow: none; background: transparent; border: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Month</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBills)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No bills found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentBills as $bill): ?>
                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar">
                                                <?php echo strtoupper(substr($bill['first_name'], 0, 1)); ?>
                                            </div>
                                            <span><?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M Y', strtotime($bill['billing_month'] . '-01')); ?></td>
                                    <td class="bill-amount"><?php echo formatCurrency($bill['amount']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $bill['status']; ?>">
                                            <?php echo ucfirst($bill['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Recent Payments</h3>
                <a href="payments.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="table-container" style="box-shadow: none; background: transparent; border: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No payments found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar success">
                                                <?php echo strtoupper(substr($payment['first_name'], 0, 1)); ?>
                                            </div>
                                            <span><?php echo $payment['first_name'] . ' ' . $payment['last_name']; ?></span>
                                        </div>
                                    </td>
                                    <td class="bill-amount"><?php echo formatCurrency($payment['amount']); ?></td>
                                    <td><span class="text-muted"><?php echo ucfirst($payment['payment_method']); ?></span></td>
                                    <td><?php echo date('M d', strtotime($payment['payment_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Row -->
    <div class="dashboard-grid">
        <!-- Recent Customers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> New Customers</h3>
                <a href="customers.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="customer-list">
                <?php if (empty($recentCustomers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No customers yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentCustomers as $customer): ?>
                        <div class="customer-list-item">
                            <div class="customer-avatar primary">
                                <?php echo strtoupper(substr($customer['first_name'], 0, 1)); ?>
                            </div>
                            <div class="customer-info">
                                <div class="customer-name"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></div>
                                <div class="customer-meta">
                                    <span><i class="fas fa-hashtag"></i> <?php echo $customer['meter_number']; ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($customer['created_at'])); ?></span>
                                </div>
                            </div>
                            <a href="customer-edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-icon btn-outline">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Customers by Revenue -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy"></i> Top Customers</h3>
            </div>
            <div class="top-customers-list">
                <?php if (empty($topCustomers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>No data yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($topCustomers as $index => $customer): ?>
                        <div class="top-customer-item">
                            <div class="rank <?php echo $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')); ?>">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="customer-avatar primary">
                                <?php echo strtoupper(substr($customer['first_name'], 0, 1)); ?>
                            </div>
                            <div class="customer-info">
                                <div class="customer-name"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></div>
                                <div class="customer-meta"><?php echo $customer['meter_number']; ?></div>
                            </div>
                            <div class="customer-revenue">
                                <?php echo formatCurrency($customer['total_paid']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links-grid">
        <a href="customers.php" class="quick-link-card">
            <i class="fas fa-users"></i>
            <span>Manage Customers</span>
        </a>
        <a href="bills.php" class="quick-link-card">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>>
                </aManage Bills</span>
                <a href="payments.php" class="quick-link-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>View Payments</span>
                </a>
                <a href="reports.php" class="quick-link-card">
                    <i class="fas fa-chart-pie"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="quick-link-card">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
    </div>
</main>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<style>
    .admin-welcome {
        background: linear-gradient(135deg, #0891b2 0%, #6366f1 100%);
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .admin-welcome::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    }

    .admin-welcome::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 5%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
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
        font-size: 0.9375rem;
    }

    .quick-actions-header {
        display: flex;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .quick-actions-header .btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .quick-actions-header .btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .header-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .stat-card.mini {
        padding: 1.25rem;
    }

    .stat-card.mini .stat-icon {
        width: 44px;
        height: 44px;
        font-size: 1.25rem;
    }

    .stat-card.mini .stat-value {
        font-size: 1.5rem;
    }

    .customer-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .customer-avatar {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8125rem;
    }

    .customer-avatar.success {
        background: linear-gradient(135deg, var(--success), #34d399);
    }

    .customer-avatar.primary {
        background: linear-gradient(135deg, var(--primary), var(--accent));
    }

    .customer-info {
        flex: 1;
    }

    .customer-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .customer-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .customer-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .customer-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .customer-list-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-input);
        border-radius: var(--radius);
        transition: all var(--transition-fast);
    }

    .customer-list-item:hover {
        background: var(--border-color);
        transform: translateX(4px);
    }

    .customer-revenue {
        font-weight: 700;
        color: var(--success);
        font-family: 'Poppins', sans-serif;
    }

    .top-customers-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .top-customer-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-input);
        border-radius: var(--radius);
    }

    .rank {
        width: 28px;
        height: 28px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8125rem;
        background: var(--border-color);
        color: var(--text-muted);
    }

    .rank.gold {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
    }

    .rank.silver {
        background: linear-gradient(135deg, #94a3b8, #64748b);
        color: white;
    }

    .rank.bronze {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
    }

    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .quick-link-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.5rem 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        transition: all var(--transition);
        text-align: center;
    }

    .quick-link-card:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .quick-link-card i {
        font-size: 1.75rem;
        color: var(--primary);
    }

    .quick-link-card span {
        font-weight: 500;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .chart-legend {
        display: flex;
        gap: 1rem;
        font-size: 0.8125rem;
        color: var(--text-muted);
    }

    .chart-legend i {
        font-size: 0.5rem;
        color: var(--primary);
    }

    .text-muted {
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .admin-welcome {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .quick-actions-header {
            flex-direction: column;
            width: 100%;
        }

        .quick-actions-header .btn {
            width: 100%;
            justify-content: center;
        }

        .quick-links-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Set current date
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    const revenueData = {
        labels: <?php echo json_encode(array_keys($monthlyRevenue)); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_values($monthlyRevenue)); ?>,
            backgroundColor: 'rgba(14, 165, 233, 0.1)',
            borderColor: '#0ea5e9',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#0ea5e9',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    };

    new Chart(revenueCtx, {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                }
            }
        }
    });

    // Consumption Chart
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');
    const consumptionData = {
        labels: <?php echo json_encode(array_keys($monthlyConsumption)); ?>,
        datasets: [{
            label: 'Consumption',
            data: <?php echo json_encode(array_values($monthlyConsumption)); ?>,
            backgroundColor: [
                'rgba(14, 165, 233, 0.8)',
                'rgba(6, 182, 212, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderWidth: 0,
            borderRadius: 8
        }]
    };

    new Chart(consumptionCtx, {
        type: 'bar',
        data: consumptionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: function(value) {
                            return value + ' m³';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                }
            }
        }
    });



    // Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
    });

    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
</script>