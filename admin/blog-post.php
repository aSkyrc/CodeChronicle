<?php
// Include the navigation bar file which contains the MongoDB connection
include_once '../admin/adminId.php';

use MongoDB\BSON\ObjectId;

// Function to process the user rating (fallback to 0 if not numeric)
function getuserRating($rating) {
    return is_numeric($rating) ? $rating : 0; // Ensure it's a valid number
}

// Function to process the blog rating (fallback to 0 if not numeric)
function getRating($rating) {
    return is_numeric($rating) ? $rating : 0; // Ensure it's a valid number
}

// Get the blog ID from the URL (ensure it's passed)
$blogId = $_GET['_id'] ?? null;

// Check if a blog ID is provided
if ($blogId) {
    try {
        // Convert the string _id to ObjectId
        $objectId = new ObjectId($blogId);

        // Access the blog collection
        $collection = $db->blog;
        $blog = $collection->findOne(['_id' => $objectId]); // Query using ObjectId

        // Retrieve blog details
        $title = $blog['title'] ?? 'No Title';
        $category = $blog['category'] ?? 'No Category';
        $thumbnailPath = $blog['thumbnailPath'] ?? '';
        $shortDescription = $blog['shortDescription'] ?? 'No Short Description';
        $fullDescription = $blog['fullDescription'] ?? 'No Full Description';

        // Retrieve user_id from the blog to fetch the author's info
        $userId = $blog['user_id'] ?? null;
        // Fetch the user's information (including picture and role) from the relevant collections
        $authorPicture = ''; // Default value for picture
        $userRole = 'No Role'; // Default role
        $userRating = 0; // Default user rating
        if ($userId) {
            // First, check in the 'users' collection
            $userCollection = $db->users; 
            $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
            
            // If the author is not found in 'users', check in 'google-users'
            if (!$author) {
                $userCollection = $db->selectCollection('google-users');  // Check in google-users collection
                $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
            }

            // If the author is found, get the profile picture and user rating
            if ($author) {
                $authorPicture = '../uploads/' . ($author['picture'] ?? 'default.jpg'); // Constructing the image path
                $userRating = getuserRating($author['user_rating'] ?? null); // Retrieve and set the user rating
            }

            // Fetch user's role from the 'user-profile' collection
            $profileCollection = $db->selectCollection('user-profile'); // Access the user-profile collection
            $userProfile = $profileCollection->findOne(['user_id' => new ObjectId($userId)]);

            // If a profile exists, fetch the role
            if ($userProfile) {
                $userRole = $userProfile['role'] ?? 'No Role';
            }
        }

        // Convert elements to a PHP array
        $elements = isset($blog['elements']) ? $blog['elements'] : []; // Preserve original order
        $createdAt = date('Y-m-d', $blog['createdAt'] ?? time());

        // Get the rating (ensure it's not null)
        $rating = getRating($blog['rating'] ?? null); // Use the function to get the rating, defaulting to 0 if null
        $blogRating = getRating($blog['blogRating'] ?? null); // Use the function to get the rating, defaulting to 0 if null
        $ratingCount = $blog['ratingCount'] ?? 0; // Default to 0 if no rating count is available

    } catch (Exception $e) {
        echo "Error fetching blog data: " . $e->getMessage();
        exit();
    }
} else {
    echo "Error: Blog ID is missing. Please ensure the blog ID is provided in the URL.";
    exit();
}
?>

