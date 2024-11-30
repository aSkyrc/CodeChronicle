<?php 
include_once '../user/navigationBar.php';

$userId = $_SESSION['user_id']; // Assuming user_id is stored in the session

// Determine which collection to use based on user type (regular user or google user)
$userCollection = $collections['users'];  // Regular users
$googleUserCollection = $collections['google-users']; // Google users collection

// Fetch the current user's profile
$userProfile = $userCollection->findOne(['_id' => $userId]);

// Check if the user is a Google user by checking the _id in google-users collection
$isGoogleUser = false;
$googleUser = $googleUserCollection->findOne(['_id' => $userId]);
if ($googleUser) {
    $isGoogleUser = true; // User is a Google user
}

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isGoogleUser) {
    $oldPassword = $_POST['old-password'];
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    // Ensure the user profile exists and the password field is present
    if (isset($userProfile['password'])) {
        // Validate old password using password_verify() to compare the hashed password
        if (password_verify($oldPassword, $userProfile['password'])) {
            // Validate new password match
            if ($newPassword === $confirmPassword) {
                // Hash the new password before storing it
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update the password in the users collection
                $updateResult = $userCollection->updateOne(
                    ['_id' => $userId],
                    ['$set' => ['password' => $hashedPassword]]
                );

                if ($updateResult->getModifiedCount() > 0) {
                    echo "<script>
                        alert('Password updated successfully!');
                        window.location.href = 'settings-profile.php';
                    </script>";
                } else {
                    echo "<script>
                        alert('Failed to update password. Please try again.');
                    </script>";
                }
            } else {
                echo "<script>
                    alert('New passwords do not match.');
                </script>";
            }
        } else {
            echo "<script>
                alert('Old password is incorrect.');
            </script>";
        }
    } else {
        echo "<script>
            alert('User profile or password field is missing.');
        </script>";
    }
}
?>

<body>
<div class="settings-profile" style="margin-top: 100px;">
    <div class="container-settings">
        <div class="sidebar-settings">
        <h3>Settings</h3>
        <ul>
            <li><a href="settings-profile.php">Public Profile</a></li>
            <li><a href="settings-account.php">Account Settings</a></li>
        </ul>
        </div>

        <div class="main-content-settings">
        <h2 style="margin-left: 30px;">Account Settings</h2>
        <div class="profile-settings">

            <div class="profile-info">
                <?php if ($isGoogleUser): ?>
                    <!-- Message for Google users -->
                    <p style="color: red; margin-left: 50px;">You cannot change your password as you're using a Google account.</p>

                    <!-- Disabled form inputs for Google users -->
                    <form method="POST">
                        <label>Old Password:</label>
                        <input type="password" name="old-password" placeholder="Enter old password" disabled>
                        <label>New Password:</label>
                        <input type="password" name="new-password" placeholder="Enter new password" disabled>
                        <label>Confirm New Password:</label>
                        <input type="password" name="confirm-password" placeholder="Confirm new password" disabled>
                        <button type="submit" class="update-button-settings" disabled>Update</button>
                    </form>
                <?php else: ?>
                    <!-- Form for regular users -->
                    <form method="POST">
                        <label>Old Password:</label>
                        <input type="password" name="old-password" placeholder="Enter old password" required>
                        <label>New Password:</label>
                        <input type="password" name="new-password" placeholder="Enter new password" required>
                        <label>Confirm New Password:</label>
                        <input type="password" name="confirm-password" placeholder="Confirm new password" required>
                        <button type="submit" class="update-button-settings">Update</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</div>

</body>
</html>
