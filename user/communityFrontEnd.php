<?php
include_once '../user/navigationBar.php';
// Store the user ID and email from the session
$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''; // Fallback if email is not set

// Get the collections (MongoDB)
$usersCollection = $collections['users']; // Regular users collection
$googleUsersCollection = $db->selectCollection('google-users'); // Google users collection
$userProfileCollection = $collections['user-profile']; // User profile collection
$blogCollection = $db->selectCollection('blog'); // Blog collection

// Fetch user data from google-users collection (Google login)
$userGoogleData = $googleUsersCollection->findOne(['email' => $user_email]);

// Fetch user data from users collection (for regular users)
$userData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

// Fetch user profile data based on user_id (from user-profile collection)
$userProfileData = $userProfileCollection->findOne(['user_id' => $user_id]); // Use 'user_id' from user-profile collection

// If no user profile data is found, display an error and exit
if (!$userProfileData) {
    echo "User profile not found.";
    exit();
}

// Determine whether the user is from Google or a regular user
if ($userGoogleData) {
    // For Google users
    $username = $userGoogleData['username'] ?? 'Guest'; // Fallback if username is not set
    $profilePicture = $userGoogleData['picture'] ?? '../logos/userDefault.png'; // Google picture or default to 'CClogo.jpg'
    $createdAt = $userGoogleData['created_at'] ?? time();
} elseif ($userData) {
    // For regular users
    $username = $userData['username'] ?? 'Guest'; // Fallback if username is not set
    $profilePicture = $userData['picture'] ?? '../logos/userDefault.png'; // Picture from uploads directory, default to 'CClogo.jpg'
    $createdAt = $userData['created_at'] ?? time();
} else {
    // If no user data is found in both collections
    echo "User data not found.";
    exit();
}

// Filter for blogs about "Frontend Development"
$filter = [
    'category' => 'Frontend Development' // Match blogs with the category "Frontend Development"
];

// Check if the user has the community in their interests
$communityName = 'Frontend Development'; // Example community name
$interests = isset($userProfileData['interest']) ? iterator_to_array($userProfileData['interest']) : [];
$isInterested = in_array($communityName, $interests);

// Fetch blog posts that match the filter
$blogsCursor = $blogCollection->find($filter);

// Convert cursor to an array for easier handling
$blogs = iterator_to_array($blogsCursor);
?>


<body>
<div class="community-category-visit" style="margin-top: 140px;">
    <div class="community-visit-allname">
        <div class="community-visit-row">
            <div class="community-visit-category">
                <a href="community.php"><h1>←</h1></a>
            </div>
            <div class="community-visit-category">
                <h1>Frontend Development</h1>
            </div>
            <div class="community-visit-category">
            <button id="joinButton" onclick="toggleJoinStatus('<?php echo $communityName; ?>')">
            <?php echo $isInterested ? 'Joined' : 'Join'; ?>
            </div>
        </div>
    </div>
    <div class="community-visit-img">
        <img src="../logos/community/frontend.jpeg" width="500px" height="200px">
    </div>
    <div class="community-visit-main-container-row">
        <div class="community-visit-content-container">
            <div class="community-visit-blog-cards">
                <?php if (empty($blogs)): ?>
                    <!-- Show this message if no blogs are found -->
                    <p style="text-align: center; font-size: 18px; margin-top: 20px;">
                        No posts about Frontend Development yet. Be the first to contribute!
                    </p>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                    <div class="community-visit-blogs-card">
                        <?php
                        // Fetch the user data for each blog author based on the user_id
                        $authorId = $blog['user_id']; // Assuming 'user_id' is stored in each blog post
                        $authorData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);
                        $authorName = $authorData['username'] ?? 'Guest';
                        $authorPicture = $authorData['picture'] ?? '../logos/userDefault.png';
                        $authorPicturePath = '../uploads/' . basename($authorPicture);
                        ?>
                        <div class="community-visit-blog-card">
                            <div class="community-visit-card-header">
                                <div class="community-visit-user-info">
                                    <div class="community-visit-text-content">
                                        <div class="community-visit-username">
                                            <img src="<?php echo htmlspecialchars($authorPicturePath); ?>" alt="User Icon" class="community-visit-user-icon">
                                            <span><?php echo htmlspecialchars($authorName); ?> <span class="dot">•</span><span class="rate"><?php 
                                                $rate = isset($blog['rate']) ? $blog['rate'] : 0;
                                                echo htmlspecialchars($rate); 
                                            ?></span></span>
                                        </div>
                                        <div class="community-visit-topic">
                                            <span><?php echo htmlspecialchars($blog['category'] ?? 'Uncategorized'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="community-visit-card-body">
                                <p class="community-title-content"><?php echo htmlspecialchars($blog['title']); ?></p>
                                <p class="community-short-content-description"><?php echo htmlspecialchars($blog['shortDescription']); ?></p>
                                <button class="community-visit-continue" onclick="location.href='communityFrontEndVisitBlog.php?_id=<?php echo htmlspecialchars((string)$blog['_id']); ?>';">Continue reading...</button>
                            </div>
                        </div>
                        <div class="community-visit-thumbnail">
                            <img src="<?php echo htmlspecialchars($blog['thumbnailPath']); ?>" alt="Community Thumbnail" class="community-visit-code-image">
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Community Sidebar -->
        <div class="community-visit-sidebar">
        <img class="blogging-image" src="../logos/bloggings.jpg" alt="Blogging">
            <div class="community-visit-top-blog">
                <div class="community-visit-blog-highlight">
                    <button onclick="window.location.href = 'communityPostBlog.php?community=' + encodeURIComponent('Frontend Development');">Post</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleJoinStatus(communityName) {
    const joinButton = document.getElementById('joinButton');
    
    // Check if the user is already joined
    if (joinButton.textContent.trim() === 'Joined') {
        alert('You have already joined this community!');
        return; // Exit the function to prevent the fetch request
    }

    // Proceed with the fetch request if not already joined
    fetch('addCommunityInterest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ community: communityName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button text and alert depending on whether the user joined
            if (data.isJoined) {
                alert('You have successfully joined the community!');
            } else {
                alert('You have left the community!');
            }
            joinButton.textContent = data.isJoined ? 'Joined' : 'Join';
        } else {
            alert('Error: ' + data.message);  // Alert error if failure
        }
    })
    .catch(error => {
        console.error('Error:', error);  // Log any fetch errors
    });
}
</script>
</body>
</html>
