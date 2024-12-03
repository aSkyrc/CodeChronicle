<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

use MongoDB\BSON\ObjectId; // Move this outside the if block

// Check if blog ID is provided
if (isset($_GET['_id'])) {
    $blogId = $_GET['_id'];

    try {
        // Convert blog ID to ObjectId
        $blogObjectId = new ObjectId($blogId);

        // Access the 'blog' collection
        $blogsCollection = $db->selectCollection('blog');

        // Delete the blog
        $result = $blogsCollection->deleteOne(['_id' => $blogObjectId]);

        if ($result->getDeletedCount() > 0) {
            echo '<script>alert("Blog deleted successfully!"); window.location.href = "profile.php";</script>';
        } else {
            echo '<script>alert("Failed to delete the blog. It may not exist."); window.history.back();</script>';
        }
    } catch (Exception $e) {
        echo '<script>alert("Error: ' . $e->getMessage() . '"); window.history.back();</script>';
    }
} else {
    echo '<script>alert("No blog ID provided."); window.history.back();</script>';
}
?>
