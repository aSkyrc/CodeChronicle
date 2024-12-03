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
    
    if (!isset($_GET['community'])) {
        header("Location: ./community.php");
        exit; // To prevent further code execution after the redirect
    }


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
        // Redirect back to the specific community page based on the community parameter
        echo '<script type="text/javascript">
                    alert("Blog uploaded successfully!");
                    window.location.href = "community.php"; 
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
        <a href="community.php">
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
                    <input type="text" id="content-title" name="title" maxlength="29" placeholder="Content Title" required>
                </div>

                <div class="form">
                    <div class="form-group-flex">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" disabled>
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
                        <textarea id="short-description" name="shortDescription" maxlength="260" placeholder="Short Description" required></textarea>
                        <small class="char-count" id="short-description-char-count">0/260 characters</small>
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

<script>

       // Function to set the selected community category in the dropdown
       window.onload = function() {
        // Retrieve the 'community' parameter from the URL
        var urlParams = new URLSearchParams(window.location.search);
        var selectedCommunity = urlParams.get('community');
        
        if (selectedCommunity) {
            // Set the category dropdown to the selected community
            var categorySelect = document.getElementById('category');
            for (var i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === selectedCommunity) {
                    categorySelect.selectedIndex = i; // Set the selected index
                    break;
                }
            }
        }
    }


    let elementOrder = []; // Track the order of added elements

document.getElementById('add-horizontal-description').addEventListener('click', function () {
    addElement('horizontalDescription');
});

document.getElementById('add-blog-description').addEventListener('click', function () {
    addElement('blogDescription');
});

document.getElementById('add-image').addEventListener('click', function () {
    addElement('image');
});

document.getElementById('add-video-link').addEventListener('click', function () {
    addElement('videoLink');
});

// Maximum character limits for the specific fields
const maxCharacters = {
    horizontalDescription: 360,
    blogDescription: 532,
    shortDescription: 260,
    fullBlogDescription: 532
};

// Add character limit enforcement on input
document.addEventListener('input', function (event) {
    if (event.target.id === 'short-description' || event.target.id === 'full-blog-content-description') {
        enforceCharacterLimit(event.target);
    }
});



// Function to enforce character limits
function enforceCharacterLimit(textarea) {
    const id = textarea.id;
    let limit = 0;

    if (id === 'short-description') {
        limit = maxCharacters.shortDescription;
    } else if (id === 'full-blog-content-description') {
        limit = maxCharacters.fullBlogDescription;
    } else if (textarea.classList.contains('horizontalDescription')) {
        limit = maxCharacters.horizontalDescription;
    } else if (textarea.classList.contains('blogDescription')) {
        limit = maxCharacters.blogDescription;
    }

    if (textarea.value.length > limit) {
        textarea.value = textarea.value.slice(0, limit); // Trim the value to the limit
        alert(`You can only enter up to ${limit} characters.`);
    }

    // Update character counter
    const charCounter = textarea.nextElementSibling;
    charCounter.textContent = `${textarea.value.length}/${limit} characters`;
}

