<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Edit Customer';

// Get customer ID
$customerId = sanitize($_GET['id'] ?? '');

if (empty($customerId)) {
    setMessage('Invalid customer ID', 'error');
    redirect('customers.php');
}

// Get customer details
$customer = getCustomerById($customerId);

if (!$customer) {
    setMessage('Customer not found', 'error');
    redirect('customers.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $meterNumber = sanitize($_POST['meter_number'] ?? '');

    $errors = [];

    // Validation
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if (empty($meterNumber)) {
        $errors[] = 'Meter number is required';
    } else {
        // Check if meter number exists for other customers
        $sql = "SELECT id FROM customers WHERE meter_number = '$meterNumber' AND id != '$customerId'";
        if (getRow($sql)) {
            $errors[] = 'Meter number already exists';
        }
    }

    if (empty($errors)) {
        // Update customer
        $sql = "UPDATE customers SET 
                first_name = '$firstName', 
                last_name = '$lastName', 
                phone = '$phone', 
                address = '$address', 
                meter_number = '$meterNumber' 
                WHERE id = '$customerId'";

        if (query($sql)) {
            setMessage('Customer updated successfully', 'success');
            redirect('customers.php');
        } else {
            $errors[] = 'Failed to update customer';
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
        <h1 class="page-title">Edit Customer</h1>
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
        <h1>Edit Customer</h1>
        <div class="page-actions">
            <a href="customers.php" class="btn btn-secondary">
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
        <form method="POST" class="auth-form" style="max-width: 100%;">
            <h3 style="margin-bottom: 1.5rem;">Personal Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="First name" required value="<?php echo htmlspecialchars($customer['first_name']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label required">Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Last name" required value="<?php echo htmlspecialchars($customer['last_name']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="Phone number" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label required">Meter Number</label>
                    <input type="text" name="meter_number" class="form-control" placeholder="Enter meter number" required value="<?php echo htmlspecialchars($customer['meter_number']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" placeholder="Enter address"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="customers.php" class="btn btn-secondary">Cancel</a>
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