<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Reports';
$stats = getDashboardStats();
$monthlyRevenue = getMonthlyRevenue(6);
$monthlyConsumption = getMonthlyConsumption(6);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Reports</h1>
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
        <h1>Analytics & Reports</h1>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value"><?php echo $stats['total_customers']; ?></div>
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
                <div class="stat-label">Overdue Bills</div>
                <div class="stat-value"><?php echo $stats['overdue_bills']; ?></div>
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

    <!-- Charts -->
    <div class="dashboard-grid">
        <!-- Revenue Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Revenue</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Consumption Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Consumption</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="consumptionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Outstanding Bills -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Outstanding Bills</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Meter No.</th>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $outstandingBills = getRows("SELECT b.*, c.first_name, c.last_name, c.meter_number 
                        FROM bills b 
                        JOIN customers c ON b.customer_id = c.id 
                        WHERE b.status IN ('pending', 'overdue') 
                        ORDER BY b.due_date ASC LIMIT 10");
                    ?>
                    <?php if (empty($outstandingBills)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No outstanding bills</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($outstandingBills as $bill): ?>
                            <tr>
                                <td><?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?></td>
                                <td><?php echo $bill['meter_number']; ?></td>
                                <td><?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?></td>
                                <td class="bill-amount"><?php echo formatCurrency($bill['amount']); ?></td>
                                <td><?php echo formatDate($bill['due_date']); ?></td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = {
        labels: <?php echo json_encode(array_keys($monthlyRevenue)); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_values($monthlyRevenue)); ?>,
            backgroundColor: 'rgba(14, 165, 233, 0.1)',
            borderColor: '#0ea5e9',
            borderWidth: 2,
            fill: true,
            tension: 0.4
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
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
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
            label: 'Consumption (m³)',
            data: <?php echo json_encode(array_values($monthlyConsumption)); ?>,
            backgroundColor: 'rgba(6, 182, 212, 0.1)',
            borderColor: '#06b6d4',
            borderWidth: 2,
            fill: true,
            tension: 0.4
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
                    beginAtZero: true
                }
            }
        }
    });

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