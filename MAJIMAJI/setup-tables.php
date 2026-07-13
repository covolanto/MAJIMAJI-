<?php
// Quick setup script to create all new tables
// Run this file once to create the new tables

require_once 'includes/db.php';

$message = '';
$error = '';

// Create activity_logs table
$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (query($sql)) {
    $message .= "Activity logs table created.<br>";
} else {
    $error .= "Error creating activity_logs: " . $conn->error . "<br>";
}

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
if (query($sql)) {
    $message .= "Meter readings table created.<br>";
} else {
    $error .= "Error creating meter_readings: " . $conn->error . "<br>";
}

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
if (query($sql)) {
    $message .= "Support tickets table created.<br>";
} else {
    $error .= "Error creating support_tickets: " . $conn->error . "<br>";
}

// Add meter_info columns to customers table
$result = $conn->query("SHOW COLUMNS FROM customers LIKE 'meter_type'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE customers ADD COLUMN meter_type VARCHAR(50) DEFAULT 'analog' AFTER meter_number");
    $conn->query("ALTER TABLE customers ADD COLUMN meter_location VARCHAR(255) AFTER meter_type");
    $conn->query("ALTER TABLE customers ADD COLUMN meter_install_date DATE AFTER meter_location");
    $conn->query("ALTER TABLE customers ADD COLUMN meter_status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active' AFTER meter_install_date");
    $conn->query("ALTER TABLE customers ADD COLUMN last_reading INT DEFAULT 0 AFTER meter_status");
    $conn->query("ALTER TABLE customers ADD COLUMN last_reading_date DATE AFTER last_reading");
    $message .= "Customer meter columns added.<br>";
} else {
    $message .= "Customer meter columns already exist.<br>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - AquaBill</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        .success {
            color: #10b981;
        }

        .error {
            color: #ef4444;
        }

        a {
            display: inline-block;
            margin-top: 1rem;
            color: #0ea5e9;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="card">
        <?php if ($error): ?>
            <h2 class="error">Setup Errors</h2>
            <p><?php echo $error; ?></p>
        <?php else: ?>
            <h2 class="success">✓ Setup Complete!</h2>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <a href="index.php">Go to Login</a>
    </div>
</body>

</html>