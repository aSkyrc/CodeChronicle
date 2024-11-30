<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings</title>
  <link rel="stylesheet" href="basura.css">
</head>
<body>
  <div class="container">
    <div class="sidebar">
      <h3>Settings</h3>
      <ul>
        <li>Public Profile</li>
        <li>Account Settings</li>
      </ul>
    </div>

    <div class="main-content">
      <h2 style="margin-left: 30px;">Public Profile</h2>
      <div class="profile">
        <div class="profile-picture">
          <img src="default-avatar.png" alt="Profile Picture">
          <div class="button-container">
            <button class="ChangePicture">Change picture</button>
            <button class="DeletePicture">Delete picture</button>
          </div>
        </div>

        <div class="profile-info">
            <div class="input-group">
                <div class="input-item">
                <label>Username:</label>
                <input type="text" value="" placeholder="Username">
                </div>
                <div class="input-item">
                <label>Role:</label>
                <select>
                    <option value="">Select</option>
                    <option>Professional</option>
                    <option>Student</option>
                    <option>Professional</option>
                </select>
                </div>
            </div>
            <label>Email:</label>
            <input type="email" value="" placeholder="Your Email">
            <label>About Yourself:</label>
            <textarea placeholder="Write about yourself"></textarea>
        </div>
      </div>

    <div class="add">
      <h3>Employment Credentials</h3>
      <div id="work-credentials">
        <div class="credential">
          <span>I.T Specialist, Concentrix (2021-2022)</span>
          <button class="delete">x</button>
        </div>
        <button class="add-btn" onclick="openWorkModal('work')">+ Add Work Credentials</button>
      </div>

      <h3>Education Credentials</h3>
      <div id="education-credentials">
        <div class="credential">
          <span>Senior High School, Valley High Academy Inc.</span>
          <button class="delete">x</button>
        </div>
        <button class="add-btn" onclick="openEducationModal('education')">+ Add Education Credentials</button>
      </div>
    </div>

     <button class="update-button-settings">Update</button>
    </div>
  </div>

<!-- Work Credentials Modal -->
<div id="work-modal" class="modal hidden">
  <div class="modal-content">
    <button class="close" onclick="closeModal('work')">x</button>
    <form id="work-form" class="modal-form">
      <h3>Add Work Credentials</h3>
      <div>
        <label for="position">Position</label>
        <input type="text" id="position" name="position" placeholder="Enter your position">
      </div>
      <div>
        <label for="organization">Company Organization</label>
        <input type="text" id="organization" name="organization" placeholder="Enter organization name">
      </div>
      <div>
        <label for="start-year">Start Year</label>
        <input type="number" id="start-year" name="start-year" min="1900" max="2099">
      </div>
      <div>
        <label for="end-year">End Year</label>
        <input type="number" id="end-year" name="end-year" min="1900" max="2099">
      </div>
      <div>
        <button type="button" onclick="saveWorkCredentials()">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Education Credentials Modal -->
<div id="education-modal" class="modal hidden">
  <div class="modal-content">
    <button class="close" onclick="closeModal('education')">x</button>
    <form id="education-form" class="modal-form">
      <h3>Add Education Credentials</h3>
      <div>
        <label for="school">School</label>
        <input type="text" id="school" name="school" placeholder="Enter school name">
      </div>
      <div>
        <label for="year-level">Year Level</label>
        <input type="text" id="year-level" name="year-level" placeholder="Enter year level">
      </div>
      <div>
        <label for="start-year">Start Year</label>
        <input type="number" id="start-year" name="start-year" min="1900" max="2099">
      </div>
      <div>
        <label for="end-year">End Year</label>
        <input type="number" id="end-year" name="end-year" min="1900" max="2099">
      </div>
      <div>
        <button type="button" onclick="saveEducationCredentials()">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Script -->
<script>
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

  // Function to save work credentials
  function saveWorkCredentials() {
    const position = document.getElementById('position').value;
    const organization = document.getElementById('organization').value;
    const startYear = document.getElementById('start-year').value;
    const endYear = document.getElementById('end-year').value;

    if (position && organization && startYear && endYear) {
      // Save logic here (e.g., send to server)
      alert('Work credentials saved!');
      closeModal('work');
    } else {
      alert('Please fill in all fields.');
    }
  }

  // Function to save education credentials
  function saveEducationCredentials() {
    const school = document.getElementById('school').value;
    const yearLevel = document.getElementById('year-level').value;
    const startYear = document.getElementById('start-year').value;
    const endYear = document.getElementById('end-year').value;

    if (school && yearLevel && startYear && endYear) {
      // Save logic here (e.g., send to server)
      alert('Education credentials saved!');
      closeModal('education');
    } else {
      alert('Please fill in all fields.');
    }
  }
</script>

</body>
</html>
