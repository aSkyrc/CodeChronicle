<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';
// Get the user profile collections
$usersCollection = $collections['users']; // Regular users collection
$googleUsersCollection = $db->selectCollection('google-users'); // Google users collection

// Initialize variables
$profilePicture = '../logos/userDefault.png'; // Default profile picture

// Check if user_id exists and fetch data from the collections
if ($user_id) {
    $usersData = null;

    // First, try to fetch from regular users collection using user_id
    $usersData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

    // If no regular user data, check in the Google users collection
    $isGoogleUser = false;
    if (!$usersData) {
        $usersData = $googleUsersCollection->findOne(['_id' => $user_id]);
        $isGoogleUser = true; // Mark as Google user
    }

    // Determine profile picture    
    if ($usersData) {
        if ($isGoogleUser) {
            // Use the `picture` directly for Google users
            if (isset($usersData['picture']) && !empty($usersData['picture'])) {
                $profilePicture = $usersData['picture'];
            }
        } else {
            // Prepend `../uploads/` for regular users
            if (isset($usersData['picture']) && !empty($usersData['picture'])) {
                $profilePicture = '../uploads/' . $usersData['picture'];
            }
        }
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Lalezar&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../screen/css/navigationBar.css">
    <link rel="stylesheet" href="../screen/css/BLOG.css">
    <link rel="stylesheet" href="../screen/css/SETTINGS.css">
    <link rel="stylesheet" href="../screen/css/homepage.css">
    <link rel="stylesheet" href="../screen/css/VIEWBLOG.css">
    <link rel="stylesheet" href="../screen/css/Community.css">
    <link rel="stylesheet" href="../screen/css/COmmunity-visit.css">
    <link rel="stylesheet" href="../screen/css/Visit-profile.css">
    <link rel="stylesheet" href="../screen/css/Search.css">
    <title>Code Chronicle</title>
</head>
<body>
<div class="navbar">
    <div class="logo">
        <img src="../logos/CClogonobg.png" alt="Logo">
        <span>Code Chronicle</span>
    </div>

    <div class="search-bar">
        <form action="searchPage.php" method="GET" id="searchForm">
            <input type="text" name="search" placeholder="Search for something..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" onkeypress="checkEnter(event)">
            <button type="submit" style="display: none;" id="hiddenSubmitButton">Submit</button> <!-- Hidden submit button -->
        </form>
    </div>


    <div class="icons">
        <!-- Notification Icon -->
        <div class="icon" id="notificationIcon">
            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917226.png" alt="Notifications">
            <div class="dropdown" id="notificationDropdown">
                <h5>Notifications</h5>
            </div>
        </div>

        <!-- Home Icon -->
        <div class="icon" id="homeIcon">
            <a href="homepage.php"><img src="https://cdn-icons-png.flaticon.com/512/3917/3917033.png" alt="Home"></a>
        </div>

        <!-- Bookmark Icon -->
        <div class="icon" id="bookmarkIcon">
            <a href="saved-blog.php"><img src="https://cdn-icons-png.flaticon.com/512/3916/3916593.png" alt="Bookmarks"></a>
        </div>

        <!-- User Icon -->
        <div class="icon" id="userIcon">
            <a href="community.php"><img src="https://cdn-icons-png.flaticon.com/512/5069/5069162.png" alt="Users"></a>
        </div>

        <!-- Profile Icon -->
        <div class="profile" id="profileIcon">
            <img src=" <?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
            <div class="dropdown" id="profileDropdown">
                <a href="profile.php" style="text-decoration: none;">Profile</a>
                <a href="community.php" style="text-decoration: none;">Community</a>
                <a href="settings-profile.php" style="text-decoration: none;">Settings</a>
                <br>
                <a href="/Code Chronicle/connection/logout.php" style="text-decoration: none; color: inherit;">Log Out</a>
            </div>
        </div>
    </div>
</div>

    <script src="../screen/javascript/NavigationBar.js"></script>

