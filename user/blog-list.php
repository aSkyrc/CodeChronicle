<?php
// Include the navigation bar file which contains the MongoDB connection
include_once '../user/navigationBar.php';

// Fetch all blog entries from the blog collection
$collection = $db->blog;
$blogs = $collection->find(); // Get all blogs
?>

<div class="blog-list" style="margin-top: 70px;">
    <h1>All Blogs</h1>
    <?php foreach ($blogs as $blog): ?>
        <div class="blog-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
            <!-- Thumbnail Image -->
            <?php if (!empty($blog['thumbnailPath'])): ?>
                <div class="blog-thumbnail" style="margin-bottom: 15px;">
                    <img src="<?php echo htmlspecialchars($blog['thumbnailPath']); ?>" alt="Thumbnail" style="width: 150px; height: auto; border-radius: 5px;">
                </div>
            <?php endif; ?>

            <!-- Blog Title -->
            <h2><?php echo htmlspecialchars($blog['title']); ?></h2>

            <!-- Blog Details -->
            <p><strong>Category:</strong> <?php echo htmlspecialchars($blog['category']); ?></p>
            <p><strong>Short Description:</strong> <?php echo nl2br(htmlspecialchars($blog['shortDescription'])); ?></p>
            
            <!-- Button to view full blog post -->
            <div style="margin-top: 10px;">
                <a href="blog-post.php?_id=<?php echo htmlspecialchars((string)$blog['_id']); ?>">
                    <button style="padding: 10px 20px; background-color: #656ee6; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Read Full Post
                    </button>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
                

</body>
</html>
