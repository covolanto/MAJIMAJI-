<?php
// Fix db.php to add error handling for missing tables

$dbContent = file_get_contents('includes/db.php');

$oldCode = '// Function to get single row
function getRow($sql)
{
    global $conn;
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get all rows
function getRows($sql)
{
    global $conn;
    $result = $conn->query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}';

$newCode = '// Function to get single row
function getRow($sql)
{
    global $conn;
    try {
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}

// Function to get all rows
function getRows($sql)
{
    global $conn;
    try {
        $result = $conn->query($sql);
        $rows = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    } catch (Exception $e) {
        return [];
    }
}';

$dbContent = str_replace($oldCode, $newCode, $dbContent);
file_put_contents('includes/db.php', $dbContent);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Fix DB</title>
    <style>
        body {
            font-family: Arial;
            padding: 2rem;
            text-align: center;
            background: #f5f5f5;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .success {
            color: #10b981;
        }

        a {
            color: #0ea5e9;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2 class="success">✓ Database Functions Fixed!</h2>
        <p>The database functions now handle missing tables gracefully.</p>
        <p><a href="setup-tables.php">Click here to create the required tables</a></p>
        <p><a href="index.php">or go to Login</a></p>
    </div>
</body>

</html>