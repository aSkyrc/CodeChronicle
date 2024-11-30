<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';

if (!isset($_SESSION)) {
    session_start();
}

// Initialize error messages
$usernameErr = $passwordErr = $otpErr = "";

if (isset($_GET['google'])) {
    header('Location: google-login.php'); // Redirect to Google login
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $about = $_POST['about'] ?? '';   // Default to empty string if no about is provided

        // Check if both fields are filled
        if (empty($username)) {
            $usernameErr = "Username is required.";
        } elseif (empty($password)) {
            $passwordErr = "Password is required.";
        } else {
            // Connect to the admin database
            $adminDB = $mongoClient->selectDatabase('admin');
            $adminCollection = $adminDB->selectCollection('account'); // Admin collection
            
            // Check if the user is an admin
            $admin = $adminCollection->findOne(['username' => $username]);

            if ($admin) {
                // Verify admin credentials
                $dbPassword = $admin['password']; // Retrieved password from database
            
                // Use password_verify for hashed passwords
                if (password_verify($password, $dbPassword) || $password === $dbPassword) {
                    $_SESSION['admin_id'] = $admin['_id'];
                    $_SESSION['admin_username'] = $username;
            
                    // Redirect to admin homepage
                    header('Location: ../admin/homepage.php');
                    exit();
                } else {
                    $passwordErr = "Incorrect admin information.";
                }
            } else {
                // Check if the user is a regular user in the default database
                $usersCollection = $collections['users'];
                $user = $usersCollection->findOne(['username' => $username]);

                if ($user) {
                    // Check OTP status before proceeding with password verification
                    if ($user['otp-status'] == false) {
                        $otpErr = "Cannot login. Verify your Account.";
                    } else {
                        // Verify the password
                        if (password_verify($password, $user['password'])) {
                            // Store user ID in session to make it secure
                            $_SESSION['user_id'] = $user['_id'];
                            $_SESSION['username'] = $username;

                            // Check if this is the user's first login
                            if (isset($user['first_login']) && $user['first_login'] == false) {
                                // Update the first_login field to true in the database
                                $usersCollection->updateOne(
                                    ['username' => $username],
                                    ['$set' => ['first_login' => true]]
                                );

                                // Add role_status and interest_status to the user-profile collection with false values
                                $userProfileCollection = $collections['user-profile'];
                                $userProfileCollection->insertOne([
                                    'user_id' => $user['_id'],
                                    'role_status' => false,
                                    'interest_status' => false,
                                    'about' => $about           // Insert about (empty string or filled data)
                                ]);

                                // Redirect to userSelectionRole.php for the first login
                                header('Location: userSelectionRole.php');
                                exit();
                            } else {
                                // Check if the user already has a profile in user-profile collection
                                $userProfileCollection = $collections['user-profile'];
                                $existingProfile = $userProfileCollection->findOne(['user_id' => $user['_id']]);

                                if (!$existingProfile) {
                                    // Add role_status and interest_status if no profile exists
                                    $userProfileCollection->insertOne([
                                        'user_id' => $user['_id'],
                                        'role_status' => false,
                                        'interest_status' => false,
                                        'about' => $about           // Insert about (empty string or filled data)
                                    ]);
                                }

                            
                                header('Location: homepage.php');
                                exit();
                            }
                        } else {
                            $passwordErr = "Incorrect password.";
                        }
                    }
                } else {
                    $usernameErr = "No account found with that username.";
                }
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
    <link rel="stylesheet" href="../screen/css/userSelectionCategory.css">
    <link rel="stylesheet" href="../screen/css/userSelectionRole.css">
    <link rel="stylesheet" href="../screen/css/designLanding.css">
    <link rel="stylesheet" href="../screen/css/otp.css">


    <title>Code Chronicle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">

        <!-- Sign In Form -->
        <div id="signin-form-content">
            <div class="logo">
                <img src="../logos/CClogo.jpg" alt="Code Chronicle Logo">
                <div class="cc">
                    <label>Code Chronicle</label>
                </div>
                <div class="cc2">
                    <label>Your Gateway to Programming Insights</label>
                </div>
            </div>

            <div class="text">
                <label>Login Form</label>
                <br>
                <label class="line">____________________________</label>
            </div>
            
            
                    <a class="go"href="?google=true" >
                        <i><img src="../logos/google.png" alt="Google" height="27px" width="27px">
                            <label>Google</label>
                        </i>
                    </a>

            <form method="POST" action="" id="login-form">
                <div class="inputbox">

                    <div class="inputtext">
                        <label for="username">Username</label>
                        <input class="username" type="text" id="username" name="username" placeholder="Enter Username" required>
                    </div>

                    <div class="inputtext">
                        <label for="password">Password</label>
                        <input class="password" type="password" id="password" name="password" placeholder="Enter Password" required>
                    </div>

                    <!-- Display error messages here -->
                    <?php if (!empty($usernameErr) || !empty($passwordErr) || !empty($otpErr)) { ?>
                        <div class="error-messages">
                            <?php
                                if (!empty($usernameErr)) {
                                    echo "<p class='error'>$usernameErr</p>";
                                }
                                if (!empty($passwordErr)) {
                                    echo "<p class='error'>$passwordErr</p>";
                                }
                                if (!empty($otpErr)) {
                                    echo "<p class='error'>$otpErr</p>";
                                }
                            ?>
                        </div>
                    <?php } ?>

                    <div class="signin-button">
                        <button id="submit" value="Signin" name="login">Sign In</button>
                    </div>
                </div>
                
             <input type="hidden" name="about" value="">
            </form>

            <div class="message">
                <label>Welcome to Code Chronicle – Empowering You<br>with Seamless Content Management</label>
            </div>

            <div class="signuplink">
                <label>Don't have an account?</label>
                <a href="#">Create Now!</a>
            </div>
        </div>
    </div>

    <script src="../screen/javascript/LANDING.js"></script>
</body>
</html>
