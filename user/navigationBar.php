<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

// Get the user profile collections
$usersCollection = $collections['users']; // Regular users collection
$googleUsersCollection = $db->selectCollection('google-users'); // Google users collection
$notificationsCollection = $db->selectCollection('notifications'); // Notifications collection

// Initialize variables
$profilePicture = '../logos/userDefault.png'; // Default profile picture
$unreadCount = 0; // Unread notification count

// Check if user_id exists
if (!empty($user_id)) {
    $usersData = null;

    // Try to fetch user data from the regular users collection
    $usersData = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

    // If not found in regular users collection, check Google users collection
    $isGoogleUser = false;
    if (!$usersData) {
        $usersData = $googleUsersCollection->findOne(['_id' => $user_id]);
        $isGoogleUser = true; // Mark as Google user
    }

    // Determine profile picture
    if ($usersData && isset($usersData['picture']) && !empty($usersData['picture'])) {
        $profilePicture = $isGoogleUser ? $usersData['picture'] : '../uploads/' . $usersData['picture'];
    }

    // Fetch unread notifications count
    $unreadCount = $notificationsCollection->countDocuments([
        'user_id' => new MongoDB\BSON\ObjectId($user_id),
        'is_read' => false
    ]);
}

// Fetch notifications for the logged-in user (limit to 5, sorted by newest)
$notifications = [];
if (!empty($user_id)) {
    $notifications = $notificationsCollection->find(
        ['user_id' => new MongoDB\BSON\ObjectId($user_id)],
        ['sort' => ['created_at' => -1], 'limit' => 5]
    );
}

// Handle notification deletion via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notificationId = $_POST['notification_id'];

    try {
        // Convert the notification ID from string to MongoDB ObjectId
        $notificationObjectId = new MongoDB\BSON\ObjectId($notificationId);

        // Attempt to delete the notification
        $result = $notificationsCollection->deleteOne(['_id' => $notificationObjectId]);

        // Check if a notification was deleted
        if ($result->getDeletedCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete notification.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit; // End the script after the delete operation to prevent further processing
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
    <link rel="stylesheet" href="../screen/css/navigationbar.css">
    <link rel="stylesheet" href="../screen/css/blogs.css">
    <link rel="stylesheet" href="../screen/css/SETTINGS.css">
    <link rel="stylesheet" href="../screen/css/HOMEPAGE.css">
    <link rel="stylesheet" href="../screen/css/VIEWBLOG.css">
    <link rel="stylesheet" href="../screen/css/Community.css">
    <link rel="stylesheet" href="../screen/css/community-visit.css">
    <link rel="stylesheet" href="../screen/css/VISIT-PROFILE.css">
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
            <input type="text" name="search" placeholder="Search for something..."
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   onkeypress="checkEnter(event)">
            <button type="submit" style="display: none;" id="hiddenSubmitButton">Submit</button>
        </form>
    </div>

    <div class="icons">
        <!-- Notification Icon -->
        <div class="icon" id="notificationIcon">
            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917226.png" alt="Notifications">
            <?php if ($unreadCount > 0): ?>
                <span class="badge"><?php echo $unreadCount; ?></span>
            <?php endif; ?>
            <div class="dropdown" id="notificationDropdown" style="display: none;">
                <h5>Notifications</h5>
                <?php if (empty($notifications)): ?>
                    <p style="text-align: center;">No notifications</p>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item" data-id="<?php echo $notification['_id']; ?>" onclick="deleteNotification(this)">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
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

</body>
</html>
