<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Profile';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

// Get user info
$userId = getUserId();
$sql = "SELECT * FROM users WHERE id = '$userId'";
$user = getRow($sql);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    $errors = [];

    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if (empty($errors)) {
        // Update customer
        $sql = "UPDATE customers SET 
                first_name = '$firstName', 
                last_name = '$lastName', 
                phone = '$phone', 
                address = '$address' 
                WHERE id = '" . $customer['id'] . "'";

        if (query($sql)) {
            setMessage('Profile updated successfully', 'success');
            redirect('profile.php');
        } else {
            $errors[] = 'Failed to update profile';
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($current_password)) {
        $errors[] = 'Current password is required';
    }

    if (empty($new_password)) {
        $errors[] = 'New password is required';
    }

    if (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
    }

    if (empty($errors)) {
        $result = changePassword($userId, $current_password, $new_password);
        if ($result['success']) {
            setMessage($result['message'], 'success');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Refresh customer data
$customer = getCustomerByUserId(getUserId());
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Profile</h1>
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
        <h1>My Profile</h1>
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

    <!-- Profile Form -->
    <div class="settings-grid" style="max-width: 100%;">
        <!-- Personal Information -->
        <div class="card">
            <form method="POST">
                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-user"></i> Personal Information</h3>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                    <small style="color: var(--text-muted);">Email cannot be changed</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Meter Number</label>
                    <input type="text" class="form-control" value="<?php echo $customer['meter_number']; ?>" disabled>
                </div>

                <hr style="margin: 1.5rem 0; border-color: var(--border-color);">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card">
            <form method="POST">
                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-lock"></i> Change Password</h3>

                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                    <small style="color: var(--text-muted);">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
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