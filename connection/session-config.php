<?php
// Secure session settings
session_set_cookie_params([
    'lifetime' => 86400, // 1 day in seconds
    'path' => '/',
    'domain' => '', // Set your domain if needed (e.g., '.example.com')
    'secure' => isset($_SERVER['HTTPS']), // True if HTTPS is used
    'httponly' => true, // Prevent JavaScript access to session cookies
    'samesite' => 'Strict' // Protect against CSRF attacks
]);

// Start the session
session_start();


$user_id = $_SESSION['user_id'];

// If the user is not logged in, continue with the existing logic
if (!isset($_SESSION['user_id'])) {
    // If user is not logged in, redirect to signIn.php
    header("Location: /Code Chronicle/user/signIn.php");
    exit();
}

?>
