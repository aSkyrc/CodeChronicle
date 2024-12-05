<?php
include_once '../user/navigationBar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data from the request
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $shortDescription = $_POST['shortDescription'] ?? '';
    $fullDescription = $_POST['fullDescription'] ?? '';
    $blogDescriptions = $_POST['blogDescriptions'] ?? [];
    $horizontalDescriptions = $_POST['horizontalDescriptions'] ?? [];
    $videoLinks = $_POST['videoLinks'] ?? [];
    $images = $_FILES['images'] ?? []; // Capture image uploads
    $elementOrder = json_decode($_POST['elementOrder'] ?? '[]', true); // Decode the order of elements

    // Prepare upload directory
    $uploadsDir = '../uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    // Allowed file extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    // Handle thumbnail upload with validation
$thumbnailPath = '';
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $thumbnailExtension = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    if (in_array($thumbnailExtension, $allowedExtensions)) {
        $thumbnailPath = $uploadsDir . basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
    } else {
        echo '<script>
                alert("Only JPG, PNG, and JPEG files are allowed for the thumbnail.");
                window.history.back(); // Redirect the user back to the form
              </script>';
        exit; // Prevent further processing if file extension is invalid
    }
}

// Handle additional image uploads with validation
$uploadedImages = [];
if (!empty($images['name'][0])) { // Check if at least one image is uploaded
    foreach ($images['name'] as $key => $imageName) {
        $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        if (in_array($imageExtension, $allowedExtensions)) {
            if ($images['error'][$key] === UPLOAD_ERR_OK) {
                $imagePath = $uploadsDir . basename($imageName);
                move_uploaded_file($images['tmp_name'][$key], $imagePath);
                $uploadedImages[] = $imagePath;
            }
        } else {
            echo '<script>
                    alert("Only JPG, PNG, and JPEG files are allowed for uploaded images.");
                    window.history.back(); // Go back to the form to choose another file
                  </script>';
            exit; // Stop further processing if any image is invalid
        }
    }
}

    // Build elements array based on the order specified in elementOrder
    $elements = [];
    foreach ($elementOrder as $order) {
        if (is_array($order)) { // Ensure $order is an array
            switch ($order['type']) {
                case 'blogDescription':
                    if (!empty($blogDescriptions)) {
                        $elements[] = [
                            'id' => $order['id'],
                            'type' => 'blogDescription',
                            'content' => array_shift($blogDescriptions),
                        ];
                    }
                    break;
                case 'horizontalDescription':
                    if (!empty($horizontalDescriptions)) {
                        $elements[] = [
                            'id' => $order['id'],
                            'type' => 'horizontalDescription',
                            'content' => array_shift($horizontalDescriptions),
                        ];
                    }
                    break;
                case 'image':
                    if (!empty($uploadedImages)) {
                        $elements[] = [
                            'id' => $order['id'],
                            'type' => 'image',
                            'content' => array_shift($uploadedImages),
                        ];
                    }
                    break;
                case 'videoLink':
                    if (!empty($videoLinks)) {
                        $elements[] = [
                            'id' => $order['id'],
                            'type' => 'videoLink',
                            'content' => array_shift($videoLinks),
                        ];
                    }
                    break;
            }
        } else {
            echo "Invalid data structure in element order.";
        }
    }

    // Prepare blog data to insert into MongoDB
    $blogData = [
        'user_id' => $_SESSION['user_id'], // Use the logged-in user's ID
        'title' => $title,
        'category' => $category,
        'thumbnailPath' => $thumbnailPath,
        'shortDescription' => $shortDescription,
        'fullDescription' => $fullDescription,
        'elements' => $elements, // Insert the elements array
        'createdAt' => time(),
    ];

    // Insert the blog into the MongoDB collection
    try {
        $collection = $db->blog; // Use your blog collection
        $result = $collection->insertOne($blogData);
        echo '<script type="text/javascript">
                alert("Blog uploaded successfully!");
                window.location.href = "homepage.php"; 
              </script>';
    } catch (Exception $e) {
        echo '<script type="text/javascript">
                alert("Error saving blog: ' . $e->getMessage() . '");
              </script>';
    }
}
?>

<form id="blog-form" method="POST" enctype="multipart/form-data">
    <div class="Post-Blog-Page">
        <a href="homepage.php">
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
                <p>Note: This is the actual format of your blog.</p>
            </div>

            <div class="content">
                <div class="form-group">
                    <label for="content-title">Title</label>
                    <input type="text" id="content-title" name="title" maxlength="29" placeholder="Content Title" required>
                </div>

                <div class="form">
                    <div class="form-group-flex">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Frontend Development">Frontend Development</option>
                                <option value="Backend Development">Backend Development</option>
                                <option value="Data Science and Machine Learning">Data Science and Machine Learning</option>
                                <option value="Mobile Development">Mobile Development</option>
                                <option value="DevOps and Cloud Computing">DevOps and Cloud Computing</option>
                                <option value="Cybersecurity">Cybersecurity</option>
                                <option value="Programming Language">Programming Language</option>
                                <option value="Algorithms and Data Structures">Algorithms and Data Structures</option>
                                <option value="Game Development">Game Development</option>
                                <option value="Career and Networking">Career and Networking</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image-upload">Image Thumbnail</label>
                            <input type="file" id="image-upload" name="thumbnail" required>
                        </div>
                    </div>

                    <div class="form-group-short">
                        <label for="short-description">Short Description</label>
                        <textarea id="short-description" name="shortDescription" maxlength="200" placeholder="Short Description" required></textarea>
                        <small class="char-count" id="short-description-char-count">0/200 characters</small>
                    </div>

                    <div id="full-blog-content-description-section" class="form-group-full">
                        <label for="full-blog-content-description">Blog Content Description</label>
                        <textarea id="full-blog-content-description" name="fullDescription" maxlength="532" placeholder="Blog Content Description" required></textarea>
                        <small class="char-count" id="full-blog-description-char-count">0/532 characters</small>
                    </div>

                    <div id="dynamic-content-container"> 
                        <div id="dynamic-blog-description-section"></div>
                        <div id="dynamic-horizontal-blog-description-section" class="form-group-horizontal"></div>
                        <div id="dynamic-image-upload-section"></div>
                        <div id="dynamic-video-link-section"></div>
                    </div>
                    
                    <div class="upload-btn">
                        <button type="submit">Upload</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="../screen/javascript/blogs.js"></script>
</body>
</html>