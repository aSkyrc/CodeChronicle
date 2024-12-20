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
    $profilePicture = $userGoogleData['picture'] ?? '../logos/userDefault.png'; // Google picture or default
    $createdAt = $userGoogleData['created_at'] ?? time();
} elseif ($userData) {
    // For regular users
    $username = $userData['username'] ?? 'Guest'; // Fallback if username is not set
    $profilePicture = $userData['picture'] ?? '../logos/userDefault.png'; // Picture from uploads directory
    $createdAt = $userData['created_at'] ?? time();
} else {
    // If no user data is found in both collections
    echo "User data not found.";
    exit();
}

// Fetch role and interests from the user-profile collection
$role = $userProfileData['role'] ?? 'Student'; // Fallback to 'Student'
$interests = $userProfileData['interest'] ?? []; // Interests from user-profile

// Convert BSONArray to PHP array
$interestsArray = iterator_to_array($interests, false);

// Create the filter query for the blogs
$filter = [
    'category' => ['$in' => $interestsArray] // Match categories with user interests
];

// Fetch blog posts that match the user's interests, sorted by createdAt in descending order
$blogs = $blogCollection->find(
    $filter,
    ['sort' => ['createdAt' => -1]] // Sort by 'createdAt' in descending order
);
?>

<body>
    <div class="contents" style="margin-top: 68px"> 
        <div class="home-page-container">
            <aside class="homepage-sidebar">
                <div class="homepage-user-info-sidebar">
                    <div class="homepage-user-details">
                        <p class="username"><?php echo htmlspecialchars($username); ?></p>
                        <p class="role"><?php echo htmlspecialchars($role); ?></p> <!-- Display role here -->
                    </div>
                    <div class="homepage-line">
                        <h5>____________</h5>
                    </div>
                    <button class="post-blog-btn" onclick="location.href='blog.php';">Post Blog</button>
                </div>

                <div class="homepage-interests">
                    <a>Your Interests</a>
                    <h5 style="font-family: Verdana, Geneva, Tahoma, sans-serif;">__________________</h5>
                    <ul>
                        <button class="add-interest-btn" onclick="openModal()">+ Add Interest</button>
                        <?php if ($interests): ?>
                            <?php foreach ($interests as $interest): ?>
                                <li><?php echo htmlspecialchars($interest); ?></li> <!-- Display interests here -->
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No interests added yet.</li>
                        <?php endif; ?>
                        <h5 style="font-family: Verdana, Geneva, Tahoma, sans-serif;">__________________</h5>
                    </ul>      
                </div>
            </aside>

            <?php foreach ($blogs as $blog): ?>
            <div class="homepage-center-content" id="post-container">
            
                    <?php
                        // Fetch the user data for each blog author based on the user_id
                        $authorId = $blog['user_id']; // Assuming 'user_id' is stored in each blog post
                        // Check if the author is a Google user
                        $authorGoogleData = $googleUsersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);
                        // If it's a Google user, use their Google data
                        if ($authorGoogleData) {
                            $authorName = $authorGoogleData['username'] ?? 'Guest';
                            $authorPicture = $authorGoogleData['picture'] ?? '../logos/userDefault.png'; // Google user picture
                        } else {
                            // Otherwise, fetch regular user data
                            $authorData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($authorId)]);
                            $authorName = $authorData['username'] ?? 'Guest';
                            $authorPicture = '../uploads/' . ($authorData['picture'] ?? 'userDefault.png'); // Regular user picture
                        }

                        // Ensure picture path is correct
                        $authorPicturePath = htmlspecialchars($authorPicture);
                    ?>

                    <div class="post-card" style="margin-top: 20px; margin-right: 480px;" >
                        <div class="homepage-content-container">
                            <div class="homepage-user-info">
                                <a href="visit-profile.php?user_id=<?php echo urlencode((string)$authorId); ?>">
                                    <img src="<?php echo $authorPicturePath; ?>" alt="User Image">
                                </a>
                                <div class="user-info">
                                    <div class="user-name-rating">
                                        <div class="user-name"><?php echo htmlspecialchars($authorName); ?> <span class="dot">•</span></div>
                                        <div class="rating">(
                                        <?php 
                                            // Check if the rate is null or not set, and default to 0
                                            $rate = isset($blog['rating']) ? $blog['rating'] : 0;
                                            echo number_format($rate, 1); 
                                        ?>)
                                        </div>
                                    </div>
                                    <div class="category"><?php echo htmlspecialchars($blog['category'] ?? 'Uncategorized'); ?></div> <!-- Category -->
                                </div>
                            </div>

                            <div class="save">
                                <img 
                                    src="https://cdn-icons-png.flaticon.com/512/3916/3916593.png" 
                                    data-id="<?php echo htmlspecialchars((string)$blog['_id']); ?>" 
                                    alt="Save Blog Icon"
                                >
                                <!-- Hidden button -->
                                <button 
                                    style="display: none;" 
                                    data-id="<?php echo htmlspecialchars((string)$blog['_id']); ?>" 
                                    onclick="saveBlog(this)">
                                    Save Blog
                                </button>
                            </div>

                            <div class="homepage-text-content">
                                <p><?php echo htmlspecialchars($blog['title']); ?></p>
                            </div>

                            <div class="tutorial">
                                <h2><?php echo htmlspecialchars($blog['shortDescription']); ?></h2>
                            </div>

                            <button class="continue-button" onclick="location.href='blog-post.php?_id=<?php echo htmlspecialchars((string)$blog['_id']); ?>';">Continue reading...</button>
                        </div>
                        <img class="homepage-thumbnail" src="<?php echo htmlspecialchars($blog['thumbnailPath']); ?>"alt="Post Image" width="400px" height="500px">
                    </div>
                <?php endforeach; ?>
                <div class="homepage-sidebar-right">
                    <img src="../logos/blogcontent.jpg">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
