<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Generate Bill';

// Get customers
$customers = getAllCustomers();
$billingMonths = getBillingMonths();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = sanitize($_POST['customer_id'] ?? '');
    $billingMonth = sanitize($_POST['billing_month'] ?? '');
    $consumption = sanitize($_POST['consumption'] ?? '');
    $dueDate = sanitize($_POST['due_date'] ?? '');

    $errors = [];

    // Validation
    if (empty($customerId)) {
        $errors[] = 'Please select a customer';
    }

    if (empty($billingMonth)) {
        $errors[] = 'Please select a billing month';
    }

    if (empty($consumption) || $consumption < 0) {
        $errors[] = 'Please enter valid consumption';
    }

    if (empty($dueDate)) {
        $errors[] = 'Please select a due date';
    }

    if (empty($errors)) {
        // Check if bill already exists
        if (billExists($customerId, $billingMonth)) {
            $errors[] = 'Bill already exists for this customer and month';
        } else {
            // Calculate amount
            $amount = calculateBill($consumption);
            $rate = getRate($consumption);

            // Insert bill
            $sql = "INSERT INTO bills (customer_id, billing_month, consumption, rate, amount, status, due_date) 
                    VALUES ('$customerId', '$billingMonth', '$consumption', '$rate', '$amount', 'pending', '$dueDate')";

            if (query($sql)) {
                setMessage('Bill generated successfully', 'success');
                redirect('bills.php');
            } else {
                $errors[] = 'Failed to generate bill';
            }
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
        <h1 class="page-title">Generate Bill</h1>
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
        <h1>Generate Monthly Bill</h1>
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
                <label class="form-label required">Customer</label>
                <select name="customer_id" class="form-control" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>">
                            <?php echo $customer['first_name'] . ' ' . $customer['last_name'] . ' - ' . $customer['meter_number']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Billing Month</label>
                <select name="billing_month" class="form-control" required>
                    <option value="">Select Month</option>
                    <?php foreach ($billingMonths as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Water Consumption (m³)</label>
                <input type="number" name="consumption" class="form-control" placeholder="Enter consumption in cubic meters" min="0" required id="consumptionInput">
                <small style="color: var(--text-muted);">Rate: $2.50/m³ (0-10), $3.00/m³ (11-30), $3.50/m³ (31-50), $4.00/m³ (50+)</small>
            </div>

            <div class="form-group">
                <label class="form-label required">Due Date</label>
                <input type="date" name="due_date" class="form-control" required>
            </div>

            <!-- Bill Preview -->
            <div class="card" style="background: var(--bg-input); margin-top: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">Bill Preview</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Consumption:</span>
                    <span id="previewConsumption">0 m³</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Rate:</span>
                    <span id="previewRate">$2.50/m³</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Meter Charge:</span>
                    <span>$5.00</span>
                </div>
                <hr style="border-color: var(--border-color); margin: 1rem 0;">
                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.25rem;">
                    <span>Total Amount:</span>
                    <span id="previewAmount">$5.00</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-invoice-dollar"></i> Generate Bill
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

    // Bill Preview Calculation
    const consumptionInput = document.getElementById('consumptionInput');
    const previewConsumption = document.getElementById('previewConsumption');
    const previewRate = document.getElementById('previewRate');
    const previewAmount = document.getElementById('previewAmount');

    consumptionInput.addEventListener('input', function() {
        const consumption = parseInt(this.value) || 0;
        previewConsumption.textContent = consumption + ' m³';

        let rate, amount;
        if (consumption <= 10) {
            rate = 2.50;
            amount = (consumption * 2.50) + 5.00;
        } else if (consumption <= 30) {
            rate = 3.00;
            amount = (10 * 2.50) + ((consumption - 10) * 3.00) + 5.00;
        } else if (consumption <= 50) {
            rate = 3.50;
            amount = (10 * 2.50) + (20 * 3.00) + ((consumption - 30) * 3.50) + 5.00;
        } else {
            rate = 4.00;
            amount = (10 * 2.50) + (20 * 3.00) + (20 * 3.50) + ((consumption - 50) * 4.00) + 5.00;
        }

        previewRate.textContent = '$' + rate.toFixed(2) + '/m³';
        previewAmount.textContent = '$' + amount.toFixed(2);
    });
</script>