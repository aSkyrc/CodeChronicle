<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';

use MongoDB\BSON\ObjectId;

// Function to delete the blog post and send a notification
function deleteBlog($blogId) {
    global $db;

    try {
        // Convert the string _id to ObjectId
        $objectId = new ObjectId($blogId);

        // Access the blog collection
        $collection = $db->blog;

        // Find the blog to get the user information and title
        $blog = $collection->findOne(['_id' => $objectId]);

        if (!$blog) {
            return ['success' => false, 'message' => 'Blog not found.'];
        }

        // Access the notifications collection
        $notificationsCollection = $db->notifications;

        // Attempt to delete the blog post
        $result = $collection->deleteOne(['_id' => $objectId]);

        if ($result->getDeletedCount() > 0) {
            // Create a notification for the blog author
            $userId = $blog['user_id']; // Assuming the blog document has a `user_id` field
            $blogTitle = $blog['title'] ?? 'Untitled'; // Assuming blogs have a `title` field

            $notificationData = [
                'user_id' => new ObjectId($userId),
                'message' => "Your blog titled '{$blogTitle}' was deleted by the admin.",
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'is_read' => false // Default unread status
            ];

            $notificationsCollection->insertOne($notificationData);

            return ['success' => true, 'message' => 'Blog deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'Error deleting the blog.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Check if a blog ID is provided
$blogId = $_GET['_id'] ?? null;

if ($blogId) {
    // Call the function to delete the blog and handle the response
    $response = deleteBlog($blogId);
    echo json_encode($response); // Return a JSON response for AJAX requests
} else {
    echo json_encode(['success' => false, 'message' => 'Error: Blog ID is missing.']);
}
?>
