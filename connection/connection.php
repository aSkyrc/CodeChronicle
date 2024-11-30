<?php
// connection.php
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client as MongoClient;
// Initialize connection status variable
$connectionStatus = false;  // Default connection status to false
$db = null;  // Database connection holder
$collections = [];  // Array to hold multiple collections

try {
    // Connect to MongoDB
    $mongoClient = new MongoClient("mongodb://localhost:27017");

    // Select the database (replace 'your_database_name' with your actual database name)
    $db = $mongoClient->selectDatabase('user');


    // Select multiple collections
    $collections = [
        'users' => $db->selectCollection('users'),
        'google-users' => $db->selectCollection('google-users'),
        'user-profile' => $db->selectCollection('user-profile'),
        'blogs' => $db->selectCollection('blogs'),
        'blogs-categories' => $db->selectCollection('blogs-categories'),
        'ratings' => $db->selectCollection('ratings')
    ];

    $connectionStatus = true;  // Set connection status to true if successful
} catch (Exception $e) {
    $connectionStatus = false;  // Set connection status to false if there's an error
    // Optionally, log the error for debugging
    error_log("MongoDB Connection Error: " . $e->getMessage());
}

// Return connection status, database object, and collections
return [
    'connectionStatus' => $connectionStatus,
    'db' => $db,
    'collections' => $collections
];
