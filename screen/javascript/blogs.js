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

