<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$userProfileCollection = $collections['user-profile'];

// Parse the request data
$request = json_decode(file_get_contents('php://input'), true);
$communityName = $request['community'] ?? null;

if (!$communityName) {
    echo json_encode(['success' => false, 'message' => 'Invalid community name.']);
    exit();
}

// Find the user profile
$userProfileData = $userProfileCollection->findOne(['user_id' => $user_id]);

if (!$userProfileData) {
    echo json_encode(['success' => false, 'message' => 'User profile not found.']);
    exit();
}

// Check if the user already has this community in their interests
$interests = $userProfileData['interest'] instanceof MongoDB\Model\BSONArray ? iterator_to_array($userProfileData['interest']) : $userProfileData['interest'];

$isInterested = in_array($communityName, $interests);

if ($isInterested) {
    // Remove the community from interests
    $updatedInterests = array_filter($interests, fn($interest) => $interest !== $communityName);
} else {
    // Add the community to interests
    $updatedInterests = array_merge($interests, [$communityName]);
}

// Update the user profile with the new interests list
$userProfileCollection->updateOne(
    ['user_id' => $user_id],
    ['$set' => ['interest' => $updatedInterests]]
);

// Prepare response
$response = [
    'success' => true,
    'isJoined' => !$isInterested
];

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
