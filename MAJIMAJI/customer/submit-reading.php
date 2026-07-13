<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Submit Meter Reading';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $readingValue = sanitize($_POST['reading_value'] ?? '');
    $readingDate = sanitize($_POST['reading_date'] ?? date('Y-m-d'));
    $notes = sanitize($_POST['notes'] ?? '');

    $errors = [];

    if (empty($readingValue)) {
        $errors[] = 'Meter reading is required';
    } elseif (!is_numeric($readingValue) || $readingValue < 0) {
        $errors[] = 'Please enter a valid meter reading';
    } elseif (!empty($customer['last_reading']) && $readingValue < $customer['last_reading']) {
        $errors[] = 'Meter reading cannot be less than previous reading (' . $customer['last_reading'] . ')';
    }

    if (empty($errors)) {
        $result = submitMeterReading($customer['id'], $readingValue, $readingDate, $notes);

        if ($result['success']) {
            // Update last reading
            $sql = "UPDATE customers SET last_reading = '$readingValue', last_reading_date = '$readingDate' WHERE id = '" . $customer['id'] . "'";
            query($sql);

            setMessage($result['message'], 'success');
            redirect('submit-reading.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Get previous readings
$readings = getMeterReadingsByCustomer($customer['id'], 5);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Submit Meter Reading</h1>
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
        <h1>Submit Meter Reading</h1>
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

    <!-- Success Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Meter Info Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Your Meter Information</h3>
        </div>
        <div class="meter-info-grid">
            <div class="meter-info-item">
                <div class="meter-info-label">Meter Number</div>
                <div class="meter-info-value"><?php echo $customer['meter_number']; ?></div>
            </div>
            <div class="meter-info-item">
                <div class="meter-info-label">Meter Type</div>
                <div class="meter-info-value"><?php echo ucfirst($customer['meter_type'] ?? 'Analog'); ?></div>
            </div>
            <div class="meter-info-item">
                <div class="meter-info-label">Meter Status</div>
                <div class="meter-info-value">
                    <span class="badge badge-<?php echo ($customer['meter_status'] ?? 'active') === 'active' ? 'paid' : 'pending'; ?>">
                        <?php echo ucfirst($customer['meter_status'] ?? 'Active'); ?>
                    </span>
                </div>
            </div>
            <div class="meter-info-item">
                <div class="meter-info-label">Last Reading</div>
                <div class="meter-info-value"><?php echo $customer['last_reading'] ?? '0'; ?> m³</div>
            </div>
            <div class="meter-info-item">
                <div class="meter-info-label">Last Reading Date</div>
                <div class="meter-info-value"><?php echo !empty($customer['last_reading_date']) ? formatDate($customer['last_reading_date']) : 'N/A'; ?></div>
            </div>
            <div class="meter-info-item">
                <div class="meter-info-label">Meter Location</div>
                <div class="meter-info-value"><?php echo $customer['meter_location'] ?? 'Not specified'; ?></div>
            </div>
        </div>
    </div>

    <!-- Submit Reading Form -->
    <div class="card" style="max-width: 600px;">
        <form method="POST">
            <h3 style="margin-bottom: 1.5rem;">Submit New Reading</h3>

            <div class="form-group">
                <label class="form-label required">Current Meter Reading (m³)</label>
                <input type="number" name="reading_value" class="form-control"
                    placeholder="Enter meter reading" required min="0"
                    value="<?php echo isset($_POST['reading_value']) ? htmlspecialchars($_POST['reading_value']) : ''; ?>">
                <small style="color: var(--text-muted);">Previous reading: <?php echo $customer['last_reading'] ?? '0'; ?> m³</small>
            </div>

            <div class="form-group">
                <label class="form-label required">Reading Date</label>
                <input type="date" name="reading_date" class="form-control" required
                    value="<?php echo isset($_POST['reading_date']) ? htmlspecialchars($_POST['reading_date']) : date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="Any additional notes..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Submit Reading
                </button>
            </div>
        </form>
    </div>

    <!-- Previous Readings -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Recent Readings</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reading</th>
                        <th>Month</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($readings)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No readings submitted yet</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($readings as $reading): ?>
                            <tr>
                                <td><?php echo formatDate($reading['reading_date']); ?></td>
                                <td><?php echo $reading['reading_value']; ?> m³</td>
                                <td><?php echo date('F Y', strtotime($reading['reading_month'] . '-01')); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo $reading['status'] === 'approved' ? 'paid' : ($reading['status'] === 'pending' ? 'pending' : 'overdue');
                                                                ?>">
                                        <?php echo ucfirst($reading['status']); ?>
                                    </span>
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

<style>
    .meter-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1rem 0;
    }

    .meter-info-item {
        background: var(--bg-input);
        padding: 1rem;
        border-radius: var(--radius);
    }

    .meter-info-label {
        font-size: 0.8125rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .meter-info-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .badge-pending {
        background: var(--warning-light);
        color: var(--warning);
    }

    .badge-paid {
        background: var(--success-light);
        color: var(--success);
    }
</style>

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

    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
</script>