<!-- Display blog content -->
<div class="blog-content">
    <div class="blogpost-sidebar">
        <?php if ($authorPicture): ?>
        <div class="blogpost-profile-container">
            <img class="profile-picture" src="<?php echo htmlspecialchars($authorPicture); ?>" alt="Profile Picture">
            <div class="profile-details">
                <div class="author-name"><?php echo htmlspecialchars($author['username'] ?? 'Unknown Author'); ?></div>
                <div class="role-description"><?php echo htmlspecialchars($userRole); ?></div>
                <div class="user-rating">User Rating: <span class="rating-value" id="rating-value"><?php echo htmlspecialchars($userRating); ?></span></div>
            </div>
        </div>
        <?php endif; ?>
        
        <hr>
        <a href="visit-profile.php?user_id=<?php echo htmlspecialchars($userId); ?>">Visit Author Profile</a>
        <div class="rate-blog">
            <button class="submit-rating-blog" id="submit-rating">Delete Blog</button>
        </div>
        <button class="go-back-btn" onclick="window.history.back();">Go Back</button>
    </div>

    <div class="blogpost-main-content" id="post-container">
        <div class="blogpost-content-container">
            <!-- Category, Date, and Rating -->
            <div class="blogpost-header">
                <div class="blogpost-category"><?php echo htmlspecialchars($category); ?></div>
                <div class="blogpost-meta">
                    <span class="blogpost-date"><?php echo $createdAt; ?></span> •
                    <span class="blogpost-rating">Blog Rating: <?php echo htmlspecialchars($rating); ?></span>
                    <span class="blogpost-rating">(<?php echo htmlspecialchars($ratingCount); ?>)</span>
                </div>
            </div>

            <!-- Title and Thumbnail -->
            <div class="blogpost-title-thumbnail">
                <div class="text-content">
                    <div class="blogpost-title"><?php echo htmlspecialchars($title); ?></div>
                    <div class="blogpost-shortdescription"><?php echo nl2br(htmlspecialchars($shortDescription)); ?></div>
                </div>
                <?php if ($thumbnailPath): ?>
                <div class="img-thumbnail">
                    <img src="<?php echo htmlspecialchars($thumbnailPath); ?>" alt="Thumbnail" />
                </div>
                <?php endif; ?>
            </div>

            <!-- Full Blog Description -->
            <div class="blogpost-fullblogcontentdescription">
                <div class="blogcontentdescription"><?php echo nl2br(htmlspecialchars($fullDescription)); ?></div>
            </div>

            <?php foreach ($elements as $element): ?>
                <?php if ($element['type'] == 'horizontalDescription'): ?>
                <div class="blogpost-horizontaldescription">
                    <div class="horizontaldescription"><?php echo nl2br(htmlspecialchars($element['content'])); ?></div>
                </div>
                <?php elseif ($element['type'] == 'videoLink'): ?>
            <?php
            // Extract the video ID from the YouTube URL
            preg_match('/(?:https?:\/\/(?:www\.)?youtube\.com\/(?:[^\/]+\/\S+\/|\S+?v=)([A-Za-z0-9_-]{11}))/', $element['content'], $matches);
            $videoId = $matches[1] ?? '';

            // Display the YouTube video player if the video ID is valid
            if ($videoId): ?>
                <div class="youtube-video-container">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $videoId; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php endif; ?>

            <?php elseif ($element['type'] == 'blogDescription'): ?>
                <div class="blogpost-blogcontentdescription">
                    <div class="blogcontentdescription"><?php echo nl2br(htmlspecialchars($element['content'])); ?></div>
                </div>

                <?php elseif ($element['type'] == 'image'): ?>
                <div class="blogpost-imageuploadsection">
                    <img src="../uploads/hanni-newjeans-get-up-4k-wallpaper-uhdpaper.com-948@1@k.jpg" alt="Uploaded Content" />
                </div>
                <?php endif; ?>
            <?php endforeach; ?>    
        </div>
    </div>
</div>
<script>
    document.getElementById('submit-rating').addEventListener('click', function () {
        // Show the confirmation dialog
        var confirmation = confirm("Are you sure you want to delete this blog?");
        if (confirmation) {
            // Make an AJAX request to delete the blog without redirecting
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "deleteBlogContent.php?_id=" + <?php echo json_encode($blogId); ?>, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Show success alert
                    alert("The blog has been deleted successfully.");

                    // Go back to the previous page in history
                    window.history.back(); 

                    // Reload the previous page after going back
                    setTimeout(function() {
                        location.reload(); // Reload the page once the back navigation is done
                    }, 500); // Delay to ensure that the back action happens first
                } else {
                    // If there was an error, show an alert
                    alert("Error deleting the blog.");
                }
            };
            xhr.send();
        }
    });
</script>


</body>
</html>
