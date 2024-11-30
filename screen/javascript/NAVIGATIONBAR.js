// Define original and updated icon sources
const iconsData = {
  homeIcon: {
    original: 'https://cdn-icons-png.flaticon.com/512/3917/3917033.png',
    updated: 'https://cdn-icons-png.flaticon.com/512/3917/3917074.png',
  },
  notificationIcon: {
    original: 'https://cdn-icons-png.flaticon.com/512/3917/3917226.png',
    updated: 'https://cdn-icons-png.flaticon.com/512/3917/3917235.png',
  },
  bookmarkIcon: {
    original: 'https://cdn-icons-png.flaticon.com/512/3916/3916593.png',
    updated: 'https://cdn-icons-png.flaticon.com/512/3916/3916594.png',
  },
  userIcon: {
    original: 'https://cdn-icons-png.flaticon.com/512/5069/5069162.png',
    updated: 'https://cdn-icons-png.flaticon.com/512/5069/5069169.png',
  },
};

// Get all icons and dropdown elements
const icons = document.querySelectorAll('.icon');
const notificationIconElement = document.getElementById('notificationIcon');
const notificationDropdown = document.getElementById('notificationDropdown');
const profileIconElement = document.getElementById('profileIcon');
const profileDropdown = document.getElementById('profileDropdown');

// Function to set the state of all icons
function setIconsState() {
  Object.keys(iconsData).forEach((iconId) => {
    const iconElement = document.getElementById(iconId).querySelector('img');
    const storedState = localStorage.getItem(iconId);
    iconElement.src = storedState ? iconsData[iconId].updated : iconsData[iconId].original;
    if (storedState) {
      document.getElementById(iconId).classList.add('highlight');
    } else {
      document.getElementById(iconId).classList.remove('highlight');
    }
  });
}

// Function to handle icon click
function handleIconClick(clickedIconId) {
  // Reset all icons to original state, except for notification icon
  Object.keys(iconsData).forEach((iconId) => {
    if (iconId !== 'notificationIcon') {  // Don't reset notification icon's state
      localStorage.removeItem(iconId); // Clear state for other icons
      document.getElementById(iconId).classList.remove('highlight');
    }
  });

  // Update the clicked icon
  localStorage.setItem(clickedIconId, 'true'); // Save updated state for clicked icon
  document.getElementById(clickedIconId).classList.add('highlight');
  setIconsState(); // Update the DOM based on the new state
}

// Add event listeners to all icons except notification icon
icons.forEach((icon) => {
  if (icon.id !== 'notificationIcon') { // Exclude notification icon from the click logic
    icon.addEventListener('click', function () {
      handleIconClick(this.id); // Handle click and update state
    });
  }
});

// Dropdown toggle logic for notification modal (doesn't affect icon state)
function toggleNotificationModal() {
  const isVisible = notificationDropdown.style.display === 'block';
  document.querySelectorAll('.dropdown').forEach((d) => (d.style.display = 'none')); // Close other dropdowns
  notificationDropdown.style.display = isVisible ? 'none' : 'block'; // Toggle notification modal visibility
}

// Add event listener for notification icon (opens the modal and toggles icon)
notificationIconElement.addEventListener('click', function (e) {
  e.stopPropagation();  // Prevent body click listener from firing
  toggleNotificationModal(); // Show the notification modal

  // Get the current state of the notification icon from localStorage
  const currentState = localStorage.getItem('notificationIcon');

  // Toggle the state between original and updated
  if (currentState === 'true') {
    localStorage.removeItem('notificationIcon'); // Set to original state
  } else {
    localStorage.setItem('notificationIcon', 'true'); // Set to updated state
  }

  setIconsState(); // Update the DOM based on the new state
});

// Dropdown toggle logic for profile modal
function toggleProfileDropdown() {
  const isVisible = profileDropdown.style.display === 'block';
  document.querySelectorAll('.dropdown').forEach((d) => (d.style.display = 'none')); // Close other dropdowns
  profileDropdown.style.display = isVisible ? 'none' : 'block'; // Toggle profile modal visibility
}

// Add event listener for profile icon (opens profile dropdown)
profileIconElement.addEventListener('click', function (e) {
  e.stopPropagation(); // Prevent body click listener from firing
  toggleProfileDropdown(); // Show the profile dropdown
});

// Prevent resetting the highlight when clicking inside the profile dropdown (stop propagation)
profileDropdown.addEventListener('click', function (e) {
  e.stopPropagation(); // Prevent the click from propagating and triggering the reset logic
});

// Function to reset icons when navigating to profile or settings (reset highlight)
function resetIconHighlightOnProfileNavigation() {
  // Reset all icons to original state
  Object.keys(iconsData).forEach((iconId) => {
    localStorage.removeItem(iconId); // Clear state for all icons
    document.getElementById(iconId).classList.remove('highlight'); // Remove highlight class
  });

  setIconsState(); // Update the DOM to reflect the reset state
}

// Add event listener for profile content clicks (reset highlight when navigating to profile settings, etc.)
const profileLinks = document.querySelectorAll('#profileDropdown a'); // Assuming profile content is in anchor tags
profileLinks.forEach((link) => {
  link.addEventListener('click', function () {
    resetIconHighlightOnProfileNavigation(); // Reset the icon highlight when navigating to profile settings
  });
});

// Close all dropdowns when clicking outside
document.body.addEventListener('click', function () {
  document.querySelectorAll('.dropdown').forEach((d) => (d.style.display = 'none')); // Close all dropdowns on body click
});

// Initialize the icons state on page load
document.addEventListener('DOMContentLoaded', setIconsState);
