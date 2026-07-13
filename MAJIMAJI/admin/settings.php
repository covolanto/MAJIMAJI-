<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Settings';
$admin = getAdminByUserId(getUserId());

// Get user info
$userId = getUserId();
$sql = "SELECT * FROM users WHERE id = '$userId'";
$user = getRow($sql);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    }

    if (empty($errors)) {
        // Check if username/email already exists for other users
        $sql = "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != '$userId'";
        $existing = getRow($sql);

        if ($existing) {
            $errors[] = 'Username or email already exists';
        } else {
            $sql = "UPDATE users SET username = '$username', email = '$email' WHERE id = '$userId'";
            if (query($sql)) {
                logActivity($userId, 'Profile Updated', 'Admin profile information updated');
                setMessage('Profile updated successfully', 'success');
                redirect('settings.php');
            } else {
                $errors[] = 'Failed to update profile';
            }
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
            logActivity($userId, 'Password Changed', 'Admin changed their password');
            setMessage($result['message'], 'success');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Refresh user data
$user = getRow("SELECT * FROM users WHERE id = '$userId'");
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Settings</h1>
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
        <h1>System Settings</h1>
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

    <!-- Settings Grid -->
    <div class="settings-grid">
        <!-- Profile Settings -->
        <div class="card">
            <form method="POST">
                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-user-circle"></i> Admin Profile</h3>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="<?php echo ucfirst($user['role'] ?? ''); ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Created</label>
                    <input type="text" class="form-control" value="<?php echo formatDate($user['created_at'] ?? ''); ?>" disabled>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Change -->
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

    <!-- Billing Rates -->
    <div class="card" style="margin-top: 1.5rem;">
        <form method="POST">
            <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-coins"></i> Billing Rates</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rate Tier 1 (0-10 m³)</label>
                    <input type="number" name="rate_tier1" class="form-control" value="2.50" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Rate Tier 2 (11-30 m³)</label>
                    <input type="number" name="rate_tier2" class="form-control" value="3.00" step="0.01">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rate Tier 3 (31-50 m³)</label>
                    <input type="number" name="rate_tier3" class="form-control" value="3.50" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Rate Tier 4 (50+ m³)</label>
                    <input type="number" name="rate_tier4" class="form-control" value="4.00" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Monthly Meter Charge</label>
                <input type="number" name="meter_charge" class="form-control" value="5.00" step="0.01">
            </div>

            <h3 style="margin: 2rem 0 1.5rem;">System Preferences</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Default Due Date (days)</label>
                    <input type="number" name="due_days" class="form-control" value="30" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-control">
                        <option value="KSH" selected>KSH (KSh)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<script>
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