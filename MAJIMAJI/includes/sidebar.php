<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userRole = getUserRole();
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-water"></i>
            <span>NYEWASCO</span>

        </div>
        <button class="close-sidebar" id="closeSidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <?php if ($userRole === 'admin'): ?>
            <a href="../admin/dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../admin/customers.php" class="nav-item <?php echo $currentPage === 'customers' || $currentPage === 'customer-add' || $currentPage === 'customer-edit' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            <a href="../admin/bills.php" class="nav-item <?php echo $currentPage === 'bills' || $currentPage === 'bill-add' || $currentPage === 'bill-edit' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Bills</span>
            </a>
            <a href="../admin/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
            <a href="../admin/reports.php" class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="../admin/activity-log.php" class="nav-item <?php echo $currentPage === 'activity-log' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Activity Log</span>
            </a>
            <a href="../admin/settings.php" class="nav-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        <?php else: ?>
            <a href="../customer/dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../customer/submit-reading.php" class="nav-item <?php echo $currentPage === 'submit-reading' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Submit Reading</span>
            </a>
            <a href="../customer/bills.php" class="nav-item <?php echo $currentPage === 'bills' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>My Bills</span>
            </a>
            <a href="../customer/usage.php" class="nav-item <?php echo $currentPage === 'usage' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Usage Stats</span>
            </a>
            <a href="../customer/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payment History</span>
            </a>
            <a href="../customer/support.php" class="nav-item <?php echo $currentPage === 'support' ? 'active' : ''; ?>">
                <i class="fas fa-life-ring"></i>
                <span>Support</span>
            </a>
            <a href="../customer/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Profile</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo getUsername(); ?></span>
                <span class="user-role"><?php echo ucfirst($userRole); ?></span>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>