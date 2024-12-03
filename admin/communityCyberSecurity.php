<?php
include_once '../admin/adminId.php';

// Get the collections (MongoDB)
$usersCollection = $collections['users']; // Regular users collection
$googleUsersCollection = $db->selectCollection('google-users'); // Google users collection
$userProfileCollection = $collections['user-profile']; // User profile collection
$blogCollection = $db->selectCollection('blog'); // Blog collection

// Filter for blogs about "Algorithms and Data Structures"
$filter = [
    'category' => 'CyberSecurity' // Filter for blog category
];

// Fetch blog posts that match the filter
$blogsCursor = $blogCollection->find($filter);

// Convert cursor to an array for easier handling
$blogs = iterator_to_array($blogsCursor);

// Check if the user has the community in their interests (if needed, you can leave it out for admin only)
$communityName = 'CyberSecurity'; // Example community name
$interests = isset($userProfileData['interest']) ? iterator_to_array($userProfileData['interest']) : [];
$isInterested = in_array($communityName, $interests);

?>

<body>
<div class="community-category-visit" style="margin-top: 110px;">
    <div class="community-visit-allname">
        <div class="community-visit-row">
            <div class="community-visit-category">
                <a href="community.php"><h1>←</h1></a>
            </div>
            <div class="community-visit-category">
                <h1>CyberSecurity</h1>
            </div>
            <div class="community-visit-category">
            <button id="joinButton" style="display: none;">
            </div>
        </div>
    </div>
    <div class="community-visit-img">
        <img src="../logos/community/cybersecutiry.webp" width="500px" height="200px">
    </div>
    <div class="community-visit-main-container-row">
        <div class="community-visit-content-container">
            <div class="community-visit-blog-cards">
                <?php if (empty($blogs)): ?>
                    <!-- Show this message if no blogs are found -->
                    <p style="text-align: center; font-size: 18px; margin-top: 20px;">
                        No posts about CyberSecurity yet.
                    </p>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                    <div class="community-visit-blogs-card">
                        <?php
                        // Fetch the user data for each blog author based on the user_id
                        $authorId = $blog['user_id']; // Assuming 'user_id' is stored in each blog post
                        $authorData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);
                        $authorName = $authorData['username'] ?? 'Guest';
                        $authorPicture = $authorData['picture'] ?? '../uploads/userDefault.png';
                        $authorPicturePath = '../uploads/' . basename($authorPicture);
                        ?>
                        <div class="community-visit-blog-card">
                            <div class="community-visit-card-header">
                                <div class="community-visit-user-info">
                                    <div class="community-visit-text-content">
                                    <div class="community-visit-username">
                                            <img src="<?php echo htmlspecialchars($authorPicturePath); ?>" alt="User Icon" class="community-visit-user-icon">
                                            <span>
                                                <?php echo htmlspecialchars($authorName); ?>
                                                <span class="dot">•</span>
                                                <span class="rate">
                                                    <?php 
                                                        $rate = isset($blog['rate']) ? $blog['rate'] : 0; // Set a default value if 'rate' is missing
                                                        echo htmlspecialchars($rate); 
                                                    ?>
                                                </span>
                                            </span>
                                            <a href="#" 
                                            class="delete-icon" 
                                            onclick="return handleDelete('<?php echo htmlspecialchars((string)$blog['_id']); ?>');" 
                                            title="Delete Blog">
                                                🗑️
                                            </a>
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
                                <button class="community-visit-continue" onclick="location.href='blog-post.php?_id=<?php echo htmlspecialchars((string)$blog['_id']); ?>';">Continue reading...</button>
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

    </div>
</div>
<script src="deleteBlog.js"></script>
</body>
</html>
