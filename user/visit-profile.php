<?php
// Include the navigation bar file which contains the MongoDB connection and session handling
include_once '../user/navigationBar.php';

// Import the MongoDB ObjectId class
use MongoDB\BSON\ObjectId;

// Define the getuserRating function (if not included elsewhere)
function getuserRating($userRating) {
    return isset($userRating) && $userRating !== null ? $userRating : 0; // Default to 0 if user rating is not set or is null
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
        // Fetch the user information from the 'users' collection
        $userCollection = $db->users;
        $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);

        // If not found in 'users', check the 'google-users' collection
        if (!$author) {
            $userCollection = $db->selectCollection('google-users');
            $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
        }

        if ($author) {
            // Fetch the user details
            $authorPicture = '../uploads/' . ($author['picture'] ?? 'default.jpg'); // Default profile picture
            $username = $author['username'] ?? 'Unknown Author';
            $userRating = getuserRating($author['user_rating'] ?? null);
            $userRole = $author['role'] ?? 'No Role'; // Assuming role is stored in the user's collection

            // Fetch additional profile details from the 'user-profile' collection
            $profileCollection = $db->selectCollection('user-profile');
            $userProfile = $profileCollection->findOne(['user_id' => new ObjectId($userId)]);

            if ($userProfile) {
                // Handle 'about' field, ensuring it's a string
                $about = is_array($userProfile['about']) ? implode(', ', $userProfile['about']) : (string)($userProfile['about'] ?? 'No about info available');

                // Fetch work credentials and education credentials arrays
                $workCredentials = $userProfile['workCredentials'] ?? [];
                $educationCredentials = $userProfile['educationCredentials'] ?? [];
            } else {
                // Default values if user profile data is missing
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
?>


<body>
<!-- Display User Profile -->
<div class="visit-profile" style="margin-top: 68px;"> 
    <div class="user-profile">
        <div class="profile-picture-container">
            <img src="<?php echo htmlspecialchars($authorPicture); ?>" alt="Profile Picture">
        </div>
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <p>Role: <?php echo htmlspecialchars($userRole); ?></p>
            <p>User Rating: <?php echo htmlspecialchars($userRating); ?></p>
            
            <!-- Display the additional user profile information -->
            <h3>About</h3>
            <p><?php echo htmlspecialchars($about); ?></p>

            <h3>Work Credentials</h3>
            <?php if (!empty($workCredentials)): ?>
                <?php foreach ($workCredentials as $work): ?>
                    <?php if (isset($work['_id'])): ?>
                        <div class="credential">
                            <span>
                                <?php echo htmlspecialchars("{$work['position']} at {$work['organization']} ({$work['startYear']}-{$work['endYear']})"); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <p>Work credential does not have a valid ID.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No work credentials available.</p>
            <?php endif; ?>

            <h3>Education Credentials</h3>
            <?php if (!empty($educationCredentials)): ?>
                <?php foreach ($educationCredentials as $education): ?>
                    <?php if (isset($education['_id'])): ?>
                        <div class="credential">
                            <span>
                                <?php echo htmlspecialchars("School: {$education['school']}, Level: {$education['yearLevel']} ({$education['startYear']}-{$education['endYear']})"); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <p>Education credential does not have a valid ID.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No education credentials available.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
