<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Activity Log';
$activityLogs = getAdminActivityLogs(100);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Activity Log</h1>
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
        <h1>Activity Log</h1>
    </div>

    <!-- Activity Log -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Recent Activities</h3>
        </div>

        <?php if (empty($activityLogs)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No activity yet</h3>
                <p>Admin activities will appear here</p>
            </div>
        <?php else: ?>
            <div class="activity-log-list">
                <?php foreach ($activityLogs as $log): ?>
                    <div class="activity-log-item <?php echo strtolower(str_replace(' ', '-', $log['action'])); ?>">
                        <div class="activity-log-icon <?php echo strtolower(str_replace(' ', '-', $log['action'])); ?>">
                            <?php
                            $icon = 'fa-circle';
                            if (stripos($log['action'], 'login') !== false) {
                                $icon = 'fa-sign-in-alt';
                            } elseif (stripos($log['action'], 'logout') !== false) {
                                $icon = 'fa-sign-out-alt';
                            } elseif (stripos($log['action'], 'profile') !== false || stripos($log['action'], 'password') !== false) {
                                $icon = 'fa-user-cog';
                            } elseif (stripos($log['action'], 'delete') !== false) {
                                $icon = 'fa-trash';
                            } elseif (stripos($log['action'], 'add') !== false || stripos($log['action'], 'create') !== false) {
                                $icon = 'fa-plus-circle';
                            } elseif (stripos($log['action'], 'update') !== false || stripos($log['action'], 'edit') !== false) {
                                $icon = 'fa-edit';
                            } elseif (stripos($log['action'], 'payment') !== false || stripos($log['action'], 'pay') !== false) {
                                $icon = 'fa-credit-card';
                            } elseif (stripos($log['action'], 'bill') !== false) {
                                $icon = 'fa-file-invoice-dollar';
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="activity-log-content">
                            <div class="activity-log-action">
                                <?php echo htmlspecialchars($log['action']); ?>
                                <span style="font-weight: 400; color: var(--text-muted);">
                                    by <?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?>
                                </span>
                            </div>
                            <?php if (!empty($log['details'])): ?>
                                <div class="activity-log-details">
                                    <?php echo htmlspecialchars($log['details']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="activity-log-meta">
                                <span><i class="fas fa-clock"></i> <?php echo formatDateTime($log['created_at']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($log['ip_address'] ?? 'Unknown'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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