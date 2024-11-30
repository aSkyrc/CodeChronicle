<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

$userId = $_SESSION['user_id']; // Assuming user_id is stored in the session
// Determine the user's profile collection
$profileCollection = $collections['user-profile']; // The collection where user profiles are stored

// Fetch the current user's profile from the user-profile collection
$userProfile = $profileCollection->findOne(['user_id' => $userId]);

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Declare the necessary POST variables
    $position = $_POST['position'];
    $organization = $_POST['organization'];
    $startYear = $_POST['start-year'];
    $endYear = $_POST['end-year'];

    // Validate the existing work credentials count
    if (isset($userProfile['workCredentials']) && count($userProfile['workCredentials']) >= 2) {
        echo "<script>
        alert('You can only add 2 credentials.');
        window.location.href = 'settings-profile.php';
    </script>";
        exit();
    }

    // Prepare the new work credential
    $newWorkCredential = [
        '_id' => new MongoDB\BSON\ObjectId(), // Generate a new ObjectId
        'position' => $position,
        'organization' => $organization,
        'startYear' => $startYear,
        'endYear' => $endYear
    ];

    // Use $push to add the new credential to the array
    $updateResult = $profileCollection->updateOne(
        ['user_id' => $userId], // Find the document by user_id
        ['$push' => ['workCredentials' => $newWorkCredential]] // Use $push to add the new work credential
    );

    // Check if the update was successful
    if ($updateResult->getModifiedCount() === 0) {
        echo "No changes were made to the database.";
        exit();
    }

    // Show success message and redirect after 2 seconds
    echo "<script>
        alert('Work credentials added successfully!');
        window.location.href = 'settings-profile.php';
    </script>";
    exit();
} else {
    // Handle the case where the request is not POST
    echo "Invalid request method.";
    exit();
}
?>
