<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aquabill_db');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    // Database created successfully
} else {
    echo "Error creating database: " . $conn->error;
}

// Select database
$conn->select_db(DB_NAME);

// Set charset
$conn->set_charset("utf8mb4");

// Function to sanitize input
function sanitize($data)
{
    global $conn;
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to hash password
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// Function to execute query
function query($sql)
{
    global $conn;
    return $conn->query($sql);
}

// Function to get single row
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
}

// Function to get last inserted ID
function lastInsertId()
{
    global $conn;
    return $conn->insert_id;
}

// Function to redirect
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Function to set session message
function setMessage($message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Function to get session message
function getMessage()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
