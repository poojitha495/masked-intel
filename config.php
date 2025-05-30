<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Default phpMyAdmin username
define('DB_PASS', '');         // Default phpMyAdmin password (empty)
define('DB_NAME', 'masked_intel'); // Database name

// Base URL configuration
define('BASE_URL', 'http://localhost/new_php2/'); // Change this according to your setup

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'MASKED_INTEL_SESSION');

// Error reporting - comment these lines in production
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors

// Set session parameters
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax'); // 'Strict' can cause issues with redirects

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database if it doesn't exist
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Create database if it doesn't exist
    $create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!mysqli_query($db, $create_db)) {
        throw new Exception("Failed to create database: " . mysqli_error($db));
    }

    // Select the database
    if (!mysqli_select_db($db, DB_NAME)) {
        throw new Exception("Failed to select database: " . mysqli_error($db));
    }

    mysqli_close($db);
} catch (Exception $e) {
    die("Configuration Error: " . $e->getMessage());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['email']);
}

// Function to redirect with proper base URL
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

// Function to redirect to login page
function redirectToLogin() {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
?> 