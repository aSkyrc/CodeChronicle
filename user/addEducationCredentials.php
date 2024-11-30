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
    // Declare the necessary POST variables for education credentials
    $school = $_POST['school'];
    $yearLevel = $_POST['year-level'];
    $startYear = $_POST['start-year'];
    $endYear = $_POST['end-year'];

    // Validate the existing education credentials count
    if (isset($userProfile['educationCredentials']) && count($userProfile['educationCredentials']) >= 2) {
        echo "You can only add up to 2 education credentials.";
        exit();
    }

    // Prepare the new education credential with an added _id field
    $newEducationCredential = [
        '_id' => new MongoDB\BSON\ObjectId(), // Generate a new ObjectId
        'school' => $school,
        'yearLevel' => $yearLevel,
        'startYear' => $startYear,
        'endYear' => $endYear
    ];

    // Use $push to add the new education credential to the array
    $updateResult = $profileCollection->updateOne(
        ['user_id' => $userId], // Find the document by user_id
        ['$push' => ['educationCredentials' => $newEducationCredential]] // Use $push to add the new education credential
    );

    // Check if the update was successful
    if ($updateResult->getModifiedCount() === 0) {
        echo "No changes were made to the database.";
        exit();
    }

    // Show success message and redirect after 2 seconds
    echo "<script>
        alert('Education credentials added successfully!');
        window.location.href = 'settings-profile.php';
    </script>";
    exit();
} else {
    // Handle the case where the request is not POST
    echo "Invalid request method.";
    exit();
}
?>
