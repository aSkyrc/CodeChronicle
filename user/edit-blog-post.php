<?php
include_once '../user/navigationBar.php';
use MongoDB\BSON\ObjectId;

$blogId = $_GET['_id'] ?? null;

$maxCharacters = [
    'horizontalDescription' => 360,
    'blogDescription' => 532,
];

try {
    $blogId = new ObjectId($blogId);
    $blogsCollection = $db->selectCollection('blog');
    $blog = $blogsCollection->findOne(['_id' => $blogId]);

    if (!$blog) {
        echo "Blog not found.";
        exit();
    }

    $title = $blog['title'];
    $category = $blog['category'];
    $shortDescription = $blog['shortDescription'];
    $fullDescription = $blog['fullDescription'];
    $thumbnailPath = $blog['thumbnailPath'];
    $elements = $blog['elements'];

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

$existingElements = $elements ?? [];
$dynamicContentHtml = '';

foreach ($existingElements as $index => $element) {
    $elementId = uniqid("element_"); // Generate unique ID for each element
    switch ($element['type']) {
        case 'horizontalDescription':
            $dynamicContentHtml .= '<div class="form-group-horizontal" id="' . $element['id'] . '">';
            $dynamicContentHtml .= '<div class="label-container">';
            $dynamicContentHtml .= '<label for="horizontal-description">Horizontal Description</label>';
            $dynamicContentHtml .= '<button type="button" class="remove-btn" onclick="removeElement(this)">x</button>';
            $dynamicContentHtml .= '</div>';
            $dynamicContentHtml .= '<textarea name="horizontalDescriptions[' . $element['id'] . ']" class="horizontalDescription" placeholder="Horizontal Description" maxlength="' . htmlspecialchars($maxCharacters['horizontalDescription']) . '" oninput="updateCharCount(this)">' . htmlspecialchars($element['content']) . '</textarea>';
            $dynamicContentHtml .= '<small class="char-count">0/' . htmlspecialchars($maxCharacters['horizontalDescription']) . ' characters</small>';
            $dynamicContentHtml .= '</div>';
            break;
    
        case 'blogDescription':
            $dynamicContentHtml .= '<div class="form-group-full" id="' . $element['id'] . '">';
            $dynamicContentHtml .= '<div class="label-container">';
            $dynamicContentHtml .= '<label for="blog-description">Blog Content Description</label>';
            $dynamicContentHtml .= '<button type="button" class="remove-btn" onclick="removeElement(this)">x</button>';
            $dynamicContentHtml .= '</div>';
            $dynamicContentHtml .= '<textarea name="blogDescriptions[' . $element['id'] . ']" class="blogDescription" placeholder="Blog Description" maxlength="' . htmlspecialchars($maxCharacters['blogDescription']) . '" oninput="updateCharCount(this)">' . htmlspecialchars($element['content']) . '</textarea>';
            $dynamicContentHtml .= '<small class="char-count">0/' . htmlspecialchars($maxCharacters['blogDescription']) . ' characters</small>';
            $dynamicContentHtml .= '</div>';
            break;
    
        case 'image':
            $dynamicContentHtml .= '<div class="form-group" id="' . $element['id'] . '">';
            $dynamicContentHtml .= '<div class="label-container">';
            $dynamicContentHtml .= '<label for="image-upload">Image</label>';
            $dynamicContentHtml .= '<button type="button" class="remove-btn" onclick="removeElement(this)">x</button>';
            $dynamicContentHtml .= '</div>';
            $dynamicContentHtml .= '<input type="file" name="images[' . $element['id'] . ']" class="image" accept="image/*">';
            $dynamicContentHtml .= '</div>';
            break;
    
        case 'videoLink':
            $dynamicContentHtml .= '<div class="form-group" id="' . $element['id'] . '">';
            $dynamicContentHtml .= '<div class="label-container">';
            $dynamicContentHtml .= '<label for="video-link">Video Link</label>';
            $dynamicContentHtml .= '<button type="button" class="remove-btn" onclick="removeElement(this)">x</button>';
            $dynamicContentHtml .= '</div>';
            $dynamicContentHtml .= '<input type="url" name="videoLinks[' . $element['id'] . ']" class="videoLink" value="' . htmlspecialchars($element['content']) . '" placeholder="Video Link">';
            $dynamicContentHtml .= '</div>';
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data from POST
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $shortDescription = $_POST['shortDescription'] ?? '';
    $fullDescription = $_POST['fullDescription'] ?? '';
    $blogDescriptions = $_POST['blogDescriptions'] ?? [];
    $horizontalDescriptions = $_POST['horizontalDescriptions'] ?? [];
    $videoLinks = $_POST['videoLinks'] ?? [];
    $images = $_FILES['images'] ?? [];
    $existingImages = $_POST['existingImages'] ?? [];
    $elementOrder = json_decode($_POST['elementOrder'] ?? '[]', true); // JSON array of element IDs and types
    $removedElements = json_decode($_POST['removedElements'] ?? '[]', true); // IDs of removed elements
    $blogId = $_GET['_id'] ?? ''; // Blog ID passed via URL

    // Fetch existing blog data from the database
    $existingBlog = $blogsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($blogId)]);
    if (!$existingBlog) {
        echo '<script>alert("Blog not found."); window.history.back();</script>';
        exit;
    }

    $existingElements = $existingBlog['elements'] ?? [];

    // Prepare the uploads directory
    $uploadsDir = '../uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    // Handle thumbnail upload
    $thumbnailPath = $existingBlog['thumbnailPath'] ?? ''; // Preserve existing thumbnail if not replaced
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnailPath = $uploadsDir . basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
    }

    // Handle additional image uploads with validation
    $uploadedImages = [];
    if (!empty($images['name'][0])) { // Check if at least one image is uploaded
        foreach ($images['name'] as $key => $imageName) {
            $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($imageExtension, $allowedExtensions)) {
                if ($images['error'][$key] === UPLOAD_ERR_OK) {
                    // Prepare file path for the uploaded image
                    $imagePath = $uploadsDir . uniqid() . '_' . basename($imageName);

                    // Move the uploaded image to the destination folder
                    if (move_uploaded_file($images['tmp_name'][$key], $imagePath)) {
                        // Map the image to its corresponding element ID
                        $uploadedImages[$images['element_id'][$key]] = $imagePath; // `element_id` comes from the form
                    } else {
                        echo '<script>alert("Failed to upload image: ' . $imageName . '");</script>';
                    }
                }
            } else {
                echo '<script>alert("Only JPG, JPEG, and PNG files are allowed for uploaded images.");</script>';
            }
        }
    }

    // Step 1: Update existing elements
    $updatedElements = [];
    foreach ($existingElements as $existingElement) {
        // Skip removed elements
        if (in_array($existingElement['id'], $removedElements)) {
            continue;
        }

        // Update the content based on the type
        switch ($existingElement['type']) {
            case 'blogDescription':
                if (isset($blogDescriptions[$existingElement['id']])) {
                    $existingElement['content'] = $blogDescriptions[$existingElement['id']];
                }
                break;

            case 'horizontalDescription':
                if (isset($horizontalDescriptions[$existingElement['id']])) {
                    $existingElement['content'] = $horizontalDescriptions[$existingElement['id']];
                }
                break;

            case 'image':
                if (isset($existingImages[$existingElement['id']])) {
                    // Replace with new image if uploaded, else retain the existing one
                    $existingElement['content'] = $uploadedImages[$existingElement['id']] ?? $existingImages[$existingElement['id']];
                }
                break;

            case 'videoLink':
                if (isset($videoLinks[$existingElement['id']])) {
                    $existingElement['content'] = $videoLinks[$existingElement['id']];
                }
                break;

            default:
                break; // Handle any other types if necessary
        }

        $updatedElements[] = $existingElement;
    }

    // Step 2: Add new elements
    foreach ($elementOrder as $order) {
        $existingIds = array_column($updatedElements, 'id');
        if (!in_array($order['id'], $existingIds)) {
            switch ($order['type']) {
                case 'blogDescription':
                    if (!empty($blogDescriptions)) {
                        $updatedElements[] = [
                            'id' => $order['id'],
                            'type' => 'blogDescription',
                            'content' => array_shift($blogDescriptions),
                        ];
                    }
                    break;

                case 'horizontalDescription':
                    if (!empty($horizontalDescriptions)) {
                        $updatedElements[] = [
                            'id' => $order['id'],
                            'type' => 'horizontalDescription',
                            'content' => array_shift($horizontalDescriptions),
                        ];
                    }
                    break;

                case 'image':
                    if (!empty($uploadedImages)) {
                        $updatedElements[] = [
                            'id' => $order['id'],
                            'type' => 'image',
                            'content' => array_shift($uploadedImages),
                        ];
                    }
                    break;

                case 'videoLink':
                    if (!empty($videoLinks)) {
                        $updatedElements[] = [
                            'id' => $order['id'],
                            'type' => 'videoLink',
                            'content' => array_shift($videoLinks),
                        ];
                    }
                    break;

                default:
                    break;
            }
        }
    }

    // Step 3: Prepare updated blog data
    $blogData = [
        'user_id' => $_SESSION['user_id'], // User ID from session
        'title' => $title ?: $existingBlog['title'], // Preserve title if not updated
        'category' => $category ?: $existingBlog['category'], // Preserve category if not updated
        'thumbnailPath' => $thumbnailPath,
        'shortDescription' => $shortDescription ?: $existingBlog['shortDescription'],
        'fullDescription' => $fullDescription ?: $existingBlog['fullDescription'],
        'elements' => $updatedElements,
        'updatedAt' => time(),
    ];

    // Step 4: Save the blog data back to the database
    try {
        $result = $blogsCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($blogId)],
            ['$set' => $blogData]
        );

        if ($result->getModifiedCount() > 0) {
            echo '<script>alert("Blog updated successfully!"); window.location.href = "profile.php";</script>';
        } else {
            echo '<script>alert("No changes made.");</script>';
        }
    } catch (Exception $e) {
        echo '<script>alert("Error updating blog: ' . $e->getMessage() . '");</script>';
    }
}
?>




