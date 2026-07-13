<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Bills';

// Handle search and filters
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$month = sanitize($_GET['month'] ?? '');

$bills = searchBills($search, $status, $month);
$billingMonths = getBillingMonths();

// Handle delete
if (isset($_GET['delete'])) {
    $billId = sanitize($_GET['delete']);
    $sql = "DELETE FROM bills WHERE id = '$billId'";
    query($sql);

    setMessage('Bill deleted successfully', 'success');
    redirect('bills.php');
}

// Handle mark as paid
if (isset($_GET['pay'])) {
    $billId = sanitize($_GET['pay']);

    // Get bill details
    $sql = "SELECT * FROM bills WHERE id = '$billId'";
    $bill = getRow($sql);

    if ($bill && $bill['status'] !== 'paid') {
        // Update bill status
        $sql = "UPDATE bills SET status = 'paid' WHERE id = '$billId'";
        query($sql);

        // Record payment
        $transactionId = generateTransactionId();
        $sql = "INSERT INTO payments (bill_id, amount, payment_method, transaction_id, status) 
                VALUES ('$billId', '" . $bill['amount'] . "', 'cash', '$transactionId', 'completed')";
        query($sql);

        setMessage('Bill marked as paid', 'success');
    }

    redirect('bills.php');
}
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Bills</h1>
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
        <h1>Bill Management</h1>
        <div class="page-actions">
            <a href="bill-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Generate Bill
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="search-input" style="flex: 1;">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="Search by customer name or meter number..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
        </select>

        <select name="month" class="form-control">
            <option value="">All Months</option>
            <?php foreach ($billingMonths as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo $month === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-filter"></i> Filter
        </button>

        <?php if (!empty($search) || !empty($status) || !empty($month)): ?>
            <a href="bills.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>

    <!-- Bills Table -->
    <div class="card">
        <div class="table-container">
            <table class="table" id="billsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Meter No.</th>
                        <th>Month</th>
                        <th>Consumption</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <h3>No bills found</h3>
                                    <p>Start by generating your first bill</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?php echo $bill['id']; ?></td>
                                <td>
                                    <strong><?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?></strong>
                                </td>
                                <td><?php echo $bill['meter_number']; ?></td>
                                <td><?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?></td>
                                <td><?php echo $bill['consumption']; ?> m³</td>
                                <td class="bill-amount"><?php echo formatCurrency($bill['amount']); ?></td>
                                <td><?php echo formatDate($bill['due_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $bill['status']; ?>">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="bill-edit.php?id=<?php echo $bill['id']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($bill['status'] !== 'paid'): ?>
                                            <a href="?pay=<?php echo $bill['id']; ?>" class="btn btn-sm btn-success" title="Mark as Paid" onclick="return confirm('Mark this bill as paid?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $bill['id']; ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
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