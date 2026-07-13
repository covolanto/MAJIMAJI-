<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Usage Statistics';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

$usageStats = getCustomerUsageStats($customer['id']);
$monthlyData = getCustomerMonthlyConsumption($customer['id'], 12);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Usage Statistics</h1>
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
            <h1>Your Water Usage 📊</h1>
            <p>Track your water consumption patterns and manage your bills effectively.</p>
        </div>
    </div>

    <!-- Usage Stats -->
    <div class="usage-stats-grid">
        <div class="usage-stat-card">
            <div class="usage-stat-value"><?php echo number_format($usageStats['total_consumption']); ?> m³</div>
            <div class="usage-stat-label">Total Consumption</div>
        </div>
        <div class="usage-stat-card">
            <div class="usage-stat-value"><?php echo $usageStats['avg_consumption']; ?> m³</div>
            <div class="usage-stat-label">Monthly Average</div>
        </div>
        <div class="usage-stat-card">
            <div class="usage-stat-value"><?php echo formatCurrency($usageStats['total_spent']); ?></div>
            <div class="usage-stat-label">Total Billed</div>
        </div>
        <div class="usage-stat-card">
            <div class="usage-stat-value"><?php echo formatCurrency($usageStats['total_paid']); ?></div>
            <div class="usage-stat-label">Total Paid</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="dashboard-grid">
        <!-- Consumption Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-bar"></i> Monthly Consumption</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="consumptionChart"></canvas>
            </div>
        </div>

        <!-- Amount Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> Monthly Bills</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="amountChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="dashboard-grid" style="margin-top: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Summary</h3>
            </div>
            <div class="summary-stats">
                <div class="summary-item">
                    <div class="summary-label">Total Bills</div>
                    <div class="summary-value"><?php echo $usageStats['bill_count']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Outstanding Amount</div>
                    <div class="summary-value warning"><?php echo formatCurrency($usageStats['outstanding']); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
            </div>
            <div class="tips-grid" style="grid-template-columns: 1fr;">
                <div class="tip-item">
                    <i class="fas fa-shower"></i>
                    <span>Take shorter showers to save water</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-faucet"></i>
                    <span>Fix leaky faucets immediately</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-tint"></i>
                    <span>Run full loads only in dishwasher/washing machine</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Consumption Chart
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    const consumptionData = {
        labels: <?php echo json_encode(array_keys($monthlyData)); ?>,
        datasets: [{
            label: 'Consumption (m³)',
            data: <?php echo json_encode(array_column($monthlyData, 'consumption')); ?>,
            backgroundColor: 'rgba(14, 165, 233, 0.8)',
            borderColor: '#0ea5e9',
            borderWidth: 2,
            borderRadius: 6
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

    // Amount Chart
    const amountCtx = document.getElementById('amountChart').getContext('2d');
    const amountData = {
        labels: <?php echo json_encode(array_keys($monthlyData)); ?>,
        datasets: [{
            label: 'Amount ($)',
            data: <?php echo json_encode(array_column($monthlyData, 'amount')); ?>,
            backgroundColor: 'rgba(6, 182, 212, 0.1)',
            borderColor: '#06b6d4',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#06b6d4',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    };

    new Chart(amountCtx, {
        type: 'line',
        data: amountData,
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
                            return '$' + value;
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

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        location.reload();
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

<style>
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
        color: var(--text-primary);
    }

    .summary-value.warning {
        color: var(--warning);
    }
</style>