// Function to add elements dynamically based on type
function addElement(type) {
    const container = document.getElementById('dynamic-content-container');

    // Maximum counts can be adjusted if needed
    const maxCount = {
        horizontalDescription: 3,
        videoLink: 2,
        image: 2,
        blogDescription: 4
    };

    // Count the elements of the same type
    const currentCount = document.querySelectorAll(`.${type}`).length;

    if (currentCount >= maxCount[type]) {
        alert(`You can only add up to ${maxCount[type]} ${type === 'horizontalDescription' ? 'Horizontal Descriptions' : type.charAt(0).toUpperCase() + type.slice(1)}.`);
        return;
    }

    // Generate a unique ID for the new element
    const elementId = `element-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

    // Create the new element based on type
    let newElement;
    if (type === 'horizontalDescription') {
        newElement = createHorizontalDescription(elementId);
    } else if (type === 'blogDescription') {
        newElement = createBlogDescription(elementId);
    } else if (type === 'image') {
        newElement = createImageElement(elementId);
    } else if (type === 'videoLink') {
        newElement = createVideoLinkElement(elementId);
    }

    // Store the element with its ID and type in the elementOrder array
    elementOrder.push({ id: elementId, type: type, element: newElement });

    // Defer the DOM update to avoid layout thrashing
    setTimeout(() => {
        container.innerHTML = ''; // Clear the container first
        elementOrder.forEach(item => container.appendChild(item.element)); // Re-render all elements in the correct order
        smoothScrollToElement(newElement); // Scroll to the newly added element
    }, 0);
}

// Helper function for smooth scrolling
function smoothScrollToElement(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'end',
        inline: 'nearest'
    });
}

// Function to create the HTML structure for each element type
function createHorizontalDescription(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group-horizontal');
    element.id = elementId;  // Assign the unique ID
    element.innerHTML = `
        <div class="label-container">
            <label for="horizontal-description">Horizontal Description</label>
            <button type="button" class="remove-btn" onclick="removeElement(this)">x</button>
        </div>
        <textarea name="horizontalDescriptions[]" class="horizontalDescription" placeholder="Horizontal Description" maxlength="${maxCharacters.horizontalDescription}"></textarea>
        <small class="char-count">0/${maxCharacters.horizontalDescription} characters</small>
    `;
    bindCharCounter(element.querySelector('.horizontalDescription'));
    return element;
}

function createBlogDescription(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group-full');
    element.id = elementId;  // Assign the unique ID
    element.innerHTML = `
        <div class="label-container">
            <label for="blog-description">Blog Content Description</label>
            <button type="button" class="remove-btn" onclick="removeElement(this)">x</button>
        </div>
        <textarea name="blogDescriptions[]" class="blogDescription" placeholder="Blog Description" maxlength="${maxCharacters.blogDescription}"></textarea>
        <small class="char-count">0/${maxCharacters.blogDescription} characters</small>
    `;
    bindCharCounter(element.querySelector('.blogDescription'));
    return element;
}

function createImageElement(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group');
    element.id = elementId;  // Assign the unique ID
    element.innerHTML = `
        <div class="label-container">
            <label for="image-upload">Image</label>
            <button type="button" class="remove-btn" onclick="removeElement(this)">x</button>
        </div>
        <input type="file" name="images[]" class="image">
    `;
    return element;
}

function createVideoLinkElement(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group');
    element.id = elementId;  // Assign the unique ID
    element.innerHTML = `
        <div class="label-container">
            <label for="video-link">Video Link</label>
            <button type="button" class="remove-btn" onclick="removeElement(this)">x</button>
        </div>
        <input type="url" name="videoLinks[]" class="videoLink" placeholder="Video Link">
    `;
    return element;
}

// Function to bind a character counter to a textarea
function bindCharCounter(textarea) {
    const charCounter = textarea.nextElementSibling;
    textarea.addEventListener('input', function () {
        charCounter.textContent = `${textarea.value.length}/${textarea.getAttribute('maxlength')} characters`;
    });
}

// Function to remove elements
function removeElement(button) {
    const element = button.parentElement.parentElement;
    element.remove(); // Remove the element from the DOM
    elementOrder = elementOrder.filter(item => item.element !== element); // Remove from the order array

    // Defer the DOM update to avoid layout thrashing
    setTimeout(() => {
        const container = document.getElementById('dynamic-content-container');
        container.innerHTML = ''; // Clear the container first
        elementOrder.forEach(item => container.appendChild(item.element)); // Re-render in the correct order
    }, 0);
}

document.getElementById('blog-form').addEventListener('submit', function () {
    // Create a hidden input to store the element order
    const elementOrderInput = document.createElement('input');
    elementOrderInput.type = 'hidden';
    elementOrderInput.name = 'elementOrder';
    elementOrderInput.value = JSON.stringify(elementOrder); // Don't just stringify the types
    this.appendChild(elementOrderInput);
});


</script>
</body>
</html>
