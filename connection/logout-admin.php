<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';
// Start the session
session_start();

// Destroy the session to log the user out
session_unset();
session_destroy();

// Redirect the user to the login page after logging out
header("Location: /Code Chronicle/user/signIn.php");
exit();
?>
