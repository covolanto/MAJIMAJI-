<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('customer/dashboard.php');
    }
}

$pageTitle = 'Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = '$email' OR username = '$email'";
        $user = getRow($sql);

        if ($user && verifyPassword($password, $user['password'])) {
            login($user['id'], $user['username'], $user['email'], $user['role']);

            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('customer/dashboard.php');
            }
        } else {
            $errors[] = 'Invalid email/username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - AquaBill</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo">
                    <i class="fas fa-water"></i>
                    <span>AquaBill</span>
                </div>
                <p>Water Billing Management System</p>
            </div>

            <h2 class="auth-title">Welcome Back</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" autocomplete="off">
                <div class="form-group">
                    <label class="form-label required">Email or Username</label>
                    <input type="text" name="email" class="form-control" placeholder="Enter your email or username" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>

            <div style="margin-top: 1.5rem; padding: 1rem; background: var(--bg-input); border-radius: var(--radius); font-size: 0.8125rem;">
                <strong>Demo Credentials:</strong>
                <br>Admin: admin@aquabill.com / admin123
                <br>Customer: customer@aquabill.com / customer123
            </div>
        </div>
    </div>
</body>

</html>