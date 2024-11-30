document.getElementById('profile_picture_input').addEventListener('change', function(event) {
  const file = event.target.files[0];
  if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
          document.querySelector('.profile-picture img').src = e.target.result; // Update image source
      };
      reader.readAsDataURL(file);
  }
});

// Function to handle the preview of the selected image before submitting the form
function previewImage(event) {
  const file = event.target.files[0];
  
  // Check if the file is an image and its type is either PNG, JPEG, or JPG
  if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      
      // Once the file is read, update the image source to the uploaded image
      reader.onload = function(e) {
          document.getElementById('profilePic').src = e.target.result; // Update the image preview
      };
      
      reader.readAsDataURL(file); // Read the file as a data URL
  }
}

  
  // Get modal references
  const workModal = document.getElementById('work-modal');
  const educationModal = document.getElementById('education-modal');

  // Function to open the work modal
  function openWorkModal() {
    workModal.classList.remove('hidden');
  }

  // Function to open the education modal
  function openEducationModal() {
    educationModal.classList.remove('hidden');
  }

  // Function to close the modals
  function closeModal(type) {
    if (type === 'work') {
      workModal.classList.add('hidden');
      resetWorkForm();
    } else if (type === 'education') {
      educationModal.classList.add('hidden');
      resetEducationForm();
    }
  }

  // Function to reset the work credentials form
  function resetWorkForm() {
    document.getElementById('position').value = '';
    document.getElementById('organization').value = '';
    document.getElementById('start-year').value = '';
    document.getElementById('end-year').value = '';
  }

  // Function to reset the education credentials form
  function resetEducationForm() {
    document.getElementById('school').value = '';
    document.getElementById('year-level').value = '';
    document.getElementById('start-year').value = '';
    document.getElementById('end-year').value = '';
  }

  document.addEventListener("DOMContentLoaded", () => {
    const maxCredentials = 2;

    function updateCredentialCount(type) {
        const credentials = document.querySelectorAll(`#${type}-credentials .credential`);
        if (credentials.length >= maxCredentials) {
            document.querySelector(`#${type}-credentials .add-btn`).style.display = "none";
        } else {
            document.querySelector(`#${type}-credentials .add-btn`).style.display = "inline-block";
        }
    }

    updateCredentialCount("work");
    updateCredentialCount("education");
});


function deleteWorkCredential(credentialId) {
  const confirmation = confirm("Are you sure you want to delete this work credential?");
  if (confirmation) {
      // Send AJAX request to delete the credential
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "/Code Chronicle/user/settings-profile.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
          if (xhr.status === 200 && xhr.responseText === "success") {
              // Remove the credential from the DOM
              document.getElementById('work-' + credentialId).remove(); // Ensure correct DOM element is removed
              alert("Work credential deleted successfully.");
              location.reload(); // Optionally reload the page to reflect the updates
          } else {
              alert("Work credential deleted successfully");
              location.reload(); // Optionally reload the page to reflect the updates
          }
      };
      xhr.send("action=deleteWorkCredential&id=" + credentialId);
  }
}

// Function to delete education credentials
function deleteEducationCredential(credentialId) {
  const confirmation = confirm("Are you sure you want to delete this education credential?");
  if (confirmation) {
      // Send AJAX request to delete the credential
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "/Code Chronicle/user/settings-profile.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
          if (xhr.status === 200 && xhr.responseText === "success") {
              // Remove the credential from the DOM
              document.getElementById('education-' + credentialId).remove(); // Ensure correct DOM element is removed
              alert("Education credential deleted successfully.");
              location.reload(); // Optionally reload the page to reflect the updates
          } else {
              alert("Education credential deleted successfully.");
              location.reload(); // Optionally reload the page to reflect the updates
          }
      };
      xhr.send("action=deleteEducationCredential&id=" + credentialId);
  }
}