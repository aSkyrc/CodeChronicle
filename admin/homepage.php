<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';

// Assuming $db is your MongoDB database instance initialized in connection.php
$usersCollection = $db->selectCollection('users');  // Accessing the 'users' collection
$googleUsersCollection = $db->selectCollection('google-users');  // Accessing the 'google-users' collection

// Get the total number of users (from 'users' and 'google-users' collections)
$totalUsers = $usersCollection->countDocuments() + $googleUsersCollection->countDocuments();

// Get total number of blogs (from 'blog' collection)
$blogCollection = $db->selectCollection('blog');  // Accessing the 'blog' collection
$totalBlogs = $blogCollection->countDocuments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Hi Admin</h1>

    <!-- Display Admin ID -->
    <div>
        <h3>Admin ID: <?php echo htmlspecialchars($_SESSION['admin_id']); ?></h3>
    </div>

    <!-- Total Users -->
    <div>
        <h3>Total Users: <?php echo $totalUsers; ?></h3>
    </div>

    <!-- Total Blogs -->
    <div>
        <h3>Total Blogs: <?php echo $totalBlogs; ?></h3>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="/Code Chronicle/connection/logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
