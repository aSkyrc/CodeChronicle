<?php
// Include the navigation bar file which contains the MongoDB connection
include_once '../user/navigationBar.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Convert user_id to ObjectId if stored as ObjectId in the database
use MongoDB\BSON\ObjectId;
if (preg_match('/^[a-f0-9]{24}$/', $user_id)) {
    $user_id = new ObjectId($user_id);
}

// Access the blog collection
$blogsCollection = $db->selectCollection('blog');

// Fetch all blogs created by the logged-in user (using user_id instead of created_by)
$userBlogsCursor = $blogsCollection->find(['user_id' => $user_id]);

// Convert the cursor to an array
$userBlogs = iterator_to_array($userBlogsCursor);

?>

<body>
    <div class="profile" style="margin-top: 70px;">
        <h1>My Blogs</h1>
        <div>
            <?php if (empty($userBlogs)): ?>
                <p>You haven't created any blogs yet.</p>
            <?php else: ?>
                <?php foreach ($userBlogs as $blog): ?>
                    <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <h2><?php echo htmlspecialchars($blog['title']); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($blog['shortDescription'] ?? 'No description available')); ?></p>
                        <small>Published on: 
                            <?php 
                            echo isset($blog['createdAt']) 
                                ? date('F j, Y', $blog['createdAt'])  // Assuming 'createdAt' is already a Unix timestamp
                                : 'Unknown date';
                            ?>
                        </small>

                        <!-- Edit and Delete buttons -->
                        <div style="margin-top: 10px;">
                            <!-- Edit Button (direct to edit page with blog id in URL) -->
                            <a href="edit-blog-post.php?_id=<?php echo $blog['_id']; ?>" style="text-decoration: none; padding: 5px 10px; background-color: #4CAF50; color: white; border-radius: 5px;">Edit</a>

                            <!-- Delete Button (link to delete handler with blog id) -->
                            <a href="deleteBlog.php?_id=<?php echo $blog['_id']; ?>" style="text-decoration: none; padding: 5px 10px; background-color: #f44336; color: white; border-radius: 5px; margin-left: 10px;" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