<form id="blog-form" method="POST" enctype="multipart/form-data">
    <div class="Post-Blog-Page">
        <a href="profile.php">
            <div class="icon-container-back">
                <img src="https://cdn-icons-png.flaticon.com/512/3916/3916840.png" alt="Back Icon" class="icon-back">
            </div>
        </a>

        <div class="container">
            <div class="sidebar">
                <h3>Tools</h3>
                <div class="linetools">
                    <h6>________________________</h6>
                </div>  
                <ul>
                    <li><a href="#" id="add-blog-description">Add Blog Description</a></li>
                    <li><a href="#" id="add-horizontal-description">Add Horizontal Blog Description</a></li>
                    <li><a href="#" id="add-image">Add Image</a></li>
                    <li><a href="#" id="add-video-link">Add Video Link</a></li>
                </ul>
                <p>Note: Make sure you use the actual format of your blog.</p>
            </div>

            <div class="content">
                <div class="form-group">
                    <label for="content-title">Title</label>
                    <input type="text" id="content-title" name="title" value="<?php echo htmlspecialchars($title); ?>" maxlength="50" placeholder="Content Title" required>
                </div>

                <div class="form">
                    <div class="form-group-flex">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Frontend Development" <?php echo ($category == "Frontend Development") ? 'selected' : ''; ?>>Frontend Development</option>
                                <option value="Backend Development" <?php echo ($category == "Backend Development") ? 'selected' : ''; ?>>Backend Development</option>
                                <option value="Data Science and Machine Learning" <?php echo ($category == "Data Science and Machine Learning") ? 'selected' : ''; ?>>Data Science and Machine Learning</option>
                                <option value="Mobile Development" <?php echo ($category == "Mobile Development") ? 'selected' : ''; ?>>Mobile Development</option>
                                <option value="DevOps and Cloud Computing" <?php echo ($category == "DevOps and Cloud Computing") ? 'selected' : ''; ?>>DevOps and Cloud Computing</option>
                                <option value="Cybersecurity" <?php echo ($category == "Cybersecurity") ? 'selected' : ''; ?>>Cybersecurity</option>
                                <option value="Programming Language" <?php echo ($category == "Programming Language") ? 'selected' : ''; ?>>Programming Language</option>
                                <option value="Algorithms and Data Structures" <?php echo ($category == "Algorithms and Data Structures") ? 'selected' : ''; ?>>Algorithms and Data Structures</option>
                                <option value="Game Development" <?php echo ($category == "Game Development") ? 'selected' : ''; ?>>Game Development</option>
                                <option value="Career and Networking" <?php echo ($category == "Career and Networking") ? 'selected' : ''; ?>>Career and Networking</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required>
                        </div>
                    </div>

                    <div class="form-group-short">
                        <label for="shortDescription">Short Description</label>
                        <textarea id="shortDescription" name="shortDescription" required><?php echo htmlspecialchars($shortDescription); ?></textarea>
                        <small class="char-count">0/200 characters</small>
                    </div>

                    <div class="form-group-full">
                        <label for="fullDescription">Blog Content Description</label>
                        <textarea id="fullDescription" name="fullDescription" required><?php echo htmlspecialchars($fullDescription); ?></textarea>
                        <small class="char-count">0/532 characters</small>
                    </div>

                    <!-- Dynamic elements container -->
                    <div id="dynamic-content-container">
                        <?php echo $dynamicContentHtml; ?>
                    </div>

                    <!-- Hidden field to store the order of elements -->
                    <input type="hidden" name="elementOrder" id="elementOrder" value="">
                    
                    <input type="hidden" id="removedElements" name="removedElements" value="[]">

                    <div class="upload-btn">
                        <button type="submit">Update Blog</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<script src="../screen/javascript/UPDATEBLOG.js"></script>

</body>
</html>