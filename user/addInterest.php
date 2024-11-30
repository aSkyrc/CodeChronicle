<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

$userId = $_SESSION['user_id'];

if (isset($_POST['interest']) && is_array($_POST['interest'])) {
    $interests = $_POST['interest'];

    // Fetch the user profile from the user-profile collection
    $userProfileCollection = $db->selectCollection('user-profile');
    $userProfileData = $userProfileCollection->findOne(['user_id' => $userId]);

    if ($userProfileData) {
        // Ensure interests field exists, initialize as empty array if not
        $existingInterests = isset($userProfileData['interest']) ? iterator_to_array($userProfileData['interest']) : [];

        // Add new interests
        $newInterests = array_diff($interests, $existingInterests); // Get interests that are selected but not yet in the profile
        $updatedInterests = array_merge($existingInterests, $newInterests); // Add the new ones to the existing interests

        // Remove unchecked interests
        $uncheckedInterests = array_diff($existingInterests, $interests); // Get interests that were in the profile but unchecked
        $updatedInterests = array_diff($updatedInterests, $uncheckedInterests); // Remove unchecked interests

        $updatedInterests = array_unique($updatedInterests); // Ensure no duplicates

        // Update the user profile with the new interests
        $updateResult = $userProfileCollection->updateOne(
            ['user_id' => $userId],
            ['$set' => ['interest' => $updatedInterests]]
        );

        if ($updateResult->getModifiedCount() > 0) {
            // Redirect to homepage after successful update
            header("Location: homepage.php");
            exit(); // Ensure no further code runs
        } else {
            echo "No changes made.";
            header("Location: homepage.php");
            exit(); // Ensure no further code runs
        }
    } else {
        echo "User profile not found.";
        exit();
    }
} else {
    // Handle the case where no interests are passed
    echo "No interests selected.";
    header("Location: homepage.php");
    exit(); // Ensure no further code runs
}
?>
