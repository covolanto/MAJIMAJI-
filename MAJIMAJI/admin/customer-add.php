<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$pageTitle = 'Add Customer';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $meterNumber = sanitize($_POST['meter_number'] ?? '');

    $errors = [];

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } else {
        $sql = "SELECT id FROM users WHERE username = '$username'";
        if (getRow($sql)) {
            $errors[] = 'Username already exists';
        }
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        $sql = "SELECT id FROM users WHERE email = '$email'";
        if (getRow($sql)) {
            $errors[] = 'Email already exists';
        }
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if (empty($meterNumber)) {
        $errors[] = 'Meter number is required';
    } else {
        $sql = "SELECT id FROM customers WHERE meter_number = '$meterNumber'";
        if (getRow($sql)) {
            $errors[] = 'Meter number already exists';
        }
    }

    if (empty($errors)) {
        // Insert user
        $hashedPassword = hashPassword($password);
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashedPassword', 'customer')";

        if (query($sql)) {
            $userId = lastInsertId();

            // Insert customer
            $sql = "INSERT INTO customers (user_id, first_name, last_name, phone, address, meter_number) 
                    VALUES ('$userId', '$firstName', '$lastName', '$phone', '$address', '$meterNumber')";

            if (query($sql)) {
                setMessage('Customer added successfully', 'success');
                redirect('customers.php');
            } else {
                $errors[] = 'Failed to add customer';
            }
        } else {
            $errors[] = 'Failed to create user account';
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
        <h1 class="page-title">Add Customer</h1>
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
        <h1>Add New Customer</h1>
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
            <h3 style="margin-bottom: 1.5rem;">Account Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email address" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Meter Number</label>
                    <input type="text" name="meter_number" class="form-control" placeholder="Enter meter number" required value="<?php echo isset($meterNumber) ? htmlspecialchars($meterNumber) : ''; ?>">
                </div>
            </div>

            <h3 style="margin: 1.5rem 0;">Personal Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="First name" required value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label required">Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Last name" required value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="Phone number" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" placeholder="Enter address"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Customer
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