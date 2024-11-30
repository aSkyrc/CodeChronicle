<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/config.php';

if (!isset($_SESSION)) {
    session_start();
}

$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);
exit;
