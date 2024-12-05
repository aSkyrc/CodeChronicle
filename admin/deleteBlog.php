<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';

header('Content-Type: application/json');

// Ensure the admin is logged in
if (empty($_SESSION['_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

// Parse input
$data = json_decode(file_get_contents('php://input'), true);
$blogId = $data['blog_id'] ?? null;

if (empty($blogId)) {
    echo json_encode(['success' => false, 'message' => 'Blog ID is required.']);
    exit();
}

// Debugging: Log the received blog ID
error_log("Blog ID received for deletion: " . $blogId);

try {
    // Convert blog ID to ObjectId
    $blogObjectId = new MongoDB\BSON\ObjectId($blogId);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid Blog ID.']);
    error_log("Failed to convert blog ID to ObjectId: " . $e->getMessage());
    exit();
}

// Debugging: Log the ObjectId value
error_log("Converted Blog ObjectId: " . $blogObjectId);

// Ensure $blogCollection is initialized properly
$blogCollection = $db->selectCollection('blog'); // Check this initialization
$notificationsCollection = $db->selectCollection('notifications'); // Notifications collection

try {
    // Find the blog to retrieve its author information and title
    $blog = $blogCollection->findOne(['_id' => $blogObjectId]);

    if (!$blog) {
        echo json_encode(['success' => false, 'message' => 'Blog not found.']);
        exit();
    }

    // Attempt to delete the blog post
    $result = $blogCollection->deleteOne(['_id' => $blogObjectId]);

    // Log the result
    error_log("Delete result: " . $result->getDeletedCount());

    // Check if deletion was successful
    if ($result->getDeletedCount() > 0) {
        // Create a notification for the blog author
        $userId = $blog['user_id']; // Assuming the blog document has a `user_id` field
        $blogTitle = $blog['title']; // Assuming the blog document has a `title` field

        $notificationData = [
            'user_id' => $userId,
            'message' => "Your blog '{$blogTitle}' was deleted by the admin.",
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'is_read' => false // Default unread status
        ];

        $notificationsCollection->insertOne($notificationData);

        echo json_encode(['success' => true, 'message' => 'Blog deleted successfully and notification sent.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Blog not found or already deleted.']);
    }
} catch (Exception $e) {
    // Catch any errors that may occur during deletion
    echo json_encode(['success' => false, 'message' => 'Error deleting blog: ' . $e->getMessage()]);
    error_log("Error deleting blog: " . $e->getMessage());
}
?>
