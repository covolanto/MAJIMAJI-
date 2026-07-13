<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Customers';

// Handle search
$search = sanitize($_GET['search'] ?? '');
$customers = !empty($search) ? searchCustomers($search) : getAllCustomers();

// Handle delete
if (isset($_GET['delete'])) {
    $customerId = sanitize($_GET['delete']);

    // Get user ID first
    $sql = "SELECT user_id FROM customers WHERE id = '$customerId'";
    $customer = getRow($sql);

    if ($customer) {
        // Delete customer
        $sql = "DELETE FROM customers WHERE id = '$customerId'";
        query($sql);

        // Delete user
        $sql = "DELETE FROM users WHERE id = '" . $customer['user_id'] . "'";
        query($sql);

        setMessage('Customer deleted successfully', 'success');
    }

    redirect('customers.php');
}
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Customers</h1>
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
        <h1>Customer Management</h1>
        <div class="page-actions">
            <a href="customer-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Customer
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <form method="GET" class="search-form">
        <div class="search-input">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="customers.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>

    <!-- Customers Table -->
    <div class="card">
        <div class="table-container">
            <table class="table" id="customersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Meter No.</th>
                        <th>Address</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h3>No customers found</h3>
                                    <p>Start by adding your first customer</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['id']; ?></td>
                                <td>
                                    <strong><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></strong>
                                </td>
                                <td><?php echo $customer['email']; ?></td>
                                <td><?php echo $customer['phone'] ?: '-'; ?></td>
                                <td><?php echo $customer['meter_number']; ?></td>
                                <td><?php echo $customer['address'] ?: '-'; ?></td>
                                <td><?php echo formatDate($customer['created_at']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="customer-edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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