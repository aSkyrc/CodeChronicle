<?php
include_once '../user/navigationBar.php'; // Includes your connection file

$workCredentials = $workCredentials ?? [];
$educationCredentials = $educationCredentials ?? [];

$userProfileCollection = $collections['user-profile'];  // Ensure the 'user-profile' collection is assigned correctly
$userProfile = $userProfileCollection->findOne(['user_id' => new MongoDB\BSON\ObjectId($user_id)]);

$googleUser = $googleUsersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
$isGoogleUser = ($googleUser !== null);

if (!$isGoogleUser) {
    // Regular user
    $user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
    if (!$user) {
        die("User data not found.");
    }
} else {
    // Google user
    $user = $googleUser;
}

// Fetch work and education credentials
$workCredentials = $userProfile['workCredentials'] ?? [];
$educationCredentials = $userProfile['educationCredentials'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $role = trim($_POST['role'] ?? $userProfile['role']); // Use current role if not updated
    $about = trim($_POST['about'] ?? $userProfile['about']); // Use current about if not updated
    $username = $_POST['username'] ?? $user['username']; // Default to current username if not updated
    $picture = $user['picture'] ?? '../logos/userDefault.png'; // Default to current picture if not updated

    // Handle profile picture change (if needed)
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        // Get the file details
        $picture = $_FILES['profile_picture']['name'];
        $targetDir = '../uploads/';  // Change this to 'uploads' directory where you want to store the image
        $targetFile = $targetDir . basename($picture);

        // Check if the file is an image
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                echo "<script>alert('Profile picture uploaded successfully.');</script>";
            } else {
                echo "<script>alert('Error uploading the profile picture.');</script>";
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG files are allowed.');</script>";
        }
    }

    // Prepare the update data for user-profile collection
    $updateProfileData = [];

    // Update role if it has changed
    if ($role && $role !== $userProfile['role']) {
        $updateProfileData['role'] = $role;
    }

    // Update about if it has changed
    if ($about && $about !== $userProfile['about']) {
        $updateProfileData['about'] = $about;
    }

    // If there are any updates, apply them to the user-profile collection
    if (!empty($updateProfileData)) {
        $updateProfileResult = $userProfileCollection->updateOne(
            ['user_id' => new MongoDB\BSON\ObjectId($user_id)], // Match by user_id
            ['$set' => $updateProfileData] // Set the new values for role and about
        );

        if ($updateProfileResult->getModifiedCount() > 0) {
            echo "<script>alert('Profile updated successfully.');
              location.reload(); // Optionally reload the page to reflect the updates;</script>";
            
        } else {
            echo "<script>alert('No changes were made to the profile.');</script>";
        }
    }

    // Prepare the update data for users or google-users collection (username, picture)
    $updateUserData = [];

    // Update username if it has changed (only for regular users)
    if (!$isGoogleUser && $username && $username !== $user['username']) {
        $updateUserData['username'] = $username;
    }

    // Update picture if it has changed
    if ($picture !== $user['picture']) {
        $updateUserData['picture'] = $picture;
    }

    // If there are updates to be made, update the users or google-users collection
    if (!empty($updateUserData)) {
        if ($isGoogleUser) {
            // Update the google-users collection if the user is a Google user
            $updateUserResult = $googleUsersCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($user_id)], // Match by user_id
                ['$set' => $updateUserData] // Set the new values for username and picture
            );
        } else {
            // Update the users collection if the user is a regular user
            $updateUserResult = $usersCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($user_id)], // Match by user_id
                ['$set' => $updateUserData] // Set the new values for username and picture
            );
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credentialId = $_POST['id'] ?? null;  // Use null if 'id' is not set
    $action = $_POST['action'] ?? null;    // Use null if 'action' is not set

    // Ensure both 'id' and 'action' are provided
    if ($credentialId && $action) {
        // Get the correct collection based on the action
        if ($action == 'deleteWorkCredential') {
            // Delete work credential
            $result = $userProfileCollection->updateOne(
                ['user_id' => new MongoDB\BSON\ObjectId($user_id)],
                ['$pull' => ['workCredentials' => ['_id' => new MongoDB\BSON\ObjectId($credentialId)]]]
            );
        } elseif ($action == 'deleteEducationCredential') {
            // Delete education credential
            $result = $userProfileCollection->updateOne(
                ['user_id' => new MongoDB\BSON\ObjectId($user_id)],
                ['$pull' => ['educationCredentials' => ['_id' => new MongoDB\BSON\ObjectId($credentialId)]]]
            );
        } else {
            echo "Invalid action.";
            exit;
        }
    } 
}

?>

