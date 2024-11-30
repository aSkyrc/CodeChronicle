<?php
require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();
$client->setClientId('418849242240-aj9jul4hu1s88p5rar2p4enfjjmbchbt.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Code%20Chronicle/connection/google-callback.php');
$client->addScope('email');
$client->addScope('profile');
?>
