<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

// Get the user ID from session
$userId = $_SESSION['user_id'];

// Get the user profile collection
$userProfileCollection = $collections['user-profile'];
$googleUsersCollection = $db->selectCollection('google-users');

// Fetch user data from the database
$user = $userProfileCollection->findOne(['user_id' => $userId]);

// Check if role_status and category_status are true
if ($user && isset($user['role_status'], $user['interest_status']) 
    && $user['role_status'] === true 
    && $user['interest_status'] === true) {
    // Redirect to haha.php if both conditions are true
    header("Location: homepage.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_type'])) {
    // Get the selected user type
    $userType = $_POST['user_type'];

    // Check if the user already has a role assigned
    $existingProfile = $userProfileCollection->findOne(['user_id' => $userId]);

    if ($existingProfile) {
        // Update the existing role and set role_status to true
        $userProfileCollection->updateOne(
            ['user_id' => $userId],
            ['$set' => [
                'role' => $userType,
                'role_status' => true  // Set role_status to true
            ]]
        );
    } else {
        // Insert a new role and set role_status to true
        $userProfileCollection->insertOne([
            'user_id' => $userId,
            'role' => $userType,
            'role_status' => true,  // Set role_status to true
        ]);
    }

    // Update the google-users collection to set first_login to true
    $googleUsersCollection->updateOne(
        ['email' => $_SESSION['user']['email']], // Ensure you use the correct email from session
        ['$set' => ['first_login' => true]]
    );

    // Redirect to the next page
    header("Location: userSelectionCategory.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="shortcut icon" href="../logos/CClogo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../screen/css/designLanding.css">
    <link rel="stylesheet" href="../screen/css/userSelectionRole.css">
    <title>Hello, New User!</title>
    <script>
        // JavaScript to check if a role is selected
        function validateForm() {
            var userType = document.querySelector('input[name="user_type"]:checked');
            if (!userType) {
                alert('Please select a role to continue.');
                return false; // Prevent form submission
            }
            return true; // Allow form submission if a role is selected
        }
    </script>
</head>
<body>
 
  <div id="myModal" class="modal">
    <div class="modal-content">
        <div class="newuserlogo">
            <img src="../logos/CClogo.jpg" alt="">
            <div class="title">
                <label class="cdchronicle">Code Chronicle</label><br>
                <label class="cdchroniclemess">Your Gateway to Programming Insights</label>    
            </div>

            <div class="hinewuser">
                <h3>Hi New User</h3>
                <h6>Which user are you!</h6>
            </div>

            <form action="userSelectionRole.php" method="POST" onsubmit="return validateForm();"> <!-- Form starts here -->
                <div class="select">
                    <div class="selected">
                        <input class="contentcreator" type="radio" id="content_creator" name="user_type" value="Content Creator">
                        <label for="content_creator">Content Creator</label>

                        <input class="developers" type="radio" id="developers" name="user_type" value="Developer">
                        <label for="developers">Developer</label>
                    </div>

                    <div class="selected">
                        <input type="radio" id="student" name="user_type" value="Student">
                        <label for="student">Student</label>

                        <input  class="exprog" type="radio" id="exploring_programming" name="user_type" value="Exploring Programming">
                        <label for="exploring_programming">Exploring Programminig</label>
                    </div>

                    <div class="selected">
                        <input type="radio" id="experience_programming" name="user_type" value="Experienced Programmer">
                        <label for="experience_programming">Experienced Programmer</label>

                        <input class="others" type="radio" id="others" name="user_type" value="Others">
                        <label for="others">Others</label>
                    </div>

                    <div class="confirm">
                        <button type="submit" id="Continue">Continue</button>
                    </div>
                </div>
            </form> <!-- Form ends here -->
        </div>
    </div>
  </div>
</body>
</html>
