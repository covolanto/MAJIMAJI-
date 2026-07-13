<?php
// Quick setup script to create activity_logs table
// Run this file once to create the activity_logs table

require_once 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (query($sql)) {
    echo "Activity logs table created successfully!";
} else {
    echo "Error creating table: " . $conn->error;
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
        }

        .success {
            color: #10b981;
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
        <?php if (query($sql)): ?>
            <h2 class="success">✓ Setup Complete!</h2>
            <p>The activity_logs table has been created successfully.</p>
            <a href="index.php">Go to Login</a>
        <?php else: ?>
            <h2>Error</h2>
            <p><?php echo $conn->error; ?></p>
        <?php endif; ?>
    </div>
</body>

</html>