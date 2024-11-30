<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';

// Secure session cookie settings
session_set_cookie_params([
    'lifetime' => 86400, // 1 day in seconds
    'path' => '/',
    'domain' => '', // Add your domain if required (e.g., '.example.com')
    'secure' => isset($_SERVER['HTTPS']), // Enable for HTTPS
    'httponly' => true, // Prevent JavaScript access to session cookies
    'samesite' => 'Strict', // Protect against CSRF
]);

if (!isset($_SESSION)) {
    session_start();
}

// Check if the admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Redirect to the login page if not logged in
    header('Location: /Code Chronicle/user/signIn.php');
    exit(); // Make sure to stop the script here after the redirect
}

?>
