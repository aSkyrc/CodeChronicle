<?php

include_once '../user/navigationBar.php';

// Fetch the search query from the URL parameter
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Initialize empty results array
$blogResults = [];

// Only perform search if query is not empty
if ($searchQuery) {
    // Create the filter query for the blogs based on the title
    $filter = [
        'title' => ['$regex' => $searchQuery, '$options' => 'i'] // Case-insensitive regex search
    ];

    // Fetch blogs that match the search query using the correct collection
    $blogResults = $collections['blog']->find($filter);
}

?>

<body>
    <div class="search-page" style="margin-top: 68px;">
        <div class="search-results-container">
            <h1 style="text-align: center;">Search Results</h1>

            <?php if (empty($blogResults)): ?>
                <p style="text-align: center; margin-top: 200px">No blogs found matching your search query.</p>
            <?php else: ?>
                <div class="visit-search-posts">
                    <?php foreach ($blogResults as $post): ?>
                        <div class="visit-search-post">
                            <div class="visit-search-post-content">
                                <div class="visit-search-meta">
                                    <!-- User Profile Image and Category with Rating -->
                                    <div class="search-user-info">
                                    <div class="search-user-image">
                                        <?php
                                        // Check if 'user_id' exists in the post
                                        if (isset($post['user_id'])) {
                                            $authorId = $post['user_id']; // Get the user's ID

                                            // Fetch user data from the regular users collection
                                            $authorData = $collections['users']->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);

                                            // If no regular user found, check for Google user in google-users collection
                                            if (!$authorData) {
                                                $authorData = $collections['google-users']->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);
                                            }

                                            // Default values if user is not found
                                            $authorName = $authorData['username'] ?? 'Guest';

                                            // Check if the user has a profile picture
                                            if (isset($authorData['picture'])) {
                                                // If regular user, check if it's a local file or a URL
                                                if (filter_var($authorData['picture'], FILTER_VALIDATE_URL)) {
                                                    // It's a URL (Google user picture)
                                                    $authorPicture = $authorData['picture'];
                                                } else {
                                                    // It's a local file
                                                    $authorPicture = '../logos/' . $authorData['picture'];  // Update the path to the picture
                                                }
                                            } else {
                                                // Default picture if not available
                                                $authorPicture = '../logos/userDefault.png';
                                            }
                                        } else {
                                            // If 'user_id' is not set, use default values
                                            $authorName = 'Guest';
                                            $authorPicture = '../logos/userDefault.png';
                                        }

                                        // Ensure the profile picture path is valid
                                        $authorPicturePath = htmlspecialchars($authorPicture);
                                        ?>
                                        <img src="<?php echo $authorPicturePath; ?>" alt="User Image" width="40px" height="40px">
                                    </div>
                                    <div class="user-details">
                                        <!-- Display the username and rating side by side -->
                                        <span class="search-username"><?php echo htmlspecialchars($authorName); ?></span>
                                        <span>•</span>
                                        <span class="search-rating">(<?php echo isset($post['rating']) ? htmlspecialchars($post['rating']) : '0'; ?>)</span>
                                        <br>
                                        <!-- Display category below the username and rating -->
                                        <span class="search-category"><?php echo isset($post['category']) ? htmlspecialchars($post['category']) : 'Uncategorized'; ?></span>
                                    </div>
                                </div>

                                </div>
                                <h2 class="search-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                                <p  class="search-short"><?php echo htmlspecialchars($post['shortDescription']); ?></p>
                                <a href="blog-post.php?_id=<?php echo htmlspecialchars((string)$post['_id']); ?>" class="visit-search-read-more">
                                    Continue reading...
                                </a>
                            </div>
                            <!-- Post Thumbnail -->
                            <div class="visit-search-post-thumbnail">
                                <?php if (!empty($post['thumbnailPath'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['thumbnailPath']); ?>" alt="Post Thumbnail" width="500px" height="450px">
                                <?php else: ?>
                                    <img src="default-thumbnail.jpg" alt="Default Thumbnail" width="500px" height="450px">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
