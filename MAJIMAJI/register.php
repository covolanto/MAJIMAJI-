<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('customer/dashboard.php');
    }
}

$pageTitle = 'Register';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'customer');
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $meterNumber = sanitize($_POST['meter_number'] ?? '');

    $errors = [];

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } else {
        // Check if username exists
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
        // Check if email exists
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

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if ($role === 'admin') {
        // Only allow admin registration with special key
        $adminKey = $_POST['admin_key'] ?? '';
        if ($adminKey !== 'ADMIN2024') {
            $errors[] = 'Invalid admin registration key';
            $role = 'customer';
        }
    }

    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if ($role === 'customer') {
        if (empty($meterNumber)) {
            $errors[] = 'Meter number is required';
        } else {
            // Check if meter number exists
            $sql = "SELECT id FROM customers WHERE meter_number = '$meterNumber'";
            if (getRow($sql)) {
                $errors[] = 'Meter number already exists';
            }
        }
    }

    if (empty($errors)) {
        // Insert user
        $hashedPassword = hashPassword($password);
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashedPassword', '$role')";

        if (query($sql)) {
            $userId = lastInsertId();

            // If customer, insert customer details
            if ($role === 'customer') {
                $sql = "INSERT INTO customers (user_id, first_name, last_name, phone, address, meter_number) 
                        VALUES ('$userId', '$firstName', '$lastName', '$phone', '$address', '$meterNumber')";
                query($sql);
            }

            setMessage('Registration successful! Please login.', 'success');
            redirect('index.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AquaBill</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .theme-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .customer-fields {
            display: none;
        }

        .show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="auth-card">
        <!-- Theme Toggle -->
        <div class="theme-toggle" onclick="toggleTheme()">
            <i class="fas fa-sun sun"></i>
            <i class="fas fa-moon moon"></i>
        </div>

        <div class="auth-logo">
            <div class="logo">
                <i class="fas fa-water"></i>
                <span>AquaBill</span>
            </div>
            <p>Water Billing Management System</p>
        </div>

        <h2 class="auth-title">Create Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" autocomplete="off">
            <div class="form-group">
                <label class="form-label required">Register As</label>
                <select name="role" class="form-control" id="roleSelect" onchange="toggleRoleFields()">
                    <option value="customer" <?php echo (isset($role) && $role === 'customer') ? 'selected' : ''; ?>>Customer</option>
                    <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

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

            <div class="form-group">
                <label class="form-label required">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label required">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>
            </div>

            <!-- Admin Key (hidden by default) -->
            <div class="form-group admin-fields" style="display: none;">
                <label class="form-label required">Admin Registration Key</label>
                <input type="text" name="admin_key" class="form-control" placeholder="Enter admin key">
            </div>

            <!-- Customer specific fields -->
            <div class="customer-fields">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Meter Number</label>
                        <input type="text" name="meter_number" class="form-control" placeholder="Enter meter number" value="<?php echo isset($meterNumber) ? htmlspecialchars($meterNumber) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Phone number" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" placeholder="Enter your address"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i>
                Register
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="index.php">Login here</a></p>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        function toggleRoleFields() {
            const role = document.getElementById('roleSelect').value;
            const customerFields = document.querySelector('.customer-fields');
            const adminFields = document.querySelector('.admin-fields');

            if (role === 'admin') {
                customerFields.style.display = 'none';
                adminFields.style.display = 'block';
            } else {
                customerFields.style.display = 'block';
                adminFields.style.display = 'none';
            }
        }

        // Check saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Initial toggle
        toggleRoleFields();
    </script>
</body>

</html>