<div id="homepage-interest-modal" class="homepage-modal">
    <div class="homepage-modal-content">
        <h3>What do you want to add?</h3>
       
        <form method="POST" action="addInterest.php" id="interest-form">
            <ul>
                <li><input type="checkbox" name="interest[]" value="Frontend Development" <?php echo in_array('Frontend Development', $interestsArray) ? 'checked' : ''; ?>> Frontend Development</li>
                <li><input type="checkbox" name="interest[]" value="Backend Development" <?php echo in_array('Backend Development', $interestsArray) ? 'checked' : ''; ?>> Backend Development</li>
                <li><input type="checkbox" name="interest[]" value="Data Science and Machine Learning" <?php echo in_array('Data Science and Machine Learning', $interestsArray) ? 'checked' : ''; ?>> Data Science and Machine Learning</li>
                <li><input type="checkbox" name="interest[]" value="Mobile Development" <?php echo in_array('Mobile Development', $interestsArray) ? 'checked' : ''; ?>> Mobile Development</li>
                <li><input type="checkbox" name="interest[]" value="DevOps and Cloud Computing" <?php echo in_array('DevOps and Cloud Computing', $interestsArray) ? 'checked' : ''; ?>> DevOps and Cloud Computing</li>
                <li><input type="checkbox" name="interest[]" value="Game Development" <?php echo in_array('Game Development', $interestsArray) ? 'checked' : ''; ?>> Game Development</li>
                <li><input type="checkbox" name="interest[]" value="Cybersecurity" <?php echo in_array('Cybersecurity', $interestsArray) ? 'checked' : ''; ?>> Cybersecurity</li>
                <li><input type="checkbox" name="interest[]" value="Programming Languages" <?php echo in_array('Programming Languages', $interestsArray) ? 'checked' : ''; ?>> Programming Languages</li>
                <li><input type="checkbox" name="interest[]" value="Algorithms and Data Structures" <?php echo in_array('Algorithms and Data Structures', $interestsArray) ? 'checked' : ''; ?>> Algorithms and Data Structures</li>
                <li><input type="checkbox" name="interest[]" value="Career and Networking" <?php echo in_array('Career and Networking', $interestsArray) ? 'checked' : ''; ?>> Career and Networking</li>
            </ul>
            <button class="add-interest-modal" type="submit">Add</button>
            <button class="cancel-interest-modal" type="button" onclick="closeModal()">Cancel</button>    
        </form>
    </div>
</div>


<script src="../screen/javascript/HOME-saved-function.js"></script>
<script>

</script>

</body>
</html>