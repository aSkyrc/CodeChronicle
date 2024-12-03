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
        $timestamp = $date->toDateTime()->getTimestamp();
        return date('j F Y', $timestamp);
    } elseif (is_numeric($date)) {
        return date('j F Y', $date);
    } else {
        return date('j F Y', strtotime($date));
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
        $userCollection = $db->users;
        $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);

        if (!$author) {
            // Check the 'google-users' collection
            $userCollection = $db->selectCollection('google-users');
            $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
        }

        if ($author) {
            // Handle the author's profile picture
            if (isset($author['picture']) && filter_var($author['picture'], FILTER_VALIDATE_URL)) {
                // If the picture is a URL, use it directly
                $authorPicture = $author['picture'];
            } else {
                // Fallback to the default uploads directory or a default picture
                $authorPicture = '../uploads/' . ($author['picture'] ?? 'default.jpg');
            }

            $username = $author['username'] ?? 'Unknown Author';
            $userRating = getUserRating($author['user_rating'] ?? null);

            $createdAt = isset($author['created_at']) ? formatDate($author['created_at']) :
                         (isset($author['createdAt']) ? formatDate($author['createdAt']) : 'Unknown date');

            $profileCollection = $db->selectCollection('user-profile');
            $userProfile = $profileCollection->findOne(['user_id' => new ObjectId($userId)]);

            if ($userProfile) {
                $userRole = $userProfile['role'] ?? 'No Role';
                $about = is_array($userProfile['about']) ? implode(', ', $userProfile['about']) : (string)($userProfile['about'] ?? 'No about info available');
                $workCredentials = $userProfile['workCredentials'] ?? [];
                $educationCredentials = $userProfile['educationCredentials'] ?? [];
            } else {
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

$posts = [];
try {
    $postsCollection = $db->selectCollection('blog');
    $postsCursor = $postsCollection->find(
        ['user_id' => new ObjectId($userId)], 
        ['sort' => ['createdAt' => -1]] // Sort by 'createdAt' in descending order
    );
    

    foreach ($postsCursor as $post) {
        $formattedPostDate = isset($post['createdAt']) ? formatDate($post['createdAt']) : 'Unknown date';

        $posts[] = [
            '_id' => (string)$post['_id'], // Include the post ID
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
    <div class="visit-profile" style="margin-top: 68px;"> 
        <div class="visit-profile-container">
            <div class="visit-profile-user-header">
                <img src="<?php echo htmlspecialchars($authorPicture); ?>" class="visit-profile-profile-pic" width="400px" height="300px">
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
                            <li>💼 <span><?php echo htmlspecialchars("{$work['position']} at {$work['organization']} ({$work['startYear']}-{$work['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>💼 No work credentials</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($educationCredentials)): ?>
                        <?php foreach ($educationCredentials as $education): ?>
                            <li>🎓 <span><?php echo htmlspecialchars("{$education['school']}, {$education['yearLevel']} ({$education['startYear']}-{$education['endYear']})"); ?></span></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>🎓 No education credentials</p>
                    <?php endif; ?>
                    <li style="padding-bottom: 20px; border-bottom: 1px solid #bfbfbf;">📅 <?php echo htmlspecialchars($createdAt); ?></li>
                    </ul>
                </div>
            </div>
            <div class="about-profile" style="margin-bottom: 20px;">
                <h3>Profile Description:</h3>
                <p class="visit-profile-bio"><?php echo htmlspecialchars($about); ?></p>
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
                                <button class="visit-profile-read-more" onclick="location.href='blog-post.php?_id=<?php echo htmlspecialchars($post['_id']); ?>';">Continue reading...</button>
                            </div>
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
