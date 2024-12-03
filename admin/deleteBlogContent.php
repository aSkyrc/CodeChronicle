<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';

use MongoDB\BSON\ObjectId;

// Function to delete the blog post
function deleteBlog($blogId) {
    global $db;
    try {
        // Convert the string _id to ObjectId
        $objectId = new ObjectId($blogId);

        // Access the blog collection and delete the blog post
        $collection = $db->blog;
        $result = $collection->deleteOne(['_id' => $objectId]);

        // Return true if a blog post was deleted
        return $result->getDeletedCount() > 0;
    } catch (Exception $e) {
        echo "Error deleting blog: " . $e->getMessage();
        return false;
    }
}

// Check if a blog ID is provided
$blogId = $_GET['_id'] ?? null;

if ($blogId) {
    // Call the function to delete the blog
    if (deleteBlog($blogId)) {
        echo "Blog deleted successfully"; // This is returned to the AJAX request
    } else {
        echo "Error deleting the blog.";
    }
} else {
    echo "Error: Blog ID is missing.";
}
?>
