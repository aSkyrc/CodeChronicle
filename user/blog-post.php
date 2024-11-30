<?php
// Include the navigation bar file which contains the MongoDB connection
include_once '../user/navigationBar.php';

use MongoDB\BSON\ObjectId;

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

        // Check if the blog was found
        if (!$blog) {
            echo "Blog not found.";
            exit();
        }

        // Retrieve blog details
        $title = $blog['title'] ?? 'No Title';
        $category = $blog['category'] ?? 'No Category';
        $thumbnailPath = $blog['thumbnailPath'] ?? '';
        $shortDescription = $blog['shortDescription'] ?? 'No Short Description';
        $fullDescription = $blog['fullDescription'] ?? 'No Full Description';

        // Retrieve user_id from the blog to fetch the author's info
        $userId = $blog['user_id'] ?? null;

        // Fetch the user's information (including picture) from the user collection
        $authorPicture = ''; // Default value for picture
        if ($userId) {
            // First, check in the 'users' collection
            $userCollection = $db->users; 
            $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
            
            // If the author is not found in 'users', check in 'google-users'
            if (!$author) {
                $userCollection = $db->google-users; // Check in google-users collection
                $author = $userCollection->findOne(['_id' => new ObjectId($userId)]);
            }

            // If the author is found, get the profile picture
            if ($author) {
                $authorPicture = '../uploads/' . ($author['picture'] ?? 'default.jpg'); // Constructing the image path
            }
        }

        // Convert elements to a PHP array
        $elements = isset($blog['elements']) ? $blog['elements'] : []; // Preserve original order
        $createdAt = date('Y-m-d H:i:s', $blog['createdAt'] ?? time());

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
    <h1><?php echo htmlspecialchars($title); ?></h1> <!-- Plain title display -->
    <p><strong>Category:</strong> <?php echo htmlspecialchars($category); ?></p>
    
    <?php if ($thumbnailPath): ?>
        <div class="thumbnail">
            <img src="<?php echo htmlspecialchars($thumbnailPath); ?>" alt="Thumbnail" style="width: 300px;">
        </div>
    <?php endif; ?>

    <p><strong>Short Description:</strong> <?php echo nl2br(htmlspecialchars($shortDescription)); ?></p>
    <p><strong>Full Description:</strong> <?php echo nl2br(htmlspecialchars($fullDescription)); ?></p>

    <!-- Render elements dynamically in the original order -->
    <?php foreach ($elements as $element): ?>
        <?php if ($element['type'] == 'horizontalDescription'): ?>
            <div class="horizontal-description">
                <p><strong>Horizontal Description:</strong> <?php echo nl2br(htmlspecialchars($element['content'])); ?></p>
            </div>
        <?php elseif ($element['type'] == 'videoLink'): ?>
            <?php
            // Extract the video ID from the YouTube URL
            preg_match('/(?:https?:\/\/(?:www\.)?youtube\.com\/(?:[^\/]+\/\S+\/|\S+?v=)([A-Za-z0-9_-]{11}))/', $element['content'], $matches);
            $videoId = $matches[1] ?? '';

            // Display the YouTube video player if the video ID is valid
            if ($videoId): ?>
                <div class="video-container">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $videoId; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        <?php elseif ($element['type'] == 'blogDescription'): ?>
            <div class="blog-description">
                <p><strong>Blog Description:</strong> <?php echo nl2br(htmlspecialchars($element['content'])); ?></p>
            </div>
        <?php elseif ($element['type'] == 'image'): ?>
            <div class="image-container">
                <img src="<?php echo htmlspecialchars($element['content']); ?>" alt="Image" style="width: 300px;">
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Display creation timestamp -->
    <p><strong>Created At:</strong> <?php echo $createdAt; ?></p>

    <!-- Display author picture if available -->
    <?php if ($authorPicture): ?>
        <div class="author-info">
            <h3>Author:</h3>
            <div class="author-picture">
                <img src="<?php echo htmlspecialchars($authorPicture); ?>" alt="Author Picture" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
            </div>
            <p><?php echo htmlspecialchars($author['username'] ?? 'Unknown Author'); ?></p> <!-- Assuming the author's name is stored in the 'username' field -->
        </div>
    <?php endif; ?>
</div>
