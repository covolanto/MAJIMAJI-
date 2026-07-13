<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Destroy session and redirect to login
session_destroy();
header('Location: index.php');
exit;
