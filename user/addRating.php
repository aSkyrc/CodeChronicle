<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';
use MongoDB\BSON\ObjectId;

// Get data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

$blogId = $data['blogId'] ?? null;
$userId = $data['userId'] ?? null; // This is the logged-in user's ID
$rating = $data['rating'] ?? null;

// Validate input data
if (!$blogId || !$userId || $rating === null || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
}

try {
    // Convert string IDs to ObjectId
    $blogObjectId = new ObjectId($blogId);
    $userObjectId = new ObjectId($userId);

    // Access blog collection
    $blogCollection = $db->blog;
    $blog = $blogCollection->findOne(['_id' => $blogObjectId]);

    if (!$blog) {
        echo json_encode(['success' => false, 'message' => 'Blog not found.']);
        exit();
    }

    // The user rating the blog
    // Check if the logged-in user has already rated this blog
    $userCollection = $db->users;
    $user = $userCollection->findOne(['_id' => $userObjectId]);

    // If the user is not found, check google-users collection
    if (!$user) {
        $userCollection = $db->selectCollection('google-users');
        $user = $userCollection->findOne(['_id' => $userObjectId]);
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    // Check if the logged-in user has already rated this blog
    $ratedBlogs = $user['ratedBlogs'] ?? [];
    if ($ratedBlogs instanceof MongoDB\Model\BSONArray) {
        $ratedBlogs = $ratedBlogs->getArrayCopy(); // Convert BSONArray to a PHP array
    }

    if (in_array($blogId, $ratedBlogs)) {
        echo json_encode(['success' => false, 'message' => 'You have already rated this blog.']);
        exit();
    }

    // Add blog ID to the user's ratedBlogs
    $userCollection->updateOne(
        ['_id' => $userObjectId],
        ['$push' => ['ratedBlogs' => $blogId]]
    );

    // Update the blog's rating based on the user's rating
    $currentRating = $blog['rating'] ?? 0;
    $ratingCount = $blog['ratingCount'] ?? 0;
    $newRating = (($currentRating * $ratingCount) + $rating) / ($ratingCount + 1);
    $newRating = number_format($newRating, 1, '.', ''); // Ensure 1 decimal place

    $blogCollection->updateOne(
        ['_id' => $blogObjectId],
        [
            '$set' => ['rating' => (float) $newRating], // Store as a float with 1 decimal point
            '$inc' => ['ratingCount' => 1] // Increment the rating count
        ]
    );

    // Optionally, update the blog author's rating if the user isn't the author
    $authorId = $blog['user_id'] ?? null; // This is the ID of the blog's author
    if ($authorId && (string) $authorId !== (string) $userObjectId) {
        $authorCollection = $db->users;
        $author = $authorCollection->findOne(['_id' => new ObjectId($authorId)]);

        if (!$author) {
            $authorCollection = $db->selectCollection('google-users');
            $author = $authorCollection->findOne(['_id' => new ObjectId($authorId)]);
        }

        if ($author) {
            $currentAuthorRating = $author['user_rating'] ?? 0;
            $authorRatingCount = $author['user_rating_count'] ?? 0;

            $newAuthorRating = (($currentAuthorRating * $authorRatingCount) + $rating) / ($authorRatingCount + 1);
            $newAuthorRating = number_format($newAuthorRating, 1, '.', ''); // Ensure 1 decimal place

            // Update the author's rating
            $result = $authorCollection->updateOne(
                ['_id' => new ObjectId($authorId)],
                [
                    '$set' => ['user_rating' => (float) $newAuthorRating],
                    '$inc' => ['user_rating_count' => 1]
                ]
            );

            if ($result->getModifiedCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'Failed to update author rating.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Author not found.']);
            exit();
        }
    }

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully!']);
} catch (Exception $e) {
    error_log('Error in addRating.php: ' . $e->getMessage());  // Log the error
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting the rating.']);
}
