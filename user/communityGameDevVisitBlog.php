<?php
// Include the navigation bar file which contains the MongoDB connection
include_once '../user/navigationBar.php';

use MongoDB\BSON\ObjectId;

// Function to handle rating check (default to 0 if null or missing)
function getRating($rating) {
    return isset($rating) && $rating !== null ? $rating : 0; // Return rating or 0 if null
}
function getblogRating($blogRating) {
    return isset($blogRating) && $blogRating !== null ? $blogRating : 0; // Return rating or 0 if null
}

function getuserRating($userRating) {
    return isset($userRating) && $userRating !== null ? $userRating : 0; // Return rating or 0 if null
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
        $blogRating = getblogRating($blog['blogRating'] ?? null); // Use the function to get the rating, defaulting to 0 if null
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
<div class="blog-content" style="margin-top: 70px;">
    
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
        <a href="#" id="save-blog-link">Save Blog</a>
        <div class="rate-blog">
            <button class="submit-rating-blog" id="submit-rating">Submit Rating</button>
            <div class="star-rating" id="star-rating">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
        </div>
        <button class="go-back-btn" onclick="location.href='communityGameDev.php';">Go Back</button>
    </div>


    <div class="blogpost-main-content" id="post-container">
        <div class="blogpost-content-container">
                <!-- Category, Date, and Rating -->
                <div class="blogpost-header">
                    <div class="blogpost-category"> <?php echo htmlspecialchars($category); ?></div>
                    <div class="blogpost-meta">
                        <span class="blogpost-date"> <?php echo $createdAt; ?></span>
                        •
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
                    <div class="blogcontentdescription"> <?php echo nl2br(htmlspecialchars($fullDescription)); ?></div>
                </div>

            <?php foreach ($elements as $element): ?>
                <?php if ($element['type'] == 'horizontalDescription'): ?>
                <div class="blogpost-horizontaldescription">
                    <div class="horizontaldescription">T<?php echo nl2br(htmlspecialchars($element['content'])); ?></div>
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
                    <div class="blogcontentdescription">T<?php echo nl2br(htmlspecialchars($element['content'])); ?>
                        </div>
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
 document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');

    // Attach click listener to the Save Blog link
    const saveBlogLink = document.getElementById('save-blog-link');
    if (saveBlogLink) {
        saveBlogLink.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent the default link behavior
            const blogId = '<?php echo $blogId; ?>'; // Pass the blog ID from PHP to JS
            console.log('Saving blog with ID:', blogId);
            saveBlog(blogId); // Call the saveBlog function
        });
    }

    // Save blog function
    function saveBlog(blogId) {
        console.log('Saving blog with ID:', blogId);

        fetch('addBlog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ blogId })
        })
        .then((response) => response.json())
        .then((data) => {
            console.log('Parsed JSON data:', data);
            if (data.success) {
                alert('Blog saved successfully!');
            } else {
                alert('This ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Error during fetch:', error);
            alert('An error occurred while saving the blog.');
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    let userRating = 0;

    // Fetch the logged-in user ID and blog author ID from PHP
    const loggedInUserId = '<?php echo $_SESSION['user_id'] ?? ""; ?>'; // User ID from session
    const blogAuthorId = '<?php echo $userId; ?>'; // Blog author ID

    // Handle star click
    document.querySelectorAll('.star').forEach((star) => {
        star.addEventListener('click', (event) => {
            userRating = parseInt(event.target.getAttribute('data-value'));
            // Highlight the stars based on the rating
            document.querySelectorAll('.star').forEach((s) => {
                s.style.color = s.getAttribute('data-value') <= userRating ? 'gold' : 'gray';
            });
        });
    });

    // Submit Rating button click
    document.getElementById('submit-rating').addEventListener('click', () => {
        if (loggedInUserId === blogAuthorId) {
            alert("You can't rate your own blog.");
            location.reload(); // Optionally reload the page to reflect the updates
            return; // Stop further execution
        }

        if (userRating === 0) {
            alert('Please select a rating before submitting.');
            return;
        }

        const blogId = '<?php echo $blogId; ?>'; // PHP to JS: Get the blog ID

        // Send the rating to the backend
        fetch('addRating.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                blogId: blogId,
                userId: loggedInUserId, // Pass the logged-in user's ID
                rating: userRating,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rating submitted successfully!');
                location.reload(); // Optionally reload the page to reflect the updates
            } else {
                alert('Error submitting rating: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('You already rated this blog.');
        });
    });
});


    </script>
</body>
</html>