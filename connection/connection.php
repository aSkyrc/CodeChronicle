<?php
// connection.php
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client as MongoClient;

// Initialize connection status variable
$connectionStatus = false; // Default connection status to false
$db = null; // Database connection holder
$dbadmin = null; // Database connection holder
$collections = []; // Array to hold multiple collections
$collectionsadmin = []; // Array to hold multiple collections

try {
    // Connect to MongoDB
    $mongoClient = new MongoClient("mongodb://localhost:27017");

    // Select databases
    $db = $mongoClient->selectDatabase('user');
    $dbadmin = $mongoClient->selectDatabase('user'); // Adjust database name if different

    // Select collections
    $collections = [
        'users' => $db->selectCollection('users'),
        'google-users' => $db->selectCollection('google-users'),
        'user-profile' => $db->selectCollection('user-profile'),
        'blog' => $db->selectCollection('blog')
    ];

    $collectionsadmin = [
        'account' => $dbadmin->selectCollection('account'), // Corrected name
    ];

    $connectionStatus = true; // Set connection status to true if successful
} catch (Exception $e) {
    $connectionStatus = false; // Set connection status to false if there's an error
    error_log("MongoDB Connection Error: " . $e->getMessage());
}

return [
    'connectionStatus' => $connectionStatus,
    'db' => $db,
    'dbadmin' => $dbadmin,
    'collections' => $collections,
    'collectionsadmin' => $collectionsadmin
];
?>
