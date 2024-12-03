<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';

// Configure session parameters for better security
session_set_cookie_params([
    'lifetime' => 86400,          // Session duration: 24 hours
    'path' => '/',                // Cookie accessible throughout the website
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',  // Secure if using HTTPS
    'httponly' => true,           // Disallow JavaScript from accessing the cookie
    'samesite' => 'Strict',       // Prevent cross-site request forgery
]);

// Start the session if not already started
if (!isset($_SESSION)) {
    session_start();
}

// Check if the admin is logged in by checking for the session variables
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // If no admin session exists, redirect to the login page
    header('Location: /Code Chronicle/user/signIn.php');
    exit();
}

// Validate and convert `admin_id` to MongoDB ObjectId
try {
    // Check if admin_id in session is a valid MongoDB ObjectId string
    $_SESSION['_id'] = new MongoDB\BSON\ObjectId($_SESSION['admin_id']);
} catch (Exception $e) {
    // If it cannot be converted to ObjectId, redirect to login
    header('Location: /Code Chronicle/user/signIn.php');
    exit();
}
?>
