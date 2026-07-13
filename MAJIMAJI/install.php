<?php
// Database Installation Script
// Run this file once to set up the database

$pageTitle = 'Install AquaBill';

$message = '';
$error = '';

// Connect to MySQL server
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
} else {
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS aquabill_db";
    if ($conn->query($sql) === TRUE) {
        $conn->select_db('aquabill_db');

        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'customer') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);

        // Create customers table
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNIQUE NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            phone VARCHAR(20),
            address VARCHAR(255),
            meter_number VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Create bills table
        $sql = "CREATE TABLE IF NOT EXISTS bills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            billing_month VARCHAR(7) NOT NULL,
            consumption INT NOT NULL,
            rate DECIMAL(10,2) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
            due_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Create payments table
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cash', 'online', 'bank_transfer') DEFAULT 'cash',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            transaction_id VARCHAR(100),
            status ENUM('completed', 'failed', 'pending') DEFAULT 'completed',
            FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Create activity_logs table
        $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);

        // Create meter_readings table
        $sql = "CREATE TABLE IF NOT EXISTS meter_readings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            reading_value INT NOT NULL,
            reading_date DATE NOT NULL,
            reading_month VARCHAR(7) NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            submitted_by INT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Create support_tickets table
        $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            subject VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            category ENUM('billing', 'meter', 'payment', 'account', 'other') DEFAULT 'other',
            status ENUM('open', 'pending', 'resolved', 'closed') DEFAULT 'open',
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            assigned_to INT,
            response TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )";
        $conn->query($sql);

        // Add meter_info columns to customers table (if not exists)
        $result = $conn->query("SHOW COLUMNS FROM customers LIKE 'meter_type'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE customers ADD COLUMN meter_type VARCHAR(50) DEFAULT 'analog' AFTER meter_number");
            $conn->query("ALTER TABLE customers ADD COLUMN meter_location VARCHAR(255) AFTER meter_type");
            $conn->query("ALTER TABLE customers ADD COLUMN meter_install_date DATE AFTER meter_location");
            $conn->query("ALTER TABLE customers ADD COLUMN meter_status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active' AFTER meter_install_date");
            $conn->query("ALTER TABLE customers ADD COLUMN last_reading INT DEFAULT 0 AFTER meter_status");
            $conn->query("ALTER TABLE customers ADD COLUMN last_reading_date DATE AFTER last_reading");
        }

        // Check if admin exists
        $result = $conn->query("SELECT id FROM users WHERE username = 'admin'");
        if ($result->num_rows == 0) {
            // Create default admin user
            $username = 'admin';
            $email = 'admin@aquabill.com';
            $password = password_hash('admin123', PASSWORD_BCRYPT);
            $role = 'admin';

            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
            if ($conn->query($sql) === TRUE) {
                $message = 'Admin user created successfully!';
            } else {
                $error = 'Failed to create admin user: ' . $conn->error;
            }
        } else {
            $message = 'Database tables created! Admin user already exists.';
        }

        // Check if demo customer exists
        $result = $conn->query("SELECT id FROM users WHERE username = 'customer'");
        if ($result->num_rows == 0) {
            // Create demo customer user
            $username = 'customer';
            $email = 'customer@aquabill.com';
            $password = password_hash('customer123', PASSWORD_BCRYPT);
            $role = 'customer';

            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
            if ($conn->query($sql) === TRUE) {
                $userId = $conn->insert_id;

                // Create customer profile
                $firstName = 'John';
                $lastName = 'Doe';
                $phone = '555-1234';
                $address = '123 Main Street, City';
                $meterNumber = 'MTR001';

                $sql = "INSERT INTO customers (user_id, first_name, last_name, phone, address, meter_number) 
                        VALUES ('$userId', '$firstName', '$lastName', '$phone', '$address', '$meterNumber')";
                $conn->query($sql);

                $message .= '<br>Demo customer created!';
            }
        }
    } else {
        $error = "Error creating database: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - AquaBill</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0ea5e9;
        }

        .logo i {
            font-size: 2rem;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .success {
            background: #d1fae5;
            color: #059669;
            border-left: 4px solid #10b981;
        }

        .error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }

        .credentials {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .credentials h3 {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .credentials p {
            font-size: 0.875rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary {
            background: #0ea5e9;
            color: white;
        }

        .btn-primary:hover {
            background: #0284c7;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="logo">
            <i class="fas fa-water"></i>
            <span>AquaBill</span>
        </div>

        <h1>Installation</h1>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>

            <div class="credentials">
                <h3>Login Credentials:</h3>
                <p><strong>Admin:</strong> admin@aquabill.com / admin123</p>
                <p><strong>Customer:</strong> customer@aquabill.com / customer123</p>
            </div>

            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        <?php endif; ?>
    </div>
</body>

</html>