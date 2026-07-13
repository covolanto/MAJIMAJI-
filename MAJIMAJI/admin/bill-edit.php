<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Edit Bill';

// Get bill ID
$billId = sanitize($_GET['id'] ?? '');

if (empty($billId)) {
    setMessage('Invalid bill ID', 'error');
    redirect('bills.php');
}

// Get bill details
$sql = "SELECT b.*, c.first_name, c.last_name 
        FROM bills b 
        JOIN customers c ON b.customer_id = c.id 
        WHERE b.id = '$billId'";
$bill = getRow($sql);

if (!$bill) {
    setMessage('Bill not found', 'error');
    redirect('bills.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consumption = sanitize($_POST['consumption'] ?? '');
    $dueDate = sanitize($_POST['due_date'] ?? '');
    $status = sanitize($_POST['status'] ?? 'pending');

    $errors = [];

    if (empty($consumption) || $consumption < 0) {
        $errors[] = 'Please enter valid consumption';
    }

    if (empty($dueDate)) {
        $errors[] = 'Please select a due date';
    }

    if (empty($errors)) {
        // Calculate amount
        $amount = calculateBill($consumption);
        $rate = getRate($consumption);

        // Update bill
        $sql = "UPDATE bills SET 
                consumption = '$consumption', 
                rate = '$rate', 
                amount = '$amount', 
                status = '$status',
                due_date = '$dueDate' 
                WHERE id = '$billId'";

        if (query($sql)) {
            setMessage('Bill updated successfully', 'success');
            redirect('bills.php');
        } else {
            $errors[] = 'Failed to update bill';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Edit Bill</h1>
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
        <h1>Edit Bill</h1>
        <div class="page-actions">
            <a href="bills.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card">
        <form method="POST" style="max-width: 600px;">
            <div class="form-group">
                <label class="form-label">Customer</label>
                <input type="text" class="form-control" value="<?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Billing Month</label>
                <input type="text" class="form-control" value="<?php echo date('F Y', strtotime($bill['billing_month'] . '-01')); ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label required">Water Consumption (m³)</label>
                <input type="number" name="consumption" class="form-control" value="<?php echo $bill['consumption']; ?>" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label required">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo $bill['due_date']; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label required">Status</label>
                <select name="status" class="form-control" required>
                    <option value="pending" <?php echo $bill['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $bill['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="overdue" <?php echo $bill['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="bills.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
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