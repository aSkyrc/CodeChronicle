<?php

// Include the navigation bar file which contains the MongoDB connection
include_once '../user/navigationBar.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Convert user_id to ObjectId if stored as ObjectId in the database
use MongoDB\BSON\ObjectId;
if (preg_match('/^[a-f0-9]{24}$/', $user_id)) {
    $user_id = new ObjectId($user_id);
}

// Access the collections
$userCollection = $db->selectCollection('users'); // Default to 'users' collection
$profileCollection = $db->selectCollection('user-profile');
$blogCollection = $db->selectCollection('blog');

// Fetch user data from the 'users' collection
$userData = $userCollection->findOne(['_id' => $user_id]);

// If user is not found in 'users', check 'google-users' collection
$isGoogleUser = false;
if (!$userData) {
    $userCollection = $db->selectCollection('google-users');
    $userData = $userCollection->findOne(['_id' => $user_id]);
    $isGoogleUser = true; // Mark as a Google user
}

// Fetch user profile data (role, bio, credentials)
$userProfile = $profileCollection->findOne(['user_id' => $user_id]);

// Fetch blog posts from the 'blog' collection
$userBlogsCursor = $blogCollection->find(['user_id' => $user_id]);
$userBlogs = iterator_to_array($userBlogsCursor);

// Determine the author's picture
if ($isGoogleUser && isset($userData['picture']) && filter_var($userData['picture'], FILTER_VALIDATE_URL)) {
    // Use Google user's profile picture if it is a valid URL
    $authorPicture = $userData['picture'];
} else {
    // Default to local profile picture for 'users' collection
    $authorPicture = '../uploads/' . ($userData['picture'] ?? 'default.jpg');
}

// Prepare other profile details
$username = $userData['username'] ?? 'Unknown User';
$userRating = isset($userData['user_rating']) ? $userData['user_rating'] : 0;
$userRole = $userProfile['role'] ?? 'No Role';

// Check if 'about' is an array or a string
$about = isset($userProfile['about']) 
    ? (is_array($userProfile['about']) ? implode(', ', $userProfile['about']) : $userProfile['about']) 
    : 'No about info available';

$workCredentials = $userProfile['workCredentials'] ?? [];
$educationCredentials = $userProfile['educationCredentials'] ?? [];

// Function to format the date from Unix timestamp (in seconds)
function formatDate($timestamp) {
    if (is_numeric($timestamp) && $timestamp > 0) {
        return date('j F Y', $timestamp); // Unix timestamps are in seconds
    } else {
        return 'Unknown date';  // Fallback if the timestamp is invalid or missing
    }
}

// Format the 'created_at' timestamp for the user
$createdAt = isset($userData['created_at']) ? formatDate($userData['created_at']) : 'Unknown date';

// Fetch blog posts from the 'blog' collection, sorted by 'createdAt' in descending order
$userBlogsCursor = $blogCollection->find(
    ['user_id' => $user_id], 
    ['sort' => ['createdAt' => -1]] // Sort by 'createdAt' field in descending order
);
$userBlogs = iterator_to_array($userBlogsCursor);


?>


<body>
    <a href="javascript:history.back()" class="back-button">←</a>
    <!-- Display User Profile -->
    <div class="visit-profile" style="margin-top: 68px;"> 
        <div class="visit-profile-container">
            <div class="visit-profile-user-header">
                <img src="<?php echo htmlspecialchars($authorPicture); ?>" class="visit-profile-profile-pic" width="400px" height="300px"></img>
                <div class="visit-profile-user-info">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <p><?php echo htmlspecialchars($userRole); ?></p>
                    <div class="visit-profile-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo ($i <= $userRating) ? 'star-filled' : 'star-empty'; ?>" data-value="<?php echo $i; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <a class="visit-profile-rating">(<?php echo number_format($userRating, 1); ?>)</a>
             
                </div>
                <div class="visit-profile-credentials">
                    <h3>Credentials & Highlights</h3>
                    <ul>
                    <?php if (!empty($workCredentials)): ?>
                        <?php foreach ($workCredentials as $work): ?>
                            <li>💼<span> <?php echo htmlspecialchars("{$work['position']} at {$work['organization']} ({$work['startYear']}-{$work['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>💼 No work credentials</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($educationCredentials)): ?>
                        <?php foreach ($educationCredentials as $education): ?>
                            <li>🎓<span><?php echo htmlspecialchars("{$education['school']}, {$education['yearLevel']} ({$education['startYear']}-{$education['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>🎓 No education credentials</p>
                    <?php endif; ?>
                    <li style="padding-bottom: 20px; border-bottom: 1px solid #bfbfbf;">📅 <?php echo htmlspecialchars($createdAt); ?></li>
                    </ul>
                </div>
            </div>
            <div class="about-profile">
                <h3>Profile Description:</h3>
                <p class="visit-profile-bio"><?php echo htmlspecialchars($about); ?></p>
                </div>
            <div class="center-button-container">
                <button class="profile-button-blog" onclick="location.href='blog.php';" style="margin-bottom: 10px; margin-top: 20px">Post</button>
            </div>
            <div class="visit-profile-posts">
                    <?php if (!empty($userBlogs)): ?>
                        <?php foreach ($userBlogs as $post): ?>
                            <?php
                                // Format the 'createdAt' timestamp for blog posts (in "Day Month Year" format)
                                $formattedPostDate = isset($post['createdAt']) ? formatDate($post['createdAt']) : 'Unknown date';

                                // Extract the blog post ID (convert to string if necessary for ObjectId)
                                $postId = (string)$post['_id'];
                            ?>
                            <div class="visit-profile-post">
                                <!-- Post Content -->
                                <div class="visit-profile-post-content">
                                    <div class="visit-profile-meta">
                                        <span><?php echo htmlspecialchars($formattedPostDate); ?></span><span>•</span>
                                        <span><?php echo htmlspecialchars($post['category'] ?? 'Uncategorized'); ?></span>
                                    </div>

                                    <!-- Edit and Delete Icons -->
                                    <div class="post-actions">
                                        <a href="edit-blog-post.php?_id=<?php echo $postId; ?>" class="edit-icon">✏️</a>
                                        <a href="deleteBlog.php?_id=<?php echo $postId; ?>" 
                                            class="delete-icon" 
                                            onclick="return confirm('Are you sure you want to delete your blog?');">🗑️</a>
                                    </div>

                                    <h2><?php echo htmlspecialchars($post['title'] ?? 'Untitled'); ?></h2>
                                    <p><?php echo htmlspecialchars($post['shortDescription'] ?? 'No description available.'); ?></p>
                                    <button class="visit-profile-read-more" onclick="location.href='blog-post.php?_id=<?php echo htmlspecialchars((string)$post['_id']); ?>';">Continue reading...</button>
                                </div>

                                <!-- Post Thumbnail -->
                                <div class="visit-profile-post-thumbnail">
                                    <img src="<?php echo htmlspecialchars($post['thumbnailPath'] ?? '../uploads/default-thumbnail.jpg'); ?>" alt="Post Thumbnail" width="500px" height="450px">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; margin-top: 120px; color:red; font-size: 30px;">No posts available.</p>
                    <?php endif; ?>
                </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
        return false; // Prevent the default link action
    }
</script>

</body>
</html>
