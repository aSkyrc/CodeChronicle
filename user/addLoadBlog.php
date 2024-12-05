<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

// Store the user ID and email from the session
$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''; 

// Get the collections (MongoDB)
$usersCollection = $collections['users']; 
$googleUsersCollection = $db->selectCollection('google-users'); 
$userProfileCollection = $collections['user-profile']; 
$blogCollection = $db->selectCollection('blog'); 

// Fetch user profile data
$userProfileData = $userProfileCollection->findOne(['user_id' => $user_id]); 

if (!$userProfileData) {
    echo "User profile not found.";
    exit();
}

$interests = $userProfileData['interest'] ?? [];
$interestsArray = iterator_to_array($interests, false);

// Create the filter query for the blogs
$filter = [
    'category' => ['$in' => $interestsArray] 
];

// Get the offset parameter
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Fetch next 10 blog posts that match the user's interests, sorted by createdAt
$blogs = $blogCollection->find(
    $filter,
    ['sort' => ['createdAt' => -1], 'skip' => $offset, 'limit' => 10] 
);

// Loop through blogs and generate HTML for each
foreach ($blogs as $blog) {
    // Fetch the author data here and render the blog HTML (similar to your existing code)
    echo '<div class="post-card" style="margin-top: 20px">';
    // Add other HTML for the post
    echo '</div>';
}
?>
