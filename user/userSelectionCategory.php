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

// Check if role_status and interest_status are true
if ($user && isset($user['role_status'], $user['interest_status']) 
    && $user['role_status'] === true 
    && $user['interest_status'] === true) {
    // Redirect to haha.php if both conditions are true
    header("Location: homepage.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['interest'])) {
    // Get the selected categories (interest)
    $interests = $_POST['interest'];  // This is an array of selected categories

    // Check if the user already has a profile
    $existingProfile = $userProfileCollection->findOne(['user_id' => $userId]);

    if ($existingProfile) {
        // Update the existing profile with the selected interests and set interest_status to true
        $userProfileCollection->updateOne(
            ['user_id' => $userId],
            ['$set' => [
                'interest' => $interests,
                'interest_status' => true,  // Set interest_status to true
            ]]
        );
    } else {
        // Insert a new profile with the selected interests and set interest_status to true
        $userProfileCollection->insertOne([
            'user_id' => $userId,
            'interest' => $interests,  // Store the selected interests
            'interest_status' => true,  // Set interest_status to true
        ]);
    }

    // Update the google-users collection to set first_login to true
    $googleUsersCollection->updateOne(
        ['email' => $_SESSION['user']['email']], // Ensure you use the correct email from session
        ['$set' => ['first_login' => true]]
    );

    // Redirect to the next page (or wherever you want to send the user)
    header("Location: homepage.php");  // Redirect after successful submission
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
  <link rel="stylesheet" href="../screen/css/userSelectionCategory.css">
  <title>Hello, New User!</title>
</head>
<body>
  <div id="myModal2" class="modal2">
    <!-- Modal Content -->
    <div class="modal-content2">
      <div class="newuserlogo">
        <img src="../logos/CClogo.jpg" alt="">
        <div class="title">
          <label class="cdchronicle">Code Chronicle</label><br>
          <label class="cdchroniclemess">Your Gateway to Programming Insights</label>    
        </div>
        <div class="hinewuser">
          <h3>Hi New User</h3>
          <h6>Which categories interest you?</h6>
          <h5>You can choose as many as you want</h5>
        </div>

        <!-- Form for selecting categories -->
        <form action="userSelectionCategory.php" method="POST">
          <div class="selected2">
            <input type="checkbox" id="frontend" name="interest[]" value="Frontend Development">
            <label for="frontend">Frontend Development</label>
            <input class="Backendev" type="checkbox" id="backend" name="interest[]" value="Backend Development">
            <label for="backend">Backend Development</label>
          </div>

          <div class="selected2">
            <input type="checkbox" id="datascience" name="interest[]" value="Data Science and Machine Learning">
            <label for="datascience">Data Science and Machine Learning</label>
            <input class="mobdev" type="checkbox" id="mobile" name="interest[]" value="Mobile Development">
            <label for="mobile">Mobile Development</label>
          </div>

          <div class="selected2">
            <input type="checkbox" id="devops" name="interest[]" value="DevOps and Cloud Computing">
            <label for="devops">DevOps and Cloud Computing</label>
            <input class="cybersec" type="checkbox" id="cybersecurity" name="interest[]" value="Cybersecurity">
            <label for="cybersecurity">Cybersecurity</label>
          </div>

          <div class="selected2">
            <input type="checkbox" id="programming" name="interest[]" value="Programming Language">
            <label for="programming">Programming Language</label>
            <input class="aads" type="checkbox" id="algorithms" name="interest[]" value="Algorithms and Data Structures">
            <label for="algorithms">Algorithms and Data Structures</label>
          </div>

          <div class="selected2">
            <input type="checkbox" id="gamedev" name="interest[]" value="Game Development">
            <label for="gamedev">Game Development</label>
            <input class="careernet" type="checkbox" id="career" name="interest[]" value="Career and Networking">
            <label for="career">Career and Networking</label>
          </div>

          <div class="confirm2">
            <button type="submit" id="confirmBtn">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
