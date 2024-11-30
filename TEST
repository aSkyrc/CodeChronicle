// Track the order and content of elements dynamically added
let elementOrder = []; 
let elementCounter = 0;  // Counter to generate unique IDs for elements

// Event listeners for adding elements
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
    shortDescription: 200,
    fullBlogDescription: 532
};

// Function to generate a unique element ID
function generateElementId() {
    return `element-${elementCounter++}`;
}


// Function to update the character count dynamically
function updateCharCount(textarea) {
    const charCountElement = textarea.nextElementSibling;
    let maxLimit = 0;

    if (textarea.classList.contains('horizontalDescription')) {
        maxLimit = maxCharacters.horizontalDescription;
    } else if (textarea.classList.contains('blogDescription')) {
        maxLimit = maxCharacters.blogDescription;
    } else if (textarea.id === 'shortDescription') {
        maxLimit = maxCharacters.shortDescription;
    } else if (textarea.id === 'fullDescription') {
        maxLimit = maxCharacters.fullBlogDescription;
    }

    const currentCount = textarea.value.length;
    charCountElement.textContent = `${currentCount}/${maxLimit} characters`;

    if (currentCount > maxLimit) {
        textarea.value = textarea.value.slice(0, maxLimit);
        charCountElement.textContent = `${maxLimit}/${maxLimit} characters`;
    }
}

// Initialize character counts on page load
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('.horizontalDescription, .blogDescription, #shortDescription, #fullDescription');
    textareas.forEach((textarea) => {
        updateCharCount(textarea);
    });
});

// Add character limit enforcement on input
document.addEventListener('input', function (event) {
    const target = event.target;
    if (
        target.classList.contains('horizontalDescription') || 
        target.classList.contains('blogDescription') || 
        target.id === 'shortDescription' || 
        target.id === 'fullDescription'
    ) {
        updateCharCount(target);
    }
});

// Maximum counts for dynamic elements
const maxCount = {
    horizontalDescription: 3,
    videoLink: 2,
    image: 2,
    blogDescription: 4
};

// Track removed elements
let removedElements = [];// Function to add a new element (e.g., horizontalDescription, blogDescription, etc.)// Function to add a new element (e.g., blogDescription, horizontalDescription, etc.)
function addElement(type) {
    const container = document.getElementById('dynamic-content-container');
    const currentCount = document.querySelectorAll(`.${type}`).length;

    if (currentCount >= maxCount[type]) {
        alert(`You can only add up to ${maxCount[type]} ${type === 'horizontalDescription' ? 'Horizontal Descriptions' : type.charAt(0).toUpperCase() + type.slice(1)}.`);
        return;
    }

    const elementId = `element-${Date.now()}-${Math.floor(Math.random() * 1000)}`; // Unique ID for new element
    let newElement;
    let content = ''; // Default empty content for new element

    if (type === 'horizontalDescription') {
        newElement = createHorizontalDescription(elementId);
        content = newElement.querySelector('textarea').value; // Capture current content for new element
    } else if (type === 'blogDescription') {
        newElement = createBlogDescription(elementId);
        content = newElement.querySelector('textarea').value; // Capture current content for new element
    } else if (type === 'image') {
        newElement = createImageElement(elementId);
        content = newElement.querySelector('input').value; // Capture current content for new element
    } else if (type === 'videoLink') {
        newElement = createVideoLinkElement(elementId);
        content = newElement.querySelector('input').value; // Capture current content for new element
    }

    // Add element to dynamic content container
    container.appendChild(newElement);

    // Track element order and its content separately, preventing overwriting
    elementOrder.push({
        id: elementId,        // Unique ID for the element
        type: type,           // Type (e.g., 'blogDescription')
        content: content,     // Store dynamic content
    });

    smoothScrollToElement(newElement);
}

// Helper function for smooth scrolling
function smoothScrollToElement(element) {
    setTimeout(function () {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'end',
            inline: 'nearest'
        });
    }, 0);
}

// Create element structures
function createHorizontalDescription(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group-horizontal');
    element.id = elementId;
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
    element.id = elementId;
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
    element.id = elementId;
    element.innerHTML = `
        <div class="label-container">
            <label for="image-upload">Image</label>
            <button type="button" class="remove-btn" onclick="removeElement(this)">x</button>
        </div>
        <input type="file" name="images[]" class="image" accept="image/*">
    `;
    return element;
}

function createVideoLinkElement(elementId) {
    const element = document.createElement('div');
    element.classList.add('form-group');
    element.id = elementId;
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


function removeElement(button) {
    const element = button.closest('.form-group, .form-group-horizontal, .form-group-full');
    const elementId = element.id; // Use the unique ID assigned to the element

    if (elementId) {
        removedElements.push(elementId); // Add ID to removedElements
    }

    element.remove(); // Remove the element from the DOM

    // Update hidden input for removed elements
    const removedElementsInput = document.getElementById('removedElements');
    if (removedElementsInput) {
        removedElementsInput.value = JSON.stringify(removedElements);
    }
}


// Handle form submission and append the dynamic elements and removed elements
document.getElementById('blog-form').addEventListener('submit', function (event) {
    event.preventDefault();  // Prevent default form submission

    // Prepare the dynamic element order data
    const elementOrderInput = document.createElement('input');
    elementOrderInput.type = 'hidden';
    elementOrderInput.name = 'elementOrder';
    elementOrderInput.value = JSON.stringify(elementOrder.map(item => ({
        id: item.id,
        type: item.type,
        content: document.getElementById(item.id).querySelector('textarea')?.value || document.getElementById(item.id).querySelector('input')?.value,
    })));
    this.appendChild(elementOrderInput);

    // Track removed elements
    let removedElementsInput = document.getElementById('removedElements');
    if (!removedElementsInput) {
        removedElementsInput = document.createElement('input');
        removedElementsInput.type = 'hidden';
        removedElementsInput.id = 'removedElements';
        removedElementsInput.name = 'removedElements';
        this.appendChild(removedElementsInput);
    }
    removedElementsInput.value = JSON.stringify(removedElements);

    // Now submit the form
    this.submit();
});



//validate for images
// Function to validate file input
function validateFileInput(fileInput) {
    const allowedExtensions = ['jpg', 'jpeg', 'png']; // Allowed extensions
    const fileName = fileInput.value;
    const fileExtension = fileName.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alert('Only JPG and PNG files are allowed.');
        fileInput.value = ''; // Clear the invalid file
    }
}

// Attach the validation to file input changes
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        validateFileInput(this);
    });
});
