<?php
// Start session (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is customer
function isCustomer()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

// Login user
function login($userId, $username, $email, $role)
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
}

// Logout user
function logout()
{
    session_destroy();
    redirect('../index.php');
}

// Require login
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('../index.php');
    }
}

// Require admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        redirect('../index.php');
    }
}

// Require customer
function requireCustomer()
{
    requireLogin();
    if (!isCustomer()) {
        redirect('../index.php');
    }
}

// Get current user id
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getUsername()
{
    return $_SESSION['username'] ?? null;
}

// Get current user email
function getUserEmail()
{
    return $_SESSION['email'] ?? null;
}

// Get current user role
function getUserRole()
{
    return $_SESSION['role'] ?? null;
}
