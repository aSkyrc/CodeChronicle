<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/admin-session-config.php';
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
    <link rel="stylesheet" href="COMMUNITY123.css">
    <title>Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lalezar&display=swap">
</head>
<body>
    <div class="sidebar">
        <h2>Hi Admin</h2>
        <img src="../uploads/hanni-newjeans-get-up-4k-wallpaper-uhdpaper.com-948@1@k.jpg" alt="Profile Pic" class="rounded-corners" />
        <ul>
            <li><a class="buttons" href="homepage.php">Statistics</a></li>
            <li><a class="buttons" href="community.php">Community</a></li>
        </ul>
        <a class="button" href="../connection/logout-admin.php">Logout</a>
    </div>
    <div class="dashboard">
    <div class="community">
    <div class="community-main-container">
        <div class="community-name">
            <h3 class="community-com">━━━ Community ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</h3>
        </div>
        <div class="community-container1">
            <div class="community-row">
                <div class="community-content">
                    <a href="communityFrontEnd.php">
                        <h3>Frontend Development</h3>
                        <img class="community-image1" src="../logos/community/frontend.jpeg" width="450px" height="100 px">
                    </a>
                </div>
                <div class="community-content">
                    <a href="communityBackEnd.php">
                        <h3>Backend Development</h3>
                        <img class="community-image1" src="../logos/community/backend.jpg" width="450px" height="100px">
                    </a>
                </div>
            </div>
            
            <div class="community-row">
                <div class="community-content">
                    <a href="communityDataScience.php">
                        <h3>Data Science and Machine Learning</h3>
                        <img class="community-image1" src="../logos/community/DATA SCIENCE.jpg" width="450px" height="100px">
                    </a>
                </div>
                <div class="community-content">
                    <a href="communityMobile.php">
                        <h3>Mobile Development</h3>
                        <img class="community-image1" src="../logos/community/mobiledev.jpg" width="450px" height="100px">
                    </a>
                </div>
            </div>
            <div class="community-row">
                <div class="community-content">
                    <a href="communityDevOps.php">
                        <h3>DevOps and Cloud Computing</h3>
                        <img class="community-image1" src="../logos/community/DevOps.png" width="450px" height="100px">
                    </a>
                </div>
                <div class="community-content">
                    <a href="communityGameDev.php">
                        <h3>Game Development</h3>
                        <img class="community-image1" src="../logos/community/gamingdev.jpeg" width="450px" height="100px">
                    </a>
                </div>
            </div>
            <div class="community-row">
                <div class="community-content">
                    <a href="communityCyberSecurity.php">
                        <h3>Cybersecurity</h3>
                        <img class="community-image1" src="../logos/community/cybersecutiry.webp" width="450px" height="100px">
                    </a>
                </div>
                <div class="community-content">
                    <a href="communityProgrammingLanguage.php">
                        <h3>Programming Languages</h3>
                        <img class="community-image1" src="../logos/community/programminglanguages.jpg" width="450px" height="100px">
                    </a>
                </div>
            </div>
            <div class="community-row">
                <div class="community-content">
                    <a href="communityAlgorithms.php">
                        <h3> Algorithms and Data Structures</h3>
                        <img class="community-image1" src="../logos/community/algorithm.jpeg" width="450px" height="100px">
                    </a>
                </div>
                <div class="community-content">
                    <a href="communityNetworking.php">
                        <h3>Career and Networking</h3>
                        <img class="community-image1" src="../logos/community/careernetworking.jpg" width="450px" height="100px">
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
 
</body>
</html>