<body>
<div class="settings-profile" style="margin-top: 100px; margin-bottom: 50px;">
    <div class="container-settings">
        <div class="sidebar-settings">
        <h3>Settings</h3>
        <ul>
        <li><a href="settings-profile.php">Public Profile</a></li>
        <li><a href="settings-account.php">Account Settings</a></li>
        </ul>
        </div>

        <div class="main-content-settings">
            <h2 style="margin-left: 30px; color: #282829;">Public Profile</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-settings">
                    <div class="profile-picture">
                        <!-- Display the current profile picture -->
                        <img id="profilePic" src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
                        
                        <div class="button-container">
                            <!-- Button to trigger file input for changing the picture, disabled for Google users -->
                            <button type="button" class="ChangePicture" <?php echo $isGoogleUser ? 'disabled' : ''; ?> onclick="document.getElementById('profile_picture_input').click();">Change picture</button>
                            
                            <!-- Button to delete the profile picture and reset to default, disabled for Google users -->
                            <button type="submit" name="delete_picture" value="1" class="DeletePicture" <?php echo $isGoogleUser ? 'disabled' : ''; ?>>Delete picture</button>

                            <!-- Hidden file input for selecting the new profile picture, disabled for Google users -->
                            <input type="file" name="profile_picture" id="profile_picture_input" style="display:none;" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(event)" <?php echo $isGoogleUser ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div class="profile-info">
                        <div class="input-group">
                            <div class="input-item">
                                <label for="username">Username:</label>
                                <!-- Disable the username input for Google users -->
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" placeholder="Username" <?php echo $isGoogleUser ? 'disabled' : ''; ?>>
                            </div>
                            <div class="input-item">
                                <label for="role">Role:</label>
                                <select id="role" name="role" <?php echo $isGoogleUser ? '' : ''; ?>>
                                    <option value="Content Creator" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Content Creator') ? 'selected' : ''; ?>>Content Creator</option>
                                    <option value="Developer" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Developer') ? 'selected' : ''; ?>>Developer</option>
                                    <option value="Student" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                                    <option value="Exploring Programming" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Exploring Programming') ? 'selected' : ''; ?>>Exploring Programming</option>
                                    <option value="Experienced Programmer" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Experienced Programmer') ? 'selected' : ''; ?>>Experienced Programmer</option>
                                    <option value="Others" <?php echo (isset($userProfile['role']) && $userProfile['role'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>
                        </div>

                        <label for="about">About Yourself:</label>
                        <textarea id="about" name="about" placeholder="Write about yourself" <?php echo $isGoogleUser ? '' : ''; ?>><?php echo htmlspecialchars($userProfile['about'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="update-button-settings" <?php echo $isGoogleUser ? '' : ''; ?>>Update</button>
            </form>

            <div class="add">
                <h3>Employment Credentials</h3>
                <div id="work-credentials">
                <?php if (!empty($workCredentials)): ?>
    <?php foreach ($workCredentials as $work): ?>
        <?php if (isset($work['_id'])): ?>
            <div class="credential">
                <span>
                    <?php echo htmlspecialchars("{$work['position']} at {$work['organization']} ({$work['startYear']}-{$work['endYear']})"); ?>
                </span>
                <button type="button" class="delete" id="work-<?php echo $work['_id']; ?>" onclick="deleteWorkCredential('<?php echo $work['_id']; ?>')">x</button>
            </div>
        <?php else: ?>
            <p>Work credential does not have a valid ID.</p>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (count($workCredentials) < 2): ?>
    <button type="button" class="add-btn" onclick="openWorkModal()">+ Add Work Credentials</button>
<?php endif; ?>
                </div>
                <br>
                <br>
                <h3>Education Credentials</h3>
                <div id="education-credentials">
                <?php if (!empty($educationCredentials)): ?>
    <?php foreach ($educationCredentials as $education): ?>
        <?php if (isset($education['_id'])): ?>
            <div class="credential">
                <span>
                    <?php echo htmlspecialchars("{$education['school']}, {$education['yearLevel']} ({$education['startYear']}-{$education['endYear']})"); ?>
                </span>
                <button type="button" class="delete" id="education-<?php echo $education['_id']; ?>" onclick="deleteEducationCredential('<?php echo $education['_id']; ?>')">x</button>
            </div>
        <?php else: ?>
            <p>Education credential does not have a valid ID.</p>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (count($educationCredentials) < 2): ?>
    <button type="button" class="add-btn" onclick="openEducationModal()">+ Add Education Credentials</button>
<?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Work Credentials Modal -->
<div id="work-modal" class="modal hidden">
    <div class="modal-content-settings">
        <button class="close" onclick="closeModal('work')">x</button>
        <div id="work-form" class="modal-form-settings">
            <form method="POST" action="addWorkCredentials.php"> 
                <input type="hidden" name="add-work" value="1"> <!-- Hidden input to identify the form -->
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
                    <button type="submit">Save</button>
            </form>
        </div>
    </div>
</div>

<!-- Education Credentials Modal -->
<div id="education-modal" class="modal hidden">
    <div class="modal-content-settings">
        <button class="close" onclick="closeModal('education')">x</button>
        <div id="education-form" class="modal-form-settings">
            <form method="POST" action="addEducationCredentials.php"> <!-- Education credentials form in modal -->
                <h3>Add Education Credentials</h3>
                <input type="hidden" name="add-education" value="1"> <!-- Hidden input to identify the form -->
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
                    <button type="submit">Save</button>
            </form>
        </div>
    </div>
</div>
<script src="../screen/javascript/settings.js"></script>


</body>
</html>
