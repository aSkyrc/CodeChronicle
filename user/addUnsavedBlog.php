<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

// Check if the blog_id is passed via GET
if (isset($_GET['blog_id']) && !empty($_GET['blog_id'])) {
    $blogId = $_GET['blog_id']; // Get the blog ID from the URL

    // Fetch the user profile data
    $userProfileCollection = $collections['user-profile']; // User profile collection
    $userProfileData = $userProfileCollection->findOne(['user_id' => $_SESSION['user_id']]); // Get profile of the logged-in user

    if ($userProfileData) {
        // Get saved blogs from the user's profile and convert BSONArray to PHP array
        $savedBlogs = iterator_to_array($userProfileData['savedBlogs'] ?? [], false);

        // Check if the blog ID exists in the saved blogs array
        if (($key = array_search($blogId, $savedBlogs)) !== false) {
            // If found, remove it from the array
            unset($savedBlogs[$key]);

            // Update the user profile to remove the blog from savedBlogs
            $updateResult = $userProfileCollection->updateOne(
                ['user_id' => $_SESSION['user_id']],
                ['$set' => ['savedBlogs' => array_values($savedBlogs)]] // Update savedBlogs array
            );

            // Check if the update was successful
            if ($updateResult->getModifiedCount() > 0) {
                echo "<script>
                        window.location.reload(); // This will reload the current page
                      </script>";
            } else {
                echo "Error unsaving the blog.";
            }
        } else {
            // Redirect to saved-blog.php with a message if blog not found in saved blogs
            echo "<script>
                    alert('Blog unsaved successfully.');
                    window.location.href = 'saved-blog.php'; // Redirect to saved-blog.php
                  </script>";
        }
    } else {
        echo "User profile not found.";
    }
} else {
    echo "Blog ID not provided.";
}
?>
