<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';
use MongoDB\BSON\ObjectId;

// Get data from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$blogId = $data['blogId'] ?? null;
$userId = $data['userId'] ?? null;
$rating = $data['rating'] ?? null;

if ($blogId && $userId && $rating !== null) {
    try {
        // Convert the string _id to ObjectId
        $objectId = new ObjectId($blogId);
        $userObjectId = new ObjectId($userId);

        // Access the blog collection
        $collection = $db->blog;

        // Check if the blog exists
        $blog = $collection->findOne(['_id' => $objectId]);
        if (!$blog) {
            echo json_encode(['success' => false, 'message' => 'Blog not found']);
            exit();
        }

        // Check if the user has already rated this blog
        $userCollection = $db->users; // Check in the 'users' collection
        $user = $userCollection->findOne(['_id' => $userObjectId]);

        if (!$user) {
            // If the user is not found in the 'users' collection, check the 'google-users' collection
            $userCollection = $db->selectCollection('google-users');
            $user = $userCollection->findOne(['_id' => $userObjectId]);
        }

        if ($user) {
            // Check if the user has already rated this blog
            if (in_array($blogId, $user['ratedBlogs'] ?? [])) {
                // User has already rated this blog
                echo json_encode(['success' => false, 'message' => 'You have already rated this blog']);
                exit();
            }

            // Add the blog ID to the user's ratedBlogs array
            $userCollection->updateOne(
                ['_id' => $userObjectId],
                ['$push' => ['ratedBlogs' => $blogId]]
            );
        }

        // Update the blog's rating
        $currentRating = $blog['rating'] ?? 0;
        $ratingCount = $blog['ratingCount'] ?? 0;
        $newRating = (($currentRating * $ratingCount) + $rating) / ($ratingCount + 1);

        // Update the blog with the new rating and increment rating count
        $collection->updateOne(
            ['_id' => $objectId],
            ['$set' => ['rating' => $newRating], '$inc' => ['ratingCount' => 1]]
        );

        // Update the user's rating in the 'users' or 'google-users' collection
        $currentUserRating = $user['user_rating'] ?? 0;
        $userRatingCount = $user['user_rating_count'] ?? 0;
        $newUserRating = (($currentUserRating * $userRatingCount) + $rating) / ($userRatingCount + 1);

        // Update the user's rating and increment rating count
        $userCollection->updateOne(
            ['_id' => $userObjectId],
            ['$set' => ['user_rating' => $newUserRating], '$inc' => ['user_rating_count' => 1]]
        );

        // Respond with success message
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Handle any errors
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}
?>
