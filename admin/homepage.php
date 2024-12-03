<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';

// Assuming $db is your MongoDB database instance initialized in connection.php
$usersCollection = $db->selectCollection('users');  // Accessing the 'users' collection
$googleUsersCollection = $db->selectCollection('google-users');  // Accessing the 'google-users' collection

// Get the total number of users (from 'users' and 'google-users' collections)
$totalUsers = $usersCollection->countDocuments() + $googleUsersCollection->countDocuments();

// Get total number of blogs (from 'blog' collection)
$blogCollection = $db->selectCollection('blog');  // Accessing the 'blog' collection
$totalBlogs = $blogCollection->countDocuments();

// Predefined categories
$allCategories = [
    "Frontend Development", 
    "Backend Development", 
    "Data Science and Machine Learning", 
    "Mobile Development", 
    "DevOps and Cloud Computing", 
    "Cybersecurity", 
    "Programming Language", 
    "Algorithms and Data Structures", 
    "Game Development", 
    "Career and Networking"
];


$weeklyBlogCounts = [];

// Loop through each predefined category
foreach ($allCategories as $category) {
    // Count the number of blogs in each category created in the current week (Monday to Sunday)
    $count = $blogCollection->countDocuments([
        'category' => $category

    ]);
    
    // Store the count for the category (even if it's zero)
    $weeklyBlogCounts[$category] = $count;
}

// Get user registrations for the current year (for the line chart)
$currentYear = date('Y');
$startOfYear = new MongoDB\BSON\UTCDateTime(strtotime("{$currentYear}-01-01 00:00:00") * 1000);
$endOfYear = new MongoDB\BSON\UTCDateTime(strtotime("{$currentYear}-12-31 23:59:59") * 1000);

// Count new user registrations in the current year
$userRegistrationsThisYear = $usersCollection->countDocuments(['created_at' => ['$gte' => $startOfYear, '$lte' => $endOfYear]]);

// Count blog posts per category for the pie chart
$categoryCounts = [];
foreach ($allCategories as $category) {
    // Count the number of blog posts in each category
    $categoryCounts[] = $blogCollection->countDocuments(['category' => $category]);
}

// JSON encode the arrays for use in JavaScript
$categoriesJson = json_encode($allCategories); // This ensures all categories are included
$weeklyBlogCountsJson = json_encode(array_values($weeklyBlogCounts)); // This gives only the counts in the correct order
$categoryCountsJson = json_encode($categoryCounts ?? []); // Added this for the pie chart
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <title>Admin</title>
    <link rel="shortcut icon" href="../logos/CClogo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="statistics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lalezar&display=swap">
</head>
<body>
    <div class="sidebar">
        <h2>Hi Admin</h2>
        <img src="../uploads/hanni-newjeans-get-up-4k-wallpaper-uhdpaper.com-948@1@k.jpg" alt="Profile Pic" class="rounded-corners" />
        <ul>
            <li><a class="buttons" href="adminstatistic.html">Statistics</a></li>
            <li><a class="buttons" href="admincommunity.html">Community</a></li>
        </ul>
        <a class="button" href="../connection/logout.php">Logout</a>
    </div>
    <div class="dashboard">
        <div class="line-chart">
            <canvas id="lineChart"></canvas>
        </div>
        <div class="info-boxes">
            <div class="info-box financial">
                <p>Total Users: <?php echo $totalUsers; ?></p>
            </div>
            <div class="info-box financial">
                <p>Total Blog Posts: <?php echo $totalBlogs; ?></p>
            </div>
            <div class="pie-chart">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
        <div class="bar-chart">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctxLine = document.getElementById('lineChart').getContext('2d');
        var lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [
                    {
                        label: 'User Registrations This Year',
                        data: [<?php echo implode(',', array_map(function ($month) use ($usersCollection, $currentYear) {
                            $start = new MongoDB\BSON\UTCDateTime(strtotime("{$currentYear}-{$month}-01 00:00:00") * 1000);
                            $end = new MongoDB\BSON\UTCDateTime(strtotime("{$currentYear}-{$month}-31 23:59:59") * 1000);
                            return $usersCollection->countDocuments(['created_at' => ['$gte' => $start, '$lte' => $end]]);
                        }, range(1, 12))); ?>],
                        borderColor: '#6c63ff',
                        fill: false
                    }
                ]
            },
            options: {
                title: {
                    display: true,
                    text: 'User Registrations Over the Year'
                }
            }
        });

        var ctxBar = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo $categoriesJson; ?>, // All categories
                datasets: [
                    {
                        label: 'Blog Posts in the Last Week',
                        data: <?php echo $weeklyBlogCountsJson; ?>, // Weekly blog counts per category
                        backgroundColor: '#6c63ff'
                    }
                ]
            },
            options: {
                title: {
                    display: true,
                    text: 'Blog Posts in the Last Week by Category'
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var ctxPie = document.getElementById('pieChart').getContext('2d');
        var pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: <?php echo $categoriesJson; ?>, // Categories (labels)
                datasets: [
                    {
                        data: <?php echo $categoryCountsJson; ?>, // Counts of blog posts per category
                        backgroundColor: ['#9f94f4', '#8274f0', '#6553ed', '#4733ea', '#2d16e2', '#2713c2', '#2010a2', '#1a0d81', '#130961', '#0d0640']
                    }
                ]
            },
            options: {
                title: {
                    display: true,
                    text: 'Blog Category Distribution'
                }
            }
        });
    });
    </script>

</body>
</html>
