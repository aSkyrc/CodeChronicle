<?php
// Include the navigation bar file which contains the MongoDB connection and session handling
include_once '../user/navigationBar.php';

use MongoDB\BSON\ObjectId;

// Function to get user rating or return 0 if null
function getUserRating($userRating) {
    return isset($userRating) && $userRating !== null ? $userRating : 0;
}

// Function to format the date from Unix timestamp (in seconds)
function formatDate($date) {
    if ($date instanceof MongoDB\BSON\UTCDateTime) {
        // Convert MongoDB UTCDateTime to Unix timestamp and format it
        $timestamp = $date->toDateTime()->getTimestamp();
        return date('j F Y', $timestamp); // Format: Day Month Year
    } elseif (is_numeric($date)) {
        // If it's already a Unix timestamp
        return date('j F Y', $date);
    } else {
        // Handle if the date is in string format and valid
        return date('j F Y', strtotime($date)); // Convert string to timestamp
    }
}

// Get the logged-in user's ID (from session)
$loggedInUserId = $_SESSION['user_id'] ?? null;

// Get the user_id from the URL query string
$userId = $_GET['user_id'] ?? null;

// Redirect to 'profile.php' if the logged-in user is viewing their own profile
if ($loggedInUserId && $userId && (string)$loggedInUserId === $userId) {
    header("Location: profile.php");
    exit();
}

if ($userId) {
    try {
        // Fetch user information from the correct collection
        $userCollection = $db->users;
        $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);

        // If not found, check the 'google-users' collection
        if (!$author) {
            $userCollection = $db->selectCollection('google-users');
            $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
        }

        if ($author) {
            // Get user profile picture, username, rating, and created_at/createdAt
            $authorPicture = '../uploads/' . ($author['picture'] ?? 'default.jpg'); // Default profile picture
            $username = $author['username'] ?? 'Unknown Author';
            $userRating = getUserRating($author['user_rating'] ?? null);

            // Check for 'created_at' or 'createdAt' field and format it
            $createdAt = isset($author['created_at']) ? formatDate($author['created_at']) : (isset($author['createdAt']) ? formatDate($author['createdAt']) : 'Unknown date');

            // Check for role in the 'user-profile' collection if it's missing in the author data
            $profileCollection = $db->selectCollection('user-profile');
            $userProfile = $profileCollection->findOne(['user_id' => new ObjectId($userId)]);

            if ($userProfile) {
                $userRole = $userProfile['role'] ?? 'No Role'; // Fetch role from user-profile collection
                $about = is_array($userProfile['about']) ? implode(', ', $userProfile['about']) : (string)($userProfile['about'] ?? 'No about info available');
                $workCredentials = $userProfile['workCredentials'] ?? [];
                $educationCredentials = $userProfile['educationCredentials'] ?? [];
            } else {
                // Default values if no user profile data is found
                $userRole = 'No Role';
                $about = 'No about info available';
                $workCredentials = [];
                $educationCredentials = [];
            }
        } else {
            echo "Author not found.";
            exit();
        }
    } catch (Exception $e) {
        echo "Error fetching author data: " . $e->getMessage();
        exit();
    }
} else {
    echo "Error: User ID is missing. Please ensure the user ID is provided in the URL.";
    exit();
}

// Fetch user blog posts from 'blog' collection
$posts = [];
try {
    $postsCollection = $db->selectCollection('blog'); // Adjust to your collection name
    $postsCursor = $postsCollection->find(['user_id' => new ObjectId($userId)]); // Query by user_id

    // Convert cursor to array and process each post
    foreach ($postsCursor as $post) {
        // Check for 'createdAt' field and format the post's created date
        $formattedPostDate = isset($post['createdAt']) ? formatDate($post['createdAt']) : 'Unknown date';

        $posts[] = [
            'createdAt' => $formattedPostDate,
            'category' => $post['category'] ?? 'Uncategorized',
            'title' => $post['title'] ?? 'Untitled',
            'shortDescription' => $post['shortDescription'] ?? 'No description available.',
            'thumbnailPath' => '../uploads/' . ($post['thumbnailPath'] ?? 'default-thumbnail.jpg'),
        ];
    }
} catch (Exception $e) {
    echo "Error fetching posts: " . $e->getMessage();
    $posts = [];
}

?>

<body>
    <a href="javascript:history.back()" class="back-button">←</a>
    <!-- Display User Profile -->
    <div class="visit-profile" style="margin-top: 68px;"> 
        <div class="visit-profile-container">
            <div class="visit-profile-user-header">
                <img src="<?php echo htmlspecialchars($authorPicture); ?>" class="visit-profile-profile-pic" width="400px" height="300px"></img>
                <div class="visit-profile-user-info">
                    <h1 class="profile-username-profile"><?php echo htmlspecialchars($username); ?></h1>
                    <p class="profile-username-profile"><?php echo htmlspecialchars($userRole); ?></p>
                    <div class="visit-profile-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo ($i <= $userRating) ? 'star-filled' : 'star-empty'; ?>" data-value="<?php echo $i; ?>">★</span>
                        <?php endfor; ?>
                        <a class="visit-profile-rating">(<?php echo number_format($userRating, 1); ?>)</a>
                    </div>
                    <p class="visit-profile-bio"><?php echo htmlspecialchars($about); ?></p>
                </div>
                <div class="visit-profile-credentials">
                    <h3>Credentials & Highlights</h3>
                    <ul>
                    <?php if (!empty($workCredentials)): ?>
                        <?php foreach ($workCredentials as $work): ?>
                            <li>💼<span> <?php echo htmlspecialchars("{$work['position']} at {$work['organization']} ({$work['startYear']}-{$work['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>💼 No work credentials available.</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($educationCredentials)): ?>
                        <?php foreach ($educationCredentials as $education): ?>
                            <li>🎓<span><?php echo htmlspecialchars("{$education['school']},{$education['yearLevel']} ({$education['startYear']}-{$education['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>🎓 No education credentials available.</p>
                    <?php endif; ?>
                    <li>📅 <?php echo htmlspecialchars($createdAt); ?></li>
                    </ul>
                </div>
            </div>

            <div class="visit-profile-posts">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="visit-profile-post">
                            <div class="visit-profile-post-content">
                                <div class="visit-profile-meta">
                                    <span><?php echo htmlspecialchars($post['createdAt']); ?></span><span>•</span>
                                    <span><?php echo htmlspecialchars($post['category']); ?></span>
                                </div>
                                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                                <p><?php echo htmlspecialchars($post['shortDescription']); ?></p>
                                <button class="visit-profile-read-more">Continue reading...</button>
                            </div>
                            <!-- Post Thumbnail -->
                            <div class="visit-profile-post-thumbnail">
                                <img src="<?php echo htmlspecialchars($post['thumbnailPath']); ?>" alt="Post Thumbnail" width="500px" height="450px">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; margin-top: 120px; color:red; font-size: 30px;">No posts available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
